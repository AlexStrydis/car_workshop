<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Models/Customer.php';
require __DIR__ . '/../src/Models/Car.php';

use Models\User;
use Models\Customer;
use Models\Car;

header('Content-Type: text/plain; charset=utf-8');

$pdo->beginTransaction();
try {
    $userModel = new User($pdo);
    $custModel = new Customer($pdo);
    $carModel  = new Car($pdo);

    // 1) Δημιουργία demo User & Customer
    $userId = $userModel->create([
        'username'        => 'owner_demo',
        'password'        => 'ownerpass',
        'first_name'      => 'George',
        'last_name'       => 'Driver',
        'identity_number' => 'DRV123456',
        'role'            => 'customer'
    ]);
    $custModel->create([
        'user_id' => $userId,
        'tax_id'  => '555666777',
        'address' => 'Λεωφ. Δείγματος 45'
    ]);

    // 2) Δημιουργία αυτοκινήτου
    $serial = 'SN'.time();
    $carModel->create([
        'serial_number'    => $serial,
        'model'            => 'Model X',
        'brand'            => 'BrandY',
        'type'             => 'passenger',
        'drive_type'       => 'gas',
        'door_count'       => 4,
        'wheel_count'      => 4,
        'production_date'  => '2020-01-15',
        'acquisition_year' => '2021',
        'owner_id'         => $userId
    ]);
    echo "Created car serial: $serial\n";

    // 3) Ανάκτηση και εμφάνιση
    $info = $carModel->findBySerial($serial);
    print_r($info);

    $pdo->rollBack();  // undo demos
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: ".$e->getMessage();
}
