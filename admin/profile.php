<?php
$page_title = "Admin Profile";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$admin_id = $_SESSION['admin_id'];
$admin = null;
$error = '';
$success = '';

try {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
} catch (Exception $e) {
    $admin = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email)) {
        $error = "Username and email are required.";
    } else {
        try {
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = "New password must be at least 6 characters long.";
                } else {
                    $hashed = hash_password($password);
                    $upStmt = $conn->prepare("UPDATE admin SET username = ?, email = ?, password = ? WHERE id = ?");
                    $upStmt->execute([$username, $email, $hashed, $admin_id]);
                    $success = "Admin details and password updated successfully!";
                }
            } else {
                $upStmt = $conn->prepare("UPDATE admin SET username = ?, email = ? WHERE id = ?");
                $upStmt->execute([$username, $email, $admin_id]);
                $success = "Admin profile updated successfully!";
            }

            if (empty($error)) {
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;
                $admin['username'] = $username;
                $admin['email'] = $email;
            }
        } catch (Exception $e) {
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-user-cog"></i> Admin Profile</h1>
      <p class="page-subtitle">Manage administrator credentials and account settings</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Dashboard</a>
  </div>

  <div class="dashboard-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar" style="background: var(--primary-dark);"><i class="fas fa-user-shield"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($admin['username']); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);">Platform Administrator</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Manage Equipment</a></li>
        <li><a href="farmers.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Farmers</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link active"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- FORM CARD -->
    <div class="table-card" style="padding: 2rem;">
      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="profile.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="username">Admin Username *</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Admin Email *</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Change Password (Leave blank to keep current)</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password (min 6 characters)">
        </div>

        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Admin Credentials</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
