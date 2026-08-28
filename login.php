<?php
$page_title = "Farmer Login";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

if (isFarmerLoggedIn()) {
    header("Location: farmer/dashboard.php");
    exit();
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email address and password.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM farmers WHERE email = ?");
            $stmt->execute([$email]);
            $farmer = $stmt->fetch();

            if ($farmer && verify_password($password, $farmer['password'])) {
                $_SESSION['farmer_id']    = $farmer['id'];
                $_SESSION['farmer_name']  = $farmer['name'];
                $_SESSION['farmer_email'] = $farmer['email'];

                setFlash('success', 'Welcome back, ' . htmlspecialchars($farmer['name']) . '!');
                header("Location: farmer/dashboard.php");
                exit();
            } else {
                $error = "Invalid email address or password.";
            }
        } catch (Exception $e) {
            $error = "System error during authentication. Please try again.";
        }
    }
}
?>

<div class="auth-page-section">
  <div class="container">
    <div class="auth-wrapper">
      <div class="auth-icon-circle">
        <i class="fas fa-tractor"></i>
      </div>
      <div class="auth-header">
        <h2>Farmer Login</h2>
        <p>Access your equipment rental dashboard</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <div class="input-icon-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. farmer@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-icon-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg auth-submit-btn"><i class="fas fa-sign-in-alt"></i> Login to Dashboard</button>
      </form>

      <div class="form-footer">
        Don't have a farmer account? <a href="register.php" class="auth-link">Register here</a>
        
        <div class="demo-credentials-box">
          <div class="demo-badge"><i class="fas fa-key"></i> Demo Credentials</div>
          <div class="demo-details">
            <span>Email: <code>farmer@example.com</code></span>
            <span>Password: <code>farmer123</code></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
