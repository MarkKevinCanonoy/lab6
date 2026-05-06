<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$my_stats = [];

try {
    $sub_sql = "SELECT subjects.id, subjects.code, subjects.name FROM enrollments JOIN subjects ON enrollments.subject_id = subjects.id WHERE enrollments.student_id = :student_id ORDER BY subjects.name ASC";
    $sub_stmt = $pdo->prepare($sub_sql);
    $sub_stmt->bindParam(':student_id', $student_id);
    $sub_stmt->execute();
    $enrolled_subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($enrolled_subjects as $subject) {
        
        $present_count = 0;
        $late_count = 0;
        $absent_count = 0;
        
        $pres_sql = "SELECT COUNT(*) FROM attendance JOIN schedules ON attendance.schedule_id = schedules.id WHERE attendance.student_id = :student_id AND schedules.subject_id = :subject_id AND attendance.status = 'Present'";
        $pres_stmt = $pdo->prepare($pres_sql);
        $pres_stmt->bindParam(':student_id', $student_id);
        $pres_stmt->bindParam(':subject_id', $subject['id']);
        $pres_stmt->execute();
        $present_count = $pres_stmt->fetchColumn();
        
        $late_sql = "SELECT COUNT(*) FROM attendance JOIN schedules ON attendance.schedule_id = schedules.id WHERE attendance.student_id = :student_id AND schedules.subject_id = :subject_id AND attendance.status = 'Late'";
        $late_stmt = $pdo->prepare($late_sql);
        $late_stmt->bindParam(':student_id', $student_id);
        $late_stmt->bindParam(':subject_id', $subject['id']);
        $late_stmt->execute();
        $late_count = $late_stmt->fetchColumn();
        
        $abs_sql = "SELECT COUNT(*) FROM attendance JOIN schedules ON attendance.schedule_id = schedules.id WHERE attendance.student_id = :student_id AND schedules.subject_id = :subject_id AND attendance.status = 'Absent'";
        $abs_stmt = $pdo->prepare($abs_sql);
        $abs_stmt->bindParam(':student_id', $student_id);
        $abs_stmt->bindParam(':subject_id', $subject['id']);
        $abs_stmt->execute();
        $absent_count = $abs_stmt->fetchColumn();
        
        $total_classes = $present_count + $late_count + $absent_count;
        
        $attendance_rate = 0;
        
        if ($total_classes > 0) {
            $attendance_rate = round((($present_count + $late_count) / $total_classes) * 100);
        }
        
        $my_stats[] = [
            'subject_name' => $subject['name'],
            'subject_code' => $subject['code'],
            'present' => $present_count,
            'late' => $late_count,
            'absent' => $absent_count,
            'rate' => $attendance_rate
        ];
    }
    
} catch (PDOException $e) {
    die("database error loading stats.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Attendance System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 style="color: #16a34a;">Student Panel</h2>
                <p>Attendance System</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="overview.php" class="active"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="qr_code.php"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
                    <li><a href="my_attendance.php"><i class="fa-solid fa-clipboard-list"></i> My Attendance</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar" style="background-color: #dcfce7; color: #16a34a;"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>My Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
            </div>

            <?php if(empty($my_stats)): ?>
                <p style="color: var(--text-muted);">You are not enrolled in any subjects yet.</p>
            <?php else: ?>
                
                <div class="stats-grid">
                    
                    <?php foreach($my_stats as $stat): ?>
                    
                    <div class="stat-card" style="display: flex; flex-direction: column; align-items: flex-start; min-width: 250px;">
                        
                        <h3 style="color: #16a34a; font-size: 18px; margin-bottom: 5px;"><?php echo htmlspecialchars($stat['subject_code']); ?></h3>
                        <p style="font-weight: 500; color: var(--text-dark); margin-bottom: 15px;"><?php echo htmlspecialchars($stat['subject_name']); ?></p>
                        
                        <div style="width: 100%; text-align: center; margin-bottom: 20px;">
                            <h2 style="font-size: 36px; color: var(--text-dark);"><?php echo $stat['rate']; ?>%</h2>
                            <p style="font-size: 12px; color: var(--text-muted);">Attendance Rate</p>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; width: 100%; border-top: 1px solid var(--border-color); padding-top: 15px;">
                            
                            <div style="text-align: center;">
                                <p style="font-weight: bold; color: #16a34a;"><?php echo $stat['present']; ?></p>
                                <p style="font-size: 11px; color: var(--text-muted);">Present</p>
                            </div>
                            
                            <div style="text-align: center;">
                                <p style="font-weight: bold; color: #f59e0b;"><?php echo $stat['late']; ?></p>
                                <p style="font-size: 11px; color: var(--text-muted);">Late</p>
                            </div>
                            
                            <div style="text-align: center;">
                                <p style="font-weight: bold; color: #ef4444;"><?php echo $stat['absent']; ?></p>
                                <p style="font-size: 11px; color: var(--text-muted);">Absent</p>
                            </div>
                            
                        </div>
                        
                        <?php if($stat['absent'] == 4): ?>
                            <div style="width: 100%; background-color: #fef3c7; color: #b45309; text-align: center; padding: 8px; font-size: 12px; font-weight: bold; border-radius: 4px; margin-top: 15px;">
                                Warning: You have 4 absences!
                            </div>
                        <?php elseif($stat['absent'] >= 5): ?>
                            <div style="width: 100%; background-color: #fee2e2; color: #ef4444; text-align: center; padding: 8px; font-size: 12px; font-weight: bold; border-radius: 4px; margin-top: 15px;">
                                Limit Reached: 5 Absences!
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <?php endforeach; ?>
                    
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>