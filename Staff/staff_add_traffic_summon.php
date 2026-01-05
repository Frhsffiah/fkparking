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

/* ===== HANDLE FORM SUBMISSION ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle_regNo = strtoupper(trim($_POST['Vehicle_regNo']));
    $violation     = $_POST['Summon_violationType'];
    $description   = $_POST['Summon_description'];

    /* DEVICE DATE & TIME */
    $issueDate = $_POST['Summon_issueDate']; // YYYY-MM-DD
    $issueTime = $_POST['Summon_issueTime']; // HH:MM:SS

    /* VIOLATION → POINT MAPPING */
    $pointsMap = [
        "Parking Without Booking" => 15,
        "Overtime Parking" => 10,
        "Parking at Reserved Area" => 20,
        "Double Parking" => 15,
        "Parking at Disabled (OKU) Space" => 25
    ];
    $points = $pointsMap[$violation] ?? 0;

    /* ===== CHECK VEHICLE EXISTS ===== */
    $stmt = $conn->prepare("SELECT User_id FROM vehicle WHERE Vehicle_regNo = ?");
    $stmt->bind_param("s", $vehicle_regNo);
    $stmt->execute();
    $vehicle = $stmt->get_result()->fetch_assoc();

    if (!$vehicle) {
        $error = "Vehicle registration number not found. Summon cannot be issued.";
    } else {

        $user_id = $vehicle['User_id'];

        /* ===== INSERT SUMMON ===== */
        $insert = $conn->prepare("
            INSERT INTO trafficsummon
            (User_id, Vehicle_regNo, Summon_issueDate, Summon_issueTime,
             Summon_violationType, Summon_description, Summon_point)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param(
            "isssssi",
            $user_id,
            $vehicle_regNo,
            $issueDate,
            $issueTime,
            $violation,
            $description,
            $points
        );

        if ($insert->execute()) {
            // Redirect to view page after successful insert
            header("Location: staff_view_traffic_summon.php");
            exit();
        } else {
            $error = "Failed to issue summon.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Traffic Summon</title>

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
    margin: 0 auto; /* Center card horizontally */
}

h2 { color: #1b5e20; }

.form-group { margin-bottom: 15px; }

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

input[readonly] { background: #f3f3f3; }

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

        <a href="staff_dashboard.php" class="button">
            <i class="fas fa-home"></i> Dashboard
        </a>

        <button class="dropdown-btn active">
            <span><i class="fas fa-file-invoice"></i> Traffic Summon</span>
            <span class="dropdown-arrow">&#9660;</span>
        </button>

        <div class="dropdown-containers" style="display:block;">
            <a href="staff_traffic_summon_list.php">
                <i class="fas fa-list"></i> Summon List
            </a>
            <a href="staff_add_traffic_summon.php">
                <i class="fas fa-plus-circle"></i> Add Summon
            </a>
        </div>
    </div>

    <button class="button" onclick="location.href='../public/logout_page.php'">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
    <div class="card">

        <h2><i class="fas fa-plus-circle"></i> Issue Traffic Summon</h2>

        <?php if ($error): ?><div class="alert-error"><?= $error ?></div><?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Vehicle Registration Number</label>
                <input type="text" name="Vehicle_regNo" placeholder="Enter vehicle registration number" required>
            </div>

            <div class="form-group">
                <label>Issue Date</label>
                <input type="text" id="displayDate" readonly>
            </div>

            <div class="form-group">
                <label>Issue Time</label>
                <input type="text" id="displayTime" readonly>
            </div>

            <!-- Hidden DB values -->
            <input type="hidden" name="Summon_issueDate" id="dbDate">
            <input type="hidden" name="Summon_issueTime" id="dbTime">

            <div class="form-group">
                <label>Violation Type</label>
                <select name="Summon_violationType" id="violation" onchange="updatePoints()" required>
                    <option value="">-- Select Violation --</option>
                    <option>Parking Without Booking</option>
                    <option>Overtime Parking</option>
                    <option>Parking at Reserved Area</option>
                    <option>Double Parking</option>
                    <option>Parking at Disabled (OKU) Space</option>
                </select>
            </div>

            <div class="form-group">
                <label>Points</label>
                <input type="number" id="points" readonly>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="Summon_description" placeholder="Enter description" rows="3"></textarea>
            </div>

            <button type="submit" class="btn-submit"> Issue Summon </button>

        </form>
    </div>
</div>

<script>
function setDeviceTime() {
    const now = new Date();

    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2,'0');
    const dd = String(now.getDate()).padStart(2,'0');

    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2,'0');
    const seconds = String(now.getSeconds()).padStart(2,'0');

    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;

    document.getElementById("displayDate").value = `${dd}/${mm}/${yyyy}`;
    document.getElementById("displayTime").value = `${hours}:${minutes} ${ampm}`;

    document.getElementById("dbDate").value = `${yyyy}-${mm}-${dd}`;
    document.getElementById("dbTime").value =
        `${now.getHours().toString().padStart(2,'0')}:${minutes}:${seconds}`;
}

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

setDeviceTime();
</script>

</body>
</html>