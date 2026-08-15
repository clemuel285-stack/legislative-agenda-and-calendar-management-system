<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN,ROLE_STAFF,ROLE_COMMITTEE]);

$pageTitle = 'Meeting Notifications';
$activeMenu = 'meeting_notifications';
$extraCss = [appUrl('assets/css/lacms-module-pages.css')];

include __DIR__ . '/../layouts/header.php';
?>
<div class="lacms-app-wrapper">
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="lacms-main-content">

<section class="lacms-module-header">
    <div>
        <div class="lacms-module-eyebrow"><i class="bi bi-envelope-paper"></i> Legislative Communication</div>
        <h1>Meeting Notifications</h1>
        <p>Planned centralized email notification workspace for meeting schedules, updates, cancellations, venue changes, and coordination messages.</p>
    </div>
    <button type="button" class="btn btn-primary" data-ui-preview><i class="bi bi-envelope-plus"></i> New Notification</button>
</section>

<section class="lacms-context-strip">
    <div><span><i class="bi bi-calendar-event"></i></span><div><small>Meeting Schedule</small><strong>Date, Time & Venue</strong></div></div>
    <div><span><i class="bi bi-people"></i></span><div><small>Recipients</small><strong>Legislators & Staff</strong></div></div>
    <div><span><i class="bi bi-arrow-repeat"></i></span><div><small>Updates</small><strong>Reschedule & Changes</strong></div></div>
    <div><span><i class="bi bi-envelope-check"></i></span><div><small>Delivery</small><strong>Email Notification Ready</strong></div></div>
</section>

<section class="lacms-panel mb-4">
    <div class="lacms-panel-heading"><div><h2><i class="bi bi-grid-1x2"></i> Notification Types</h2></div></div>
    <div class="lacms-feature-grid">
        <?php
        $types = [
            ['bi-calendar-check','New Meeting Schedule','Inform participants of a newly confirmed legislative meeting.'],
            ['bi-arrow-repeat','Schedule Update','Notify participants when date, time, venue, or meeting details change.'],
            ['bi-x-circle','Cancellation Notice','Prepare cancellation communication to affected participants.'],
            ['bi-list-check','Agenda Update','Inform participants when a linked agenda is finalized or revised.'],
            ['bi-alarm','Meeting Reminder','Send future reminders before the scheduled meeting.'],
            ['bi-person-plus','Participant Update','Notify users when participant requirements or invitations change.'],
        ];
        ?>
        <?php foreach ($types as [$icon,$title,$description]): ?>
        <button type="button" class="lacms-feature-card" data-ui-preview>
            <span><i class="bi <?= e($icon) ?>"></i></span>
            <div><strong><?= e($title) ?></strong><small><?= e($description) ?></small></div>
            <i class="bi bi-chevron-right"></i>
        </button>
        <?php endforeach; ?>
    </div>
</section>

<section class="lacms-panel">
    <div class="lacms-panel-heading"><div><h2><i class="bi bi-table"></i> Notification Delivery Workspace</h2><p>Email delivery will be connected during backend development.</p></div></div>
    <div class="lacms-empty-workspace">
        <i class="bi bi-envelope-paper"></i>
        <strong>Meeting notification navigation is complete</strong>
        <span>The future backend will generate recipients, send email notifications, record delivery status, and preserve notification history.</span>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
