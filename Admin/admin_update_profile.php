<?php
session_start();
include '../php/config.php';

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];

    $stmt = $conn->prepare("
        UPDATE admins
        SET 
            Admin_firstname = ?,
            Admin_lastname  = ?,
            Admin_email     = ?,
            Admin_phoneNum  = ?
        WHERE User_id = ?
    ");

    $stmt->bind_param(
        "ssssi",
        $firstname,
        $lastname,
        $email,
        $phone,
        $_SESSION['User_id']
    );

    if ($stmt->execute()) {
        // ✅ IMPORTANT: redirect WITH success flag
        header("Location: admin_edit_profile.php?updated=success");
        exit();
    } else {
        echo "Update failed.";
    }
}
