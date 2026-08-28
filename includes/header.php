<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';

// Base URL helper
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = strpos($script_name, '/farmer/') !== false || strpos($script_name, '/admin/') !== false ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " - Farm Tools Rental" : "Farm Tools Rental Platform"; ?></title>
  <!-- Google Fonts & FontAwesome -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>

<header class="header-site">
  <div class="nav-container">
    <a href="<?php echo $base_path; ?>index.php" class="brand-logo">
      <i class="fas fa-tractor"></i>
      <span>Farm Tools Rental</span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <i class="fas fa-bars"></i>
    </button>

    <ul class="nav-menu" id="navMenu">
      <li><a href="<?php echo $base_path; ?>index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="<?php echo $base_path; ?>about.php" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a></li>
      <li><a href="<?php echo $base_path; ?>equipment.php" class="nav-link"><i class="fas fa-tools"></i> Equipment</a></li>
      <li><a href="<?php echo $base_path; ?>contact.php" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a></li>

      <?php if (isFarmerLoggedIn()): ?>
        <li><a href="<?php echo $base_path; ?>farmer/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Farmer Portal</a></li>
        <li><a href="<?php echo $base_path; ?>farmer/bookings.php" class="nav-link"><i class="fas fa-calendar-check"></i> My Bookings</a></li>
        <li><a href="<?php echo $base_path; ?>farmer/profile.php" class="nav-link"><i class="fas fa-user-circle"></i> Profile</a></li>
        <li><a href="<?php echo $base_path; ?>farmer/logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      <?php elseif (isAdminLoggedIn()): ?>
        <li><a href="<?php echo $base_path; ?>admin/dashboard.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
        <li><a href="<?php echo $base_path; ?>admin/equipment.php" class="nav-link"><i class="fas fa-cubes"></i> Manage Tools</a></li>
        <li><a href="<?php echo $base_path; ?>admin/reports.php" class="nav-link"><i class="fas fa-chart-line"></i> Reports</a></li>
        <li><a href="<?php echo $base_path; ?>admin/logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      <?php else: ?>
        <li class="nav-btn-group">
          <a href="<?php echo $base_path; ?>login.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-in-alt"></i> Farmer Login</a>
          <a href="<?php echo $base_path; ?>register.php" class="btn btn-accent btn-sm"><i class="fas fa-user-plus"></i> Register</a>
          <a href="<?php echo $base_path; ?>admin/login.php" class="btn btn-primary btn-sm" style="background: rgba(255,255,255,0.15);"><i class="fas fa-lock"></i> Admin</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</header>

<main class="main-content">
<?php echo displayFlash(); ?>
