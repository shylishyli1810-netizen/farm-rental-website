<?php
$page_title = "Farmer Dashboard";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];

// Fetch Stats
$total_bookings = 0;
$active_bookings = 0;
$completed_bookings = 0;
$available_equipment_count = 0;
$recent_bookings = [];

try {
    // Total Bookings for this farmer
    $stmt1 = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE farmer_id = ?");
    $stmt1->execute([$farmer_id]);
    $total_bookings = $stmt1->fetchColumn();

    // Active Bookings (Pending + Approved)
    $stmt2 = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE farmer_id = ? AND status IN ('Pending', 'Approved')");
    $stmt2->execute([$farmer_id]);
    $active_bookings = $stmt2->fetchColumn();

    // Completed Bookings
    $stmt3 = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE farmer_id = ? AND status = 'Completed'");
    $stmt3->execute([$farmer_id]);
    $completed_bookings = $stmt3->fetchColumn();

    // Available Equipment
    $stmt4 = $conn->query("SELECT COUNT(*) FROM equipment WHERE availability = 'Available'");
    $available_equipment_count = $stmt4->fetchColumn();

    // Recent Bookings (Last 5)
    $stmt5 = $conn->prepare("
        SELECT b.*, e.name AS equipment_name, e.image AS equipment_image 
        FROM bookings b 
        JOIN equipment e ON b.equipment_id = e.id 
        WHERE b.farmer_id = ? 
        ORDER BY b.id DESC LIMIT 5
    ");
    $stmt5->execute([$farmer_id]);
    $recent_bookings = $stmt5->fetchAll();
} catch (Exception $e) {
    // Silent catch
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Farmer Dashboard</h1>
      <p class="page-subtitle">Welcome back, <strong><?php echo htmlspecialchars($farmer_name); ?></strong>!</p>
    </div>
    <a href="equipment.php" class="btn btn-accent"><i class="fas fa-plus"></i> Book Equipment</a>
  </div>

  <div class="dashboard-layout">
    <!-- SIDEBAR NAVIGATION -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($farmer_name); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($_SESSION['farmer_email']); ?></p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Equipment Catalog</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-list-alt"></i> My Bookings</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> My Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN DASHBOARD CONTENT -->
    <div>
      <!-- STATS GRID -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-wrap icon-green"><i class="fas fa-shopping-basket"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_bookings; ?></h3>
            <p>Total Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-amber"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <h3><?php echo $active_bookings; ?></h3>
            <p>Active Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-blue"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <h3><?php echo $completed_bookings; ?></h3>
            <p>Completed Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-purple"><i class="fas fa-tractor"></i></div>
          <div class="stat-info">
            <h3><?php echo $available_equipment_count; ?></h3>
            <p>Available Machinery</p>
          </div>
        </div>
      </div>

      <!-- RECENT BOOKINGS TABLE -->
      <div class="table-card">
        <div class="table-header">
          <h3 class="table-title"><i class="fas fa-history"></i> Recent Booking Activity</h3>
          <a href="bookings.php" class="btn btn-outline-dark btn-sm">View All Bookings</a>
        </div>

        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Equipment</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($recent_bookings)): ?>
                <?php foreach ($recent_bookings as $booking): ?>
                  <tr>
                    <td><strong>#BK-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                    <td>
                      <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="../images/equipment/<?php echo htmlspecialchars($booking['equipment_image']); ?>" alt="" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
                        <strong><?php echo htmlspecialchars($booking['equipment_name']); ?></strong>
                      </div>
                    </td>
                    <td><?php echo date('d M Y', strtotime($booking['start_date'])); ?> &rarr; <?php echo date('d M Y', strtotime($booking['end_date'])); ?></td>
                    <td><?php echo $booking['days']; ?> Day(s)</td>
                    <td><strong>₹ <?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                    <td>
                      <?php 
                        $status = $booking['status'];
                        $class = strtolower($status);
                        echo "<span class='status-pill status-{$class}'>{$status}</span>";
                      ?>
                    </td>
                    <td>
                      <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-eye"></i> View</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">You haven't made any bookings yet. <a href="equipment.php">Browse equipment to make your first booking!</a></td>
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
