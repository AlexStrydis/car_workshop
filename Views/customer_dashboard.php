<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Dashboard Πελάτη</title>
  <style>
    .dashboard-header {
      text-align: center;
      margin-top: 20px;
      font-size: 1.5rem;
      color: #333;
    }
  </style>
</head>
<body>
  <?php
  require_once __DIR__ . '/../src/Models/User.php';
  use Models\User;

  $userModel = new User($pdo);
  $user = $userModel->findById((int)$_SESSION['user_id']);
  $username = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Χρήστης';

  ?>
  <header style="text-align: center; margin: 0; font-size: 1.5rem; color: #333;">
    <h1>Πίνακας ελέγχου του χρήστη <?php echo htmlspecialchars($username); ?></h1>
  </header>

  <h1><?= date('d/m/Y') ?></h1>

  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green"><?=htmlspecialchars($_SESSION['success'])?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?=htmlspecialchars($_SESSION['error'])?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- 1) Τα Αυτοκίνητά Μου -->
  <h2>Τα Αυτοκίνητά Μου</h2>
  <p><a href="create_car.php">Νέο Αυτοκίνητο</a></p>
  <?php if (empty($cars)): ?>
    <p>Δεν έχεις καταχωρήσει κάποιο αυτοκίνητο.</p>
  <?php else: ?>
    <table border="1" cellpadding="5">
      <tr>
        <th>Serial</th><th>Model</th><th>Brand</th><th>Type</th><th>Drive</th><th>Doors</th><th>Wheels</th><th>Prod Date</th><th>Year</th><th>Ενέργειες</th>
      </tr>
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
          <td>
            <a href="edit_car.php?serial=<?= urlencode($c['serial_number']) ?>">Edit</a> |
            <form method="post" action="delete_car.php" style="display:inline">
              <input type="hidden" name="_csrf" value="<?=htmlspecialchars($token)?>">
              <input type="hidden" name="serial" value="<?=htmlspecialchars($c['serial_number'])?>">
              <button type="submit" onclick="return confirm('Διαγραφή αυτοκινήτου;')">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- 2) Τα Ραντεβού Μου (επερχόμενα) -->
  <h2>Τα Επερχόμενα Ραντεβού Μου</h2>
  <p><a href="create_appointment.php">Νέο Ραντεβού</a></p>
  <?php if (empty($appts)): ?>
    <p>Δεν έχεις ραντεβού από σήμερα και μετά.</p>
  <?php else: ?>
    <table border="1" cellpadding="5">
      <tr>
        <th>ID</th><th>Ημερομηνία</th><th>Ώρα</th><th>Reason</th><th>Status</th><th>Αυτοκίνητο</th><th>Ενέργειες</th>
      </tr>
      <?php foreach ($appts as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['id']) ?></td>
          <td><?= htmlspecialchars($a['appointment_date']) ?></td>
          <td><?= htmlspecialchars($a['appointment_time']) ?></td>
          <td><?= htmlspecialchars($a['reason']) ?></td>
          <td><?= htmlspecialchars($a['status']) ?></td>
          <td><?= htmlspecialchars($a['car_serial']) ?></td>
          <td>
            <?php if ($a['status'] === 'CREATED'): ?>
              <a href="edit_appointment.php?id=<?= $a['id'] ?>">Edit</a> |
            <?php endif; ?>
            <?php if ($a['status'] !== 'CANCELLED'): ?>
              <form method="post" action="cancel_appointment.php" style="display:inline">
                <input type="hidden" name="_csrf" value="<?=htmlspecialchars($token)?>">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" onclick="return confirm('Ακύρωση ραντεβού;')">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p>
    <a href="dashboard.php">Γενικός Πίνακας Ελέγχου</a> |
    <a href="logout.php">Αποσύνδεση</a>
  </p>
</body>
</html>
