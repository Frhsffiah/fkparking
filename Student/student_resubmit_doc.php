<?php
session_start();
include("../php/config.php");

/* ===== SECURITY ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

$userId = $_SESSION['User_id'];

/* ===== FETCH STUDENT ===== */
$s = $conn->prepare("
    SELECT Stud_firstname 
    FROM student 
    WHERE User_id = ?
");
$s->bind_param("i", $userId);
$s->execute();
$student = $s->get_result()->fetch_assoc();

/* ===== FETCH VEHICLE STATUS ===== */
$v = $conn->prepare("
    SELECT Document_status 
    FROM vehicle 
    WHERE User_id = ?
");
$v->bind_param("i", $userId);
$v->execute();
$vehicle = $v->get_result()->fetch_assoc();

/* ===== BLOCK ACCESS IF NOT REJECTED ===== */
if (!$vehicle || $vehicle['Document_status'] !== 'Rejected') {
    header("Location: student_application_stat.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resubmit Vehicle Document</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
}
.form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn-primary {
    background: #0d6efd;
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 6px;
    font-weight: 600;
}
.btn-primary:hover {
    background: #0b5ed7;
}
</style>
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="header">
  <div class="header-content">
    <div></div>
    <div class="profile-name">
      Hi <?= htmlspecialchars($student['Stud_firstname']) ?>, Welcome to FKPARK!
      <i class="fas fa-user-circle"></i>
    </div>
  </div>
</header>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">

  <div class="logo-container">
    <img src="../uploads/fkparkLogo.jpg" class="logo">
  </div>

  <a href="student_dashboard.php" class="button">
    <i class="fas fa-home"></i> Dashboard
  </a>

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

  <a href="#" class="button"><i class="fas fa-parking"></i> Parking Spaces</a>
  <a href="#" class="button"><i class="fas fa-calendar-check"></i> Booking</a>
  <a href="#" class="button"><i class="fas fa-file-invoice"></i> Summon</a>

  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
  <div class="card">

    <h2 style="color:#2a2a72; margin-bottom:25px;">
      <i class="fas fa-upload"></i> Resubmit Vehicle Document
    </h2>

    <form action="student_resubmit_process.php"
          method="POST"
          enctype="multipart/form-data">

      <div class="form-group">
        <label>Upload New Vehicle Document (PDF / JPG / PNG)</label>
        <input type="file"
               name="vehicle_document"
               accept=".pdf,.jpg,.jpeg,.png"
               required>
      </div>

      <button type="submit" class="btn-primary">
        <i class="fas fa-save"></i> Submit
      </button>

    </form>

  </div>
</div>

<!-- ================= JS ================= -->
<script>
document.getElementById("vehicleBtn").onclick = function () {
  const menu = document.getElementById("vehicleMenu");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
};
</script>

</body>
</html>
