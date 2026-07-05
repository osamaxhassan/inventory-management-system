<?php
// admin/dashboard.php
session_start();
require '../config/db.php'; // We need the DB to count active jobs and stock!

// The Session Bouncer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

$role = $_SESSION['user_role'];

try {
    // Quick Queries for the Operations Dashboard
    $pending_jobs = $pdo->query("SELECT COUNT(*) FROM repair_orders WHERE status = 'Pending'")->fetchColumn();
    $low_stock = $pdo->query("SELECT COUNT(*) FROM parts WHERE quantity_in_stock <= 5")->fetchColumn();
} catch (PDOException $e) {
    $pending_jobs = 0; $low_stock = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #3498db; margin-top: 0; }
        .sidebar p { color: #95a5a6; font-size: 14px; border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; font-size: 16px; transition: 0.3s; }
        .sidebar a:hover { color: #3498db; padding-left: 5px; }
        
        .content { flex-grow: 1; padding: 30px; }
        .header { background: white; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;}
        .header h1 { margin: 0; color: #2c3e50; font-size: 24px; }
        .btn-logout { background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        
        /* Welcome Banner */
        .welcome-banner { background: linear-gradient(135deg, #3498db, #2c3e50); color: white; padding: 30px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .welcome-banner h2 { margin: 0 0 10px 0; font-size: 28px; }
        .welcome-banner p { margin: 0; font-size: 16px; opacity: 0.9; }

        /* Grid System */
        .section-title { color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #bdc3c7; padding-bottom: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #3498db; }
        .stat-card h3 { margin: 0 0 10px 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 28px; font-weight: bold; color: #2c3e50; }
        
        /* Quick Action Buttons */
        .btn-quick { display: inline-block; background: #2ecc71; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 15px; }
        .btn-quick:hover { background: #27ae60; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        
        <div class="header">
            <h1 style="margin:0;">Dashboard Overview</h1>
            <a href="../logic/auth_logout.php" class="btn-logout">Log Out</a>
        </div>

        <div class="welcome-banner">
            <h2>Welcome to the workshop, <?php echo $_SESSION['full_name']; ?>!</h2>
            <p>You are logged in securely as <strong><?php echo $role; ?></strong>. Have a great shift at Ayaan Motors.</p>
        </div>

        <?php if ($role == 'Admin' || $role == 'Accountant'): ?>
            <h3 class="section-title">Financial Overview</h3>
            <div class="stats-grid">
                <div class="stat-card" style="border-color: #3498db;">
                    <h3>Total Income</h3>
                    <div class="value" id="val-income">Loading...</div>
                </div>
                <div class="stat-card" style="border-color: #f39c12;">
                    <h3>Workshop Expenses</h3>
                    <div class="value" id="val-expenses">Loading...</div>
                </div>
                <div class="stat-card" style="border-color: #9b59b6;">
                    <h3>Inventory Value</h3>
                    <div class="value" id="val-inventory">Loading...</div>
                </div>
                <div class="stat-card" style="border-color: #2ecc71;">
                    <h3>Net Profit</h3>
                    <div class="value" id="val-profit">Loading...</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($role == 'Admin' || $role == 'Mechanic' || $role == 'Staff'): ?>
            <h3 class="section-title">Workshop Operations</h3>
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                
                <div class="stat-card" style="border-color: #e67e22;">
                    <h3>Cars Currently in Garage</h3>
                    <div class="value"><?php echo $pending_jobs; ?></div>
                    <a href="job_cards.php" class="btn-quick" style="background: #e67e22;">View Job Cards</a>
                </div>

                <div class="stat-card" style="border-color: #e74c3c;">
                    <h3>Low Stock Alerts</h3>
                    <div class="value" style="<?php echo $low_stock > 0 ? 'color: #e74c3c;' : ''; ?>"><?php echo $low_stock; ?></div>
                    <?php if($role == 'Admin'): ?>
                        <a href="inventory.php" class="btn-quick" style="background: #e74c3c;">Check Inventory</a>
                    <?php else: ?>
                        <p style="color: #7f8c8d; font-size: 13px; margin-top:15px;">Notify Admin to restock.</p>
                    <?php endif; ?>
                </div>

                <div class="stat-card" style="border-color: #34495e;">
                    <h3>Quick Action</h3>
                    <div style="margin-top: 15px;">
                        <a href="job_cards.php" class="btn-quick" style="display: block; margin-bottom:10px;">+ Check In New Vehicle</a>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>

    <script>
        if (document.getElementById('val-income')) {
            fetch('../api/dashboard_stats.php')
                .then(response => response.json()) 
                .then(data => {
                    if(data.status === 'success') {
                        document.getElementById('val-income').innerText = 'PKR ' + parseFloat(data.total_income).toLocaleString();
                        document.getElementById('val-expenses').innerText = 'PKR ' + (parseFloat(data.total_expenses) + parseFloat(data.total_payroll)).toLocaleString();
                        document.getElementById('val-inventory').innerText = 'PKR ' + parseFloat(data.inventory_value).toLocaleString();
                        
                        let profitEl = document.getElementById('val-profit');
                        profitEl.innerText = 'PKR ' + parseFloat(data.net_profit).toLocaleString();
                        
                        if(data.net_profit < 0) {
                            profitEl.style.color = '#e74c3c'; 
                        } else {
                            profitEl.style.color = '#27ae60';
                        }
                    }
                })
                .catch(error => console.error('Error fetching API data:', error));
        }
    </script>

</body>
</html>