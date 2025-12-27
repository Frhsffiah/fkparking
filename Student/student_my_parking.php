<?php
session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH STUDENT INFO ===== */
$stmt = $conn->prepare("
    SELECT Stud_id, Stud_firstname
    FROM student
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* ===== FETCH STUDENT PARKING BOOKINGS ===== */
$stmt = $conn->prepare("
    SELECT 
        pb.PB_id,
        pb.PB_date,
        pb.PB_startTime,
        pb.PB_endTime,
        pb.PB_status,
        pb.Vehicle_regNo,
        ps.vehicle_type,
        ps.PS_Area,
        ps.PS_box
    FROM parkingbooking pb
    JOIN parkingspace ps ON pb.PS_id = ps.PS_id
    WHERE pb.Stud_id = ?
    ORDER BY pb.PB_date DESC
");
$stmt->bind_param("i", $student['Stud_id']);
$stmt->execute();
$resultBooking = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Parking</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.main-content {
    margin-left: 250px;
    margin-top: 90px;
    padding: 40px;
}

.card {
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    max-width: 900px;
}

.parking-card {
    border: 1px solid #eee;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.badge {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 13px;
    margin-top: 8px;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-ended {
    background: #f8d7da;
    color: #721c24;
}

.empty {
    text-align: center;
    color: #777;
    padding: 40px;
}
</style>
</head>

<body>

<!-- ===== SIDEBAR ===== -->
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
  <button class="button">
    <i class="fas fa-receipt"></i> Summon
  </button>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>
<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

<div class="card">
<h2>My Parking</h2>

<?php if ($resultBooking->num_rows > 0): ?>
    <?php while ($b = $resultBooking->fetch_assoc()): ?>
        <div class="parking-card">
            <p><strong>Vehicle Type:</strong> <?= ucfirst($b['vehicle_type']) ?></p>
            <p><strong>Vehicle Plate:</strong> <?= htmlspecialchars($b['Vehicle_regNo']) ?></p>
            <p><strong>Area:</strong> <?= htmlspecialchars($b['PS_Area']) ?></p>
            <p><strong>Box:</strong> <?= htmlspecialchars($b['PS_box']) ?></p>
            <p><strong>From:</strong> <?= $b['PB_startTime'] ?></p>
            <p><strong>Until:</strong> <?= $b['PB_endTime'] ?></p>

            <span class="badge <?= $b['PB_status']=='active' ? 'badge-active' : 'badge-ended' ?>">
                <?= ucfirst($b['PB_status']) ?>
            </span>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty">
        <i class="fas fa-car-side fa-3x"></i>
        <p>You do not have any parking bookings yet.</p>
    </div>
<?php endif; ?>

</div>
</div>

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
