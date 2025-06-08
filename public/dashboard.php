<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

requireLogin();

// Καθαρίζουμε τυχόν success μήνυμα από προηγούμενη ενέργεια
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Φόρτωση User model για να βρούμε το όνομα
require_once __DIR__ . '/../src/Models/User.php';
use Models\User;

$userModel = new User($pdo);
$user      = $userModel->findById((int)$_SESSION['user_id']);
$username  = $user
           ? $user['first_name'] . ' ' . $user['last_name']
           : ($_SESSION['username'] ?? 'Χρήστης');

$role = $_SESSION['role'];

// Επιλογή view ανά ρόλο, κρατώντας το ίδιο layout
$viewFile = __DIR__ . '/../Views/dashboard.php';
if ($role === 'customer') {
    $viewFile = __DIR__ . '/../Views/customer_dashboard.php';
} elseif ($role === 'mechanic') {
    $viewFile = __DIR__ . '/../Views/mechanic_dashboard.php';
}

include $viewFile;
