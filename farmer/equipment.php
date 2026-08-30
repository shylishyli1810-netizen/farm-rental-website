<?php
$page_title = "Farmer Equipment Listing";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$equipment = [];
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM equipment ORDER BY availability ASC, name ASC");
    $equipment = $stmt->fetchAll();

    $catStmt = $conn->query("SELECT DISTINCT category FROM equipment ORDER BY category ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $equipment = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-tools"></i> Equipment Catalog</h1>
      <p class="page-subtitle">Select machinery to view full details or place a rental booking</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
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
        <li><a href="equipment.php" class="sidebar-link active"><i class="fas fa-tools"></i> Equipment Catalog</a></li>
        <li><a href="bookings.php" class="sidebar-link"><i class="fas fa-list-alt"></i> My Bookings</a></li>
        <li><a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i> My Profile</a></li>
        <li><a href="logout.php" class="sidebar-link" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- CATALOG CONTENT -->
    <div>
      <!-- SEARCH & FILTER BAR -->
      <div class="table-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
        <div class="filter-bar" style="justify-content: space-between;">
          <div style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 220px;">
              <input type="text" id="catalogSearch" class="form-control" placeholder="Search equipment..." style="padding-left: 2.5rem;">
              <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            </div>

            <select id="categoryFilter" class="form-control" style="max-width: 200px;">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- GRID -->
      <div class="grid-3" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        <?php foreach ($equipment as $item): ?>
          <div class="equipment-card equipment-card-item" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-category="<?php echo htmlspecialchars($item['category']); ?>">
            <div class="equipment-img-wrap" style="height: 180px;">
              <span class="equipment-category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
              <?php if ($item['availability'] === 'Available'): ?>
                <span class="availability-badge badge-available">Available</span>
              <?php elseif ($item['availability'] === 'Maintenance'): ?>
                <span class="availability-badge badge-maintenance">Maintenance</span>
              <?php else: ?>
                <span class="availability-badge badge-unavailable">Unavailable</span>
              <?php endif; ?>
              <img src="../images/equipment/<?php echo htmlspecialchars($item['image']); ?>" alt="" class="equipment-img" onerror="this.src='../images/equipment/default_equipment.jpg'">
            </div>

            <div class="equipment-body">
              <h4 class="equipment-title" style="font-size: 1.1rem;"><?php echo htmlspecialchars($item['name']); ?></h4>
              <p class="equipment-desc" style="font-size: 0.85rem;"><?php echo htmlspecialchars($item['description']); ?></p>

              <div class="equipment-price-row">
                <span class="price-amount" style="font-size: 1.25rem;">₹ <?php echo number_format($item['price_per_day'], 2); ?></span>
                <span class="price-unit">/ day</span>
              </div>

              <div class="equipment-footer">
                <a href="equipment_details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-info-circle"></i> Details</a>
                <?php if ($item['availability'] === 'Available'): ?>
                  <a href="book.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> Book</a>
                  <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price_per_day']; ?>" data-image="<?php echo htmlspecialchars($item['image']); ?>"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                <?php else: ?>
                  <button class="btn btn-outline-dark btn-sm" disabled style="opacity: 0.5;"><i class="fas fa-ban"></i> N/A</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
