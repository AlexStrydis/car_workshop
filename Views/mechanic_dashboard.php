<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Mechanic Dashboard</title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="max-width: 1000px; background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <?php
    // Υποθέτουμε ότι ο controller έχει περάσει:
    //   $today      → string, π.χ. '2025-06-02'
    //   $appts      → array με ραντεβού (με πεδία appointment_time, brand, model, cust_last, cust_first, status)
    //   $tasksToday → array με εργασίες για σήμερα, αν υπάρχουν
  ?>

  <p>
    <!-- Προσθήκη συνδέσμου επιστροφής στο Dashboard -->
    <a href="dashboard.php">← Επιστροφή στο Dashboard</a>
  </p>

  <h1>Dashboard Μηχανικού για <?= htmlspecialchars($today) ?></h1>

  <h2>Ραντεβού Σήμερα</h2>
  <?php if (!empty($appts)): ?>
    <table border="1" cellpadding="5">
      <tr>
        <th>Ώρα</th>
        <th>Τύπος Αυτοκινήτου</th>
        <th>Πελάτης</th>
        <th>Κατάσταση</th>
      </tr>
      <?php foreach ($appts as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['appointment_time']) ?></td>
          <td>
            <?= htmlspecialchars($a['brand'] . ' ' . $a['model']) ?>
          </td>
          <td>
            <?= htmlspecialchars($a['cust_last'] . ' ' . $a['cust_first']) ?>
          </td>
          <td>
            <?= htmlspecialchars($a['status']) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p><em>Δεν υπάρχουν ραντεβού για σήμερα.</em></p>
  <?php endif; ?>

  <h2>Εργασίες Σήμερα</h2>
  <?php if (!empty($tasksToday)): ?>
    <table border="1" cellpadding="5">
      <tr>
        <th>ID Εργασίας</th>
        <th>Περιγραφή</th>
        <th>Υλικά</th>
        <th>Ώρα Ολοκλήρωσης</th>
        <th>Κόστος</th>
      </tr>
      <?php foreach ($tasksToday as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t['id']) ?></td>
          <td><?= htmlspecialchars($t['description']) ?></td>
          <td><?= htmlspecialchars($t['materials']) ?></td>
          <td><?= htmlspecialchars($t['completion_time']) ?></td>
          <td><?= htmlspecialchars($t['cost']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p><em>Δεν υπάρχουν εργασίες για σήμερα.</em></p>
  <?php endif; ?>

</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
