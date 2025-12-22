<?php
session_start();
include("../php/config.php");

/* ===== SECURITY ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== STAFF NAME ===== */
$stmtStaff = $conn->prepare("
    SELECT Staff_firstname FROM staff WHERE User_id=?
");
$stmtStaff->bind_param("i", $_SESSION['User_id']);
$stmtStaff->execute();
$staff = $stmtStaff->get_result()->fetch_assoc();

/* ===== FETCH VEHICLES ===== */
$stmt = $conn->prepare("
    SELECT Vehicle_regNo, Vehicle_type, Document_name, Document_status
    FROM vehicle
    ORDER BY Created_at DESC
");
$stmt->execute();
$vehicles = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Vehicle Approval</title>
<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.table-card{
    background:#fff;
    border-radius:12px;
    padding:25px;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:14px;
    border-bottom:1px solid #eee;
    text-align:center;
}
th{
    background:#f5f5f5;
}
.badge-pending{color:#ff9800;font-weight:bold;}
.badge-ok{color:#4caf50;font-weight:bold;}
.badge-reject{color:#f44336;font-weight:bold;}
.btn{
    padding:8px 16px;
    border-radius:6px;
    border:none;
    color:#fff;
    cursor:pointer;
}
.btn-approve{background:#4caf50;}
.btn-reject{background:#f44336;}
.btn-view{background:#2196f3;text-decoration:none;color:#fff;padding:8px 14px;border-radius:6px;}
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

<!-- MAIN -->
<div class="main-content" style="margin-left:250px;padding:40px;">
  <div class="table-card">

    <h2 style="color:#1b5e20;margin-bottom:20px;">
      <i class="fas fa-check-circle"></i> Vehicle Approval
    </h2>

    <table>
      <tr>
        <th>Vehicle No</th>
        <th>Type</th>
        <th>Document</th>
        <th>Status</th>
        <th>Action</th>
      </tr>

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

<?php while($v = $vehicles->fetch_assoc()): ?>
<tr>
<td><?= $v['Vehicle_regNo'] ?></td>
<td><?= $v['Vehicle_type'] ?></td>
<td>
<a class="btn-view" target="_blank"
href="../uploads/vehicle_docs/<?= $v['Document_name'] ?>">View</a>
</td>
<td>
<?php
if($v['Document_status']=="Pending") echo "<span class='badge-pending'>Pending</span>";
elseif($v['Document_status']=="Verified") echo "<span class='badge-ok'>Verified</span>";
else echo "<span class='badge-reject'>Rejected</span>";
?>
</td>
<td>
<?php if($v['Document_status']=="Pending"): ?>
<form method="POST" action="staff_approve_vec_action.php" style="display:inline;">
<input type="hidden" name="regNo" value="<?= $v['Vehicle_regNo'] ?>">
<button class="btn btn-approve" name="action" value="approve">Approve</button>
</form>

<form method="POST" action="staff_approve_vec_action.php" style="display:inline;">
<input type="hidden" name="regNo" value="<?= $v['Vehicle_regNo'] ?>">
<button class="btn btn-reject" name="action" value="reject">Reject</button>
</form>
<?php else: ?>
—
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>

    </table>
  </div>
</div>
</body>
</html>
