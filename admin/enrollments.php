<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $del_stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = :id");
    $del_stmt->bindParam(':id', $del_id);
    $del_stmt->execute();
    header("Location: enrollments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];

    if (empty($student_id) || empty($subject_id)) {
        $error_msg = "please select both a student and a subject.";
    } else {
        $check_sql = "SELECT id FROM enrollments WHERE student_id = :stud_id AND subject_id = :subj_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':stud_id', $student_id);
        $check_stmt->bindParam(':subj_id', $subject_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error_msg = "that student is already enrolled in that subject.";
        } else {
            $insert_sql = "INSERT INTO enrollments (student_id, subject_id) VALUES (:stud_id, :subj_id)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':stud_id', $student_id);
            $insert_stmt->bindParam(':subj_id', $subject_id);
            $insert_stmt->execute();
            $success_msg = "student successfully enrolled.";
        }
    }
}

$students = [];
$stud_stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'Student' ORDER BY name ASC");
$students = $stud_stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = [];
$subj_stmt = $pdo->query("SELECT id, name, code FROM subjects ORDER BY name ASC");
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

$enrollments = [];
$enroll_sql = "SELECT enrollments.id, users.name AS student_name, subjects.code, subjects.name AS subject_name FROM enrollments JOIN users ON enrollments.student_id = users.id JOIN subjects ON enrollments.subject_id = subjects.id ORDER BY subjects.name ASC, users.name ASC";
$enroll_stmt = $pdo->query($enroll_sql);
$enrollments = $enroll_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollments - Admin Panel</title>
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
                    <li><a href="enrollments.php" class="active"><i class="fa-solid fa-user-plus"></i> Enrollments</a></li>
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
            <div class="page-header">
                <h1>Manage Enrollments</h1>
                <p>Assign students to subjects.</p>
            </div>

            <?php if(isset($error_msg)): ?>
                <div class="error-msg"><?php echo $error_msg; ?></div>
            <?php elseif(isset($success_msg)): ?>
                <div style="background-color: #dcfce7; color: #16a34a; padding: 10px; border-radius: 6px; text-align: center; margin-bottom: 20px;">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <div class="login-card" style="max-width: 100%; margin: 0; margin-bottom: 30px;">
                <h3 style="color: var(--primary-magenta); margin-bottom: 20px; font-size: 16px;">Enroll a Student</h3>
                
                <form action="enrollments.php" method="POST" style="display: flex; align-items: flex-end; gap: 20px;">
                    
                    <div class="input-group" style="flex: 1; margin-bottom: 0;">
                        <label>Select Student</label>
                        <select name="student_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a student...</option>
                            <?php foreach($students as $stud): ?>
                                <option value="<?php echo $stud['id']; ?>"><?php echo htmlspecialchars($stud['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="input-group" style="flex: 1; margin-bottom: 0;">
                        <label>Select Subject</label>
                        <select name="subject_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="">Choose a subject...</option>
                            <?php foreach($subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: auto;">Enroll</button>
                    
                </form>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>SUBJECT CODE</th>
                            <th>SUBJECT NAME</th>
                            <th>STUDENT NAME</th>
                            <th class="text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($enrollments as $enroll): ?>
                        <tr>
                            <td class="font-medium" style="color: var(--primary-magenta);"><?php echo htmlspecialchars($enroll['code']); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($enroll['subject_name']); ?></td>
                            <td class="font-medium"><?php echo htmlspecialchars($enroll['student_name']); ?></td>
                            <td class="actions-cell text-right">

                                    <a href="edit_enrollment.php?id=<?php echo $enroll['id']; ?>" class="action-btn edit-btn" style="margin-right: 10px;">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                <a href="enrollments.php?delete_id=<?php echo $enroll['id']; ?>" class="action-btn delete-btn" onclick="return confirm('remove student from this class?');">
                                    <i class="fa-regular fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($enrollments)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">no students are enrolled in any subjects yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</body>
</html>