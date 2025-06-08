<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../src/Controllers/MechanicController.php';
use Controllers\MechanicController;

$ctl = new MechanicController($pdo);
$ctl->dashboard();
