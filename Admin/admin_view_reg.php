<?php
session_start();
include '../php/config.php';

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

// Admin name
$stmt = $conn->prepare("SELECT Admin_firstname FROM admins WHERE User_id=?");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Level filter
$level = $_GET['level'] ?? 'Undergraduate';

$stmt = $conn->prepare("
    SELECT Stud_id, Stud_firstname, Stud_lastname, Stud_matricNum, Stud_level
    FROM student
    WHERE Stud_level = ?
    ORDER BY Stud_firstname
");
$stmt->bind_param("s", $level);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Students</title>
    <link rel="stylesheet" href="style/navbaradmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="card">
        <h2>Registered Students</h2>

        <div class="tabs">
            <a href="?level=Undergraduate" class="<?= $level=='Undergraduate'?'active':'' ?>">
                Undergraduate
            </a>
            <a href="?level=Postgraduate" class="<?= $level=='Postgraduate'?'active':'' ?>">
                Postgraduate
            </a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Level</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" class="empty">No records found</td>
                    </tr>
                <?php else: ?>
                    <?php $i=1; while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['Stud_firstname'].' '.$row['Stud_lastname']) ?></td>
                            <td><?= htmlspecialchars($row['Stud_matricNum']) ?></td>
                            <td><?= htmlspecialchars($row['Stud_level']) ?></td>
                            <td class="actions">
                                <a class="btn-view"
                                   href="admin_view_detail.php?id=<?= $row['Stud_id'] ?>">
                                    View
                                </a>
                                <a class="btn-delete"
                                   href="admin_del_student.php?id=<?= $row['Stud_id'] ?>"
                                   onclick="return confirm('Delete this student?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- EXTRA STYLING -->
<style>
.main-content {
    margin-left: 250px;
    padding: 40px;
}

.card {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card h2 {
    color: #f50057;
    margin-bottom: 20px;
}

.tabs {
    margin-bottom: 20px;
}

.tabs a {
    margin-right: 15px;
    padding: 8px 18px;
    border-radius: 20px;
    text-decoration: none;
    color: #555;
    background: #eee;
    font-weight: 600;
}

.tabs a.active {
    background: #1976d2;
    color: white;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f50057;
    color: white;
    padding: 12px;
    text-align: left;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.data-table tr:hover {
    background: #f9f9f9;
}

.actions {
    text-align: center;
}

.btn-view {
    background: #1976d2;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    margin-right: 8px;
}

.btn-delete {
    background: #e53935;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
}

.empty {
    text-align: center;
    padding: 20px;
    color: #777;
}
</style>

<script>
document.querySelectorAll(".dropdown-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        this.classList.toggle("active");

        let dropdown = this.nextElementSibling;
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        } else {
            dropdown.style.display = "block";
        }
    });
});
</script>

</body>
</html>
