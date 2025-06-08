<?php
namespace Controllers;

use Models\Task;
use Models\Appointment;

class TaskController {
    private Task $taskModel;
    private Appointment $apptModel;

    public function __construct(\PDO $pdo) {
        $this->taskModel = new Task($pdo);
        $this->apptModel = new Appointment($pdo);
    }

    /**
     * 1) Λίστα Εργασιών για τον Μηχανικό + Φόρμα Δημιουργίας Νέας Εργασίας
     */
    public function mechanicList(): void {
        requireLogin();
        requireRole('mechanic');

        $mechanicId = $_SESSION['user_id'];
        $filterDate = $_GET['date'] ?? null; // π.χ. ?date=2025-06-02

        // Φέρνουμε τις Εργασίες του μηχανικού (με βάση την υπάρχουσα μέθοδο getByMechanic)
        $tasks = $this->taskModel->getByMechanic($mechanicId, $filterDate);

        // --------------------------------------------------------
        // Αντί να καλούμε getByMechanicAndDate (που επέστρεφε μόνο ώρες),
        // τώρα φέρνουμε όλα τα ραντεβού του μηχανικού με search(),
        // φιλτραρισμένα εντός της επιλεγμένης ημερομηνίας.
        // --------------------------------------------------------

        // Κατασκευάζουμε τα κριτήρια φιλτραρίσματος
        $criteria = ['mechanic_id' => $mechanicId];
        if ($filterDate !== null) {
            $criteria['date_from'] = $filterDate;
            $criteria['date_to']   = $filterDate;
        } else {
            // Αν δεν έχει δοθεί φίλτρο, φέρνουμε μόνο ραντεβού της σημερινής ημερομηνίας
            $today = date('Y-m-d');
            $criteria['date_from'] = $today;
            $criteria['date_to']   = $today;
        }

        // Καλούμε τη search($criteria) χωρίς επιπλέον παραμέτρους limit/offset
        $appts = $this->apptModel->search($criteria);

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/tasks_mechanic.php';
    }

    /**
     * 2) Φόρμα Δημιουργίας Νέας Εργασίας (δεν χρησιμοποιείται ξεχωριστά,
     *    γιατί τη βάζουμε μέσα στη mechanicList()).
     */
    public function createForm(): void {
        requireLogin();
        requireRole('mechanic');

        $mechanicId = $_SESSION['user_id'];
        $filterDate = $_GET['date'] ?? date('Y-m-d');

        $criteria = [
            'mechanic_id' => $mechanicId,
            'date_from'   => $filterDate,
            'date_to'     => $filterDate
        ];

        // Καλούμε ξανά μονάχα search($criteria)
        $appts = $this->apptModel->search($criteria);

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/task_form.php';
    }

