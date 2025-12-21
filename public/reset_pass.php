<?php
include("../php/config.php");

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];
$msg = "";

$stmt = $conn->prepare("
    SELECT User_id FROM user WHERE reset_token=?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired token.");
}

$user = $result->fetch_assoc();

if (isset($_POST['reset'])) {

    $newpass = hash('sha256', $_POST['password']);

    $stmt = $conn->prepare("
        UPDATE user 
        SET User_password=?, reset_token=NULL
        WHERE User_id=?
    ");
    $stmt->bind_param("si", $newpass, $user['User_id']);
    $stmt->execute();

    $msg = "Password reset successful. You may login now.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5 col-md-4">
<h4>Reset Password</h4>

<?php if ($msg): ?>
<div class="alert alert-success"><?= $msg ?></div>
<a href="login_page.php" class="btn btn-primary">Go to Login</a>
<?php else: ?>

<form method="POST">
<input type="password" name="password" class="form-control"
       placeholder="New password" required>
<button name="reset" class="btn btn-success mt-3">Reset</button>
</form>

<?php endif; ?>
</div>
</body>
</html>
