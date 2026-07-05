<?php
// admin/inventory.php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

try {
    // Fetch all parts currently in the database
    $stmt = $pdo->query("SELECT * FROM parts ORDER BY part_name ASC");
    $inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management - Star Auto</title>
    <style>
        /* Reusing our standard layout */
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { color: #3498db; margin-top: 0; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; transition: 0.3s; }
        .sidebar a:hover { color: #3498db; padding-left: 5px; }
        .content { flex-grow: 1; padding: 30px; height: 100vh; overflow-y: auto; box-sizing: border-box;}
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #34495e; font-size: 14px;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #2980b9; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        table th { background-color: #ecf0f1; color: #2c3e50; }
        .low-stock { color: #e74c3c; font-weight: bold; }
        .good-stock { color: #2ecc71; font-weight: bold; }
    </style>
</head>
<body>

    <!-- <div class="sidebar">
        <h2>Star Auto</h2>
        <p>Logged in as: <strong><?php echo $_SESSION['user_role']; ?></strong></p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="job_cards.php">📝 Job Cards</a>
        <a href="inventory.php" style="color: #3498db;">📦 Inventory</a>
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

        <?php if (isset($_GET['msg'])): ?>
            <div style="background-color: #2ecc71; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                <?php 
                    if ($_GET['msg'] == 'part_added') echo "✅ New Part Catalog Entry Created!";
                    if ($_GET['msg'] == 'stock_purchased') echo "✅ Stock Successfully Received and Added to Shelf!";
                ?>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px;">
            
            <div class="card" style="flex: 1;">
                <h3 style="margin-top:0; border-bottom: 2px solid #ecf0f1; padding-bottom:10px;">1. Create New Catalog Part</h3>
                <form action="../logic/part_add.php" method="POST">
                    <div class="form-group"><label>Part Name</label><input type="text" name="part_name" required placeholder="e.g., Oil Filter XL"></div>
                    <div class="form-group"><label>Category</label><input type="text" name="category" required placeholder="e.g., Engine"></div>
                    <div class="form-group"><label>Brand</label><input type="text" name="brand" required placeholder="e.g., Toyota"></div>
                    <div class="form-group"><label>Compatibility</label><input type="text" name="vehicle_compatibility" placeholder="e.g., Corolla 2018-2022"></div>
                    <div class="form-group"><label>Bin Location</label><input type="text" name="bin_location" placeholder="e.g., Shelf A2"></div>
                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;"><label>Purchase Price</label><input type="number" name="purchase_price" required></div>
                        <div class="form-group" style="flex:1;"><label>Selling Price</label><input type="number" name="selling_price" required></div>
                    </div>
                    <input type="hidden" name="quantity_in_stock" value="0">
                    <button type="submit" class="btn-submit">Create Part in Catalog</button>
                </form>
            </div>

            <div class="card" style="flex: 1;">
                <h3 style="margin-top:0; border-bottom: 2px solid #ecf0f1; padding-bottom:10px;">2. Buy Stock / Receive Delivery</h3>
                <form action="../logic/inventory_add.php" method="POST">
                    <div class="form-group">
                        <label>Select Part to Restock</label>
                        <select name="part_id" required>
                            <option value="">-- Choose a part --</option>
                            <?php foreach($inventory_items as $item): ?>
                                <option value="<?php echo $item['part_id']; ?>"><?php echo $item['part_name']; ?> (Current: <?php echo $item['quantity_in_stock']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Quantity Received</label><input type="number" name="quantity" required min="1"></div>
                    <div class="form-group"><label>New Purchase Price (Unit)</label><input type="number" name="purchase_price" required></div>
                    <div class="form-group"><label>Total Tax Paid</label><input type="number" name="purchase_tax" value="0"></div>
                    <div class="form-group"><label>Delivery Date</label><input type="date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <button type="submit" class="btn-submit" style="background-color:#2ecc71;">Record Stock Delivery</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0; border-bottom: 2px solid #ecf0f1; padding-bottom:10px;">Current Master Inventory List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Part Name</th>
                        <th>Brand & Category</th>
                        <th>Shelf Location</th>
                        <th>Buy / Sell Price</th>
                        <th>Stock Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($inventory_items as $item): ?>
                    <tr>
                        <td>#<?php echo $item['part_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($item['part_name']); ?></strong><br><small><?php echo htmlspecialchars($item['vehicle_compatibility']); ?></small></td>
                        <td><?php echo htmlspecialchars($item['brand']); ?><br><small><?php echo htmlspecialchars($item['category']); ?></small></td>
                        <td><?php echo htmlspecialchars($item['bin_location']); ?></td>
                        <td>Buy: <?php echo number_format($item['purchase_price']); ?><br>Sell: <?php echo number_format($item['selling_price']); ?></td>
                        <td class="<?php echo ($item['quantity_in_stock'] <= 5) ? 'low-stock' : 'good-stock'; ?>">
                            <?php echo $item['quantity_in_stock']; ?> 
                            <?php if($item['quantity_in_stock'] <= 5) echo "⚠️"; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>