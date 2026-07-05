<?php
// admin/expenses.php
session_start();

// 1. The Regular Bouncer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

// 2. THE VIP BOUNCER: Only Admin and Accountant can see the money!
if ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Accountant') {
    header("Location: job_cards.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses & Payroll - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .content { flex-grow: 1; padding: 30px; box-sizing: border-box; height: 100vh; overflow-y: auto;}
        .header { background: white; padding: 20px 30px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        .form-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #34495e; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #27ae60; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin:0;">Record Money Out</h1>
            <a href="../logic/auth_logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log Out</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background-color: #2ecc71; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                <?php 
                    if ($_GET['msg'] == 'expense_success') echo "✅ Expense recorded successfully!";
                    if ($_GET['msg'] == 'payroll_success') echo "✅ Payroll recorded successfully!";
                ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px;">
            
            <div class="form-card" style="flex: 1;">
                <h3>Add Daily Expense</h3>
                <form action="../logic/expense_add.php" method="POST">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="expense_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Electricity">Electricity Bill</option>
                            <option value="Water">Water Bill</option>
                            <option value="Rent">Shop Rent</option>
                            <option value="Tea">Customer Tea/Snacks</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" required placeholder="e.g., Biscuits for waiting area">
                    </div>
                    <div class="form-group">
                        <label>Amount (PKR)</label>
                        <input type="number" name="amount" required min="1">
                    </div>
                    <button type="submit" class="btn-submit">Record Expense</button>
                </form>
            </div>

            <div class="form-card" style="flex: 1;">
                <h3>Pay Employee</h3>
                <form action="../logic/payroll_add.php" method="POST">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="payment_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Employee Name</label>
                        <input type="text" name="employee_name" required placeholder="e.g., Ali Mechanic">
                    </div>
                    <div class="form-group">
                        <label>Amount (PKR)</label>
                        <input type="number" name="amount" required min="1">
                    </div>
                    <button type="submit" class="btn-submit" style="background-color: #3498db;">Record Payroll</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>