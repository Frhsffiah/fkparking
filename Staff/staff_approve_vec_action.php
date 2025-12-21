<?php
session_start();
include("../php/config.php");

if (!isset($_SESSION['User_id']) || $_SESSION['User_type'] !== 'staff') {
    header("Location: ../public/login_page.php");
    exit();
}

$regNo = $_POST['regNo'];
$action = $_POST['action'];

$status = ($action === "approve") ? "Verified" : "Rejected";

$stmt = $conn->prepare("
    UPDATE vehicle SET Document_status=? WHERE Vehicle_regNo=?
");
$stmt->bind_param("ss", $status, $regNo);
$stmt->execute();
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style/navbarstaff.css">
<style>
.overlay{
position:fixed;top:0;left:0;width:100%;height:100%;
background:rgba(0,0,0,.5);
display:flex;align-items:center;justify-content:center;
}
.box{
background:#fff;padding:30px;border-radius:12px;text-align:center;width:380px;
}
.btn-ok{
background:#4caf50;color:#fff;border:none;padding:10px 24px;border-radius:6px;
}
</style>
</head>
<body>

<div class="overlay">
<div class="box">
<h3><?= $status=="Verified"?"Approved":"Rejected" ?> Successfully</h3>
<p>The vehicle registration has been <?= strtolower($status) ?>.</p>
<button class="btn-ok" onclick="goBack()">OK</button>
</div>
</div>

<script>
function goBack(){
    window.location.href="staff_approve_vec.php";
}
</script>

</body>
</html>
