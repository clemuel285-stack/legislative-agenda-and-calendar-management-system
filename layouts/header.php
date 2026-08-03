<?php
/**
 * LACMS shared page header.
 *
 * Expected optional variables:
 * $pageTitle  = 'Page title';
 * $activeMenu = 'dashboard';
 * $extraCss   = [APP_URL . '/assets/css/example.css'];
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$extraCss = $extraCss ?? [];

$currentUser = currentUser();
$flash = getFlash();

$bootstrapCss = vendorAsset(
    'bootstrap/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Legislative Agenda and Calendar Management System"
    >

    <title>
        <?= e($pageTitle) ?> | <?= e(APP_SHORT_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= e($bootstrapCss) ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= e(appUrl('assets/css/layout.css')) ?>"
    >

    <?php foreach ($extraCss as $css): ?>
        <link
            rel="stylesheet"
            href="<?= e((string)$css) ?>"
        >
    <?php endforeach; ?>
</head>

<body>

<div class="lacms-shell">
    <header class="topbar">
        <div class="topbar-left">
            <button
                type="button"
                class="topbar-menu-button"
                id="sidebarToggle"
                aria-label="Open navigation"
            >
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-page-title">
                <span><?= e(APP_SHORT_NAME) ?></span>
                <h1><?= e($pageTitle) ?></h1>
            </div>
        </div>

        <div class="topbar-right">
            <div class="system-switcher dropdown">
                <button
                    type="button"
                    class="topbar-icon-button dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Switch legislative system"
                >
                    <i class="bi bi-grid"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end system-menu">
                    <div class="system-menu-heading">
                        Legislative Systems
                    </div>

                    <a
                        href="http://localhost/orlms"
                        class="system-menu-item"
                    >
                        <span class="system-menu-icon ordinance">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>

                        <div>
                            <strong>ORLMS</strong>
                            <small>
                                Ordinance and Resolution Lifecycle
                            </small>
                        </div>
                    </a>

                    <a
                        href="<?= e(APP_URL) ?>"
                        class="system-menu-item active"
                    >
                        <span class="system-menu-icon calendar">
                            <i class="bi bi-calendar3"></i>
                        </span>

                        <div>
                            <strong>LACMS</strong>
                            <small>
                                Agenda and Calendar Management
                            </small>
                        </div>
                    </a>

                    <a
                        href="http://localhost/lph"
                        class="system-menu-item"
                    >
                        <span class="system-menu-icon hearing">
                            <i class="bi bi-megaphone"></i>
                        </span>

                        <div>
                            <strong>LPH</strong>
                            <small>
                                Public Hearing and Consultation
                            </small>
                        </div>
                    </a>
                </div>
            </div>

            <button
                type="button"
                class="topbar-icon-button"
                id="notificationButton"
                title="Notifications"
            >
                <i class="bi bi-bell"></i>
                <span class="topbar-notification-dot"></span>
            </button>

            <div class="dropdown">
                <button
                    type="button"
                    class="user-menu-button dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <span class="user-avatar">
                        <?= e(strtoupper(
                            mb_substr(
                                $currentUser['full_name'] ?: 'U',
                                0,
                                1
                            )
                        )) ?>
                    </span>

                    <span class="user-menu-copy">
                        <strong>
                            <?= e(
                                $currentUser['full_name']
                                ?: 'Legislative User'
                            ) ?>
                        </strong>

                        <small>
                            <?= e(
                                orlmsFriendlyRole(
                                    $currentUser['role']
                                )
                            ) ?>
                        </small>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                    <li class="user-dropdown-header">
                        <strong>
                            <?= e(
                                $currentUser['full_name']
                                ?: 'Legislative User'
                            ) ?>
                        </strong>

                        <span>
                            <?= e($currentUser['email']) ?>
                        </span>
                    </li>

                    <li>
                        <a
                            class="dropdown-item"
                            href="<?= e(appUrl('pages/profile.php')) ?>"
                        >
                            <i class="bi bi-person"></i>
                            My Profile
                        </a>
                    </li>

                    <li>
                        <a
                            class="dropdown-item"
                            href="<?= e(appUrl('pages/settings.php')) ?>"
                        >
                            <i class="bi bi-gear"></i>
                            Account Settings
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a
                            class="dropdown-item text-danger"
                            href="<?= e(appUrl('logout.php')) ?>"
                        >
                            <i class="bi bi-box-arrow-right"></i>
                            Log Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>

    <?php if ($flash): ?>
        <div
            class="lacms-flash-message"
            data-flash-type="<?= e($flash['type']) ?>"
            data-flash-message="<?= e($flash['message']) ?>"
        ></div>
    <?php endif; ?>
