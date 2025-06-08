<?php
require __DIR__.'/../config/app.php';
require __DIR__.'/../config/db.php';
require __DIR__.'/../src/Controllers/AppointmentController.php';
use Controllers\AppointmentController;

$ctl = new AppointmentController($pdo);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $ctl->create();
} else {
    $ctl->createForm();
}
