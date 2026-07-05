<?php
// config/db.php
// The Bridge between your PHP logic and the Database Guy's tables

// ==========================================
// PLACEHOLDERS: WAITING FOR DATABASE GUY
// ==========================================
$host     = 'localhost'; // Since you are using XAMPP, this is always 'localhost'
$dbname   = 'inventory_system'; // What did you name the database?
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password is blank
// ==========================================

try {
    // Attempt to open the connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO to throw an error if something goes wrong (great for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // If the bridge collapses, stop the whole software and print the error
    die("Database Connection Failed. Tell the Backend Dev: " . $e->getMessage());
}
?>