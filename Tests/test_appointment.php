<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Models/Customer.php';
require __DIR__ . '/../src/Models/Mechanic.php';
require __DIR__ . '/../src/Models/Car.php';
require __DIR__ . '/../src/Models/Appointment.php';

use Models\User;
use Models\Customer;
use Models\Mechanic;
use Models\Car;
use Models\Appointment;

header('Content-Type: text/plain; charset=utf-8');
$pdo->beginTransaction();

try {
    $uM = new User($pdo);
    $cM = new Customer($pdo);
    $mM = new Mechanic($pdo);
    $carM = new Car($pdo);
    $aM = new Appointment($pdo);

    // 1) Δημιουργία Customer και Mechanic
    $custId = $uM->create([
      'username'=>'app_cust','password'=>'pw','first_name'=>'Anna','last_name'=>'Cli','identity_number'=>'CLI123','role'=>'customer'
    ]);
    $cM->create(['user_id'=>$custId,'tax_id'=>'101010101','address'=>'Οδός X 1']);

    $mechId = $uM->create([
      'username'=>'app_mech','password'=>'pw','first_name'=>'Petros','last_name'=>'Fix','identity_number'=>'FIX456','role'=>'mechanic'
    ]);
    $mM->create(['user_id'=>$mechId,'specialty'=>'Brakes']);

    // 2) Δημιουργία Car
    $serial = 'AP'.time();
    $carM->create([
      'serial_number'=>$serial,'model'=>'M1','brand'=>'B1','type'=>'passenger',
      'drive_type'=>'gas','door_count'=>4,'wheel_count'=>4,
      'production_date'=>'2022-06-01','acquisition_year'=>'2023','owner_id'=>$custId
    ]);

    // 3) Δημιουργία Appointment
    $appId = $aM->create([
      'appointment_date'=>'2025-06-15',
      'appointment_time'=>'10:30',
      'reason'=>'repair',
      'problem_description'=>'Engine noise',
      'car_serial'=>$serial,
      'customer_id'=>$custId,
      'mechanic_id'=>$mechId
    ]);
    echo "Created appointment ID: $appId\n";

    // 4) Ανάκτηση
    $app = $aM->findById($appId);
    print_r($app);

    // 5) Reschedule
    $ok = $aM->reschedule($appId,'2025-06-16','11:00');
    echo "Rescheduled? ".($ok?'yes':'no')."\n";

    // 6) Change status
    $aM->changeStatus($appId,'IN_PROGRESS');
    echo "Status set to IN_PROGRESS\n";

    // 7) Cancel
    $aM->cancel($appId);
    echo "Cancelled appointment\n";

    $pdo->rollBack();
} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Error: ".$e->getMessage();
}
