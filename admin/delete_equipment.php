<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($equipment_id > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$equipment_id]);
        setFlash('success', 'Equipment #' . $equipment_id . ' deleted successfully.');
    } catch (Exception $e) {
        setFlash('error', 'Unable to delete equipment: ' . $e->getMessage());
    }
}

header("Location: equipment.php");
exit();
?>
