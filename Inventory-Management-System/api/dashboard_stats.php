<?php
// api/dashboard_stats.php
session_start();
require '../config/db.php';

// ========================================================================
// 1. THE BOUNCER (Security)
// ========================================================================
// We are temporarily commenting this out just so you can test it easily 
// in your browser right now. In real life, uncomment these 4 lines!
/*
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Accountant')) {
    die(json_encode(['error' => 'Access Denied. Admins and Accountants only.']));
}
*/

try {
    // ========================================================================
    // 2. THE MATH (Database Queries)
    // ========================================================================

    // A. Total Income (Sum of all PAID jobs)
    $stmt_income = $pdo->query("SELECT SUM(final_amount) as total_income FROM repair_orders WHERE status = 'Completed'");
    $income = $stmt_income->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;

    // B. Total Workshop Expenses (Rent, Electricity, Tea)
    $stmt_expenses = $pdo->query("SELECT SUM(amount) as total_expenses FROM expenses");
    $expenses = $stmt_expenses->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;

    // C. Total Payroll (Mechanic Salaries)
    $stmt_payroll = $pdo->query("SELECT SUM(amount) as total_payroll FROM payroll");
    $payroll = $stmt_payroll->fetch(PDO::FETCH_ASSOC)['total_payroll'] ?? 0;

    // D. Inventory Value (How much money is sitting on the physical shelves?)
    $stmt_inventory = $pdo->query("SELECT SUM(quantity_in_stock * purchase_price) as inventory_value FROM parts");
    $inventory = $stmt_inventory->fetch(PDO::FETCH_ASSOC)['inventory_value'] ?? 0;

    // E. Basic Net Profit (Money In minus Money Out)
    // Note: A real ERP would also subtract the 'Cost of Goods Sold', but this is a great start.
    $net_profit = $income - ($expenses + $payroll);

    // ========================================================================
    // 3. THE DELIVERY (Package it for the Frontend Guy)
    // ========================================================================
    $dashboard_data = [
        'status'          => 'success',
        'total_income'    => $income,
        'total_expenses'  => $expenses,
        'total_payroll'   => $payroll,
        'inventory_value' => $inventory,
        'net_profit'      => $net_profit
    ];

    // Tell the browser "Hey, this is raw data, not a webpage!"
    header('Content-Type: application/json');
    
    // Spit out the data as a JSON string
    echo json_encode($dashboard_data);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>