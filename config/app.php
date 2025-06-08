<?php
// config/app.php
// 30 λεπτά (1800 δευτερόλεπτα) timeout αδράνειας
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800, '/'); 

session_start();

// αυτόματο φορτωτή PSR-4
spl_autoload_register(function(string $class) {
    // Βασικό namespace-to-path mapping
    // π.χ. "Models\User"  -> "../src/Models/User.php"
    $baseDir = __DIR__ . '/../src/';
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});


// CSRF helpers
function generateCsrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
function verifyCsrfToken(string $token): bool {
    return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

/**
 * Απαγορεύει την πρόσβαση αν δεν είσαι συνδεδεμένος.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Απαγορεύει την πρόσβαση αν δεν είσαι σε ένα από τα επιτρεπόμενα roles.
 */
function requireRole(string ...$roles): void {
    if (empty($_SESSION['role']) || !in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied');
    }
}
