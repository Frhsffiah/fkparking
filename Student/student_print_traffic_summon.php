<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== GET SUMMON ID ===== */
if (!isset($_GET['id'])) {
    die("Summon ID not provided.");
}

$summon_id = intval($_GET['id']);
$userId = $_SESSION['User_id'];

/* ===== FETCH SUMMON DETAILS ===== */
$stmt = $conn->prepare("
    SELECT 
        ts.Summon_id,
        ts.Summon_issueDate,
        ts.Summon_issueTime,
        ts.Summon_violationType,
        ts.Summon_point,
        ts.Summon_description,
        v.Vehicle_regNo,
        v.Vehicle_type,
        v.Vehicle_brand,
        v.Vehicle_color,
        s.Stud_firstname,
        s.Stud_lastname,
        s.Stud_email,
        s.Stud_phoneNum,
        s.Stud_matricNum
    FROM trafficsummon ts
    JOIN vehicle v ON ts.Vehicle_regNo = v.Vehicle_regNo
    JOIN student s ON ts.User_id = s.User_id
    WHERE ts.Summon_id = ? AND ts.User_id = ?
");
$stmt->bind_param("ii", $summon_id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Summon not found or you do not have permission to view it.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Traffic Summon</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    margin: 0;
    padding: 20px;
}
.container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
h2 {
    color: #1565c0;
    margin-bottom: 25px;
    text-align: center;
}
.section {
    margin-bottom: 20px;
}
.section h3 {
    border-bottom: 2px solid #1565c0;
    padding-bottom: 5px;
    color: #1565c0;
    margin-bottom: 10px;
}
.section p {
    margin: 6px 0;
}
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #ffeb3b;
    color: #333;
    font-weight: 600;
}
.btn-print {
    display: block;
    width: 180px;
    margin: 25px auto 0;
    padding: 10px;
    background: #1565c0;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}
.btn-print:hover {
    background: #0d3c75;
}
@media print {
    .btn-print { display: none; }
    body { background: #fff; padding: 0; }
}
</style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-file-invoice"></i> Traffic Summon</h2>

    <div class="section">
        <h3>Summon Details</h3>
        <p><strong>ID:</strong> <?= $row['Summon_id'] ?></p>
        <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($row['Summon_issueDate'])) ?></p>
        <p><strong>Time:</strong> <?= date('H:i', strtotime($row['Summon_issueTime'])) ?></p>
        <p><strong>Violation:</strong> <?= htmlspecialchars($row['Summon_violationType']) ?></p>
        <p><strong>Points:</strong> <span class="badge"><?= $row['Summon_point'] ?> pts</span></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($row['Summon_description']) ?></p>
    </div>

    <div class="section">
        <h3>Vehicle Details</h3>
        <p><strong>Vehicle No:</strong> <?= htmlspecialchars($row['Vehicle_regNo']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($row['Vehicle_type']) ?></p>
        <p><strong>Brand:</strong> <?= htmlspecialchars($row['Vehicle_brand']) ?></p>
        <p><strong>Color:</strong> <?= htmlspecialchars($row['Vehicle_color']) ?></p>
    </div>

    <div class="section">
        <h3>Student Details</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($row['Stud_firstname'] . ' ' . $row['Stud_lastname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($row['Stud_email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($row['Stud_phoneNum']) ?></p>
        <p><strong>Matric Number:</strong> <?= htmlspecialchars($row['Stud_matricNum']) ?></p>
    </div>

    <button class="btn-print" onclick="window.print();">
        <i class="fas fa-print"></i> Print Summon
    </button>
</div>

</body>
</html>
