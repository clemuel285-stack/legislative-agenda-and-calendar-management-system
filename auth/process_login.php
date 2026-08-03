<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(appUrl('login.php'));
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('danger', 'Your login form expired. Please try again.');
    redirect(appUrl('login.php'));
}

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    setFlash('warning', 'Enter both your email address and password.');
    redirect(appUrl('login.php'));
}

$stmt = db()->prepare(
    "SELECT
        u.id,
        u.full_name,
        u.email,
        u.password,
        u.role_id,
        u.status,
        r.name AS role_name
     FROM users u
     LEFT JOIN roles r ON r.id = u.role_id
     WHERE LOWER(u.email) = :email
     LIMIT 1"
);

$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'Invalid email address or password.');
    redirect(appUrl('login.php'));
}

$status = strtolower(trim((string)($user['status'] ?? 'active')));

if (!in_array($status, ['active', 'enabled', 'approved', '1'], true)) {
    setFlash('danger', 'This account is inactive or unavailable.');
    redirect(appUrl('login.php'));
}

$storedPassword = (string)($user['password'] ?? '');
$validPassword = password_verify($password, $storedPassword);

if (!$validPassword && $storedPassword !== '' && hash_equals($storedPassword, $password)) {
    $validPassword = true;

    try {
        $upgrade = db()->prepare(
            "UPDATE users SET password = :password WHERE id = :id"
        );
        $upgrade->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => (int)$user['id'],
        ]);
    } catch (Throwable $exception) {
        error_log('[LACMS Password Upgrade] ' . $exception->getMessage());
    }
}

if (!$validPassword) {
    setFlash('danger', 'Invalid email address or password.');
    redirect(appUrl('login.php'));
}

session_regenerate_id(true);
$roleName = (string)($user['role_name'] ?? '');

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['full_name'] = (string)$user['full_name'];
$_SESSION['email'] = (string)$user['email'];
$_SESSION['role_id'] = (int)$user['role_id'];
$_SESSION['role'] = $roleName;
$_SESSION['logged_in'] = true;
$_SESSION['last_activity'] = time();
$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'full_name' => (string)$user['full_name'],
    'email' => (string)$user['email'],
    'role_id' => (int)$user['role_id'],
    'role' => $roleName,
];

unset($_SESSION['auth_expired']);
setFlash('success', 'Welcome to LACMS.');
redirect(appUrl('dashboard.php'));
