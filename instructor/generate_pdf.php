<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Instructor') {
    die("access denied.");
}

if (!isset($_GET['schedule_id']) || empty($_GET['schedule_id'])) {
    die("please select a schedule.");
}

$schedule_id = $_GET['schedule_id'];
$records = [];
$info = [];

try {
    $info_stmt = $pdo->prepare("SELECT schedules.class_date, subjects.name, subjects.code FROM schedules JOIN subjects ON schedules.subject_id = subjects.id WHERE schedules.id = :id");
    $info_stmt->bindParam(':id', $schedule_id);
    $info_stmt->execute();
    $info = $info_stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT users.name, users.email, attendance.status, attendance.time_logged FROM attendance JOIN users ON attendance.student_id = users.id WHERE attendance.schedule_id = :sched_id ORDER BY users.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sched_id', $schedule_id);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("database error");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - <?php echo htmlspecialchars($info['code']); ?></title>
    <style>
        body { 
            font-family: "Times New Roman", Times, serif; 
            color: #000; 
            margin: 40px auto; 
            max-width: 900px;
            line-height: 1.5;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }

        .divider {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 20px 0;
        }

        .info-table {
            width: 100%;
            border: none;
            margin-bottom: 30px;
            table-layout: auto;
        }
        .info-table td {
            border: none;
            padding: 8px 10px;
            font-size: 14px;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            white-space: nowrap; 
            width: 1%; 
        }

        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .data-table th, .data-table td { 
            border: 1px solid #000; 
            padding: 12px 10px; 
            text-align: left; 
            font-size: 14px;
        }
        .data-table th { 
            background-color: #f0f0f0; 
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .data-table th {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="report-header">
        <h1>Official Attendance Report</h1>
        <h2>Instructor Copy</h2>
    </div>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td class="info-label">Subject Name:</td>
            <td style="width: 50%;"><?php echo htmlspecialchars($info['name']); ?></td>
            <td class="info-label text-right">Class Date:</td>
            <td><?php echo date('F d, Y', strtotime($info['class_date'])); ?></td>
        </tr>
        <tr>
            <td class="info-label">Subject Code:</td>
            <td><?php echo htmlspecialchars($info['code']); ?></td>
            <td class="info-label text-right">Report Generated:</td>
            <td><?php echo date('F d, Y'); ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 45%;">Student Name</th>
                <th style="width: 25%;">Status</th>
                <th style="width: 30%;">Time Logged</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($records)): ?>
                <tr>
                    <td colspan="3" class="text-center" style="padding: 20px;">
                        <em>No attendance records found.</em>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($records as $rec): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rec['name']); ?></td>
                        <td class="bold"><?php echo htmlspecialchars($rec['status']); ?></td>
                        <td><?php echo $rec['time_logged'] ? date('m/d/Y h:i A', strtotime($rec['time_logged'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; font-size: 14px;">
        <p>___________________________________</p>
        <p style="margin-right: 40px;"><strong>Instructor Signature</strong></p>
    </div>

</body>
</html>