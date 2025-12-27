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
    $box         = $_POST['parking_box'];
    $status      = $_POST['status'];

    if ($status === 'unavailable') {
        $reason = $_POST['reason'];
        $start  = $_POST['start_datetime'];
        $end    = $_POST['end_datetime'];
    } else {
        $reason = 'not applicable';
        $start  = NULL;
        $end    = NULL;
    }

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Parking Area</title>

<link rel="stylesheet" href="style/navbaradmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.main-content {
    margin-left: 260px;
    padding: 30px;
}

.form-card {
    background: #fff;
    max-width: 550px;
    margin: auto;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.form-card h2 {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

select, input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ff2b78, #ff5fa2);
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    opacity: 0.9;
}

.alert {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 6px;
}

.alert-success {
    background: #dff0d8;
    color: #3c763d;
}

.alert-danger {
    background: #f2dede;
    color: #a94442;
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
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

    <!-- Vehicle Type -->
    <div class="form-group">
        <label>Vehicle Type</label>
        <select name="vehicle_type" id="vehicle_type" required>
            <option value="">Select Vehicle Type</option>
            <option value="car">Car</option>
            <option value="motorcycle">Motorcycle</option>
        </select>
    </div>

    <!-- Parking Area -->
    <div class="form-group">
        <label>Parking Area</label>
        <select name="parking_area" id="parking_area" required disabled>
            <option value="">Select Parking Area</option>
        </select>
    </div>

    <!-- Box Number -->
    <div class="form-group">
        <label>Box Number</label>
        <input type="text" name="parking_box" placeholder="e.g. 01" required>
    </div>

    <!-- Status -->
    <div class="form-group">
        <label>Status</label>
        <select name="status" id="status" required>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
        </select>
    </div>

    <!-- Reason -->
    <div class="form-group" id="reasonGroup" style="display:none;">
        <label>Status Reason</label>
        <select name="reason">
            <option value="maintenance">Maintenance</option>
            <option value="events">Events</option>
            <option value="cleaning">Cleaning</option>
        </select>
    </div>

    <!-- Time Period -->
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
/* ===== VEHICLE TYPE → PARKING AREA ===== */
const vehicleType = document.getElementById("vehicle_type");
const parkingArea = document.getElementById("parking_area");

vehicleType.addEventListener("change", function () {
    parkingArea.innerHTML = '<option value="">Select Parking Area</option>';
    parkingArea.disabled = false;

    if (this.value === "car") {
        ["B1", "B2", "B3"].forEach(a => {
            let opt = document.createElement("option");
            opt.value = a;
            opt.textContent = a;
            parkingArea.appendChild(opt);
        });
    }

    if (this.value === "motorcycle") {
        let opt = document.createElement("option");
        opt.value = "B4";
        opt.textContent = "B4";
        parkingArea.appendChild(opt);
    }

    if (this.value === "") {
        parkingArea.disabled = true;
    }
});

/* ===== STATUS TOGGLE ===== */
document.getElementById("status").addEventListener("change", function () {
    const show = this.value === "unavailable";
    document.getElementById("reasonGroup").style.display = show ? "block" : "none";
    document.getElementById("timeGroup").style.display   = show ? "block" : "none";
});
</script>

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
