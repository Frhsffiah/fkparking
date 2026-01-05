<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== GET SUMMON ID ===== */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: staff_traffic_summon_list.php");
    exit();
}

$summon_id = intval($_GET['id']);

/* ===== FETCH SUMMON, VEHICLE, STUDENT DETAILS ===== */
$stmt = $conn->prepare("
    SELECT 
        ts.Summon_id, ts.Summon_issueDate, ts.Summon_issueTime,
        ts.Summon_violationType, ts.Summon_description, ts.Summon_point,
        v.Vehicle_regNo, v.Vehicle_type, v.Vehicle_brand, v.Vehicle_color,
        s.Stud_firstname, s.Stud_lastname, s.Stud_email, s.Stud_phoneNum, s.Stud_matricNum
    FROM trafficsummon ts
    JOIN vehicle v ON ts.Vehicle_regNo = v.Vehicle_regNo
    JOIN student s ON ts.User_id = s.User_id
    WHERE ts.Summon_id = ?
");
$stmt->bind_param("i", $summon_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Traffic summon not found.'); window.location.href='staff_traffic_summon_list.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Traffic Summon</title>

<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
}

/* ---------- FLEX CONTAINER FOR CENTER ---------- */
.content-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 20px;
    margin-left: 250px; /* sidebar width */
    box-sizing: border-box;
}

/* ---------- BUTTONS ---------- */
.btn-back, .btn-print {
    display: inline-block;
    padding: 10px 20px;
    background-color: #1b5e20;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.2s;
}

.btn-back:hover, .btn-print:hover {
    background-color: #145214;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* ---------- BUTTON CONTAINER FOR CENTER ---------- */
.button-group {
    text-align: center;
    margin-top: 20px;
}

.button-group a {
    margin: 0 10px;
}

/* ---------- SINGLE CARD ---------- */
.card {
    background: #fff;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    max-width: 800px;
    width: 100%;
}

.card h2 {
    color: #1b5e20;
    margin-bottom: 20px;
    font-size: 24px;
}

/* ---------- SECTION TITLE ---------- */
.section-title {
    font-weight: 600;
    color: #1b5e20;
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 18px;
    border-bottom: 1px solid #1b5e20;
    padding-bottom: 4px;
}

/* ---------- DETAIL ROW ---------- */
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e0e0e0;
}

.detail-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #1b5e20;
    font-size: 16px;
}

.value {
    color: #555;
    font-size: 16px;
}

/* ---------- BADGE ---------- */
.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    background: #ffeb3b;
    color: #333;
    font-weight: 600;
}

/* ---------- RESPONSIVE ---------- */
@media screen and (max-width: 850px) {
    .content-wrapper {
        margin-left: 0;
        padding: 20px 10px;
    }
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .value {
        text-align: left;
        margin-top: 5px;
    }
}
</style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">
    <div>
        <div class="logo-container">
            <img src="../uploads/fkparkLogo.jpg" class="logo">
        </div>

        <a href="staff_dashboard.php" class="button">
            <i class="fas fa-home"></i> Dashboard
        </a>

        <!-- Traffic Summon -->
        <button class="dropdown-btn active">
            <span><i class="fas fa-file-invoice"></i> Traffic Summon</span>
            <span class="dropdown-arrow">&#9660;</span>
        </button>
        <div class="dropdown-containers" style="display:block;">
            <a href="staff_traffic_summon_list.php">
                <i class="fas fa-list"></i> Summon List
            </a>
            <a href="staff_add_traffic_summon.php">
                <i class="fas fa-plus-circle"></i> Add Summon
            </a>
        </div>
    </div>

    <button class="button" onclick="location.href='../public/logout_page.php'">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="content-wrapper">

    <div>
        <!-- ===== CARD ===== -->
        <div class="card">
            <h2><i class="fas fa-file-invoice"></i> Traffic Summon Details</h2>

            <!-- ===== SUMMON SECTION ===== -->
            <div class="section-title">Summon Information</div>
            <div class="detail-row"><div class="label">ID:</div><div class="value"><?= $row['Summon_id'] ?></div></div>
            <div class="detail-row"><div class="label">Date:</div><div class="value"><?= date("d/m/Y", strtotime($row['Summon_issueDate'])) ?></div></div>
            <div class="detail-row"><div class="label">Time:</div><div class="value"><?= htmlspecialchars($row['Summon_issueTime']) ?></div></div>
            <div class="detail-row"><div class="label">Violation:</div><div class="value"><?= htmlspecialchars($row['Summon_violationType']) ?></div></div>
            <div class="detail-row"><div class="label">Points:</div><div class="value"><span class="badge"><?= $row['Summon_point'] ?> pts</span></div></div>
            <div class="detail-row"><div class="label">Description:</div><div class="value"><?= htmlspecialchars($row['Summon_description']) ?></div></div>

            <!-- ===== VEHICLE SECTION ===== -->
            <div class="section-title">Vehicle Information</div>
            <div class="detail-row"><div class="label">Registration:</div><div class="value"><?= htmlspecialchars($row['Vehicle_regNo']) ?></div></div>
            <div class="detail-row"><div class="label">Type:</div><div class="value"><?= htmlspecialchars($row['Vehicle_type']) ?></div></div>
            <div class="detail-row"><div class="label">Brand:</div><div class="value"><?= htmlspecialchars($row['Vehicle_brand']) ?></div></div>
            <div class="detail-row"><div class="label">Color:</div><div class="value"><?= htmlspecialchars($row['Vehicle_color']) ?></div></div>

            <!-- ===== STUDENT SECTION ===== -->
            <div class="section-title">Student Information</div>
            <div class="detail-row"><div class="label">Name:</div><div class="value"><?= htmlspecialchars($row['Stud_firstname'] . ' ' . $row['Stud_lastname']) ?></div></div>
            <div class="detail-row"><div class="label">Email:</div><div class="value"><?= htmlspecialchars($row['Stud_email']) ?></div></div>
            <div class="detail-row"><div class="label">Phone Number:</div><div class="value"><?= htmlspecialchars($row['Stud_phoneNum']) ?></div></div>
            <div class="detail-row"><div class="label">Matric Number:</div><div class="value"><?= htmlspecialchars($row['Stud_matricNum']) ?></div></div>
        </div>

        <!-- ===== BUTTON GROUP BELOW CARD ===== -->
        <div class="button-group">
            <a href="staff_traffic_summon_list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
            <a href="staff_print_traffic_summon.php?id=<?= $row['Summon_id'] ?>" target="_blank" class="btn-print">
                <i class="fas fa-print"></i> Print Summon
            </a>
        </div>

    </div>
</div>

<!-- ================= DROPDOWN SCRIPT ================= -->
<script>
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");
        let menu = this.nextElementSibling;
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });
});
</script>

</body>
</html>
