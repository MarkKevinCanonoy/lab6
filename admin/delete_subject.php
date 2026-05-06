<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $target_id = $_GET['id'];
    
    $sql = "DELETE FROM subjects WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':id', $target_id);
    
    $stmt->execute();
}

header("Location: subjects.php");
exit();
?>