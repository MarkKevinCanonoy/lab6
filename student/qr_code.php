<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My QR Code - Student Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
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
                    <li><a href="qr_code.php" class="active"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
                    <li><a href="my_attendance.php"><i class="fa-solid fa-clipboard-list"></i> My Attendance</a></li>
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
                <h1>My QR Code</h1>
                <p>Show this to your instructor to mark your attendance.</p>
            </div>

            <div class="login-card" style="max-width: 400px; margin: 0 auto; text-align: center;">
                
                <h2 style="color: var(--text-dark); margin-bottom: 5px;"><?php echo htmlspecialchars($student_name); ?></h2>
                <p style="color: var(--text-muted); margin-bottom: 25px; font-size: 14px;">Student ID: <?php echo $student_id; ?></p>
                
                <canvas id="qr-code" style="margin-bottom: 20px; border: 10px solid #fdf8fa; border-radius: 8px;"></canvas>
                
                <input type="hidden" id="student_id_value" value="<?php echo $student_id; ?>">
                
                <button onclick="downloadQR()" class="btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-download"></i> Download QR Code
                </button>
                
            </div>

            <script>
                var myStudentId = document.getElementById('student_id_value').value;
                
                var qr = new QRious({
                    element: document.getElementById('qr-code'),
                    value: myStudentId,
                    size: 250,
                    background: 'white',
                    foreground: '#e6007e'
                });

                function downloadQR() {
                    var canvas = document.getElementById('qr-code');
                    var image = canvas.toDataURL("image/png");
                    
                    var link = document.createElement('a');
                    link.download = 'My_Attendance_QR.png';
                    link.href = image;
                    link.click();
                }
            </script>

        </main>
    </div>
</body>
</html>