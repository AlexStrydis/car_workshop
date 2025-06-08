<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Τα Αυτοκίνητά μου (Σελίδα <?= htmlspecialchars($page) ?> από <?= htmlspecialchars($totalPages) ?>)</title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <h1>Τα Αυτοκίνητά μου</h1>

  <!-- Εμφάνιση μηνυμάτων επιτυχίας/σφάλματος -->
  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green"><?= htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <p>
    <a href="create_car.php">Προσθήκη νέου αυτοκινήτου</a> |
    <a href="dashboard.php">Επιστροφή στον Πίνακα Ελέγχου</a>
  </p>

  <!-- -------------------------- -->
  <!-- Φόρμα φίλτρων (Search/Filter) -->
  <!-- -------------------------- -->
  <form method="get" action="cars.php">
    <label>Σειριακός Αριθμός:
      <input
        type="text"
        name="serial"
        value="<?= htmlspecialchars($_GET['serial'] ?? '') ?>">
    </label>
    &nbsp;
    <label>Μοντέλο:
      <input
        type="text"
        name="model"
        value="<?= htmlspecialchars($_GET['model'] ?? '') ?>">
    </label>
    &nbsp;
    <label>Μάρκα:
      <input
        type="text"
        name="brand"
        value="<?= htmlspecialchars($_GET['brand'] ?? '') ?>">
    </label>
    &nbsp;
    <button type="submit">Αναζήτηση</button>
  </form>

  <!-- -------------------------- -->
  <!-- Πίνακας με τα Cars -->
  <!-- -------------------------- -->
  <div class="table-responsive">
  <table>
    <tr>
      <th>Σειριακός Αριθμός</th>
      <th>Μοντέλο</th>
      <th>Μάρκα</th>
      <th>Τύπος</th>
      <th>Κίνηση</th>
      <th>Πόρτες</th>
      <th>Τροχοί</th>
      <th>Ημερομηνία Παραγωγής</th>
      <th>Έτος Απόκτησης</th>
      <th>Ιδιοκτήτης</th>
      <th>Ενέργειες</th>
    </tr>
    <?php if (empty($cars)): ?>
      <tr>
        <td colspan="11" style="text-align:center; color:#777;">
          Δεν βρέθηκαν αυτοκίνητα.
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($cars as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['serial_number']) ?></td>
          <td><?= htmlspecialchars($c['model']) ?></td>
          <td><?= htmlspecialchars($c['brand']) ?></td>
          <td><?= htmlspecialchars($c['type']) ?></td>
          <td><?= htmlspecialchars($c['drive_type']) ?></td>
          <td><?= htmlspecialchars($c['door_count']) ?></td>
          <td><?= htmlspecialchars($c['wheel_count']) ?></td>
          <td><?= htmlspecialchars($c['production_date']) ?></td>
          <td><?= htmlspecialchars($c['acquisition_year']) ?></td>
          <td><?= htmlspecialchars($c['owner_name']) ?></td>
          <td>
            <a href="edit_car.php?serial=<?= urlencode($c['serial_number']) ?>">Επεξεργασία</a>
            &nbsp;
            <form method="post" action="delete_car.php" class="inline" onsubmit="return confirm('Delete this car?');">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="serial" value="<?= htmlspecialchars($c['serial_number']) ?>">
              <button type="submit">Διαγραφή</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </table>
  </div>

  <!-- -------------------------- -->
  <!-- Pagination Links -->
  <!-- -------------------------- -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <span>Σελίδα:</span>
      <?php
        // Κρατάμε όλα τα GET params για να συμπεριλάβουμε filters + page
        $queryParams = $_GET;
      ?>
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php
          $queryParams['page'] = $p;
          $href = 'cars.php?' . http_build_query($queryParams);
        ?>
        <a
          href="<?= htmlspecialchars($href) ?>"
          class="<?= ($p === $page) ? 'current' : '' ?>">
          <?= $p ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
