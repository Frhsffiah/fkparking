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

$success = false;

if (isset($_POST['register'])) {

    // Create user (NO password)
    $stmt = $conn->prepare("
        INSERT INTO user (User_username, User_type)
        VALUES (?, 'student')
    ");
    $stmt->bind_param("s", $_POST['Stud_matricNum']);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Create student
    $stmt = $conn->prepare("
        INSERT INTO student (
            Stud_firstname, Stud_lastname, Stud_IC,
            Stud_course, Stud_gender, Stud_phoneNum,
            Stud_email, Stud_matricNum, Stud_level, User_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssssssi",
        $_POST['Stud_firstname'],
        $_POST['Stud_lastname'],
        $_POST['Stud_IC'],
        $_POST['Stud_course'],
        $_POST['Stud_gender'],
        $_POST['Stud_phoneNum'],
        $_POST['Stud_email'],
        $_POST['Stud_matricNum'],
        $_POST['Stud_level'],
        $user_id
    );

    $stmt->execute();
    $success = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Student</title>
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

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="card">
        <h2>Register Student</h2>
        

        <form method="POST" class="form-grid">

            <div>
                <label>First Name</label>
                <input type="text" name="Stud_firstname" required>
            </div>

            <div>
                <label>Last Name</label>
                <input type="text" name="Stud_lastname" required>
            </div>

            <div>
                <label>IC Number</label>
                <input type="text" name="Stud_IC" required>
            </div>

            <div>
                <label>Matric Number</label>
                <input type="text" name="Stud_matricNum" required>
            </div>

            <div>
                <label>Course</label>
                <select name="Stud_course" required>
                    <option value="">Select Course</option>
                    <option value="BCN">BCN</option>
                    <option value="BCG">BCG</option>
                    <option value="BCS">BCS</option>
                    <option value="BCY">BCY</option>
                    <option value="DRC">DRC</option>
                </select>
            </div>

            <div>
                <label>Gender</label>
                <select name="Stud_gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div>
                <label>Phone Number</label>
                <input type="text" name="Stud_phoneNum" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="Stud_email" required>
            </div>

            <div>
                <label>Level</label>
                <select name="Stud_level" required>
                    <option value="">Select Level</option>
                    <option value="Undergraduate">Undergraduate</option>
                    <option value="Postgraduate">Postgraduate</option>
                </select>
            </div>

            <div class="full-width">
                <button type="submit" name="register" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Register Student
                </button>
            </div>

        </form>
    </div>
</div>

<!-- SUCCESS POPUP -->
<?php if ($success): ?>
<div class="popup-overlay">
    <div class="popup">
        <h3>Student Registered</h3>
        <p>The student has been registered successfully.</p>
        <button onclick="window.location.href='admin_dashboard.php'" class="btn-primary">
            OK
        </button>
    </div>
</div>
<?php endif; ?>

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
    max-width: 900px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card h2 {
    color: #f50057;
    margin-bottom: 5px;
}

.subtitle {
    color: #666;
    margin-bottom: 25px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-grid label {
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

.form-grid input,
.form-grid select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.full-width {
    grid-column: span 2;
    text-align: right;
}

.btn-primary {
    background: #1976d2;
    color: #fff;
    border: none;
    padding: 12px 25px;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-primary:hover {
    background: #125ca1;
}

.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
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
