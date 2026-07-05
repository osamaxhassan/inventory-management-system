<?php
// logic/job_finalize.php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $repair_id = (int)$_POST['repair_id'];
    $discount  = isset($_POST['discount']) ? (float)$_POST['discount'] : 0.00;
    
    // We see in the dummy data the DB Guy used a 17% tax rate
    $tax_rate  = 17.00; 

    try {
        // 1. Calculate Parts Total
        $stmt_p = $pdo->prepare("SELECT SUM(quantity * selling_price) as parts_total FROM repair_parts WHERE repair_id = ?");
        $stmt_p->execute([$repair_id]);
        $parts_total = $stmt_p->fetch(PDO::FETCH_ASSOC)['parts_total'] ?? 0.00;

        // 2. Calculate Services Total
        $stmt_s = $pdo->prepare("SELECT SUM(labor_cost) as serv_total FROM repair_services WHERE repair_id = ?");
        $stmt_s->execute([$repair_id]);
        $serv_total = $stmt_s->fetch(PDO::FETCH_ASSOC)['serv_total'] ?? 0.00;

        // 3. Do the final math
        $raw_total = $parts_total + $serv_total;
        $taxable_amount = $raw_total - $discount;
        
        if ($taxable_amount < 0) $taxable_amount = 0; // Prevent negative bills
        
        $tax_amount = $taxable_amount * ($tax_rate / 100);
        $final_amount = $taxable_amount + $tax_amount;

        // 4. Save EVERYTHING to the Repair Order and lock it
        $sql = "UPDATE repair_orders 
                SET total_amount = ?, discount = ?, tax_rate = ?, tax_amount = ?, final_amount = ?, status = 'Completed', completed_at = NOW() 
                WHERE repair_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$raw_total, $discount, $tax_rate, $tax_amount, $final_amount, $repair_id]);
        require_once 'logger.php';
        log_activity($pdo, "Completed Job Card #$repair_id with a final bill of PKR " . number_format($final_amount));

        // Teleport back to the specific Job Card so they can print the PDF
        header("Location: ../admin/job_manage.php?id=" . $repair_id . "&msg=job_finalized");
        exit;

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>