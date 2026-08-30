<?php
$page_title = "Equipment Rental Agreement & Receipt";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Allow access if Farmer is logged in OR Admin is logged in
$is_farmer = isFarmerLoggedIn();
$is_admin  = isAdminLoggedIn();

if (!$is_farmer && !$is_admin) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$booking = null;

try {
    if ($is_admin) {
        $stmt = $conn->prepare("
            SELECT b.*, e.name AS equipment_name, e.category, e.description AS equipment_desc, e.image AS equipment_image, e.price_per_day,
                   f.name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone, f.address AS farmer_address
            FROM bookings b
            JOIN equipment e ON b.equipment_id = e.id
            JOIN farmers f ON b.farmer_id = f.id
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
    } else {
        $farmer_id = $_SESSION['farmer_id'];
        $stmt = $conn->prepare("
            SELECT b.*, e.name AS equipment_name, e.category, e.description AS equipment_desc, e.image AS equipment_image, e.price_per_day,
                   f.name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone, f.address AS farmer_address
            FROM bookings b
            JOIN equipment e ON b.equipment_id = e.id
            JOIN farmers f ON b.farmer_id = f.id
            WHERE b.id = ? AND b.farmer_id = ?
        ");
        $stmt->execute([$booking_id, $farmer_id]);
    }
    $booking = $stmt->fetch();
} catch (Exception $e) {
    $booking = null;
}

if (!$booking) {
    echo "<p style='font-family:sans-serif; text-align:center; padding:3rem;'>Booking receipt not found or access denied. <a href='index.php'>Go Home</a></p>";
    exit();
}

// Clean phone for WhatsApp
$clean_phone = preg_replace('/[^0-9]/', '', $booking['farmer_phone']);
if (strlen($clean_phone) === 10) {
    $clean_phone = '91' . $clean_phone;
}

$whatsapp_text = urlencode(
    "🚜 *Farm Tools Rental - Booking Receipt & Handover Proof*\n\n" .
    "📌 *Booking ID:* #BK-" . str_pad($booking['id'], 4, '0', STR_PAD_LEFT) . "\n" .
    "👤 *Farmer Name:* " . $booking['farmer_name'] . "\n" .
    "🛠️ *Equipment:* " . $booking['equipment_name'] . " (" . $booking['category'] . ")\n" .
    "📅 *Rental Dates:* " . date('d-M-Y', strtotime($booking['start_date'])) . " to " . date('d-M-Y', strtotime($booking['end_date'])) . " (" . $booking['days'] . " Days)\n" .
    "💰 *Total Amount:* ₹ " . number_format($booking['total_amount'], 2) . "\n" .
    "⚡ *Status:* " . $booking['status'] . "\n\n" .
    "📍 *Pickup/Support:* Anna Salai, Guindy, Chennai\n" .
    "📞 *Helpline:* +91 81228 44191\n\n" .
    "Thank you for choosing Farm Tools Rental!"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rental Receipt #BK-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?> - Farm Tools Rental</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #1b4d2e;
      --primary-dark: #123720;
      --accent: #d97706;
      --accent-gold: #f59e0b;
      --text-main: #1f2937;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --bg: #f8faf7;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text-main);
      line-height: 1.5;
      padding: 2rem 1rem;
    }

    .action-bar {
      max-width: 820px;
      margin: 0 auto 1.5rem auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1.2rem;
      font-size: 0.9rem;
      font-weight: 600;
      border-radius: 8px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-print {
      background: var(--primary);
      color: #fff;
    }

    .btn-print:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }

    .btn-whatsapp {
      background: #25d366;
      color: #fff;
    }

    .btn-whatsapp:hover {
      background: #1ebc57;
      transform: translateY(-1px);
    }

    .btn-back {
      background: #fff;
      color: var(--text-main);
      border: 1px solid var(--border);
    }

    .btn-back:hover {
      background: #f1f5f9;
    }

    /* RECEIPT LETTER CARD */
    .receipt-card {
      max-width: 820px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      border: 1px solid var(--border);
      padding: 2.5rem;
      position: relative;
    }

    /* HEADER */
    .receipt-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid var(--primary);
      padding-bottom: 1.5rem;
      margin-bottom: 1.75rem;
      gap: 1.5rem;
    }

    .brand-section h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 1.5rem;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.25rem;
    }

    .brand-section p {
      font-size: 0.85rem;
      color: var(--text-muted);
      line-height: 1.4;
    }

    .receipt-meta {
      text-align: right;
    }

    .receipt-badge {
      display: inline-block;
      background: #dcfce7;
      color: #15803d;
      font-size: 0.8rem;
      font-weight: 700;
      padding: 0.3rem 0.75rem;
      border-radius: 50px;
      text-transform: uppercase;
      margin-bottom: 0.4rem;
    }

    .receipt-title {
      font-family: 'Outfit', sans-serif;
      font-size: 1.25rem;
      color: var(--primary-dark);
      font-weight: 700;
    }

    .receipt-id {
      font-size: 0.95rem;
      color: var(--text-muted);
      font-weight: 600;
    }

    /* TWO-COL INFO */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-bottom: 1.75rem;
      background: #f8faf7;
      padding: 1.25rem;
      border-radius: 8px;
      border: 1px solid #eef2eb;
    }

    .info-block h4 {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--text-muted);
      margin-bottom: 0.5rem;
    }

    .info-block p {
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
    }

    .info-block strong {
      color: var(--primary-dark);
    }

    /* EQUIPMENT DETAILS TABLE */
    .table-receipt {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1.75rem;
    }

    .table-receipt th {
      background: #f1f5f9;
      color: var(--primary-dark);
      font-size: 0.85rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 0.75rem 1rem;
      text-align: left;
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }

    .table-receipt td {
      padding: 1rem;
      border-bottom: 1px solid var(--border);
      font-size: 0.9rem;
      vertical-align: middle;
    }

    .table-receipt td.text-right,
    .table-receipt th.text-right {
      text-align: right;
    }

    /* TOTALS */
    .total-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 2rem;
    }

    .total-box {
      width: 320px;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      padding: 0.4rem 0;
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .total-row.grand {
      border-top: 2px solid var(--primary);
      margin-top: 0.5rem;
      padding-top: 0.6rem;
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--primary-dark);
    }

    /* TERMS & HANDOVER PROOF */
    .terms-box {
      background: #fffdf5;
      border: 1px dashed #d97706;
      border-radius: 8px;
      padding: 1rem 1.25rem;
      margin-bottom: 2.25rem;
    }

    .terms-box h4 {
      font-size: 0.85rem;
      color: #92400e;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }

    .terms-box ul {
      padding-left: 1.2rem;
      font-size: 0.8rem;
      color: #78350f;
      line-height: 1.6;
    }

    /* SIGNATURE SECTION */
    .sig-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      margin-top: 2rem;
      padding-top: 1.5rem;
    }

    .sig-box {
      text-align: center;
    }

    .sig-line {
      border-top: 1.5px dashed #94a3b8;
      margin-bottom: 0.5rem;
      height: 40px;
    }

    .sig-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    .sig-sub {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    .receipt-footer-note {
      text-align: center;
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid var(--border);
    }

    /* PRINT STYLES */
    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .action-bar {
        display: none !important;
      }

      .receipt-card {
        border: none;
        box-shadow: none;
        padding: 0;
        max-width: 100%;
      }

      @page {
        margin: 1.5cm;
      }
    }
  </style>
