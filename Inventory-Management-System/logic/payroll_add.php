<?php
// logic/payroll_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_name = clean_input($_POST['employee_name']);
    $amount        = (float) $_POST['amount'];
    $payment_date  = clean_input($_POST['payment_date']);

    try {
        $sql = "INSERT INTO payroll (employee_name, amount, payment_date) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employee_name, $amount, $payment_date]);
        require_once 'logger.php';
        log_activity($pdo, "Paid PKR " . number_format($amount) . " to employee: $employee_name");
        
        // THE TELEPORTER: Send them back to the expenses page
        header("Location: ../admin/expenses.php?msg=payroll_success");
        exit;
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>