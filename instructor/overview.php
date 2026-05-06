<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Instructor') {
    header("Location: ../index.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];
$my_subjects = [];
$total_my_subjects = 0;

try {
    $sql = "SELECT id, code, name FROM subjects WHERE instructor_id = :instructor_id ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':instructor_id', $instructor_id);
    $stmt->execute();
    $my_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_my_subjects = count($my_subjects);
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - Attendance System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 style="color: var(--primary-magenta);">Instructor Panel</h2>
                <p>Attendance System</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="overview.php" class="active"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="scan_qr.php"><i class="fa-solid fa-qrcode"></i> Scan QR</a></li>
                    <li><a href="attendance.php"><i class="fa-solid fa-clipboard-user"></i> Attendance</a></li>
                    <li><a href="reports.php"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar" style="background-color: #fce7f3; color: var(--primary-magenta);"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <span class="user-email">smith@test.com</span>
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Instructor Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
            </div>

            <div style="margin-bottom: 30px; width: 300px;">
                <div class="stat-card">
                    <div class="stat-icon blue-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-details">
                        <p>My Subjects</p>
                        <h3><?php echo $total_my_subjects; ?></h3>
                    </div>
                </div>
            </div>

            <h3 style="margin-bottom: 20px; color: var(--text-dark);">Your Subjects</h3>

            <div class="stats-grid">
                
                <?php if(empty($my_subjects)): ?>
                    <p style="color: var(--text-muted);">You have no subjects assigned yet.</p>
                <?php else: ?>
                    
                    <?php foreach($my_subjects as $subject): ?>
                    <div class="stat-card" style="display: flex; flex-direction: column; align-items: flex-start;">
                        
                        <div class="stat-icon" style="background-color: #fce7f3; color: var(--primary-magenta); margin-bottom: 15px;"><i class="fa-solid fa-book-open"></i></div>
                        
                        <h3 style="font-size: 16px; margin-bottom: 5px;"><?php echo htmlspecialchars($subject['name']); ?></h3>
                        <p style="color: var(--primary-magenta); font-weight: bold; font-size: 14px;"><?php echo htmlspecialchars($subject['code']); ?></p>
                        
                    </div>
                    <?php endforeach; ?>
                    
                <?php endif; ?>
                
            </div>
            
        </main>
    </div>
</body>
</html>