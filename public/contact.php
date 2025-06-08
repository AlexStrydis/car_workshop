<?php
// public/contact.php
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <title>Car Workshop | Επικοινωνία</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Κοινό CSS -->
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <!-- Εισαγωγή Header -->
  <?php include __DIR__ . '/inc/header.php'; ?>

  <!-- Contact Section -->
  <section class="hero-background">
    <div class="container contact-section">
      <h2>Επικοινωνία</h2>

      <div class="contact-grid">
        <!-- Αριστερή στήλη: Στοιχεία Εταιρείας -->
        <div class="contact-info">
          <h4>Διεύθυνση</h4>
          <p>Οδός Παράδειγμα 123, Καρλόβασι</p>
          <h4>Τηλέφωνο</h4>
          <p>210 1234567</p>
          <h4>Email</h4>
          <p><a href="mailto:info@carworkshop.gr" style="color: #f1c40f;">info@carworkshop.gr</a></p>
          <h4>Ωράριο</h4>
          <p>Δευτέρα – Παρασκευή: 08:00 – 18:00<br />Σάββατο: 09:00 – 14:00</p>
          <h4>Βρες μας στο χάρτη</h4>

          <!-- Χάρτης Google -->
          <div class="map-container">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2651.15325488895!2d26.705267329724315!3d37.795422231999765!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14bc6a0187727ef5%3A0xb1901cb39d97cd1c!2sUniversity%20of%20the%20Aegean%2C%20Dept.%20of%20Information%20and%20Communication%20Systems%20Engineering!5e0!3m2!1sel!2sgr!4v1749335180205!5m2!1sel!2sgr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
              width="100%"
              height="300"
              style="border:0; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
        </div>

        <!-- Δεξιά στήλη: Φόρμα Επικοινωνίας -->
        <div class="contact-form-wrapper">
          <form class="contact-form" method="post" action="send_message.php">
            <!-- CSRF token (εφόσον έχεις αντίστοιχο helper) -->
            <!-- <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>"> -->

            <div class="form-group">
              <label for="name">Όνομα:</label>
              <input type="text" id="name" name="name" required placeholder="Το όνομά σας" />
            </div>

            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" required placeholder="your@email.com" />
            </div>

            <div class="form-group">
              <label for="subject">Θέμα:</label>
              <input type="text" id="subject" name="subject" required placeholder="Θέμα μηνύματος" />
            </div>

            <div class="form-group">
              <label for="message">Μήνυμα:</label>
              <textarea id="message" name="message" rows="5" required placeholder="Περιγράψτε το ερώτημα ή το σχόλιό σας"></textarea>
            </div>

            <button type="submit" class="btn-primary">Αποστολή Μηνύματος</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Εισαγωγή Footer -->
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
