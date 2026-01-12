<?php
session_start();
include("../php/config.php");

// 1. AUTH CHECK (Admin OR Staff Only)
if (!isset($_SESSION['User_id']) || !in_array($_SESSION['User_type'], ['admin', 'staff'])) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['User_id'];

// 2. FETCH ADMIN DATA (Fixes "Undefined variable $admin")
// We need this so the Header can say "Hi Zahirah Amani"
$adminSql = "SELECT * FROM admins WHERE User_id = ?";
$stmt = $conn->prepare($adminSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// 3. FILTER LOGIC FOR TABLE
$status_filter = $_GET['status'] ?? 'All';
$sql = "SELECT pb.*, s.Stud_firstname, s.Stud_lastname, s.Stud_id, ps.PS_box, ps.PS_Area 
        FROM parkingbooking pb
        JOIN student s ON pb.Stud_id = s.Stud_id
        JOIN parkingspace ps ON pb.PS_id = ps.PS_id";

if ($status_filter != 'All') {
    $sql .= " WHERE pb.PB_status = '" . $conn->real_escape_string($status_filter) . "'";
}

$sql .= " ORDER BY pb.PB_date DESC, pb.PB_startTime DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="style/navbaradmin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Specific Styles for the Content Area */
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; }
        
        .main-content {
            margin-left: 250px; /* Pushes content right to make room for sidebar */
            padding: 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Filter & Header Styles */
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .filter-group select { padding: 8px; border-radius: 5px; border: 1px solid #ccc; }
        .btn-go { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #343a40; color: white; }
        tr:hover { background: #f1f1f1; }

        /* Badge Styles */
        .badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.8rem; font-weight: bold; }
        .bg-pending { background: #ffc107; color: #333; }
        .bg-approved { background: #28a745; }
        .bg-success { background: #17a2b8; }
        .bg-cancelled { background: #dc3545; }

        .btn-ticket { padding: 5px 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; }
        .btn-ticket:hover { background: #5a6268; }
        .role-badge { background: #007bff; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8rem; vertical-align: middle; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidenav">
    <div class="logo-container">
        <img src="../uploads/fkparkLogo.jpg" class="logo">
    </div>

    <button onclick="location.href='admin_dashboard.php'">
        <i class="fas fa-home"></i> Dashboard
    </button>

    <button class="dropdown-btn" id="uvBtn">
            <span>
                <i class="fas fa-users"></i> User & Vehicle Registration
            </span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>

    <div class="dropdown-container">
        <a href="admin_reg_student.php">User Registration</a>
        <a href="admin_view_reg.php">List Registration</a>
        <a href="admin_profile.php">User Profile</a>
    </div>

    <button class="dropdown-btn" id="psBtn">
        <span>
        <i class="fas fa-parking"></i> Parking Spaces
        </span>
        <span class="dropdown-arrow">&#9654;</span>
    </button>

    <div class="dropdown-container">
        <a href="admin_list_parking.php">Parking List</a>
        <a href="admin_add_parking.php">Add Parking</a>
    </div>

    <button onclick="location.href='admin_bookings.php'">
        <i class="fas fa-list"></i> Bookings
    </button>

    <div class="logout">
        <button onclick="location.href='../public/logout_page.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
    
</div>

<!-- HEADER -->
<div class="header">
    <div class="header-content">
        <div></div>
        <div class="profile-name">
            Hi <?= htmlspecialchars($admin['Admin_firstname']) ?>, Welcome to FKPARK!
            <span class="profile-icon"><i class="fas fa-user-circle"></i></span>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container">
        <div class="header-flex">
            <h2>
                <i class="fas fa-tasks"></i> Manage Bookings 
                <span class="role-badge"><?= ucfirst($_SESSION['User_type']) ?> View</span>
            </h2>
            
            <form method="GET" class="filter-group">
                <select name="status">
                    <option value="All">All Status</option>
                    <option value="Pending" <?= $status_filter=='Pending'?'selected':'' ?>>Pending</option>
                    <option value="Success" <?= $status_filter=='Success'?'selected':'' ?>>Success</option>
                    <option value="Cancelled" <?= $status_filter=='Cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn-go">Filter</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Slot</th>
                    <th>Date / Time</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                    <th>Proof</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            $statusClass = 'bg-' . strtolower($row['PB_status']);
                            $timeStr = date("h:i A", strtotime($row['PB_startTime'])) . " - " . date("h:i A", strtotime($row['PB_endTime']));
                        ?>
                        <tr>
                            <td>#<?= $row['PB_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['Stud_firstname']) ?></strong><br>
                                <small style="color:#666;">ID: <?= $row['Stud_id'] ?></small>
                            </td>
                            <td>
                                <span style="font-weight:bold; color:#007bff;"><?= $row['PS_box'] ?></span><br>
                                <small>(Area <?= $row['PS_Area'] ?>)</small>
                            </td>
                            <td>
                                <?= date("d M Y", strtotime($row['PB_date'])) ?><br>
                                <small><?= $timeStr ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['Vehicle_regNo']) ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $row['PB_status'] ?></span></td>
                            <td>
                                <a href="../Student/student_view_ticket.php?id=<?= $row['PB_id'] ?>" target="_blank" class="btn-ticket">
                                    <i class="fas fa-file-alt"></i> View Ticket
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Dropdown Script
document.querySelectorAll('.dropdown-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    this.classList.toggle("active");
    var dropdownContent = this.nextElementSibling;
    if (dropdownContent.style.display === "block") {
      dropdownContent.style.display = "none";
    } else {
      dropdownContent.style.display = "block";
    }
  });
});
</script>

</body>
</html>