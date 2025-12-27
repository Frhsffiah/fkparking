<?php
session_start();
include("../php/config.php");

// 2. AUTH CHECK
if (!isset($_SESSION['Stud_id'])) {
    header("Location: student_login_page.php");
    exit();
}

$stud_id = $_SESSION['Stud_id'];

// 3. FETCH STUDENT DATA (For Header)
$sql = "SELECT Stud_firstname FROM student WHERE Stud_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// 4. HANDLE CANCELLATION REQUEST
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    // Security Check: Ensure this booking actually belongs to the logged-in student
    $cancelSql = "UPDATE parkingbooking SET PB_status = 'Cancelled' 
                  WHERE PB_id = ? AND Stud_id = ?";
    $cancelStmt = $conn->prepare($cancelSql);
    $cancelStmt->bind_param("ii", $booking_id, $stud_id);
    
    if ($cancelStmt->execute()) {
        echo "<script>alert('Booking cancelled successfully.'); window.location.href='student_bookings.php';</script>";
    } else {
        echo "<script>alert('Error cancelling booking.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Bookings</title>
    <link rel="stylesheet" href="style/navbarstud.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; }
        
        .main-content {
            margin-left: 250px;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem; font-weight: bold; color: #333;
            margin-bottom: 15px; border-left: 5px solid #007bff; padding-left: 10px;
        }

        /* CARD STYLES */
        .booking-card {
            background: #fff; border-radius: 8px; padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
            border-left: 5px solid #ccc; /* Default border */
        }

        /* Status Colors */
        .status-pending { border-left-color: #ffc107; } /* Yellow */
        .status-approved { border-left-color: #28a745; } /* Green */
        .status-cancelled { border-left-color: #dc3545; } /* Red */
        .status-completed { border-left-color: #6c757d; } /* Grey */

        .booking-info h3 { margin: 0 0 5px 0; color: #333; }
        .booking-info p { margin: 3px 0; color: #666; font-size: 0.95rem; }
        .booking-info .badge {
            display: inline-block; padding: 4px 8px; border-radius: 4px;
            font-size: 0.8rem; font-weight: bold; color: white;
            margin-top: 5px;
        }
        
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-approved { background-color: #28a745; }
        .badge-cancelled { background-color: #dc3545; }
        .badge-success { background-color: #28a745; }

        .action-area {
            display: flex;             /* Forces items to sit in a row */
            align-items: center;       /* Centers them vertically */
            justify-content: flex-end; /* Pushes them to the right */
            gap: 10px;                 /* Adds space between the buttons */
        }
        .action-area form {
            margin: 0;
        }
        
        .btn-cancel {
            background-color: #dc3545; color: white; border: none;
            padding: 8px 15px; border-radius: 5px; cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-cancel:hover { background-color: #c82333; }

        .no-data {
            text-align: center; color: #888; padding: 30px;
            background: #fff; border-radius: 8px;
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

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>My Bookings</h1>
            <button onclick="location.href='student_search_parking.php'" style="padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;">
                <i class="fas fa-plus"></i> New Booking
            </button>
        </div>

        <div class="section-title">Upcoming & Active</div>
        
        <?php
        $activeSql = "
            SELECT pb.*, ps.PS_box, ps.PS_Area 
            FROM parkingbooking pb
            JOIN parkingspace ps ON pb.PS_id = ps.PS_id
            WHERE pb.Stud_id = ? 
            AND pb.PB_status IN ('Pending', 'Approved', 'Success')
            ORDER BY pb.PB_date ASC, pb.PB_startTime ASC";
            
        $stmt = $conn->prepare($activeSql);
        $stmt->bind_param("i", $stud_id);
        $stmt->execute();
        $activeResult = $stmt->get_result();

        if ($activeResult->num_rows > 0) {
            while ($row = $activeResult->fetch_assoc()) {
                $statusClass = 'status-' . strtolower($row['PB_status']);
                $badgeClass = 'badge-' . strtolower($row['PB_status']);
                $formattedDate = date("d M Y", strtotime($row['PB_date']));
                $formattedStart = date("h:i A", strtotime($row['PB_startTime']));
                $formattedEnd = date("h:i A", strtotime($row['PB_endTime']));
                ?>
                
                <div class="booking-card <?= $statusClass ?>">
                    <div class="booking-info">
                        <h3>Slot <?= htmlspecialchars($row['PS_box']) ?> <span style="font-size:0.8em; color:#888;">(Area <?= $row['PS_Area'] ?>)</span></h3>
                        <p><i class="far fa-calendar-alt"></i> <?= $formattedDate ?></p>
                        <p><i class="far fa-clock"></i> <?= $formattedStart ?> - <?= $formattedEnd ?></p>
                        <p><i class="fas fa-car"></i> <?= htmlspecialchars($row['Vehicle_regNo']) ?></p>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['PB_status']) ?></span>
                        
                        <?php if($row['PB_status'] == 'Pending'): ?>
                            <!-- <p style="color:#d9534f; font-size:0.8rem; margin-top:5px;">
                                * Scan QR at slot within 10 mins of arrival.
                            </p> -->
                        <?php endif; ?>
                    </div>
                    
                    <div class="action-area">
                        <?php if (in_array($row['PB_status'], ['Pending', 'Approved'])): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                <input type="hidden" name="booking_id" value="<?= $row['PB_id'] ?>">
                                <button type="submit" name="cancel_booking" class="btn-cancel">Cancel</button>
                            </form>
                            <a href="student_view_ticket.php?id=<?= $row['PB_id'] ?>" 
   target="_blank"
   style="display:inline-block; padding:8px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px; margin-right:5px;">
   <i class="fas fa-receipt"></i> Ticket
</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
            }
        } else {
            echo "<div class='no-data'>No active bookings found.</div>";
        }
        ?>

        <br>
        <div class="section-title">History</div>

        <?php
        // Fetch Past/Cancelled Bookings
        $historySql = "
            SELECT pb.*, ps.PS_box 
            FROM parkingbooking pb
            JOIN parkingspace ps ON pb.PS_id = ps.PS_id
            WHERE pb.Stud_id = ? 
            AND pb.PB_status NOT IN ('Pending', 'Approved', 'Success')
            ORDER BY pb.PB_date DESC, pb.PB_startTime DESC
            LIMIT 5"; // Limit to last 5 for cleanliness
            
        $stmt = $conn->prepare($historySql);
        $stmt->bind_param("i", $stud_id);
        $stmt->execute();
        $historyResult = $stmt->get_result();

        if ($historyResult->num_rows > 0) {
            while ($row = $historyResult->fetch_assoc()) {
                $statusClass = 'status-' . strtolower($row['PB_status']);
                $badgeClass = 'badge-' . strtolower($row['PB_status']);
                ?>
                <div class="booking-card <?= $statusClass ?>" style="opacity: 0.7;">
                    <div class="booking-info">
                        <h3>Slot <?= htmlspecialchars($row['PS_box']) ?></h3>
                        <p><?= date("d M Y", strtotime($row['PB_date'])) ?></p>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['PB_status']) ?></span>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='no-data'>No history found.</div>";
        }
        ?>

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