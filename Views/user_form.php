<!DOCTYPE html>
<html lang="el">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Edit User</title>
</head>
<body>
<?php include __DIR__ . '/../public/inc/header.php'; ?>
<section class="hero-background">
  <div class="container" style="background-color: rgba(0, 0, 0, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);">
  <h1>Edit User #<?= htmlspecialchars($user['id']) ?></h1>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form method="post" action="edit_user.php">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token) ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">

    <label>Username:
      <input name="username" value="<?= htmlspecialchars($user['username']) ?>">
    </label><br>

    <label>First Name:
      <input name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
    </label><br>

    <label>Last Name:
      <input name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
    </label><br>

    <label>ID Number:
      <input name="identity_number" value="<?= htmlspecialchars($user['identity_number']) ?>">
    </label><br>

    <label>Role:
      <select name="role">
        <?php foreach (['customer','mechanic','secretary'] as $r): ?>
          <option value="<?= $r ?>" <?= ($user['role']===$r)?'selected':'' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </label><br>

    <label>Active:
      <input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
    </label><br>

    <button type="submit">Save</button>
    <a href="users.php">Cancel</a>
  </form>
</div>
</section>
<?php include __DIR__ . '/../public/inc/footer.php'; ?>
</body>
</html>
