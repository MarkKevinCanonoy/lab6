<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $schedule_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT id, subject_id, class_date FROM schedules WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $schedule_id);
    $stmt->execute();
    $current_schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_schedule) {
        header("Location: schedules.php");
        exit();
    }
} else {
    header("Location: schedules.php");
    exit();
}

$subjects = [];
try {
    $sub_stmt = $pdo->query("SELECT id, code, name FROM subjects ORDER BY name ASC");
    $subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("error finding subjects.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_id = $_POST['id'];
    $new_subject_id = $_POST['subject_id'];
    $new_class_date = $_POST['class_date'];

    if (empty(trim($new_subject_id)) || empty(trim($new_class_date))) {
        $error_msg = "Stop! You must pick a subject and a date.";
    } else {
        try {
            $update_sql = "UPDATE schedules SET subject_id = :subject_id, class_date = :class_date WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            
            $update_stmt->bindParam(':subject_id', $new_subject_id);
            $update_stmt->bindParam(':class_date', $new_class_date);
            $update_stmt->bindParam(':id', $update_id);

            if ($update_stmt->execute()) {
                header("Location: schedules.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "failed to update schedule.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh;">
    
    <div class="login-card">
        <h2 style="margin-bottom: 20px;">Edit Schedule</h2>
        
        <?php if(isset($error_msg)): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $current_schedule['id']; ?>">
            
            <div class="input-group">
                <label>Select Subject</label>
                <select name="subject_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="">-- Choose a Subject --</option>
                    
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>" <?php if($current_schedule['subject_id'] == $sub['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    
                </select>
            </div>
            
            <div class="input-group">
                <label>Class Date</label>
                <?php $today = date('Y-m-d'); ?>
                <input type="date" name="class_date" value="<?php echo htmlspecialchars($current_schedule['class_date']); ?>" min="<?php echo $today; ?>" required>
            </div>
            
            <button type="submit" class="btn-primary">Update Schedule</button>
            
            <a href="schedules.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6b7280; font-size: 14px;">Cancel</a>
            
        </form>
    </div>

</body>
</html>