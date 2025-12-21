<?php
session_start();
include("../php/config.php");

if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

$stmt = $conn->prepare("
    UPDATE staff
    SET Staff_firstname=?, Staff_lastname=?, Staff_email=?, Staff_phoneNum=?
    WHERE User_id=?
");
$stmt->bind_param(
    "ssssi",
    $_POST['firstname'],
    $_POST['lastname'],
    $_POST['email'],
    $_POST['phone'],
    $_SESSION['User_id']
);
$stmt->execute();
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style/navbarstaff.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.modal-overlay{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.5);
  display:flex;justify-content:center;align-items:center;
}
.modal-box{
  background:#fff;padding:30px;border-radius:12px;text-align:center;width:380px;
}
.success-icon{font-size:50px;color:#4caf50;margin-bottom:10px;}
.btn-primary{
  background:#4caf50;color:#fff;border:none;
  padding:10px 24px;border-radius:6px;font-weight:600;
}
</style>
</head>

<body>

<div class="modal-overlay">
  <div class="modal-box">
    <i class="fas fa-check-circle success-icon"></i>
    <h3>Profile Updated</h3>
    <p>Your profile has been updated successfully.</p>
    <button class="btn-primary" onclick="goProfile()">OK</button>
  </div>
</div>

<script>
function goProfile(){
    window.location.href = "staff_profile.php";
}
</script>

</body>
</html>
