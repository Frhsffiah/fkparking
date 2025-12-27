<?php
session_start();
include("../php/config.php"); 

/* ================= AUTH CHECK ================= */
// Using your specific session variable 'Stud_id'
if (!isset($_SESSION['Stud_id'])) {
    header("Location: student_login_page.php");
    exit();
}

// Fetch student data
$sql = "SELECT * FROM student WHERE Stud_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['Stud_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Parking</title>
    <link rel="stylesheet" type="text/css" href="style/navbarstud.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    
    <style>
        /* Keeping your existing styles */
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; }
        
        /* Main Content Alignment to match Sidebar */
        .main-content {
            margin-left: 250px; /* Width of sidebar */
            padding: 40px;
        }

        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Center in the main-content area */
        }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        input[type="date"], input[type="time"] {
            width: 100%; padding: 10px; border: 1px solid #ccc;
            border-radius: 4px; box-sizing: border-box;
        }

        .button-group { text-align: right; margin-top: -10px; margin-bottom: 15px; }
        .btn-quick {
            padding: 5px 15px; background-color: #6c757d; color: white;
            border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em;
        }
        .btn-quick:hover { background-color: #5a6268; }

        .btn-search {
            width: 100%; padding: 12px; background-color: #007bff; color: white;
            border: none; border-radius: 5px; cursor: pointer; font-size: 16px;
        }
        .btn-search:hover { background-color: #0056b3; }

        /* Error Message Style */
        .error-msg {
            color: #dc3545; font-size: 0.9em; display: none; margin-top: 5px;
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
        <h1>Parking Booking</h1>
        
        <form id="bookingForm" action="student_select_slot.php" method="GET" onsubmit="return validateForm()">
            
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>
                <div id="dateError" class="error-msg">Date cannot be in the past.</div>
            </div>
            
            <div class="button-group">
                <button type="button" class="btn-quick" onclick="setDate('today')">Today</button>
                <button type="button" class="btn-quick" onclick="setDate('tomorrow')">Tomorrow</button>
            </div>

            <div class="form-group">
                <label for="startTime">Start Time:</label>
                <input type="time" id="startTime" name="startTime" required>
            </div>

            <div class="form-group">
                <label for="endTime">End Time:</label>
                <input type="time" id="endTime" name="endTime" required>
                <div id="timeError" class="error-msg">End time must be after start time.</div>
            </div>

            <button type="submit" class="btn-search">Search Parking</button>
        </form>
    </div>
</div>

<script>
    // Helper to set date
    function setDate(type) {
        const dateInput = document.getElementById('date');
        const today = new Date();
        
        if (type === 'tomorrow') {
            today.setDate(today.getDate() + 1);
        }
        
        // Format YYYY-MM-DD
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
    }

    // Main Validation Function
    function validateForm() {
        const dateInput = document.getElementById('date').value;
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;
        
        const dateError = document.getElementById('dateError');
        const timeError = document.getElementById('timeError');
        
        // Reset Styles
        dateError.style.display = 'none';
        timeError.style.display = 'none';
        let isValid = true;

        // 1. CHECK PAST DATES
        if (dateInput) {
            const selectedDate = new Date(dateInput);
            const today = new Date();
            today.setHours(0,0,0,0); // Remove time for accurate date comparison
            
            if (selectedDate < today) {
                dateError.innerText = "Error: You cannot book a date in the past.";
                dateError.style.display = 'block';
                isValid = false;
            }
        }

        // 2. CHECK TIME LOGIC
        if (startTime && endTime) {
            if (endTime <= startTime) {
                timeError.innerText = "Error: End Time must be after Start Time.";
                timeError.style.display = 'block';
                isValid = false;
            }
        }

        return isValid; // Form will not submit if this is false
    }
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