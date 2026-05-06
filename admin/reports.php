<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$subjects = [];
$schedules = [];
$attendance_list = [];

$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$selected_schedule = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : '';

try {
    $sub_stmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC");
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($selected_subject != '') {
        $sched_sql = "SELECT schedules.id, schedules.class_date, subjects.name FROM schedules LEFT JOIN subjects ON schedules.subject_id = subjects.id WHERE schedules.subject_id = :sub_id ORDER BY schedules.class_date DESC";
        $sched_stmt = $pdo->prepare($sched_sql);
        $sched_stmt->bindParam(':sub_id', $selected_subject);
        $sched_stmt->execute();
        $schedules = $sched_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sched_stmt = $pdo->query("SELECT schedules.id, schedules.class_date, subjects.name FROM schedules LEFT JOIN subjects ON schedules.subject_id = subjects.id ORDER BY schedules.class_date DESC");
        $schedules = $sched_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($selected_schedule != '') {
        $att_sql = "SELECT users.name, users.email, attendance.status, attendance.time_logged FROM attendance JOIN users ON attendance.student_id = users.id WHERE attendance.schedule_id = :sched_id ORDER BY users.name ASC";
        $att_stmt = $pdo->prepare($att_sql);
        $att_stmt->bindParam(':sched_id', $selected_schedule);
        $att_stmt->execute();
        $attendance_list = $att_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("database error");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin Panel</title>
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
                    <li><a href="schedules.php"><i class="fa-regular fa-calendar"></i> Schedules</a></li>
                    <li><a href="reports.php" class="active"><i class="fa-regular fa-file-lines"></i> Reports</a></li>
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
                    <h1>Attendance Reports</h1>
                    <p>Generate PDF reports for classes.</p>
                </div>
                
                <button type="submit" form="reportForm" formaction="generate_pdf.php" formtarget="_blank" class="btn-primary" style="background-color: #e5e7eb; color: #4b5563; width: auto; border: 1px solid #d1d5db;">
                    <i class="fa-solid fa-download"></i> Generate PDF
                </button>
            </div>

            <div class="login-card" style="max-width: 100%; margin: 0; margin-bottom: 30px;">
                <h3 style="color: var(--primary-magenta); margin-bottom: 20px; font-size: 16px;">
                    <i class="fa-solid fa-filter"></i> Filters
                </h3>
                
                <form id="reportForm" action="reports.php" method="GET" style="display: flex; gap: 20px;">
                    
                    <div class="input-group" style="flex: 1;">
                        <label>Select Subject</label>
                        <select name="subject_id" onchange="this.form.submit()" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">All Subjects</option>
                            <?php foreach($subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>" <?php if($selected_subject == $sub['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($sub['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="input-group" style="flex: 1;">
                        <label>Select Schedule</label>
                        <select name="schedule_id" onchange="this.form.submit()" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a date...</option>
                            <?php foreach($schedules as $sched): ?>
                                <option value="<?php echo $sched['id']; ?>" <?php if($selected_schedule == $sched['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($sched['name']); ?> - <?php echo date('m/d/Y', strtotime($sched['class_date'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                </form>
            </div>

            <?php if($selected_schedule != ''): ?>
            
            <h3 style="margin-bottom: 15px; color: var(--text-dark);">Live Preview</h3>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>STUDENT NAME</th>
                            <th>STATUS</th>
                            <th>TIME LOGGED</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php if(empty($attendance_list)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">no attendance yet</td>
                        </tr>
                        
                        <?php else: ?>
                        
                            <?php foreach($attendance_list as $att): ?>
                            <tr>
                                <td class="font-medium"><?php echo htmlspecialchars($att['name']); ?></td>
                                <td>
                                    <span style="font-weight: 600; color: <?php echo ($att['status'] == 'Present') ? '#16a34a' : (($att['status'] == 'Absent') ? '#ef4444' : '#f59e0b'); ?>">
                                        <?php echo htmlspecialchars($att['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo $att['time_logged'] ? date('h:i A', strtotime($att['time_logged'])) : '-'; ?></td>
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