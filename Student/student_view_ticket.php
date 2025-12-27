<?php
session_start();
include("../php/config.php");

// 1. AUTH CHECK
// Must be logged in as SOMETHING (Student, Admin, or Staff)
if (!isset($_SESSION['User_id'])) {
    header("Location: ../public/login_page.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No Ticket ID provided.";
    exit();
}

$booking_id = $_GET['id'];
$user_type = $_SESSION['User_type'] ?? 'student'; // Default to student if missing
$stud_id = $_SESSION['Stud_id'] ?? 0;

// 2. DEFINE PERMISSIONS
// Admins and Staff can view ALL tickets. Students can only view THEIR OWN.
$can_view_all = in_array($user_type, ['admin', 'staff']);

// 3. BUILD SQL
$sql = "SELECT pb.*, ps.PS_box, ps.PS_Area, s.Stud_firstname, s.Stud_lastname, vr.Vehicle_brand, vr.Vehicle_color 
        FROM parkingbooking pb
        JOIN parkingspace ps ON pb.PS_id = ps.PS_id
        JOIN student s ON pb.Stud_id = s.Stud_id
        LEFT JOIN vehicle vr ON pb.Vehicle_regNo = vr.Vehicle_regNo
        WHERE pb.PB_id = ?";

// If they are NOT admin/staff (meaning they are a Student), add the ownership check
if (!$can_view_all) {
    $sql .= " AND pb.Stud_id = ?";
}

$stmt = $conn->prepare($sql);

if ($can_view_all) {
    // Admin/Staff only binds Booking ID
    $stmt->bind_param("i", $booking_id);
} else {
    // Student binds Booking ID AND Student ID
    $stmt->bind_param("ii", $booking_id, $stud_id);
}

$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    echo "<h2>Access Denied</h2><p>Ticket not found or you do not have permission to view it.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parking Ticket #<?= $ticket['PB_id'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Ticket font style */
            background-color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .ticket-container {
            background: #fff;
            width: 350px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: relative;
            border-top: 5px solid #007bff;
        }

        /* Dashed Line Effect */
        .dashed-line {
            border-bottom: 2px dashed #ccc;
            margin: 15px 0;
        }

        .header { text-align: center; margin-bottom: 20px; }
        .logo { font-size: 2rem; color: #333; font-weight: bold; }
        .sub-header { font-size: 0.9rem; color: #666; }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .label { font-weight: bold; color: #555; }
        .value { color: #000; }

        .status-badge {
            text-align: center;
            background: #28a745;
            color: white;
            padding: 5px;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }

        .barcode {
            text-align: center;
            margin-top: 20px;
            font-size: 2rem;
            letter-spacing: 5px;
            font-family: 'Libre Barcode 39', cursive; /* Optional google font */
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #333;
            color: white;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        .btn-print:hover { background: #555; }

        /* Hide button when printing */
        @media print {
            .btn-print { display: none; }
            body { background: white; }
            .ticket-container { box-shadow: none; border: 1px solid #000; }
        }
    </style>
</head>
<body>

<div class="ticket-container">
    <div class="header">
        <div class="logo"><i class="fas fa-parking"></i> FKPARK</div>
        <div class="sub-header">Official Booking Receipt</div>
    </div>

    <div class="status-badge">
        <?= strtoupper($ticket['PB_status']) ?>
    </div>

    <div class="info-row">
        <span class="label">Ticket ID:</span>
        <span class="value">#<?= str_pad($ticket['PB_id'], 6, '0', STR_PAD_LEFT) ?></span>
    </div>
    <div class="info-row">
        <span class="label">Student:</span>
        <span class="value"><?= htmlspecialchars($ticket['Stud_firstname']) ?></span>
    </div>
    <div class="info-row">
        <span class="label">Vehicle:</span>
        <span class="value"><?= htmlspecialchars($ticket['Vehicle_regNo']) ?></span>
    </div>

    <div class="dashed-line"></div>

    <div class="info-row">
        <span class="label">Area:</span>
        <span class="value">Zone <?= htmlspecialchars($ticket['PS_Area']) ?></span>
    </div>
    <div class="info-row">
        <span class="label" style="font-size:1.2rem;">SLOT:</span>
        <span class="value" style="font-size:1.2rem; font-weight:bold;"><?= htmlspecialchars($ticket['PS_box']) ?></span>
    </div>

    <div class="dashed-line"></div>

    <div class="info-row">
        <span class="label">Date:</span>
        <span class="value"><?= date("d M Y", strtotime($ticket['PB_date'])) ?></span>
    </div>
    <div class="info-row">
        <span class="label">Time:</span>
        <span class="value"><?= date("h:i A", strtotime($ticket['PB_startTime'])) ?> - <?= date("h:i A", strtotime($ticket['PB_endTime'])) ?></span>
    </div>

    <div class="barcode">
        ||||||||||||||||
    </div>
    <div style="text-align: center; font-size: 0.8rem;">
        <?= htmlspecialchars($ticket['PB_Qrcode']) ?>
    </div>

    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print Ticket</button>
    <button onclick="window.close()" class="btn-print" style="background:#dc3545; margin-top:10px;">Close</button>
</div>

</body>
</html>