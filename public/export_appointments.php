<?php
// public/export_appointments.php

// Ίδια boilerplate με τα υπόλοιπα public αρχεία
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

use Controllers\AppointmentController;

// Δημιουργούμε τον controller
$controller = new AppointmentController($pdo);

// Καλούμε τη μέθοδο που εξάγει σε CSV, περνώντας τα GET filters
$controller->exportCsv();
