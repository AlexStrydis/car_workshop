<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Appointments</title>
</head>
<body>
  <h1>Appointments</h1>

  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green"><?= htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <p>
    <a href="create_appointment.php">New Appointment</a> |
    <a href="dashboard.php">Dashboard</a>
  </p>

  <!-- ------------------------ -->
  <!-- Link για Export σε CSV -->
  <!-- ------------------------ -->
  <?php
    // Φτιάχνουμε το query string με τα τρέχοντα GET params
    $qs = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : '';
    // Αν υπάρχουν φίλτρα, το περνάμε στο export_appointments.php
    $exportUrl = 'export_appointments.php' . ($qs ? '?' . htmlspecialchars($qs) : '');
  ?>
  <p>
    <a href="<?= $exportUrl ?>">Export to CSV</a>
  </p>

  <!-- ------------------------ -->
  <!-- Φόρμα φίλτρων -->
  <!-- (Μπορείτε να την αφαιρέσετε αν δεν την χρειάζεστε,
       αλλά θεωρώ ότι βοηθάει τον χρήστη να φιλτράρει πριν εξάγει) -->
  <form method="get" action="appointments.php">
    <label>From:
      <input
        type="date"
        name="date_from"
        value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
    </label>
    <label>To:
      <input
        type="date"
        name="date_to"
        value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
    </label>
    <label>Status:
      <select name="status">
        <option value=""></option>
        <?php foreach (['CREATED','IN_PROGRESS','COMPLETED','CANCELLED'] as $s): ?>
          <option
            value="<?= $s ?>"
            <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>>
            <?= $s ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Customer Last Name:
      <input
        type="text"
        name="customer_last_name"
        value="<?= htmlspecialchars($_GET['customer_last_name'] ?? '') ?>">
    </label>
    <label>Car Serial:
      <input
        type="text"
        name="car_serial"
        value="<?= htmlspecialchars($_GET['car_serial'] ?? '') ?>">
    </label>
    <button type="submit">Filter</button>
  </form>
  <br>

  <table border="1" cellpadding="5">
    <tr>
      <th>ID</th>
      <th>Date</th>
      <th>Time</th>
      <th>Reason</th>
      <th>Status</th>
      <th>Car</th>
      <th>Customer</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($appts as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['id']) ?></td>
        <td><?= htmlspecialchars($a['appointment_date']) ?></td>
        <td><?= htmlspecialchars($a['appointment_time']) ?></td>
        <td><?= htmlspecialchars($a['reason']) ?></td>
        <td><?= htmlspecialchars($a['status']) ?></td>
        <td><?= htmlspecialchars($a['car_serial']) ?></td>
        <td><?= htmlspecialchars($a['customer_last_name']) ?></td>
        <td>
          <?php if ($a['status'] === 'CREATED'): ?>
            <a href="edit_appointment.php?id=<?= $a['id'] ?>">Reschedule</a>
          <?php endif; ?>

          <?php if (in_array($_SESSION['role'], ['secretary','mechanic'], true)): ?>
            <form method="post" action="change_status.php" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <select name="status">
                <?php foreach (['CREATED','IN_PROGRESS','COMPLETED','CANCELLED'] as $s): ?>
                  <option
                    value="<?= $s ?>"
                    <?= ($a['status'] === $s) ? 'selected' : '' ?>>
                    <?= $s ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit">Set</button>
            </form>
          <?php endif; ?>

          <?php if ($a['status'] !== 'CANCELLED'): ?>
            <form method="post" action="cancel_appointment.php" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <button type="submit">Cancel</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- ----------------------------- -->
  <!-- Σελίδωση (Pagination) αν χρειάζεται -->
  <!-- ----------------------------- -->
  <?php if ($totalPages > 1): ?>
    <p>Σελίδα:
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <?php
          $linkParams = $_GET;
          $linkParams['page'] = $p;
          $href = 'appointments.php?' . http_build_query($linkParams);
        ?>
        <a href="<?= htmlspecialchars($href) ?>"
           <?= ($p === ($page ?? 1)) ? 'style="font-weight:bold"' : '' ?>>
          <?= $p ?>
        </a>
      <?php endfor; ?>
    </p>
  <?php endif; ?>
</body>
</html>
