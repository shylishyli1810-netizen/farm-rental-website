<?php
$page_title = "About Us";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="page-header text-center" style="text-align: center; margin-top: 1.5rem;">
    <h1 class="page-title" style="justify-content: center;"><i class="fas fa-leaf"></i> About Farm Tools Rental</h1>
    <p class="page-subtitle">Transforming traditional farming through digital machinery sharing</p>
  </div>

  <div style="background: var(--bg-card); padding: 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 2.5rem;">
    <div style="background: var(--accent-light); border-left: 5px solid var(--accent-gold); padding: 1.5rem; border-radius: var(--radius-sm); margin-bottom: 2rem;">
      <h3 style="color: #92400e; margin-bottom: 0.5rem;"><i class="fas fa-quote-left"></i> Our Tagline</h3>
      <blockquote style="font-size: 1.25rem; font-weight: 700; color: #78350f;">
        “Helping farmers easily rent agricultural equipment, improving productivity and promoting sustainable agriculture.”
      </blockquote>
    </div>

    <div class="grid-3" style="margin-bottom: 2rem;">
      <div>
        <h3 style="color: var(--primary-dark); font-size: 1.25rem; margin-bottom: 0.75rem;"><i class="fas fa-bullseye"></i> Our Mission</h3>
        <p style="color: var(--text-muted);">
          To democratize access to modern agricultural machinery for small and medium-scale farmers, eliminating financial barriers and manual equipment searching.
        </p>
      </div>

      <div>
        <h3 style="color: var(--primary-dark); font-size: 1.25rem; margin-bottom: 0.75rem;"><i class="fas fa-eye"></i> Our Vision</h3>
        <p style="color: var(--text-muted);">
          To build a sustainable digital farming ecosystem where every farmer can access state-of-the-art tractors, harvesters, and tools on-demand.
        </p>
      </div>

      <div>
        <h3 style="color: var(--primary-dark); font-size: 1.25rem; margin-bottom: 0.75rem;"><i class="fas fa-chart-line"></i> Our Impact</h3>
        <p style="color: var(--text-muted);">
          Lowering operational costs by up to 60%, speeding up crop sowing and harvesting cycles, and increasing overall agricultural profitability.
        </p>
      </div>
    </div>

    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

    <h2 style="color: var(--primary-dark); margin-bottom: 1.25rem;"><i class="fas fa-cogs"></i> Key Platform Features</h2>
    <ul style="list-style: none; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
      <li style="display: flex; align-items: flex-start; gap: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem; margin-top: 0.2rem;"></i> <div><strong>Transparent Daily Pricing:</strong> Fixed rates with no hidden charges or commission fees.</div></li>
      <li style="display: flex; align-items: flex-start; gap: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem; margin-top: 0.2rem;"></i> <div><strong>Real-Time Availability:</strong> Live tracking of equipment availability to prevent double bookings.</div></li>
      <li style="display: flex; align-items: flex-start; gap: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem; margin-top: 0.2rem;"></i> <div><strong>Farmer Portal:</strong> Simple dashboard to manage equipment rentals, dates, and total expenses.</div></li>
      <li style="display: flex; align-items: flex-start; gap: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem; margin-top: 0.2rem;"></i> <div><strong>Admin Analytics:</strong> Comprehensive reports for inventory status, farmer user base, and revenue tracking.</div></li>
    </ul>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
