<?php
$page_title = "Rent Agricultural Equipment Easily";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

// Fetch popular equipment from DB
$popular_equipment = [];
try {
    $stmt = $conn->query("SELECT * FROM equipment WHERE availability = 'Available' ORDER BY id ASC LIMIT 3");
    $popular_equipment = $stmt->fetchAll();
} catch (Exception $e) {
    // Graceful fallback if database connection issue
    $popular_equipment = [];
}
?>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="hero-content">
    <span class="hero-tag"><i class="fas fa-seedling"></i> Modern Agricultural Solutions</span>
    <h1 class="hero-title">Rent Agricultural Equipment Easily</h1>
    <p class="hero-desc">
      Empowering farmers across the region to rent top-quality machinery online. Save capital, boost crop yield, and streamline your farming operations without heavy equipment ownership costs.
    </p>
    <div class="hero-actions">
      <a href="equipment.php" class="btn btn-accent btn-lg"><i class="fas fa-search"></i> Browse Equipment</a>
      <a href="register.php" class="btn btn-outline btn-lg"><i class="fas fa-user-plus"></i> Get Started</a>
    </div>
  </div>
</section>

<!-- POPULAR EQUIPMENT SECTION -->
<section class="section-padding">
  <div class="container">
    <div class="section-title-wrap">
      <h2 class="section-title">Popular Farming Equipment</h2>
      <p class="section-desc">Explore our top-rated, well-maintained tractors and agricultural implements available for immediate online booking.</p>
    </div>

    <div class="grid-3">
      <?php if (!empty($popular_equipment)): ?>
        <?php foreach ($popular_equipment as $item): ?>
          <div class="equipment-card">
            <div class="equipment-img-wrap">
              <span class="equipment-category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
              <span class="availability-badge badge-available"><i class="fas fa-check-circle"></i> Available</span>
              <img src="images/equipment/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="equipment-img" onerror="this.src='images/equipment/default_equipment.jpg'">
            </div>
            <div class="equipment-body">
              <h3 class="equipment-title"><?php echo htmlspecialchars($item['name']); ?></h3>
              <p class="equipment-desc"><?php echo htmlspecialchars($item['description']); ?></p>
              <div class="equipment-price-row">
                <div>
                  <span class="price-amount">₹ <?php echo number_format($item['price_per_day'], 2); ?></span>
                  <span class="price-unit">/ day</span>
                </div>
              </div>
              <div class="equipment-footer">
                <a href="farmer/equipment_details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-eye"></i> Details</a>
                <a href="farmer/book.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart"></i> Book Now</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; grid-column: 1/-1;" class="text-muted">No equipment loaded yet. Please run SQL seed script.</p>
      <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 2.5rem;">
      <a href="equipment.php" class="btn btn-primary btn-lg"><i class="fas fa-th-large"></i> View All Equipment Catalog</a>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US SECTION -->
<section class="section-padding" style="background: #ffffff; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
  <div class="container">
    <div class="section-title-wrap">
      <h2 class="section-title">Why Choose Farm Tools Rental?</h2>
      <p class="section-desc">We deliver hassle-free machinery rental with transparent pricing and guaranteed field performance.</p>
    </div>

    <div class="grid-4">
      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <h3 class="feature-title">Affordable Daily Rates</h3>
        <p class="feature-desc">Pay only for the days you use. Eliminate high capital investments and maintenance overheads.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
        <h3 class="feature-title">Verified Equipment</h3>
        <p class="feature-desc">All tractors, harvesters, and tools undergo strict safety checks and maintenance before dispatch.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-clock"></i></div>
        <h3 class="feature-title">Instant Online Booking</h3>
        <p class="feature-desc">Check live availability and book machinery in under 2 minutes from your smartphone.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon"><i class="fas fa-headset"></i></div>
        <h3 class="feature-title">24/7 Farmer Support</h3>
        <p class="feature-desc">Dedicated helpline assistance to help you choose the right machinery for your farm size and crop type.</p>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS SECTION -->
<section class="section-padding">
  <div class="container">
    <div class="section-title-wrap">
      <h2 class="section-title">How It Works</h2>
      <p class="section-desc">Rent equipment in 4 quick and easy steps</p>
    </div>

    <div class="grid-4">
      <div class="step-card feature-card">
        <div class="step-number">1</div>
        <h3 class="feature-title">Register Account</h3>
        <p class="feature-desc">Create your free farmer profile with basic contact and farm location details.</p>
      </div>

      <div class="step-card feature-card">
        <div class="step-number">2</div>
        <h3 class="feature-title">Select Tool</h3>
        <p class="feature-desc">Browse our extensive inventory of tractors, harvesters, cultivators, and pumps.</p>
      </div>

      <div class="step-card feature-card">
        <div class="step-number">3</div>
        <h3 class="feature-title">Book Dates</h3>
        <p class="feature-desc">Pick your rental start date and end date with auto-calculated total pricing.</p>
      </div>

      <div class="step-card feature-card">
        <div class="step-number">4</div>
        <h3 class="feature-title">Work &amp; Return</h3>
        <p class="feature-desc">Get the machinery delivered or pick it up, complete your farming tasks, and return smoothly.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
