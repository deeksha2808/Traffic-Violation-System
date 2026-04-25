<?php
session_start();
require 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_type']==='officer' ? 'officer_dashboard.php' : 'owner_dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id,name,user_type,password FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                // redirect based on role
                header("Location: " . ($user['user_type']==='officer' ? 'officer_dashboard.php' : 'owner_dashboard.php'));
                exit;
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'No account found with that email.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Smart Traffic System</title>
  <link rel="stylesheet" href="style.css"/>
</head>
<body class="auth-body">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="icon">🔐</div>
      <h2>Welcome Back</h2>
      <p>Sign in to your Smart Traffic account</p>
    </div>

    <?php if($error): ?><div class="error-msg"><?=htmlspecialchars($error)?></div><?php endif; ?>

    <form id="loginForm" action="login.php" method="POST" novalidate>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?=htmlspecialchars($_POST['email']??'')?>" required/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Your password" required/>
      </div>
      <div id="js-error" class="error-msg" style="display:none"></div>
      <button type="submit" class="btn btn-primary btn-full">Sign In</button>
    </form>
    <div class="auth-footer">Don't have an account? <a href="register.php">Register Now</a></div>
  </div>
  <script src="script.js"></script>
</body>
</html>
