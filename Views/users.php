<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Users (<?= htmlspecialchars($mode) ?>)</title>
  <style> form.inline{display:inline;} </style>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <h1>Users (<?= htmlspecialchars($mode) ?>)</h1>
  <p>
    <a href="users.php?mode=all">All</a> |
    <a href="users.php?mode=pending">Pending</a> |
    <a href="dashboard.php">Dashboard</a>
  </p>

  <?php if (!empty($_SESSION['success'])): ?>
    <p style="color:green"><?= htmlspecialchars($_SESSION['success']) ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <!-- Search Form -->
  <form method="get" action="users.php">
    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
    <label>Username: <input name="username" value="<?= htmlspecialchars($_GET['username'] ?? '') ?>"></label>
    <label>Last name: <input name="last_name" value="<?= htmlspecialchars($_GET['last_name'] ?? '') ?>"></label>
    <label>ID No: <input name="identity_number" value="<?= htmlspecialchars($_GET['identity_number'] ?? '') ?>"></label>
    <label>Role:
      <select name="role">
        <option value=""></option>
        <?php foreach (['customer','mechanic','secretary'] as $r): ?>
          <option value="<?= $r ?>" <?= (($_GET['role']??'')===$r)?'selected':'' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">Search</button>
  </form>

  <table border="1" cellpadding="5">
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>ID No</th>
      <th>Role</th>
      <th>Active</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
    <?php foreach($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['first_name']) ?></td>
        <td><?= htmlspecialchars($u['last_name']) ?></td>
        <td><?= htmlspecialchars($u['identity_number']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
        <td><?= htmlspecialchars($u['created_at']) ?></td>
        <td>
          <?php if ($mode==='pending'): ?>
            <!-- Activate -->
            <form method="post" action="activate_user.php" class="inline">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button type="submit">Activate</button>
            </form>
          <?php endif; ?>

          <!-- Edit -->
          <a href="edit_user.php?id=<?= $u['id'] ?>">Edit</a>

          <!-- Delete -->
          <form method="post" action="delete_user.php" class="inline" onsubmit="return confirm('Delete user?');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
