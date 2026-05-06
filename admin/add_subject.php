<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

$instructors = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'Instructor' ORDER BY name ASC");
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("error finding instructors.");
}

$predefined_subjects = [
    "GEN ED 002" => "Understanding the Self",
    "GEN ED 001" => "Purposive Communication",
    "IT 113" => "Introduction to Computing",
    "PATHFIT 112" => "Movement Competency Training",
    "MATH ENHANCE 1" => "College Algebra and Trigonometry",
    "IT 134" => "Computer Programming 1",
    "GEN ED 009" => "The Entrepreneurial Mind",
    "NSTP 113" => "CWTS, LTS, MTS(Naval or Air Force)",
    "DRR 113" => "Disaster Risk Reduction and Education in Emergencies",
    "GEN ED 004" => "Mathematics in the Modern world",
    "IT 163" => "Computer Programming 2",
    "PATHFIT 122" => "Fitness Training",
    "IT 143" => "Discrete Mathematics",
    "NSTP 123" => "CWTS, LTS, MTS (Naval or Air Force)",
    "GEN. ED. 007" => "The Contemporary World",
    "GEN. ED. 010" => "Living in the IT Era",
    "GEN. ED. 006" => "Ethics",
    "IT 123" => "Introduction to Human Computer Interaction",
    "GEN. ED. 003" => "Readings in Philippine History",
    "IT 273" => "Web Systems and Technologies 1",
    "CCNA 213" => "Introduction to Networks",
    "RIZAL 001" => "Rizals, Life, and Works",
    "IT 253" => "Platform Technologies",
    "IT 233" => "Object Oriented Programming",
    "IT213" => "Data Structures and Algorithms",
    "PATHFIT 212" => "Dance, Sport Group Exercise Outdoor & Adventure Actvties",
    "IT 293" => "Statistics and Probability",
    "GEN. ED. 005" => "Art Appreciation",
    "CCNA 223" => "Routing and Switchng Essentials",
    "GEN. ED. 011" => "Technical Writing",
    "IT 223" => "Information Management",
    "GEN. ED. 08" => "Science, Technology, and Society",
    "IT 243" => "Quantitative Methods",
    "PATHFIT 222" => "Dance, Sport Group Exercise Outdoor & Adventure Actvties",
    "IT 263" => "Integrative Programming and Technologies 1",
    "IT 373A" => "Event-Driven Programming (*)",
    "IT 313" => "Advanced Database Systems (*)",
    "IT 353" => "Data Mining and Analytics",
    "IT 353A" => "Systems Integration and Architecture 1 (*)",
    "IT 333" => "Systems Analysis and Design (*)",
    "CCNA 313" => "Scaling Networks (*)",
    "IT 393" => "Social and Professional Issues",
    "IT 373" => "Web Systems and Technology 2 (*)",
    "IT 363A" => "Application Development and Emerging Technologies (*)",
    "IT 363" => "Information Assurance and Security 1 (*)",
    "IT 343A" => "IT Electives",
    "IT 383A" => "Systems Integration and Architecture 2 (*)",
    "IT 383" => "Integrative Programming and Technologies 2 (*)",
    "IT 343" => "Multimedia Systems (*)",
    "IT 323" => "Software Engineering",
    "CCNA 323" => "Connecting Networks (*)",
    "IT 303" => "Information Assurance and Security 2",
    "IT 303A" => "Capstone project and research 1",
    "IT 413" => "System Administration and Maintenance",
    "IT 433" => "Capstone Project and Research 2",
    "IT 429" => "Practicum"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_data = $_POST['course_data'];
    $instructor_id = $_POST['instructor_id'];

    if (empty(trim($course_data)) || empty(trim($instructor_id))) {
        $error_msg = "Stop! You must pick a subject and an instructor.";
    } else {
        $parts = explode('||', $course_data);
        $code = $parts[0];
        $name = $parts[1];

        try {
            $sql = "INSERT INTO subjects (code, name, instructor_id) VALUES (:code, :name, :instructor_id)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':instructor_id', $instructor_id);

            if ($stmt->execute()) {
                header("Location: subjects.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "failed to save. maybe that subject code already exists?";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Subject - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .select2-container .select2-selection--single {
            height: 45px !important;
            padding: 8px 12px;
            border: 1px solid var(--border-color, #d1d5db) !important;
            border-radius: 6px !important;
            background-color: #fff;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 27px !important;
            padding-left: 0 !important;
            color: #374151 !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
            right: 10px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #e6007e !important;
        }
    </style>
</head>
<body style="background-color: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh;">
    
    <div class="login-card" style="width: 100%; max-width: 700px;">
        <h2 style="margin-bottom: 20px;">Add Subject</h2>
        
        <?php if(isset($error_msg)): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            
            <div class="input-group">
                <label>Select Course</label>
                <select name="course_data" class="searchable" required style="width: 100%;">
                    <option value="">-- Choose a Subject from the List --</option>
                    
                    <?php foreach($predefined_subjects as $sub_code => $sub_name): ?>
                        <option value="<?php echo htmlspecialchars($sub_code . '||' . $sub_name); ?>">
                            <?php echo htmlspecialchars($sub_code . ' - ' . $sub_name); ?>
                        </option>
                    <?php endforeach; ?>
                    
                </select>
            </div>
            
            <div class="input-group" style="margin-top: 15px;">
                <label>Assign Instructor</label>
                <select name="instructor_id" class="searchable" required style="width: 100%;">
                    <option value="">-- No Instructor Assigned --</option>
                    
                    <?php foreach($instructors as $inst): ?>
                        <option value="<?php echo $inst['id']; ?>">
                            <?php echo htmlspecialchars($inst['name']); ?>
                        </option>
                    <?php endforeach; ?>
                    
                </select>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 20px;">Save Subject</button>
            
            <a href="subjects.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #6b7280; font-size: 14px;">Cancel</a>
            
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.searchable').select2({
                placeholder: "Type to search...",
                allowClear: true
            });
        });
    </script>
</body>
</html>