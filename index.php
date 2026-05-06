<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = strtolower($_SESSION['role']);
    header("Location: $role/overview.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px; 
        }
        .password-container i {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #666; 
        }
    </style>
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <div class="icon-container">
                <i class="fa-regular fa-user"></i>
            </div>
            <h2>Welcome</h2>
            <p>Sign in to the Attendance System</p>
        </div>

        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="error-msg">
                <?php 
                    echo $_SESSION['login_error']; 
                    unset($_SESSION['login_error']); 
                ?>
            </div>
        <?php endif; ?>

        <form action="api/login_action.php" method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="admin@test.com" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" placeholder="password" required>
                    <i class="fa-regular fa-eye" id="togglePassword"></i>
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div style="margin-top: 20px; padding: 12px; background-color: rgba(224, 242, 254, 0.4); border: 1px solid rgba(186, 230, 253, 0.4); border-radius: 8px; text-align: center; opacity: 0.6;">
            <p style="font-weight: bold; color: #0369a1; margin-bottom: 5px; font-size: 13px;">admin demo account</p>
            <p style="color: #0f172a; font-size: 12px; margin-bottom: 2px;">Email: admin@test.com</p>
            <p style="color: #0f172a; font-size: 12px; margin: 0;">Password: password</p>
        </div>

    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>