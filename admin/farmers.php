<?php
$page_title = "Manage Farmers";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

// Delete Farmer Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    try {
        $delStmt = $conn->prepare("DELETE FROM farmers WHERE id = ?");
        $delStmt->execute([$delete_id]);
        setFlash('success', 'Farmer account #' . $delete_id . ' deleted successfully.');
    } catch (Exception $e) {
        setFlash('error', 'Unable to delete farmer: ' . $e->getMessage());
    }
    header("Location: farmers.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$farmers = [];

try {
    if ($search !== '') {
        $stmt = $conn->prepare("
            SELECT f.*, COUNT(b.id) AS total_bookings_count
            FROM farmers f
            LEFT JOIN bookings b ON f.id = b.farmer_id
            WHERE f.name LIKE ? OR f.email LIKE ? OR f.phone LIKE ? OR f.address LIKE ?
            GROUP BY f.id
            ORDER BY f.id DESC
        ");
        $param = "%{$search}%";
        $stmt->execute([$param, $param, $param, $param]);
    } else {
        $stmt = $conn->query("
            SELECT f.*, COUNT(b.id) AS total_bookings_count
            FROM farmers f
            LEFT JOIN bookings b ON f.id = b.farmer_id
            GROUP BY f.id
            ORDER BY f.id DESC
        ");
    }
    $farmers = $stmt->fetchAll();
} catch (Exception $e) {
    $farmers = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-users"></i> Manage Registered Farmers</h1>
      <p class="page-subtitle">Inspect registered farmer accounts, contact details, and rental activity</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Dashboard</a>
  </div>

  <div class="dashboard-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar" style="background: var(--primary-dark);"><i class="fas fa-user-shield"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);">Platform Administrator</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Manage Equipment</a></li>
        <li><a href="farmers.php" class="sidebar-link active"><i class="fas fa-users"></i> Manage Farmers</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- FARMERS TABLE -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title"><i class="fas fa-address-book"></i> Farmer Accounts</h3>

        <form action="farmers.php" method="GET" style="display: flex; gap: 0.5rem; max-width: 300px;">
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search farmer..." value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
          <?php if ($search !== ''): ?>
            <a href="farmers.php" class="btn btn-outline-dark btn-sm"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Farmer Name</th>
              <th>Contact Info</th>
              <th>Farm Address</th>
              <th>Total Bookings</th>
              <th>Registered Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($farmers)): ?>
              <?php foreach ($farmers as $f): ?>
                <tr>
                  <td>#FM-<?php echo str_pad($f['id'], 3, '0', STR_PAD_LEFT); ?></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                      <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.9rem; margin: 0;"><i class="fas fa-user"></i></div>
                      <strong><?php echo htmlspecialchars($f['name']); ?></strong>
                    </div>
                  </td>
                  <td>
                    <i class="fas fa-envelope" style="color: var(--text-muted); font-size: 0.85rem;"></i> <?php echo htmlspecialchars($f['email']); ?><br>
                    <i class="fas fa-phone-alt" style="color: var(--text-muted); font-size: 0.85rem;"></i> <?php echo htmlspecialchars($f['phone']); ?>
                  </td>
                  <td><small><?php echo htmlspecialchars($f['address']); ?></small></td>
                  <td>
                    <span class="badge" style="background: var(--bg-main); color: var(--text-main); font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 4px;">
                      <?php echo $f['total_bookings_count']; ?> Booking(s)
                    </span>
                  </td>
                  <td><small><?php echo date('d M Y', strtotime($f['created_at'])); ?></small></td>
                  <td>
                    <a href="farmers.php?action=delete&id=<?php echo $f['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deleting this farmer will also remove all associated bookings! Are you sure?');"><i class="fas fa-trash"></i> Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">No farmer records found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
