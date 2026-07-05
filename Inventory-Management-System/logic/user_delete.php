<?php
// logic/user_delete.php
session_start();
require '../config/db.php';
require_once 'logger.php';

if (isset($_GET['id']) && $_SESSION['user_role'] === 'Admin') {
    $user_id = (int)$_GET['id'];
    
    // Prevent the Admin from accidentally deleting themselves!
    if ($user_id == $_SESSION['user_id']) {
        die("Error: You cannot delete your own admin account.");
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    log_activity($pdo, "Removed staff member (User ID: $user_id)");

    header("Location: ../admin/users.php?msg=user_deleted");
    exit;
}