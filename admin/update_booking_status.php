<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$new_status = trim($_GET['status'] ?? '');
$allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Completed'];

if ($booking_id > 0 && in_array($new_status, $allowed_statuses)) {
    try {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
        setFlash('success', 'Booking #' . $booking_id . ' status updated to ' . $new_status . '.');
    } catch (Exception $e) {
        setFlash('error', 'Failed to update booking status: ' . $e->getMessage());
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'bookings.php';
header("Location: " . $referer);
exit();
?>
