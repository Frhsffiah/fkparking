<?php
session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== FETCH STUDENT INFO ===== */
$stmt = $conn->prepare("
    SELECT Stud_firstname
    FROM student
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* ===== FILTER VALUES ===== */
$vehicleFilter = $_GET['vehicle'] ?? '';
$statusFilter  = $_GET['status'] ?? '';

/* ===== BUILD QUERY ===== */
$sqlParking = "SELECT * FROM parkingspace WHERE 1=1";
$params = [];
$types  = "";

if (!empty($vehicleFilter)) {
    $sqlParking .= " AND vehicle_type = ?";
    $params[] = $vehicleFilter;
    $types .= "s";
}

if (!empty($statusFilter)) {
    $sqlParking .= " AND PS_status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sqlParking .= " ORDER BY PS_Area, PS_box";

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
<title>Parking Availability</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.main-content {
    margin-left: 250px;
    margin-top: 90px;
    padding: 40px;
}

.card {
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    max-width: 1000px;
}

.filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.filter-bar select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.filter-bar button {
    background: #0d6efd;
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
}

.filter-bar button:hover {
    background: #0b5ed7;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

th {
    background: #f5f5f5;
}

.status-available {
    color: green;
    font-weight: bold;
}

.status-unavailable {
    color: red;
    font-weight: bold;
}

.book-btn {
    background: #28a745;
    color: #fff;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
}

.book-btn:hover {
    background: #218838;
}

.details-btn {
    background: #0d6efd;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    cursor: pointer;
}

.details-btn:hover {
    background: #0b5ed7;
}

/* ===== MODAL ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-card {
    background: #fff;
    padding: 25px;
    width: 400px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.modal-card h3 {
    color: #dc3545;
    margin-bottom: 15px;
}

.modal-card button {
    margin-top: 15px;
    background: #6c757d;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidenav">

  <div class="logo-container">
    <img src="../uploads/fkparkLogo.jpg" class="logo">
  </div>

  <button class="button active" onclick="location.href='student_dashboard.php'">
    <i class="fas fa-home"></i> Dashboard
  </button>

<button class="dropdown-btn" id="vehicleBtn">
  <span class="icon-text">
    <i class="fas fa-car"></i>
    <span>Vehicle</span>
  </span>
  <span class="dropdown-arrow">&#9654;</span>
</button>

 <div class="dropdown-containers" id="vehicleMenu">
    <a href="student_vec_reg.php">
      <i class="fas fa-plus-circle"></i> Vehicle Registration
    </a>

  <a href="student_application_stat.php">
    <i class="fas fa-clipboard-check"></i> Application Status
  </a>

  <a href="student_profile.php">
    <i class="fas fa-user"></i> User Profile
  </a>
</div>

   <!-- Parking Spaces -->
  <button class="dropdown-btn" id="psBtn">
    <span class="icon-text">
    <i class="fas fa-parking"></i> 
    <span>Parking Spaces</span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="psMenu">
    <a href="student_parking_availability.php">
      <i class="fas fa-list"></i> Parking Availability
    </a>
    </div>
    

  <!-- Booking -->
  <button class="dropdown-btn" id="psBtn">
    <span class="icon-text">
    <i class="fas fa-parking"></i> 
    <span>Booking</span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="psMenu">
    <a href="student_search_parking.php">
      <i class="fas fa-list"></i> Make Booking
    </a>
    <a href="student_bookings.php">
      <i class="fas fa-car-side"></i> My Bookings
    </a>
    </div>

  <!-- Summon -->
  <button class="button">
    <i class="fas fa-receipt"></i> Summon
  </button>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<!-- ================= HEADER ================= -->
<div class="header">
  <div class="header-content">
    <div></div>
    <div class="profile-name">
      Hi <?= htmlspecialchars($student['Stud_firstname'] ?? 'Student') ?>, Welcome to FKPARK!
      <span class="profile-icon">
        <i class="fas fa-user-circle"></i>
      </span>
    </div>
  </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

<div class="card">
<h2>Parking Availability</h2>

<!-- FILTER -->
<form method="GET" class="filter-bar">
    <select name="vehicle">
        <option value="">All Vehicles</option>
        <option value="car" <?= ($vehicleFilter=='car')?'selected':'' ?>>Car</option>
        <option value="motorcycle" <?= ($vehicleFilter=='motorcycle')?'selected':'' ?>>Motorcycle</option>
    </select>

    <select name="status">
        <option value="">All Status</option>
        <option value="available" <?= ($statusFilter=='available')?'selected':'' ?>>Available</option>
        <option value="unavailable" <?= ($statusFilter=='unavailable')?'selected':'' ?>>Unavailable</option>
    </select>

    <button type="submit">
        <i class="fas fa-filter"></i> Filter
    </button>
</form>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th>No.</th>
    <th>Vehicle</th>
    <th>Area</th>
    <th>Box</th>
    <th>Status</th>
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
    <td><?= ucfirst($p['vehicle_type']) ?></td>
    <td><?= htmlspecialchars($p['PS_Area']) ?></td>
    <td><?= htmlspecialchars($p['PS_box']) ?></td>
    <td class="<?= $p['PS_status']=='available' ? 'status-available' : 'status-unavailable' ?>">
        <?= ucfirst($p['PS_status']) ?>
    </td>
    <td>
        <?php if ($p['PS_status']=='available'): ?>
            <a class="book-btn" href="student_search_parking.php">
                Book
            </a>
        <?php else: ?>
            <button class="details-btn"
                onclick="showDetails(
                    '<?= htmlspecialchars($p['PS_Area']) ?>',
                    '<?= htmlspecialchars($p['PS_box']) ?>',
                    '<?= htmlspecialchars($p['PS_reason']) ?>',
                    '<?= $p['start_datetime'] ?>',
                    '<?= $p['end_datetime'] ?>'
                )">
                View Details
            </button>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; else: ?>
<tr>
    <td colspan="6" style="text-align:center;">No parking data found</td>
</tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>

<!-- ===== MODAL ===== -->
<div id="detailsModal" class="modal-overlay">
<div class="modal-card">
<h3>Parking Not Available</h3>
<p><strong>Area:</strong> <span id="modalArea"></span></p>
<p><strong>Box:</strong> <span id="modalBox"></span></p>
<p><strong>Reason:</strong> <span id="modalReason"></span></p>
<p><strong>From:</strong> <span id="modalFrom"></span></p>
<p><strong>Until:</strong> <span id="modalUntil"></span></p>
<button onclick="closeModal()">Close</button>
</div>
</div>

<script>
function showDetails(area, box, reason, from, until) {
    document.getElementById("modalArea").innerText = area;
    document.getElementById("modalBox").innerText = box;
    document.getElementById("modalReason").innerText = reason;
    document.getElementById("modalFrom").innerText = from;
    document.getElementById("modalUntil").innerText = until;
    document.getElementById("detailsModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("detailsModal").style.display = "none";
}
</script>

<script>
document.querySelectorAll('.dropdown-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    this.classList.toggle("active");
    let dropdown = this.nextElementSibling;
    dropdown.style.display =
      dropdown.style.display === "block" ? "none" : "block";
  });
});
</script>

</body>
</html>
