<?php
namespace Controllers;

use Models\Car;
use Models\Customer;
use Models\User;

class CarController {
    private \PDO $pdo;
    private Car $carModel;
    private User $userModel;
    private Customer $custModel;

    public function __construct(\PDO $pdo) {
        $this->pdo       = $pdo;
        $this->carModel  = new Car($pdo);
        $this->userModel = new User($pdo);
        $this->custModel = new Customer($pdo);
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

        if ($_SESSION['role'] === 'secretary') {
            $owners = $this->custModel->search();
        } else {
            $owners = [];
        }

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

        // Αποθήκευση όλων των πεδίων για περίπτωση σφάλματος
        $_SESSION['old_car'] = [
            'serial_number'   => $_POST['serial_number']   ?? '',
            'model'           => $_POST['model']           ?? '',
            'brand'           => $_POST['brand']           ?? '',
            'type'            => $_POST['type']            ?? '',
            'drive_type'      => $_POST['drive_type']      ?? '',
            'door_count'      => $_POST['door_count']      ?? '',
            'wheel_count'     => $_POST['wheel_count']     ?? '',
            'production_date' => $_POST['production_date'] ?? '',
            'acquisition_year'=> $_POST['acquisition_year']?? '',
            'owner_id'        => $_POST['owner_id']        ?? ''
        ];

        $type     = $_SESSION['old_car']['type'];
        $doors    = (int)($_SESSION['old_car']['door_count']);
        $wheels   = (int)($_SESSION['old_car']['wheel_count']);

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

        $serial   = trim($_SESSION['old_car']['serial_number']);
        if ($this->carModel->findBySerial($serial)) {
            $_SESSION['error'] = "Το serial number «{$serial}» υπάρχει ήδη.";
            header('Location: create_car.php');
            exit;
        }

        $ownerId = ($_SESSION['role'] === 'secretary')
                 ? (int)($_POST['owner_id'] ?? 0)
                 : $_SESSION['user_id'];

        if (!$this->custModel->findByUserId($ownerId)) {
            $_SESSION['error'] = 'Ο επιλεγμένος ιδιοκτήτης δεν υπάρχει ως πελάτης.';
            header('Location: create_car.php');
            exit;
        }

        $data = [
            'serial_number'   => $serial,
            'model'           => trim($_SESSION['old_car']['model']),
            'brand'           => trim($_SESSION['old_car']['brand']),
            'type'            => $type,
            'drive_type'      => $_SESSION['old_car']['drive_type'],
            'door_count'      => $doors,
            'wheel_count'     => $wheels,
            'production_date' => $_SESSION['old_car']['production_date'] ?: null,
            'acquisition_year'=> $_SESSION['old_car']['acquisition_year'] ?: null,
            'owner_id'        => $ownerId,
        ];

        try {
            $ok = $this->carModel->create($data);
            unset($_SESSION['old_car']);
            $_SESSION['success'] = $ok ? 'Car created.' : 'Creation failed.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

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
        if ($_SESSION['role'] === 'secretary') {
            $owners = $this->custModel->search();
        } else {
            $owners = [];
        }

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

        $serial  = $_POST['serial'] ?? '';
        $ownerId = $_SESSION['role'] === 'secretary'
                 ? (int)($_POST['owner_id'] ?? 0)
                 : $_SESSION['user_id'];

        if (!$this->custModel->findByUserId($ownerId)) {
            $_SESSION['error'] = 'Ο επιλεγμένος ιδιοκτήτης δεν υπάρχει ως πελάτης.';
            header("Location: edit_car.php?serial={$serial}");
            exit;
        }

        $data = [
            'model'           => trim($_POST['model'] ?? ''),
            'brand'           => trim($_POST['brand'] ?? ''),
            'type'            => $_POST['type'] ?? '',
            'drive_type'      => $_POST['drive_type'] ?? '',
            'door_count'      => (int)($_POST['door_count'] ?? 0),
            'wheel_count'     => (int)($_POST['wheel_count'] ?? 0),
            'production_date' => $_POST['production_date'] ?? null,
            'acquisition_year'=> $_POST['acquisition_year'] ?? null,
            'owner_id'        => $ownerId,
        ];

        try {
            $ok = $this->carModel->update($serial, $data);
            $_SESSION['success'] = $ok ? 'Car updated.' : 'No changes.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
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
        try {
            $ok = $this->carModel->delete($serial);
            $_SESSION['success'] = $ok ? 'Car deleted.' : 'Delete failed.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        header('Location: cars.php');
        exit;
    }
}
