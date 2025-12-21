<?php
session_start();
include("../php/config.php");

if (!isset($_SESSION['User_id'])) {
    header("Location: ../public/login_page.php");
    exit();
}

$userId = $_SESSION['User_id'];

$regNo  = strtoupper(trim($_POST['vehicle_reg_no']));
$type   = $_POST['vehicle_type'];
$brand  = $_POST['vehicle_brand'];
$color  = $_POST['vehicle_color'];

/* ================= FILE UPLOAD ================= */
$uploadDir = "../uploads/vehicle_docs/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$fileName = time() . "_" . basename($_FILES["vehicle_document"]["name"]);
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($_FILES["vehicle_document"]["tmp_name"], $filePath)) {
    die("File upload failed.");
}

/* ================= HASH ================= */
$docHash = hash_file("sha256", $filePath);

/* ================= CHECK DUPLICATE DOCUMENT ================= */
$checkHash = $conn->prepare("SELECT Vehicle_regNo FROM vehicle WHERE Document_hash=?");
$checkHash->bind_param("s", $docHash);
$checkHash->execute();
$dup = $checkHash->get_result()->fetch_assoc();

if ($dup) {
    unlink($filePath); // remove uploaded file
    $error = "duplicate";
}

/* ================= CHECK EXISTING VEHICLE ================= */
$checkExisting = $conn->prepare("SELECT Document_status FROM vehicle WHERE User_id=?");
$checkExisting->bind_param("i", $userId);
$checkExisting->execute();
$existing = $checkExisting->get_result()->fetch_assoc();

/* ================= INSERT / UPDATE ================= */
if (!isset($error)) {

    if ($existing && $existing['Document_status'] === 'Rejected') {

        // RESUBMIT
        $stmt = $conn->prepare("
            UPDATE vehicle 
            SET Vehicle_regNo=?, Vehicle_type=?, Vehicle_brand=?, Vehicle_color=?,
                Document_name=?, Document_hash=?, Document_status='Pending', Created_at=NOW()
            WHERE User_id=?
        ");
        $stmt->bind_param(
            "ssssssi",
            $regNo, $type, $brand, $color, $fileName, $docHash, $userId
        );

    } else {

        // FIRST SUBMISSION
        $stmt = $conn->prepare("
            INSERT INTO vehicle
            (Vehicle_regNo, User_id, Vehicle_type, Vehicle_brand, Vehicle_color, Document_name, Document_hash)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "sisssss",
            $regNo, $userId, $type, $brand, $color, $fileName, $docHash
        );
    }

    $stmt->execute();
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style/navbarstud.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.modal-overlay{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.5);
  display:flex;justify-content:center;align-items:center;
}
.modal-box{
  background:#fff;padding:30px;border-radius:12px;text-align:center;width:420px;
}
.success-icon{font-size:55px;color:#1e90ff;margin-bottom:10px;}
.error-icon{font-size:55px;color:#e74c3c;margin-bottom:10px;}
.btn-primary{background:#1e90ff;color:#fff;border:none;padding:10px 22px;border-radius:6px;}
</style>
</head>
<body>

<div class="modal-overlay">
  <div class="modal-box">

<?php if (isset($error) && $error === "duplicate"): ?>

    <i class="fas fa-times-circle error-icon"></i>
    <h3>Duplicate Document</h3>
    <p>This vehicle document has already been used by another user.</p>
    <button class="btn-primary" onclick="goBack()">OK</button>

<?php else: ?>

    <i class="fas fa-check-circle success-icon"></i>
    <h3>Submission Successful</h3>
    <p>Your vehicle registration has been submitted for verification.</p>
    <button class="btn-primary" onclick="goStatus()">OK</button>

<?php endif; ?>

  </div>
</div>

<script>
function goStatus(){
  window.location.href = "student_application_stat.php";
}
function goBack(){
  window.location.href = "student_vec_reg.php";
}
</script>

</body>
</html>
