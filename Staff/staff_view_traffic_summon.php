<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
        ts.Summon_id, 
        ts.Summon_issueDate, 
        ts.Summon_issueTime,
        ts.Summon_violationType, 
        ts.Summon_description, 
        ts.Summon_point,
        ts.Summon_status,
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

/* ===== AUTO MARK OVERDUE ===== */
$issueDate = strtotime($row['Summon_issueDate']);
if ($row['Summon_status'] == 'Unpaid' && (time() - $issueDate) > 3*24*60*60) {
    $update = $conn->prepare("UPDATE trafficsummon SET Summon_status='Overdue' WHERE Summon_id=?");
    $update->bind_param("i", $summon_id);
    $update->execute();
    $row['Summon_status'] = 'Overdue';
}
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
    font-family: Arial, sans-serif; 
    background:#f0f2f5; margin:0; 
    padding:0; 
}

.content-wrapper { 
    display:flex; 
    justify-content:center; 
    padding:40px; 
    margin-left:250px; 
}

.card { 
    background:#fff; 
    padding:30px 40px; 
    border-radius:15px; 
    box-shadow:0 8px 25px rgba(0,0,0,0.1); 
    max-width:800px; 
    width:100%; 
}

.card h2 { 
    color:#1b5e20; 
    margin-bottom:20px; 
    font-size:24px; 
}

.section-title { 
    font-weight:600; 
    color:#1b5e20; 
    margin-top:20px; 
    margin-bottom:10px; 
    font-size:18px; 
    border-bottom:1px solid #1b5e20; 
    padding-bottom:4px; 
}

.detail-row { 
    display:flex; 
    justify-content:space-between; 
    padding:10px 0; 
    border-bottom:1px solid #e0e0e0; 
}

.detail-row:last-child { 
    border-bottom:none; 
}

.label { 
    font-weight:600; 
    color:#1b5e20; 
    font-size:16px; 
}

.value { 
    color:#555; 
    font-size:16px; 
}

.badge { 
    display:inline-block; 
    padding:5px 12px; 
    border-radius:20px; 
    color:#fff; 
    font-weight:600; 
}

.badge.Unpaid { 
    background-color:#f44336; 
}

.badge.Paid { 
    background-color:#4caf50; 
}

.badge.Overdue { 
    background-color:#ff9800; 
}

.button-group { 
    text-align:center; 
    margin-top:20px; 
}

.btn-back, .btn-print { 
    display:inline-block; 
    padding:10px 20px; 
    background:#1b5e20; 
    color:#fff; 
    text-decoration:none; 
    border-radius:8px; 
    font-weight:600; 
    margin:0 5px; 
}

.btn-back:hover, .btn-print:hover { 
    background:#145214; 
    box-shadow:0 5px 15px rgba(0,0,0,0.2); 
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

        <!-- Dashboard -->
        <a href="staff_dashboard.php" class="button active">
            <i class="fas fa-home"></i> Dashboard
        </a>

        <!-- User & Vehicle Registration -->
        <button class="dropdown-btn" id="uvBtn">
            <span>
                <i class="fas fa-users"></i> User & Vehicle Registration
            </span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>

        <div class="dropdown-containers" id="uvMenu">
            <a href="staff_approve_vec.php">
                <i class="fas fa-car"></i> Vehicle Approval
            </a>
            <a href="staff_profile.php">
                <i class="fas fa-user"></i> User Profiles
            </a>
        </div>

        <!-- Parking -->
         <button class="dropdown-btn" id="psBtn">
            <span>
                <i class="fas fa-parking"></i> Parking Spaces
            </span>
            <span class="dropdown-arrow">&#9654;</span>
         </button>
            <div class="dropdown-containers" id="psMenu">
                <a href="staff_parking_availability.php">
                    <i class="fas fa-list"></i> Parking Availability
                </a>
            </div>

        <a onclick="location.href='staff_bookings.php'">
            <i class="fas fa-list"></i> Bookings
        </a>

        <!-- Traffic Summon -->
        <button class="dropdown-btn" id="tsBtn">
            <span>
                <i class="fas fa-file-invoice"></i> Traffic Summon
            </span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>

        <div class="dropdown-containers" id="tsMenu">
            <a href="staff_traffic_summon_list.php">
                <i class="fas fa-list"></i> Summon List
            </a>
            <a href="staff_add_traffic_summon.php">
                <i class="fas fa-plus-circle"></i> Add Summon
            </a>
        </div>
    </div>

    <!-- Logout -->
    <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>
</div>

<div class="content-wrapper">
    <div>
        <div class="card">
            <h2><i class="fas fa-file-invoice"></i> Traffic Summon Details</h2>

            <div class="section-title">Summon Information</div>
            <div class="detail-row"><div class="label">ID:</div><div class="value"><?= $row['Summon_id'] ?></div></div>
            <div class="detail-row"><div class="label">Date:</div><div class="value"><?= date("d/m/Y", strtotime($row['Summon_issueDate'])) ?></div></div>
            <div class="detail-row"><div class="label">Time:</div><div class="value"><?= htmlspecialchars($row['Summon_issueTime']) ?></div></div>
            <div class="detail-row"><div class="label">Violation:</div><div class="value"><?= htmlspecialchars($row['Summon_violationType']) ?></div></div>
            <div class="detail-row"><div class="label">Points:</div><div class="value"><?= $row['Summon_point'] ?> pts</div></div>
            <div class="detail-row"><div class="label">Description:</div><div class="value"><?= htmlspecialchars($row['Summon_description']) ?></div></div>
            <div class="detail-row"><div class="label">Status:</div><div class="value"><span class="badge <?= $row['Summon_status'] ?>"><?= $row['Summon_status'] ?></span></div></div>

            <div class="section-title">Vehicle Information</div>
            <div class="detail-row"><div class="label">Registration:</div><div class="value"><?= htmlspecialchars($row['Vehicle_regNo']) ?></div></div>
            <div class="detail-row"><div class="label">Type:</div><div class="value"><?= htmlspecialchars($row['Vehicle_type']) ?></div></div>
            <div class="detail-row"><div class="label">Brand:</div><div class="value"><?= htmlspecialchars($row['Vehicle_brand']) ?></div></div>
            <div class="detail-row"><div class="label">Color:</div><div class="value"><?= htmlspecialchars($row['Vehicle_color']) ?></div></div>

            <div class="section-title">Student Information</div>
            <div class="detail-row"><div class="label">Name:</div><div class="value"><?= htmlspecialchars($row['Stud_firstname'].' '.$row['Stud_lastname']) ?></div></div>
            <div class="detail-row"><div class="label">Email:</div><div class="value"><?= htmlspecialchars($row['Stud_email']) ?></div></div>
            <div class="detail-row"><div class="label">Phone:</div><div class="value"><?= htmlspecialchars($row['Stud_phoneNum']) ?></div></div>
            <div class="detail-row"><div class="label">Matric:</div><div class="value"><?= htmlspecialchars($row['Stud_matricNum']) ?></div></div>

            <div class="button-group">
                <a href="staff_traffic_summon_list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
                <a href="staff_print_traffic_summon.php?id=<?= $row['Summon_id'] ?>" target="_blank" class="btn-print"><i class="fas fa-print"></i> Print Summon</a>
            </div>
        </div>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
document.querySelectorAll(".dropdown-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");

        let dropdown = this.nextElementSibling;
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    });
});
</script>
</body>
</html>