</head>
<body>

  <!-- TOP ACTION BAR -->
  <div class="action-bar">
    <div>
      <?php if ($is_admin): ?>
        <a href="admin/bookings.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
      <?php else: ?>
        <a href="farmer/bookings.php" class="btn-action btn-back"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
      <?php endif; ?>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <?php if (!empty($clean_phone)): ?>
        <a href="https://wa.me/<?php echo $clean_phone; ?>?text=<?php echo $whatsapp_text; ?>" target="_blank" class="btn-action btn-whatsapp" title="Send Receipt via WhatsApp">
          <i class="fab fa-whatsapp"></i> Share on WhatsApp
        </a>
      <?php endif; ?>
      <button type="button" onclick="window.print()" class="btn-action btn-print">
        <i class="fas fa-print"></i> Print / Download PDF
      </button>
    </div>
  </div>

  <!-- PRINTABLE RECEIPT CARD -->
  <div class="receipt-card">
    <!-- HEADER -->
    <div class="receipt-header">
      <div class="brand-section">
        <h1><i class="fas fa-tractor" style="color: var(--accent-gold);"></i> Farm Tools Rental</h1>
        <p>
          📍 Anna Salai, Guindy, Chennai, Tamil Nadu - 600032<br>
          📞 Helpline: +91 81228 44191 | ✉️ shylishyli1810@gmail.com
        </p>
      </div>
      <div class="receipt-meta">
        <span class="receipt-badge"><?php echo htmlspecialchars($booking['status']); ?></span>
        <div class="receipt-title">Rental Handover Slip</div>
        <div class="receipt-id">#BK-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></div>
        <small style="color: var(--text-muted); display: block; margin-top: 0.2rem;">Date: <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></small>
      </div>
    </div>

    <!-- FARMER & RENTAL TIMELINE -->
    <div class="info-grid">
      <div class="info-block">
        <h4><i class="fas fa-user"></i> Renter (Farmer) Information</h4>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['farmer_name']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['farmer_phone']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['farmer_email']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($booking['farmer_address']); ?></p>
      </div>
      <div class="info-block">
        <h4><i class="fas fa-calendar-check"></i> Rental Schedule & Duration</h4>
        <p><strong>Start Date:</strong> <?php echo date('d M Y (D)', strtotime($booking['start_date'])); ?></p>
        <p><strong>End Date:</strong> <?php echo date('d M Y (D)', strtotime($booking['end_date'])); ?></p>
        <p><strong>Total Duration:</strong> <?php echo $booking['days']; ?> Day(s)</p>
        <p><strong>Handover Status:</strong> Verified &amp; Approved</p>
      </div>
    </div>

    <!-- EQUIPMENT ITEM TABLE -->
    <table class="table-receipt">
      <thead>
        <tr>
          <th>Equipment / Machine Description</th>
          <th>Category</th>
          <th class="text-right">Daily Rate</th>
          <th class="text-right">Rental Days</th>
          <th class="text-right">Total Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong><?php echo htmlspecialchars($booking['equipment_name']); ?></strong>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;"><?php echo htmlspecialchars($booking['equipment_desc']); ?></div>
          </td>
          <td><span style="background: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($booking['category']); ?></span></td>
          <td class="text-right">₹ <?php echo number_format($booking['price_per_day'], 2); ?></td>
          <td class="text-right"><?php echo $booking['days']; ?></td>
          <td class="text-right"><strong>₹ <?php echo number_format($booking['total_amount'], 2); ?></strong></td>
        </tr>
      </tbody>
    </table>

    <!-- TOTALS -->
    <div class="total-section">
      <div class="total-box">
        <div class="total-row">
          <span>Subtotal (₹ <?php echo number_format($booking['price_per_day'], 2); ?> &times; <?php echo $booking['days']; ?> d):</span>
          <span>₹ <?php echo number_format($booking['total_amount'], 2); ?></span>
        </div>
        <div class="total-row">
          <span>Maintenance &amp; Inspection Fee:</span>
          <span style="color: #16a34a;">FREE (₹ 0.00)</span>
        </div>
        <div class="total-row grand">
          <span>Total Rental Amount:</span>
          <span>₹ <?php echo number_format($booking['total_amount'], 2); ?></span>
        </div>
      </div>
    </div>

    <!-- TERMS & CONDITIONS / HANDOVER PROOF -->
    <div class="terms-box">
      <h4><i class="fas fa-file-contract"></i> Equipment Handover & Rental Terms (விதிமுறைகள் &amp; ஒப்பந்தம்)</h4>
      <ul>
        <li>The machinery is inspected and handed over in clean, fully working mechanical condition.</li>
        <li>Farmer is responsible for daily fuel, lubricants, and standard operational safety.</li>
        <li>The equipment must be returned safely on or before <strong><?php echo date('d M Y', strtotime($booking['end_date'])); ?> (6:00 PM)</strong>.</li>
        <li>Any accidental damage or breakdown during the rental period must be notified immediately to <strong>+91 81228 44191</strong>.</li>
      </ul>
    </div>

    <!-- SIGNATURE BLOCKS (OFFICIAL HANDOVER PROOF) -->
    <div class="sig-grid">
      <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-title">Equipment Owner / Authorized Signatory</div>
        <div class="sig-sub">Farm Tools Rental Platform, Chennai</div>
      </div>
      <div class="sig-box">
        <div class="sig-line"></div>
        <div class="sig-title">Farmer / Receiver Signature (ஒப்பம்)</div>
        <div class="sig-sub"><?php echo htmlspecialchars($booking['farmer_name']); ?></div>
      </div>
    </div>

    <div class="receipt-footer-note">
      This is an official computer-generated rental acknowledgment slip from Farm Tools Rental Platform.
    </div>
  </div>

</body>
</html>
