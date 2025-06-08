<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Models/Mechanic.php';

use Models\User;
use Models\Mechanic;

header('Content-Type: text/plain; charset=utf-8');

$pdo->beginTransaction();

try {
    $userModel = new User($pdo);
    $mechModel = new Mechanic($pdo);

    // 1) Δημιουργία User πρώτα
    $userId = $userModel->create([
        'username'        => 'mech_demo',
        'password'        => 'pwmech123',
        'first_name'      => 'Nikos',
        'last_name'       => 'Tech',
        'identity_number' => 'MECH123456',
        'role'            => 'mechanic'
    ]);
    echo "New Mechanic User ID: $userId\n";

    // 2) Δημιουργία Mechanic record
    $mechModel->create([
        'user_id'   => $userId,
        'specialty' => 'Engine Repair'
    ]);
    echo "Mechanic record created.\n";

    // 3) Ανάκτηση
    $info = $mechModel->findByUserId($userId);
    print_r($info);

    $pdo->rollBack();  // undo demo data
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
