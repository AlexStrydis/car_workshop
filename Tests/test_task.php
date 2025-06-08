<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Models/Customer.php';
require __DIR__ . '/../src/Models/Mechanic.php';
require __DIR__ . '/../src/Models/Car.php';
require __DIR__ . '/../src/Models/Appointment.php';
require __DIR__ . '/../src/Models/Task.php';

use Models\User;
use Models\Customer;
use Models\Mechanic;
use Models\Car;
use Models\Appointment;
use Models\Task;

header('Content-Type: text/plain; charset=utf-8');
$pdo->beginTransaction();

try {
    // 1) Setup: User, Customer, Mechanic, Car, Appointment
    $uM = new User($pdo);
    $cM = new Customer($pdo);
    $mM = new Mechanic($pdo);
    $carM = new Car($pdo);
    $aM = new Appointment($pdo);
    $tM = new Task($pdo);

    $custId = $uM->create([
      'username'=>'tsk_cust','password'=>'pw','first_name'=>'Eleni','last_name'=>'Fixer','identity_number'=>'FIX789','role'=>'customer'
    ]);
    $cM->create(['user_id'=>$custId,'tax_id'=>'123123123','address'=>'Οδός Δοκιμής 7']);

    $mechId = $uM->create([
      'username'=>'tsk_mech','password'=>'pw','first_name'=>'Yannis','last_name'=>'Maker','identity_number'=>'MAK321','role'=>'mechanic'
    ]);
    $mM->create(['user_id'=>$mechId,'specialty'=>'Oil Change']);

    $serial = 'TSK'.time();
    $carM->create([
      'serial_number'=>$serial,'model'=>'MX','brand'=>'BZ','type'=>'passenger','drive_type'=>'gas',
      'door_count'=>4,'wheel_count'=>4,'production_date'=>'2021-05-10','acquisition_year'=>'2022','owner_id'=>$custId
    ]);

    $appId = $aM->create([
      'appointment_date'=>'2025-07-01','appointment_time'=>'09:00','reason'=>'service',
      'problem_description'=>null,'car_serial'=>$serial,'customer_id'=>$custId,'mechanic_id'=>$mechId
    ]);

    // 2) Δημιουργία Task
    $taskId = $tM->create([
      'appointment_id'   => $appId,
      'description'      => 'Replace oil filter',
      'materials'        => 'Oil filter, Oil',
      'completion_time'  => '2025-07-01 10:30:00',
      'cost'             => 45.50
    ]);
    echo "Created Task ID: $taskId\n";

    // 3) Ανάκτηση όλων των tasks για το appointment
    $tasks = $tM->findByAppointment($appId);
    print_r($tasks);

    // 4) Ενημέρωση Task
    $updated = $tM->update($taskId, [
      'description'     => 'Replace oil filter & gasket',
      'materials'       => 'Oil filter, Oil, Gasket',
      'completion_time' => '2025-07-01 11:00:00',
      'cost'            => 50.00
    ]);
    echo "Task updated? ".($updated?'yes':'no')."\n";

    // 5) Διαγραφή Task
    $deleted = $tM->delete($taskId);
    echo "Task deleted? ".($deleted?'yes':'no')."\n";

    $pdo->rollBack();  // undo everything
} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Error: ".$e->getMessage();
}
