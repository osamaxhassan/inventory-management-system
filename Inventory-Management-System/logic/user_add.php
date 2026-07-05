<?php
// logic/user_add.php
session_start();
require '../config/db.php';
require '../includes/functions.php';
require_once 'logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Only Admin can execute this
    if ($_SESSION['user_role'] !== 'Admin') {
        die("Unauthorized.");
    }

    $full_name = clean_input($_POST['full_name']);
    $username = clean_input($_POST['username']);
    $password = $_POST['password']; // In production, use password_hash()
    $role = $_POST['role'];

    try {
        $sql = "INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $username, $password, $role]);

        // LOG IT!
        log_activity($pdo, "Created new $role account for: $username");

        header("Location: ../admin/users.php?msg=user_created");
        exit;
    } catch (PDOException $e) {
        die("Error: Username might already exist. " . $e->getMessage());
    }
}