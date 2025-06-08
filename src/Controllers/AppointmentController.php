<?php
namespace Controllers;

use Models\Appointment;
use Models\Car;
use Models\Customer;
use Models\Mechanic;

/**
 * AppointmentController.php
 *
 * - Λειτουργίες CRUD για Ραντεβού (Appointments)
 * - Προβολή λίστας, δημιουργία, επεξεργασία, ακύρωση, αλλαγή κατάστασης
 * - Εξαγωγή (Export) της λίστας σε CSV με σωστό UTF-8 BOM
 */
class AppointmentController {
    private Appointment $m;
    private Car         $carM;
    private Customer    $custM;
    private Mechanic    $mechM;

    public function __construct(\PDO $pdo) {
        $this->m     = new Appointment($pdo);
        $this->carM  = new Car($pdo);
        $this->custM = new Customer($pdo);
        $this->mechM = new Mechanic($pdo);
    }

    // ------------------------------------------------------------
    // 1. List Appointments (με φίλτρα & pagination)
    // ------------------------------------------------------------
    public function list(): void {
        requireLogin();
        $role     = $_SESSION['role'];
        $criteria = [];

        if ($role === 'customer') {
            $criteria['customer_id'] = $_SESSION['user_id'];
        } elseif ($role === 'mechanic') {
            $criteria['mechanic_id'] = $_SESSION['user_id'];
        } else {
            requireRole('secretary');
        }

        // Φίλτρα από GET
        if (!empty($_GET['date_from'])) {
            $criteria['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $criteria['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['status'])) {
            $criteria['status'] = $_GET['status'];
        }
        if (!empty($_GET['customer_last_name'])) {
            $criteria['customer_last_name'] = trim($_GET['customer_last_name']);
        }
        if (!empty($_GET['car_serial'])) {
            $criteria['car_serial'] = trim($_GET['car_serial']);
        }

        // Pagination
        $page   = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0
                  ? (int)$_GET['page']
                  : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $appts      = $this->m->search($criteria, $limit, $offset);
        $totalCount = $this->m->countAll($criteria);
        $totalPages = (int)ceil($totalCount / $limit);

        $token = generateCsrfToken();

        include __DIR__ . '/../../Views/appointments.php';
    }

    // ------------------------------------------------------------
    // 2. Φόρμα για Νέο Appointment
    // ------------------------------------------------------------
    public function createForm(): void {
        requireLogin();
        requireRole('secretary','customer');

        $token = generateCsrfToken();

        if ($_SESSION['role'] === 'customer') {
            $cars = $this->carM->search(['owner_id' => $_SESSION['user_id']]);
        } else {
            $cars = $this->carM->search([], 100000, 0);
        }

        $mechanics = $this->mechM->search();
        $customers = ($_SESSION['role'] === 'secretary')
                    ? $this->custM->search()
                    : [$this->custM->findByUserId($_SESSION['user_id'])];

        $slots = ['08:00','10:00','12:00','14:00'];

        $booked = [];
        if (!empty($_GET['mechanic_id']) && !empty($_GET['appointment_date'])) {
            $booked = $this->m->getByMechanicAndDate(
                (int)$_GET['mechanic_id'],
                $_GET['appointment_date']
            );
        }

        include __DIR__ . '/../../Views/appointment_form.php';
    }

