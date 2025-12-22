<?php
session_start();
include '../php/config.php';

/* ===== AUTH CHECK (ADMIN ONLY) ===== */
if (!isset($_SESSION['User_type']) || $_SESSION['User_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

/* ===== FETCH PARKING DATA ===== */
$sql = "SELECT 
            PS_id,
            vehicle_type,
            PS_Area,
            PS_box,
            PS_status,
            PS_reason,
            start_datetime,
            end_datetime,
            PS_create,
            PS_updated
        FROM parkingspace";

$result = $conn->query($sql);

$parkingData = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $parkingData[] = [
            "PS_id"          => $row['PS_id'],
            "vehicle_type"   => $row['vehicle_type'],
            "PS_Area"        => $row['PS_Area'],
            "PS_box"         => $row['PS_box'],
            "PS_status"      => $row['PS_status'],
            "PS_reason"      => $row['PS_reason'],
            "start_datetime" => $row['start_datetime'],
            "end_datetime"   => $row['end_datetime'],
            "PS_create"      => $row['PS_create'],
            "PS_updated"     => $row['PS_updated']
        ];
    }
}

/* ===== OUTPUT JSON ===== */
header('Content-Type: application/json');
echo json_encode($parkingData);

$conn->close();
