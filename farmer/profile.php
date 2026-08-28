<?php
$page_title = "Farmer Profile";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$farmer_id = $_SESSION['farmer_id'];
$farmer = null;
$error = '';
$success = '';

try {
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE id = ?");
    $stmt->execute([$farmer_id]);
    $farmer = $stmt->fetch();
} catch (Exception $e) {
    $farmer = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($phone) || empty($address)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $updateStmt = $conn->prepare("UPDATE farmers SET name = ?, phone = ?, address = ? WHERE id = ?");
            $updateStmt->execute([$name, $phone, $address, $farmer_id]);

            $_SESSION['farmer_name'] = $name;
            $farmer['name'] = $name;
            $farmer['phone'] = $phone;
            $farmer['address'] = $address;

            $success = "Profile details updated successfully!";
        } catch (Exception $e) {
            $error = "Failed to update profile details: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-user-cog"></i> Farmer Profile</h1>
      <p class="page-subtitle">View and update your contact information and farm location</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Dashboard</a>
  </div>

  <div class="dashboard-layout">
    <!-- SIDEBAR NAVIGATION -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($farmer['name']); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($farmer['email']); ?></p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Equipment Catalog</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-list-alt"></i> My Bookings</a></li>
        <li><a href="profile.php" class="sidebar-link active"><i class="fas fa-user-cog"></i> My Profile</a></li>
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
        <div class="form-group">
          <label class="form-label">Email Address (Read-only)</label>
          <input type="email" class="form-control" value="<?php echo htmlspecialchars($farmer['email']); ?>" readonly style="background: #e2e8f0;">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="name">Full Name *</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($farmer['name']); ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="phone">Mobile Number *</label>
            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($farmer['phone']); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="address">Farm Location / Address *</label>
          <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($farmer['address']); ?></textarea>
        </div>

        <div class="form-group" style="margin-top: 1rem;">
          <small style="color: var(--text-muted);">Registered On: <?php echo date('d M Y, h:i A', strtotime($farmer['created_at'])); ?></small>
        </div>

        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update Profile Settings</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
