<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="max-width: 1100px; background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <header style="text-align: center; margin: 0; font-size: 1.5rem; color: #333; position: relative;">
    <h1 style="padding:0 20px;">Κέντρο Ελέγχου του Χρήστη <?php echo htmlspecialchars($username); ?></h1>
  </header>
  <div style="text-align:center; margin:20px 0;">
    <?php if ($role === 'customer'): ?>
      <a href="cars.php" class="btn btn-primary">Διαχείριση Αυτοκινήτων</a>
      <a href="appointments.php" class="btn btn-primary">Διαχείριση Ραντεβού</a>
    <?php elseif ($role === 'mechanic'): ?>
      <a href="appointments_mechanic.php" class="btn btn-primary">Διαχείριση Ραντεβού</a>
      <a href="tasks.php" class="btn btn-primary">Ιστορικό Εργασιών</a>
      <a href="create_task.php" class="btn btn-primary">Νέα Εργασία</a>
    <?php elseif ($role === 'secretary'): ?>
      <a href="users.php" class="btn btn-primary">Διαχείριση Χρηστών</a>
      <a href="cars.php" class="btn btn-primary">Διαχείριση Αυτοκινήτων</a>
      <a href="appointments.php" class="btn btn-primary">Διαχείριση Ραντεβού</a>
      <a href="create_appointment.php" class="btn btn-primary">Νέα Προσθήκη</a>
    <?php endif; ?>
  </div>
  <p style="text-align:center;">Καλωσήρθες, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)!</p>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
