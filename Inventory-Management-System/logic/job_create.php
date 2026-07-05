<?php
// logic/job_create.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Catch the form data
    $name   = clean_input($_POST['customer_name']);
    $phone  = clean_input($_POST['customer_phone']);
    $brand  = clean_input($_POST['brand']);
    $model  = clean_input($_POST['model']);
    $reg_no = clean_input($_POST['registration_number']);
    $repair_date = date('Y-m-d'); // Automatically set to today

    try {
        // Lock the database so we can do 3 things safely
        $pdo->beginTransaction(); 

        // Step A: Create Customer
        $stmt = $pdo->prepare("INSERT INTO customer (name, phone) VALUES (?, ?)");
        $stmt->execute([$name, $phone]);
        $customer_id = $pdo->lastInsertId(); // Get the ID of the guy we just created!

        // Step B: Create Vehicle using that Customer ID
        $stmt = $pdo->prepare("INSERT INTO vehicle (customer_id, brand, model, registration_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_id, $brand, $model, $reg_no]);
        $vehicle_id = $pdo->lastInsertId(); // Get the ID of the car!

        // Step C: Create Pending Job Card using that Vehicle ID
        $stmt = $pdo->prepare("INSERT INTO repair_orders (vehicle_id, repair_date, status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$vehicle_id, $repair_date]);
        $repair_id = $pdo->lastInsertId();
        require_once 'logger.php';
        log_activity($pdo, "Created a new Job Card for vehicle ID: $vehicle_id");

        // Unlock and Save!
        $pdo->commit(); 
        
        // THE TELEPORTER
        header("Location: ../admin/job_cards.php?msg=job_created");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack(); // If anything went wrong, undo all 3 steps
        die("Database Error: " . $e->getMessage());
    }
}
?>