<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN,ROLE_STAFF,ROLE_COMMITTEE]);

$pageTitle = 'AI-Based Automated Email Reminder';
$activeMenu = 'ai_email_reminders';
$extraCss = [appUrl('assets/css/lacms-module-pages.css')];

include __DIR__ . '/../layouts/header.php';
?>
<div class="lacms-app-wrapper">
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="lacms-main-content">

<section class="lacms-module-header">
    <div>
        <div class="lacms-module-eyebrow"><i class="bi bi-stars"></i> Planned Intelligent Automation</div>
        <h1>AI-Based Automated Email Reminder</h1>
        <p>Planned feature for generating and sending AI-assisted reminder emails for upcoming meetings, legislative deadlines, and scheduled activities.</p>
    </div>
    <span class="lacms-planned-pill"><i class="bi bi-clock"></i> Not Implemented Yet</span>
</section>

<section class="lacms-ai-banner">
    <span><i class="bi bi-envelope-paper-heart"></i></span>
    <div>
        <small>Current Phase</small>
        <strong>Navigation and UI Only</strong>
        <p>No AI model request, email sending, reminder engine, or scheduled background job is included in this revision.</p>
    </div>
</section>

<div class="row g-4 mb-4">
    <div class="col-xl-4">
        <section class="lacms-panel h-100">
            <div class="lacms-intelligence-card">
                <i class="bi bi-calendar-event"></i>
                <strong>1. Detect Upcoming Activity</strong>
                <p>Future automation will identify meetings, deadlines, and legislative events approaching their scheduled time.</p>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="lacms-panel h-100">
            <div class="lacms-intelligence-card">
                <i class="bi bi-stars"></i>
                <strong>2. Assist Reminder Content</strong>
                <p>AI can later help prepare concise reminder content using meeting, agenda, deadline, recipient, and schedule information.</p>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="lacms-panel h-100">
            <div class="lacms-intelligence-card">
                <i class="bi bi-envelope-check"></i>
                <strong>3. Send Email Reminder</strong>
                <p>The future notification service can send approved reminders to authorized participants and record delivery status.</p>
            </div>
        </section>
    </div>
</div>

<section class="lacms-panel mb-4">
    <div class="lacms-panel-heading">
        <div><h2><i class="bi bi-sliders"></i> Planned Reminder Configuration</h2><p>Client-demo layout for future reminder settings.</p></div>
    </div>

    <div class="lacms-reminder-preview">
        <div><label>Reminder Type</label><select class="form-select" disabled><option>Upcoming Meeting</option></select></div>
        <div><label>Lead Time</label><select class="form-select" disabled><option>24 hours before</option></select></div>
        <div><label>Recipients</label><input type="text" class="form-control" value="Meeting participants" disabled></div>
        <div><label>Email Status</label><input type="text" class="form-control" value="Waiting for backend integration" disabled></div>
        <div class="wide"><label>AI-Assisted Reminder Preview</label><textarea class="form-control" rows="5" disabled>Future AI-generated reminder content will appear here for review before automated delivery.</textarea></div>
        <div class="wide"><button type="button" class="btn btn-primary" disabled><i class="bi bi-stars"></i> Generate Reminder</button><small>Disabled during the navigation phase.</small></div>
    </div>
</section>

<section class="lacms-panel">
    <div class="lacms-panel-heading"><div><h2><i class="bi bi-shield-check"></i> Planned Controls</h2></div></div>
    <div class="lacms-record-fields two-columns">
        <div><i class="bi bi-check2"></i><span>Only authorized users can configure reminders.</span></div>
        <div><i class="bi bi-check2"></i><span>Reminder activity can later be recorded in Activity Logs.</span></div>
        <div><i class="bi bi-check2"></i><span>Recipient email addresses will come from authorized user records.</span></div>
        <div><i class="bi bi-check2"></i><span>Meeting changes can later trigger revised email notifications.</span></div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
