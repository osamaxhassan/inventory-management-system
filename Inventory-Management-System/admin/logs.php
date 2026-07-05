<?php
// admin/logs.php
session_start();
require '../config/db.php';

// 1. The Regular Bouncer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

// 2. THE VIP BOUNCER: Strictly Admin Only!
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

try {
    // Fetch the 100 most recent logs
    $stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the table doesn't exist yet, just create an empty array to prevent crashing
    $logs = []; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Logs - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .content { flex-grow: 1; padding: 30px; box-sizing: border-box; height: 100vh; overflow-y: auto;}
        
        .header { background: white; padding: 20px 30px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        table th { background-color: #ecf0f1; color: #2c3e50; font-size: 14px; text-transform: uppercase;}
        table tr:hover { background-color: #f9f9f9; }
        
        /* Badges for roles */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
        .badge-admin { background-color: #e74c3c; }
        .badge-accountant { background-color: #9b59b6; }
        .badge-mechanic { background-color: #f39c12; }
        .badge-staff { background-color: #34495e; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin:0; color: #2c3e50;">System Audit Logs</h1>
                <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 14px;">Immutable ledger of all system activity (Read-Only).</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="../logic/backup_db.php" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">💾 Download Database Backup</a>
                
                <a href="../logic/auth_logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log Out</a>
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action Performed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #7f8c8d; padding: 20px;">No activity logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td style="color: #7f8c8d; font-size: 14px;">
                                <?php echo date('d-M-Y', strtotime($log['created_at'])) . '<br><small>' . date('h:i A', strtotime($log['created_at'])) . '</small>'; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($log['user_name']); ?></strong></td>
                            <td>
                                <?php 
                                    $role_class = 'badge-staff';
                                    if ($log['role'] == 'Admin') $role_class = 'badge-admin';
                                    if ($log['role'] == 'Accountant') $role_class = 'badge-accountant';
                                    if ($log['role'] == 'Mechanic') $role_class = 'badge-mechanic';
                                ?>
                                <span class="badge <?php echo $role_class; ?>"><?php echo htmlspecialchars($log['role']); ?></span>
                            </td>
                            <td style="color: #2c3e50;"><?php echo htmlspecialchars($log['action']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>