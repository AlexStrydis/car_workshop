<?php
require_once '../config/app.php';
$token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Register</title>
</head>
<body>
  <!-- Add Car Workshop logo at the top-left corner -->
  <header style="position: absolute; top: 0; left: 0; width: 100%; display: flex; align-items: center; padding: 10px; background: none;">
    <div style="margin-left: 10px;">
      <img src="images/logo.png" alt="Car Workshop Logo" style="height: 80px;">
    </div>
  </header>

  <!-- Adding 'Car Workshop' title to the register view -->
  <div class="workshop-title" style="position: absolute; right: 30px; top: 35px;">
    <h1 style="font-size: 1.5rem; color: #f1c40f;">Car Workshop</h1>
  </div>

  <section class="hero-background">
    <div class="register-container" style="background-color: rgba(0, 0, 0, 0.8); padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); width: 100%; max-width: 400px; margin-bottom: 20px;">
      <h2 class="register-title" style="color: #f1c40f; text-align: center; margin-bottom: 20px;">Εγγραφή</h2>
      <form method="post" action="register.php" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">

        <!-- Adjust error message logic to persist for respective fields -->
        <?php
          $errorMessages = $_SESSION['errors'] ?? [];
        ?>

        <!-- Username: required, minlength=4, pattern αλφαριθμητικά -->
        <label style="color: #ffffff;">
          Username:
          <input
            name="username"
            type="text"
            minlength="4"
            pattern="[A-Za-z0-9]+"
            title="Τουλάχιστον 4 χαρακτήρες. Μόνο λατινικοί χαρακτήρες και αριθμοί."
            required
            value="<?= htmlspecialchars($_SESSION['old']['username'] ?? '') ?>"
            style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          <?php
            if (!empty($errorMessages['username'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['username']) . '</span>';
            }
          ?>
        </label>

        <!-- Password: required, minlength=8, pattern γράμμα + αριθμός -->
        <label style="color: #ffffff;">
          Password:
          <input
            type="password"
            name="password"
            minlength="8"
            pattern="(?=.*[A-Za-z])(?=.*\d).{8,}"
            title="Τουλάχιστον 8 χαρακτήρες, με ένα γράμμα και έναν αριθμό."
            required
            value="<?= htmlspecialchars($_SESSION['old']['password'] ?? '') ?>"
            style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          <?php
            if (!empty($errorMessages['password'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['password']) . '</span>';
            }
          ?>
        </label>

        <!-- First Name: required -->
        <label style="color: #ffffff;">
          First name:
          <input
            name="first_name"
            type="text"
            minlength="1"
            required
            value="<?= htmlspecialchars($_SESSION['old']['first_name'] ?? '') ?>"
            style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          <?php
            if (!empty($errorMessages['first_name'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['first_name']) . '</span>';
            }
          ?>
        </label>

        <!-- Last Name: required -->
        <label style="color: #ffffff;">
          Last name:
          <input
            name="last_name"
            type="text"
            minlength="1"
            required
            value="<?= htmlspecialchars($_SESSION['old']['last_name'] ?? '') ?>"
            style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          <?php
            if (!empty($errorMessages['last_name'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['last_name']) . '</span>';
            }
          ?>
        </label>

        <!-- Identity Number: required, pattern 2 γράμματα + 6 ψηφία -->
        <label style="color: #ffffff;">
          Identity no.:
          <input
            name="identity_number"
            type="text"
            pattern="[A-Za-z]{2}[0-9]{6}"
            title="2 γράμματα (A–Z) ακολουθούμενα από 6 ψηφία."
            required
            value="<?= htmlspecialchars($_SESSION['old']['identity_number'] ?? '') ?>"
            style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          <?php
            if (!empty($errorMessages['identity_number'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['identity_number']) . '</span>';
            }
          ?>
        </label>

        <!-- Role: required -->
        <label style="color: #ffffff;">
          Role:
          <select name="role" id="role" required style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
            <option value="">-- Επιλέξτε --</option>
            <option value="customer"
              <?= (($_SESSION['old']['role'] ?? '') === 'customer') ? 'selected' : '' ?>>
              Customer
            </option>
            <option value="mechanic"
              <?= (($_SESSION['old']['role'] ?? '') === 'mechanic') ? 'selected' : '' ?>>
              Mechanic
            </option>
          </select>
          <?php
            if (!empty($errorMessages['role'])) {
                echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['role']) . '</span>';
            }
          ?>
        </label>

        <!-- Extra πεδία ανάλογα με το Role -->
        <div id="extra" style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
          <?php if (($_SESSION['old']['role'] ?? '') === 'customer'): ?>
            <!-- Tax ID: required, pattern 9 ψηφία -->
            <label style="color: #ffffff; margin-bottom: 15px;">
              Tax ID:
              <input
                name="tax_id"
                type="text"
                pattern="\d{9}"
                title="Ακριβώς 9 ψηφία."
                required
                value="<?= htmlspecialchars($_SESSION['old']['tax_id'] ?? '') ?>"
                style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
              <?php
                if (!empty($errorMessages['tax_id'])) {
                    echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['tax_id']) . '</span>';
                }
              ?>
            </label>
            <!-- Address: required -->
            <label style="color: #ffffff; margin-bottom: 15px;">
              Address:
              <input
                name="address"
                type="text"
                minlength="1"
                required
                value="<?= htmlspecialchars($_SESSION['old']['address'] ?? '') ?>"
                style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
              <?php
                if (!empty($errorMessages['address'])) {
                    echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['address']) . '</span>';
                }
              ?>
            </label>
          <?php elseif (($_SESSION['old']['role'] ?? '') === 'mechanic'): ?>
            <!-- Specialty: required -->
            <label style="color: #ffffff; margin-bottom: 15px;">
              Specialty:
              <input
                name="specialty"
                type="text"
                minlength="1"
                required
                value="<?= htmlspecialchars($_SESSION['old']['specialty'] ?? '') ?>"
                style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
              <?php
                if (!empty($errorMessages['specialty'])) {
                    echo '<span style="color:red; font-size:12px;">' . htmlspecialchars($errorMessages['specialty']) . '</span>';
                }
              ?>
            </label>
          <?php endif; ?>
        </div>

        <button type="submit" style="padding: 10px; border: none; border-radius: 5px; background-color: #f1c40f; color: #1f1f1f; font-weight: bold; cursor: pointer; transition: background-color 0.3s;">Εγγραφή</button>
      </form>

      <!-- Adjusting login prompt with white text and smaller button -->
      <div class="navigation-buttons" style="display: flex; flex-direction: column; align-items: center; margin-top: 20px;">
        <a href="../public/index.php" class="btn-secondary" style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff; text-decoration: none; font-weight: bold; transition: background-color 0.3s;">Πίσω στην Αρχική</a>
        <p style="color: #ffffff; margin-top: 10px;">Έχεις ήδη λογαριασμό; Κάνε σύνδεση τώρα</p>
        <a href="../public/login.php" class="btn-primary" style="padding: 10px; border: none; border-radius: 5px; background-color: #f1c40f; color: #1f1f1f; text-decoration: none; font-weight: bold; transition: background-color 0.3s;">Σύνδεση</a>
      </div>
    </div>
  </section>

  <script>
    const extra = document.getElementById('extra');
    const roleSelect = document.getElementById('role');

    function updateExtraFields() {
      extra.innerHTML = '';
      if (roleSelect.value === 'customer') {
        extra.innerHTML = `
          <label style="color: #ffffff; margin-bottom: 15px;">
            Tax ID:
            <input
              name="tax_id"
              type="text"
              pattern="\\d{9}"
              title="Ακριβώς 9 ψηφία."
              required
              value="${<?= json_encode($_SESSION['old']['tax_id'] ?? '') ?>}"
              style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          </label>
          <label style="color: #ffffff; margin-bottom: 15px;">
            Address:
            <input
              name="address"
              type="text"
              minlength="1"
              required
              value="${<?= json_encode($_SESSION['old']['address'] ?? '') ?>}"
              style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          </label>
        `;
      } else if (roleSelect.value === 'mechanic') {
        extra.innerHTML = `
          <label style="color: #ffffff; margin-bottom: 15px;">
            Specialty:
            <input
              name="specialty"
              type="text"
              minlength="1"
              required
              value="${<?= json_encode($_SESSION['old']['specialty'] ?? '') ?>}"
              style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
          </label>
        `;
      }
    }

    roleSelect.addEventListener('change', updateExtraFields);
    updateExtraFields();
  </script>

  <?php
    // Μετά την αποτύπωση των πεδίων στο view, καθαρίζουμε τα δεδομένα
    unset($_SESSION['errors']);
    unset($_SESSION['old']);
  ?>
  
</body>
</html>
