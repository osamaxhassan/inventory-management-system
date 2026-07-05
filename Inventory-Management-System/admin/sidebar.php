<?php 
// Figure out what page we are currently on so we can highlight it blue!
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<div style="width: 250px; flex-shrink: 0; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box;">
    <h2 style="color: #3498db; margin-top: 0; font-family: 'Segoe UI', sans-serif;">Ayaan Motors</h2>
    <p style="color: #95a5a6; font-size: 14px; border-bottom: 1px solid #34495e; padding-bottom: 10px; font-family: 'Segoe UI', sans-serif;">
        Logged in as: <strong><?php echo $_SESSION['user_role']; ?></strong>
    </p>
    
    <a href="dashboard.php" style="color: <?php echo ($current_page == 'dashboard.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">📊 Dashboard</a>
    
    <a href="job_cards.php" style="color: <?php echo ($current_page == 'job_cards.php' || $current_page == 'job_manage.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">📝 Job Cards</a>

    <?php if ($_SESSION['user_role'] == 'Admin' || $_SESSION['user_role'] == 'Accountant'): ?>
        <a href="inventory.php" style="color: <?php echo ($current_page == 'inventory.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">📦 Inventory</a>
        
        <a href="expenses.php" style="color: <?php echo ($current_page == 'expenses.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">💸 Expenses & Payroll</a>
        
        <a href="customers.php" style="color: <?php echo ($current_page == 'customers.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">👥 Customers & Vehicles</a>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] == 'Admin'): ?>
        <hr style="border: 0; border-top: 1px solid #34495e; margin: 20px 0;">
        <p style="color: #95a5a6; font-size: 11px; text-transform: uppercase; margin-bottom: 10px;">Administration</p>

        <a href="users.php" style="color: <?php echo ($current_page == 'users.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">👤 Manage Staff</a>
        
        <a href="reports.php" style="color: <?php echo ($current_page == 'reports.php') ? '#3498db' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">📈 Reports & Tracking</a>
        
        <a href="logs.php" style="color: <?php echo ($current_page == 'logs.php') ? '#e74c3c' : 'white'; ?>; text-decoration: none; display: block; padding: 12px 0; font-family: 'Segoe UI', sans-serif;">🔒 Audit Logs</a>
    <?php endif; ?>
        
</div>