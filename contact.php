<?php
$page_title = "Contact Us";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && $email && $message) {
        $message_sent = true;
        setFlash('success', 'Thank you! Your message has been sent successfully. Our support team will get back to you shortly.');
    } else {
        setFlash('error', 'Please fill in all required fields.');
    }
}
?>

<div class="container">
  <div class="page-header text-center" style="text-align: center;">
    <h1 class="page-title" style="justify-content: center;"><i class="fas fa-headset"></i> Contact Us</h1>
    <p class="page-subtitle">Have questions or need assistance with renting farming equipment? We are here to help!</p>
  </div>

  <div class="grid-3" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Contact Info Cards -->
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
      <div class="feature-card" style="text-align: left; padding: 1.5rem;">
        <div class="feature-icon" style="margin: 0 0 1rem 0; width: 48px; height: 48px; font-size: 1.25rem;"><i class="fas fa-map-marker-alt"></i></div>
        <h3 class="feature-title" style="font-size: 1.1rem;">Main Hub Address</h3>
        <p class="feature-desc">Kisan Vikas Bhavan, GT Road, Near Agriculture Mandi, Karnal, Haryana - 132001</p>
      </div>

      <div class="feature-card" style="text-align: left; padding: 1.5rem;">
        <div class="feature-icon" style="margin: 0 0 1rem 0; width: 48px; height: 48px; font-size: 1.25rem;"><i class="fas fa-phone-alt"></i></div>
        <h3 class="feature-title" style="font-size: 1.1rem;">Toll-Free Helpline</h3>
        <p class="feature-desc">+91 1800-123-4567<br><small style="color: var(--text-muted);">Mon - Sat: 7:00 AM - 8:00 PM</small></p>
      </div>

      <div class="feature-card" style="text-align: left; padding: 1.5rem;">
        <div class="feature-icon" style="margin: 0 0 1rem 0; width: 48px; height: 48px; font-size: 1.25rem;"><i class="fas fa-envelope"></i></div>
        <h3 class="feature-title" style="font-size: 1.1rem;">Email Support</h3>
        <p class="feature-desc">support@farmtoolsrental.com<br>info@farmtoolsrental.com</p>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="table-card" style="padding: 2rem;">
      <h2 style="color: var(--primary-dark); font-size: 1.35rem; margin-bottom: 1.5rem;"><i class="fas fa-paper-plane"></i> Send Us a Message</h2>

      <form action="contact.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="name">Your Name *</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Ramesh Kumar" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Email Address *</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. farmer@example.com" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="subject">Subject</label>
          <input type="text" id="subject" name="subject" class="form-control" placeholder="e.g. Query regarding tractor rental dates">
        </div>

        <div class="form-group">
          <label class="form-label" for="message">Message *</label>
          <textarea id="message" name="message" class="form-control" rows="5" placeholder="Write your message or inquiry details here..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;"><i class="fas fa-paper-plane"></i> Send Inquiry</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
