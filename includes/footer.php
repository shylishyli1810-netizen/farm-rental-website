</main>

<footer class="footer-site">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="brand-logo" style="margin-bottom: 1rem;">
          <i class="fas fa-tractor" style="color: var(--accent-gold);"></i>
          <span>Farm Tools Rental</span>
        </div>
        <p style="font-size: 0.9rem; line-height: 1.6;">
          Helping farmers easily rent agricultural equipment online, improving productivity, reducing operational costs, and promoting sustainable agriculture.
        </p>
      </div>

      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?php echo $base_path; ?>index.php"><i class="fas fa-angle-right"></i> Home</a></li>
          <li><a href="<?php echo $base_path; ?>about.php"><i class="fas fa-angle-right"></i> About Us</a></li>
          <li><a href="<?php echo $base_path; ?>equipment.php"><i class="fas fa-angle-right"></i> Equipment Catalog</a></li>
          <li><a href="<?php echo $base_path; ?>contact.php"><i class="fas fa-angle-right"></i> Contact Us</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Farmer Services</h4>
        <ul class="footer-links">
          <li><a href="<?php echo $base_path; ?>register.php"><i class="fas fa-angle-right"></i> Register Account</a></li>
          <li><a href="<?php echo $base_path; ?>login.php"><i class="fas fa-angle-right"></i> Farmer Login</a></li>
          <li><a href="<?php echo $base_path; ?>farmer/dashboard.php"><i class="fas fa-angle-right"></i> Farmer Dashboard</a></li>
          <li><a href="<?php echo $base_path; ?>farmer/bookings.php"><i class="fas fa-angle-right"></i> Track Bookings</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact & Support</h4>
        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="fas fa-map-marker-alt" style="color: var(--accent-gold);"></i> Anna Salai, Guindy, Chennai, Tamil Nadu</p>
        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="fas fa-phone-alt" style="color: var(--accent-gold);"></i> +91 81228 44191</p>
        <p style="font-size: 0.9rem;"><i class="fas fa-envelope" style="color: var(--accent-gold);"></i> shylishyli1810@gmail.com</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Farm Tools Rental Platform. All rights reserved. | Project Tagline: <em>"Helping farmers easily rent agricultural equipment, improving productivity and promoting sustainable agriculture."</em></p>
    </div>
  </div>
</footer>

<!-- SHOPPING CART MODAL / DRAWER -->
<div id="cartModal" class="cart-modal" aria-hidden="true">
  <div class="cart-modal-overlay" id="cartOverlay"></div>
  <div class="cart-modal-content">
    <div class="cart-modal-header">
      <h3><i class="fas fa-shopping-cart"></i> Shopping Cart</h3>
      <button type="button" id="closeCartBtn" class="cart-close-btn" aria-label="Close cart">&times;</button>
    </div>
    <div class="cart-modal-body" id="cartItemsContainer">
      <!-- Items dynamically injected by JavaScript -->
    </div>
    <div class="cart-modal-footer">
      <div class="cart-total-row">
        <span>Total Daily Rate:</span>
        <strong id="cartTotalPrice">₹ 0.00</strong>
      </div>
      <div class="cart-actions">
        <button type="button" id="clearCartBtn" class="btn btn-outline-dark btn-sm"><i class="fas fa-trash-alt"></i> Clear Cart</button>
        <button type="button" id="closeCartFooterBtn" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Done</button>
      </div>
    </div>
  </div>
</div>

<!-- CART TOAST NOTIFICATION -->
<div id="cartToast" class="cart-toast" role="alert">
  <i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem;"></i>
  <span id="cartToastMsg">Item added to cart!</span>
</div>

<script src="<?php echo $base_path; ?>js/script.js?v=2"></script>

</body>
</html>
