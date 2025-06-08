<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Models/Customer.php';

use Models\User;
use Models\Customer;

header('Content-Type: text/plain; charset=utf-8');

$pdo->beginTransaction();

try {
    $userModel = new User($pdo);
    $custModel = new Customer($pdo);

    // 1) Δημιουργία πρώτα User
    $userId = $userModel->create([
        'username'        => 'cust_demo',
        'password'        => 'pw12345',
        'first_name'      => 'Maria',
        'last_name'       => 'Kots',
        'identity_number' => 'GHI789012',
        'role'            => 'customer'
    ]);
    echo "New User ID: $userId\n";

    // 2) Δημιουργία Customer details
    $custModel->create([
        'user_id' => $userId,
        'tax_id'  => '999888777',
        'address' => 'Οδός Παράδειγμα 123'
    ]);
    echo "Customer record created.\n";

    // 3) Ανάκτηση
    $info = $custModel->findByUserId($userId);
    print_r($info);

    $pdo->rollBack();  // γυρνάμε πίσω για να μην μπαίνουν demo εγγραφές
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
