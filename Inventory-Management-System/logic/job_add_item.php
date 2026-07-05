<?php
// logic/job_add_item.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $repair_id = (int)$_POST['repair_id'];
    $item_type = clean_input($_POST['item_type']); // 'Part' or 'Service'
    $item_id   = (int)$_POST['item_id'];
    $price     = (float)$_POST['price'];

    try {
        if ($item_type === 'Part') {
            $quantity = (int)$_POST['quantity'];
            
            // 1. Insert into repair_parts
            $stmt = $pdo->prepare("INSERT INTO repair_parts (repair_id, part_id, quantity, selling_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$repair_id, $item_id, $quantity, $price]);
            
            // 2. Deduct physical stock from the shelf
            $stmt_stock = $pdo->prepare("UPDATE parts SET quantity_in_stock = quantity_in_stock - ? WHERE part_id = ?");
            $stmt_stock->execute([$quantity, $item_id]);

            // Teleport back to the specific Job Card
            header("Location: ../admin/job_manage.php?id=" . $repair_id . "&msg=item_added");
            exit;

        } else if ($item_type === 'Service') {
            // Insert into repair_services
            $stmt = $pdo->prepare("INSERT INTO repair_services (repair_id, service_id, labor_cost) VALUES (?, ?, ?)");
            $stmt->execute([$repair_id, $item_id, $price]);
            require_once 'logger.php';
            log_activity($pdo, "Added an item/part to Job Card #$repair_id");
            
            echo "Success: Service Labor added to Job.";
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>