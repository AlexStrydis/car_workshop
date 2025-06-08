<?php
// public/index.php
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <title>Car Workshop | Αρχική</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap"
    rel="stylesheet"
  />
  <style>
    .btn-primary {
      font-size: 0.7rem;
      padding: 4px 8px;
      border-radius: 6px;
      background-color: #f1c40f;
      color: #fff;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <!--==============================
    =            HEADER            =
  ==============================-->
  <?php include __DIR__ . '/inc/header.php'; ?>

  <!--==============================
    =         HERO SECTION         =
  ==============================-->
  <section class="hero-section hero-background">
    <div class="hero-content container">
      <h2 class="hero-title">Βρες τον καλύτερο μηχανικό για το αυτοκίνητό σου</h2>
      <p class="hero-subtitle">
        Εξειδικευμένες υπηρεσίες service & καθαρισμού, αξιόπιστα ανταλλακτικά
        και προσωπική εξυπηρέτηση.
      </p>
      <div class="hero-cta">
        <a href="register.php" class="btn-register">Εγγραφή Τώρα</a>
        <a href="login.php" class="btn-login">Login</a>
      </div>
    </div>
  </section>

  <!--==============================
    =        ABOUT SECTION         =
  ==============================-->
  <section class="about-section hero-background">
    <div class="container">
      <h2>Για εμάς</h2>
      <p class="lead">
        Στο Car Workshop, πάνω από 10 χρόνια εμπειρίας στην επισκευή και την
        συντήρηση αυτοκινήτων. Είμαστε ΑΣΕ πιστοποιημένοι μηχανικοί που
        νοιαζόμαστε πραγματικά για την ασφάλειά σας στο δρόμο. Κάθε όχημα
        αντιμετωπίζεται σαν να ήταν δικό μας.
      </p>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">🛠️</div>
          <h3>Έμπειροι Μηχανικοί</h3>
          <p>
            Όλοι οι μηχανικοί μας είναι ASE-Certified και εκπαιδευμένοι στις
            τελευταίες τεχνολογίες.
          </p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">⏱️</div>
          <h3>Γρήγορη Εξυπηρέτηση</h3>
          <p>
            Στόχος μας η άμεση επίλυση, χωρίς να θυσιάζουμε την ποιότητα.
          </p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">💯</div>
          <h3>Εγγύηση Εργασίας</h3>
          <p>
            Όλες οι εργασίες μας συνοδεύονται από 1 έτος / 10.000χλμ εγγύηση.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!--==============================
    =       SERVICES SECTION       =
  ==============================-->
  <section class="services-section hero-background">
    <div class="container">
      <h2>Οι Υπηρεσίες μας</h2>
      <p class="lead">
        Καλύπτουμε οποιαδήποτε ανάγκη συντήρησης ή επισκευής αυτοκινήτου, από
        αλλαγή λαδιών έως και πλήρη ανακατασκευή κινητήρα.
      </p>
      <div class="services-grid">
        <div class="service-card">
          <img src="images/icon-oil.png" alt="Oil Change" />
          <h4>Αλλαγή Λαδιών</h4>
        </div>
        <div class="service-card">
          <img src="images/icon-brake.png" alt="Brake Repair" />
          <h4>Επισκευή Φρένων</h4>
        </div>
        <div class="service-card">
          <img src="images/icon-engine.png" alt="Engine Repair" />
          <h4>Επισκευή Κινητήρα</h4>
        </div>
        <div class="service-card">
          <img src="images/icon-ac.png" alt="A/C Service" />
          <h4>Σέρβις Κλιματισμού</h4>
        </div>
        <div class="service-card">
          <img src="images/icon-battery.png" alt="Battery Service" />
          <h4>Μπαταρίες & Ηλεκτρικά</h4>
        </div>
        <div class="service-card">
          <img src="images/icon-inspection.png" alt="Diagnostics" />
          <h4>Διαγνωστικός Έλεγχος</h4>
        </div>
      </div>
      <div class="text-center mt-10">
        <a href="services.php" class="btn-primary">Δες Όλες τις Υπηρεσίες</a>
      </div>
    </div>
  </section>

  <!--==============================
    =       CONTACT / FOOTER        =
  ==============================-->
  <?php include __DIR__ . '/inc/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loginButton = document.querySelector('.btn-login');
      const registerButton = document.querySelector('.btn-register');

      if (loginButton) loginButton.style.display = 'none';
      if (registerButton) registerButton.style.display = 'none';
    });
  </script>
</body>
</html>
