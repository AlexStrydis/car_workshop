<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Car Workshop</title>
  <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
  <!-- Add Car Workshop logo at the top-left corner -->
  <header style="position: absolute; top: 0; left: 0; width: 100%; display: flex; align-items: center; padding: 10px; background: none;">
    <div style="margin-left: 10px;">
      <img src="../public/images/logo.png" alt="Car Workshop Logo" style="height: 80px;">
    </div>
  </header>
  <!-- Adding 'Car Workshop' title to the login view -->
  <div class="workshop-title" style="position: absolute; right: 30px; top: 35px;">
    <h1 style="font-size: 1.5rem; color: #f1c40f;">Car Workshop</h1>
  </div>
  <section class="hero-background">
    <div class="login-container" style="background-color: rgba(0, 0, 0, 0.8); padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5); width: 100%; max-width: 400px;">
      <h2 class="login-title" style="color: #f1c40f; text-align: center; margin-bottom: 20px;">Σύνδεση</h2>
      <form method="POST" action="../public/login.php" class="login-form" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group" style="display: flex; flex-direction: column;">
          <label for="username" style="color: #ffffff; margin-bottom: 5px;">Όνομα Χρήστη:</label>
          <input type="text" id="username" name="username" required style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
        </div>
        <div class="form-group" style="display: flex; flex-direction: column;">
          <label for="password" style="color: #ffffff; margin-bottom: 5px;">Κωδικός:</label>
          <input type="password" id="password" name="password" required style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff;">
        </div>
        <button type="submit" class="btn-primary" style="padding: 10px; border: none; border-radius: 5px; background-color: #f1c40f; color: #1f1f1f; font-weight: bold; cursor: pointer; transition: background-color 0.3s;">Σύνδεση</button>
      </form>
      <?php if (!empty($_SESSION['error'])): ?>
        <p class="error-message" style="color: #e74c3c; text-align: center; margin-top: 15px;"><?= htmlspecialchars($_SESSION['error']) ?></p>
      <?php endif; ?>
      <!-- Add buttons for navigation -->
      <div class="navigation-buttons" style="display: flex; flex-direction: column; align-items: center; margin-top: 20px;">
        <a href="../public/index.php" class="btn-secondary" style="padding: 10px; border: none; border-radius: 5px; background-color: #333; color: #fff; text-decoration: none; font-weight: bold; transition: background-color 0.3s;">Πίσω στην Αρχική</a>
        <p style="color: #ffffff; margin-top: 10px;">Δεν έχεις λογαριασμό; Κάνε εγγραφή τώρα</p>
        <a href="../public/register.php" class="btn-primary" style="padding: 10px; border: none; border-radius: 5px; background-color: #f1c40f; color: #1f1f1f; text-decoration: none; font-weight: bold; transition: background-color 0.3s;">Εγγραφή</a>
      </div>
    </div>
  </section>
</body>
</html>
