<?php
session_start();
require '../db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $message = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO notifications (subject_id, title, message, sent_by) VALUES (?, ?, ?, 'Admin')");
    $stmt->execute([$subject_id, $title, $message]);

    $emailQuery = $pdo->prepare("SELECT users.email FROM users JOIN enrollments ON users.id = enrollments.student_id WHERE enrollments.subject_id = ?");
    $emailQuery->execute([$subject_id]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kevinixmarkix@gmail.com';
        $mail->Password = 'eaih uypt mucu rapu'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('kevinixmarkix@gmail.com', 'Instructor');
        $mail->isHTML(true);
        $mail->Subject = $title;
        
        $emailBody = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #333; border-radius: 8px; overflow: hidden; background-color: #1a1a1a; color: #eeeeee;">
            <div style="background-color: #ff69b4; padding: 20px; text-align: center;">
                <h2 style="color: #121212; margin: 0; font-size: 24px;">New Class Announcement</h2>
            </div>
            <div style="padding: 30px; background-color: #2a2a2a;">
                <h3 style="color: #ff69b4; margin-top: 0; font-size: 20px;">' . htmlspecialchars($title) . '</h3>
                <p style="font-size: 16px; line-height: 1.6; color: #cccccc; margin-bottom: 0;">' . nl2br(htmlspecialchars($message)) . '</p>
            </div>
            <div style="padding: 15px; text-align: center; background-color: #1a1a1a; border-top: 1px solid #333;">
                <p style="margin: 0; font-size: 12px; color: #777;">This is an automated message from the Attendance System. Please do not reply directly to this email.</p>
            </div>
        </div>';

        $mail->Body = $emailBody;

        $hasRecipients = false;
        while ($row = $emailQuery->fetch(PDO::FETCH_ASSOC)) {
            $mail->addBCC($row['email']);
            $hasRecipients = true;
        }

        if ($hasRecipients) {
            $mail->send();
            echo "<script>alert('Announcement sent and logged');</script>";
        } else {
            echo "<script>alert('Announcement logged, but no students are enrolled in this class to receive the email.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Message failed. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
}

$subjects = $pdo->query("SELECT * FROM subjects"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Announcement</title>
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
                    <li><a href="send_announcement.php" class="active"><i class="fa-solid fa-bullhorn"></i> Send Announcement</a></li>
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
                <a href="../api/logout.php" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <h1>Send Announcement</h1>
            </header>
            <div class="announcement-container">
                <form method="POST" action="" class="announcement-form">
                    <label>Select Subject</label>
                    <select name="subject_id" required>
                        <option value="">-- Choose a class --</option>
                        <?php while($sub = $subjects->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $sub['id'] ?>"><?= $sub['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    
                    <label>Title</label>
                    <input type="text" name="title" placeholder="e.g. Practical Exam" required>
                    
                    <label>Message</label>
                    <textarea name="message" rows="6" required></textarea>
                    
                    <button type="submit" class="btn-pink">Send Notification</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>