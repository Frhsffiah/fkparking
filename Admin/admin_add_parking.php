<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH ADMIN DATA ===== */
$stmt = $conn->prepare("SELECT Admin_firstname FROM admins WHERE User_id = ?");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

/* ===== HANDLE FORM SUBMIT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicleType = $_POST['vehicle_type'];
    $area        = $_POST['parking_area'];

    /* Auto-format box number (01, 02, etc.) */
    $box = str_pad($_POST['parking_box'], 2, '0', STR_PAD_LEFT);

    $status = $_POST['status'];

    if ($status === 'unavailable') {
        $reason = $_POST['reason'];
        $start  = $_POST['start_datetime'];
        $end    = $_POST['end_datetime'];
    } else {
        $reason = 'not applicable';
        $start  = NULL;
        $end    = NULL;
    }

    /* ===== PREVENT DUPLICATE PARKING BOX ===== */
    $checkSql = "
        SELECT 1 
        FROM parkingspace 
        WHERE vehicle_type = ?
          AND PS_Area = ?
          AND PS_box = ?
    ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("sss", $vehicleType, $area, $box);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "This parking box already exists for the selected area and vehicle type.";
    } else {

        $sql = "INSERT INTO parkingspace 
                (vehicle_type, PS_Area, PS_box, PS_status, PS_reason, start_datetime, end_datetime, PS_create, PS_updated)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $vehicleType,
            $area,
            $box,
            $status,
            $reason,
            $start,
            $end
        );

        if ($stmt->execute()) {
            $success = "Parking space added successfully.";
        } else {
            $error = "Failed to add parking space.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Parking Area</title>

<link rel="stylesheet" href="style/navbaradmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.main-content {
    margin-left: 260px;
    padding: 40px;
    background: #f7f8fa;
    min-height: 100vh;
}

/* Professional container */
.form-card {
    background: #fff;
    max-width: 900px;
    padding: 40px 45px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.form-card h2 {
    margin-bottom: 30px;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.form-group {
    margin-bottom: 22px;
}

label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

select, input {
    width: 100%;
    padding: 13px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}

button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #ff2b78, #ff5fa2);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    opacity: 0.9;
}

/* Validation UI */
.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #e6f7ee;
    color: #1e7e34;
    border-left: 6px solid #28a745;
}

.alert-danger {
    background: #fdecea;
    color: #b02a37;
    border-left: 6px solid #dc3545;
}
</style>
</head>

<body>

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

<!-- SIDEBAR -->
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

    <button onclick="location.href='admin_bookings.php'">
        <i class="fas fa-list"></i> Bookings
    </button>

    <div class="logout">
        <button onclick="location.href='../public/logout_page.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
<div class="form-card">

<h2>Add Parking Area</h2>

<?php if (!empty($success)): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?= $success ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
</div>
<?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Vehicle Type</label>
        <select name="vehicle_type" id="vehicle_type" required>
            <option value="">Select Vehicle Type</option>
            <option value="car">Car</option>
            <option value="motorcycle">Motorcycle</option>
        </select>
    </div>

    <div class="form-group">
        <label>Parking Area</label>
        <select name="parking_area" id="parking_area" required disabled>
            <option value="">Select Parking Area</option>
        </select>
    </div>

    <div class="form-group">
        <label>Box Number</label>
        <input type="text" name="parking_box" placeholder="e.g. 01" required>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status" id="status" required>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
        </select>
    </div>

    <div class="form-group" id="reasonGroup" style="display:none;">
        <label>Status Reason</label>
        <select name="reason">
            <option value="maintenance">Maintenance</option>
            <option value="events">Events</option>
            <option value="cleaning">Cleaning</option>
        </select>
    </div>

    <div class="form-group" id="timeGroup" style="display:none;">
        <label>Unavailable Period</label>
        <input type="datetime-local" name="start_datetime" style="margin-bottom:10px;">
        <input type="datetime-local" name="end_datetime">
    </div>

    <button type="submit">Add Parking</button>
</form>

</div>
</div>

<script>
/* Auto-format box number */
const boxInput = document.querySelector('input[name="parking_box"]');
boxInput.addEventListener("input", function () {
    let v = this.value.replace(/\D/g, '');
    this.value = v.length === 1 ? "0" + v : v.substring(0,2);
});

/* Vehicle Type → Parking Area */
const vehicleType = document.getElementById("vehicle_type");
const parkingArea = document.getElementById("parking_area");

vehicleType.addEventListener("change", function () {
    parkingArea.innerHTML = '<option value="">Select Parking Area</option>';
    parkingArea.disabled = false;

    if (this.value === "car") {
        ["B1","B2","B3"].forEach(a => {
            parkingArea.innerHTML += `<option value="${a}">${a}</option>`;
        });
    }

    if (this.value === "motorcycle") {
        parkingArea.innerHTML += `<option value="B4">B4</option>`;
    }

    if (!this.value) parkingArea.disabled = true;
});

/* Status Toggle */
document.getElementById("status").addEventListener("change", function () {
    const show = this.value === "unavailable";
    document.getElementById("reasonGroup").style.display = show ? "block" : "none";
    document.getElementById("timeGroup").style.display   = show ? "block" : "none";
});

/* Sidebar Dropdown */
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        let content = this.nextElementSibling;
        let arrow = this.querySelector(".dropdown-arrow");
        content.style.display = content.style.display === "block" ? "none" : "block";
        arrow.innerHTML = content.style.display === "block" ? "&#9660;" : "&#9654;";
    });
});
</script>

</body>
</html>
