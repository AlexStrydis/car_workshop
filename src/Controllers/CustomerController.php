<?php
namespace Controllers;

use Models\Car;
use Models\Appointment;

class CustomerController {
    private Car         $carM;
    private Appointment $apptM;
    private \PDO        $pdo;

    public function __construct(\PDO $pdo) {
        $this->carM  = new Car($pdo);
        $this->apptM = new Appointment($pdo);
        $this->pdo   = $pdo;
    }

    /**
     *  Προβολή Dashboard Πελάτη: τα οχήματα του και τα επερχόμενα ραντεβού του.
     */
    public function dashboard(): void {
        requireLogin();
        requireRole('customer');

        $customerId = $_SESSION['user_id'];
        $today      = date('Y-m-d');

        // Φέρνουμε τα αυτοκίνητα που έχει ο πελάτης
        $cars = $this->carM->search(['owner_id' => $customerId]);

        // Φέρνουμε τα ραντεβού από σήμερα και μετά
        $appts = $this->apptM->search([
            'customer_id' => $customerId,
            'date_from'   => $today
        ]);

        $userModel = new \Models\User($this->pdo);
        $user = $userModel->findById($customerId);
        $username = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Χρήστης';

        $token = generateCsrfToken();
        $pdo = $this->pdo;
        include __DIR__ . '/../../Views/customer_dashboard.php';
    }
}
