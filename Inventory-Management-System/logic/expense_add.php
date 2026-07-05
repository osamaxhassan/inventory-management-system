<?php
// logic/expense_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

// BOUNCER: Only logged-in users can add expenses
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Catch the frontend form data
    $expense_date = clean_input($_POST['expense_date']);
    $category     = clean_input($_POST['category']);      // e.g., 'Electricity', 'Tea'
    $description  = clean_input($_POST['description']);
    $amount       = (float) $_POST['amount'];

    try {
        // 2. Insert exactly into the Database Guy's table
        $sql = "INSERT INTO expenses (expense_date, category, description, amount) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$expense_date, $category, $description, $amount]);
        require 'logger.php';
        log_activity($pdo, "Recorded an expense of PKR $amount for $category.");

        // 3. THE TELEPORTER: Send them back to the expenses page
        header("Location: ../admin/expenses.php?msg=expense_success");
        exit;

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>