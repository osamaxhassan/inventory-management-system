<?php
// logic/inventory_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $part_id        = (int) $_POST['part_id'];
    $quantity       = (int) $_POST['quantity'];
    $purchase_price = (float) $_POST['purchase_price'];
    $purchase_tax   = (float) $_POST['purchase_tax'];
    $purchase_date  = clean_input($_POST['purchase_date']);

    try {
        $pdo->beginTransaction();

        // 1. Record the receipt in part_purchases
        $sql1 = "INSERT INTO part_purchases (part_id, quantity, purchase_price, purchase_tax, purchase_date) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$part_id, $quantity, $purchase_price, $purchase_tax, $purchase_date]);
        require_once 'logger.php';
        log_activity($pdo, "Added $quantity units to Inventory Part ID: $part_id");

        // 2. ADD the physical stock to the Parts table
        $sql2 = "UPDATE parts SET quantity_in_stock = quantity_in_stock + ?, purchase_price = ? WHERE part_id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$quantity, $purchase_price, $part_id]);

        $pdo->commit();
        // THE TELEPORTER
        header("Location: ../admin/inventory.php?msg=stock_purchased");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Database Error: " . $e->getMessage());
    }
}
?>