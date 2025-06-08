<?php
namespace Controllers;

use Models\User;

class UsersController {
    private User $userModel;

    public function __construct(\PDO $pdo) {
        $this->userModel = new User($pdo);
    }

    /**
     * 1. List & Search users
     */
    public function list(): void {
        requireLogin();
        requireRole('secretary');

        // Διαβάζουμε φίλτρα από GET
        $criteria = [];
        if (!empty($_GET['username'])) {
            $criteria['username'] = trim($_GET['username']);
        }
        if (!empty($_GET['last_name'])) {
            $criteria['last_name'] = trim($_GET['last_name']);
        }
        if (!empty($_GET['identity_number'])) {
            $criteria['identity_number'] = trim($_GET['identity_number']);
        }
        if (!empty($_GET['role'])) {
            $criteria['role'] = trim($_GET['role']);
        }

        // Pending mode
        $mode = $_GET['mode'] ?? 'all';
        if ($mode === 'pending') {
            $users = $this->userModel->getPending();
        } else {
            // Η search() παίρνει username, last_name, role
            $users = $this->userModel->search($criteria);
        }

        include __DIR__ . '/../../views/users.php';
    }

    /**
     * 2. Activate a pending user
     */
    public function activate(): void {
        requireLogin();
        requireRole('secretary');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['user_id'] ?? 0);
        if ($id > 0) {
            $ok = $this->userModel->activate($id);
            $_SESSION['success'] = $ok
                ? "User #{$id} ενεργοποιήθηκε."
                : "Activation failed.";
        }
        header('Location: users.php?mode=pending');
        exit;
    }

    /**
     * 3. Show edit form
     */
    public function editForm(): void {
        requireLogin();
        requireRole('secretary');

        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) {
            http_response_code(404);
            exit('User not found');
        }
        $token = generateCsrfToken();
        include __DIR__ . '/../../views/user_form.php';
    }

    /**
     * 4. Handle edit POST
     */
    public function edit(): void {
        requireLogin();
        requireRole('secretary');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'username'        => trim($_POST['username'] ?? ''),
            'first_name'      => trim($_POST['first_name'] ?? ''),
            'last_name'       => trim($_POST['last_name'] ?? ''),
            'identity_number' => trim($_POST['identity_number'] ?? ''),
            'role'            => $_POST['role'] ?? '',
            'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        ];

        $ok = $this->userModel->update($id, $data);
        $_SESSION['success'] = $ok ? 'User updated.' : 'No changes.';
        header('Location: users.php');
        exit;
    }

    /**
     * 5. Delete user
     */
    public function delete(): void {
        requireLogin();
        requireRole('secretary');

        if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
            http_response_code(400);
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['user_id'] ?? 0);
        if ($id > 0) {
            $ok = $this->userModel->delete($id);
            $_SESSION['success'] = $ok ? 'User deleted.' : 'Delete failed.';
        }
        header('Location: users.php');
        exit;
    }
}