    // ------------------------------------------------------------
    // 3. Δημιουργία Νέου Appointment (POST) με Validation & old data
    // ------------------------------------------------------------
    public function create(): void {
        requireLogin();
        requireRole('secretary','customer');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        // Αποθηκεύουμε όλα τα πεδία για περίπτωση σφάλματος
        $_SESSION['old_appt'] = [
            'appointment_date'    => $_POST['appointment_date']   ?? '',
            'appointment_time'    => $_POST['appointment_time']   ?? '',
            'reason'              => $_POST['reason']             ?? '',
            'problem_description' => trim($_POST['problem_description'] ?? ''),
            'car_serial'          => $_POST['car_serial']          ?? '',
            'customer_id'         => ($_SESSION['role'] === 'customer')
                                     ? $_SESSION['user_id']
                                     : (int)($_POST['customer_id'] ?? 0),
            'mechanic_id'         => (int)($_POST['mechanic_id']   ?? 0)
        ];

        // Διαβάζουμε
        $date               = $_SESSION['old_appt']['appointment_date'];
        $time               = $_SESSION['old_appt']['appointment_time'];
        $mechanicId         = $_SESSION['old_appt']['mechanic_id'];
        $reason             = $_SESSION['old_appt']['reason'];
        $problemDescription = $_SESSION['old_appt']['problem_description'];
        $carSerial          = $_SESSION['old_appt']['car_serial'];
        $customerId         = $_SESSION['old_appt']['customer_id'];

        // 3.1 Validation: Date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['error'] = 'Ημερομηνία απαραίτητη με μορφή YYYY-MM-DD.';
            header("Location: create_appointment.php?mechanic_id={$mechanicId}&appointment_date={$date}");
            exit;
        }
        $tsDate = strtotime($date);
        if ($tsDate === false) {
            $_SESSION['error'] = 'Μη έγκυρη ημερομηνία.';
            header("Location: create_appointment.php");
            exit;
        }
        $dow = (int)date('w', $tsDate);
        if ($dow === 0) {
            $_SESSION['error'] = 'Τα ραντεβού επιτρέπονται μόνο Δευτέρα–Σάββατο.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }
        if ($tsDate < strtotime(date('Y-m-d'))) {
            $_SESSION['error'] = 'Ημερομηνία ραντεβού δεν μπορεί να είναι στο παρελθόν.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }

        // 3.2 Validation: Time
        $validSlots = ['08:00','10:00','12:00','14:00'];
        if (!in_array($time, $validSlots, true)) {
            $_SESSION['error'] = 'Επιλέξτε ώρα 08:00, 10:00, 12:00 ή 14:00.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }

        // 3.3 Double-booking
        $existingSlots = $this->m->getByMechanicAndDate($mechanicId, $date);
        if (in_array($time, $existingSlots, true)) {
            $_SESSION['error'] = "Το slot {$time} έχει ήδη κλειστεί για αυτόν τον μηχανικό.";
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }

        // 3.4 Reason & Description
        if (!in_array($reason, ['repair','service'], true)) {
            $_SESSION['error'] = 'Επιλέξτε σωστό λόγο (Repair ή Service).';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }
        if ($reason === 'repair' && $problemDescription === '') {
            $_SESSION['error'] = 'Για Repair, απαιτείται περιγραφή προβλήματος.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }

        // 3.5 Έλεγχοι FK: Car, Mechanic, Customer
        $carRow = $this->carM->findBySerial($carSerial);
        if (!$carRow) {
            $_SESSION['error'] = 'Το αυτοκίνητο δεν βρέθηκε.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }
        $mechRow = $this->mechM->findByUserId($mechanicId);
        if (!$mechRow) {
            $_SESSION['error'] = 'Ο μηχανικός δεν βρέθηκε.';
            header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
            exit;
        }
        if ($_SESSION['role'] !== 'customer') {
            $custRow = $this->custM->findByUserId($customerId);
            if (!$custRow) {
                $_SESSION['error'] = 'Ο πελάτης δεν βρέθηκε.';
                header("Location: create_appointment.php?appointment_date={$date}&mechanic_id={$mechanicId}");
                exit;
            }
        }

        // 3.6 Όλα OK: Δημιουργία
        $data = [
            'appointment_date'    => $date,
            'appointment_time'    => $time,
            'reason'              => $reason,
            'problem_description' => $problemDescription ?: null,
            'car_serial'          => $carSerial,
            'customer_id'         => $customerId,
            'mechanic_id'         => $mechanicId
        ];

        try {
            $newId = $this->m->create($data);
            unset($_SESSION['old_appt']);
            $_SESSION['success'] = "Το ραντεβού #{$newId} δημιουργήθηκε επιτυχώς.";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία ραντεβού: ' . $e->getMessage();
        }
        header('Location: appointments.php');
        exit;
    }

    // ------------------------------------------------------------
    // 4. Φόρμα Reschedule (Επεξεργασία) Appointment
    // ------------------------------------------------------------
    public function editForm(): void {
        requireLogin();
        requireRole('secretary','customer','mechanic');

        $id   = (int)($_GET['id'] ?? 0);
        $appt = $this->m->findById($id);

        if (!$appt || $appt['status'] !== 'CREATED') {
            http_response_code(403);
            exit('Δεν επιτρέπεται reschedule για αυτό το ραντεβού.');
        }

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/appointment_form.php';
    }

    // ------------------------------------------------------------
    // 5. Reschedule Appointment (POST) με Validation & old data
    // ------------------------------------------------------------
    public function reschedule(): void {
        requireLogin();
        requireRole('secretary','customer','mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id   = (int)($_POST['id'] ?? 0);
        $appt = $this->m->findById($id);
        if (!$appt) {
            $_SESSION['error'] = 'Το ραντεβού δεν βρέθηκε.';
            header('Location: appointments.php');
            exit;
        }

        // Αποθήκευση παλιών και νέων πεδίων
        $_SESSION['old_appt'] = [
            'appointment_date'    => $_POST['appointment_date'] ?? $appt['appointment_date'],
            'appointment_time'    => $_POST['appointment_time'] ?? substr($appt['appointment_time'], 0, 5),
            'reason'              => $appt['reason'],
            'problem_description' => $appt['problem_description'],
            'car_serial'          => $appt['car_serial'],
            'customer_id'         => $appt['customer_id'],
            'mechanic_id'         => $appt['mechanic_id']
        ];

        $newDate    = $_SESSION['old_appt']['appointment_date'];
        $newTime    = $_SESSION['old_appt']['appointment_time'];
        $mechanicId = $appt['mechanic_id'];

        // 5.1 Validation New Date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            $_SESSION['error'] = 'Ημερομηνία απαραίτητη με μορφή YYYY-MM-DD.';
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }
        $tsDate = strtotime($newDate);
        if ($tsDate === false) {
            $_SESSION['error'] = 'Μη έγκυρη ημερομηνία.';
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }
        $dow = (int)date('w', $tsDate);
        if ($dow === 0) {
            $_SESSION['error'] = 'Τα ραντεβού επιτρέπονται μόνο Δευτέρα–Σάββατο.';
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }
        if ($tsDate < strtotime(date('Y-m-d'))) {
            $_SESSION['error'] = 'Ημερομηνία ραντεβού δεν μπορεί να είναι στο παρελθόν.';
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }

        // 5.2 Validation New Time
        $validSlots = ['08:00','10:00','12:00','14:00'];
        if (!in_array($newTime, $validSlots, true)) {
            $_SESSION['error'] = 'Επιλέξτε ώρα 08:00, 10:00, 12:00 ή 14:00.';
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }

        // 5.3 Double-booking, εξαιρώντας το τρέχον appointment
        $existingSlots = $this->m->getByMechanicAndDate($mechanicId, $newDate);
        $existingSlots = array_filter($existingSlots, function($slot) use ($appt) {
            return $slot !== substr($appt['appointment_time'], 0, 5);
        });
        if (in_array($newTime, $existingSlots, true)) {
            $_SESSION['error'] = "Το slot {$newTime} έχει ήδη κλειστεί για αυτόν τον μηχανικό.";
            header("Location: edit_appointment.php?id={$id}");
            exit;
        }

        // 5.4 Όλα εντάξει: αποθήκευση αλλαγής
        $ok = $this->m->reschedule($id, $newDate, $newTime);
        if ($ok) {
            unset($_SESSION['old_appt']);
            $_SESSION['success'] = 'Το ραντεβού μετατέθηκε επιτυχώς.';
        } else {
            $_SESSION['error'] = 'Δεν επιτράπηκε αλλαγή.';
        }
        header('Location: appointments.php');
        exit;
    }

    // ------------------------------------------------------------
    // 6. Change Status (χωρίς αλλαγές)
    // ------------------------------------------------------------
    public function changeStatus(): void {
        requireLogin();
        requireRole('secretary','mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $allowed = ['CREATED','IN_PROGRESS','COMPLETED','CANCELLED'];
        if (!in_array($status, $allowed, true)) {
            $_SESSION['error'] = 'Μη έγκυρη κατάσταση.';
            header('Location: appointments.php');
            exit;
        }

        try {
            $ok = $this->m->changeStatus($id, $status);
            $_SESSION['success'] = $ok ? "Η κατάσταση άλλαξε σε {$status}." : 'Η κατάσταση δεν άλλαξε.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Σφάλμα κατά την αλλαγή κατάστασης: ' . $e->getMessage();
        }
        header('Location: appointments.php');
        exit;
    }

    // ------------------------------------------------------------
    // 7. Cancel Appointment (χωρίς αλλαγές)
    // ------------------------------------------------------------
    public function cancel(): void {
        requireLogin();
        requireRole('secretary','customer','mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->m->cancel($id);
        $_SESSION['success'] = $ok ? 'Το ραντεβού ακυρώθηκε.' : 'Η ακύρωση απέτυχε.';
        header('Location: appointments.php');
        exit;
    }

    // ------------------------------------------------------------
    // 8. List Appointments for Mechanic (χωρίς αλλαγές)
    // ------------------------------------------------------------
    public function mechanicList(): void {
        requireLogin();
        requireRole('mechanic');

        $mechanicId = $_SESSION['user_id'];
        $filterDate = $_GET['date'] ?? null;

        $appts = $this->m->getForMechanic($mechanicId, $filterDate);
        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/appointments_mechanic.php';
    }

    // ------------------------------------------------------------
    // 9. Export CSV (Διορθωμένη έκδοση fputcsv)
    // ------------------------------------------------------------
    public function exportCsv(): void {
        requireLogin();
        // Ορίζουμε ποιοι ρόλοι επιτρέπονται να κάνουν export
        requireRole('secretary','mechanic','customer');

        $role     = $_SESSION['role'];
        $criteria = [];

        if ($role === 'customer') {
            $criteria['customer_id'] = $_SESSION['user_id'];
        } elseif ($role === 'mechanic') {
            $criteria['mechanic_id'] = $_SESSION['user_id'];
        }

        // Φίλτρα από GET
        if (!empty($_GET['date_from'])) {
            $criteria['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $criteria['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['status'])) {
            $criteria['status'] = $_GET['status'];
        }
        if (!empty($_GET['customer_last_name'])) {
            $criteria['customer_last_name'] = trim($_GET['customer_last_name']);
        }
        if (!empty($_GET['car_serial'])) {
            $criteria['car_serial'] = trim($_GET['car_serial']);
        }

        // Παίρνουμε όλα τα ραντεβού (χωρίς pagination) για export
        $appointments = $this->m->search($criteria, 1000000, 0);

        // Στέλνουμε τους κατάλληλους HTTP headers **πριν** από οποιοδήποτε output
        header('Content-Type: text/csv; charset=UTF-8');
        $filename = 'appointments_' . date('Ymd_His') . '.csv';
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        // Αποστολή BOM UTF-8
        echo "\xEF\xBB\xBF";

        // Ανοίγουμε την έξοδο ως “file” για το fputcsv
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new \Exception('Δεν μπόρεσε να ανοίξει η έξοδος για CSV');
        }

        // Header row
        fputcsv($output, [
            'ID',
            'Date',
            'Time',
            'Reason',
            'Status',
            'Car Serial',
            'Customer Last Name',
            'Customer Identity No',
            'Mechanic ID'
        ]); // Χρησιμοποιούμε default separator=','  και default enclosure='"'

        // Κάθε ραντεβού σε νέα γραμμή
        foreach ($appointments as $a) {
            fputcsv($output, [
                $a['id'],
                $a['appointment_date'],
                $a['appointment_time'],
                $a['reason'],
                $a['status'],
                $a['car_serial'],
                $a['customer_last_name'] ?? '',
                $a['customer_id'] ?? '',
                $a['mechanic_id'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
