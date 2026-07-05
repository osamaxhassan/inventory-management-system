<?php
// logic/backup_db.php
session_start();
require '../config/db.php';
require_once 'logger.php';

// 1. THE VIP BOUNCER: Only the Admin can download the shop's brain!
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    die("Error: Unauthorized access.");
}

try {
    // 2. Find all tables in the database
    $tables = [];
    $query = $pdo->query("SHOW TABLES");
    while ($row = $query->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    // 3. Start writing the Backup File
    $sql_dump = "-- ===========================================\n";
    $sql_dump .= "-- Ayaan Motors Complete Database Backup\n";
    $sql_dump .= "-- Generated: " . date('d-M-Y H:i A') . "\n";
    $sql_dump .= "-- ===========================================\n\n";

    // 4. Loop through every single table to get its structure and data
    foreach ($tables as $table) {
        // Get the "CREATE TABLE" blueprint
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $create_table = $stmt->fetch(PDO::FETCH_NUM);
        $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql_dump .= $create_table[1] . ";\n\n";

        // Get every single row of data inside that table
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $sql_dump .= "INSERT INTO `$table` VALUES (";
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    // Escape weird characters so the SQL doesn't break
                    $escaped_value = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
                    $values[] = "'" . $escaped_value . "'";
                }
            }
            $sql_dump .= implode(", ", $values) . ");\n";
        }
        $sql_dump .= "\n\n";
    }

    // 5. SECURITY: Log that the Admin downloaded a backup!
    log_activity($pdo, "Downloaded a full system database backup.");

    // 6. Force the browser to download it as a file
    $filename = "AyaanMotors_Backup_" . date('Y-m-d_H-i') . ".sql";
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $sql_dump;
    exit;

} catch (PDOException $e) {
    die("Backup Error: " . $e->getMessage());
}
?>