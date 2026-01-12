<?php
session_start();
include '../php/config.php';

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== Fetch Admin Profile ===== */
$stmt = $conn->prepare("
    SELECT Admin_firstname, Admin_lastname, Admin_email, Admin_phoneNum
    FROM admins
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile</title>
    <link rel="stylesheet" href="style/navbaradmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidenav">
    <div class="logo-container">
        <img src="../uploads/fkparkLogo.jpg" class="logo">
    </div>

    <button onclick="location.href='admin_dashboard.php'">
        <i class="fas fa-home"></i> Dashboard
    </button>

    <button class="dropdown-btn" id="uvBtn">
            <span>
                <i class="fas fa-users"></i> User & Vehicle Registration
            </span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>

    <div class="dropdown-container">
        <a href="admin_reg_student.php">User Registration</a>
        <a href="admin_view_reg.php">List Registration</a>
        <a href="admin_profile.php">User Profile</a>
    </div>

    <button class="dropdown-btn" id="psBtn">
        <span>
        <i class="fas fa-parking"></i> Parking Spaces
        </span>
        <span class="dropdown-arrow">&#9654;</span>
    </button>

    <div class="dropdown-container">
        <a href="admin_list_parking.php">Parking List</a>
        <a href="admin_add_parking.php">Add Parking</a>
    </div>

    <button onclick="location.href='admin_bookings.php'">
        <i class="fas fa-list"></i> Bookings
    </button>

    <div class="logout">
        <button onclick="location.href='../public/logout_page.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<!-- HEADER -->
<div class="header">
    <div class="header-content">
        <div></div>
        <div class="profile-name">
            Hi <?= htmlspecialchars($admin['Admin_firstname']) ?>, Welcome to FKPARK!
            <span class="profile-icon"><i class="fas fa-user-circle"></i></span>
        </div>
    </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
    
    <div class="card">
        <h2 style="color:#f50057; margin-bottom:25px;">Admin Profile</h2>

        <div style="display:grid; grid-template-columns: 200px auto; row-gap:14px;">
            <strong>First Name</strong> <span><?= htmlspecialchars($admin['Admin_firstname']) ?></span>
            <strong>Last Name</strong> <span><?= htmlspecialchars($admin['Admin_lastname']) ?></span>
            <strong>Email</strong> <span><?= htmlspecialchars($admin['Admin_email']) ?></span>
            <strong>Phone Number</strong> <span><?= htmlspecialchars($admin['Admin_phoneNum']) ?></span>
        </div>

        <div style="margin-top:30px;">
            <a href="admin_edit_profile.php" class="btn btn-primary">
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
