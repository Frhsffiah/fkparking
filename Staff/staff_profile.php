<?php
session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH STAFF DATA ===== */
$stmt = $conn->prepare("
    SELECT Staff_firstname, Staff_lastname, Staff_email, Staff_phoneNum
    FROM staff
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
<title>Staff Profile</title>
<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        <a href="#">
            <i class="fas fa-file-invoice"></i> Traffic Summon
        </a>
    </div>

    <!-- Logout -->
    <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>
</div>

<!-- ================= HEADER ================= -->
<header class="header">
    <div class="header-content">
        <div class="role">Staff</div>
        <div class="profile-details">
            <div class="profile-name">
                Hi <?= htmlspecialchars($staff['Staff_firstname']) ?>, Welcome to FKPARK!
            </div>
            <div class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
    </div>
</header>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content" style="margin-left:250px;padding:40px;">
    <div class="card" style="max-width:800px;background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.08);">

        <h2 style="color:#2e7d32;margin-bottom:25px;">Staff Profile</h2>

        <div style="display:grid;grid-template-columns:200px auto;row-gap:14px;">
            <strong>First Name</strong> <span><?= htmlspecialchars($staff['Staff_firstname']) ?></span>
            <strong>Last Name</strong> <span><?= htmlspecialchars($staff['Staff_lastname']) ?></span>
            <strong>Email</strong> <span><?= htmlspecialchars($staff['Staff_email']) ?></span>
            <strong>Phone Number</strong> <span><?= htmlspecialchars($staff['Staff_phoneNum']) ?></span>
        </div>

        <div style="margin-top:30px;">
            <a href="staff_edit_profile.php" class="btn-primary" style="background:#1e88e5;padding:10px 20px;border-radius:6px;color:#fff;text-decoration:none;">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>

    </div>
</div>

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
