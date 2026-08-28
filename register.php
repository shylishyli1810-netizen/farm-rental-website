<?php
$page_title = "Farmer Registration";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

if (isFarmerLoggedIn()) {
    header("Location: farmer/dashboard.php");
    exit();
}

$name = $email = $phone = $address = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($name)) { $errors[] = "Full name is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Valid email address is required."; }
    if (empty($phone) || !preg_match('/^[0-9]{10,12}$/', $phone)) { $errors[] = "Valid 10-digit mobile number is required."; }
    if (empty($address)) { $errors[] = "Farm address is required."; }
    if (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters long."; }
    if ($password !== $confirm) { $errors[] = "Password and confirm password do not match."; }

    // Check email uniqueness
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email address is already registered. Please login instead.";
            }
        } catch (Exception $e) {
            $errors[] = "Database validation error. Please try again.";
        }
    }

    // Insert user
    if (empty($errors)) {
        try {
            $hashed_password = hash_password($password);
            $insertStmt = $conn->prepare("INSERT INTO farmers (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->execute([$name, $email, $phone, $address, $hashed_password]);

            setFlash('success', 'Registration successful! You can now log in with your credentials.');
            header("Location: login.php");
            exit();
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<div class="auth-page-section">
  <div class="container">
    <div class="auth-wrapper register-auth-wrapper">
      <div class="auth-icon-circle">
        <i class="fas fa-user-plus"></i>
      </div>
      <div class="auth-header">
        <h2>Farmer Registration</h2>
        <p>Create your account to rent agricultural equipment online</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-triangle"></i>
          <div>
            <?php foreach ($errors as $err): ?>
              <div>• <?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <form action="register.php" method="POST" id="registerForm">
        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <div class="input-icon-group">
            <i class="fas fa-user input-icon"></i>
            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Ramesh Kumar" value="<?php echo htmlspecialchars($name); ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <div class="input-icon-group">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" id="email" name="email" class="form-control" placeholder="e.g. farmer@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="phone">Mobile Number *</label>
            <div class="input-icon-group">
              <i class="fas fa-phone-alt input-icon"></i>
              <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 9876543210" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="address">Farm Address *</label>
          <div class="input-icon-group">
            <i class="fas fa-map-marker-alt input-icon" style="top: 1.2rem;"></i>
            <textarea id="address" name="address" class="form-control" rows="2" placeholder="Enter village, district, state..." required><?php echo htmlspecialchars($address); ?></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Password *</label>
            <div class="input-icon-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password *</label>
            <div class="input-icon-group">
              <i class="fas fa-key input-icon"></i>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg auth-submit-btn"><i class="fas fa-check-circle"></i> Register Account</button>
      </form>

      <div class="form-footer">
        Already have a farmer account? <a href="login.php" class="auth-link">Login here</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
