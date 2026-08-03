<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Calendar Scheduling';
$activeMenu = 'calendar';
$extraCss   = [appUrl('assets/css/calendar.css')];
$extraJs    = [appUrl('assets/js/calendar.js')];

$pdo = db();

function lacmsCalendarSafeQuery(PDO $pdo, string $sql): array
{
    try {
        return $pdo->query($sql)->fetchAll();
    } catch (Throwable $exception) {
        error_log('[LACMS Calendar] ' . $exception->getMessage());
        return [];
    }
}

$offices = lacmsCalendarSafeQuery(
    $pdo,
    "SELECT id, name FROM offices ORDER BY name"
);

$committees = lacmsCalendarSafeQuery(
    $pdo,
    "SELECT id, name FROM committees ORDER BY name"
);

$candidateRows = lacmsCalendarSafeQuery(
    $pdo,
    "SELECT
        li.id,
        li.reference_number,
        li.title,
        li.current_status,
        li.priority_level,
        li.updated_at,
        lit.name AS item_type_name,
        lit.code AS item_type_code,
        o.name AS originating_office
     FROM legislative_items li
     INNER JOIN legislative_item_types lit
        ON lit.id = li.item_type_id
     LEFT JOIN offices o
        ON o.id = li.originating_office_id
     WHERE li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')
     ORDER BY
        FIELD(li.priority_level, 'Urgent', 'High', 'Normal', 'Low'),
        li.updated_at DESC
     LIMIT 300"
);

$urgentCount = 0;
$highCount = 0;
$scheduleReadyCount = 0;

$scheduleReadyStatuses = [
    'Submitted',
    'Under Review',
    'Committee Endorsed',
    'For Approval',
    'Approved',
    'Enacted',
    'For Publication',
    'Published',
    'Under Implementation',
    'Implemented',
];

