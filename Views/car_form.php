<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title><?= isset($car) ? 'Edit Car' : 'New Car' ?></title>
</head>
<body>
  <p>
    <a href="cars.php">← Επιστροφή στη Λίστα Αυτοκινήτων</a>
  </p>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form method="post" action="<?= isset($car) ? 'edit_car.php' : 'create_car.php' ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">

    <!-- Serial Number: αν create => editable, αν edit => readonly -->
    <label>
      Serial Number:
      <input
        name="serial_number"
        type="text"
        pattern="[A-Za-z0-9\-]+"
        title="Μόνο λατινικοί χαρακτήρες, αριθμοί ή παύλες."
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['serial_number'] 
          ?? ($car['serial_number'] ?? '')
        ) ?>"
        <?= isset($car) ? 'readonly' : 'required' ?>>
    </label>
    <br>

    <!-- Model: required -->
    <label>
      Model:
      <input
        name="model"
        type="text"
        minlength="1"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['model'] 
          ?? ($car['model'] ?? '')
        ) ?>">
    </label>
    <br>

    <!-- Brand: required -->
    <label>
      Brand:
      <input
        name="brand"
        type="text"
        minlength="1"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['brand'] 
          ?? ($car['brand'] ?? '')
        ) ?>">
    </label>
    <br>

    <!-- Type: required -->
    <label>
      Type:
      <select name="type" id="type" required>
        <option value="">-- Επιλέξτε --</option>
        <?php foreach (['passenger','truck','bus'] as $t): ?>
          <option
            value="<?= $t ?>"
            <?= (
                 (isset($_SESSION['old_car']['type']) && $_SESSION['old_car']['type'] === $t)
              || (!isset($_SESSION['old_car']['type']) && isset($car) && $car['type'] === $t)
            ) ? 'selected' : '' ?>>
            <?= ucfirst($t) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br>

    <!-- Drive Type: required -->
    <label>
      Drive Type:
      <select name="drive_type" id="drive_type" required>
        <option value="">-- Επιλέξτε --</option>
        <?php foreach (['electric','diesel','gas','hybrid'] as $d): ?>
          <option
            value="<?= $d ?>"
            <?= (
                 (isset($_SESSION['old_car']['drive_type']) && $_SESSION['old_car']['drive_type'] === $d)
              || (!isset($_SESSION['old_car']['drive_type']) && isset($car) && $car['drive_type'] === $d)
            ) ? 'selected' : '' ?>>
            <?= ucfirst($d) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br>

    <!-- Door Count: required, min/max ανάλογα με type -->
    <label>
      Door Count:
      <input
        name="door_count"
        id="door_count"
        type="number"
        min="1"
        max="7"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['door_count'] 
          ?? ($car['door_count'] ?? '1')
        ) ?>">
    </label>
    <br>

    <!-- Wheel Count: required, min/max ανάλογα με type -->
    <label>
      Wheel Count:
      <input
        name="wheel_count"
        id="wheel_count"
        type="number"
        min="4"
        max="18"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['wheel_count'] 
          ?? ($car['wheel_count'] ?? '4')
        ) ?>">
    </label>
    <br>

    <!-- Production Date: required, max = σήμερα -->
    <label>
      Production Date:
      <input
        name="production_date"
        type="date"
        max="<?= date('Y-m-d') ?>"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['production_date'] 
          ?? ($car['production_date'] ?? '')
        ) ?>">
    </label>
    <br>

    <!-- Acquisition Year: required, min=2000, max=τρέχον έτος -->
    <label>
      Acquisition Year:
      <input
        name="acquisition_year"
        type="number"
        min="2000"
        max="<?= date('Y') ?>"
        required
        value="<?= htmlspecialchars(
          $_SESSION['old_car']['acquisition_year'] 
          ?? ($car['acquisition_year'] ?? date('Y'))
        ) ?>">
    </label>
    <br>

    <button type="submit"><?= isset($car) ? 'Update Car' : 'Create Car' ?></button>
    &nbsp;<a href="cars.php">Cancel</a>
  </form>

  <script>
    const typeEl = document.getElementById('type');
    const doorEl = document.getElementById('door_count');
    const wheelEl = document.getElementById('wheel_count');

    // Ορισμός ορίων πόρτες/ρόδες ανά τύπο
    const limits = {
      passenger: { doors_min: 1, doors_max: 7, wheels_min: 4, wheels_max: 4 },
      truck:     { doors_min: 1, doors_max: 4, wheels_min: 4, wheels_max: 18 },
      bus:       { doors_min: 1, doors_max: 3, wheels_min: 4, wheels_max: 8 }
    };

    function updateLimits() {
      const type = typeEl.value;
      if (!limits[type]) {
        doorEl.min = 1;
        doorEl.max = 7;
        wheelEl.min = 4;
        wheelEl.max = 18;
        return;
      }
      const cfg = limits[type];
      doorEl.min = cfg.doors_min;
      doorEl.max = cfg.doors_max;
      if (doorEl.value < cfg.doors_min) doorEl.value = cfg.doors_min;
      if (doorEl.value > cfg.doors_max) doorEl.value = cfg.doors_max;

      wheelEl.min = cfg.wheels_min;
      wheelEl.max = cfg.wheels_max;
      if (type === 'passenger') {
        wheelEl.value = 4;
        wheelEl.readOnly = true;
      } else {
        wheelEl.readOnly = false;
        if (wheelEl.value < cfg.wheels_min) wheelEl.value = cfg.wheels_min;
        if (wheelEl.value > cfg.wheels_max) wheelEl.value = cfg.wheels_max;
      }
    }

    typeEl.addEventListener('change', updateLimits);
    updateLimits();
  </script>

  <?php
    // Καθαρίζουμε το old_car data
    unset($_SESSION['old_car']);
  ?>
</body>
</html>
