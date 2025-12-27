<?php
session_start();
include("../php/config.php");

// 1. AUTH CHECK
if (!isset($_SESSION['Stud_id'])) {
    header("Location: student_login_page.php");
    exit();
}

$stud_id = $_SESSION['Stud_id'];

// 2. GET SEARCH PARAMETERS
// We use '??' to provide a default empty string if the value is missing
$date = $_GET['date'] ?? '';
$start = $_GET['startTime'] ?? ''; // Ensure this matches the name in your form (startTime)
$end = $_GET['endTime'] ?? '';     // Ensure this matches the name in your form (endTime)

// Basic validation: If data is missing, redirect back
if (!$date || !$start || !$end) {
    echo "<script>alert('Please select a date and time first.'); window.location.href='student_search_parking.php';</script>";
    exit();
}

// ==========================================================
// CORRECTIVE MAINTENANCE: "Set booking limit at a time to one only"
// ==========================================================
// Check if this student already has an active booking (Pending or Approved) for the future
$checkLimit = $conn->prepare("
    SELECT PB_id FROM parkingbooking 
    WHERE Stud_id = ? 
    AND PB_status IN ('Pending', 'Approved', 'Success') 
    AND (PB_date > CURDATE() OR (PB_date = CURDATE() AND PB_endTime > CURTIME()))
");
$checkLimit->bind_param("i", $stud_id);
$checkLimit->execute();
$checkLimit->store_result();

if ($checkLimit->num_rows > 0) {
    // If they already have a booking, stop them here.
    echo "<div class='main-content'><div class='container'><div class='alert-box error'>
          <h3 style='color: #dc3545;'><i class='fas fa-exclamation-circle'></i> Booking Limit Reached</h3>
          <p>You already have an active booking. You can only make one booking at a time.</p>
          <a href='student_bookings.php' class='btn-back'>View My Bookings</a>
          </div></div></div>";
    exit();
}
// ==========================================================

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Parking Slot</title>
    <link rel="stylesheet" href="style/navbarstud.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content { margin-left: 250px; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-info {
            background: #fff; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Grid for Parking Slots */
        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .slot-card {
            background: white; border-radius: 10px; padding: 20px;
            text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s; border-left: 5px solid #28a745; /* Green for Available */
        }
        .slot-card:hover { transform: translateY(-5px); }

        .slot-header { font-size: 1.2rem; font-weight: bold; color: #333; margin-bottom: 5px; }
        .slot-area { color: #666; font-size: 0.9rem; margin-bottom: 15px; }
        .vehicle-icon { font-size: 2rem; color: #28a745; margin-bottom: 15px; }

        .btn-book {
            background-color: #007bff; color: white; padding: 10px 20px;
            text-decoration: none; border-radius: 5px; display: inline-block;
            font-size: 0.9rem; border: none; cursor: pointer; width: 100%;
        }
        .btn-book:hover { background-color: #0056b3; }

        .alert-box {
            background: #fff; padding: 30px; border-radius: 8px; text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .alert-box.error { border-top: 5px solid #dc3545; }
        .btn-back {
            display: inline-block; margin-top: 15px; padding: 10px 20px;
            background: #6c757d; color: white; text-decoration: none; border-radius: 4px;
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

<div class="main-content">
    <div class="container">
        
        <div class="header-info">
            <div>
                <h2>Select a Parking Spot</h2>
                <p>
                    <i class="far fa-calendar-alt"></i> Date: <strong><?= htmlspecialchars($date) ?></strong> | 
                    <i class="far fa-clock"></i> Time: <strong><?= htmlspecialchars($start) ?> - <?= htmlspecialchars($end) ?></strong>
                </p>
            </div>
            <a href="student_search_parking.php" class="btn-back" style="margin:0;">Change Time</a>
        </div>

        <div class="slot-grid">
            <?php
            // ==========================================================
            // LOGIC CHANGE: SHOW ALL, BUT MARK BOOKED AS UNAVAILABLE
            // ==========================================================
            
            // We use a LEFT JOIN. 
            // 1. Get ALL parking spaces (ps)
            // 2. Try to join a booking (pb) that overlaps with our requested time.
            // 3. If the join is successful (pb.PB_id is NOT NULL), it means the slot is TAKEN.
            
            $sql = "
                SELECT ps.*, 
                (
                    SELECT COUNT(*) 
                    FROM parkingbooking pb 
                    WHERE pb.PS_id = ps.PS_id 
                    AND pb.PB_date = ? 
                    AND pb.PB_status IN ('Approved', 'Pending', 'Success') 
                    AND NOT (pb.PB_endTime <= ? OR pb.PB_startTime >= ?)
                ) as active_bookings
                FROM parkingspace ps
                ORDER BY ps.PS_Area, ps.PS_box
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $date, $start, $end);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    
                    // 1. Check if Admin marked it as Maintenance/Unavailable
                    $isMaintenance = (strtolower($row['PS_status']) !== 'available');

                    // 2. Check if Student booked it
                    $isBooked = ($row['active_bookings'] > 0);

                    // Combined Unavailable Status
                    $isUnavailable = ($isMaintenance || $isBooked);

                    // UI Setup
                    $cardClass = $isUnavailable ? 'slot-card unavailable' : 'slot-card';
                    
                    // Icon Setup
                    $type = isset($row['vehicle_type']) ? $row['vehicle_type'] : 'Car';
                    $icon = ($type == 'motorcycle') ? 'fa-motorcycle' : 'fa-car';
                    ?>
                    
                    <div class="<?= $cardClass ?>">
                        <div class="vehicle-icon"><i class="fas <?= $icon ?>"></i></div>
                        <div class="slot-header"><?= htmlspecialchars($row['PS_box']) ?></div>
                        <div class="slot-area">Area: <?= htmlspecialchars($row['PS_Area']) ?></div>
                        
                        <?php if ($isUnavailable): ?>
                            <?php $reason = $isMaintenance ? "Maintenance" : "Booked"; ?>
                            <button class="btn-disabled" disabled><?= $reason ?></button>
                        <?php else: ?>
                            <form action="student_process_booking.php" method="POST">
                                <input type="hidden" name="ps_id" value="<?= $row['PS_id'] ?>">
                                <input type="hidden" name="date" value="<?= $date ?>">
                                <input type="hidden" name="start_time" value="<?= $start ?>">
                                <input type="hidden" name="end_time" value="<?= $end ?>">
                                <button type="submit" class="btn-book" onclick="return confirm('Confirm booking for <?= $row['PS_box'] ?>?')">Book Now</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php
                }
            } else {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 40px;'>
                        <i class='fas fa-times-circle' style='font-size: 3rem; color: #ccc; margin-bottom: 20px;'></i>
                        <h3 style='color: #666;'>No Slots Available</h3>
                        <p style='color: #888;'>All parking slots are booked for this time range.<br>Please try a different time.</p>
                      </div>";
            }
            ?>
        </div>
    </div>
</div>
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