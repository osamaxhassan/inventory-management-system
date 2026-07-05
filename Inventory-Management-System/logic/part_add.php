<?php
// logic/part_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';
require 'logger.php';
log_activity($pdo, "Added $quantityx $part_name to Job Card #$repair_id.");

// Security: Only Admins or Accountants should add new catalog items
check_role(['Admin', 'Accountant']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $part_name     = clean_input($_POST['part_name']);
    $category      = clean_input($_POST['category']);
    $brand         = clean_input($_POST['brand']);
    $compatibility = clean_input($_POST['vehicle_compatibility']);
    $bin_location  = clean_input($_POST['bin_location']);
    $purchase_price= (float) $_POST['purchase_price'];
    $selling_price = (float) $_POST['selling_price'];
    
    // Usually 0 when just creating the catalog entry (they will use inventory_add to actually buy the stock)
    $qty_in_stock  = isset($_POST['quantity_in_stock']) ? (int)$_POST['quantity_in_stock'] : 0; 

    try {
        $sql = "INSERT INTO parts (part_name, category, brand, vehicle_compatibility, quantity_in_stock, purchase_price, selling_price, bin_location) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$part_name, $category, $brand, $compatibility, $qty_in_stock, $purchase_price, $selling_price, $bin_location]);
        // ... after successfully adding the part to the repair_orders ...
        require_once 'logger.php';
        log_activity($pdo, "Added quantity $quantity of Part ID #$part_id to Job Card #$repair_id");


        // THE TELEPORTER
        header("Location: ../admin/inventory.php?msg=part_added");
        exit;
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>