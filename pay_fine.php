<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php"); exit;
}
require 'db_connect.php';

$violation_id = intval($_POST['violation_id'] ?? 0);

if ($violation_id > 0) {
    // Update status to Paid
    $stmt = $conn->prepare("UPDATE violations SET status='Paid' WHERE id=?");
    $stmt->bind_param("i", $violation_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: owner_dashboard.php?paid=1");
exit;
?>
