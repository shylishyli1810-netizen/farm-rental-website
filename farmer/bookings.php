<?php
$page_title = "My Bookings";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$farmer_id = $_SESSION['farmer_id'];
$bookings = [];

try {
    $stmt = $conn->prepare("
        SELECT b.*, e.name AS equipment_name, e.category, e.image AS equipment_image, e.price_per_day 
        FROM bookings b 
        JOIN equipment e ON b.equipment_id = e.id 
        WHERE b.farmer_id = ? 
        ORDER BY b.id DESC
    ");
    $stmt->execute([$farmer_id]);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $bookings = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-calendar-alt"></i> My Equipment Bookings</h1>
      <p class="page-subtitle">Track status and rental contracts for all your booked agricultural tools</p>
    </div>
    <a href="equipment.php" class="btn btn-accent"><i class="fas fa-plus"></i> New Booking</a>
  </div>

  <div class="dashboard-layout">
    <!-- SIDEBAR NAVIGATION -->
    <aside class="sidebar">
      <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <h4 style="font-size: 1.05rem; color: var(--primary-dark); font-weight: 700;"><?php echo htmlspecialchars($_SESSION['farmer_name']); ?></h4>
        <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($_SESSION['farmer_email']); ?></p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="equipment.php" class="sidebar-link"><i class="fas fa-tools"></i> Equipment Catalog</a></li>
        <li><a href="bookings.php" class="sidebar-link active"><i class="fas fa-list-alt"></i> My Bookings</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> My Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- BOOKINGS TABLE CONTENT -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title"><i class="fas fa-history"></i> All Booking History</h3>
        <span class="badge" style="background: var(--primary-light); color: #fff; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem;">
          Total: <?php echo count($bookings); ?> Booking(s)
        </span>
      </div>

      <div class="table-responsive">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Equipment</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Days</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($bookings)): ?>
              <?php foreach ($bookings as $b): ?>
                <tr>
                  <td><strong>#BK-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                      <img src="../images/equipment/<?php echo htmlspecialchars($b['equipment_image']); ?>" alt="" style="width: 45px; height: 45px; border-radius: 6px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
                      <div>
                        <strong><?php echo htmlspecialchars($b['equipment_name']); ?></strong><br>
                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($b['category']); ?></small>
                      </div>
                    </div>
                  </td>
                  <td><?php echo date('d M Y', strtotime($b['start_date'])); ?></td>
                  <td><?php echo date('d M Y', strtotime($b['end_date'])); ?></td>
                  <td><?php echo $b['days']; ?> Day(s)</td>
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
                      <a href="booking_details.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-eye"></i> Details</a>
                      <a href="../receipt.php?id=<?php echo $b['id']; ?>" class="btn btn-primary btn-sm" target="_blank" title="Print Rental Letter / Slip"><i class="fas fa-print"></i> Slip</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                  No bookings found in your account. <a href="equipment.php" style="font-weight: 700;">Browse agricultural equipment to book!</a>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
