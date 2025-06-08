<?php
namespace Controllers;

use Models\Appointment;
use Models\Task;

class MechanicController {
    private Appointment $apptM;
    private Task        $taskM;

    public function __construct(\PDO $pdo) {
        $this->apptM = new Appointment($pdo);
        $this->taskM = new Task($pdo);
    }

    /**
     *  Προβολή Dashboard μηχανικού: σημερινά ραντεβού & εργασίες
     */
    public function dashboard(): void {
        requireLogin();
        requireRole('mechanic');

        // Ο μηχανικός
        $mechanicId = $_SESSION['user_id'];
        // Σημερινή ημερομηνία σε format YYYY-MM-DD
        $today = date('Y-m-d');

        // Φέρνουμε όσα ραντεβού έχει ο μηχανικός σήμερα
        $appts = $this->apptM->getForMechanic($mechanicId, $today);

        // Φέρνουμε όσες εργασίες έχουν completion_time την ίδια μέρα
        $tasksToday = $this->taskM->getByMechanic($mechanicId, $today);

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/mechanic_dashboard.php';
    }
}
