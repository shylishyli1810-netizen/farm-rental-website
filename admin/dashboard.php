<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

// Fetch Admin Dashboard Metrics
$total_farmers = 0;
$total_equipment = 0;
$total_bookings = 0;
$pending_bookings = 0;
$approved_bookings = 0;
$completed_bookings = 0;
$total_revenue = 0.00;
$recent_bookings = [];

try {
    $total_farmers    = $conn->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
    $total_equipment  = $conn->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $total_bookings   = $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    $approved_bookings= $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'Approved'")->fetchColumn();
    $completed_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'")->fetchColumn();
    $total_revenue    = $conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status IN ('Approved', 'Completed')")->fetchColumn();

    $stmt = $conn->query("
        SELECT b.*, f.name AS farmer_name, f.phone AS farmer_phone, e.name AS equipment_name, e.image AS equipment_image
        FROM bookings b
        JOIN farmers f ON b.farmer_id = f.id
        JOIN equipment e ON b.equipment_id = e.id
        ORDER BY b.id DESC LIMIT 6
    ");
    $recent_bookings = $stmt->fetchAll();
} catch (Exception $e) {
    // Graceful catch
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
      <p class="page-subtitle">Platform metrics, equipment inventory, and booking approvals</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
      <a href="add_equipment.php" class="btn btn-accent"><i class="fas fa-plus-circle"></i> Add New Equipment</a>
      <a href="reports.php" class="btn btn-primary"><i class="fas fa-chart-line"></i> View Reports</a>
    </div>
  </div>

  <div class="dashboard-layout">
    <!-- ADMIN SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar" style="background: var(--primary-dark);"><i class="fas fa-user-shield"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);">Platform Administrator</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Manage Equipment</a></li>
        <li><a href="farmers.php" class="sidebar-link"><i class="fas fa-users"></i> Manage Farmers</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- ADMIN CONTENT -->
    <div>
      <!-- METRIC CARDS -->
      <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
          <div class="stat-icon-wrap icon-purple"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_farmers; ?></h3>
            <p>Registered Farmers</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-green"><i class="fas fa-tractor"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_equipment; ?></h3>
            <p>Total Equipment</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-blue"><i class="fas fa-list-ol"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_bookings; ?></h3>
            <p>Total Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-amber"><i class="fas fa-hourglass-half"></i></div>
          <div class="stat-info">
            <h3><?php echo $pending_bookings; ?></h3>
            <p>Pending Approvals</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-green"><i class="fas fa-thumbs-up"></i></div>
          <div class="stat-info">
            <h3><?php echo $approved_bookings; ?></h3>
            <p>Approved Rentals</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-purple"><i class="fas fa-rupee-sign"></i></div>
          <div class="stat-info">
            <h3>₹ <?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Rental Revenue</p>
          </div>
        </div>
      </div>

      <!-- VISUAL BREAKDOWN CHARTS / PROGRESS BARS -->
      <div class="table-card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <h3 class="table-title" style="margin-bottom: 1rem;"><i class="fas fa-chart-pie"></i> Booking Status Distribution</h3>
        <?php 
          $pct_pending = $total_bookings > 0 ? round(($pending_bookings / $total_bookings) * 100) : 0;
          $pct_approved = $total_bookings > 0 ? round(($approved_bookings / $total_bookings) * 100) : 0;
          $pct_completed = $total_bookings > 0 ? round(($completed_bookings / $total_bookings) * 100) : 0;
        ?>
        <div style="display: flex; height: 24px; border-radius: 50px; overflow: hidden; background: #e2e8f0; margin-bottom: 1rem;">
          <div style="width: <?php echo $pct_approved; ?>%; background: var(--info);" title="Approved: <?php echo $pct_approved; ?>%"></div>
          <div style="width: <?php echo $pct_pending; ?>%; background: var(--warning);" title="Pending: <?php echo $pct_pending; ?>%"></div>
          <div style="width: <?php echo $pct_completed; ?>%; background: var(--success);" title="Completed: <?php echo $pct_completed; ?>%"></div>
        </div>
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.85rem; font-weight: 600;">
          <span style="display: flex; align-items: center; gap: 0.4rem;"><span style="width: 12px; height: 12px; border-radius: 50%; background: var(--info);"></span> Approved: <?php echo $approved_bookings; ?> (<?php echo $pct_approved; ?>%)</span>
          <span style="display: flex; align-items: center; gap: 0.4rem;"><span style="width: 12px; height: 12px; border-radius: 50%; background: var(--warning);"></span> Pending: <?php echo $pending_bookings; ?> (<?php echo $pct_pending; ?>%)</span>
          <span style="display: flex; align-items: center; gap: 0.4rem;"><span style="width: 12px; height: 12px; border-radius: 50%; background: var(--success);"></span> Completed: <?php echo $completed_bookings; ?> (<?php echo $pct_completed; ?>%)</span>
        </div>
      </div>

      <!-- RECENT BOOKINGS MANAGEMENT TABLE -->
      <div class="table-card">
        <div class="table-header">
          <h3 class="table-title"><i class="fas fa-clock"></i> Recent Booking Requests</h3>
          <a href="bookings.php" class="btn btn-outline-dark btn-sm">Manage All Bookings</a>
        </div>

        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Farmer</th>
                <th>Equipment</th>
                <th>Dates</th>
                <th>Total</th>
                <th>Status</th>
                <th>Quick Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_bookings)): ?>
                <?php foreach ($recent_bookings as $b): ?>
                  <tr>
                    <td><strong>#BK-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                    <td>
                      <strong><?php echo htmlspecialchars($b['farmer_name']); ?></strong><br>
                      <small style="color: var(--text-muted);"><?php echo htmlspecialchars($b['farmer_phone']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($b['equipment_name']); ?></td>
                    <td><?php echo date('d M', strtotime($b['start_date'])); ?> &rarr; <?php echo date('d M', strtotime($b['end_date'])); ?> (<?php echo $b['days']; ?>d)</td>
                    <td><strong>₹ <?php echo number_format($b['total_amount'], 2); ?></strong></td>
                    <td>
                      <?php 
                        $st = $b['status'];
                        $cls = strtolower($st);
                        echo "<span class='status-pill status-{$cls}'>{$st}</span>";
                      ?>
                    </td>
                    <td>
                      <?php if ($b['status'] === 'Pending'): ?>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Approved" class="btn btn-primary btn-sm" title="Approve"><i class="fas fa-check"></i> Approve</a>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Rejected" class="btn btn-danger btn-sm" title="Reject" onclick="return confirm('Reject this booking?');"><i class="fas fa-times"></i> Reject</a>
                      <?php elseif ($b['status'] === 'Approved'): ?>
                        <a href="update_booking_status.php?id=<?php echo $b['id']; ?>&status=Completed" class="btn btn-accent btn-sm"><i class="fas fa-check-double"></i> Complete</a>
                      <?php else: ?>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">No action</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No bookings found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
