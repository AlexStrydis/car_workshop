<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title><?= isset($appt) ? 'Edit Appointment' : 'New Appointment' ?></title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <p>
    <a href="appointments.php">← Επιστροφή στα Ραντεβού</a>
  </p>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php endif; ?>


  <form method="post" action="<?= isset($appt) ? 'edit_appointment.php' : 'create_appointment.php' ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
    <?php if (isset($appt)): ?>
      <input type="hidden" name="id" value="<?= (int)$appt['id'] ?>">
    <?php endif; ?>

    <!-- Date: required, min=σήμερα -->
    <label>
      Date:
      <input
        type="date"
        name="appointment_date"
        required
        min="<?= date('Y-m-d') ?>"
        value="<?= htmlspecialchars(
          $_SESSION['old_appt']['appointment_date'] 
          ?? ($appt['appointment_date'] ?? '')
        ) ?>">
    </label>
    <br>

    <!-- Time: required, επιλογή από slots -->
    <label>
      Time:
      <select name="appointment_time" id="appointment_time" required>
        <option value="">-- Επιλέξτε --</option>
        <?php if (isset($slots) && is_array($slots)): ?>
          <?php foreach ($slots as $slot): ?>
            <?php
              $chosen = 
                (isset($_SESSION['old_appt']['appointment_time']) && $_SESSION['old_appt']['appointment_time'] === $slot)
                || (!isset($_SESSION['old_appt']['appointment_time']) && isset($appt) && substr($appt['appointment_time'], 0,5) === $slot);
              $isBooked = (isset($booked) && in_array($slot, $booked, true));
            ?>
            <option
              value="<?= htmlspecialchars($slot) ?>"
              <?= $isBooked ? 'disabled' : '' ?>
              <?= $chosen ? 'selected' : '' ?>>
              <?= htmlspecialchars($slot) ?> – <?= date('H:i', strtotime("+2 hours", strtotime($slot))) ?>
            </option>
          <?php endforeach; ?>
        <?php else: ?>
          <option
            value="<?= htmlspecialchars(
              $_SESSION['old_appt']['appointment_time'] 
              ?? (isset($appt) ? substr($appt['appointment_time'], 0,5) : '08:00')
            ) ?>" selected>
            <?= htmlspecialchars(
              $_SESSION['old_appt']['appointment_time'] 
              ?? (isset($appt) ? substr($appt['appointment_time'], 0,5) : '08:00')
            ) ?>
          </option>
        <?php endif; ?>
      </select>
    </label>
    <br>

    <!-- Reason: required -->
    <label>
      Reason:
      <select name="reason" id="reason" required>
        <option value="">-- Επιλέξτε --</option>
        <option value="repair"
          <?= (
               (isset($_SESSION['old_appt']['reason']) && $_SESSION['old_appt']['reason'] === 'repair')
            || (!isset($_SESSION['old_appt']['reason']) && isset($appt) && $appt['reason'] === 'repair')
          ) ? 'selected' : '' ?>>
          Repair
        </option>
        <option value="service"
          <?= (
               (isset($_SESSION['old_appt']['reason']) && $_SESSION['old_appt']['reason'] === 'service')
            || (!isset($_SESSION['old_appt']['reason']) && isset($appt) && $appt['reason'] === 'service')
          ) ? 'selected' : '' ?>>
          Service
        </option>
      </select>
    </label>
    <br>

    <!-- Problem Description: required μόνο αν Reason=repair -->
    <label>
      Problem description:
      <textarea
        name="problem_description"
        id="problem_description"
        rows="3"
        cols="40"
        <?= (
             (isset($_SESSION['old_appt']['reason']) && $_SESSION['old_appt']['reason'] === 'repair')
          || (!isset($_SESSION['old_appt']['reason']) && isset($appt) && $appt['reason'] === 'repair')
        ) ? 'required' : '' ?>><?= htmlspecialchars(
            $_SESSION['old_appt']['problem_description'] 
            ?? ($appt['problem_description'] ?? '')
        ) ?></textarea>
    </label>
    <br>

    <!-- Car Serial: required -->
    <label>
      Car:
      <select name="car_serial" required>
        <option value="">-- Επιλέξτε --</option>
        <?php foreach ($cars as $c): ?>
          <option
            value="<?= htmlspecialchars($c['serial_number']) ?>"
            <?= (
                 (isset($_SESSION['old_appt']['car_serial']) && $_SESSION['old_appt']['car_serial'] === $c['serial_number'])
              || (!isset($_SESSION['old_appt']['car_serial']) && isset($appt) && $appt['car_serial'] === $c['serial_number'])
            ) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['serial_number']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br>

    <!-- Customer (μόνο αν secretary): required -->
    <?php if (isset($customers) && is_array($customers)): ?>
      <label>
        Customer:
        <select name="customer_id" required>
          <option value="">-- Επιλέξτε --</option>
          <?php foreach ($customers as $cu): ?>
            <option
              value="<?= (int)$cu['user_id'] ?>"
              <?= (
                   (isset($_SESSION['old_appt']['customer_id']) && $_SESSION['old_appt']['customer_id'] === $cu['user_id'])
                || (!isset($_SESSION['old_appt']['customer_id']) && isset($appt) && $appt['customer_id'] === $cu['user_id'])
              ) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cu['first_name'] . ' ' . $cu['last_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <br>
    <?php endif; ?>

    <!-- Mechanic: required -->
    <?php if (isset($mechanics) && is_array($mechanics)): ?>
      <label>
        Mechanic:
        <select name="mechanic_id" id="mechanic_id" required>
          <option value="">-- Επιλέξτε --</option>
          <?php foreach ($mechanics as $m): ?>
            <option
              value="<?= (int)$m['user_id'] ?>"
              <?= (
                   (isset($_SESSION['old_appt']['mechanic_id']) && $_SESSION['old_appt']['mechanic_id'] === $m['user_id'])
                || (!isset($_SESSION['old_appt']['mechanic_id']) && isset($appt) && $appt['mechanic_id'] === $m['user_id'])
              ) ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <br>
    <?php endif; ?>

    <button type="submit"><?= isset($appt) ? 'Update Appointment' : 'Create Appointment' ?></button>
    &nbsp;<a href="appointments.php">Cancel</a>
  </form>

  <script>
    const reasonEl = document.getElementById('reason');
    const problemEl = document.getElementById('problem_description');

    function updateProblemRequirement() {
      if (reasonEl.value === 'repair') {
        problemEl.setAttribute('required', 'required');
      } else {
        problemEl.removeAttribute('required');
      }
    }

    reasonEl.addEventListener('change', updateProblemRequirement);
    updateProblemRequirement();
  </script>

  <?php
    // Καθαρίζουμε το old_appt data
    unset($_SESSION['old_appt']);
  ?>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
