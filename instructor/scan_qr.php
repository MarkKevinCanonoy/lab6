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

$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$selected_schedule = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $scanned_student_id = $_POST['student_id'];
    $post_schedule_id = $_POST['schedule_id'];
    $post_status = $_POST['status'];

    try {
        $check_sql = "SELECT id FROM attendance WHERE schedule_id = :sched_id AND student_id = :student_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':sched_id', $post_schedule_id);
        $check_stmt->bindParam(':student_id', $scanned_student_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $scan_msg = "student already marked for this class.";
            $msg_color = "#ef4444";
        } else {
            $insert_sql = "INSERT INTO attendance (schedule_id, student_id, status) VALUES (:sched_id, :student_id, :status)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':sched_id', $post_schedule_id);
            $insert_stmt->bindParam(':student_id', $scanned_student_id);
            $insert_stmt->bindParam(':status', $post_status);
            $insert_stmt->execute();
            
            $scan_msg = "student attendance saved successfully!";
            $msg_color = "#16a34a";
        }
        
        $selected_schedule = $post_schedule_id;
        $selected_subject = $_POST['subject_id'];
        
    } catch (PDOException $e) {
        $scan_msg = "database error saving attendance.";
        $msg_color = "#ef4444";
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
} catch (PDOException $e) {
    // stop if it breaks
    die("database error loading filters.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scan QR - Instructor Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
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
                    <li><a href="scan_qr.php" class="active"><i class="fa-solid fa-qrcode"></i> Scan QR</a></li>
                    <li><a href="attendance.php"><i class="fa-solid fa-clipboard-user"></i> Attendance</a></li>
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
                <h1>QR Code Scanner</h1>
                <p>Select your class and scan student QR codes.</p>
            </div>

            <?php if(isset($scan_msg)): ?>
                <div style="background-color: #f3f4f6; color: <?php echo $msg_color; ?>; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo $scan_msg; ?>
                </div>
            <?php endif; ?>

            <div class="login-card" style="max-width: 100%; margin: 0; margin-bottom: 30px;">
                <form action="scan_qr.php" method="GET" style="display: flex; gap: 20px;">
                    
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
            
            <div class="login-card" style="max-width: 600px; margin: 0 auto;">
                
                <form id="scan_form" action="scan_qr.php" method="POST">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                    <input type="hidden" name="schedule_id" value="<?php echo $selected_schedule; ?>">
                    <input type="hidden" name="student_id" id="scanned_student_id" value="">
                    
                    <div class="input-group" style="margin-bottom: 20px;">
                        <label>Mark Status As</label>
                        <select name="status" id="status_dropdown" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px; font-weight: bold; color: #16a34a;">
                            <option value="Present">Present</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                </form>

                <div id="qr-reader" style="width: 100%; border: 2px dashed #ccc; border-radius: 8px; overflow: hidden;"></div>
                
                <script>
                    window.onload = function() {
                        
                        function onScanSuccess(decodedText, decodedResult) {
                            html5QrcodeScanner.clear();
                            
                            document.getElementById('scanned_student_id').value = decodedText;
                            
                            document.getElementById('scan_form').submit();
                        }
                        
                        function onScanFailure(error) {
                        }

                        let html5QrcodeScanner = new Html5QrcodeScanner(
                            "qr-reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
                        
                        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                        
                        document.getElementById('status_dropdown').addEventListener('change', function() {
                            if(this.value == 'Present') {
                                this.style.color = '#16a34a';
                            } else {
                                this.style.color = '#f59e0b';
                            }
                        });
                    };
                </script>
                
            </div>
            
            <?php endif; ?>

        </main>
    </div>
</body>
</html>