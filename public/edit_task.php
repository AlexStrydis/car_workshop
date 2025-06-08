<?php
// public/edit_task.php
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Controllers/TaskController.php';

use Controllers\TaskController;

$ctl = new TaskController($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctl->edit();
} else {
    $ctl->editForm();
}
