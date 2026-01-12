<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include("../php/config.php");

/* ===== SECURITY CHECK ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

$userId = $_SESSION['User_id'];

/* ===== FETCH STUDENT'S OWN TRAFFIC SUMMONS ===== */
$stmt = $conn->prepare("
    SELECT 
        ts.Summon_id,
        ts.Summon_issueDate,
        ts.Summon_issueTime,
        ts.Summon_violationType,
        ts.Summon_point,
        ts.Vehicle_regNo,
        ts.Summon_status
    FROM trafficsummon ts
    WHERE ts.User_id = ?
    ORDER BY ts.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    /* ===== AUTO MARK OVERDUE ===== */
    $issueDate = strtotime($row['Summon_issueDate']);
    if ($row['Summon_status'] == 'Unpaid' && (time() - $issueDate) > 3*24*60*60) {
        $update = $conn->prepare("UPDATE trafficsummon SET Summon_status='Overdue' WHERE Summon_id=?");
        $update->bind_param("i", $row['Summon_id']);
        $update->execute();
        $row['Summon_status'] = 'Overdue';
    }

    $rows[] = $row;
}
$total_rows = count($rows);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Traffic Summons</title>

<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body { 
    font-family: Arial, sans-serif; 
    background: #f2f6fc; 
    margin: 0; 
}

.main-content { 
    margin-left: 250px; 
    padding: 40px; 
    width: calc(100% - 250px); 
    max-width: none; 
}

.card { 
    background: #fff; 
    padding: 15px 20px; 
    border-radius: 12px; 
    box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
    max-width: 90%; 
}

.page-title { 
    color: #1565c0; 
    margin-bottom: 20px; 
}

table { 
    width: 100%; 
    border-collapse: collapse; 
}

th, td { 
    padding: 14px 16px; 
    border-bottom: 1px solid #e0e0e0; 
    text-align: left; 
}

th { 
    background: #1565c0; 
    color: #fff; 
    font-weight: 600; 
}

tr:hover { 
    background: #e3f2fd; 
    cursor: pointer; 
}

.badge { 
    padding: 5px 12px; 
    border-radius: 20px; 
    font-size: 13px; 
    background: #bbdefb; 
    color: #0d47a1; 
    font-weight: 600; 
}

.badge.Unpaid { 
    background-color: #f44336; 
    color: #fff; 
}

.badge.Paid { 
    background-color: #4caf50; 
    color: #fff; 
}

.badge.Overdue { 
    background-color: #ff9800; 
    color: #fff; 
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
  <button class="dropdown-btn" id="tsBtn">
    <span class="icon-text">
      <i class="fas fa-receipt"></i>
      <span>Traffic Summon</span>
    </span>
    <span class="dropdown-arrow">&#9654;</span>
  </button>

  <div class="dropdown-containers" id="tsMenu">
    <a href="student_traffic_summon_list.php">
      <i class="fas fa-list"></i> My Summons
    </a>
  </div>


  <button class="button" id="logout-button"
          onclick="location.href='../public/logout_page.php'">
    <i class="fas fa-sign-out-alt"></i> Logout
  </button>

</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="card">
    <h2 class="page-title"><i class="fas fa-file-invoice"></i> My Traffic Summons</h2>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Time</th>
          <th>Vehicle No</th>
          <th>Violation</th>
          <th>Points</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php $i = $total_rows; ?>
          <?php foreach ($rows as $row): ?>
          <tr onclick="location.href='student_view_traffic_summon.php?id=<?= $row['Summon_id'] ?>'">
            <td><?= $i-- ?></td>
            <td><?= date("d/m/Y", strtotime($row['Summon_issueDate'])) ?></td>
            <td><?= htmlspecialchars($row['Summon_issueTime']) ?></td>
            <td><?= htmlspecialchars($row['Vehicle_regNo']) ?></td>
            <td><?= htmlspecialchars($row['Summon_violationType']) ?></td>
            <td><span class="badge"><?= $row['Summon_point'] ?> pts</span></td>
            <td><span class="badge <?= $row['Summon_status'] ?>"><?= $row['Summon_status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No traffic summons found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll(".dropdown-btn").forEach(btn => {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");
        const menu = this.nextElementSibling;
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });
});
</script>

</body>
</html>
