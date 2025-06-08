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
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <header style="text-align: center; margin: 0; font-size: 1.5rem; color: #333; position: relative;">
    <h1>Πίνακας ελέγχου του χρήστη <?php echo htmlspecialchars($username); ?></h1>
    <button class="btn btn-danger" style="position: absolute; top: 10px; right: 10px; font-size: 0.9rem; padding: 5px 10px;">
      <a href="logout.php" style="text-decoration: none; color: #fff;">Αποσύνδεση</a>
    </button>
  </header>
  <div style="display: flex; position: relative;">
    <aside style="width: 20%; padding: 10px; position: absolute; left: 0;">
      <h2>Μενού</h2>
      <ul style="list-style: none; padding: 0;">
        <?php if ($role === 'secretary'): ?>
          <li><a href="users.php">Διαχείριση Χρηστών</a></li>
          <li><a href="cars.php">Διαχείριση Αυτοκινήτων</a></li>
          <li><a href="appointments.php">Διαχείριση Ραντεβού</a></li>
        <?php elseif ($role === 'customer'): ?>
          <li><a href="customer_dashboard.php">Διαχείριση Αυτοκινήτων Μου</a></li>
          <li><a href="customer_dashboard.php">Διαχείριση Ραντεβού Μου</a></li>
        <?php elseif ($role === 'mechanic'): ?>
          <li><a href="mechanic_dashboard.php">Τα Ραντεβού Μου</a></li>
          <li><a href="tasks.php">Οι Εργασίες Μου</a></li>
        <?php endif; ?>
      </ul>
    </aside>
    <main style="flex-grow: 1; display: flex; justify-content: center; align-items: center; height: calc(100vh - 500px);">
      <p>Καλωσήρθες, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)!</p>
    </main>
  </div>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
