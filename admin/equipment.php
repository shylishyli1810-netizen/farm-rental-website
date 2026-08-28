<?php
$page_title = "Manage Equipment";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$equipment = [];
try {
    $stmt = $conn->query("SELECT * FROM equipment ORDER BY id DESC");
    $equipment = $stmt->fetchAll();
} catch (Exception $e) {
    $equipment = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-tools"></i> Manage Farm Machinery</h1>
      <p class="page-subtitle">Add, edit, change availability status, or remove equipment</p>
    </div>
    <a href="add_equipment.php" class="btn btn-accent"><i class="fas fa-plus"></i> Add New Equipment</a>
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
        <li><a href="equipment.php" class="sidebar-link active"><i class="fas fa-tools"></i> Manage Equipment</a></li>
        <li><a href="farmers.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Farmers</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- EQUIPMENT LIST TABLE -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title"><i class="fas fa-boxes"></i> Equipment Inventory</h3>
        <span class="badge" style="background: var(--primary); color: #fff; padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.85rem;">
          Total: <?php echo count($equipment); ?> Item(s)
        </span>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Equipment Name</th>
              <th>Category</th>
              <th>Price / Day</th>
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($equipment)): ?>
              <?php foreach ($equipment as $item): ?>
                <tr>
                  <td>#EQ-<?php echo str_pad($item['id'], 3, '0', STR_PAD_LEFT); ?></td>
                  <td>
                    <img src="../images/equipment/<?php echo htmlspecialchars($item['image']); ?>" alt="" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                    <small style="color: var(--text-muted); display: block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['description']); ?></small>
                  </td>
                  <td><span class="equipment-category-badge" style="position: static;"><?php echo htmlspecialchars($item['category']); ?></span></td>
                  <td><strong>₹ <?php echo number_format($item['price_per_day'], 2); ?></strong></td>
                  <td>
                    <?php if ($item['availability'] === 'Available'): ?>
                      <span class="status-pill status-completed">Available</span>
                    <?php elseif ($item['availability'] === 'Maintenance'): ?>
                      <span class="status-pill status-pending">Maintenance</span>
                    <?php else: ?>
                      <span class="status-pill status-rejected">Unavailable</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="display: flex; gap: 0.5rem;">
                      <a href="edit_equipment.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-dark btn-sm" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                      <a href="delete_equipment.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this equipment? This action cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">No equipment inventory items found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