foreach ($candidateRows as $candidate) {
    if ($candidate['priority_level'] === 'Urgent') {
        $urgentCount++;
    }

    if ($candidate['priority_level'] === 'High') {
        $highCount++;
    }

    if (in_array($candidate['current_status'], $scheduleReadyStatuses, true)) {
        $scheduleReadyCount++;
    }
}

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">
        <section class="calendar-page-header">
            <div>
                <div class="calendar-eyebrow">
                    <i class="bi bi-calendar-week"></i>
                    Legislative Calendar Planning
                </div>

                <h1>Calendar Scheduling</h1>

                <p>
                    Plan sessions, committee meetings, agenda activities,
                    deadlines, and coordinated legislative events.
                </p>
            </div>

            <div class="calendar-header-actions">
                <a href="report.php" class="btn btn-outline-secondary">
                    <i class="bi bi-bar-chart"></i>
                    Reports
                </a>

                <a
                    href="print.php"
                    class="btn btn-outline-secondary"
                    target="_blank"
                >
                    <i class="bi bi-printer"></i>
                    Print Queue
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnScheduleEvent"
                >
                    <i class="bi bi-calendar-plus"></i>
                    Schedule Activity
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php
            $cards = [
                [
                    'value' => count($candidateRows),
                    'label' => 'Scheduling Candidates',
                    'icon'  => 'bi-files',
                    'class' => '',
                ],
                [
                    'value' => $urgentCount,
                    'label' => 'Urgent Candidates',
                    'icon'  => 'bi-exclamation-octagon',
                    'class' => 'urgent',
                ],
                [
                    'value' => $highCount,
                    'label' => 'High-Priority Candidates',
                    'icon'  => 'bi-arrow-up-circle',
                    'class' => 'high',
                ],
                [
                    'value' => $scheduleReadyCount,
                    'label' => 'Schedule-Ready Records',
                    'icon'  => 'bi-calendar-check',
                    'class' => '',
                ],
                [
                    'value' => 0,
                    'label' => 'Saved Activities',
                    'icon'  => 'bi-clock',
                    'class' => '',
                ],
                [
                    'value' => 0,
                    'label' => 'Detected Conflicts',
                    'icon'  => 'bi-calendar-x',
                    'class' => 'conflict',
                ],
            ];
            ?>

            <?php foreach ($cards as $card): ?>
                <div class="col-sm-6 col-xl">
                    <div class="calendar-summary-card <?= e($card['class']) ?>">
                        <span class="summary-icon">
                            <i class="bi <?= e($card['icon']) ?>"></i>
                        </span>

                        <strong><?= (int)$card['value'] ?></strong>
                        <span><?= e($card['label']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-9">
                <section class="calendar-panel">
                    <div class="calendar-panel-heading calendar-toolbar">
                        <div>
                            <h2>
                                <i class="bi bi-calendar3"></i>
                                Legislative Calendar
                            </h2>

                            <p>
                                Browse dates and preview activities before
                                backend persistence is connected.
                            </p>
                        </div>

                        <div class="calendar-toolbar-controls">
                            <div class="calendar-navigation">
                                <button
                                    type="button"
                                    id="calendarPrevious"
                                    title="Previous month"
                                >
                                    <i class="bi bi-chevron-left"></i>
                                </button>

                                <button
                                    type="button"
                                    id="calendarToday"
                                >
                                    Today
                                </button>

                                <button
                                    type="button"
                                    id="calendarNext"
                                    title="Next month"
                                >
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>

                            <strong id="calendarCurrentTitle">
                                <?= e(date('F Y')) ?>
                            </strong>

                            <div class="calendar-view-switcher">
                                <button
                                    type="button"
                                    class="active"
                                    data-calendar-view="month"
                                >
                                    Month
                                </button>

                                <button
                                    type="button"
                                    data-calendar-view="week"
                                >
                                    Week
                                </button>

                                <button
                                    type="button"
                                    data-calendar-view="agenda"
                                >
                                    Agenda
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="calendar-view-section active"
                        data-calendar-section="month"
                    >
                        <div class="calendar-weekday-row">
                            <span>Sun</span>
                            <span>Mon</span>
                            <span>Tue</span>
                            <span>Wed</span>
                            <span>Thu</span>
                            <span>Fri</span>
                            <span>Sat</span>
                        </div>

                        <div
                            class="calendar-month-grid"
                            id="calendarMonthGrid"
                        ></div>
                    </div>

                    <div
                        class="calendar-view-section"
                        data-calendar-section="week"
                    >
                        <div class="calendar-empty-view">
                            <i class="bi bi-calendar-week"></i>
                            <strong>Weekly scheduling view</strong>
                            <span>
                                Hourly time slots will appear here after
                                saved calendar activities are connected.
                            </span>
                        </div>
                    </div>

                    <div
                        class="calendar-view-section"
                        data-calendar-section="agenda"
                    >
                        <div class="calendar-empty-view">
                            <i class="bi bi-card-checklist"></i>
                            <strong>Agenda scheduling view</strong>
                            <span>
                                A chronological event and agenda list will
                                appear here after calendar records are stored.
                            </span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-3">
                <section class="calendar-panel h-100">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-list-check"></i>
                                Upcoming Agenda
                            </h2>

                            <p>Next legislative activities.</p>
                        </div>
                    </div>

                    <div
                        class="calendar-upcoming-list"
                        id="calendarUpcomingList"
                    >
                        <div class="calendar-upcoming-empty">
                            <i class="bi bi-calendar2-plus"></i>
                            <strong>No saved activities</strong>
                            <span>
                                Browser preview activities will appear here.
                            </span>
                        </div>
                    </div>

                    <div class="calendar-side-actions">
                        <button
                            type="button"
                            class="btn btn-outline-primary w-100"
                            id="btnOpenConflictChecker"
                        >
                            <i class="bi bi-calendar-x"></i>
                            Check Schedule Conflicts
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <section class="calendar-filter-panel">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label" for="calendarQueueSearch">
                        Search Scheduling Queue
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="search"
                            id="calendarQueueSearch"
                            class="form-control"
                            placeholder="Reference, title, office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="calendarQueueType">
                        Record Type
                    </label>

                    <select
                        id="calendarQueueType"
                        class="form-select"
                    >
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="calendarQueuePriority">
                        Priority
                    </label>

                    <select
                        id="calendarQueuePriority"
                        class="form-select"
                    >
                        <option value="">All Priorities</option>
                        <option value="Urgent">Urgent</option>
                        <option value="High">High</option>
                        <option value="Normal">Normal</option>
                        <option value="Low">Low</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="calendarQueueStatus">
                        Workflow Status
                    </label>

                    <select
                        id="calendarQueueStatus"
                        class="form-select"
                    >
                        <option value="">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Committee Endorsed">
                            Committee Endorsed
                        </option>
                        <option value="For Approval">For Approval</option>
                        <option value="Approved">Approved</option>
                        <option value="Enacted">Enacted</option>
                        <option value="Published">Published</option>
                        <option value="Under Implementation">
                            Under Implementation
                        </option>
                        <option value="Implemented">Implemented</option>
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="btnResetCalendarQueue"
                        title="Reset filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="calendar-panel">
            <div class="calendar-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-inboxes"></i>
                        Legislative Scheduling Queue
                    </h2>

                    <p>
                        Ordinances and resolutions available for calendar
                        placement.
                    </p>
                </div>
            </div>

            <?php include __DIR__ . '/table.php'; ?>
        </section>

        <div
            class="modal fade"
            id="calendarEventModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="calendarEventForm">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrfToken()) ?>"
                        >

                        <div class="modal-header">
                            <div>
                                <h5
                                    class="modal-title"
                                    id="calendarEventModalTitle"
                                >
                                    Schedule Legislative Activity
                                </h5>

                                <small class="text-muted">
                                    Configure event details, coordination,
                                    agenda, and notifications.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="calendar-form-steps">
                                <button
                                    type="button"
                                    class="calendar-step active"
                                    data-calendar-step="1"
                                >
                                    <span>1</span>
                                    Event Details
                                </button>

                                <button
                                    type="button"
                                    class="calendar-step"
                                    data-calendar-step="2"
                                >
                                    <span>2</span>
                                    Coordination
                                </button>

                                <button
                                    type="button"
                                    class="calendar-step"
                                    data-calendar-step="3"
                                >
                                    <span>3</span>
                                    Agenda
                                </button>

                                <button
                                    type="button"
                                    class="calendar-step"
                                    data-calendar-step="4"
                                >
                                    <span>4</span>
                                    Notifications
                                </button>
                            </div>

                            <div
                                class="calendar-form-section active"
                                data-calendar-form-section="1"
                            >
                                <div class="calendar-section-heading">
                                    <h6>Activity Information</h6>
                                    <p>
                                        Define the activity type, title,
                                        legislative measure, date, and time.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Activity Type
                                        </label>

                                        <select
                                            class="form-select"
                                            name="activity_type"
                                        >
                                            <option>Regular Session</option>
                                            <option>Special Session</option>
                                            <option>Committee Meeting</option>
                                            <option>Agenda Conference</option>
                                            <option>
                                                Executive-Legislative Meeting
                                            </option>
                                            <option>Public Hearing</option>
                                            <option>Workshop</option>
                                            <option>Deadline</option>
                                            <option>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">
                                            Activity Title
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="activity_title"
                                            id="calendarActivityTitle"
                                            placeholder="Enter activity title"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Related Legislative Measure
                                        </label>

                                        <select
                                            class="form-select"
                                            name="legislative_item_id"
                                            id="calendarMeasureSelect"
                                        >
                                            <option value="">
                                                -- No related measure --
                                            </option>

                                            <?php foreach ($candidateRows as $candidate): ?>
                                                <option
                                                    value="<?= (int)$candidate['id'] ?>"
                                                    data-reference="<?= e($candidate['reference_number']) ?>"
                                                    data-title="<?= e($candidate['title']) ?>"
                                                    data-type="<?= e($candidate['item_type_name']) ?>"
                                                    data-status="<?= e($candidate['current_status']) ?>"
                                                    data-priority="<?= e($candidate['priority_level']) ?>"
                                                >
                                                    <?= e(
                                                        $candidate['reference_number'] .
                                                        ' — ' .
                                                        $candidate['title']
                                                    ) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <div
                                            class="calendar-measure-preview"
                                            id="calendarMeasurePreview"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>

                                            <div>
                                                <strong>
                                                    No legislative measure linked
                                                </strong>

                                                <span>
                                                    The activity may remain
                                                    independent or be connected
                                                    to a legislative record.
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Activity Date
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="activity_date"
                                            id="calendarActivityDate"
                                            value="<?= date('Y-m-d') ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">
                                            Start Time
                                        </label>

                                        <input
                                            type="time"
                                            class="form-control"
                                            name="start_time"
                                            id="calendarStartTime"
                                            value="09:00"
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">
                                            End Time
                                        </label>

                                        <input
                                            type="time"
                                            class="form-control"
                                            name="end_time"
                                            id="calendarEndTime"
                                            value="10:00"
                                        >
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label d-block">
                                            Duration
                                        </label>

                                        <label class="calendar-all-day-option">
                                            <input
                                                type="checkbox"
                                                id="calendarAllDay"
                                                name="all_day"
                                                value="1"
                                            >

                                            <span>All day</span>
                                        </label>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Description
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="activity_description"
                                            rows="4"
                                            placeholder="Purpose and expected outcome"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="calendar-form-section"
                                data-calendar-form-section="2"
                            >
                                <div class="calendar-section-heading">
                                    <h6>Coordination Details</h6>
                                    <p>
                                        Assign the venue, organizer,
                                        committee, office, and participants.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Meeting Mode
                                        </label>

                                        <select
                                            class="form-select"
                                            name="meeting_mode"
                                        >
                                            <option>In Person</option>
                                            <option>Online</option>
                                            <option>Hybrid</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">
                                            Venue or Meeting Link
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="venue"
                                            placeholder="Room, hall, or URL"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Responsible Office
                                        </label>

                                        <select
                                            class="form-select"
                                            name="office_id"
                                        >
                                            <option value="">
                                                -- Select office --
                                            </option>

                                            <?php foreach ($offices as $office): ?>
                                                <option value="<?= (int)$office['id'] ?>">
                                                    <?= e($office['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Responsible Committee
                                        </label>

                                        <select
                                            class="form-select"
                                            name="committee_id"
                                        >
                                            <option value="">
                                                -- Select committee --
                                            </option>

                                            <?php foreach ($committees as $committee): ?>
                                                <option value="<?= (int)$committee['id'] ?>">
                                                    <?= e($committee['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Organizer / Coordinator
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="organizer"
                                            placeholder="Organizer name"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Recurrence
                                        </label>

                                        <select
                                            class="form-select"
                                            name="recurrence"
                                        >
                                            <option>None</option>
                                            <option>Daily</option>
                                            <option>Weekly</option>
                                            <option>Monthly</option>
                                            <option>Custom</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Participants and Invitees
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="participants"
                                            rows="4"
                                            placeholder="Officials, offices, committees, and stakeholders"
                                        ></textarea>
                                    </div>

                                    <div class="col-12">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary"
                                            id="btnCheckEventConflict"
                                        >
                                            <i class="bi bi-calendar-x"></i>
                                            Run Conflict Check
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="calendar-form-section"
                                data-calendar-form-section="3"
                            >
                                <div class="calendar-section-heading">
                                    <h6>Meeting Agenda</h6>
                                    <p>
                                        Add agenda items and expected outputs.
                                    </p>
                                </div>

                                <div
                                    class="calendar-agenda-builder"
                                    id="calendarAgendaBuilder"
                                >
                                    <div
                                        class="calendar-agenda-item"
                                        data-agenda-item
                                    >
                                        <span class="agenda-order">1</span>

                                        <div class="agenda-fields">
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="agenda_title[]"
                                                placeholder="Agenda item title"
                                            >

                                            <textarea
                                                class="form-control"
                                                name="agenda_description[]"
                                                rows="2"
                                                placeholder="Agenda description or expected action"
                                            ></textarea>
                                        </div>

                                        <button
                                            type="button"
                                            class="agenda-remove-button"
                                            data-remove-agenda
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    id="btnAddAgendaItem"
                                >
                                    <i class="bi bi-plus-circle"></i>
                                    Add Agenda Item
                                </button>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Expected Output
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="expected_output"
                                            rows="4"
                                            placeholder="Decision or expected output"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Preparation Notes
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="preparation_notes"
                                            rows="4"
                                            placeholder="Documents and preparation requirements"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="calendar-form-section"
                                data-calendar-form-section="4"
                            >
                                <div class="calendar-section-heading">
                                    <h6>Notifications and Publication</h6>
                                    <p>
                                        Configure reminders, visibility,
                                        event status, and files.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Reminder
                                        </label>

                                        <select
                                            class="form-select"
                                            name="reminder"
                                        >
                                            <option>15 Minutes Before</option>
                                            <option>1 Hour Before</option>
                                            <option selected>1 Day Before</option>
                                            <option>3 Days Before</option>
                                            <option>1 Week Before</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Visibility
                                        </label>

                                        <select
                                            class="form-select"
                                            name="visibility"
                                        >
                                            <option>Internal</option>
                                            <option>Legislative Staff</option>
                                            <option>Officials and Invitees</option>
                                            <option>Public</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Event Status
                                        </label>

                                        <select
                                            class="form-select"
                                            name="event_status"
                                        >
                                            <option>Draft</option>
                                            <option>Tentative</option>
                                            <option selected>Confirmed</option>
                                            <option>Postponed</option>
                                            <option>Cancelled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="calendar-notification-checklist">
                                    <label>
                                        <input type="checkbox" checked>
                                        <span>
                                            Send invitations after confirmation.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>
                                            Require attendance confirmation.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>
                                            Publish to the shared calendar.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>
                                            Create related preparation deadlines.
                                        </span>
                                    </label>
                                </div>

                                <div class="calendar-upload-area">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <strong>Upload agenda and meeting files</strong>
                                    <span>
                                        Agenda, invitations, notices,
                                        presentations, and supporting files
                                    </span>

                                    <input
                                        type="file"
                                        id="calendarDocuments"
                                        multiple
                                    >
                                </div>

                                <div
                                    id="calendarSelectedFiles"
                                    class="calendar-file-list"
                                ></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary me-auto"
                                id="btnPreviousCalendarStep"
                                disabled
                            >
                                <i class="bi bi-arrow-left"></i>
                                Previous
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="btnSaveCalendarDraft"
                            >
                                <i class="bi bi-floppy"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnNextCalendarStep"
                            >
                                Next
                                <i class="bi bi-arrow-right"></i>
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary d-none"
                                id="btnSubmitCalendarEvent"
                            >
                                <i class="bi bi-calendar-check"></i>
                                Schedule Activity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
