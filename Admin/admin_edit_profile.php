<?php
session_start();
include '../php/config.php';

if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    header("Location: ../public/login_page.php");
    exit();
}

/* ===== Fetch Admin Profile ===== */
$stmt = $conn->prepare("
    SELECT Admin_firstname, Admin_lastname, Admin_email, Admin_phoneNum
    FROM admins
    WHERE User_id = ?
");
$stmt->bind_param("i", $_SESSION['User_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin Profile</title>

    <link rel="stylesheet" href="style/navbaradmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- ===== Modal Styling ===== -->
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 16px 20px;
            align-items: center;
        }

        .form-grid input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        /* ===== Modal ===== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-box {
            background: #fff;
            width: 380px;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0,0,0,0.25);
            position: relative;
        }

        .modal-box h3 {
            margin-top: 10px;
            margin-bottom: 8px;
            color: #333;
        }

        .modal-box p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success-icon {
            font-size: 60px;
            color: #0d6efd;
        }

        .close-btn {
            position: absolute;
            top: 12px;
            right: 15px;
            font-size: 22px;
            cursor: pointer;
            color: #888;
        }

        .close-btn:hover {
            color: #333;
        }
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

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">
    <div class="card">
        <h1>Edit Admin Profile</h1>

        <form action="admin_update_profile.php" method="POST">
            <div class="form-grid">
                <strong>First Name</strong>
                <input type="text" name="firstname"
                       value="<?= htmlspecialchars($admin['Admin_firstname']) ?>" required>

                <strong>Last Name</strong>
                <input type="text" name="lastname"
                       value="<?= htmlspecialchars($admin['Admin_lastname']) ?>" required>

                <strong>Email</strong>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($admin['Admin_email']) ?>" required>

                <strong>Phone Number</strong>
                <input type="text" name="phone"
                       value="<?= htmlspecialchars($admin['Admin_phoneNum']) ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>

                <a href="admin_profile.php" class="btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ================= SUCCESS MODAL ================= -->
<?php if (isset($_GET['updated']) && $_GET['updated'] === 'success'): ?>
<div class="modal-overlay">
    <div class="modal-box">
        <span class="close-btn" onclick="closeModal()">×</span>
        <i class="fas fa-check-circle success-icon"></i>
        <h3>Profile Updated</h3>
        <p>Your profile has been updated successfully.</p>
        <button class="btn btn-primary" onclick="closeModal()">OK</button>
    </div>
</div>
<?php endif; ?>

<!-- ================= SCRIPTS ================= -->
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
