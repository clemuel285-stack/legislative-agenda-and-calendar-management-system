<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireRole([ROLE_ADMIN,ROLE_STAFF,ROLE_COMMITTEE]);

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$extraCss = [appUrl('assets/css/lacms-dashboard-v2.css')];

$modules = [
    ['01','Legislative Agenda Management','Create, organize, update, and manage legislative agendas in one centralized workspace.','bi-list-check','modules/agendas/index.php'],
    ['02','Calendar Scheduling','Schedule meetings, events, and legislative activities while reducing conflicts.','bi-calendar-week','modules/calendar/index.php'],
    ['03','Meeting Coordination','Plan meetings, participants, agendas, schedules, and communication.','bi-people','modules/meetings/index.php'],
    ['04','Deadline Tracking','Monitor legislative deadlines, assignments, due dates, reminders, and overdue tasks.','bi-alarm','modules/deadlines/index.php'],
];

include __DIR__ . '/layouts/header.php';
?>
<div class="lacms-app-wrapper">
<?php include __DIR__ . '/layouts/sidebar.php'; ?>
<main class="lacms-main-content">

<section class="lacms-dashboard-hero">
    <div>
        <div class="lacms-dashboard-eyebrow"><i class="bi bi-building"></i> Local Government Unit of Manila</div>
        <h1>Legislative Agenda and Calendar Management System</h1>
        <p>A modern, intelligent, and centralized legislative management system designed to improve the organization of agendas, schedules, meetings, deadlines, communication, monitoring, and decision support through automation and future artificial intelligence.</p>
        <div class="lacms-dashboard-hero-actions">
            <a href="<?= e(appUrl('modules/agendas/index.php')) ?>" class="btn btn-warning"><i class="bi bi-list-check"></i> Open Legislative Agendas</a>
            <a href="<?= e(appUrl('modules/calendar/index.php')) ?>" class="btn btn-outline-light"><i class="bi bi-calendar3"></i> Open Master Calendar</a>
        </div>
    </div>

    <div class="lacms-intelligence-overview">
        <span><i class="bi bi-stars"></i></span>
        <div>
            <small>Planned Intelligent Feature</small>
            <strong>AI-Assisted Email Reminders</strong>
            <p>Future automation will support upcoming meetings, deadlines, and scheduled legislative activities.</p>
        </div>
    </div>
</section>

<section class="lacms-problem-strip">
    <div><i class="bi bi-list-check"></i><span><small>Disorganized Agendas</small><strong>Central Agenda Management</strong></span></div>
    <div><i class="bi bi-alarm"></i><span><small>Missed Deadlines</small><strong>Automated Reminder Ready</strong></span></div>
    <div><i class="bi bi-envelope-paper"></i><span><small>Inefficient Communication</small><strong>Email Notifications</strong></span></div>
    <div><i class="bi bi-calendar-x"></i><span><small>Scheduling Conflicts</small><strong>Central Calendar Coordination</strong></span></div>
    <div><i class="bi bi-eye"></i><span><small>No Central Monitoring</small><strong>Dashboard & Reports</strong></span></div>
</section>

<section class="lacms-dashboard-panel mb-4">
    <div class="lacms-dashboard-panel-heading">
        <div><h2><i class="bi bi-grid-3x3-gap"></i> Core Legislative Coordination Modules</h2><p>Revised based on the current project vision and scope.</p></div>
    </div>

    <div class="lacms-dashboard-module-grid">
        <?php foreach ($modules as [$number,$title,$description,$icon,$href]): ?>
        <a href="<?= e(appUrl($href)) ?>" class="lacms-dashboard-module-card">
            <span class="module-number"><?= e($number) ?></span>
            <span class="module-icon"><i class="bi <?= e($icon) ?>"></i></span>
            <div><strong><?= e($title) ?></strong><small><?= e($description) ?></small></div>
            <i class="bi bi-arrow-right"></i>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <section class="lacms-dashboard-panel h-100">
            <div class="lacms-dashboard-panel-heading">
                <div><h2><i class="bi bi-bell"></i> Automation & Communication</h2><p>Reminder and meeting-notification features in scope.</p></div>
            </div>

            <div class="lacms-dashboard-feature-list">
                <a href="<?= e(appUrl('pages/ai_email_reminders.php')) ?>">
                    <i class="bi bi-stars"></i>
                    <span><strong>AI-Based Automated Email Reminder</strong><small>Planned AI-assisted reminders for meetings, deadlines, and scheduled activities.</small></span>
                    <em>PLANNED</em>
                </a>

                <a href="<?= e(appUrl('pages/meeting_notifications.php')) ?>">
                    <i class="bi bi-envelope-paper"></i>
                    <span><strong>Meeting Notifications</strong><small>Email communication for schedules, updates, changes, and cancellations.</small></span>
                    <i class="bi bi-arrow-right"></i>
                </a>

                <a href="<?= e(appUrl('pages/search.php')) ?>">
                    <i class="bi bi-search"></i>
                    <span><strong>Search & Filter</strong><small>Locate agendas, meetings, events, deadlines, and other records quickly.</small></span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="lacms-dashboard-panel h-100">
            <div class="lacms-dashboard-panel-heading">
                <div><h2><i class="bi bi-shield-lock"></i> Security & Accountability</h2><p>In-scope administrative controls.</p></div>
            </div>

            <div class="lacms-security-list">
                <div><i class="bi bi-person-lock"></i><span><strong>User Login & Authentication</strong><small>Authorized access to the platform.</small></span></div>
                <div><i class="bi bi-person-gear"></i><span><strong>Role-Based User Management</strong><small>Permissions based on user role.</small></span></div>
                <div><i class="bi bi-clock-history"></i><span><strong>Activity Logs</strong><small>Audit trail for accountability.</small></span></div>
                <div><i class="bi bi-database-lock"></i><span><strong>Secure Database Management</strong><small>Protected centralized legislative data.</small></span></div>
            </div>
        </section>
    </div>
</div>

<section class="lacms-dashboard-panel">
    <div class="lacms-dashboard-panel-heading">
        <div><h2><i class="bi bi-check2-square"></i> Navigation Phase Status</h2><p>Revised scope implementation checklist.</p></div>
    </div>

    <div class="lacms-status-grid">
        <div class="done"><i class="bi bi-check-circle-fill"></i><span><strong>Legislative Agenda Management</strong><small>Revised module navigation ready</small></span></div>
        <div class="done"><i class="bi bi-check-circle-fill"></i><span><strong>Calendar & Meeting Coordination</strong><small>Scheduling and coordination pages ready</small></span></div>
        <div class="done"><i class="bi bi-check-circle-fill"></i><span><strong>Deadline Tracking</strong><small>Due-soon and overdue navigation ready</small></span></div>
        <div class="done"><i class="bi bi-check-circle-fill"></i><span><strong>Reports, Search & Activity Logs</strong><small>Administrative navigation represented</small></span></div>
        <div class="planned"><i class="bi bi-clock-fill"></i><span><strong>AI Email Reminder Backend</strong><small>Planned for future development</small></span></div>
        <div class="planned"><i class="bi bi-clock-fill"></i><span><strong>Database CRUD & Email Delivery</strong><small>Intentionally postponed</small></span></div>
    </div>
</section>

<?php include __DIR__ . '/layouts/footer.php'; ?>
