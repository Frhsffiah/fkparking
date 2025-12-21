<?php
session_start();
include '../php/config.php';

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== VALIDATE ID ===== */
if (!isset($_GET['id'])) {
    header("Location: admin_view_reg.php");
    exit();
}

$stud_id = $_GET['id'];

/* ===== GET User_id FROM student ===== */
$stmt = $conn->prepare("
    SELECT User_id 
    FROM student 
    WHERE Stud_id = ?
");
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Student not found
    header("Location: admin_view_reg.php");
    exit();
}

$student = $result->fetch_assoc();
$user_id = $student['User_id'];

/* ===== DELETE FROM student ===== */
$stmt = $conn->prepare("DELETE FROM student WHERE Stud_id = ?");
$stmt->bind_param("i", $stud_id);
$stmt->execute();

/* ===== DELETE FROM user ===== */
$stmt = $conn->prepare("DELETE FROM user WHERE User_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

/* ===== REDIRECT BACK ===== */
header("Location: admin_view_reg.php");
exit();
