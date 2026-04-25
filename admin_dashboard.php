<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }
require 'db_connect.php';

$admin_name = htmlspecialchars($_SESSION['admin_name']);

// Stats
$total_users      = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_officers   = $conn->query("SELECT COUNT(*) as c FROM users WHERE user_type='officer'")->fetch_assoc()['c'];
$total_owners     = $conn->query("SELECT COUNT(*) as c FROM users WHERE user_type='owner'")->fetch_assoc()['c'];
$total_violations = $conn->query("SELECT COUNT(*) as c FROM violations")->fetch_assoc()['c'];
$total_unpaid     = $conn->query("SELECT COUNT(*) as c FROM violations WHERE status='Unpaid'")->fetch_assoc()['c'];
$total_paid       = $conn->query("SELECT COUNT(*) as c FROM violations WHERE status='Paid'")->fetch_assoc()['c'];
$total_fine       = $conn->query("SELECT SUM(fine_amount) as s FROM violations WHERE status='Unpaid'")->fetch_assoc()['s'] ?? 0;

// All violations
$violations = $conn->query("SELECT * FROM violations ORDER BY created_at DESC");

// All users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="dash-body">

  <nav class="dash-navbar">
    <div class="logo">🛡️ Admin Panel — Smart Traffic System</div>
    <div class="user-info">
      <span>🛡️ <?=$admin_name?></span>
      <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div class="dash-content">

    <div class="welcome-banner">
      <div>
        <h1>Admin Dashboard 🛡️</h1>
        <p>Full system overview — users, violations, and fine collection.</p>
      </div>
      <div class="role-badge">🛡️ Administrator</div>
    </div>

    <!-- STATS GRID -->
    <div class="dash-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:36px;">
      <div class="dash-card">
        <div class="card-icon">👥</div>
        <h3>Total Users</h3>
        <p><?=$total_users?> registered users</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">🚔</div>
        <h3>Officers</h3>
        <p><?=$total_officers?> traffic officers</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">👤</div>
        <h3>Vehicle Owners</h3>
        <p><?=$total_owners?> owners</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">📋</div>
        <h3>Total Challans</h3>
        <p><?=$total_violations?> violations</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">💳</div>
        <h3>Unpaid Fines</h3>
        <p><?=$total_unpaid?> | ₹<?=number_format($total_fine,2)?></p>
      </div>
      <div class="dash-card">
        <div class="card-icon">✅</div>
        <h3>Paid Fines</h3>
        <p><?=$total_paid?> cleared</p>
      </div>
    </div>

    <!-- ALL VIOLATIONS -->
    <div class="section-title" style="margin-bottom:16px;">📋 All Violations</div>
    <div class="table-wrap" style="margin-bottom:40px;">
      <table>
        <thead>
          <tr><th>#</th><th>Vehicle No.</th><th>Violation</th><th>Location</th><th>Fine (₹)</th><th>Image</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php if($violations->num_rows===0): ?>
            <tr><td colspan="8" style="text-align:center;color:#9e7a6a;padding:24px;">No violations yet.</td></tr>
          <?php else: $i=1; while($row=$violations->fetch_assoc()): ?>
          <tr>
            <td><?=$i++?></td>
            <td><strong><?=htmlspecialchars($row['vehicle_number'])?></strong></td>
            <td><?=htmlspecialchars($row['violation_type'])?></td>
            <td><?=htmlspecialchars($row['location'])?></td>
            <td>₹<?=number_format($row['fine_amount'],2)?></td>
            <td>
              <?php if($row['image_path'] && file_exists($row['image_path'])): ?>
                <img src="<?=htmlspecialchars($row['image_path'])?>" class="vio-img"/>
              <?php else: ?><span style="color:#ccc;font-size:0.8rem;">No image</span><?php endif; ?>
            </td>
            <td><span class="badge <?=$row['status']==='Paid'?'badge-paid':'badge-unpaid'?>"><?=$row['status']?></span></td>
            <td><?=date('d M Y',strtotime($row['created_at']))?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ALL USERS -->
    <div class="section-title" style="margin-bottom:16px;">👥 Registered Users</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Registered</th></tr>
        </thead>
        <tbody>
          <?php if($users->num_rows===0): ?>
            <tr><td colspan="6" style="text-align:center;color:#9e7a6a;padding:24px;">No users yet.</td></tr>
          <?php else: $i=1; while($row=$users->fetch_assoc()): ?>
          <tr>
            <td><?=$i++?></td>
            <td><?=htmlspecialchars($row['name'])?></td>
            <td><?=htmlspecialchars($row['email'])?></td>
            <td><?=htmlspecialchars($row['phone'])?></td>
            <td><span class="badge <?=$row['user_type']==='officer'?'badge-unpaid':'badge-paid'?>"><?=ucfirst($row['user_type'])?></span></td>
            <td><?=date('d M Y',strtotime($row['created_at']))?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
