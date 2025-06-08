<?php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

use Controllers\TaskController;

$ctl = new TaskController($pdo);
$ctl->create();
