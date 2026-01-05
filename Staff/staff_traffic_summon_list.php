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

/* ===== FETCH TRAFFIC SUMMONS ===== */
// Fetch in descending order for newest first
$stmt = $conn->prepare("
    SELECT 
        ts.Summon_id,
        ts.Summon_issueDate,
        ts.Summon_issueTime,
        ts.Summon_violationType,
        ts.Summon_point,
        ts.Vehicle_regNo,
        u.User_username
    FROM trafficsummon ts
    JOIN vehicle v ON ts.Vehicle_regNo = v.Vehicle_regNo
    JOIN `user` u ON ts.User_id = u.User_id
    ORDER BY ts.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

/* ===== DELETE FUNCTIONALITY ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $del_stmt = $conn->prepare("DELETE FROM trafficsummon WHERE Summon_id = ?");
    $del_stmt->bind_param("i", $delete_id);
    if ($del_stmt->execute()) {
        echo "<script>alert('Summon deleted successfully'); window.location.href='staff_traffic_summon_list.php';</script>";
    } else {
        echo "<script>alert('Error deleting summon: {$del_stmt->error}');</script>";
    }
    $del_stmt->close();
}

/* ===== FETCH DATA INTO ARRAY ===== */
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$total_rows = count($rows);
$result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Traffic Summon List</title>

<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #1b5e20;
    color: #fff;
    font-weight: 600;
}

tr:hover {
    background: #e0f2f1;
    transition: background 0.2s;
}

.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 13px;
    background: #ffeb3b;
}

.btn-action {
    margin-right: 5px;
    padding: 4px 8px;
    font-size: 13px;
    border-radius: 4px;
    text-decoration: none;
    color: #fff;
    border: none;
    cursor: pointer;
}
.btn-edit { background-color: #007bff; }
.btn-delete { background-color: #dc3545; }

.clickable-row {
    cursor: pointer;
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

        <!-- User & Vehicle -->
        <button class="dropdown-btn">
            <span><i class="fas fa-users"></i> User & Vehicle Registration</span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>
        <div class="dropdown-containers">
            <a href="staff_approve_vec.php"><i class="fas fa-car"></i> Vehicle Approval</a>
            <a href="staff_profile.php"><i class="fas fa-user"></i> User Profiles</a>
        </div>

        <!-- Parking -->
        <button class="dropdown-btn">
            <span><i class="fas fa-parking"></i> Parking Spaces</span>
            <span class="dropdown-arrow">&#9654;</span>
        </button>
        <div class="dropdown-containers">
            <a href="staff_parking_availability.php"><i class="fas fa-list"></i> Parking Availability</a>
        </div>

        <a href="staff_bookings.php">
            <i class="fas fa-list"></i> Bookings
        </a>

        <!-- Traffic Summon -->
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
<div class="main-content" style="margin-left:250px; padding:40px;">
    <div class="card" style="
        background:#fff;
        padding:30px;
        border-radius:12px;
        box-shadow:0 4px 10px rgba(0,0,0,0.08);
    ">
        <h2 style="color:#1b5e20; margin-bottom:20px;">
            <i class="fas fa-file-invoice"></i> Traffic Summon List
        </h2>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Vehicle No</th>
                    <th>User</th>
                    <th>Violation</th>
                    <th>Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php 
                    $i = $total_rows; // start numbering from total rows
                    foreach ($rows as $row):
                    ?>
                        <tr class="clickable-row" data-href="staff_view_traffic_summon.php?id=<?= $row['Summon_id'] ?>">
                            <td><?= $i-- ?></td>
                            <td><?= date("d/m/Y", strtotime($row['Summon_issueDate'])) ?></td>
                            <td><?= htmlspecialchars($row['Summon_issueTime']) ?></td>
                            <td><?= htmlspecialchars($row['Vehicle_regNo']) ?></td>
                            <td><?= htmlspecialchars($row['User_username']) ?></td>
                            <td><?= htmlspecialchars($row['Summon_violationType']) ?></td>
                            <td><span class="badge"><?= $row['Summon_point'] ?> pts</span></td>
                            <td>
                                <a href="staff_edit_traffic_summon.php?id=<?= $row['Summon_id'] ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this summon?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['Summon_id'] ?>">
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No traffic summons found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
document.querySelectorAll('.clickable-row').forEach(row => {
    row.addEventListener('click', function(e) {
        // Prevent click if clicked on Edit button or Delete form
        if (!e.target.closest('.btn-action') && !e.target.closest('form')) {
            window.location.href = this.dataset.href;
        }
    });
});
</script>

</body>
</html>