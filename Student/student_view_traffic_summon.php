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
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: student_traffic_summon_list.php");
    exit();
}

$summon_id = intval($_GET['id']);
$userId = $_SESSION['User_id'];

/* ===== FETCH SUMMON AND VEHICLE DETAILS ===== */
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
        v.Vehicle_color
    FROM trafficsummon ts
    JOIN vehicle v ON ts.Vehicle_regNo = v.Vehicle_regNo
    WHERE ts.Summon_id = ? AND ts.User_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $summon_id, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Traffic summon not found.'); window.location.href='student_traffic_summon_list.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Traffic Summon</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f2f6fc;
    margin: 0;
}

/* ---------- FLEX CONTAINER ---------- */
.content-wrapper {
    display: flex;
    justify-content: center;
    padding: 30px 20px;
    margin-left: 250px; /* sidebar width */
    box-sizing: border-box;
}

/* ---------- CARD ---------- */
.card {
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    max-width: 700px;
    width: 100%;
}

.card h2 {
    color: #1565c0;
    margin-bottom: 20px;
}

/* ---------- SECTION TITLE ---------- */
.section-title {
    font-weight: 600;
    color: #1565c0;
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 18px;
    border-bottom: 1px solid #1565c0;
    padding-bottom: 3px;
}

/* ---------- DETAIL ROW ---------- */
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e0e0e0;
}

.detail-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #1565c0;
}

.value {
    color: #555;
}

/* ---------- BADGE ---------- */
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #ffeb3b;
    color: #333;
    font-weight: 600;
}

.badge.Unpaid { 
    background-color: #f44336; 
    color: #fff; 
}

.badge.Paid { 
    background-color: #4caf50; 
    color: #fff; 
}

.badge.Overdue { 
    background-color: #ff9800; 
    color: #fff; 
}


/* ---------- BUTTONS BELOW CARD ---------- */
.button-group {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.btn-back, .btn-print {
    display: inline-block;
    padding: 10px 20px;
    background-color: #1565c0;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    margin: 0 5px;
    transition: 0.2s;
}

.btn-back:hover, .btn-print:hover {
    background-color: #0d3c75;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* ---------- RESPONSIVE ---------- */
@media screen and (max-width: 800px) {
    .content-wrapper {
        margin-left: 0;
        padding: 20px 10px;
    }
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .value {
        margin-top: 5px;
    }
}
</style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">

  <div class="logo-container">
    <img src="../uploads/fkparkLogo.jpg" class="logo">
  </div>

  <button class="button active" onclick="location.href='student_dashboard.php'">
    <i class="fas fa-home"></i> Dashboard
  </button>

<button class="dropdown-btn" id="vehicleBtn">
  <span class="icon-text">
    <i class="fas fa-car"></i>
    <span>Vehicle</span>
  </span>
  <span class="dropdown-arrow">&#9654;</span>
</button>

 <div class="dropdown-containers" id="vehicleMenu">
    <a href="student_vec_reg.php">
      <i class="fas fa-plus-circle"></i> Vehicle Registration
    </a>

  <a href="student_application_stat.php">
    <i class="fas fa-clipboard-check"></i> Application Status
  </a>

  <a href="student_profile.php">
    <i class="fas fa-user"></i> User Profile
  </a>
</div>

   <!-- Parking Spaces -->
  <button class="dropdown-btn" id="psBtn">
    <span class="icon-text">
    <i class="fas fa-parking"></i> 
    <span>Parking Spaces</span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="psMenu">
    <a href="student_parking_availability.php">
      <i class="fas fa-list"></i> Parking Availability
    </a>

    </div>
    

  <!-- Booking -->
  <button class="dropdown-btn" id="psBtn">
    <span class="icon-text">
    <i class="fas fa-parking"></i> 
    <span>Booking</span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="psMenu">
    <a href="student_search_parking.php">
      <i class="fas fa-list"></i> Make Booking
    </a>
    <a href="student_bookings.php">
      <i class="fas fa-car-side"></i> My Bookings
    </a>
    </div>

  <!-- Summon -->
  <button class="dropdown-btn" id="tsBtn">
    <span class="icon-text">
      <i class="fas fa-receipt"></i>
      <span>Traffic Summon</span>
    </span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="tsMenu">
    <a href="student_traffic_summon_list.php">
      <i class="fas fa-list"></i> My Summons
    </a>
  </div>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="content-wrapper">

    <div style="display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 700px;">

        <!-- ===== CARD ===== -->
        <div class="card">
            <h2><i class="fas fa-file-invoice"></i> Traffic Summon Details</h2>

            <!-- SUMMON INFO -->
            <div class="section-title">Summon Information</div>
            <div class="detail-row"><div class="label">ID:</div><div class="value"><?= $row['Summon_id'] ?></div></div>
            <div class="detail-row"><div class="label">Date:</div><div class="value"><?= date("d/m/Y", strtotime($row['Summon_issueDate'])) ?></div></div>
            <div class="detail-row"><div class="label">Time:</div><div class="value"><?= htmlspecialchars($row['Summon_issueTime']) ?></div></div>
            <div class="detail-row"><div class="label">Violation:</div><div class="value"><?= htmlspecialchars($row['Summon_violationType']) ?></div></div>
            <div class="detail-row"><div class="label">Points:</div><div class="value"><span class="badge"><?= $row['Summon_point'] ?> pts</span></div></div>
            <div class="detail-row"><div class="label">Description:</div><div class="value"><?= htmlspecialchars($row['Summon_description']) ?></div></div>
            <div class="detail-row"><div class="label">Status:</div><div class="value"><span class="badge <?= $row['Summon_status'] ?>"><?= $row['Summon_status'] ?></span></div></div>
            <!-- VEHICLE INFO -->
            <div class="section-title">Vehicle Information</div>
            <div class="detail-row"><div class="label">Registration:</div><div class="value"><?= htmlspecialchars($row['Vehicle_regNo']) ?></div></div>
            <div class="detail-row"><div class="label">Type:</div><div class="value"><?= htmlspecialchars($row['Vehicle_type']) ?></div></div>
            <div class="detail-row"><div class="label">Brand:</div><div class="value"><?= htmlspecialchars($row['Vehicle_brand']) ?></div></div>
            <div class="detail-row"><div class="label">Color:</div><div class="value"><?= htmlspecialchars($row['Vehicle_color']) ?></div></div>
        </div>

        <!-- ===== BUTTONS BELOW CARD ===== -->
        <div class="button-group">
            <a href="student_traffic_summon_list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="student_print_traffic_summon.php?id=<?= $row['Summon_id'] ?>" target="_blank" class="btn-print"><i class="fas fa-print"></i> Print</a>
        </div>

    </div>

</div>

<script>
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");
        const menu = this.nextElementSibling;
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });
});
</script>

</body>
</html>
