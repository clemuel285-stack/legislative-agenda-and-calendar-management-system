<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function startSharedSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

startSharedSession();

$lastActivity = (int)($_SESSION['last_activity'] ?? 0);

if ($lastActivity > 0 && (time() - $lastActivity) > SESSION_IDLE_TIMEOUT) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookie['path'],
            $cookie['domain'],
            $cookie['secure'],
            $cookie['httponly']
        );
    }

    session_destroy();
    session_start();
    $_SESSION['auth_expired'] = true;
}

if (currentUserId() > 0) {
    $_SESSION['last_activity'] = time();
}

function isAuthenticated(): bool
{
    return currentUserId() > 0;
}

function requireLogin(): void
{
    if (isAuthenticated()) return;

    $query = !empty($_SESSION['auth_expired']) ? '?expired=1' : '';
    redirect(appUrl('login.php' . $query));
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    $current = normalizeRole(currentRole());
    $allowed = array_map(
        static fn(mixed $role): string => normalizeRole((string)$role),
        $allowedRoles
    );

    if (in_array($current, $allowed, true)) {
        return;
    }

    http_response_code(403);
    exit(
        '<h1>403 - Access Denied</h1>' .
        '<p>Your account does not have permission to access this page.</p>' .
        '<p><a href="' . e(appUrl('dashboard.php')) . '">Return to dashboard</a></p>'
    );
}
