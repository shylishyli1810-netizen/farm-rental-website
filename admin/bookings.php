<?php
$page_title = "Manage Bookings";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$status_filter = trim($_GET['status'] ?? '');
$search        = trim($_GET['search'] ?? '');
$bookings      = [];

try {
    $sql = "
        SELECT b.*, f.name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone, 
               e.name AS equipment_name, e.category, e.image AS equipment_image, e.price_per_day
        FROM bookings b
        JOIN farmers f ON b.farmer_id = f.id
        JOIN equipment e ON b.equipment_id = e.id
        WHERE 1=1
    ";
    $params = [];

    if ($status_filter !== '') {
        $sql .= " AND b.status = ?";
        $params[] = $status_filter;
    }

    if ($search !== '') {
        $sql .= " AND (f.name LIKE ? OR e.name LIKE ? OR b.id LIKE ?)";
        $param_s = "%{$search}%";
        $params[] = $param_s;
        $params[] = $param_s;
        $params[] = $param_s;
    }

    $sql .= " ORDER BY b.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-calendar-check"></i> Manage Equipment Bookings</h1>
      <p class="page-subtitle">Approve, reject, or update status for farmer rental contracts</p>
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
        <li><a href="farmers.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Farmers</a></li>
        <li><a href="bookings.php" class="sidebar-link active"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- BOOKINGS MANAGEMENT CONTENT -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title"><i class="fas fa-list"></i> All Rental Contracts</h3>

        <!-- SEARCH & STATUS FILTER -->
        <form action="bookings.php" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
          <select name="status" class="form-control form-control-sm" style="max-width: 140px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo $status_filter==='Pending'?'selected':''; ?>>Pending</option>
            <option value="Approved" <?php echo $status_filter==='Approved'?'selected':''; ?>>Approved</option>
            <option value="Completed" <?php echo $status_filter==='Completed'?'selected':''; ?>>Completed</option>
            <option value="Rejected" <?php echo $status_filter==='Rejected'?'selected':''; ?>>Rejected</option>
          </select>

          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 180px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Farmer</th>
              <th>Equipment</th>
              <th>Dates</th>
              <th>Days</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Change Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $b): ?>
                <tr>
                  <td><strong>#BK-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                  <td>
                    <strong><?php echo htmlspecialchars($b['farmer_name']); ?></strong><br>
                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($b['farmer_phone']); ?></small>
                  </td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                      <img src="../images/equipment/<?php echo htmlspecialchars($b['equipment_image']); ?>" alt="" style="width: 35px; height: 35px; border-radius: 4px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
                      <span><?php echo htmlspecialchars($b['equipment_name']); ?></span>
                    </div>
                  </td>
                  <td><small><?php echo date('d M Y', strtotime($b['start_date'])); ?><br>&rarr; <?php echo date('d M Y', strtotime($b['end_date'])); ?></small></td>
                  <td><?php echo $b['days']; ?>d</td>
                  <td><strong>₹ <?php echo number_format($b['total_amount'], 2); ?></strong></td>
                  <td>
                    <?php 
                      $st = $b['status'];
                      $cls = strtolower($st);
                      echo "<span class='status-pill status-{$cls}'>{$st}</span>";
                    ?>
                  </td>
                  <td>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                      <?php if ($b['status'] !== 'Approved'): ?>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Approved" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;" title="Approve Booking"><i class="fas fa-check"></i> Approve</a>
                      <?php endif; ?>

                      <?php if ($b['status'] !== 'Completed' && $b['status'] === 'Approved'): ?>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Completed" class="btn btn-accent btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;" title="Mark Completed"><i class="fas fa-check-double"></i> Complete</a>
                      <?php endif; ?>

                      <?php if ($b['status'] !== 'Rejected' && $b['status'] !== 'Completed'): ?>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Rejected" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;" title="Reject Booking" onclick="return confirm('Reject this booking request?');"><i class="fas fa-times"></i> Reject</a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">No booking contracts found matching your filters.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
