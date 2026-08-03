<?php
/**
 * LACMS main dashboard.
 *
 * The current phase intentionally uses empty-state counts because the
 * dedicated LACMS database tables will be designed after the navigation
 * and client-ready interfaces are completed.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

$extraCss = [
    appUrl('assets/css/dashboard.css'),
];

$stats = [
    'priorities' => 0,
    'scheduled'  => 0,
    'meetings'   => 0,
    'deadlines'  => 0,
    'sync_items' => 0,
];

$moduleCards = [
    [
        'title' => 'Legislative Priority Setting',
        'description' => 'Rank and manage legislative agenda priorities.',
        'icon' => 'bi-list-stars',
        'url' => appUrl('modules/priorities/index.php'),
    ],
    [
        'title' => 'Calendar Scheduling',
        'description' => 'Build and manage the legislative calendar.',
        'icon' => 'bi-calendar-week',
        'url' => appUrl('modules/calendar/index.php'),
    ],
    [
        'title' => 'Meeting Coordination',
        'description' => 'Coordinate sessions, committees, and participants.',
        'icon' => 'bi-people',
        'url' => appUrl('modules/meetings/index.php'),
    ],
    [
        'title' => 'Deadline Tracking',
        'description' => 'Monitor deadlines, reminders, and overdue items.',
        'icon' => 'bi-alarm',
        'url' => appUrl('modules/deadlines/index.php'),
    ],
    [
        'title' => 'Executive-Legislative Sync',
        'description' => 'Align legislative and executive schedules.',
        'icon' => 'bi-arrow-left-right',
        'url' => appUrl('modules/synchronization/index.php'),
    ],
];

include __DIR__ . '/layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <main class="main-content">
        <section class="dashboard-page-header">
            <div>
                <div class="dashboard-eyebrow">
                    <i class="bi bi-calendar3"></i>
                    Legislative Agenda Overview
                </div>

                <h1>
                    Welcome to <?= e(APP_SHORT_NAME) ?>
                </h1>

                <p>
                    Manage legislative priorities, calendars, meetings,
                    deadlines, and executive-legislative coordination from
                    one shared scheduling workspace.
                </p>
            </div>

            <div class="dashboard-date">
                <i class="bi bi-calendar-event"></i>

                <div>
                    <strong><?= e(date('l')) ?></strong>
                    <span><?= e(date('F j, Y')) ?></span>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="dashboard-summary-card">
                    <span class="dashboard-summary-icon">
                        <i class="bi bi-list-stars"></i>
                    </span>

                    <strong><?= $stats['priorities'] ?></strong>
                    <span>Active Priorities</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="dashboard-summary-card">
                    <span class="dashboard-summary-icon">
                        <i class="bi bi-calendar-check"></i>
                    </span>

                    <strong><?= $stats['scheduled'] ?></strong>
                    <span>Scheduled Activities</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="dashboard-summary-card">
                    <span class="dashboard-summary-icon">
                        <i class="bi bi-people"></i>
                    </span>

                    <strong><?= $stats['meetings'] ?></strong>
                    <span>Upcoming Meetings</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="dashboard-summary-card">
                    <span class="dashboard-summary-icon">
                        <i class="bi bi-alarm"></i>
                    </span>

                    <strong><?= $stats['deadlines'] ?></strong>
                    <span>Open Deadlines</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="dashboard-summary-card">
                    <span class="dashboard-summary-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </span>

                    <strong><?= $stats['sync_items'] ?></strong>
                    <span>Synchronization Items</span>
                </div>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <section class="dashboard-panel h-100">
                    <div class="dashboard-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-calendar-week"></i>
                                Legislative Calendar
                            </h2>

                            <p>
                                Upcoming sessions, meetings, hearings,
                                and coordinated activities.
                            </p>
                        </div>

                        <a
                            href="<?= e(appUrl(
                                'modules/calendar/index.php'
                            )) ?>"
                            class="btn btn-sm btn-outline-light"
                        >
                            Open Calendar
                        </a>
                    </div>

                    <div class="dashboard-calendar-empty">
                        <i class="bi bi-calendar2-plus"></i>

                        <strong>No calendar events scheduled yet</strong>

                        <span>
                            Scheduled legislative sessions and activities
                            will appear here after the Calendar Scheduling
                            Module is connected.
                        </span>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="dashboard-panel h-100">
                    <div class="dashboard-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-grid"></i>
                                Module Shortcuts
                            </h2>

                            <p>
                                Open each agenda and calendar workflow.
                            </p>
                        </div>
                    </div>

                    <div class="dashboard-module-grid">
                        <?php foreach ($moduleCards as $module): ?>
                            <a
                                href="<?= e($module['url']) ?>"
                                class="dashboard-module-card"
                            >
                                <span class="dashboard-module-icon">
                                    <i class="bi <?= e($module['icon']) ?>"></i>
                                </span>

                                <div>
                                    <strong>
                                        <?= e($module['title']) ?>
                                    </strong>

                                    <small>
                                        <?= e($module['description']) ?>
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="dashboard-panel h-100">
                    <div class="dashboard-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-alarm"></i>
                                Deadline Monitoring
                            </h2>

                            <p>
                                Due dates, reminders, and overdue items.
                            </p>
                        </div>

                        <a
                            href="<?= e(appUrl(
                                'modules/deadlines/index.php'
                            )) ?>"
                            class="btn btn-sm btn-outline-light"
                        >
                            View Deadlines
                        </a>
                    </div>

                    <div class="dashboard-deadline-empty">
                        <i class="bi bi-hourglass-split"></i>

                        <strong>No deadlines recorded yet</strong>

                        <span>
                            Deadline cards and reminder status will appear
                            here after the Deadline Tracking Module is built.
                        </span>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="dashboard-panel h-100">
                    <div class="dashboard-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clock-history"></i>
                                Recent System Activity
                            </h2>

                            <p>
                                Latest LACMS navigation and setup events.
                            </p>
                        </div>
                    </div>

                    <div class="dashboard-activity-list">
                        <div class="dashboard-activity-item">
                            <span class="dashboard-activity-icon">
                                <i class="bi bi-check2-circle"></i>
                            </span>

                            <div>
                                <strong>
                                    Shared authentication configured
                                </strong>

                                <small>
                                    LACMS uses the legislative shared session.
                                </small>
                            </div>
                        </div>

                        <div class="dashboard-activity-item">
                            <span class="dashboard-activity-icon">
                                <i class="bi bi-layout-sidebar"></i>
                            </span>

                            <div>
                                <strong>
                                    Navigation layout connected
                                </strong>

                                <small>
                                    Header, sidebar, footer, and mobile menu
                                    are now active.
                                </small>
                            </div>
                        </div>

                        <div class="dashboard-activity-item">
                            <span class="dashboard-activity-icon">
                                <i class="bi bi-database-check"></i>
                            </span>

                            <div>
                                <strong>
                                    Shared database available
                                </strong>

                                <small>
                                    Connected to <?= e(DB_NAME) ?>.
                                </small>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
