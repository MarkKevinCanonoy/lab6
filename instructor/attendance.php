<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Instructor') {
    header("Location: ../index.php");
    exit();
}

$instructor_id = $_SESSION['user_id'];
$subjects = [];
$schedules = [];
$students = [];

$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$selected_schedule = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $target_student = $_POST['student_id'];
    $target_schedule = $_POST['schedule_id'];
    $new_status = $_POST['status'];
    
    try {
        $check_sql = "SELECT id FROM attendance WHERE schedule_id = :sched_id AND student_id = :student_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':sched_id', $target_schedule);
        $check_stmt->bindParam(':student_id', $target_student);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $update_sql = "UPDATE attendance SET status = :status, time_logged = CURRENT_TIMESTAMP WHERE schedule_id = :sched_id AND student_id = :student_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':status', $new_status);
            $update_stmt->bindParam(':sched_id', $target_schedule);
            $update_stmt->bindParam(':student_id', $target_student);
            $update_stmt->execute();
        } else {
            $insert_sql = "INSERT INTO attendance (schedule_id, student_id, status) VALUES (:sched_id, :student_id, :status)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':sched_id', $target_schedule);
            $insert_stmt->bindParam(':student_id', $target_student);
            $insert_stmt->bindParam(':status', $new_status);
            $insert_stmt->execute();
        }
        
        $success_msg = "attendance updated.";
        
    } catch (PDOException $e) {
        $error_msg = "failed to update attendance.";
    }
}

try {
    $sub_sql = "SELECT id, name, code FROM subjects WHERE instructor_id = :inst_id ORDER BY name ASC";
    $sub_stmt = $pdo->prepare($sub_sql);
    $sub_stmt->bindParam(':inst_id', $instructor_id);
    $sub_stmt->execute();
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($selected_subject != '') {
        $sched_sql = "SELECT id, class_date FROM schedules WHERE subject_id = :sub_id ORDER BY class_date DESC";
        $sched_stmt = $pdo->prepare($sched_sql);
        $sched_stmt->bindParam(':sub_id', $selected_subject);
        $sched_stmt->execute();
        $schedules = $sched_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($selected_subject != '' && $selected_schedule != '') {
        $student_sql = "SELECT users.id AS student_id, users.name AS student_name, attendance.status, attendance.time_logged FROM enrollments JOIN users ON enrollments.student_id = users.id LEFT JOIN attendance ON attendance.student_id = users.id AND attendance.schedule_id = :sched_id WHERE enrollments.subject_id = :sub_id ORDER BY users.name ASC";
        $student_stmt = $pdo->prepare($student_sql);
        $student_stmt->bindParam(':sched_id', $selected_schedule);
        $student_stmt->bindParam(':sub_id', $selected_subject);
        $student_stmt->execute();
        $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    die("database error loading data.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Attendance - Instructor Panel</title>
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
                    <li><a href="overview.php"><i class="fa-solid fa-border-all"></i> Overview</a></li>
                    <li><a href="scan_qr.php"><i class="fa-solid fa-qrcode"></i> Scan QR</a></li>
                    <li><a href="attendance.php" class="active"><i class="fa-solid fa-clipboard-user"></i> Attendance</a></li>
                    <li><a href="reports.php"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar" style="background-color: #fce7f3; color: var(--primary-magenta);"><?php echo substr($_SESSION['name'], 0, 1); ?></div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </div>
                <a href="../api/logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            
            <div class="page-header">
                <h1>Manage Attendance</h1>
                <p>View and update student attendance records.</p>
            </div>

            <?php if(isset($success_msg)): ?>
                <div style="background-color: #dcfce7; color: #16a34a; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_msg)): ?>
                <div style="background-color: #fee2e2; color: #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="login-card" style="max-width: 100%; margin: 0; margin-bottom: 30px;">
                <form action="attendance.php" method="GET" style="display: flex; gap: 20px;">
                    
                    <div class="input-group" style="flex: 1;">
                        <label>Select Subject</label>
                        <select name="subject_id" onchange="this.form.submit()" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a subject...</option>
                            <?php foreach($subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>" <?php if($selected_subject == $sub['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="input-group" style="flex: 1;">
                        <label>Select Schedule Date</label>
                        <select name="schedule_id" onchange="this.form.submit()" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a date...</option>
                            <?php foreach($schedules as $sched): ?>
                                <option value="<?php echo $sched['id']; ?>" <?php if($selected_schedule == $sched['id']) echo 'selected'; ?>>
                                    <?php echo date('F d, Y', strtotime($sched['class_date'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                </form>
            </div>

            <?php if($selected_subject != '' && $selected_schedule != ''): ?>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>STUDENT NAME</th>
                            <th>CURRENT STATUS</th>
                            <th>TIME LOGGED</th>
                            <th class="text-right">UPDATE ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php if(empty($students)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">no students enrolled in this subject yet.</td>
                        </tr>
                        
                        <?php else: ?>
                        
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td class="font-medium"><?php echo htmlspecialchars($student['student_name']); ?></td>
                                
                                <td>
                                    <?php if(empty($student['status'])): ?>
                                        <span class="badge" style="background-color: #f3f4f6; color: #4b5563;">Not Marked</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: <?php echo ($student['status'] == 'Present') ? '#dcfce7' : (($student['status'] == 'Absent') ? '#fee2e2' : '#fef3c7'); ?>; color: <?php echo ($student['status'] == 'Present') ? '#16a34a' : (($student['status'] == 'Absent') ? '#ef4444' : '#f59e0b'); ?>;">
                                            <?php echo htmlspecialchars($student['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-muted"><?php echo $student['time_logged'] ? date('h:i A', strtotime($student['time_logged'])) : '-'; ?></td>
                                
                                <td class="text-right">
                                    <form action="attendance.php?subject_id=<?php echo $selected_subject; ?>&schedule_id=<?php echo $selected_schedule; ?>" method="POST" style="margin: 0; display: inline-block;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                        <input type="hidden" name="schedule_id" value="<?php echo $selected_schedule; ?>">
                                        
                                        <select name="status" onchange="this.form.submit()" style="padding: 6px; border: 1px solid var(--border-color); border-radius: 4px; font-size: 13px;">
                                            <option value="">Change...</option>
                                            <option value="Present">Present</option>
                                            <option value="Late">Late</option>
                                            <option value="Absent">Absent</option>
                                        </select>
                                    </form>
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