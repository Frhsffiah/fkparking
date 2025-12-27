<?php
session_start();
include("../php/config.php");

// 1. AUTH CHECK
if (!isset($_SESSION['Stud_id'])) {
    header("Location: student_login_page.php");
    exit();
}

// 2. CHECK IF FORM WAS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // If someone tries to access this file directly without submitting the form, kick them back
    header("Location: student_search_parking.php");
    exit();
}

$stud_id = $_SESSION['Stud_id'];
$ps_id = $_POST['ps_id'];
$date = $_POST['date'];
$start = $_POST['start_time'];
$end = $_POST['end_time'];

// ==========================================================
// MAINTENANCE CHECK 1: BOOKING LIMIT (Server Side Safety)
// ==========================================================
// We check this again here just in case the user tried to bypass the previous page.
$checkLimit = $conn->prepare("
    SELECT PB_id FROM parkingbooking 
    WHERE Stud_id = ? 
    AND PB_status IN ('Pending', 'Approved', 'Success') 
    AND (PB_date > CURDATE() OR (PB_date = CURDATE() AND PB_endTime > CURTIME()))
");
$checkLimit->bind_param("i", $stud_id);
$checkLimit->execute();

if ($checkLimit->get_result()->num_rows > 0) {
    echo "<script>
            alert('Booking Failed: You already have an active booking. You can only hold one booking at a time.');
            window.location.href = 'student_bookings.php';
          </script>";
    exit();
}

// ==========================================================
// MAINTENANCE CHECK 2: FETCH APPROVED VEHICLE
// ==========================================================
// Automation: We automatically find the student's approved vehicle so they don't have to type it.
// We look for 'Approved' or 'Success' status in the vehicle_registration table.
// NEW CORRECTED CODE (Matches your images)
// 1. We need the User_id, not Stud_id, because vehicle_registration uses User_id
$userSql = "SELECT User_id FROM student WHERE Stud_id = ?";
$uStmt = $conn->prepare($userSql);
$uStmt->bind_param("i", $stud_id);
$uStmt->execute();
$userRow = $uStmt->get_result()->fetch_assoc();
$real_user_id = $userRow['User_id'];

// 2. Fetch Vehicle using User_id and correct column names
$vehSql = "SELECT Vehicle_regNo FROM vehicle
           WHERE User_id = ? AND Document_status IN ('Verified', 'Success') 
           LIMIT 1";
$vehStmt = $conn->prepare($vehSql);
$vehStmt->bind_param("i", $real_user_id);
$vehStmt->execute();
$vehResult = $vehStmt->get_result();

if ($vehResult->num_rows === 0) {
    echo "<script>
            alert('Booking Failed: You do not have an approved vehicle yet. Please register your vehicle first.');
            window.location.href = 'student_vec_reg.php';
          </script>";
    exit();
}
$vehicle = $vehResult->fetch_assoc();
$plate_no = $vehicle['Vehicle_regNo'];

// ==========================================================
// MAINTENANCE CHECK 3: RACE CONDITION PREVENTION
// ==========================================================
// If two students click 'Book' on the same slot at the exact same second, this stops the second one.
$checkSql = "SELECT PB_id FROM parkingbooking 
             WHERE PS_id = ? 
             AND PB_date = ? 
             AND PB_status IN ('Approved', 'Pending', 'Success')
             AND NOT (PB_endTime <= ? OR PB_startTime >= ?)";

$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("isss", $ps_id, $date, $start, $end);
$checkStmt->execute();

if ($checkStmt->get_result()->num_rows > 0) {
    echo "<script>
            alert('Sorry! This slot was just taken by another user a moment ago. Please select a different slot.');
            window.location.href = 'student_search_parking.php';
          </script>";
    exit();
}

// ==========================================================
// FINAL STEP: INSERT BOOKING
// ==========================================================
// Status is 'Pending' because they must scan the QR code upon arrival (Requirement 2).
$status = 'Pending'; 
$qr_string = "FKPARK-" . $stud_id . "-" . time(); // Generates a unique string for the QR code

$insertSql = "INSERT INTO parkingbooking 
              (PB_date, PB_startTime, PB_endTime, PB_Qrcode, PB_status, Stud_id, Vehicle_regNo, PS_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insertSql);
// sssssisi means: string, string, string, string, string, int, string, int
$stmt->bind_param("sssssisi", $date, $start, $end, $qr_string, $status, $stud_id, $plate_no, $ps_id);

if ($stmt->execute()) {
    // Success! Redirect to My Bookings page
    echo "<script>
            alert('Booking Successful! Please remember to scan the QR code at the parking slot within 10 minutes of your arrival time.');
            window.location.href = 'student_bookings.php';
          </script>";
} else {
    echo "System Error: " . $stmt->error;
}
?>