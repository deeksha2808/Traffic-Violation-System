<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'officer') {
    header("Location: login.php"); exit;
}
require 'db_connect.php';

$name = htmlspecialchars($_SESSION['user_name']);

// Fetch all violations for the officer to see
$violations = $conn->query("SELECT * FROM violations ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Officer Dashboard - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="dash-body">

  <!-- NAVBAR -->
  <nav class="dash-navbar">
    <div class="logo">🚦 Smart Traffic System</div>
    <div class="user-info">
      <span>🚔 <?=$name?></span>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div class="dash-content">

    <!-- WELCOME -->
    <div class="welcome-banner">
      <div>
        <h1>Welcome, <?=$name?> 👋</h1>
        <p>Manage and record traffic violations from your dashboard.</p>
      </div>
      <div class="role-badge">🚔 Traffic Officer</div>
    </div>

    <!-- QUICK ACTION CARDS -->
    <div class="dash-grid">
      <div class="dash-card" onclick="document.getElementById('add-section').scrollIntoView({behavior:'smooth'})">
        <div class="card-icon">➕</div>
        <h3>Add Violation</h3>
        <p>Record a new traffic violation with photo evidence.</p>
      </div>
      <div class="dash-card" onclick="document.getElementById('violations-section').scrollIntoView({behavior:'smooth'})">
        <div class="card-icon">📋</div>
        <h3>View All Violations</h3>
        <p>Browse all recorded challans and their status.</p>
      </div>
      <div class="dash-card">
        <div class="card-icon">📊</div>
        <h3>Total Challans</h3>
        <p><?=$violations->num_rows?> violation(s) recorded so far.</p>
      </div>
    </div>

    <!-- ADD VIOLATION FORM -->
    <div id="add-section" style="margin-bottom:36px;">
      <div class="section-title">➕ Add New Violation</div>
      <div class="form-card">
        <?php if(isset($_GET['added_email'])): ?>
          <div class="success-msg">✅ Challan generated and email notification sent to vehicle owner!</div>
        <?php elseif(isset($_GET['added'])): ?>
          <div class="success-msg">✅ Challan generated! (Vehicle owner email not found in system — no notification sent.)</div>
        <?php endif; ?>
        <?php if(isset($_GET['err'])): ?>
          <div class="error-msg">❌ <?=htmlspecialchars($_GET['err'])?></div>
        <?php endif; ?>

        <form action="add_violation.php" method="POST" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label>Vehicle Number</label>
              <input type="text" name="vehicle_number" placeholder="e.g. KA01AB1234" required/>
            </div>
            <div class="form-group">
              <label>Violation Type</label>
              <select name="violation_type" required>
                <option value="">-- Select --</option>
                <option>Speeding</option>
                <option>Signal Jump</option>
                <option>No Helmet</option>
                <option>Wrong Side Driving</option>
                <option>Drunk Driving</option>
                <option>No Seatbelt</option>
                <option>Illegal Parking</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Location</label>
              <input type="text" name="location" placeholder="e.g. MG Road, Bangalore" required/>
            </div>
            <div class="form-group">
              <label>Fine Amount (₹)</label>
              <input type="number" name="fine_amount" placeholder="e.g. 500" min="1" required/>
            </div>
          </div>
          <div class="form-group">
            <label>Upload Violation Image</label>
            <input type="file" name="violation_image" accept="image/*"/>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Generate Challan</button>
        </form>
      </div>
    </div>

    <!-- VIOLATIONS TABLE -->
    <div id="violations-section">
      <div class="section-title">📋 All Recorded Violations</div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Vehicle No.</th>
              <th>Violation</th>
              <th>Location</th>
              <th>Fine (₹)</th>
              <th>Image</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // reset pointer
            $violations->data_seek(0);
            $i = 1;
            while($row = $violations->fetch_assoc()):
            ?>
            <tr>
              <td><?=$i++?></td>
              <td><strong><?=htmlspecialchars($row['vehicle_number'])?></strong></td>
              <td><?=htmlspecialchars($row['violation_type'])?></td>
              <td><?=htmlspecialchars($row['location'])?></td>
              <td>₹<?=number_format($row['fine_amount'],2)?></td>
              <td>
                <?php if($row['image_path'] && file_exists($row['image_path'])): ?>
                  <img src="<?=htmlspecialchars($row['image_path'])?>" class="vio-img" alt="violation"/>
                <?php else: ?>
                  <span style="color:#ccc;font-size:0.8rem;">No image</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?=$row['status']==='Paid'?'badge-paid':'badge-unpaid'?>">
                  <?=$row['status']?>
                </span>
              </td>
              <td><?=date('d M Y', strtotime($row['created_at']))?></td>
            </tr>
            <?php endwhile; ?>
            <?php if($violations->num_rows === 0): ?>
              <tr><td colspan="8" style="text-align:center;color:#9e7a6a;padding:30px;">No violations recorded yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
