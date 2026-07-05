<?php
// logic/logger.php

function log_activity($pdo, $action) {
    // Only log if someone is actually logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['full_name'];
        $role = $_SESSION['user_role'];

        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, user_name, role, action) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $role, $action]);
    }
}
?>