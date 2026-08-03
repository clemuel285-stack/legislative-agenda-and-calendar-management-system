<?php
/**
 * Shared utility functions for LACMS.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Escape output for HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Build a URL within LACMS.
 */
function appUrl(string $path = ''): string
{
    $path = trim($path);

    if ($path === '') {
        return APP_URL;
    }

    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirect and stop execution.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Read the current authenticated user.
 */
function currentUser(): array
{
    $nestedUser = $_SESSION['user'] ?? [];

    if (!is_array($nestedUser)) {
        $nestedUser = [];
    }

    return [
        'id' => (int)(
            $_SESSION['user_id']
            ?? $nestedUser['id']
            ?? 0
        ),
        'full_name' => (string)(
            $_SESSION['full_name']
            ?? $nestedUser['full_name']
            ?? ''
        ),
        'email' => (string)(
            $_SESSION['email']
            ?? $nestedUser['email']
            ?? ''
        ),
        'role_id' => (int)(
            $_SESSION['role_id']
            ?? $nestedUser['role_id']
            ?? 0
        ),
        'role' => (string)(
            $_SESSION['role']
            ?? $nestedUser['role']
            ?? ''
        ),
    ];
}

function currentUserId(): int
{
    return currentUser()['id'];
}

function currentRole(): string
{
    return currentUser()['role'];
}

function isAdmin(): bool
{
    return normalizeRole(currentRole()) === normalizeRole(ROLE_ADMIN);
}

/**
 * Normalize roles such as ROLE_ADMIN, admin, and Administrator.
 */
function normalizeRole(string $role): string
{
    $role = strtoupper(trim($role));
    $role = preg_replace('/^ROLE_/', '', $role) ?? $role;
    $role = str_replace([' ', '-'], '_', $role);

    if (in_array($role, ['ADMINISTRATOR', 'SYSTEM_ADMIN'], true)) {
        return 'ADMIN';
    }

    if (in_array($role, ['LEGISLATIVE_STAFF', 'SECRETARIAT'], true)) {
        return 'STAFF';
    }

    if (in_array($role, ['COMMITTEE_MEMBER', 'COMMITTEE_CHAIR'], true)) {
        return 'COMMITTEE';
    }

    return $role;
}

/**
 * Return a user-friendly role label for the layout.
 *
 * Examples:
 * ADMIN                -> System Administrator
 * ROLE_ADMIN           -> System Administrator
 * STAFF                -> Legislative Staff
 * COMMITTEE            -> Committee Member
 * EXECUTIVE_LIAISON    -> Executive Liaison
 */
function orlmsFriendlyRole(string $role): string
{
    $normalized = normalizeRole($role);

    $labels = [
        'ADMIN'     => 'System Administrator',
        'STAFF'     => 'Legislative Staff',
        'COMMITTEE' => 'Committee Member',
    ];

    if (isset($labels[$normalized])) {
        return $labels[$normalized];
    }

    if ($normalized === '') {
        return 'Legislative User';
    }

    return ucwords(
        strtolower(
            str_replace('_', ' ', $normalized)
        )
    );
}

/**
 * Create or return the current CSRF token.
 */
function csrfToken(): string
{
    if (
        empty($_SESSION['csrf_token']) ||
        !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 */
function verifyCsrfToken(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    return is_string($token)
        && is_string($sessionToken)
        && $token !== ''
        && hash_equals($sessionToken, $token);
}

/**
 * Store a one-time message.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and remove the current one-time message.
 */
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function formatDate(?string $value): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);

    return $timestamp === false
        ? (string)$value
        : date('M d, Y', $timestamp);
}

function formatDateTime(?string $value): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime($value);

    return $timestamp === false
        ? (string)$value
        : date('M d, Y h:i A', $timestamp);
}

/**
 * Prefer a local vendor asset, otherwise use its CDN fallback.
 */
function vendorAsset(
    string $relativePath,
    string $fallbackUrl
): string {
    $relativePath = ltrim($relativePath, '/');
    $localFile = APP_ROOT . '/assets/vendor/' . $relativePath;

    return is_file($localFile)
        ? appUrl('assets/vendor/' . $relativePath)
        : $fallbackUrl;
}
