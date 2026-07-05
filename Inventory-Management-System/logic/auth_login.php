<?php
// logic/auth_login.php
session_start(); 

require '../config/db.php';
require '../includes/functions.php';
require 'logger.php'; // Load the camera, but don't use it yet!

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Catch what the user typed in the HTML form
    $input_username = clean_input($_POST['username']); 
    $input_password = $_POST['password'];

    try {
        // 2. Look for the user in the database
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verify the user exists AND the password matches
        if ($user && $user['password_hash'] === $input_password) {
            
            // LOGIN SUCCESS: Put their VIP wristband on
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name']; 

            // --- THE SECURITY CAMERA GOES HERE ---
            // Now that the session is set, the logger knows who they are!
            log_activity($pdo, "Logged into the system.");
            // -------------------------------------

            // THE TELEPORTER: Send them directly to the visual dashboard!
            header("Location: ../admin/dashboard.php");
            exit;

        } else {
            // LOGIN FAILED: Kick them back to the login screen with an error code
            header("Location: ../index.php?error=wrong_credentials");
            exit;
        }
    } catch (PDOException $e) {
        die("System Error during login: " . $e->getMessage());
    }
}
?>