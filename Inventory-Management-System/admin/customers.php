<?php
// admin/customers.php
session_start();
require '../config/db.php';

// The Bouncer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

// VIP Bouncer: Only Admin and Accountant can view customer data
if ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Accountant') {
    header("Location: job_cards.php");
    exit;
}

try {
    // Fetch all customers and join their vehicle data
    $stmt = $pdo->query("
        SELECT c.customer_id, c.name, c.phone, v.brand, v.model, v.registration_number
        FROM customer c
        LEFT JOIN vehicle v ON c.customer_id = v.customer_id
        ORDER BY c.name ASC
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers & Vehicles - Ayaan Motors</title>
    <style>
        /* Standard Layout */
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        
        /* Fixed content spacing (removed the old margin-left: 250px hack!) */
        .content { flex-grow: 1; padding: 30px; box-sizing: border-box; height: 100vh; overflow-y: auto;}
        
        .header { background: white; padding: 20px 30px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        table th { background-color: #ecf0f1; color: #2c3e50; }
        
        .avatar { background-color: #3498db; color: white; padding: 8px 12px; border-radius: 50%; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin:0;">Client Directory</h1> 
            <a href="../logic/auth_logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log Out</a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Customer Details</th>
                        <th>Phone Number</th>
                        <th>Vehicle Make & Model</th>
                        <th>Registration No.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr>
                        <td>
                            <span class="avatar"><?php echo strtoupper(substr($c['name'], 0, 1)); ?></span>
                            <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                        </td>
                        
                        <td>
                            <?php echo !empty($c['phone']) ? htmlspecialchars($c['phone']) : '<span style="color: #95a5a6; font-style: italic;">No Phone</span>'; ?>
                        </td>
                        
                        <td>
                            <?php echo !empty($c['brand']) ? htmlspecialchars($c['brand'] . ' ' . $c['model']) : '<span style="color: #95a5a6; font-style: italic;">No Vehicle</span>'; ?>
                        </td>
                        
                        <td>
                            <?php if(!empty($c['registration_number'])): ?>
                                <span style="background: #f1c40f; padding: 4px 8px; border-radius: 4px; font-weight:bold; font-family:monospace;">
                                    <?php echo htmlspecialchars($c['registration_number']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #95a5a6; font-style: italic;">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>