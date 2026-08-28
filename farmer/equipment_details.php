<?php
$page_title = "Equipment Details";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$equipment = null;

try {
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch();
} catch (Exception $e) {
    $equipment = null;
}

if (!$equipment) {
    setFlash('error', 'Equipment not found.');
    header("Location: ../equipment.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header">
    <a href="equipment.php" class="btn btn-outline-dark btn-sm" style="margin-bottom: 1rem;"><i class="fas fa-arrow-left"></i> Back to Catalog</a>
    <h1 class="page-title"><i class="fas fa-info-circle"></i> Equipment Details</h1>
  </div>

  <div class="table-card" style="padding: 2rem; margin-bottom: 2rem;">
    <div class="grid-3" style="grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: start;">
      <!-- IMAGE -->
      <div style="background: #f1f5f9; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); position: relative;">
        <img src="../images/equipment/<?php echo htmlspecialchars($equipment['image']); ?>" alt="<?php echo htmlspecialchars($equipment['name']); ?>" style="width: 100%; height: 350px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
      </div>

      <!-- DETAILS -->
      <div>
        <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem;">
          <span class="equipment-category-badge" style="position: static; font-size: 0.85rem; padding: 0.35rem 1rem;"><?php echo htmlspecialchars($equipment['category']); ?></span>
          
          <?php if ($equipment['availability'] === 'Available'): ?>
            <span class="status-pill status-completed" style="font-size: 0.85rem;"><i class="fas fa-check-circle"></i> Available for Rent</span>
          <?php elseif ($equipment['availability'] === 'Maintenance'): ?>
            <span class="status-pill status-pending" style="font-size: 0.85rem;"><i class="fas fa-wrench"></i> Under Maintenance</span>
          <?php else: ?>
            <span class="status-pill status-rejected" style="font-size: 0.85rem;"><i class="fas fa-times-circle"></i> Currently Unavailable</span>
          <?php endif; ?>
        </div>

        <h2 style="font-size: 2rem; color: var(--primary-dark); font-weight: 800; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($equipment['name']); ?></h2>
        
        <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;">
          ₹ <?php echo number_format($equipment['price_per_day'], 2); ?> <span style="font-size: 1rem; color: var(--text-muted); font-weight: 500;">/ day</span>
        </div>

        <div style="background: #f8faf7; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 1.75rem;">
          <h4 style="color: var(--primary-dark); margin-bottom: 0.5rem;"><i class="fas fa-align-left"></i> Description &amp; Specifications</h4>
          <p style="color: var(--text-main); font-size: 1rem; line-height: 1.7;"><?php echo nl2br(htmlspecialchars($equipment['description'])); ?></p>
        </div>

        <div style="display: flex; gap: 1rem;">
          <?php if ($equipment['availability'] === 'Available'): ?>
            <a href="book.php?id=<?php echo $equipment['id']; ?>" class="btn btn-accent btn-lg" style="flex: 1;"><i class="fas fa-shopping-cart"></i> Book This Equipment Now</a>
          <?php else: ?>
            <button class="btn btn-outline-dark btn-lg" disabled style="flex: 1; opacity: 0.6; cursor: not-allowed;"><i class="fas fa-ban"></i> Equipment Unavailable</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
