<?php
require __DIR__.'/../config/app.php';
require __DIR__.'/../config/db.php';
require __DIR__.'/../src/Controllers/AuthController.php';
use Controllers\AuthController;

$auth = new AuthController($pdo);
$auth->logout();