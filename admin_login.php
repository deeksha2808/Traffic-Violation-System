<?php
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: admin_dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_connect.php';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: admin_dashboard.php"); exit;
        } else { $error = 'Incorrect password.'; }
    } else { $error = 'No admin account found.'; }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="icon">🛡️</div>
      <h2>Admin Login</h2>
      <p>Smart Traffic System — Admin Panel</p>
    </div>
    <?php if($error): ?><div class="error-msg"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form action="admin_login.php" method="POST">
      <div class="form-group">
        <label>Admin Email</label>
        <input type="email" name="email" placeholder="admin@traffic.com" required/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required/>
      </div>
      <button type="submit" class="btn btn-primary btn-full">Login as Admin</button>
    </form>
    <div class="auth-footer"><a href="login.php">← Back to User Login</a></div>
  </div>
</body>
</html>
