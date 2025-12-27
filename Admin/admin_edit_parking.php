<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== CHECK ID ===== */
if (!isset($_GET['id'])) {
    header("Location: admin_list_parking.php");
    exit();
}

$PS_id = $_GET['id'];

/* ===== FETCH ADMIN ===== */
$stmt = $conn->prepare("SELECT Admin_firstname FROM admins WHERE User_id = ?");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

/* ===== FETCH PARKING ===== */
$stmt = $conn->prepare("SELECT * FROM parkingspace WHERE PS_id = ?");
$stmt->bind_param("i", $PS_id);
$stmt->execute();
$parking = $stmt->get_result()->fetch_assoc();

if (!$parking) {
    header("Location: admin_list_parking.php");
    exit();
}

/* ===== HANDLE UPDATE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $area   = $_POST['parking_area'];
    $box    = $_POST['parking_box'];
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

    $sql = "UPDATE parkingspace
            SET PS_Area = ?, PS_box = ?, PS_status = ?, PS_reason = ?,
                start_datetime = ?, end_datetime = ?, PS_updated = NOW()
            WHERE PS_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $area, $box, $status, $reason, $start, $end, $PS_id);

    if ($stmt->execute()) {
        $success = "Parking area updated successfully.";
        $parking = array_merge($parking, $_POST);
    } else {
        $error = "Failed to update parking.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Parking Area</title>

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

.form-group { margin-bottom: 15px; }

label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

input, select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ff2b78, #ff5fa2);
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.alert { padding: 12px; margin-bottom: 15px; border-radius: 6px; }
.alert-success { background:#dff0d8; color:#3c763d; }
.alert-danger  { background:#f2dede; color:#a94442; }
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="header-content">
        <div></div>
        <div class="profile-name">
            Hi <?= htmlspecialchars($admin['Admin_firstname']) ?>, Welcome to FKPARK!
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

<h2>Edit Parking Area</h2>

<?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST">

    <div class="form-group">
        <label>Vehicle Type</label>
        <input type="text" value="<?= ucfirst($parking['vehicle_type']) ?>" readonly>
    </div>

    <div class="form-group">
        <label>Parking Area</label>
        <select name="parking_area" id="parking_area" required></select>
    </div>

    <div class="form-group">
        <label>Box Number</label>
        <input type="text" name="parking_box" value="<?= htmlspecialchars($parking['PS_box']) ?>" required>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status" id="status">
            <option value="available" <?= $parking['PS_status']=='available'?'selected':'' ?>>Available</option>
            <option value="unavailable" <?= $parking['PS_status']=='unavailable'?'selected':'' ?>>Unavailable</option>
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
        <input type="datetime-local" name="start_datetime" value="<?= $parking['start_datetime'] ?>">
        <input type="datetime-local" name="end_datetime" value="<?= $parking['end_datetime'] ?>">
    </div>

    <button type="submit">Update Parking</button>

</form>
</div>
</div>

<script>
const vehicleType = "<?= $parking['vehicle_type'] ?>";
const areaSelect  = document.getElementById("parking_area");

["B1","B2","B3","B4"].forEach(a => {
    if ((vehicleType==="car" && ["B1","B2","B3"].includes(a)) ||
        (vehicleType==="motorcycle" && a==="B4")) {
        let opt = document.createElement("option");
        opt.value = a;
        opt.textContent = a;
        if (a === "<?= $parking['PS_Area'] ?>") opt.selected = true;
        areaSelect.appendChild(opt);
    }
});

document.getElementById("status").addEventListener("change", toggleFields);
toggleFields();

function toggleFields() {
    const show = document.getElementById("status").value === "unavailable";
    document.getElementById("reasonGroup").style.display = show ? "block" : "none";
    document.getElementById("timeGroup").style.display   = show ? "block" : "none";
}
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
