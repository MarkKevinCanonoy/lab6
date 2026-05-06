<?php
session_start();
require_once '../db.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY role ASC, name ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Attendance System</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="overview.php"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="users.php" class="active"><i class="fa-solid fa-user-group"></i> Users</a></li>
                    <li><a href="subjects.php"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
                    <li><a href="enrollments.php"><i class="fa-solid fa-user-plus"></i> Enrollments</a></li>
                    <li><a href="schedules.php"><i class="fa-regular fa-calendar"></i> Schedules</a></li>
                    <li><a href="reports.php"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header header-with-action">
                <div>
                    <h1>Manage Users</h1>
                    <p>Add, edit, or delete users.</p>
                </div>
                    <a href="add_user.php" class="btn-primary" style="width: auto; text-decoration: none;">+ Add User</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>ROLE</th>
                            <th class="text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td class="font-medium"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td class="actions-cell text-right">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn"><i class="fa-solid fa-pen"></i></a>
    
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('are you sure you want to delete this user?');"><i class="fa-regular fa-trash-can"></i></a>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>