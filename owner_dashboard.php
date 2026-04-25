<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header("Location: login.php"); exit;
}
require 'db_connect.php';

$name  = htmlspecialchars($_SESSION['user_name']);
$email = ''; // fetch owner email to match vehicles

// Get owner email from users table
$u = $conn->prepare("SELECT email FROM users WHERE id=?");
$u->bind_param("i", $_SESSION['user_id']);
$u->execute();
$u->bind_result($email);
$u->fetch();
$u->close();

// Find all vehicles registered to this owner email
$veh_stmt = $conn->prepare("SELECT vehicle_number FROM vehicles WHERE owner_email=?");
$veh_stmt->bind_param("s", $email);
$veh_stmt->execute();
$veh_result = $veh_stmt->get_result();

$vehicle_numbers = [];
while ($v = $veh_result->fetch_assoc()) {
    $vehicle_numbers[] = $v['vehicle_number'];
}
$veh_stmt->close();

// Fetch violations for those vehicles
$violations = [];
$has_unpaid = false;

if (!empty($vehicle_numbers)) {
    $placeholders = implode(',', array_fill(0, count($vehicle_numbers), '?'));
    $types        = str_repeat('s', count($vehicle_numbers));
    $vio_stmt     = $conn->prepare("SELECT * FROM violations WHERE vehicle_number IN ($placeholders) ORDER BY created_at DESC");
    $vio_stmt->bind_param($types, ...$vehicle_numbers);
    $vio_stmt->execute();
    $vio_result = $vio_stmt->get_result();
    while ($row = $vio_result->fetch_assoc()) {
        $violations[] = $row;
        if ($row['status'] === 'Unpaid') $has_unpaid = true;
    }
    $vio_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Owner Dashboard - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="dash-body">

  <!-- NAVBAR -->
  <nav class="dash-navbar">
    <div class="logo">🚦 Smart Traffic System</div>
    <div class="user-info">
      <span>👤 <?=$name?></span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div class="dash-content">

    <!-- WELCOME -->
    <div class="welcome-banner">
      <div>
        <h1>Welcome, <?=$name?> 👋</h1>
        <p>View your vehicle violations and pay fines from here.</p>
      </div>
      <div class="role-badge">👤 Vehicle Owner</div>
    </div>

    <!-- NOTIFICATION -->
    <?php if($has_unpaid): ?>
    <div class="notif-bar">
      🔔 <strong>New violation has been recorded for your vehicle.</strong> Please review and pay the fine below.
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['paid'])): ?>
    <div class="success-msg" style="margin-bottom:20px;">✅ Payment Successful! Your fine has been marked as Paid.</div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div class="dash-grid">
      <div class="dash-card">
        <div class="card-icon">📋</div>
        <h3>Total Violations</h3>
        <p><?=count($violations)?> violation(s) on record.</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">💳</div>
        <h3>Unpaid Fines</h3>
        <p><?=count(array_filter($violations, fn($v)=>$v['status']==='Unpaid'))?> unpaid challan(s).</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">✅</div>
        <h3>Paid Fines</h3>
        <p><?=count(array_filter($violations, fn($v)=>$v['status']==='Paid'))?> paid challan(s).</p>
      </div>
    </div>

    <!-- VIOLATIONS TABLE -->
    <div class="section-title">📋 My Violations & Challans</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Vehicle No.</th>
            <th>Violation</th>
            <th>Location</th>
            <th>Image</th>
            <th>Fine (₹)</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($violations)): ?>
            <tr><td colspan="9" style="text-align:center;color:#9e7a6a;padding:30px;">No violations found for your vehicle(s).</td></tr>
          <?php else: ?>
            <?php $i=1; foreach($violations as $row): ?>
            <tr>
              <td><?=$i++?></td>
              <td><strong><?=htmlspecialchars($row['vehicle_number'])?></strong></td>
              <td><?=htmlspecialchars($row['violation_type'])?></td>
              <td><?=htmlspecialchars($row['location'])?></td>
              <td>
                <?php if($row['image_path'] && file_exists($row['image_path'])): ?>
                  <img src="<?=htmlspecialchars($row['image_path'])?>" class="vio-img" alt="violation"/>
                <?php else: ?>
                  <span style="color:#ccc;font-size:0.8rem;">No image</span>
                <?php endif; ?>
              </td>
              <td>₹<?=number_format($row['fine_amount'],2)?></td>
              <td>
                <span class="badge <?=$row['status']==='Paid'?'badge-paid':'badge-unpaid'?>">
                  <?=$row['status']?>
                </span>
              </td>
              <td><?=date('d M Y', strtotime($row['created_at']))?></td>
              <td>
                <?php if($row['status']==='Unpaid'): ?>
                  <form action="pay_fine.php" method="POST" style="margin:0">
                    <input type="hidden" name="violation_id" value="<?=$row['id']?>"/>
                    <button type="submit" class="btn btn-green" style="padding:7px 16px;font-size:0.8rem;">Pay Now</button>
                  </form>
                <?php else: ?>
                  <span style="color:#27ae60;font-size:0.82rem;">✔ Paid</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
