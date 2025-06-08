<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title><?= isset($task) ? 'Edit Task #' . htmlspecialchars($task['id']) : 'New Task' ?></title>
  <style>
    label { display: block; margin-bottom: 10px; }
    textarea { width: 100%; max-width: 500px; }
    input[type="text"], input[type="number"], input[type="datetime-local"], select {
      width: 300px;
    }
  </style>
</head>
<body>
  <h1><?= isset($task) ? 'Επεξεργασία Εργασίας #' . htmlspecialchars($task['id']) : 'Προσθήκη Νέας Εργασίας' ?></h1>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form
    method="post"
    action="<?= isset($task) ? 'edit_task.php' : 'create_task.php' ?>">

    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">

    <?php if (isset($task)): ?>
      <!-- Κρύβουμε το ID για το edit -->
      <input type="hidden" name="id" value="<?= $task['id'] ?>">
    <?php endif; ?>

    <!-- Appointment Dropdown -->
    <label>
      Appointment:
      <select name="appointment_id" required>
        <option value="">-- Επιλέξτε Ραντεβού --</option>
        <?php foreach ($appts as $a): ?>
          <option
            value="<?= htmlspecialchars($a['id']) ?>"
            <?= (isset($task) && $task['appointment_id'] == $a['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['id'] . ' | ' . $a['appointment_date'] . ' ' . substr($a['appointment_time'], 0, 5) . ' | Car: ' . $a['car_serial']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <!-- Περιγραφή -->
    <label>
      Περιγραφή:
      <textarea name="description" rows="3" required><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
    </label>

    <!-- Υλικά -->
    <label>
      Υλικά:
      <input type="text" name="materials" value="<?= htmlspecialchars($task['materials'] ?? '') ?>">
    </label>

    <!-- Ώρα Ολοκλήρωσης -->
    <label>
      Ώρα Ολοκλήρωσης:
      <?php
        // Αν υπάρχει ήδη το υπάρχον completion_time (YYYY-MM-DD HH:MM:SS), μετατρέπουμε σε format που
        // καταλαβαίνει το <input type="datetime-local">: "YYYY-MM-DDTHH:MM"
        $dtValue = '';
        if (isset($task['completion_time']) && $task['completion_time'] !== '') {
            $dt = strtotime($task['completion_time']);
            if ($dt !== false) {
                // π.χ. 2025-06-02 14:30:00 → "2025-06-02T14:30"
                $dtValue = date('Y-m-d\TH:i', $dt);
            }
        }
      ?>
      <input
        type="datetime-local"
        name="completion_time"
        value="<?= $dtValue ?>"
        required>
    </label>

    <!-- Κόστος -->
    <label>
      Κόστος:
      <input
        type="number"
        step="0.01"
        name="cost"
        value="<?= htmlspecialchars($task['cost'] ?? '') ?>"
        required>
    </label>

    <button type="submit"><?= isset($task) ? 'Save Changes' : 'Create Task' ?></button>
    &nbsp;
    <a href="tasks.php">Ακύρωση</a>
  </form>
</body>
</html>
