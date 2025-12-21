<?php
session_start();
include '../php/config.php';

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== Fetch Admin Name ===== */
$stmtAdmin = $conn->prepare("
    SELECT Admin_firstname 
    FROM admins 
    WHERE User_id = ?
");
$stmtAdmin->bind_param("i", $_SESSION['User_id']);
$stmtAdmin->execute();
$admin = $stmtAdmin->get_result()->fetch_assoc();

/* ===== Fetch Student Detail ===== */
$stud_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM student WHERE Stud_id=?");
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Profile</title>
    <link rel="stylesheet" href="style/navbaradmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">
    <div class="logo-container">
        <img src="../uploads/fkparkLogo.jpg" class="logo">
    </div>

    <button onclick="location.href='admin_dashboard.php'">
        <i class="fas fa-home"></i> Dashboard
    </button>

    <button class="dropdown-btn">
        <span><i class="fas fa-users"></i> User & Vehicle Registration</span>
        <i class="fas fa-chevron-down"></i>
    </button>

    <div class="dropdown-container">
        <a href="admin_reg_student.php">User Registration</a>
        <a href="admin_view_reg.php">List Registration</a>
        <a href="admin_profile.php">User Profile</a>
    </div>

    <button><i class="fas fa-parking"></i> Parking Spaces</button>

    <div class="logout">
        <button onclick="location.href='../public/logout_page.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<!-- ================= HEADER ================= -->
<header class="header">
    <div class="header-content">
        <div></div>
        <div class="profile-name">
            Hi <?= htmlspecialchars($admin['Admin_firstname']) ?>, Welcome to FKPARK!
            <i class="fas fa-user-circle"></i>
        </div>
    </div>
</header>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
    <div class="card">
        <h2 style="color:#f50057; margin-bottom:25px;">
            Student Profile Details
        </h2>

        <div style="display:grid; grid-template-columns: 220px auto; row-gap:14px;">
            <strong>First Name</strong> <span><?= htmlspecialchars($student['Stud_firstname']) ?></span>
            <strong>Last Name</strong> <span><?= htmlspecialchars($student['Stud_lastname']) ?></span>
            <strong>IC Number</strong> <span><?= htmlspecialchars($student['Stud_IC']) ?></span>
            <strong>Course</strong> <span><?= htmlspecialchars($student['Stud_course']) ?></span>
            <strong>Gender</strong> <span><?= htmlspecialchars($student['Stud_gender']) ?></span>
            <strong>Phone Number</strong> <span><?= htmlspecialchars($student['Stud_phoneNum']) ?></span>
            <strong>Email</strong> <span><?= htmlspecialchars($student['Stud_email']) ?></span>
            <strong>Matric Number</strong> <span><?= htmlspecialchars($student['Stud_matricNum']) ?></span>
            <strong>Level</strong> <span><?= htmlspecialchars($student['Stud_level']) ?></span>
        </div>

        <div style="margin-top:35px;">
            <a href="admin_view_reg.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<script>
document.querySelector(".dropdown-btn").onclick = function () {
    let dropdown = this.nextElementSibling;
    dropdown.style.display =
        dropdown.style.display === "block" ? "none" : "block";
};
</script>

</body>
</html>
