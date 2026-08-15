<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN,ROLE_STAFF,ROLE_COMMITTEE]);

$pageTitle = 'Search & Filter';
$activeMenu = 'search';
$extraCss = [appUrl('assets/css/lacms-module-pages.css')];

include __DIR__ . '/../layouts/header.php';
?>
<div class="lacms-app-wrapper">
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="lacms-main-content">

<section class="lacms-module-header">
    <div>
        <div class="lacms-module-eyebrow"><i class="bi bi-search"></i> Centralized Record Retrieval</div>
        <h1>Search and Filter Functions</h1>
        <p>Unified navigation for quickly locating agendas, calendar events, meetings, deadlines, reminders, and related legislative coordination records.</p>
    </div>
</section>

<section class="lacms-panel mb-4">
    <div class="lacms-panel-heading"><div><h2><i class="bi bi-funnel"></i> Search Filters</h2><p>Client-demo form only.</p></div></div>
    <div class="lacms-search-preview">
        <div class="wide"><label>Keyword</label><input type="text" class="form-control" placeholder="Search title, agenda, meeting, deadline..."></div>
        <div><label>Record Type</label><select class="form-select"><option>All Records</option><option>Legislative Agenda</option><option>Calendar Event</option><option>Meeting</option><option>Deadline</option></select></div>
        <div><label>Status</label><select class="form-select"><option>All Statuses</option><option>Draft</option><option>Scheduled</option><option>Confirmed</option><option>Completed</option><option>Cancelled</option></select></div>
        <div><label>Date From</label><input type="date" class="form-control"></div>
        <div><label>Date To</label><input type="date" class="form-control"></div>
        <div><label>Committee / Office</label><input type="text" class="form-control" placeholder="Committee or office"></div>
        <div><label>Responsible User</label><input type="text" class="form-control" placeholder="Assigned user"></div>
        <div class="wide actions"><button type="button" class="btn btn-primary" data-ui-preview><i class="bi bi-search"></i> Search Records</button><button type="button" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Clear Filters</button></div>
    </div>
</section>

<section class="lacms-panel">
    <div class="lacms-panel-heading"><div><h2><i class="bi bi-table"></i> Search Results</h2><p>Results will be loaded from the database later.</p></div></div>
    <div class="lacms-empty-workspace">
        <i class="bi bi-search"></i>
        <strong>Search navigation is ready</strong>
        <span>Database-backed searching, filtering, pagination, and export will be connected during the backend phase.</span>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
