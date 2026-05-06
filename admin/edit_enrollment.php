<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $enrollment_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT id, student_id, subject_id FROM enrollments WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $enrollment_id);
    $stmt->execute();
    $current_enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_enrollment) {
        header("Location: enrollments.php");
        exit();
    }
} else {
    header("Location: enrollments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_id = $_POST['id'];
    $new_student_id = $_POST['student_id'];
    $new_subject_id = $_POST['subject_id'];

    $check_sql = "SELECT id FROM enrollments WHERE student_id = :stud_id AND subject_id = :subj_id AND id != :current_id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':stud_id', $new_student_id);
    $check_stmt->bindParam(':subj_id', $new_subject_id);
    $check_stmt->bindParam(':current_id', $update_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $error_msg = "that student is already enrolled in that subject.";
    } else {
        $update_sql = "UPDATE enrollments SET student_id = :stud_id, subject_id = :subj_id WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->bindParam(':stud_id', $new_student_id);
        $update_stmt->bindParam(':subj_id', $new_subject_id);
        $update_stmt->bindParam(':id', $update_id);

        if ($update_stmt->execute()) {
            header("Location: enrollments.php");
            exit();
        } else {
            $error_msg = "failed to update enrollment.";
        }
    }
}

$students = [];
$stud_stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'Student' ORDER BY name ASC");
$students = $stud_stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = [];
$subj_stmt = $pdo->query("SELECT id, name, code FROM subjects ORDER BY name ASC");
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Enrollment - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh;">
    
    <div class="login-card" style="width: 100%; max-width: 500px;">
        <h2 style="margin-bottom: 20px;">Edit Enrollment</h2>
        
        <?php if(isset($error_msg)): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $current_enrollment['id']; ?>">
            
            <div class="input-group">
                <label>Student</label>
                <select name="student_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <?php foreach($students as $stud): ?>
                        <option value="<?php echo $stud['id']; ?>" <?php if($current_enrollment['student_id'] == $stud['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($stud['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="input-group">
                <label>Subject</label>
                <select name="subject_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>" <?php if($current_enrollment['subject_id'] == $sub['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Update Enrollment</button>
            
            <a href="enrollments.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6b7280; font-size: 14px;">Cancel</a>
            
        </form>
    </div>

</body>
</html>