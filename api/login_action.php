<?php
session_start();
require_once '../db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: ../index.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'Admin':
                    header("Location: ../admin/overview.php");
                    break;
                case 'Instructor':
                    header("Location: ../instructor/overview.php");
                    break;
                case 'Student':
                    header("Location: ../student/overview.php");
                    break;
                default:
                    $_SESSION['login_error'] = "Invalid role assigned.";
                    header("Location: ../index.php");
                    break;
            }
            exit();

        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: ../index.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['login_error'] = "System error. Please try again later.";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>