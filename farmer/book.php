<?php
$page_title = "Book Equipment";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireFarmerLogin();

// Auto-add payment columns if not existing
try {
    $conn->exec("ALTER TABLE bookings ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash on Delivery'");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE bookings ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending'");
} catch (Exception $e) {}
try {
    $conn->exec("ALTER TABLE bookings ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL");
} catch (Exception $e) {}

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
    $start_date     = trim($_POST['start_date'] ?? '');
    $end_date       = trim($_POST['end_date'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'Cash on Delivery');
    $transaction_id = trim($_POST['transaction_id'] ?? '');

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
                $pay_status = ($payment_method === 'UPI / GPay / PhonePe' && !empty($transaction_id)) ? 'Paid' : 'Pending';

                // Save Booking to Database
                try {
                    $insertStmt = $conn->prepare("
                        INSERT INTO bookings (farmer_id, equipment_id, start_date, end_date, days, total_amount, status, payment_method, payment_status, transaction_id)
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $farmer_id,
                        $equipment_id,
                        $start_date,
                        $end_date,
                        $days,
                        $total_amount,
                        $payment_method,
                        $pay_status,
                        $transaction_id
                    ]);
                } catch (Exception $fallbackEx) {
                    // Fallback if older schema
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
                }

                $new_booking_id = $conn->lastInsertId();

                setFlash('success', 'Booking request placed successfully! You can view and print your rental slip now.');
                header("Location: booking_details.php?id=" . $new_booking_id);
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
    <h1 class="page-title"><i class="fas fa-calendar-check"></i> Book Equipment &amp; Select Payment</h1>
    <p class="page-subtitle">Fill in rental dates and select your preferred payment mode</p>
  </div>

  <div class="auth-wrapper" style="max-width: 680px;">
    <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="book.php?id=<?php echo $equipment_id; ?>" method="POST" id="bookingForm">
      <!-- EQUIPMENT SUMMARY HEADER -->
      <div style="display: flex; gap: 1rem; align-items: center; background: #f8faf7; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
        <img src="../images/equipment/<?php echo htmlspecialchars($equipment['image']); ?>" alt="" style="width: 75px; height: 75px; border-radius: 8px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
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
      <div class="booking-summary-box" style="margin-bottom: 1.5rem;">
        <h4 style="color: var(--primary-dark); margin-bottom: 0.75rem;"><i class="fas fa-calculator"></i> Price Calculation Summary</h4>
        <div class="summary-row">
          <span>Daily Rental Price:</span>
          <strong>₹ <?php echo number_format($equipment['price_per_day'], 2); ?> / day</strong>
        </div>
        <div class="summary-row">
          <span>Calculated Duration:</span>
          <strong id="calc_days">1</strong> day(s)
        </div>
        <div class="summary-row summary-total">
          <span>Total Payable Amount:</span>
          <span id="calc_total" style="color: var(--primary);">₹ <?php echo number_format($equipment['price_per_day'], 2); ?></span>
        </div>
      </div>

      <!-- PAYMENT METHOD SELECTION -->
      <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
        <h4 style="color: var(--primary-dark); font-size: 1.05rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fas fa-credit-card" style="color: var(--accent-gold);"></i> Choose Payment Method (கட்டண முறை)
        </h4>

        <!-- Option 1: Cash on Handover -->
        <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s;" id="label_cash">
          <input type="radio" name="payment_method" value="Cash on Delivery" checked style="margin-top: 0.3rem;" onchange="togglePaymentView('cash')">
          <div>
            <strong style="color: var(--primary-dark); display: block;"><i class="fas fa-money-bill-wave" style="color: #16a34a;"></i> Cash on Handover / Delivery (நேரடி பணம்)</strong>
            <small style="color: var(--text-muted);">Pay the full amount directly in cash when the machinery is delivered or picked up.</small>
          </div>
        </label>

        <!-- Option 2: UPI / GPay / PhonePe / QR Code -->
        <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s;" id="label_upi">
          <input type="radio" name="payment_method" value="UPI / GPay / PhonePe" style="margin-top: 0.3rem;" onchange="togglePaymentView('upi')">
          <div>
            <strong style="color: var(--primary-dark); display: block;"><i class="fab fa-google-pay" style="color: #2563eb;"></i> UPI / GPay / PhonePe / QR Code (ஆன்லைன் கட்டணம்)</strong>
            <small style="color: var(--text-muted);">Instant online payment via Google Pay, PhonePe, Paytm or scanning QR code.</small>
          </div>
        </label>

        <!-- Option 3: Bank Transfer -->
        <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; cursor: pointer; transition: all 0.2s;" id="label_bank">
          <input type="radio" name="payment_method" value="Bank Transfer" style="margin-top: 0.3rem;" onchange="togglePaymentView('bank')">
          <div>
            <strong style="color: var(--primary-dark); display: block;"><i class="fas fa-university" style="color: #d97706;"></i> Direct Bank Transfer (NEFT / IMPS)</strong>
            <small style="color: var(--text-muted);">Transfer directly to our official bank account.</small>
          </div>
        </label>

        <!-- UPI PAYMENT DETAILS BOX -->
        <div id="upi_box" style="display: none; margin-top: 1.25rem; padding: 1.25rem; background: #f0fdf4; border: 1.5px dashed #16a34a; border-radius: 8px;">
          <h5 style="color: #166534; font-size: 0.95rem; margin-bottom: 0.75rem;"><i class="fas fa-qrcode"></i> Scan UPI QR Code or Pay to Mobile Number</h5>
          <div style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;">
            <div style="background: #fff; padding: 0.5rem; border-radius: 8px; border: 1px solid #bbf7d0; text-align: center;">
              <img id="upi_qr_img" src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=upi://pay?pa=8122844191@okaxis%26pn=FarmToolsRental%26cu=INR" alt="UPI QR Code" style="width: 130px; height: 130px; display: block;">
              <small style="color: #166534; font-size: 0.75rem; font-weight: 700;">Scan to Pay</small>
            </div>
            <div style="flex: 1; min-width: 220px; font-size: 0.88rem; color: #166534;">
              <p style="margin-bottom: 0.4rem;"><strong>📱 GPay / PhonePe No:</strong> <code style="background:#fff; padding:0.2rem 0.5rem; border-radius:4px; font-size:0.95rem; color:#15803d; font-weight:700;">+91 81228 44191</code></p>
              <p style="margin-bottom: 0.4rem;"><strong>🆔 UPI ID:</strong> <code style="background:#fff; padding:0.2rem 0.5rem; border-radius:4px; font-size:0.95rem; color:#15803d; font-weight:700;">8122844191@okaxis</code></p>
              <p style="margin-bottom: 0.75rem;"><strong>👤 Payee:</strong> Farm Tools Rental Platform</p>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.8rem; color: #166534;">UTR / Transaction Reference No. (பரிவர்த்தனை எண் - Optional)</label>
                <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="e.g. 324567891234" style="background:#fff;">
              </div>
            </div>
          </div>
        </div>

        <!-- BANK DETAILS BOX -->
        <div id="bank_box" style="display: none; margin-top: 1.25rem; padding: 1.25rem; background: #fffbeb; border: 1.5px dashed #d97706; border-radius: 8px; font-size: 0.9rem; color: #92400e;">
          <h5 style="color: #92400e; margin-bottom: 0.5rem;"><i class="fas fa-building"></i> Official Bank Details:</h5>
          <p><strong>Bank:</strong> State Bank of India (SBI)</p>
          <p><strong>Account Name:</strong> Farm Tools Rental Platform</p>
          <p><strong>A/C No:</strong> 39824510294</p>
          <p><strong>IFSC Code:</strong> SBIN0001234 (Guindy Branch, Chennai)</p>
        </div>
      </div>

      <button type="submit" class="btn btn-accent btn-lg" style="width: 100%;"><i class="fas fa-check-circle"></i> Confirm Booking &amp; Generate Slip</button>
    </form>
  </div>
</div>

<script>
function togglePaymentView(mode) {
  const upiBox = document.getElementById('upi_box');
  const bankBox = document.getElementById('bank_box');
  const labelCash = document.getElementById('label_cash');
  const labelUpi = document.getElementById('label_upi');
  const labelBank = document.getElementById('label_bank');

  // Reset borders
  labelCash.style.borderColor = '#cbd5e1';
  labelUpi.style.borderColor = '#cbd5e1';
  labelBank.style.borderColor = '#cbd5e1';

  if (mode === 'upi') {
    upiBox.style.display = 'block';
    bankBox.style.display = 'none';
    labelUpi.style.borderColor = '#16a34a';
  } else if (mode === 'bank') {
    upiBox.style.display = 'none';
    bankBox.style.display = 'block';
    labelBank.style.borderColor = '#d97706';
  } else {
    upiBox.style.display = 'none';
    bankBox.style.display = 'none';
    labelCash.style.borderColor = '#16a34a';
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