    /**
     * 3) Δημιουργία Νέας Εργασίας (POST)
     */
    public function create(): void {
        requireLogin();
        requireRole('mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $appointmentId   = (int)($_POST['appointment_id'] ?? 0);
        $description     = trim($_POST['description'] ?? '');
        $materials       = trim($_POST['materials'] ?? '');
        $completionTime  = trim($_POST['completion_time'] ?? '');
        $cost            = trim($_POST['cost'] ?? '');

        if ($appointmentId <= 0) {
            $_SESSION['error'] = 'Επιλέξτε ένα ραντεβού.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }
        if ($description === '') {
            $_SESSION['error'] = 'Η περιγραφή είναι υποχρεωτική.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }
        // Έλεγχος μορφής datetime-local "YYYY-MM-DDTHH:MM"
        if ($completionTime === '' || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $completionTime)) {
            $_SESSION['error'] = 'Η “Ώρα Ολοκλήρωσης” πρέπει να έχει μορφή YYYY-MM-DDTHH:MM.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }
        // Μετατροπή σε "YYYY-MM-DD HH:MM:00"
        $completionTimeFormatted = str_replace('T', ' ', $completionTime) . ':00';

        if (!is_numeric($cost) || (float)$cost < 0) {
            $_SESSION['error'] = 'Το κόστος πρέπει να είναι αριθμός ≥ 0.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        // Έλεγχος ότι το appointment ανήκει στ’ αλήθεια στον μηχανικό
        $appt = $this->apptModel->findById($appointmentId);
        if (!$appt || (int)$appt['mechanic_id'] !== $_SESSION['user_id']) {
            $_SESSION['error'] = 'Μη έγκυρο ραντεβού για εσάς.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        try {
            $newId = $this->taskModel->create([
                'appointment_id'  => $appointmentId,
                'description'     => $description,
                'materials'       => $materials,
                'completion_time' => $completionTimeFormatted,
                'cost'            => $cost,
            ]);
            $_SESSION['success'] = "Η εργασία #{$newId} δημιουργήθηκε.";
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία: ' . $e->getMessage();
        }

        header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
        exit;
    }

    /**
     * 4) Φόρμα Επεξεργασίας Εργασίας
     */
    public function editForm(): void {
        requireLogin();
        requireRole('mechanic');

        $id   = (int)($_GET['id'] ?? 0);
        $task = $this->taskModel->findById($id);
        if (!$task) {
            http_response_code(404);
            exit('Task not found');
        }

        $appt = $this->apptModel->findById($task['appointment_id']);
        if (!$appt || (int)$appt['mechanic_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            exit('Access Denied');
        }

        $mechanicId = $_SESSION['user_id'];
        // Όπως πριν, φέρνουμε τα ραντεβού της ίδιας ημερομηνίας
        $filterDate = explode(' ', $task['completion_time'])[0];
        $criteria   = [
            'mechanic_id' => $mechanicId,
            'date_from'   => $filterDate,
            'date_to'     => $filterDate
        ];
        // Πλέον μόνο search($criteria)
        $appts = $this->apptModel->search($criteria);

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/task_form.php';
    }

    /**
     * 5) Επεξεργασία (POST) Εργασίας
     */
    public function edit(): void {
        requireLogin();
        requireRole('mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id              = (int)($_POST['id'] ?? 0);
        $appointmentId   = (int)($_POST['appointment_id'] ?? 0);
        $description     = trim($_POST['description'] ?? '');
        $materials       = trim($_POST['materials'] ?? '');
        $completionTime  = trim($_POST['completion_time'] ?? '');
        $cost            = trim($_POST['cost'] ?? '');

        $task = $this->taskModel->findById($id);
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        $appt = $this->apptModel->findById($task['appointment_id']);
        if (!$appt || (int)$appt['mechanic_id'] !== $_SESSION['user_id']) {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα να επεξεργαστείτε αυτή την εργασία.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        if ($appointmentId <= 0) {
            $_SESSION['error'] = 'Επιλέξτε ένα ραντεβού.';
            header('Location: edit_task.php?id=' . $id);
            exit;
        }
        if ($description === '') {
            $_SESSION['error'] = 'Η περιγραφή είναι υποχρεωτική.';
            header('Location: edit_task.php?id=' . $id);
            exit;
        }
        if ($completionTime === '' || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $completionTime)) {
            $_SESSION['error'] = 'Η “Ώρα Ολοκλήρωσης” πρέπει να έχει μορφή YYYY-MM-DDTHH:MM.';
            header('Location: edit_task.php?id=' . $id);
            exit;
        }
        $completionTimeFormatted = str_replace('T', ' ', $completionTime) . ':00';

        if (!is_numeric($cost) || (float)$cost < 0) {
            $_SESSION['error'] = 'Το κόστος πρέπει να είναι αριθμός ≥ 0.';
            header('Location: edit_task.php?id=' . $id);
            exit;
        }

        $newAppt = $this->apptModel->findById($appointmentId);
        if (!$newAppt || (int)$newAppt['mechanic_id'] !== $_SESSION['user_id']) {
            $_SESSION['error'] = 'Το επιλεγμένο ραντεβού δεν είναι δικό σας.';
            header('Location: edit_task.php?id=' . $id);
            exit;
        }

        try {
            $ok = $this->taskModel->update($id, [
                'description'     => $description,
                'materials'       => $materials,
                'completion_time' => $completionTimeFormatted,
                'cost'            => $cost,
            ]);
            $_SESSION['success'] = $ok ? 'Η εργασία ενημερώθηκε.' : 'Δεν έγιναν αλλαγές.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση: ' . $e->getMessage();
        }

        header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
        exit;
    }

    /**
     * 6) Διαγραφή Εργασίας
     */
    public function delete(): void {
        requireLogin();
        requireRole('mechanic');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['id'] ?? 0);
        $task = $this->taskModel->findById($id);
        if (!$task) {
            $_SESSION['error'] = 'Η εργασία δεν βρέθηκε.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        $appt = $this->apptModel->findById($task['appointment_id']);
        if (!$appt || (int)$appt['mechanic_id'] !== $_SESSION['user_id']) {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα να διαγράψετε αυτή την εργασία.';
            header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
            exit;
        }

        $ok = $this->taskModel->delete($id);
        $_SESSION['success'] = $ok ? 'Η εργασία διαγράφηκε.' : 'Η διαγραφή απέτυχε.';
        header('Location: tasks.php?date=' . ($_GET['date'] ?? ''));
        exit;
    }
}
