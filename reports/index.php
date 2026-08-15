<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN,ROLE_STAFF,ROLE_COMMITTEE]);

$pageTitle = 'Dashboard & Reports';
$activeMenu = 'reports';
$extraCss = [appUrl('assets/css/lacms-module-pages.css')];

include __DIR__ . '/../layouts/header.php';
?>
<div class="lacms-app-wrapper">
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="lacms-main-content">

<section class="lacms-module-header">
    <div>
        <div class="lacms-module-eyebrow"><i class="bi bi-bar-chart-line"></i> Monitoring & Decision Support</div>
        <h1>Dashboard and Reports</h1>
        <p>Planned reporting workspace for summarized agenda, calendar, meeting, deadline, reminder, notification, and activity information.</p>
    </div>
    <button type="button" class="btn btn-primary" data-ui-preview><i class="bi bi-file-earmark-bar-graph"></i> Generate Report</button>
</section>

<section class="row g-3 mb-4">
<?php $cards = [
    ['0','Active Agendas','bi-list-check'],
    ['0','Upcoming Meetings','bi-calendar-event'],
    ['0','Due Soon','bi-alarm'],
    ['0','Overdue','bi-exclamation-triangle'],
    ['0','Notifications','bi-envelope-paper'],
    ['0','Completed Activities','bi-check2-circle'],
]; ?>
<?php foreach ($cards as [$value,$label,$icon]): ?>
<div class="col-sm-6 col-xl">
    <div class="lacms-report-summary"><i class="bi <?= e($icon) ?>"></i><strong><?= e($value) ?></strong><span><?= e($label) ?></span></div>
</div>
<?php endforeach; ?>
</section>

<div class="row g-4">
    <div class="col-xl-7">
        <section class="lacms-panel h-100">
            <div class="lacms-panel-heading"><div><h2><i class="bi bi-file-earmark-bar-graph"></i> Report Categories</h2></div></div>
            <div class="lacms-feature-grid">
                <?php
                $reports = [
                    ['bi-list-check','Legislative Agenda Report','Agenda status, meeting linkage, committees, and scheduled dates.'],
                    ['bi-calendar-week','Calendar Activity Report','Meetings, events, schedules, changes, and calendar usage.'],
                    ['bi-people','Meeting Coordination Report','Meeting schedules, participants, statuses, and updates.'],
                    ['bi-alarm','Deadline Compliance Report','Due soon, overdue, completed, and assigned deadline information.'],
                    ['bi-envelope-paper','Notification Report','Future reminder and meeting notification delivery information.'],
                    ['bi-clock-history','Activity Audit Report','Future audit trail of significant user and workflow actions.'],
                ];
                ?>
                <?php foreach ($reports as [$icon,$title,$description]): ?>
                <button type="button" class="lacms-feature-card" data-ui-preview>
                    <span><i class="bi <?= e($icon) ?>"></i></span>
                    <div><strong><?= e($title) ?></strong><small><?= e($description) ?></small></div>
                    <i class="bi bi-chevron-right"></i>
                </button>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <div class="col-xl-5">
        <section class="lacms-panel h-100">
            <div class="lacms-panel-heading"><div><h2><i class="bi bi-shield-lock"></i> Secure Data Management</h2></div></div>
            <div class="lacms-record-fields">
                <div><i class="bi bi-check2"></i><span>Authorized user authentication</span></div>
                <div><i class="bi bi-check2"></i><span>Role-based access control</span></div>
                <div><i class="bi bi-check2"></i><span>Centralized database storage</span></div>
                <div><i class="bi bi-check2"></i><span>Activity logging and audit trail</span></div>
                <div><i class="bi bi-check2"></i><span>Protected legislative coordination records</span></div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
