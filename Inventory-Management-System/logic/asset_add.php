<?php
// logic/asset_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asset_name     = clean_input($_POST['asset_name']);
    $purchase_price = (float) $_POST['purchase_price'];
    $purchase_date  = clean_input($_POST['purchase_date']);

    try {
        $sql = "INSERT INTO workshop_assets (asset_name, purchase_price, purchase_date) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$asset_name, $purchase_price, $purchase_date]);
        require_once 'logger.php';
        log_activity($pdo, "Purchased shop asset: $asset_name for PKR " . number_format($amount));
        
        echo "Success: Workshop asset recorded.";
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>