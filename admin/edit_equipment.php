<?php
$page_title = "Edit Equipment";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

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
    setFlash('error', 'Equipment item not found.');
    header("Location: equipment.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $availability  = trim($_POST['availability'] ?? 'Available');
    $image_filename = $equipment['image'];

    if (empty($name) || empty($category) || empty($description) || $price_per_day <= 0) {
        $error = "Please fill in all required fields and provide a valid daily rental price.";
    } else {
        // Optional Image Update
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.]/', '', $fileName);
                $uploadFileDir = __DIR__ . '/../images/equipment/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image_filename = $newFileName;
                }
            }
        }

        try {
            $updateStmt = $conn->prepare("
                UPDATE equipment 
                SET name = ?, category = ?, description = ?, price_per_day = ?, availability = ?, image = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$name, $category, $description, $price_per_day, $availability, $image_filename, $equipment_id]);

            setFlash('success', 'Equipment #' . $equipment_id . ' updated successfully!');
            header("Location: equipment.php");
            exit();
        } catch (Exception $e) {
            $error = "Database update failed: " . $e->getMessage();
        }
    }
} else {
    $name          = $equipment['name'];
    $category      = $equipment['category'];
    $description   = $equipment['description'];
    $price_per_day = $equipment['price_per_day'];
    $availability  = $equipment['availability'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-edit"></i> Edit Equipment #EQ-<?php echo str_pad($equipment['id'], 3, '0', STR_PAD_LEFT); ?></h1>
      <p class="page-subtitle">Update machinery specifications, daily pricing, or availability state</p>
    </div>
    <a href="equipment.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Cancel &amp; Return</a>
  </div>

  <div class="auth-wrapper" style="max-width: 650px;">
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit_equipment.php?id=<?php echo $equipment_id; ?>" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label" for="name">Equipment Name *</label>
        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="category">Category *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="Tractors" <?php echo $category==='Tractors'?'selected':''; ?>>Tractors</option>
            <option value="Harvesting" <?php echo $category==='Harvesting'?'selected':''; ?>>Harvesting</option>
            <option value="Irrigation" <?php echo $category==='Irrigation'?'selected':''; ?>>Irrigation</option>
            <option value="Tillage" <?php echo $category==='Tillage'?'selected':''; ?>>Tillage</option>
            <option value="Planting" <?php echo $category==='Planting'?'selected':''; ?>>Planting</option>
            <option value="Pest Control" <?php echo $category==='Pest Control'?'selected':''; ?>>Pest Control</option>
            <option value="Implements" <?php echo $category==='Implements'?'selected':''; ?>>Implements</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="price_per_day">Rental Price Per Day (₹) *</label>
          <input type="number" step="0.01" id="price_per_day" name="price_per_day" class="form-control" value="<?php echo htmlspecialchars($price_per_day); ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Detailed Description *</label>
        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="availability">Availability Status *</label>
          <select id="availability" name="availability" class="form-control">
            <option value="Available" <?php echo $availability==='Available'?'selected':''; ?>>Available</option>
            <option value="Unavailable" <?php echo $availability==='Unavailable'?'selected':''; ?>>Unavailable</option>
            <option value="Maintenance" <?php echo $availability==='Maintenance'?'selected':''; ?>>Maintenance</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="image">Update Image (Optional)</label>
          <input type="file" id="image" name="image" class="form-control" accept="image/*">
        </div>
      </div>

      <div style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 1rem; background: #f8faf7; padding: 0.75rem; border-radius: 8px;">
        <img src="../images/equipment/<?php echo htmlspecialchars($equipment['image']); ?>" alt="" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover;" onerror="this.src='../images/equipment/default_equipment.jpg'">
        <small style="color: var(--text-muted);">Current Image: <code><?php echo htmlspecialchars($equipment['image']); ?></code></small>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;"><i class="fas fa-save"></i> Save Changes</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
