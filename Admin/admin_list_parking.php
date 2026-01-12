<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH ADMIN DATA ===== */
$sqlAdmin = "SELECT * FROM admins WHERE User_id = ?";
$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("i", $_SESSION['User_id']);
$stmtAdmin->execute();
$admin = $stmtAdmin->get_result()->fetch_assoc();

/* ===== FILTER VALUES ===== */
$vehicleType = $_GET['vehicle_type'] ?? '';
$status      = $_GET['status'] ?? '';

/* ===== BUILD PARKING QUERY ===== */
$sqlParking = "SELECT * FROM parkingspace WHERE 1";
$params = [];
$types  = "";

/* Filter by vehicle type */
if (!empty($vehicleType)) {
    $sqlParking .= " AND vehicle_type = ?";
    $params[] = $vehicleType;
    $types .= "s";
}

/* Filter by status */
if (!empty($status)) {
    $sqlParking .= " AND PS_status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmtParking = $conn->prepare($sqlParking);
if (!empty($params)) {
    $stmtParking->bind_param($types, ...$params);
}
$stmtParking->execute();
$resultParking = $stmtParking->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>List of Parking Areas</title>

<link rel="stylesheet" href="style/navbaradmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 260px;
    padding: 25px;
}

/* ===== CARD ===== */
.parking-card {
    background: #ffffff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    max-width: 1200px;
    margin: auto;
}

/* ===== HEADER ===== */
.parking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.parking-header h2 {
    margin: 0;
    font-size: 22px;
    color: #333;
}

/* ===== FILTER ===== */
.filter-form {
    display: flex;
    gap: 10px;
}

.filter-form select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.filter-form button {
    padding: 8px 16px;
    background: linear-gradient(135deg, #ff2b78, #ff5fa2);
    border: none;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.filter-form button:hover {
    opacity: 0.9;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background-color: #f5f5f5;
    padding: 12px;
    text-align: center;
}

td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

tbody tr:hover {
    background-color: #fafafa;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.action-buttons a {
    padding: 6px 12px;
    background-color: #007bff;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
}

.action-buttons a:hover {
    background-color: #0056b3;
}

/* ===== QR ===== */
.qr-code-img {
    width: 45px;
    height: 45px;
    transition: 0.3s;
}

.qr-code-img:hover {
    transform: scale(2);
}

/* ===== EMPTY STATE ===== */
.no-data {
    text-align: center;
    font-style: italic;
    color: #888;
    padding: 30px;
}
</style>
</head>

<body>

<!-- HEADER (UNCHANGED) -->
<div class="header">
    <div class="header-content">
        <div></div>
        <div class="profile-name">
            Hi <?= htmlspecialchars($admin['Admin_firstname']) ?>, Welcome to FKPARK!
            <span class="profile-icon"><i class="fas fa-user-circle"></i></span>
        </div>
    </div>
</div>

<!-- SIDEBAR (UNCHANGED) -->
<div class="sidenav">
    <div class="logo-container">
        <img src="../uploads/fkparkLogo.jpg" class="logo">
    </div>

    <button onclick="location.href='admin_dashboard.php'">
        <i class="fas fa-home"></i> Dashboard
    </button>

    <button class="dropdown-btn">
        <span><i class="fas fa-users"></i> User & Vehicle Registration</span>
        <span class="dropdown-arrow">&#9654;</span>
    </button>

    <div class="dropdown-container">
        <a href="admin_reg_student.php">User Registration</a>
        <a href="admin_view_reg.php">List Registration</a>
        <a href="admin_profile.php">User Profile</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fas fa-parking"></i> Parking Spaces</span>
        <span class="dropdown-arrow">&#9654;</span>
    </button>

    <div class="dropdown-container">
        <a href="admin_list_parking.php">Parking List</a>
        <a href="admin_add_parking.php">Add Parking</a>
    </div>

    <div class="logout">
        <button onclick="location.href='../public/logout_page.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
<div class="parking-card">

    <div class="parking-header">
        <h2>List of Parking Areas</h2>

        <form method="GET" class="filter-form">
            <select name="vehicle_type">
                <option value="">All Vehicle Types</option>
                <option value="car" <?= ($vehicleType=='car')?'selected':'' ?>>Car</option>
                <option value="motorcycle" <?= ($vehicleType=='motorcycle')?'selected':'' ?>>Motorcycle</option>
            </select>

            <select name="status">
                <option value="">All Status</option>
                <option value="available" <?= ($status=='available')?'selected':'' ?>>Available</option>
                <option value="unavailable" <?= ($status=='unavailable')?'selected':'' ?>>Unavailable</option>
            </select>

            <button type="submit">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>
    </div>

    <table>
        <thead>
        <tr>
            <th>No.</th>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Area</th>
            <th>Box</th>
            <th>Status</th>
            <th>Reason</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $count = 1;
        if ($resultParking->num_rows > 0):
            while ($p = $resultParking->fetch_assoc()):
        ?>
        <tr>
            <td><?= $count++ ?></td>
            <td><?= $p['PS_id'] ?></td>
            <td><?= ucfirst($p['vehicle_type']) ?></td>
            <td><?= htmlspecialchars($p['PS_Area']) ?></td>
            <td><?= htmlspecialchars($p['PS_box']) ?></td>
            <td><?= ucfirst($p['PS_status']) ?></td>
            <td><?= $p['PS_status']=='unavailable' ? htmlspecialchars($p['PS_reason']) : '-' ?></td>
            <td class="action-buttons">
                <a href="admin_edit_parking.php?id=<?= $p['PS_id'] ?>">Edit</a>
                <a href="admin_delete_parking.php?id=<?= $p['PS_id'] ?>"
                   onclick="return confirm('Delete this parking?');">Delete</a>
            </td>
            <td>
                <?php if (!empty($p['PS_Qrcode'])): ?>
                    <img src="<?= htmlspecialchars($p['PS_Qrcode']) ?>" class="qr-code-img">
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
            <td colspan="9" class="no-data">No parking found</td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</div>

<!-- DROPDOWN SCRIPT (UNCHANGED) -->
<script>
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        let content = this.nextElementSibling;
        let arrow = this.querySelector(".dropdown-arrow");
        if (content.style.display === "block") {
            content.style.display = "none";
            arrow.innerHTML = "&#9654;";
        } else {
            content.style.display = "block";
            arrow.innerHTML = "&#9660;";
        }
    });
});
</script>

</body>
</html>
