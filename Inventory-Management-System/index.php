<?php
// index.php
session_start();

// If they are already logged in, send them straight to the dashboard!
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayaan Motors and Oil Lubricant - Login</title>
    <style>
        /* Professional CSS Styling for the Login Screen */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .login-card p {
            color: #7f8c8d;
            margin-bottom: 25px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            box-sizing: border-box; /* keeps padding inside width */
            font-size: 16px;
        }
        .btn-login {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background-color: #2980b9;
        }
        .error-message {
            color: #e74c3c;
            background: #fadbd8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Ayaan Motors and Oil Lubricant</h2>
        <p>Inventory Management System</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message" style="color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php 
                    if ($_GET['error'] == 'wrong_credentials') echo "❌ Incorrect username or password.";
                    elseif ($_GET['error'] == 'unauthorized') echo "🔒 Please log in to access the system.";
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'logged_out'): ?>
            <div style="color: #27ae60; background: #eaeded; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                ✅ You have been securely logged out.
            </div>
        <?php endif; ?>

        <form action="logic/auth_login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn-login">Log In</button>
        </form>
    </div>

</body>
</html>