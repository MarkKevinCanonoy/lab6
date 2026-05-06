<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$subjects = [];

try {
    $sql = "SELECT subjects.id, subjects.code, subjects.name, users.name AS instructor_name FROM subjects LEFT JOIN users ON subjects.instructor_id = users.id ORDER BY subjects.name ASC";
    $stmt = $pdo->query($sql);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects - Admin Panel</title>
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
                    <li><a href="subjects.php" class="active"><i class="fa-solid fa-book-open"></i> Subjects</a></li>
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
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header header-with-action">
                <div>
                    <h1>Manage Subjects</h1>
                    <p>Add, edit, or delete courses.</p>
                </div>
                <a href="add_subject.php" class="btn-primary" style="width: auto; text-decoration: none;">+ Add Subject</a>
            </div>

            <div class="stats-grid">
                <?php foreach($subjects as $subject): ?>
                
                <div class="stat-card" style="display: flex; flex-direction: column; align-items: flex-start; position: relative;">
                    
                    <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 10px;">
                        <a href="edit_subject.php?id=<?php echo $subject['id']; ?>" class="edit-btn"><i class="fa-solid fa-pen"></i></a>
                        <a href="delete_subject.php?id=<?php echo $subject['id']; ?>" class="delete-btn" onclick="return confirm('delete this subject?');"><i class="fa-regular fa-trash-can"></i></a>
                    </div>

                    <div class="stat-icon purple-icon" style="margin-bottom: 15px;"><i class="fa-solid fa-book-open"></i></div>
                    
                    <h3 style="font-size: 16px; margin-bottom: 5px;"><?php echo htmlspecialchars($subject['name']); ?></h3>
                    <p style="color: var(--primary-magenta); font-weight: bold; margin-bottom: 15px;"><?php echo htmlspecialchars($subject['code']); ?></p>
                    
                    <div style="width: 100%; border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: auto;">
                        <p style="font-size: 12px; color: var(--text-muted);">Instructor: <span style="color: var(--text-dark); font-weight: 500;"><?php echo $subject['instructor_name'] ? htmlspecialchars($subject['instructor_name']) : 'Unassigned'; ?></span></p>
                    </div>
                    
                </div>
                
                <?php endforeach; ?>
            </div>
            
        </main>
    </div>
</body>
</html>