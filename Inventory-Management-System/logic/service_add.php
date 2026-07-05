<?php
// logic/service_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';

// Security: Only Admins or Accountants should add new services to the menu
check_role(['Admin', 'Accountant']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Catch the form data
    $service_name       = clean_input($_POST['service_name']);
    $category           = clean_input($_POST['category']); // e.g., 'Engine', 'Electrical', 'Detailing'
    $default_labor_cost = (float) $_POST['default_labor_cost'];

    try {
        // Insert it into the Database Guy's 'services' table
        $sql = "INSERT INTO services (service_name, category, default_labor_cost) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$service_name, $category, $default_labor_cost]);
        require_once 'logger.php';
        log_activity($pdo, "Added a new labor service: $service_name");

        // Success!
        // header("Location: ../admin/services.php?success=1");
        echo "Success: New Service added to the master menu.";

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>