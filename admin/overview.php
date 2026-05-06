<?php
session_start();

require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$total_users = 0;
$total_subjects = 0;
$total_schedules = 0;
$total_attendance = 0;

try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    $total_subjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    
    $total_schedules = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
    
    $total_attendance = $pdo->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
    
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance System</title>
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
                    <li><a href="overview.php" class="active"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="users.php"><i class="fa-solid fa-user-group"></i> Users</a></li>
                    <li><a href="subjects.php"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
                    <li><a href="enrollments.php"><i class="fa-solid fa-user-plus"></i> Enrollments</a></li>
                    <li><a href="schedules.php"><i class="fa-regular fa-calendar"></i> Schedules</a></li>
                    <li><a href="reports.php"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
                    <li><a href="send_announcement.php"><i class="fa-solid fa-bullhorn"></i> Send Announcement</a></li>
                    <li><a href="notification_logs.php"><i class="fa-solid fa-clock-rotate-left"></i> Announcement Logs</a></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <span class="user-email">admin@test.com</span> 
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <h1>Dashboard Overview</h1>
                <p>Welcome back to the admin panel.</p>
            </header>

            <div class="stats-grid">
                
                <div class="stat-card">
                    <div class="stat-icon blue-icon"><i class="fa-solid fa-user-group"></i></div>
                    <div class="stat-details">
                        <p>Total Users</p>
                        <h3><?php echo $total_users; ?></h3>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-details">
                        <p>Total Subjects</p>
                        <h3><?php echo $total_subjects; ?></h3>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange-icon"><i class="fa-regular fa-calendar"></i></div>
                    <div class="stat-details">
                        <p>Total Schedules</p>
                        <h3><?php echo $total_schedules; ?></h3>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green-icon"><i class="fa-regular fa-circle-check"></i></div>
                    <div class="stat-details">
                        <p>Attendance Records</p>
                        <h3><?php echo $total_attendance; ?></h3>
                    </div>
                </div>
                
            </div>
        </main>

    </div>
</body>
</html>