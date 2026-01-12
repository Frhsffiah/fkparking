<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

$error = "";

/* ===== GET SUMMON ID ===== */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: staff_traffic_summon_list.php");
    exit();
}

$summon_id = intval($_GET['id']);

/* ===== FETCH EXISTING SUMMON ===== */
$stmt = $conn->prepare("
    SELECT ts.*, v.Vehicle_regNo, u.User_username
    FROM trafficsummon ts
    JOIN vehicle v ON ts.Vehicle_regNo = v.Vehicle_regNo
    JOIN `user` u ON ts.User_id = u.User_id
    WHERE ts.Summon_id = ?
");
$stmt->bind_param("i", $summon_id);
$stmt->execute();
$result = $stmt->get_result();
$summon = $result->fetch_assoc();
$stmt->close();

if (!$summon) {
    header("Location: staff_traffic_summon_list.php");
    exit();
}

/* ===== HANDLE FORM SUBMISSION ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $violation   = $_POST['Summon_violationType'];
    $description = $_POST['Summon_description'];
    $status      = $summon['Summon_status']; // default to current

    // Allow changing Unpaid/Overdue -> Paid only
    if (isset($_POST['Summon_status']) && $_POST['Summon_status'] === 'Paid' &&
        ($summon['Summon_status'] === 'Unpaid' || $summon['Summon_status'] === 'Overdue')) {
        $status = 'Paid';
    }

    /* VIOLATION → POINT MAPPING */
    $pointsMap = [
        "Parking Without Booking" => 15,
        "Overtime Parking" => 10,
        "Parking at Reserved Area" => 20,
        "Double Parking" => 15,
        "Parking at Disabled (OKU) Space" => 25
    ];
    $points = $pointsMap[$violation] ?? 0;

    /* ===== UPDATE SUMMON (violation, description, points, status) ===== */
    $update = $conn->prepare("
        UPDATE trafficsummon SET
            Summon_violationType = ?,
            Summon_description = ?,
            Summon_point = ?,
            Summon_status = ?
        WHERE Summon_id = ?
    ");
    $update->bind_param(
        "ssisi",
        $violation,
        $description,
        $points,
        $status,
        $summon_id
    );

    if ($update->execute()) {
        header("Location: staff_view_traffic_summon.php?id=$summon_id");
        exit();
    } else {
        $error = "Failed to update summon.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Traffic Summon</title>

<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.main-content { 
    margin-left: 250px; 
    padding: 40px; 
}

.card { 
    background: #fff; 
    padding: 30px; 
    border-radius: 15px; 
    max-width: 650px; 
    box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
    margin: 0 auto; 
}

h2 { 
    color: #1b5e20; 
}

.form-group { 
    margin-bottom: 15px; 
}

label { 
    font-weight: 600; 
    color: #1b5e20; 
    margin-bottom: 5px; 
    display: block; 
}

input, select, textarea { 
    width: 100%; 
    padding: 10px; 
    border-radius: 8px; 
    border: 1px solid #ccc; 
}

input[readonly] { 
    background: #f3f3f3; 
}

.alert-error { 
    background: #ffebee; 
    color: #c62828; 
    padding: 12px; 
    border-radius: 8px; 
    margin-bottom: 15px; 
}

.btn-submit { 
    background: #1b5e20; 
    color: #fff; 
    padding: 12px; 
    width: 100%; 
    border-radius: 8px; 
    border: none; 
    cursor: pointer; 
    font-size: 16px; 
    margin-top: 10px; 
}

.btn-back { 
    background: #555; 
    color: #fff; 
    padding: 10px 20px; 
    border-radius: 8px; 
    display: inline-block; 
    text-decoration: none; 
    margin-bottom: 20px; 
}

.btn-back:hover { 
    background: #333; 
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
        <button class="dropdown-btn" id="tsBtn">
            <span>
                <i class="fas fa-file-invoice"></i> Traffic Summon
            </span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>

        <div class="dropdown-containers" id="tsMenu">
            <a href="staff_traffic_summon_list.php">
                <i class="fas fa-list"></i> Summon List
            </a>
            <a href="staff_add_traffic_summon.php">
                <i class="fas fa-plus-circle"></i> Add Summon
            </a>
        </div>
    </div>

    <!-- Logout -->
    <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
    <div class="card">

        <h2><i class="fas fa-edit"></i> Edit Traffic Summon</h2>

        <?php if ($error): ?><div class="alert-error"><?= $error ?></div><?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Vehicle Registration Number</label>
                <input type="text" value="<?= htmlspecialchars($summon['Vehicle_regNo']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Issue Date</label>
                <input type="text" value="<?= date("d/m/Y", strtotime($summon['Summon_issueDate'])) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Issue Time</label>
                <input type="text" value="<?= htmlspecialchars($summon['Summon_issueTime']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Violation Type</label>
                <select name="Summon_violationType" id="violation" onchange="updatePoints()" required>
                    <option value="">-- Select Violation --</option>
                    <option <?= $summon['Summon_violationType']=="Parking Without Booking"?"selected":"" ?>>Parking Without Booking</option>
                    <option <?= $summon['Summon_violationType']=="Overtime Parking"?"selected":"" ?>>Overtime Parking</option>
                    <option <?= $summon['Summon_violationType']=="Parking at Reserved Area"?"selected":"" ?>>Parking at Reserved Area</option>
                    <option <?= $summon['Summon_violationType']=="Double Parking"?"selected":"" ?>>Double Parking</option>
                    <option <?= $summon['Summon_violationType']=="Parking at Disabled (OKU) Space"?"selected":"" ?>>Parking at Disabled (OKU) Space</option>
                </select>
            </div>

            <div class="form-group">
                <label>Points</label>
                <input type="number" id="points" readonly>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="Summon_description" rows="3"><?= htmlspecialchars($summon['Summon_description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Summon Status</label>
                <?php if($summon['Summon_status'] === 'Unpaid' || $summon['Summon_status'] === 'Overdue'): ?>
                    <select name="Summon_status" required>
                        <option value="Paid">Paid</option>
                    </select>
                    <small>Current status: <?= $summon['Summon_status'] ?> (will change to Paid if submitted)</small>
                <?php else: ?>
                    <input type="text" value="<?= $summon['Summon_status'] ?>" readonly>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit"> Update Summon </button>

        </form>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
function updatePoints() {
    const map = {
        "Parking Without Booking": 15,
        "Overtime Parking": 10,
        "Parking at Reserved Area": 20,
        "Double Parking": 15,
        "Parking at Disabled (OKU) Space": 25
    };
    document.getElementById("points").value = map[violation.value] || "";
}

// Set points on page load
updatePoints();

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
