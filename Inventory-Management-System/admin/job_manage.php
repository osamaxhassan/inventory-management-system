<?php
// admin/job_manage.php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit;
}

// 1. Get the Job ID from the URL
if (!isset($_GET['id'])) {
    die("Error: No Job ID selected.");
}
$repair_id = (int)$_GET['id'];

try {
    // 2. Fetch the Car & Customer Details
    $stmt = $pdo->prepare("
        SELECT ro.*, v.brand, v.model, v.registration_number, c.name as customer_name
        FROM repair_orders ro
        JOIN vehicle v ON ro.vehicle_id = v.vehicle_id
        JOIN customer c ON v.customer_id = c.customer_id
        WHERE ro.repair_id = ?
    ");
    $stmt->execute([$repair_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) die("Job not found.");

    // 3. Fetch Items already added to the bill
    $stmt_parts = $pdo->prepare("SELECT rp.*, p.part_name FROM repair_parts rp JOIN parts p ON rp.part_id = p.part_id WHERE rp.repair_id = ?");
    $stmt_parts->execute([$repair_id]);
    $added_parts = $stmt_parts->fetchAll(PDO::FETCH_ASSOC);

    $stmt_serv = $pdo->prepare("SELECT rs.*, s.service_name FROM repair_services rs JOIN services s ON rs.service_id = s.service_id WHERE rs.repair_id = ?");
    $stmt_serv->execute([$repair_id]);
    $added_services = $stmt_serv->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch dropdown catalogs for the form
    $catalog_parts = $pdo->query("SELECT * FROM parts WHERE quantity_in_stock > 0")->fetchAll(PDO::FETCH_ASSOC);
    $catalog_services = $pdo->query("SELECT * FROM services WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Current Running Total
    $running_total = 0;
    foreach($added_parts as $p) $running_total += ($p['selling_price'] * $p['quantity']);
    foreach($added_services as $s) $running_total += $s['labor_cost'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Job #<?php echo $repair_id; ?> - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; height: 100vh; padding: 20px; box-sizing: border-box; position: fixed;}
        .sidebar h2 { color: #3498db; margin-top: 0; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; transition: 0.3s; }
        .sidebar a:hover { color: #3498db; padding-left: 5px; }
        .content { flex-grow: 1; padding: 30px; box-sizing: border-box; }
        
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group select, .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background: #f9f9f9; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; color: white;}
        .btn-add { background: #3498db; }
        .btn-success { background: #2ecc71; width: 100%; font-size: 16px; margin-top: 15px;}
        .btn-pdf { background: #e74c3c; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 4px; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; color: white; }
        .bg-pending { background: #f39c12; }
        .bg-completed { background: #2ecc71; }
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
        <a href="customers.php">👥 Customers</a>
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
            
            <div>
                <h1 style="margin:0;">Job Card #<?php echo $job['repair_id']; ?> 
                    <span class="badge <?php echo $job['status'] == 'Completed' ? 'bg-completed' : 'bg-pending'; ?>">
                        <?php echo $job['status']; ?>
                    </span>
                </h1>
                <p style="margin: 5px 0 0 0; color: #7f8c8d;">
                    <strong>Vehicle:</strong> <?php echo $job['brand'] . ' ' . $job['model'] . ' (' . $job['registration_number'] . ')'; ?> | 
                    <strong>Customer:</strong> <?php echo $job['customer_name']; ?>
                </p>
            </div>

            <a href="../logic/auth_logout.php" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log Out</a>
            
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'item_added'): ?>
            <div style="background: #2ecc71; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px;">✅ Item added to bill!</div>
        <?php endif; ?>

        <div style="display: flex; gap: 20px;">
            
            <?php if($job['status'] == 'Pending'): ?>
            <div class="card" style="flex: 1;">
                <h3>Add Part or Labor</h3>
                <form action="../logic/job_add_item.php" method="POST">
                    <input type="hidden" name="repair_id" value="<?php echo $repair_id; ?>">
                    
                    <div class="form-group">
                        <label>Item Type</label>
                        <select name="item_type" id="itemType" required>
                            <option value="Part">Spare Part</option>
                            <option value="Service">Labor / Service</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Item</label>
                        <select name="item_id" required>
                            <optgroup label="--- INVENTORY PARTS ---">
                                <?php foreach($catalog_parts as $p): ?>
                                    <option value="<?php echo $p['part_id']; ?>">Part: <?php echo $p['part_name']; ?> (Stock: <?php echo $p['quantity_in_stock']; ?>) - PKR <?php echo $p['selling_price']; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="--- LABOR SERVICES ---">
                                <?php foreach($catalog_services as $s): ?>
                                    <option value="<?php echo $s['service_id']; ?>">Service: <?php echo $s['service_name']; ?> - PKR <?php echo $s['default_labor_cost']; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity (If Part)</label>
                        <input type="number" name="quantity" value="1" min="1">
                    </div>

                    <div class="form-group">
                        <label>Final Price/Labor Cost (PKR)</label>
                        <input type="number" name="price" required placeholder="Enter the agreed price">
                    </div>

                    <button type="submit" class="btn btn-add">Add to Bill</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="card" style="flex: 2;">
                <h3 style="margin-top:0;">Current Bill</h3>
                <table>
                    <thead>
                        <tr><th>Type</th><th>Description</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($added_parts as $p): ?>
                        <tr>
                            <td><span class="badge" style="background:#3498db;">Part</span></td>
                            <td><?php echo $p['part_name']; ?></td>
                            <td><?php echo $p['quantity']; ?></td>
                            <td><?php echo number_format($p['selling_price']); ?></td>
                            <td><strong><?php echo number_format($p['selling_price'] * $p['quantity']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php foreach($added_services as $s): ?>
                        <tr>
                            <td><span class="badge" style="background:#f39c12;">Labor</span></td>
                            <td><?php echo $s['service_name']; ?></td>
                            <td>1</td>
                            <td><?php echo number_format($s['labor_cost']); ?></td>
                            <td><strong><?php echo number_format($s['labor_cost']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h3 style="text-align: right; margin-top: 20px;">Subtotal: PKR <?php echo number_format($running_total); ?></h3>

                <?php if($job['status'] == 'Pending'): ?>
                    <hr>
                    <form action="../logic/job_finalize.php" method="POST">
                        <input type="hidden" name="repair_id" value="<?php echo $repair_id; ?>">
                        <div class="form-group" style="text-align:right;">
                            <label>Apply Discount (PKR)</label>
                            <input type="number" name="discount" value="0" min="0" style="width: 150px; display:inline-block;">
                        </div>
                        <button type="submit" class="btn btn-success">Finalize Job & Apply Tax (17%)</button>
                    </form>
                <?php else: ?>
                    <hr>
                    <div style="text-align: right;">
                        <h4 style="color:#2ecc71;">GRAND TOTAL PAID: PKR <?php echo number_format($job['final_amount']); ?></h4>
                        <a href="../logic/generate_pdf.php?job_id=<?php echo $repair_id; ?>" target="_blank" class="btn btn-pdf">📄 Print PDF Invoice</a>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

</body>
</html>