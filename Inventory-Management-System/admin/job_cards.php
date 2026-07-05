<?php
// admin/job_cards.php
session_start();
require '../config/db.php'; // We need the database to list the cars!

// The Bouncer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

// Fetch all "Pending" jobs (cars currently in the workshop)
try {
    $stmt = $pdo->query("
        SELECT ro.repair_id, ro.repair_date, v.registration_number, v.brand, v.model, c.name as customer_name
        FROM repair_orders ro
        JOIN vehicle v ON ro.vehicle_id = v.vehicle_id
        JOIN customer c ON v.customer_id = c.customer_id
        WHERE ro.status = 'Pending'
        ORDER BY ro.repair_date DESC
    ");
    $pending_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching jobs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Cards - Ayaan Motors</title>
    <style>
        /* Reusing our standard layout */
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #3498db; margin-top: 0; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; transition: 0.3s; }
        .sidebar a:hover { color: #3498db; padding-left: 5px; }
        .content { flex-grow: 1; padding: 30px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #34495e; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #2980b9; }
        
        /* Data Table CSS */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        table th { background-color: #ecf0f1; color: #2c3e50; }
        .btn-manage { background-color: #f39c12; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 14px; font-weight: bold;}
        .btn-manage:hover { background-color: #e67e22; }
    </style>
</head>
<body>

    <!-- <div class="sidebar">
        <h2>Ayaan Motors</h2>
        <p>Logged in as: <strong><?php echo $_SESSION['user_role']; ?></strong></p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="job_cards.php" style="color: #3498db;">📝 Job Cards</a>
        <a href="inventory.php">📦 Inventory</a>
        <a href="expenses.php">💸 Expenses & Payroll</a>
        <a href="customers.php">👥 Customers & Vehicles</a>
    </div> -->

    <!-- <div class="sidebar">
        <h2>Ayaan Motors</h2>
        <p>Logged in as: <strong><?php echo $_SESSION['user_role']; ?></strong></p>
        
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        $role = $_SESSION['user_role'];
        ?>

        <?php if ($role == 'Admin'): ?>
            <a href="dashboard.php" style="<?php echo ($current_page == 'dashboard.php') ? 'color: #3498db;' : ''; ?>">📊 Dashboard</a>
        <?php endif; ?>

        <a href="job_cards.php" style="<?php echo ($current_page == 'job_cards.php' || $current_page == 'job_manage.php') ? 'color: #3498db;' : ''; ?>">📝 Job Cards</a>

        <a href="inventory.php" style="<?php echo ($current_page == 'inventory.php') ? 'color: #3498db;' : ''; ?>">📦 Inventory</a>

        <?php if ($role == 'Admin' || $role == 'Accountant'): ?>
            <a href="expenses.php" style="<?php echo ($current_page == 'expenses.php') ? 'color: #3498db;' : ''; ?>">💸 Expenses & Payroll</a>
            <a href="customers.php" style="<?php echo ($current_page == 'customers.php') ? 'color: #3498db;' : ''; ?>">👥 Customers & Vehicles</a>
        <?php endif; ?>
    </div> -->

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin:0;">Workshop Job Cards</h1> <a href="../logic/auth_logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log Out</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'job_created'): ?>
            <div style="background-color: #2ecc71; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                ✅ New Vehicle Checked In Successfully!
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px;">
            
            <div class="form-card" style="flex: 1;">
                <h3 style="margin-top:0; border-bottom: 2px solid #ecf0f1; padding-bottom:10px;">Check In Vehicle</h3>
                <form action="../logic/job_create.php" method="POST">
                    <div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" required></div>
                    <div class="form-group"><label>Customer Phone</label><input type="text" name="customer_phone" required></div>
                    <div class="form-group"><label>Vehicle Brand</label><input type="text" name="brand" placeholder="e.g., Suzuki" required></div>
                    <div class="form-group"><label>Vehicle Model</label><input type="text" name="model" placeholder="e.g., Alto" required></div>
                    <div class="form-group"><label>Registration No.</label><input type="text" name="registration_number" placeholder="e.g., KHI-9999" required></div>
                    <button type="submit" class="btn-submit">Open Job Card</button>
                </form>
            </div>

            <div class="form-card" style="flex: 2;">
                <h3 style="margin-top:0; border-bottom: 2px solid #ecf0f1; padding-bottom:10px;">Vehicles Currently in Workshop</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Job #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending_jobs as $job): ?>
                        <tr>
                            <td><strong>#<?php echo $job['repair_id']; ?></strong></td>
                            <td><?php echo date('d-M-Y', strtotime($job['repair_date'])); ?></td>
                            <td><?php echo htmlspecialchars($job['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($job['brand'] . ' ' . $job['model']) . '<br><small>' . htmlspecialchars($job['registration_number']) . '</small>'; ?></td>
                            <td>
                                <a href="job_manage.php?id=<?php echo $job['repair_id']; ?>" class="btn-manage">Manage</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($pending_jobs)): ?>
                            <tr><td colspan="5" style="text-align:center; color:#7f8c8d;">No active jobs in the workshop right now.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>