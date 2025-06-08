<?php
// public/about.php
require_once '../config/app.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8" />
  <title>Car Workshop | Σχετικά</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <!-- Header -->
  <?php include __DIR__ . '/inc/header.php'; ?>

  <!-- Main Content -->
  <section class="hero-background">
    <div class="container about-section" style="background-color: rgba(0, 0, 0, 0.8); padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
      <h2 style="color: #f1c40f; text-align: center; margin-bottom: 20px;">Σχετικά με το Car Workshop</h2>
      <p class="lead" style="color: #ffffff; margin-bottom: 20px;">
        Το Car Workshop ιδρύθηκε το 2015 με όραμα την ποιοτική και γρήγορη εξυπηρέτηση
        των πελατών μας. Από την πρώτη μέρα, στόχος μας είναι να προσφέρουμε:
      </p>
      <ul style="color: #ffffff; margin-bottom: 20px;">
        <li>100% αυθεντικά ανταλλακτικά</li>
        <li>Άμεση διάγνωση βλαβών</li>
        <li>Ισχυρές εγγυήσεις για κάθε εργασία</li>
        <li>Συνεχή εκπαίδευση προσωπικού σε νέες τεχνολογίες</li>
      </ul>
      <p style="color: #ffffff;">
        Ελάτε να γνωριστούμε από κοντά, να δείτε τον χώρο μας, και να βεβαιωθείτε ότι
        το αυτοκίνητό σας είναι σε καλά χέρια.
      </p>
    </div>
  </section>


  <!-- Footer -->
   <?php include __DIR__ . '/inc/footer.php'; ?>
   
</body>
</html>
