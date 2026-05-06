<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$my_subjects = [];
$attendance_history = [];

$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';

try {
    $sub_sql = "SELECT subjects.id, subjects.code, subjects.name FROM enrollments JOIN subjects ON enrollments.subject_id = subjects.id WHERE enrollments.student_id = :student_id ORDER BY subjects.name ASC";
    $sub_stmt = $pdo->prepare($sub_sql);
    $sub_stmt->bindParam(':student_id', $student_id);
    $sub_stmt->execute();
    $my_subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($selected_subject != '') {
        $history_sql = "SELECT schedules.class_date, attendance.status, attendance.time_logged FROM attendance JOIN schedules ON attendance.schedule_id = schedules.id WHERE attendance.student_id = :student_id AND schedules.subject_id = :subject_id ORDER BY schedules.class_date DESC";
        $history_stmt = $pdo->prepare($history_sql);
        $history_stmt->bindParam(':student_id', $student_id);
        $history_stmt->bindParam(':subject_id', $selected_subject);
        $history_stmt->execute();
        $attendance_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    die("database error loading history.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance - Student Panel</title>
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
                    <li><a href="overview.php"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="qr_code.php"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
                    <li><a href="my_attendance.php" class="active"><i class="fa-solid fa-clipboard-list"></i> My Attendance</a></li>
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
                <h1>Attendance History</h1>
                <p>View your past attendance records by subject.</p>
            </div>

            <div class="login-card" style="max-width: 100%; margin: 0; margin-bottom: 30px;">
                <form action="my_attendance.php" method="GET" style="display: flex; gap: 20px;">
                    
                    <div class="input-group" style="flex: 1; margin-bottom: 0;">
                        <label>Select Subject</label>
                        <select name="subject_id" onchange="this.form.submit()" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a subject to view history...</option>
                            <?php foreach($my_subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>" <?php if($selected_subject == $sub['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                </form>
            </div>

            <?php if($selected_subject != ''): ?>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>STATUS</th>
                            <th>TIME RECORDED</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php if(empty($attendance_history)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">you have no attendance records for this subject yet.</td>
                        </tr>
                        
                        <?php else: ?>
                        
                            <?php foreach($attendance_history as $record): ?>
                            <tr>
                                <td class="font-medium" style="color: var(--text-dark);">
                                    <i class="fa-regular fa-calendar" style="margin-right: 8px; color: var(--text-muted);"></i>
                                    <?php echo date('F d, Y', strtotime($record['class_date'])); ?>
                                </td>
                                
                                <td>
                                    <span class="badge" style="background-color: <?php echo ($record['status'] == 'Present') ? '#dcfce7' : (($record['status'] == 'Absent') ? '#fee2e2' : '#fef3c7'); ?>; color: <?php echo ($record['status'] == 'Present') ? '#16a34a' : (($record['status'] == 'Absent') ? '#ef4444' : '#f59e0b'); ?>;">
                                        <?php echo htmlspecialchars($record['status']); ?>
                                    </span>
                                </td>
                                
                                <td class="text-muted">
                                    <i class="fa-regular fa-clock" style="margin-right: 5px;"></i>
                                    <?php echo $record['time_logged'] ? date('h:i A', strtotime($record['time_logged'])) : '-'; ?>
                                </td>
                                
                            </tr>
                            <?php endforeach; ?>
                            
                        <?php endif; ?>
                        
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>

        </main>
    </div>
</body>
</html>