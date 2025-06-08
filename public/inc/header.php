<?php
// public/inc/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Reusable Header -->
<header class="site-header">
  <div class="container header-container">
    <div class="logo" style="display: flex; align-items: center; justify-content: center; height: 100px; margin-top: 10px;">
      <img src="images/logo.png" alt="Car Workshop Logo" style="max-height: 80px; width: auto;">
    </div>
    <nav class="site-nav" style="margin: 0 auto; display: flex; align-items: center; justify-content: flex-end;">
      <a href="index.php">Αρχική</a>
      <a href="about.php">Σχετικά</a>
      <a href="services.php">Υπηρεσίες</a>
      <a href="contact.php">Επικοινωνία</a>
      <?php if (empty($_SESSION['user_id'])): ?>
      <a href="register.php" class="btn-register" style="font-size: 0.7rem; padding: 4px 8px; margin-left: 100px; border-radius: 6px;">Εγγραφή</a>
      <a href="login.php" class="btn-login" style="font-size: 0.7rem; padding: 4px 8px; margin-left: 10px; border-radius: 6px;">Login</a>
      <?php endif; ?>
    </nav>
    <div class="workshop-title" style="position: absolute; right: 20px; top: 15px;">
      <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="logout.php" class="btn-login" style="font-size: 0.7rem; padding: 4px 8px; margin-right: 10px; border-radius: 6px;">Αποσύνδεση</a>
      <?php endif; ?>
      <h1 style="font-size: 1.5rem; color: #f1c40f; display: inline-block;">Car Workshop</h1>
    </div>
  </div>
</header>
