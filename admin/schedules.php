<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$schedules = [];

try {
    $sql = "SELECT schedules.id, schedules.class_date, subjects.code, subjects.name FROM schedules LEFT JOIN subjects ON schedules.subject_id = subjects.id ORDER BY schedules.class_date DESC";
    $stmt = $pdo->query($sql);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedules - Admin Panel</title>
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
                    <li><a href="users.php"><i class="fa-solid fa-user-group"></i> Users</a></li>
                    <li><a href="subjects.php"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
                    <li><a href="enrollments.php"><i class="fa-solid fa-user-plus"></i> Enrollments</a></li>
                    <li><a href="schedules.php" class="active"><i class="fa-regular fa-calendar"></i> Schedules</a></li>
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
                    <h1>Manage Schedules</h1>
                    <p>Create class schedules for attendance.</p>
                </div>
                <a href="add_schedule.php" class="btn-primary" style="width: auto; text-decoration: none;">+ Add Schedule</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>SUBJECT CODE</th>
                            <th>SUBJECT NAME</th>
                            <th class="text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($schedules as $sched): ?>
                        <tr>
                            <td class="font-medium" style="color: var(--primary-magenta);"><i class="fa-regular fa-calendar" style="margin-right: 8px;"></i> <?php echo date('m/d/Y', strtotime($sched['class_date'])); ?></td>
                            <td style="color: var(--primary-magenta); font-weight: 500;"><?php echo htmlspecialchars($sched['code']); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($sched['name']); ?></td>
                            <td class="actions-cell text-right">
                                <a href="edit_schedule.php?id=<?php echo $sched['id']; ?>" class="action-btn edit-btn"><i class="fa-solid fa-pen"></i></a>
                                <a href="delete_schedule.php?id=<?php echo $sched['id']; ?>" class="action-btn delete-btn" onclick="return confirm('delete this schedule?');"><i class="fa-regular fa-trash-can"></i></a>
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