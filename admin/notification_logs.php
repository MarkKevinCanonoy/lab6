<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$logs = $pdo->query("
    SELECT n.*, s.name as subject_name 
    FROM notifications n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    ORDER BY n.date_sent DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Logs</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/announcement.css">
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
                    <li><a href="users.php"><i class="fa-solid fa-user-group"></i> Users</a></li>
                    <li><a href="subjects.php"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
                    <li><a href="enrollments.php"><i class="fa-solid fa-user-plus"></i> Enrollments</a></li>
                    <li><a href="schedules.php"><i class="fa-regular fa-calendar"></i> Schedules</a></li>
                    <li><a href="reports.php"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
                    <li><a href="send_announcement.php"><i class="fa-solid fa-bullhorn"></i> Send Announcement</a></li>
                    <li><a href="notification_logs.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Announcement Logs</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <h1>Announcement Logs</h1>
            </header>
            <div class="announcement-container" style="max-width: 95%;">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Sent By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $logs->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['date_sent'] ?></td>
                            <td><?= $row['subject_name'] ?></td>
                            <td><?= $row['title'] ?></td>
                            <td><?= nl2br($row['message']) ?></td>
                            <td><?= $row['sent_by'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>