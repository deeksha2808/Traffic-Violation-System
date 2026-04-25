<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'officer') {
    header("Location: login.php"); exit;
}
require 'db_connect.php';
require 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: officer_dashboard.php"); exit;
}

$vehicle_number = strtoupper(trim($_POST['vehicle_number'] ?? ''));
$violation_type = trim($_POST['violation_type'] ?? '');
$location       = trim($_POST['location'] ?? '');
$fine_amount    = floatval($_POST['fine_amount'] ?? 0);

if (!$vehicle_number || !$violation_type || !$location || $fine_amount <= 0) {
    header("Location: officer_dashboard.php?err=All+fields+are+required"); exit;
}

// Handle image upload
$image_path = null;
if (!empty($_FILES['violation_image']['name'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext     = strtolower(pathinfo($_FILES['violation_image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed)) {
        header("Location: officer_dashboard.php?err=Invalid+image+format"); exit;
    }

    $filename   = 'vio_' . time() . '_' . rand(100,999) . '.' . $ext;
    $image_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['violation_image']['tmp_name'], $image_path)) {
        header("Location: officer_dashboard.php?err=Image+upload+failed"); exit;
    }
}

// Save violation (challan) — status defaults to Unpaid
$stmt = $conn->prepare(
    "INSERT INTO violations (vehicle_number, violation_type, location, image_path, fine_amount, status)
     VALUES (?, ?, ?, ?, ?, 'Unpaid')"
);
$stmt->bind_param("ssssd", $vehicle_number, $violation_type, $location, $image_path, $fine_amount);

if (!$stmt->execute()) {
    header("Location: officer_dashboard.php?err=Failed+to+save+violation"); exit;
}
$stmt->close();

// ===== SEND EMAIL NOTIFICATION TO VEHICLE OWNER =====
// Look up owner in vehicles table by vehicle number
$veh = $conn->prepare("SELECT owner_name, owner_email FROM vehicles WHERE vehicle_number = ?");
$veh->bind_param("s", $vehicle_number);
$veh->execute();
$veh->bind_result($owner_name, $owner_email);

$email_sent = false;
if ($veh->fetch() && $owner_email) {
    $email_sent = sendChallanEmail($owner_email, $owner_name, $vehicle_number, $violation_type, $location, $fine_amount);
}
$veh->close();

// Also check users table (owners who registered)
if (!$email_sent) {
    $usr = $conn->prepare("SELECT name, email FROM users WHERE user_type='owner' AND email IN (SELECT owner_email FROM vehicles WHERE vehicle_number=?)");
    $usr->bind_param("s", $vehicle_number);
    $usr->execute();
    $usr->bind_result($uname, $uemail);
    if ($usr->fetch() && $uemail) {
        $email_sent = sendChallanEmail($uemail, $uname, $vehicle_number, $violation_type, $location, $fine_amount);
    }
    $usr->close();
}

$msg = $email_sent ? 'added_email' : 'added';
header("Location: officer_dashboard.php?$msg=1");
exit;
?>
