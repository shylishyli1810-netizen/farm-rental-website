<?php
$page_title = "Admin Login";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$username = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both admin username and password.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if ($admin && verify_password($password, $admin['password'])) {
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email']    = $admin['email'];

                setFlash('success', 'Admin session started. Welcome, ' . htmlspecialchars($admin['username']) . '!');
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid admin username or password.";
            }
        } catch (Exception $e) {
            $error = "Authentication system error. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page-section">
  <div class="container">
    <div class="auth-wrapper admin-auth-wrapper">
      <div class="auth-icon-circle admin-icon-circle">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="auth-header">
        <h2>Administrator Login</h2>
        <p>Secure Portal for Managing Farm Equipment &amp; Rentals</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="form-group">
          <label class="form-label" for="username">Admin Username</label>
          <div class="input-icon-group">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="username" name="username" class="form-control" placeholder="e.g. admin" value="<?php echo htmlspecialchars($username); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-icon-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter admin password" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg auth-submit-btn admin-submit-btn"><i class="fas fa-lock"></i> Authorize Admin Login</button>
      </form>

      <div class="form-footer">
        <div class="demo-credentials-box">
          <div class="demo-badge"><i class="fas fa-user-shield"></i> Admin Credentials</div>
          <div class="demo-details">
            <span>Username: <code>admin</code></span>
            <span>Password: <code>admin123</code></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
