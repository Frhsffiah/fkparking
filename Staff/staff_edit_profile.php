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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Staff Profile</title>

<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ===== MAIN CONTENT FIX ===== */
.main-content{
    margin-left:250px;
    padding:40px;
}

/* ===== CARD (SAME AS STUDENT) ===== */
.card{
    background:#fff;
    border-radius:12px;
    padding:35px;
    max-width:850px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* ===== FORM GRID ===== */
.form-group{
    display:grid;
    grid-template-columns:200px auto;
    align-items:center;
    margin-bottom:20px;
}

.form-group label{
    font-weight:600;
    color:#333;
}

.form-group input{
    padding:11px 14px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:15px;
    width:100%;
}

/* ===== BUTTONS ===== */
.form-actions{
    margin-top:30px;
    display:flex;
    gap:20px;
}

.btn-primary{
    background:#0d6efd;
    color:#fff;
    padding:11px 26px;
    border-radius:6px;
    border:none;
    font-size:15px;
    cursor:pointer;
}

.btn-primary:hover{
    background:#0b5ed7;
}

.cancel-link{
    color:#0d6efd;
    font-weight:600;
    text-decoration:none;
    align-self:center;
}
</style>
</head>

<body>

<!-- ================= HEADER ================= -->
<header class="header">
  <div class="header-content">
    <div></div>
    <div class="profile-name">
      Hi <?= htmlspecialchars($staff['Staff_firstname']) ?>, Welcome to FKPARK!
      <i class="fas fa-user-circle"></i>
    </div>
  </div>
</header>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">

  <div class="logo-container">
    <img src="../uploads/fkparkLogo.jpg" class="logo">
  </div>

  <a href="staff_dashboard.php" class="button">
    <i class="fas fa-home"></i> Dashboard
  </a>

  <button class="dropdown-btn" id="uvBtn">
    <span>
      <i class="fas fa-users"></i> User & Vehicle Registration
    </span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="uvMenu">
    <a href="staff_approve_vec.php"><i class="fas fa-car"></i> Vehicle Approval</a>
    <a href="staff_profile.php"><i class="fas fa-user"></i> User Profile</a>
  </div>

  <a href="#" class="button"><i class="fas fa-parking"></i> Parking Spaces</a>
  <a href="#" class="button"><i class="fas fa-exclamation-circle"></i> Traffic Summon</a>

  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
  <div class="card">

    <h2 style="color:#1b5e20; margin-bottom:30px;">
      Edit Staff Profile
    </h2>

    <form method="POST" action="staff_update_profile.php">

      <div class="form-group">
        <label>First Name</label>
        <input type="text" name="firstname"
               value="<?= htmlspecialchars($staff['Staff_firstname']) ?>" required>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="lastname"
               value="<?= htmlspecialchars($staff['Staff_lastname']) ?>" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($staff['Staff_email']) ?>" required>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone"
               value="<?= htmlspecialchars($staff['Staff_phoneNum']) ?>" required>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">
          <i class="fas fa-save"></i> Update Profile
        </button>
        <a href="staff_profile.php" class="cancel-link">Cancel</a>
      </div>

    </form>

  </div>
</div>

<script>
document.getElementById("uvBtn").onclick = function () {
  let menu = document.getElementById("uvMenu");
  menu.style.display = menu.style.display === "block" ? "none" : "block";
};
</script>

</body>
</html>
