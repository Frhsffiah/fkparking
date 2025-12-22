<?php
session_start();
include("../php/config.php");

/* SECURITY */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* FETCH STUDENT */
$stu = $conn->prepare("SELECT Stud_firstname FROM student WHERE User_id=?");
$stu->bind_param("i", $_SESSION['User_id']);
$stu->execute();
$student = $stu->get_result()->fetch_assoc();

/* FETCH VEHICLE */
$stmt = $conn->prepare("
    SELECT Vehicle_regNo, Vehicle_type, Vehicle_brand, Vehicle_color, Document_status
    FROM vehicle
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
<title>Application Status</title>
<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

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
    <a href="student_my_parking.php">
      <i class="fas fa-car-side"></i> My Parking
    </a>
    </div>
    

  <!-- Booking -->
  <button class="button">
    <i class="fas fa-calendar-check"></i> Booking
  </button>

  <!-- Summon -->
  <button class="button">
    <i class="fas fa-receipt"></i> Summon
  </button>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<div class="main-content">
  <div class="card">

    <h2 style="color:#2a2a72;">
      <i class="fas fa-clipboard-check"></i> Application Status
    </h2>

<?php if (!$vehicle): ?>
    <p>You have not registered any vehicle yet.</p>
    <a href="student_vec_reg.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Register Vehicle
    </a>

<?php else: ?>

<div style="display:grid; grid-template-columns:220px auto; row-gap:14px; margin-top:20px;">
  <strong>Vehicle Reg No</strong><span><?= $vehicle['Vehicle_regNo'] ?></span>
  <strong>Vehicle Type</strong><span><?= $vehicle['Vehicle_type'] ?></span>
  <strong>Brand</strong><span><?= $vehicle['Vehicle_brand'] ?></span>
  <strong>Color</strong><span><?= $vehicle['Vehicle_color'] ?></span>
  <strong>Status</strong>
  <span>
    <?php if ($vehicle['Document_status']=='Pending'): ?>
      <span style="color:orange;font-weight:bold;">Pending</span>
    <?php elseif ($vehicle['Document_status']=='Verified'): ?>
      <span style="color:green;font-weight:bold;">Verified</span>
    <?php else: ?>
      <span style="color:red;font-weight:bold;">Rejected</span>
    <?php endif; ?>
  </span>
</div>

<?php if ($vehicle['Document_status']=='Rejected'): ?>
  <div style="margin-top:25px;">
    <a href="student_resubmit_doc.php" class="btn btn-primary">
      <i class="fas fa-upload"></i> Resubmit Document
    </a>
  </div>
<?php endif; ?>

<?php endif; ?>

  </div>
</div>

<!-- ================= JS ================= -->

<script>
document.querySelectorAll('.dropdown-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    this.classList.toggle("active");
    let dropdown = this.nextElementSibling;
    dropdown.style.display =
      dropdown.style.display === "block" ? "none" : "block";
  });
});
</script>
</body>
</html>
