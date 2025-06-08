<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../src/Controllers/CustomerController.php';
use Controllers\CustomerController;

$ctl = new CustomerController($pdo);
$ctl->dashboard();
