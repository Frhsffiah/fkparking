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

    /* VIOLATION → POINT MAPPING */
    $pointsMap = [
        "Parking Without Booking" => 15,
        "Overtime Parking" => 10,
        "Parking at Reserved Area" => 20,
        "Double Parking" => 15,
        "Parking at Disabled (OKU) Space" => 25
    ];
    $points = $pointsMap[$violation] ?? 0;

    /* ===== UPDATE SUMMON (only violation & description & points) ===== */
    $update = $conn->prepare("
        UPDATE trafficsummon SET
            Summon_violationType = ?,
            Summon_description = ?,
            Summon_point = ?
        WHERE Summon_id = ?
    ");
    $update->bind_param(
        "ssii",
        $violation,
        $description,
        $points,
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
.main-content { margin-left: 250px; padding: 40px; }
.card { background: #fff; padding: 30px; border-radius: 15px; max-width: 650px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin: 0 auto; }
h2 { color: #1b5e20; }
.form-group { margin-bottom: 15px; }
label { font-weight: 600; color: #1b5e20; margin-bottom: 5px; display: block; }
input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
input[readonly] { background: #f3f3f3; }
.alert-error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
.btn-submit { background: #1b5e20; color: #fff; padding: 12px; width: 100%; border-radius: 8px; border: none; cursor: pointer; font-size: 16px; }
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">
    <div>
        <div class="logo-container">
            <img src="../uploads/fkparkLogo.jpg" class="logo">
        </div>

        <a href="staff_dashboard.php" class="button">
            <i class="fas fa-home"></i> Dashboard
        </a>

        <button class="dropdown-btn active">
            <span><i class="fas fa-file-invoice"></i> Traffic Summon</span>
            <span class="dropdown-arrow">&#9660;</span>
        </button>

        <div class="dropdown-containers" style="display:block;">
            <a href="staff_traffic_summon_list.php"><i class="fas fa-list"></i> Summon List</a>
            <a href="staff_add_traffic_summon.php"><i class="fas fa-plus-circle"></i> Add Summon</a>
        </div>
    </div>

    <button class="button" onclick="location.href='../public/logout_page.php'">
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

            <button type="submit" class="btn-submit"> Update Summon </button>

        </form>
    </div>
</div>

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
</script>

</body>
</html>