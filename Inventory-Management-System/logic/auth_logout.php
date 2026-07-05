<?php
// logic/auth_logout.php
session_start();

// --- THE SECURITY CAMERA GOES HERE FIRST ---
// We must log them out BEFORE we destroy their wristband,
// otherwise the system won't know WHO is leaving!
if (isset($_SESSION['user_id'])) {
    require '../config/db.php';
    require 'logger.php';
    log_activity($pdo, "Logged out of the system.");
}
// -------------------------------------------

// 1. Destroy all VIP wristbands (Session variables)
session_unset();
session_destroy();

// 2. THE TELEPORTER: Send them back to the login screen
header("Location: ../index.php?msg=logged_out");
exit;
?>