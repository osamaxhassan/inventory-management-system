<?php
// admin/users.php
session_start();
require '../config/db.php';

// BOUNCER: Only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

// Fetch all existing staff
$stmt = $pdo->query("SELECT user_id, username, full_name, password_hash, role FROM users ORDER BY role ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management - Ayaan Motors</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ecf0f1; margin: 0; display: flex; }
        .content { flex-grow: 1; padding: 30px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; }
        .btn-add { background: #3498db; color: white; }
        .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .Admin { background: #e74c3c; }
        .Accountant { background: #9b59b6; }
        .Mechanic { background: #f39c12; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h1 style="margin:0;">Staff Management</h1>
            <span>Logged in as: <strong>Admin</strong></span>
        </div>

        <div class="card">
            <h3>Add New Staff Member</h3>
            <form action="../logic/user_add.php" method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                <div>
                    <label>Full Name</label><br>
                    <input type="text" name="full_name" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                </div>
                <div>
                    <label>Username</label><br>
                    <input type="text" name="username" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                </div>
                <div>
                    <label>Password</label><br>
                    <input type="password" name="password" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                </div>
                <div>
                    <label>Assign Role</label><br>
                    <select name="role" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="Mechanic">Mechanic</option>
                        <option value="Accountant">Accountant</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-add">+ Create Account</button>
            </form>
        </div>

        <div class="card">
            <h3>Current Team</h3>
            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Password</th> <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><span class="role-badge <?php echo $user['role']; ?>"><?php echo $user['role']; ?></span></td>
                        <td>
                            <span id="pass-<?php echo $user['user_id']; ?>" style="filter: blur(4px); cursor: pointer;" onclick="this.style.filter='none'">
                                <?php echo htmlspecialchars($user['password_hash']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="../logic/user_delete.php?id=<?php echo $user['user_id']; ?>" 
                            onclick="return confirm('Are you sure? This staff member will lose all access.')" 
                            style="color: #e74c3c; text-decoration: none; font-size: 14px; font-weight: bold;">
                            <i class="fas fa-trash"></i> Remove
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>