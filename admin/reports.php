<?php
$page_title = "Reports & Analytics";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$start_date = trim($_GET['start_date'] ?? '');
$end_date   = trim($_GET['end_date'] ?? '');

$total_farmers = 0;
$total_equipment = 0;
$total_bookings = 0;
$approved_bookings = 0;
$pending_bookings = 0;
$completed_bookings = 0;
$rejected_bookings = 0;
$total_rental_amount = 0.00;
$filtered_bookings = [];

try {
    $total_farmers   = $conn->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
    $total_equipment = $conn->query("SELECT COUNT(*) FROM equipment")->fetchColumn();

    // Query with optional date range filter on created_at or start_date
    $where_sql = " WHERE 1=1";
    $params = [];

    if ($start_date !== '') {
        $where_sql .= " AND DATE(b.start_date) >= ?";
        $params[] = $start_date;
    }
    if ($end_date !== '') {
        $where_sql .= " AND DATE(b.end_date) <= ?";
        $params[] = $end_date;
    }

    // Filtered Counts
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM bookings b" . $where_sql);
    $countStmt->execute($params);
    $total_bookings = $countStmt->fetchColumn();

    $apprStmt = $conn->prepare("SELECT COUNT(*) FROM bookings b" . $where_sql . " AND b.status = 'Approved'");
    $apprStmt->execute($params);
    $approved_bookings = $apprStmt->fetchColumn();

    $pendStmt = $conn->prepare("SELECT COUNT(*) FROM bookings b" . $where_sql . " AND b.status = 'Pending'");
    $pendStmt->execute($params);
    $pending_bookings = $pendStmt->fetchColumn();

    $compStmt = $conn->prepare("SELECT COUNT(*) FROM bookings b" . $where_sql . " AND b.status = 'Completed'");
    $compStmt->execute($params);
    $completed_bookings = $compStmt->fetchColumn();

    $rejStmt = $conn->prepare("SELECT COUNT(*) FROM bookings b" . $where_sql . " AND b.status = 'Rejected'");
    $rejStmt->execute($params);
    $rejected_bookings = $rejStmt->fetchColumn();

    $revStmt = $conn->prepare("SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b" . $where_sql . " AND b.status IN ('Approved', 'Completed')");
    $revStmt->execute($params);
    $total_rental_amount = $revStmt->fetchColumn();

    // Fetch Details List
    $listStmt = $conn->prepare("
        SELECT b.*, f.name AS farmer_name, e.name AS equipment_name, e.category
        FROM bookings b
        JOIN farmers f ON b.farmer_id = f.id
        JOIN equipment e ON b.equipment_id = e.id
        " . $where_sql . "
        ORDER BY b.id DESC
    ");
    $listStmt->execute($params);
    $filtered_bookings = $listStmt->fetchAll();
} catch (Exception $e) {
    // Graceful catch
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-chart-bar"></i> System Reports &amp; Analytics</h1>
      <p class="page-subtitle">Comprehensive summary of farmers, equipment inventory, rental revenue, and date-filtered metrics</p>
    </div>
    <button class="btn btn-outline-dark" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
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
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
        <li><a href="reports.php" class="sidebar-link active"><i class="fas fa-file-invoice-dollar"></i> Reports &amp; Analytics</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> Admin Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN REPORTS CONTENT -->
    <div>
      <!-- DATE FILTER BAR -->
      <div class="table-card" style="padding: 1.25rem; margin-bottom: 2rem;">
        <form action="reports.php" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 180px;">
            <label class="form-label" style="margin-bottom: 0.25rem;">Start Rental Date</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
          </div>

          <div style="flex: 1; min-width: 180px;">
            <label class="form-label" style="margin-bottom: 0.25rem;">End Rental Date</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
          </div>

          <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Date Filter</button>
            <?php if ($start_date !== '' || $end_date !== ''): ?>
              <a href="reports.php" class="btn btn-outline-dark"><i class="fas fa-redo"></i> Reset</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- KEY REPORT METRICS GRID -->
      <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
        <div class="stat-card">
          <div class="stat-icon-wrap icon-purple"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_farmers; ?></h3>
            <p>Total Farmers</p>
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
          <div class="stat-icon-wrap icon-blue"><i class="fas fa-calendar-alt"></i></div>
          <div class="stat-info">
            <h3><?php echo $total_bookings; ?></h3>
            <p>Total Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-amber"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <h3><?php echo $pending_bookings; ?></h3>
            <p>Pending Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-blue"><i class="fas fa-thumbs-up"></i></div>
          <div class="stat-info">
            <h3><?php echo $approved_bookings; ?></h3>
            <p>Approved Bookings</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrap icon-green"><i class="fas fa-check-double"></i></div>
          <div class="stat-info">
            <h3><?php echo $completed_bookings; ?></h3>
            <p>Completed Bookings</p>
          </div>
        </div>

        <div class="stat-card" style="grid-column: 1/-1; background: #f0fdf4; border-color: #bbf7d0;">
          <div class="stat-icon-wrap icon-green" style="background: #22c55e; color: #fff;"><i class="fas fa-rupee-sign"></i></div>
          <div class="stat-info">
            <h2 style="font-size: 2.2rem; color: #15803d; font-weight: 800;">₹ <?php echo number_format($total_rental_amount, 2); ?></h2>
            <p style="color: #166534; font-weight: 700; font-size: 1rem;">Total Rental Revenue (Approved &amp; Completed Contracts)</p>
          </div>
        </div>
      </div>

      <!-- FILTERED REPORT DETAILS TABLE -->
      <div class="table-card">
        <div class="table-header">
          <h3 class="table-title"><i class="fas fa-table"></i> Report Details Breakdown</h3>
          <small style="color: var(--text-muted);">
            <?php 
              if ($start_date && $end_date) {
                  echo "Showing records from " . date('d M Y', strtotime($start_date)) . " to " . date('d M Y', strtotime($end_date));
              } else {
                  echo "Showing all time records";
              }
            ?>
          </small>
        </div>

        <div class="table-responsive">
          <table class="custom-table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Farmer Name</th>
                <th>Equipment</th>
                <th>Category</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Total Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($filtered_bookings)): ?>
                <?php foreach ($filtered_bookings as $row): ?>
                  <tr>
                    <td><strong>#BK-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['farmer_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['equipment_name']); ?></strong></td>
                    <td><span class="equipment-category-badge" style="position: static;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                    <td><small><?php echo date('d M Y', strtotime($row['start_date'])); ?> &rarr; <?php echo date('d M Y', strtotime($row['end_date'])); ?></small></td>
                    <td><?php echo $row['days']; ?>d</td>
                    <td><strong>₹ <?php echo number_format($row['total_amount'], 2); ?></strong></td>
                    <td>
                      <?php 
                        $st = $row['status'];
                        $cls = strtolower($st);
                        echo "<span class='status-pill status-{$cls}'>{$st}</span>";
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">No booking records found for the selected date criteria.</td>
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
