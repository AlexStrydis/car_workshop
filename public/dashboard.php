<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

requireLogin();

// Φόρτωση User model για να βρούμε το όνομα
require_once __DIR__ . '/../src/Models/User.php';
use Models\User;

$userModel = new User($pdo);
$user      = $userModel->findById((int)$_SESSION['user_id']);

// Αν υπάρχει ο χρήστης, φτιάχνουμε το πλήρες όνομα, αλλιώς fallback στο username
if ($user) {
    $username = $user['first_name'] . ' ' . $user['last_name'];
} else {
    $username = $_SESSION['username'] ?? 'Χρήστης';
}

$role = $_SESSION['role'];

include __DIR__ . '/../Views/dashboard.php';

