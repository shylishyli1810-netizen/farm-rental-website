<?php
$page_title = "Book Equipment";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

$farmer_id = $_SESSION['farmer_id'];
$farmer_name = $_SESSION['farmer_name'];
$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$equipment = null;
$error = '';

try {
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch();
} catch (Exception $e) {
    $equipment = null;
}

if (!$equipment) {
    setFlash('error', 'Please select a valid equipment to book.');
    header("Location: equipment.php");
    exit();
}

if ($equipment['availability'] !== 'Available') {
    setFlash('error', 'This equipment is currently unavailable for booking.');
    header("Location: equipment_details.php?id=" . $equipment_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date   = trim($_POST['end_date'] ?? '');

    if (empty($start_date) || empty($end_date)) {
        $error = "Please select both start date and end date.";
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = "End date cannot be earlier than start date.";
    } else {
        // Calculate days (inclusive)
        $diff = strtotime($end_date) - strtotime($start_date);
        $days = floor($diff / (60 * 60 * 24)) + 1;
        $total_amount = $days * floatval($equipment['price_per_day']);

        // Check date conflict with existing Approved or Pending bookings for this equipment
        try {
            $conflictStmt = $conn->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE equipment_id = ? 
                AND status IN ('Pending', 'Approved') 
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )
            ");
            $conflictStmt->execute([
                $equipment_id,
                $start_date, $start_date,
                $end_date, $end_date,
                $start_date, $end_date
            ]);
            $conflict_count = $conflictStmt->fetchColumn();

            if ($conflict_count > 0) {
                $error = "Sorry! This equipment is already booked for the selected dates. Please choose different rental dates.";
            } else {
                // Save Booking to Database
                $insertStmt = $conn->prepare("
                    INSERT INTO bookings (farmer_id, equipment_id, start_date, end_date, days, total_amount, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending')
                ");
                $insertStmt->execute([
                    $farmer_id,
                    $equipment_id,
                    $start_date,
                    $end_date,
                    $days,
                    $total_amount
                ]);

                setFlash('success', 'Booking request submitted successfully! Booking status is now Pending admin approval.');
                header("Location: bookings.php");
                exit();
            }
        } catch (Exception $e) {
            $error = "Failed to process booking: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header">
    <a href="equipment_details.php?id=<?php echo $equipment_id; ?>" class="btn btn-outline-dark btn-sm" style="margin-bottom: 1rem;"><i class="fas fa-arrow-left"></i> Back to Equipment Details</a>
    <h1 class="page-title"><i class="fas fa-calendar-check"></i> Book Equipment Online</h1>
    <p class="page-subtitle">Fill in rental start and end dates to complete your booking</p>
  </div>

  <div class="auth-wrapper" style="max-width: 650px;">
    <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="book.php?id=<?php echo $equipment_id; ?>" method="POST" id="bookingForm">
      <!-- EQUIPMENT SUMMARY HEADER -->
      <div style="display: flex; gap: 1rem; align-items: center; background: #f8faf7; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
        <img src="../images/equipment/<?php echo htmlspecialchars($equipment['image']); ?>" alt="" style="width: 70px; height: 70px; border-radius: 8px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
        <div>
          <h3 style="color: var(--primary-dark); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.2rem;"><?php echo htmlspecialchars($equipment['name']); ?></h3>
          <p style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($equipment['category']); ?></p>
          <div style="color: var(--primary); font-weight: 700; font-size: 0.95rem; margin-top: 0.2rem;">
            ₹ <?php echo number_format($equipment['price_per_day'], 2); ?> / day
          </div>
        </div>
      </div>

      <input type="hidden" id="price_per_day" value="<?php echo htmlspecialchars($equipment['price_per_day']); ?>">
      <input type="hidden" name="days" id="days_hidden" value="1">
      <input type="hidden" name="total_amount" id="total_amount_hidden" value="<?php echo htmlspecialchars($equipment['price_per_day']); ?>">

      <div class="form-group">
        <label class="form-label">Farmer Name</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($farmer_name); ?>" readonly style="background: #e2e8f0;">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="start_date">Rental Start Date *</label>
          <input type="date" id="start_date" name="start_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="end_date">Rental End Date *</label>
          <input type="date" id="end_date" name="end_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>

      <!-- LIVE CALCULATION SUMMARY BOX -->
      <div class="booking-summary-box">
        <h4 style="color: var(--primary-dark); margin-bottom: 0.75rem;"><i class="fas fa-calculator"></i> Price Calculation Summary</h4>
        <div class="summary-row">
          <span>Daily Rental Price:</span>
          <strong>₹ <?php echo number_format($equipment['price_per_day'], 2); ?> / day</strong>
        </div>
        <div class="summary-row">
          <span>Calculated Days:</span>
          <strong id="calc_days">1</strong> day(s)
        </div>
        <div class="summary-row summary-total">
          <span>Total Payable Amount:</span>
          <span id="calc_total" style="color: var(--primary);">₹ <?php echo number_format($equipment['price_per_day'], 2); ?></span>
        </div>
      </div>

      <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 1.5rem;"><i class="fas fa-check-circle"></i> Confirm &amp; Submit Booking</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
