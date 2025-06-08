<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';

use Models\User;

header('Content-Type: text/plain; charset=utf-8');

// 1) Σύνδεση & instantiation
$userModel = new User($pdo);

// 2) Δημιουργία demo χρήστη
$newId = $userModel->create([
    'username'        => 'demo_user',
    'password'        => 'pass1234',
    'first_name'      => 'Alex',
    'last_name'       => 'Student',
    'identity_number' => 'ID123456',
    'role'            => 'customer'
]);
echo "Created user ID: $newId\n";

// 3) Ανάκτηση & print
$user = $userModel->findById($newId);
print_r($user);
