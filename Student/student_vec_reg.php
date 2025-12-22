<?php
session_start();
include("../php/config.php");

/* ===== SECURITY ===== */
if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'student') {
    header("Location: ../public/login_page.php");
    exit();
}

$userId = $_SESSION['User_id'];

/* ===== FETCH STUDENT NAME ===== */
$stmtStud = $conn->prepare("
    SELECT Stud_firstname 
    FROM student 
    WHERE User_id = ?
");
$stmtStud->bind_param("i", $userId);
$stmtStud->execute();
$student = $stmtStud->get_result()->fetch_assoc();

/* ===== FETCH VEHICLE (IF EXISTS) ===== */
$stmt = $conn->prepare("
    SELECT Vehicle_regNo, Document_status
    FROM vehicle
    WHERE User_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();

/* ===== DECISION ===== */
$canRegister = true;
$message = "";

if ($vehicle) {
    if ($vehicle['Document_status'] === 'Pending') {
        $canRegister = false;
        $message = "Your vehicle registration is currently pending verification.";
    }
    elseif ($vehicle['Document_status'] === 'Verified') {
        $canRegister = false;
        $message = "Your vehicle registration has already been approved.";
    }
    elseif ($vehicle['Document_status'] === 'Rejected') {
        $message = "Your registration was rejected. Please resubmit your document.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Vehicle Registration</title>
<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.form-group{margin-bottom:18px;}
.form-group label{font-weight:600;margin-bottom:6px;display:block;}
.form-control{
    width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;
}
.btn-primary{
    background:#1e90ff;border:none;padding:10px 22px;border-radius:6px;
    color:#fff;font-weight:600;
}
.alert{
    background:#fff3cd;border-left:5px solid #ffc107;
    padding:15px;border-radius:6px;margin-bottom:20px;
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
    <a href="student_my_parking.php">
      <i class="fas fa-car-side"></i> My Parking
    </a>
    </div>
    

  <!-- Booking -->
  <button class="button">
    <i class="fas fa-calendar-check"></i> Booking
  </button>

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
<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="card">

    <h2 style="color:#2a2a72;margin-bottom:20px;">
      <i class="fas fa-car"></i> Vehicle Registration
    </h2>

    <?php if ($message): ?>
      <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($canRegister): ?>
    <form action="student_process_reg.php" method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>Vehicle Registration Number</label>
        <input type="text" name="vehicle_reg_no" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Vehicle Type</label>
        <select name="vehicle_type" id="vehicleType" class="form-control" required>
          <option value="">-- Select Type --</option>
          <option value="Car">Car</option>
          <option value="Motorcycle">Motorcycle</option>
        </select>
      </div>

      <div class="form-group">
        <label>Vehicle Brand</label>
        <select name="vehicle_brand" id="vehicleBrand" class="form-control" required>
          <option value="">-- Select Brand --</option>
        </select>
      </div>

      <div class="form-group">
        <label>Vehicle Color</label>
        <input type="text" name="vehicle_color" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Vehicle Document (PDF / JPG / PNG)</label>
        <input type="file" name="vehicle_document" class="form-control" required>
      </div>

      <button type="submit" class="btn-primary">
        <i class="fas fa-save"></i> Submit
      </button>

    </form>
    <?php endif; ?>

  </div>
</div>

<script>
/* BRAND AUTO LOAD */
const brands = {
  Car: ["Proton","Perodua","BMW","Mercedes-Benz","Toyota","Honda","Nissan","Mazda","Mitsubishi","Hyundai","Kia","Audi","Volvo","BYD","Tesla","Chery","Ford","Jeep","Lotus","MINI","Peugeot","Porsche","Rolls-Royce","Suzuki","Volkswagen","Haval"],
  Motorcycle: ["Modenas","Yamaha","Benelli","Honda","Keeway","Kawasaki","Vespa","SYM"]
};

document.getElementById("vehicleType")?.addEventListener("change", function(){
  let brandSelect = document.getElementById("vehicleBrand");
  brandSelect.innerHTML = '<option value="">-- Select Brand --</option>';
  brands[this.value]?.forEach(b => {
    brandSelect.innerHTML += `<option value="${b}">${b}</option>`;
  });
});
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
