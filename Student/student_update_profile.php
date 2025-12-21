<?php
session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== UPDATE PROFILE ===== */
$stmt = $conn->prepare("
    UPDATE student
    SET Stud_firstname = ?, Stud_lastname = ?, Stud_email = ?,
        Stud_course = ?, Stud_level = ?, Stud_phoneNum = ?
    WHERE User_id = ?
");

$stmt->bind_param(
    "ssssssi",
    $_POST['firstname'],
    $_POST['lastname'],
    $_POST['email'],
    $_POST['course'],
    $_POST['level'],
    $_POST['phone'],
    $_SESSION['User_id']
);

$stmt->execute();

/* ===== REDIRECT WITH SUCCESS FLAG ===== */
header("Location: student_profile.php?updated=success");
exit();
