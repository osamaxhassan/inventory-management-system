<?php
// includes/functions.php
// ====================================================================
// MASTER UTILITY LIBRARY
// ====================================================================

/**
 * Cleans raw user input to prevent SQL Injection and XSS hacks.
 */
function clean_input($raw_data) {
    return htmlspecialchars(stripslashes(trim($raw_data)));
}

/**
 * Formats raw numbers into proper currency.
 * Input: 1500 -> Output: "1,500.00 PKR"
 */
function format_money($amount) {
    return number_format((float)$amount, 2, '.', ',') . " PKR";
}

/**
 * THE MASTER BOUNCER (Role Security)
 * Call this at the top of a protected page to check the user's VIP wristband.
 * Example: check_role(['Admin', 'Accountant']);
 */
function check_role($allowed_roles) {
    // 1. Check if they are even logged in
    if (!isset($_SESSION['user_role'])) {
        // Redirect them to the login screen
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
    
    // 2. Check if their specific role is allowed on this page
    $user_role = $_SESSION['user_role'];
    if (!in_array($user_role, $allowed_roles)) {
        die("Access Denied: " . $user_role . "s do not have permission to view this page.");
    }
}
?>