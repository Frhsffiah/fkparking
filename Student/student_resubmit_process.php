<?php
session_start();
include("../php/config.php");

if (!isset($_SESSION['User_id'])) {
    exit();
}

$file = $_FILES['vehicle_document'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['pdf','jpg','jpeg','png'])) {
    die("Invalid file type");
}

$newName = uniqid().".".$ext;
move_uploaded_file($file['tmp_name'], "../uploads/".$newName);

$hash = hash_file("sha256", "../uploads/".$newName);

$stmt = $conn->prepare("
  UPDATE vehicle
  SET Document_name=?, Document_hash=?, Document_status='Pending'
  WHERE User_id=?
");
$stmt->bind_param("ssi", $newName, $hash, $_SESSION['User_id']);
$stmt->execute();

header("Location: student_application_stat.php");
exit();
