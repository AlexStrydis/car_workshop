<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Οι Εργασίες Μου</title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <h1>Οι Εργασίες Μου</h1>

  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green"><?= htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Φίλτρο Ημερομηνίας -->
  <form method="get" action="tasks.php">
    <label>Φιλτράρισμα κατά Ημερομηνία:
      <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    </label>
    <button type="submit">Φίλτρο</button>
    &nbsp;
    <a href="tasks.php">Reset</a>
  </form>

  <?php if (empty($tasks)): ?>
    <p>Δεν υπάρχουν εργασίες για αυτόν τον μηχανικό<?= isset($_GET['date']) ? ' (ή για αυτή την ημερομηνία)' : '' ?>.</p>
  <?php else: ?>
    <table>
      <tr>
        <th>ID</th>
        <th>Appointment ID</th>
        <th>Περιγραφή</th>
        <th>Υλικά</th>
        <th>Ώρα Ολοκλήρωσης</th>
        <th>Κόστος</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($tasks as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t['id']) ?></td>
          <td><?= htmlspecialchars($t['appointment_id']) ?></td>
          <td><?= htmlspecialchars($t['description']) ?></td>
          <td><?= htmlspecialchars($t['materials']) ?></td>
          <td><?= htmlspecialchars($t['completion_time']) ?></td>
          <td><?= htmlspecialchars($t['cost']) ?></td>
          <td>
            <a href="edit_task.php?id=<?= $t['id'] ?>">Edit</a>
            &nbsp;
            <form method="post" action="delete_task.php" class="inline" onsubmit="return confirm('Delete this task?');">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <!-- ---------------------------- -->
  <!-- Φόρμα Προσθήκης Νέας Εργασίας -->
  <!-- ---------------------------- -->
  <h2>Προσθήκη Νέας Εργασίας</h2>
  <form method="post" action="create_task.php">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">

    <label>Appointment:
      <select name="appointment_id" required>
        <option value="">-- Επιλέξτε Ραντεβού --</option>
        <?php foreach ($appts as $a): ?>
          <option value="<?= htmlspecialchars($a['id']) ?>">
            <?= htmlspecialchars($a['id'] . ' | ' . $a['appointment_date'] . ' ' . substr($a['appointment_time'],0,5) . ' | Car: ' . $a['car_serial']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br><br>

    <label>Περιγραφή:
      <textarea name="description" rows="3" cols="50" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </label>
    <br><br>

    <label>Υλικά:
      <input type="text" name="materials" value="<?= htmlspecialchars($_POST['materials'] ?? '') ?>">
    </label>
    <br><br>

    <label>Ώρα Ολοκλήρωσης:
      <input
        type="datetime-local"
        name="completion_time"
        value="<?= htmlspecialchars($_POST['completion_time'] ?? '') ?>"
        required>
      <!-- 
        Σημείωση: το HTML5 'datetime-local' παράγει τιμή σε μορφή "YYYY-MM-DDTHH:MM".
        Στον controller προσαρμόζουμε σε "YYYY-MM-DD HH:MM:00" αν χρειάζεται.
      -->
    </label>
    <br><br>

    <label>Κόστος:
      <input type="number" step="0.01" name="cost" value="<?= htmlspecialchars($_POST['cost'] ?? '') ?>" required>
    </label>
    <br><br>

    <button type="submit">Create Task</button>
  </form>

  <!-- ---------------------------- -->
  <!-- Link για Επιστροφή στο Dashboard -->
  <!-- ---------------------------- -->
  <p class="back-link">
    <a href="dashboard.php">&larr; Επιστροφή στο Dashboard</a>
  </p>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
