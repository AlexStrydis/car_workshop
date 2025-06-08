<?php
namespace Controllers;

use Models\Car;
use Models\User;

class CarController {
    private \PDO $pdo;
    private Car $carModel;
    private User $userModel;

    public function __construct(\PDO $pdo) {
        $this->pdo       = $pdo;
        $this->carModel  = new Car($pdo);
        $this->userModel = new User($pdo);
    }

    /**
     * Λειτουργία: List cars — 
     *   - πελάτες βλέπουν μόνο τα δικά τους,
     *   - secretary & mechanic βλέπουν όλα.
     * Μετατρέπει owner_id σε owner_name.
     * Προσθέτει pagination (limit/offset).
     */
    public function list(): void {
        requireLogin();

        $criteria = [];
        if ($_SESSION['role'] === 'customer') {
            // Ο πελάτης βλέπει μόνο δικά του
            $criteria['owner_id'] = $_SESSION['user_id'];
        } else {
            // Οι άλλοι πρέπει να είναι secretary ή mechanic
            requireRole('secretary','mechanic');
        }

        // Φόρτωση φίλτρων GET (π.χ. για future search)
        if (!empty($_GET['serial'])) {
            $criteria['serial'] = trim($_GET['serial']);
        }
        if (!empty($_GET['model'])) {
            $criteria['model'] = trim($_GET['model']);
        }
        if (!empty($_GET['brand'])) {
            $criteria['brand'] = trim($_GET['brand']);
        }

        // Pagination Settings
        $page   = isset($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] > 0
                  ? (int)$_GET['page']
                  : 1;
        $limit  = 10; // Αυτοκίνητα ανά σελίδα
        $offset = ($page - 1) * $limit;

        // Πραγματική Λίστα από το μοντέλο (με limit/offset)
        $cars = $this->carModel->search($criteria, $limit, $offset);

        // Υπολογισμός συνολικού πλήθους για να βρούμε το πόσες σελίδες θα φτιάξουμε
        $totalCount = $this->carModel->countAll($criteria);
        $totalPages = (int)ceil($totalCount / $limit);

        // Μετατροπή owner_id → owner_name
        foreach ($cars as &$c) {
            $u = $this->userModel->findById($c['owner_id']);
            $c['owner_name'] = $u
                ? $u['last_name'].' '.$u['first_name']
                : 'Unknown';
        }
        unset($c);

        // Δημιουργούμε token CSRF (για φόρμες διαγραφής)
        $token = generateCsrfToken();

        // Φορτώνουμε το view
        include __DIR__ . '/../../Views/cars.php';
    }

    /**
     * Φόρμα για νέο car
     */
    public function createForm(): void {
        requireLogin();
        requireRole('secretary','customer');

        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/car_form.php';
    }

    /**
     * Δημιουργία νέου car με back-end validation limits
     */
    public function create(): void {
        requireLogin();
        requireRole('secretary','customer');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        // Βασικά πεδία
        $type     = $_POST['type'] ?? '';
        $doors    = (int)($_POST['door_count']  ?? 0);
        $wheels   = (int)($_POST['wheel_count'] ?? 0);

        // Ορισμός ορίων ανά τύπο
        $limits = [
            'passenger'=>['doors_max'=>7,'wheels_min'=>4,'wheels_max'=>4],
            'truck'    =>['doors_max'=>4,'wheels_min'=>4,'wheels_max'=>18],
            'bus'      =>['doors_max'=>3,'wheels_min'=>4,'wheels_max'=>8],
        ];

        if (!isset($limits[$type])) {
            $_SESSION['error'] = 'Άγνωστος τύπος οχήματος.';
            header('Location: create_car.php');
            exit;
        }

        $cfg = $limits[$type];
        if ($doors < 1 || $doors > $cfg['doors_max']) {
            $_SESSION['error'] = "Για $type, max {$cfg['doors_max']} πόρτες επιτρέπονται.";
            header('Location: create_car.php');
            exit;
        }
        if ($wheels < $cfg['wheels_min'] || $wheels > $cfg['wheels_max']) {
            $_SESSION['error'] = "Για $type, επιτρέπονται μεταξύ {$cfg['wheels_min']} και {$cfg['wheels_max']} ρόδες.";
            header('Location: create_car.php');
            exit;
        }

        // Περνάμε στο μοντέλο
        $data = [
            'serial_number'   => trim($_POST['serial_number'] ?? ''),
            'model'           => trim($_POST['model'] ?? ''),
            'brand'           => trim($_POST['brand'] ?? ''),
            'type'            => $type,
            'drive_type'      => $_POST['drive_type'] ?? '',
            'door_count'      => $doors,
            'wheel_count'     => $wheels,
            'production_date' => $_POST['production_date'] ?? null,
            'acquisition_year'=> $_POST['acquisition_year'] ?? null,
            'owner_id'        => $_SESSION['user_id']
        ];

        $ok = $this->carModel->create($data);
        $_SESSION['success'] = $ok ? 'Car created.' : 'Creation failed.';
        header('Location: cars.php');
        exit;
    }

    /**
     * Φόρμα edit
     */
    public function editForm(): void {
        requireLogin();
        requireRole('secretary','customer');

        $serial = $_GET['serial'] ?? '';
        $car = $this->carModel->findBySerial($serial);
        if (!$car) {
            http_response_code(404);
            exit('Not found');
        }
        $token = generateCsrfToken();
        include __DIR__ . '/../../Views/car_form.php';
    }

    /**
     * Update
     */
    public function edit(): void {
        requireLogin();
        requireRole('secretary','customer');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $serial = $_POST['serial'] ?? '';
        $data = [
            'model'           => trim($_POST['model'] ?? ''),
            'brand'           => trim($_POST['brand'] ?? ''),
            'type'            => $_POST['type'] ?? '',
            'drive_type'      => $_POST['drive_type'] ?? '',
            'door_count'      => (int)($_POST['door_count'] ?? 0),
            'wheel_count'     => (int)($_POST['wheel_count'] ?? 0),
            'production_date' => $_POST['production_date'] ?? null,
            'acquisition_year'=> $_POST['acquisition_year'] ?? null
        ];
        $ok = $this->carModel->update($serial, $data);
        $_SESSION['success'] = $ok ? 'Car updated.' : 'No changes.';
        header('Location: cars.php');
        exit;
    }

    /**
     * Delete
     */
    public function delete(): void {
        requireLogin();
        requireRole('secretary','customer');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $serial = $_POST['serial'] ?? '';
        $ok = $this->carModel->delete($serial);
        $_SESSION['success'] = $ok ? 'Car deleted.' : 'Delete failed.';
        header('Location: cars.php');
        exit;
    }
}
