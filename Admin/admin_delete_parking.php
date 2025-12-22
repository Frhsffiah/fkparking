<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== CHECK PARKING ID ===== */
if (!isset($_GET['id'])) {
    header("Location: admin_list_parking.php?message=" . urlencode("Invalid parking ID."));
    exit();
}

$PS_id = $_GET['id'];

/* ===== DELETE PARKING ===== */
$sql = "DELETE FROM parkingspace WHERE PS_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $PS_id);

if ($stmt->execute()) {
    $message = "Parking area deleted successfully.";
} else {
    $message = "Failed to delete parking area.";
}

$stmt->close();
$conn->close();

/* ===== REDIRECT BACK ===== */
header("Location: admin_list_parking.php?message=" . urlencode($message));
exit();
