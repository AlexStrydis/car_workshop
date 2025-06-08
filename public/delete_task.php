<?php
// public/delete_task.php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Controllers/TaskController.php';

use Controllers\TaskController;

$ctl = new TaskController($pdo);
$ctl->delete();
