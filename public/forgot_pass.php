<?php
include("../php/config.php");

$msg = "";

if (isset($_POST['submit'])) {

    $username = $_POST['username']; // email OR matric number

    $stmt = $conn->prepare("
        SELECT User_id FROM user WHERE User_username=?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        $token = bin2hex(random_bytes(32));

        $stmt = $conn->prepare("
            UPDATE user SET reset_token=? WHERE User_id=?
        ");
        $stmt->bind_param("si", $token, $row['User_id']);
        $stmt->execute();

        // Simulated email (acceptable for project)
        $msg = "Click this link to reset password:<br>
        <a href='reset_pass.php?token=$token'>
        Reset Password</a>";

    } else {
        $msg = "Account not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5 col-md-4">
<h4>Forgot Password</h4>

<?php if ($msg): ?>
<div class="alert alert-info"><?= $msg ?></div>
<?php endif; ?>

<form method="POST">
<input type="text" name="username" class="form-control"
       placeholder="Email or Matric Number" required>
<button name="submit" class="btn btn-primary mt-3">Next</button>
</form>
</div>
</body>
</html>
