<?php
session_start();
require 'db_connect.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $user_type      = trim($_POST['user_type'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm        = $_POST['confirm_password'] ?? '';
    $vehicle_number = strtoupper(trim($_POST['vehicle_number'] ?? ''));

    if (!$name || !$email || !$phone || !$user_type || !$password)
        $error = 'All fields are required.';
    elseif ($user_type === 'owner' && !$vehicle_number)
        $error = 'Vehicle number is required for owners.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Invalid email address.';
    elseif (!preg_match('/^\d{10}$/', $phone))
        $error = 'Phone must be 10 digits.';
    elseif (strlen($password) < 6)
        $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm)
        $error = 'Passwords do not match.';
    elseif (!in_array($user_type, ['officer','owner']))
        $error = 'Invalid user type.';
    else {
        // Check duplicate email
        $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $conn->prepare("INSERT INTO users (name,email,phone,user_type,password) VALUES (?,?,?,?,?)");
            $ins->bind_param("sssss", $name, $email, $phone, $user_type, $hash);

            if ($ins->execute()) {
                // If owner, save vehicle to vehicles table
                if ($user_type === 'owner' && $vehicle_number) {
                    $veh = $conn->prepare("INSERT IGNORE INTO vehicles (vehicle_number, owner_name, owner_email) VALUES (?,?,?)");
                    $veh->bind_param("sss", $vehicle_number, $name, $email);
                    $veh->execute();
                    $veh->close();
                }
                $success = 'Account created! You can now sign in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="icon">🚦</div>
      <h2>Create Account</h2>
      <p>Join the Smart Traffic Management System</p>
    </div>

    <?php if($error): ?><div class="error-msg"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if($success): ?><div class="success-msg"><?=htmlspecialchars($success)?></div><?php endif; ?>

    <form id="regForm" action="register.php" method="POST" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="John Doe" value="<?=htmlspecialchars($_POST['name']??'')?>" required/>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="9876543210" value="<?=htmlspecialchars($_POST['phone']??'')?>" required/>
        </div>
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?=htmlspecialchars($_POST['email']??'')?>" required/>
      </div>

      <div class="form-group">
        <label>Registering As</label>
        <select name="user_type" id="user_type" onchange="toggleVehicleField()" required>
          <option value="">-- Select --</option>
          <option value="officer" <?=(($_POST['user_type']??'')==='officer')?'selected':''?>>Traffic Officer</option>
          <option value="owner"   <?=(($_POST['user_type']??'')==='owner')?'selected':''?>>Vehicle Owner</option>
        </select>
      </div>

      <!-- Vehicle number field — shown only for owners -->
      <div class="form-group" id="vehicle-field" style="display:<?=(($_POST['user_type']??'')==='owner')?'block':'none'?>">
        <label>Vehicle Number</label>
        <input type="text" name="vehicle_number" placeholder="e.g. KA01AB1234"
               value="<?=htmlspecialchars($_POST['vehicle_number']??'')?>"
               style="text-transform:uppercase"/>
        <small style="color:#9e7a6a;font-size:0.78rem;">Enter your registered vehicle number</small>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 6 chars" required/>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat" required/>
        </div>
      </div>

      <div id="js-error" class="error-msg" style="display:none"></div>
      <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="login.php">Sign In</a></div>
  </div>

  <script src="script.js"></script>
  <script>
    // Show vehicle number field only when "Vehicle Owner" is selected
    function toggleVehicleField() {
      const type  = document.getElementById('user_type').value;
      const field = document.getElementById('vehicle-field');
      field.style.display = type === 'owner' ? 'block' : 'none';
    }
  </script>
</body>
</html>
