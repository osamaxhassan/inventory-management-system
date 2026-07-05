<?php
// logic/investment_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

// Security: ONLY the Admin (Owner) should be able to touch this
check_role(['Admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount          = (float) $_POST['amount'];
    $investment_date = clean_input($_POST['investment_date']);
    $notes           = clean_input($_POST['notes']);

    try {
        $sql = "INSERT INTO capital_investments (amount, investment_date, notes) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$amount, $investment_date, $notes]);
        require_once 'logger.php';
        log_activity($pdo, "Added business investment of PKR " . number_format($amount));
        
        echo "Success: Capital investment recorded successfully.";
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>