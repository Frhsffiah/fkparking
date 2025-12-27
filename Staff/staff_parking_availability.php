<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH STAFF INFO ===== */
$stmt = $conn->prepare("
    SELECT Staff_firstname, Staff_lastname
    FROM staff
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();


/* ===== FILTER VALUES ===== */
$vehicleType = $_GET['vehicle_type'] ?? '';
$status      = $_GET['status'] ?? '';

/* ===== FETCH PARKING DATA ===== */
$sql = "SELECT * FROM parkingspace WHERE 1";
$params = [];
$types  = "";

if (!empty($vehicleType)) {
    $sql .= " AND vehicle_type = ?";
    $params[] = $vehicleType;
    $types .= "s";
}

if (!empty($status)) {
    $sql .= " AND PS_status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Availability</title>

<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.main-content {
    margin-left: 260px;
    padding: 25px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    max-width: 1100px;
    margin: auto;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.card-header h2 {
    margin: 0;
}

.filter-form {
    display: flex;
    gap: 10px;
}

.filter-form select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background: #f5f5f5;
    padding: 12px;
}

td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

.status-available {
    color: green;
    font-weight: bold;
}

.status-unavailable {
    color: red;
    font-weight: bold;
}

.no-data {
    text-align: center;
    padding: 25px;
    font-style: italic;
    color: #777;
}
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

<!-- MAIN CONTENT -->
<div class="main-content">
<div class="card">

    <div class="card-header">
        <h2>Parking Availability</h2>

        <form method="GET" class="filter-form">
            <select name="vehicle_type">
                <option value="">All Vehicles</option>
                <option value="car" <?= $vehicleType=='car'?'selected':'' ?>>Car</option>
                <option value="motorcycle" <?= $vehicleType=='motorcycle'?'selected':'' ?>>Motorcycle</option>
            </select>

            <select name="status">
                <option value="">All Status</option>
                <option value="available" <?= $status=='available'?'selected':'' ?>>Available</option>
                <option value="unavailable" <?= $status=='unavailable'?'selected':'' ?>>Unavailable</option>
            </select>

            <button type="submit">
                <i class="fas fa-filter"></i>
            </button>
        </form>
    </div>

    <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>Vehicle</th>
            <th>Area</th>
            <th>Box</th>
            <th>Status</th>
            <th>Reason</th>
            <th>From</th>
            <th>Until</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $i = 1;
        if ($result->num_rows > 0):
            while ($p = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= ucfirst($p['vehicle_type']) ?></td>
            <td><?= htmlspecialchars($p['PS_Area']) ?></td>
            <td><?= htmlspecialchars($p['PS_box']) ?></td>
            <td class="<?= $p['PS_status']=='available'?'status-available':'status-unavailable' ?>">
                <?= ucfirst($p['PS_status']) ?>
            </td>
            <td><?= $p['PS_status']=='unavailable' ? htmlspecialchars($p['PS_reason']) : '-' ?></td>
            <td><?= $p['PS_status']=='unavailable' ? $p['start_datetime'] : '-' ?></td>
            <td><?= $p['PS_status']=='unavailable' ? $p['end_datetime'] : '-' ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
            <td colspan="8" class="no-data">No parking data found</td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>

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
