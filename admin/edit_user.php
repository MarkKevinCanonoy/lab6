<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_user) {
        header("Location: users.php");
        exit();
    }
} else {
    header("Location: users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_id = $_POST['id'];
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];
    $new_role = $_POST['role'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name = :name, email = :email, role = :role, password = :password WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':password', $hashed_password);
    } else {
        $update_sql = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
    }

    $update_stmt->bindParam(':name', $new_name);
    $update_stmt->bindParam(':email', $new_email);
    $update_stmt->bindParam(':role', $new_role);
    $update_stmt->bindParam(':id', $update_id);

    if ($update_stmt->execute()) {
        header("Location: users.php");
        exit();
    } else {
        $error_msg = "failed to update user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh;">
    
    <div class="login-card">
        <h2 style="margin-bottom: 20px;">Edit User</h2>
        
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $current_user['id']; ?>">
            
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($current_user['name']); ?>" required>
            </div>
            
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
            </div>

            <div class="input-group">
                <label>Password (leave blank to keep current)</label>
                <input type="password" name="password" placeholder="new password">
            </div>
            
            <div class="input-group">
                <label>Account Role</label>
                <select name="role" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="Instructor" <?php if($current_user['role'] == 'Instructor') echo 'selected'; ?>>Instructor</option>
                    <option value="Student" <?php if($current_user['role'] == 'Student') echo 'selected'; ?>>Student</option>
                    <option value="Admin" <?php if($current_user['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Update User</button>
            
            <a href="users.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6b7280; font-size: 14px;">Cancel</a>
            
        </form>
    </div>

</body>
</html>