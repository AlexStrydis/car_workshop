<?php
require __DIR__.'/../config/app.php';
require __DIR__.'/../config/db.php';
require __DIR__.'/../src/Controllers/UsersController.php';
use Controllers\UsersController;

$ctl = new UsersController($pdo);
$ctl->activate();
