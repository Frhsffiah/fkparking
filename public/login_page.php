<?php
session_start();
include("../php/config.php");

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);   // email OR matric
    $password = hash('sha256', $_POST['password']);
    $role     = $_POST['role'];

    if (empty($role)) {
        $error = "Please select a role.";
    } else {

        /* ===== LOGIN CHECK ===== */
        $stmt = $conn->prepare("
            SELECT * FROM user
            WHERE User_username = ?
            AND User_password = ?
            AND User_type = ?
        ");
        $stmt->bind_param("sss", $username, $password, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $_SESSION['User_id']   = $row['User_id'];
            $_SESSION['User_type'] = $row['User_type'];

            /* ===== STUDENT EXTRA SESSION (IMPORTANT) ===== */
            if ($role === 'student') {

                $stmtStud = $conn->prepare("
                    SELECT Stud_id
                    FROM student
                    WHERE User_id = ?
                ");
                $stmtStud->bind_param("i", $row['User_id']);
                $stmtStud->execute();
                $stud = $stmtStud->get_result()->fetch_assoc();

                $_SESSION['Stud_id'] = $stud['Stud_id'];

                header("Location: ../Student/student_dashboard.php");
                exit();

            } elseif ($role === 'admin') {

                header("Location: ../Admin/admin_dashboard.php");
                exit();

            } else {

                header("Location: ../Staff/staff_dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid login credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FKPARK Login</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="card col-md-5 mx-auto p-4">

        <h3 class="text-center mb-3">FKPARK Login</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <!-- Username -->
            <div class="form-group">
                <label>Username / Matric Number</label>
                <input type="text" name="username" class="form-control" required>
                <small class="text-muted">
                    Admin & Staff: Email | Student: Matric Number
                </small>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Role -->
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="">-- Select Role --</option>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button name="login" class="btn btn-primary btn-block">Login</button>

            <div class="text-center mt-3">
                <a href="forgot_pass.php">Forgot Password?</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
