// public/create_car.php
<?php
require __DIR__.'/../config/app.php';
require __DIR__.'/../config/db.php';
require __DIR__.'/../src/Controllers/CarController.php';
use Controllers\CarController;

$ctl = new CarController($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctl->create();
} else {
    $ctl->createForm();
}
