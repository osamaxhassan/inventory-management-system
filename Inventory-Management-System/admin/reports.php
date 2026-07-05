<?php
// admin/reports.php
session_start();
require '../config/db.php';

// The VIP Bouncer: Only the Admin/Owner belongs here!
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: job_cards.php");
    exit;
}

// 1. Figure out what timeframe the user clicked (Default to 'today')
$time_filter = isset($_GET['time']) ? $_GET['time'] : 'today';

// 2. Set the SQL conditions based on the filter
if ($time_filter == 'today') {
    $filter_label = "Today's Overview";
    $ro_date = "DATE(repair_date) = CURDATE()";
    $ex_date = "DATE(expense_date) = CURDATE()";
    $pr_date = "DATE(payment_date) = CURDATE()";
} elseif ($time_filter == 'month') {
    $filter_label = "This Month's Overview";
    $ro_date = "MONTH(repair_date) = MONTH(CURDATE()) AND YEAR(repair_date) = YEAR(CURDATE())";
    $ex_date = "MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())";
    $pr_date = "MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
} else { // 'year'
    $filter_label = "This Year's Overview";
    $ro_date = "YEAR(repair_date) = YEAR(CURDATE())";
    $ex_date = "YEAR(expense_date) = YEAR(CURDATE())";
    $pr_date = "YEAR(payment_date) = YEAR(CURDATE())";
}

try {
    // 3. Run the filtered math!
    
    // Income & Completed Jobs
    $stmt = $pdo->query("SELECT SUM(final_amount) as total_income, COUNT(repair_id) as jobs_done FROM repair_orders WHERE status = 'Completed' AND $ro_date");
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $income = (float)$sales_data['total_income'];
    $jobs_done = (int)$sales_data['jobs_done'];

    // Expenses
    $exp_sum = $pdo->query("SELECT SUM(amount) FROM expenses WHERE $ex_date")->fetchColumn();
    $pay_sum = $pdo->query("SELECT SUM(amount) FROM payroll WHERE $pr_date")->fetchColumn();
    $total_expenses = (float)$exp_sum + (float)$pay_sum;

    // Net Profit
    $net_profit = $income - $total_expenses;

    // Fetch the actual jobs completed in this timeframe for the table
    $stmt_jobs = $pdo->query("
        SELECT ro.repair_id, ro.repair_date, ro.final_amount, c.name, v.registration_number 
        FROM repair_orders ro 
        JOIN vehicle v ON ro.vehicle_id = v.vehicle_id 
        JOIN customer c ON v.customer_id = c.customer_id 
        WHERE ro.status = 'Completed' AND $ro_date 
        ORDER BY ro.repair_date DESC
    ");
    $recent_jobs = $stmt_jobs->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #3498db; margin-top: 0; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; transition: 0.3s; }
        .sidebar a:hover { color: #3498db; padding-left: 5px; }
        .content { flex-grow: 1; padding: 30px; height: 100vh; overflow-y: auto; box-sizing: border-box; }
        
        .header { background: white; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 24px; }
        .btn-logout { background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        
        /* Time Filter Buttons */
        .filter-bar { background: white; padding: 15px 30px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-filter { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: #34495e; font-weight: bold; border: 2px solid #bdc3c7; transition: 0.3s; }
        .btn-filter:hover { background: #ecf0f1; }
        .btn-filter.active { background: #3498db; color: white; border-color: #3498db; }

        /* Stat Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #3498db; }
        .stat-card h3 { margin: 0 0 10px 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 26px; font-weight: bold; color: #2c3e50; }

        /* Data Table */
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        table th { background-color: #ecf0f1; color: #2c3e50; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h1>Sales Tracking & Reports</h1>
            <a href="../logic/auth_logout.php" class="btn-logout">Log Out</a>
        </div>

        <div class="filter-bar">
            <span style="padding-top: 8px; font-weight: bold; color: #7f8c8d;">Timeframe:</span>
            <a href="reports.php?time=today" class="btn-filter <?php echo $time_filter == 'today' ? 'active' : ''; ?>">Today</a>
            <a href="reports.php?time=month" class="btn-filter <?php echo $time_filter == 'month' ? 'active' : ''; ?>">This Month</a>
            <a href="reports.php?time=year" class="btn-filter <?php echo $time_filter == 'year' ? 'active' : ''; ?>">This Year</a>
        </div>

        <h3 style="color: #2c3e50; border-bottom: 2px solid #bdc3c7; padding-bottom: 5px;"><?php echo $filter_label; ?></h3>

        <div class="stats-grid">
            <div class="stat-card" style="border-color: #3498db;">
                <h3>Sales Revenue</h3>
                <div class="value">PKR <?php echo number_format($income); ?></div>
            </div>
            <div class="stat-card" style="border-color: #e74c3c;">
                <h3>Money Out (Expenses)</h3>
                <div class="value">PKR <?php echo number_format($total_expenses); ?></div>
            </div>
            <div class="stat-card" style="border-color: <?php echo $net_profit >= 0 ? '#2ecc71' : '#e74c3c'; ?>;">
                <h3>Net Profit</h3>
                <div class="value" style="color: <?php echo $net_profit >= 0 ? '#2ecc71' : '#e74c3c'; ?>;">
                    PKR <?php echo number_format($net_profit); ?>
                </div>
            </div>
            <div class="stat-card" style="border-color: #9b59b6;">
                <h3>Jobs Completed</h3>
                <div class="value"><?php echo $jobs_done; ?></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Completed Jobs in this Timeframe</h3>
            <table>
                <thead>
                    <tr>
                        <th>Job #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Vehicle Reg</th>
                        <th>Total Billed</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_jobs as $job): ?>
                    <tr>
                        <td><strong>#<?php echo $job['repair_id']; ?></strong></td>
                        <td><?php echo date('d-M-Y', strtotime($job['repair_date'])); ?></td>
                        <td><?php echo htmlspecialchars($job['name']); ?></td>
                        <td><span style="background: #f1c40f; padding: 4px 8px; border-radius: 4px; font-family:monospace; font-weight:bold;"><?php echo htmlspecialchars($job['registration_number']); ?></span></td>
                        <td style="color:#27ae60; font-weight:bold;">PKR <?php echo number_format($job['final_amount']); ?></td>
                        <td><a href="../logic/generate_pdf.php?job_id=<?php echo $job['repair_id']; ?>" target="_blank" style="color: #e74c3c; text-decoration:none; font-weight:bold;">📄 PDF</a></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($recent_jobs)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#7f8c8d; padding: 20px;">No completed jobs found for this timeframe.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>