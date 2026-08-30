<?php
$page_title = "Booking Details";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$farmer_id = $_SESSION['farmer_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$booking = null;

try {
    $stmt = $conn->prepare("
        SELECT b.*, e.name AS equipment_name, e.category, e.description AS equipment_desc, e.image AS equipment_image, e.price_per_day,
               f.name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone, f.address AS farmer_address
        FROM bookings b
        JOIN equipment e ON b.equipment_id = e.id
        JOIN farmers f ON b.farmer_id = f.id
        WHERE b.id = ? AND b.farmer_id = ?
    ");
    $stmt->execute([$booking_id, $farmer_id]);
    $booking = $stmt->fetch();
} catch (Exception $e) {
    $booking = null;
}

if (!$booking) {
    setFlash('error', 'Booking record not found or access denied.');
    header("Location: bookings.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <a href="bookings.php" class="btn btn-outline-dark btn-sm" style="margin-bottom: 0.5rem;"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
      <h1 class="page-title"><i class="fas fa-receipt"></i> Booking #BK-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></h1>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
      <a href="../receipt.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary" target="_blank" style="padding: 0.5rem 1.25rem;"><i class="fas fa-file-invoice"></i> View &amp; Print Receipt Letter</a>
      <?php 
        $st = $booking['status'];
        $cls = strtolower($st);
        echo "<span class='status-pill status-{$cls}' style='font-size: 1rem; padding: 0.5rem 1.25rem;'>Status: {$st}</span>";
      ?>
    </div>
  </div>

  <div class="grid-3" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- LEFT: DETAILS CARD -->
    <div class="table-card" style="padding: 2rem;">
      <h3 style="color: var(--primary-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
        <i class="fas fa-tools"></i> Rented Equipment Summary
      </h3>

      <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <img src="../images/equipment/<?php echo htmlspecialchars($booking['equipment_image']); ?>" alt="" style="width: 130px; height: 110px; border-radius: var(--radius-md); object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
        <div>
          <span class="equipment-category-badge" style="position: static;"><?php echo htmlspecialchars($booking['category']); ?></span>
          <h2 style="font-size: 1.5rem; color: var(--primary-dark); margin: 0.35rem 0;"><?php echo htmlspecialchars($booking['equipment_name']); ?></h2>
          <p style="color: var(--text-muted); font-size: 0.9rem;"><?php echo htmlspecialchars($booking['equipment_desc']); ?></p>
        </div>
      </div>

      <h3 style="color: var(--primary-dark); border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1.5rem; margin-top: 2rem;">
        <i class="fas fa-calendar-alt"></i> Schedule &amp; Cost Breakdown
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
        <div style="background: #f8faf7; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
          <small style="color: var(--text-muted); display: block;">Start Date</small>
          <strong style="font-size: 1.1rem; color: var(--primary-dark);"><?php echo date('d M Y (D)', strtotime($booking['start_date'])); ?></strong>
        </div>

        <div style="background: #f8faf7; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
          <small style="color: var(--text-muted); display: block;">End Date</small>
          <strong style="font-size: 1.1rem; color: var(--primary-dark);"><?php echo date('d M Y (D)', strtotime($booking['end_date'])); ?></strong>
        </div>

        <div style="background: #f8faf7; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
          <small style="color: var(--text-muted); display: block;">Duration</small>
          <strong style="font-size: 1.1rem; color: var(--primary-dark);"><?php echo $booking['days']; ?> Day(s)</strong>
        </div>
      </div>

      <div class="booking-summary-box">
        <div class="summary-row">
          <span>Daily Rental Price:</span>
          <span>₹ <?php echo number_format($booking['price_per_day'], 2); ?></span>
        </div>
        <div class="summary-row">
          <span>Total Rental Days:</span>
          <span>&times; <?php echo $booking['days']; ?></span>
        </div>
        <div class="summary-row summary-total">
          <span>Total Payable Amount:</span>
          <span style="color: var(--primary);">₹ <?php echo number_format($booking['total_amount'], 2); ?></span>
        </div>
      </div>
    </div>

    <!-- RIGHT: FARMER & CONTACT DETAILS -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
      <div class="table-card" style="padding: 1.5rem;">
        <h4 style="color: var(--primary-dark); margin-bottom: 1rem;"><i class="fas fa-user-circle"></i> Farmer Contact Info</h4>
        <p style="margin-bottom: 0.5rem;"><strong>Name:</strong> <?php echo htmlspecialchars($booking['farmer_name']); ?></p>
        <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($booking['farmer_email']); ?></p>
        <p style="margin-bottom: 0.5rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($booking['farmer_phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['farmer_address']); ?></p>
      </div>

      <div class="table-card" style="padding: 1.5rem; background: #fffbe6; border-color: #ffe58f;">
        <h4 style="color: #873800; margin-bottom: 0.5rem;"><i class="fas fa-info-circle"></i> Rental Instructions</h4>
        <p style="font-size: 0.85rem; color: #612500; line-height: 1.5;">
          Upon admin approval, the equipment will be prepared for delivery or pick-up on <strong><?php echo date('d M Y', strtotime($booking['start_date'])); ?></strong>. For any support or modification, contact our helpline.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
