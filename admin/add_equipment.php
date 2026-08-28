<?php
$page_title = "Add New Equipment";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$name = $category = $description = $price_per_day = $availability = '';
$availability = 'Available';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $availability  = trim($_POST['availability'] ?? 'Available');
    $image_filename = 'default_equipment.jpg';

    if (empty($name) || empty($category) || empty($description) || $price_per_day <= 0) {
        $error = "Please fill in all required fields and provide a valid daily rental price.";
    } else {
        // Image Upload Handling
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
            $insertStmt = $conn->prepare("
                INSERT INTO equipment (name, category, description, price_per_day, availability, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([$name, $category, $description, $price_per_day, $availability, $image_filename]);

            setFlash('success', 'New machinery item "' . htmlspecialchars($name) . '" added successfully!');
            header("Location: equipment.php");
            exit();
        } catch (Exception $e) {
            $error = "Database insertion failed: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 class="page-title"><i class="fas fa-plus-circle"></i> Add Equipment</h1>
      <p class="page-subtitle">Register new agricultural machinery into inventory catalog</p>
    </div>
    <a href="equipment.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Back to List</a>
  </div>

  <div class="auth-wrapper" style="max-width: 650px;">
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add_equipment.php" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label" for="name">Equipment Name *</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Combine Harvester 75 HP" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="category">Category *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="">Select Category</option>
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
          <input type="number" step="0.01" id="price_per_day" name="price_per_day" class="form-control" placeholder="e.g. 1500.00" value="<?php echo htmlspecialchars($price_per_day); ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Detailed Description *</label>
        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter specifications, horsepower, features..." required><?php echo htmlspecialchars($description); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="availability">Initial Availability Status</label>
          <select id="availability" name="availability" class="form-control">
            <option value="Available" <?php echo $availability==='Available'?'selected':''; ?>>Available</option>
            <option value="Unavailable" <?php echo $availability==='Unavailable'?'selected':''; ?>>Unavailable</option>
            <option value="Maintenance" <?php echo $availability==='Maintenance'?'selected':''; ?>>Maintenance</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="image">Equipment Image File</label>
          <input type="file" id="image" name="image" class="form-control" accept="image/*">
        </div>
      </div>

      <button type="submit" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 1rem;"><i class="fas fa-check"></i> Add Machinery to Catalog</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
