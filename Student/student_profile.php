<?php
session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== GET STUDENT DATA ===== */
$stmt = $conn->prepare("
    SELECT Stud_firstname, Stud_lastname, Stud_email,
           Stud_matricNum, Stud_course, Stud_level,
           Stud_phoneNum
    FROM student
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ===== PROFILE CARD (ADMIN-LEVEL UI) ===== */
.profile-card {
    background: #ffffff;
    padding: 35px 40px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    max-width: 900px;
}

/* Title */
.profile-card h2 {
    color: #2a2a72;
    font-size: 28px;
    margin-bottom: 30px;
}

/* Grid layout */
.profile-grid {
    display: grid;
    grid-template-columns: 220px auto;
    row-gap: 18px;
    column-gap: 20px;
    font-size: 16px;
}

.profile-grid strong {
    color: #222;
}

/* Action button */
.profile-actions {
    margin-top: 35px;
}

.profile-actions a {
    background-color: #0d6efd;
    color: #fff;
    padding: 10px 22px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.profile-actions a:hover {
    background-color: #0b5ed7;
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
  <button class="button">
    <i class="fas fa-receipt"></i> Summon
  </button>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">

    <div class="profile-card">

        <h2>Student Profile</h2>

        <div class="profile-grid">
            <strong>First Name</strong>
            <span><?= htmlspecialchars($student['Stud_firstname']) ?></span>

            <strong>Last Name</strong>
            <span><?= htmlspecialchars($student['Stud_lastname']) ?></span>

            <strong>Email</strong>
            <span><?= htmlspecialchars($student['Stud_email']) ?></span>

            <strong>Matric Number</strong>
            <span><?= htmlspecialchars($student['Stud_matricNum']) ?></span>

            <strong>Course</strong>
            <span><?= htmlspecialchars($student['Stud_course']) ?></span>

            <strong>Level</strong>
            <span><?= htmlspecialchars($student['Stud_level']) ?></span>

            <strong>Phone Number</strong>
            <span><?= htmlspecialchars($student['Stud_phoneNum'] ?? '-') ?></span>
        </div>

        <div class="profile-actions">
            <a href="student_edit_profile.php">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>

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



<?php if (isset($_GET['updated']) && $_GET['updated'] === 'success'): ?>
<style>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.modal-box {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  width: 350px;
  position: relative;
}
.success-icon {
  font-size: 48px;
  color: #0d6efd;
}
.close-btn {
  position: absolute;
  top: 12px;
  right: 15px;
  cursor: pointer;
  font-size: 22px;
}
</style>

<div class="modal-overlay" id="successModal">
  <div class="modal-box">
    <span class="close-btn" onclick="closeModal()">×</span>
    <i class="fas fa-check-circle success-icon"></i>
    <h3>Profile Updated</h3>
    <p>Your profile has been updated successfully.</p>
    <button class="btn btn-primary" onclick="closeModal()">OK</button>
  </div>
</div>

<script>
function closeModal() {
  document.getElementById("successModal").style.display = "none";
  window.location.href = "student_profile.php";
}
</script>
<?php endif; ?>

</body>
</html>
