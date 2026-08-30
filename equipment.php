<?php
$page_title = "Equipment Catalog";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Fetch equipment list & categories
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
?>

<div class="container">
  <div class="page-header" style="text-align: center;">
    <h1 class="page-title" style="justify-content: center;"><i class="fas fa-tools"></i> Agricultural Equipment Catalog</h1>
    <p class="page-subtitle">Browse and rent modern farming machinery at affordable daily rates</p>
  </div>

  <!-- FILTER & SEARCH BAR -->
  <div class="table-card" style="padding: 1.25rem; margin-bottom: 2rem;">
    <div class="filter-bar" style="justify-content: space-between;">
      <div style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap;">
        <div style="position: relative; flex: 1; min-width: 240px;">
          <input type="text" id="catalogSearch" class="form-control" placeholder="Search by equipment name..." style="padding-left: 2.5rem;">
          <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
        </div>

        <select id="categoryFilter" class="form-control" style="max-width: 220px;">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">
        Showing <strong><?php echo count($equipment); ?></strong> items
      </div>
    </div>
  </div>

  <!-- EQUIPMENT GRID -->
  <div class="grid-3">
    <?php if (!empty($equipment)): ?>
      <?php foreach ($equipment as $item): ?>
        <div class="equipment-card equipment-card-item" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-category="<?php echo htmlspecialchars($item['category']); ?>">
          <div class="equipment-img-wrap">
            <span class="equipment-category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
            
            <?php if ($item['availability'] === 'Available'): ?>
              <span class="availability-badge badge-available"><i class="fas fa-check-circle"></i> Available</span>
            <?php elseif ($item['availability'] === 'Maintenance'): ?>
              <span class="availability-badge badge-maintenance"><i class="fas fa-wrench"></i> Maintenance</span>
            <?php else: ?>
              <span class="availability-badge badge-unavailable"><i class="fas fa-times-circle"></i> Unavailable</span>
            <?php endif; ?>

            <img src="images/equipment/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="equipment-img" onerror="this.src='images/equipment/default_equipment.jpg'">
          </div>

          <div class="equipment-body">
            <h3 class="equipment-title"><?php echo htmlspecialchars($item['name']); ?></h3>
            <p class="equipment-desc"><?php echo htmlspecialchars($item['description']); ?></p>

            <div class="equipment-price-row">
              <div>
                <span class="price-amount">₹ <?php echo number_format($item['price_per_day'], 2); ?></span>
                <span class="price-unit">/ day</span>
              </div>
            </div>

            <div class="equipment-footer">
              <a href="farmer/equipment_details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-info-circle"></i> Details</a>
              
              <?php if ($item['availability'] === 'Available'): ?>
                <a href="farmer/book.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> Book Now</a>
                <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price_per_day']; ?>" data-image="<?php echo htmlspecialchars($item['image']); ?>"><i class="fas fa-cart-plus"></i> Add to Cart</button>
              <?php else: ?>
                <button class="btn btn-outline-dark btn-sm" disabled style="opacity: 0.6; cursor: not-allowed;"><i class="fas fa-ban"></i> Unavailable</button>
              <?php endif; ?>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3rem;">No equipment currently available in database.</p>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
