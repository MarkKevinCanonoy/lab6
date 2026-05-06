<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$subjects = [];
try {
    $stmt = $pdo->query("SELECT id, code, name FROM subjects ORDER BY name ASC");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("error finding subjects.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $class_date = $_POST['class_date'];
    
    $today = date("Y-m-d");

    if (empty(trim($subject_id)) || empty(trim($class_date))) {
        $error_msg = "Stop! You must pick a subject and a date.";
    } elseif ($class_date < $today) {
        $error_msg = "Stop! You cannot select a date in the past.";
    } else {
        try {
            $sql = "INSERT INTO schedules (subject_id, class_date) VALUES (:subject_id, :class_date)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':subject_id', $subject_id);
            $stmt->bindParam(':class_date', $class_date);

            if ($stmt->execute()) {
                header("Location: schedules.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "failed to save schedule. database error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Schedule - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh;">
    
    <div class="login-card">
        <h2 style="margin-bottom: 20px;">Add New Schedule</h2>
        
        <?php if(isset($error_msg)): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            
            <div class="input-group">
                <label>Select Subject</label>
                <select name="subject_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="">-- Choose a Subject --</option>
                    
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?php echo $sub['id']; ?>">
                            <?php echo htmlspecialchars($sub['code'] . ' - ' . $sub['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    
                </select>
            </div>
            
            <div class="input-group">
                <label>Class Date</label>
                <input type="date" name="class_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <button type="submit" class="btn-primary">Save Schedule</button>
            
            <a href="schedules.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6b7280; font-size: 14px;">Cancel</a>
            
        </form>
    </div>

</body>
</html>