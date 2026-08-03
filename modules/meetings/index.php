<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Meeting Coordination';
$activeMenu = 'meetings';
$extraCss   = [appUrl('assets/css/meetings.css')];
$extraJs    = [appUrl('assets/js/meetings.js')];

$pdo = db();

$offices = [];
$committees = [];

try {
    $offices = $pdo->query(
        "SELECT id, name FROM offices ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Meetings Offices] ' . $exception->getMessage());
}

try {
    $committees = $pdo->query(
        "SELECT id, name FROM committees ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Meetings Committees] ' . $exception->getMessage());
}

$stmt = $pdo->query(
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

$candidateRows = $stmt->fetchAll();

$urgent = 0;
$high = 0;
$ready = 0;

foreach ($candidateRows as $row) {
    $urgent += $row['priority_level'] === 'Urgent' ? 1 : 0;
    $high += $row['priority_level'] === 'High' ? 1 : 0;

    if (in_array(
        $row['current_status'],
        [
            'Submitted',
            'Under Review',
            'Committee Endorsed',
            'For Approval',
            'Approved',
            'Enacted'
        ],
        true
    )) {
        $ready++;
    }
}

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="meeting-page-header">
            <div>
                <div class="meeting-eyebrow">
                    <i class="bi bi-people"></i>
                    Legislative Meeting Operations
                </div>

                <h1>Meeting Coordination</h1>

                <p>
                    Coordinate legislative sessions, committee meetings,
                    participants, invitations, agendas, attendance, minutes,
                    decisions, and post-meeting action items.
                </p>
            </div>

            <div class="meeting-header-actions">
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
                    id="btnCreateMeeting"
                >
                    <i class="bi bi-plus-circle"></i>
                    Coordinate Meeting
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php
            $cards = [
                ['value' => count($candidateRows), 'label' => 'Coordination Candidates', 'icon' => 'bi-files', 'class' => ''],
                ['value' => $urgent, 'label' => 'Urgent Measures', 'icon' => 'bi-exclamation-octagon', 'class' => 'urgent'],
                ['value' => $high, 'label' => 'High-Priority Measures', 'icon' => 'bi-arrow-up-circle', 'class' => 'high'],
                ['value' => $ready, 'label' => 'Coordination-Ready Records', 'icon' => 'bi-check2-square', 'class' => ''],
                ['value' => 0, 'label' => 'Saved Meetings', 'icon' => 'bi-calendar-event', 'class' => ''],
                ['value' => 0, 'label' => 'Confirmed Participants', 'icon' => 'bi-person-check', 'class' => ''],
            ];
            ?>

            <?php foreach ($cards as $card): ?>
                <div class="col-sm-6 col-xl">
                    <div class="meeting-summary-card <?= e($card['class']) ?>">
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
            <div class="col-xl-8">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-calendar-check"></i>
                                Upcoming Coordinated Meetings
                            </h2>

                            <p>
                                Browser-preview meetings appear here until
                                database persistence is connected.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-light"
                            id="btnQuickMeeting"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Quick Meeting
                        </button>
                    </div>

                    <div id="meetingUpcomingList" class="meeting-upcoming-list">
                        <div class="meeting-empty-state">
                            <i class="bi bi-calendar2-plus"></i>
                            <strong>No meetings coordinated yet</strong>
                            <span>Create a meeting to preview it here.</span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clipboard-check"></i>
                                Coordination Checklist
                            </h2>

                            <p>Recommended pre-meeting requirements.</p>
                        </div>
                    </div>

                    <div class="meeting-checklist-panel">
                        <?php
                        $checklist = [
                            'Purpose and expected output defined',
                            'Venue or online meeting link confirmed',
                            'Committee, office, and coordinator assigned',
                            'Participants and invitees identified',
                            'Agenda and supporting materials prepared',
                            'Attendance confirmation requested',
                        ];
                        ?>

                        <?php foreach ($checklist as $item): ?>
                            <label>
                                <input
                                    type="checkbox"
                                    class="coordination-check"
                                >
                                <span><?= e($item) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="meeting-progress-block">
                        <div>
                            <span>Checklist Progress</span>
                            <strong id="meetingChecklistValue">0%</strong>
                        </div>

                        <div class="meeting-progress-track">
                            <span id="meetingChecklistBar"></span>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <section class="meeting-filter-panel">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label" for="meetingSearch">
                        Search Coordination Queue
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="search"
                            id="meetingSearch"
                            class="form-control"
                            placeholder="Reference, title, office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="meetingTypeFilter">
                        Record Type
                    </label>

                    <select id="meetingTypeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="meetingPriorityFilter">
                        Priority
                    </label>

                    <select id="meetingPriorityFilter" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="Urgent">Urgent</option>
                        <option value="High">High</option>
                        <option value="Normal">Normal</option>
                        <option value="Low">Low</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="meetingStatusFilter">
                        Workflow Status
                    </label>

                    <select id="meetingStatusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Committee Endorsed">Committee Endorsed</option>
                        <option value="For Approval">For Approval</option>
                        <option value="Approved">Approved</option>
                        <option value="Enacted">Enacted</option>
                        <option value="Published">Published</option>
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="btnResetMeetingFilters"
                        title="Reset filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="meeting-panel">
            <div class="meeting-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-inboxes"></i>
                        Meeting Coordination Queue
                    </h2>

                    <p>
                        Legislative measures available for committee,
                        agenda, executive-legislative, or working meetings.
                    </p>
                </div>
            </div>

            <?php include __DIR__ . '/table.php'; ?>
        </section>

        <div
            class="modal fade"
            id="meetingModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="meetingForm">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrfToken()) ?>"
                        >

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="meetingModalTitle">
                                    Coordinate Legislative Meeting
                                </h5>

                                <small class="text-muted">
                                    Configure meeting details, participants,
                                    agenda, attendance, and outputs.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="meeting-form-steps">
                                <?php
                                $steps = [
                                    1 => 'Meeting Details',
                                    2 => 'Participants',
                                    3 => 'Agenda & Materials',
                                    4 => 'Attendance & Outputs',
                                ];
                                ?>

                                <?php foreach ($steps as $number => $label): ?>
                                    <button
                                        type="button"
                                        class="meeting-step <?= $number === 1 ? 'active' : '' ?>"
                                        data-meeting-step="<?= $number ?>"
                                    >
                                        <span><?= $number ?></span>
                                        <?= e($label) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="meeting-form-section active"
                                data-meeting-form-section="1"
                            >
                                <div class="meeting-section-heading">
                                    <h6>Meeting Information</h6>
                                    <p>Define the purpose, type, schedule, venue, and responsible unit.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Meeting Type</label>
                                        <select class="form-select" name="meeting_type">
                                            <option>Committee Meeting</option>
                                            <option>Agenda Conference</option>
                                            <option>Regular Session Preparation</option>
                                            <option>Special Session Preparation</option>
                                            <option>Executive-Legislative Meeting</option>
                                            <option>Technical Working Group</option>
                                            <option>Stakeholder Consultation</option>
                                            <option>Internal Coordination</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Meeting Title</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="meetingTitle"
                                            name="meeting_title"
                                            required
                                            placeholder="Enter meeting title"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Related Legislative Measure</label>
                                        <select
                                            class="form-select"
                                            id="meetingMeasureSelect"
                                            name="legislative_item_id"
                                        >
                                            <option value="">-- No related measure --</option>

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
                                            class="meeting-measure-preview"
                                            id="meetingMeasurePreview"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>
                                            <div>
                                                <strong>No legislative measure linked</strong>
                                                <span>
                                                    The meeting can remain independent or be
                                                    linked to an ordinance or resolution.
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Meeting Date</label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="meetingDate"
                                            name="meeting_date"
                                            value="<?= date('Y-m-d') ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Start Time</label>
                                        <input
                                            type="time"
                                            class="form-control"
                                            id="meetingStartTime"
                                            name="start_time"
                                            value="09:00"
                                        >
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">End Time</label>
                                        <input
                                            type="time"
                                            class="form-control"
                                            name="end_time"
                                            value="10:30"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Meeting Mode</label>
                                        <select class="form-select" name="meeting_mode">
                                            <option>In Person</option>
                                            <option>Online</option>
                                            <option>Hybrid</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Venue or Online Meeting Link</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="meetingVenue"
                                            name="venue"
                                            placeholder="Session hall, conference room, or meeting URL"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Meeting Status</label>
                                        <select
                                            class="form-select"
                                            id="meetingStatus"
                                            name="meeting_status"
                                        >
                                            <option>Draft</option>
                                            <option>Tentative</option>
                                            <option selected>Confirmed</option>
                                            <option>Postponed</option>
                                            <option>Cancelled</option>
                                            <option>Completed</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Responsible Office</label>
                                        <select class="form-select" name="office_id">
                                            <option value="">-- Select office --</option>
                                            <?php foreach ($offices as $office): ?>
                                                <option value="<?= (int)$office['id'] ?>">
                                                    <?= e($office['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Responsible Committee</label>
                                        <select class="form-select" name="committee_id">
                                            <option value="">-- Select committee --</option>
                                            <?php foreach ($committees as $committee): ?>
                                                <option value="<?= (int)$committee['id'] ?>">
                                                    <?= e($committee['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Meeting Purpose</label>
                                        <textarea
                                            class="form-control"
                                            name="meeting_purpose"
                                            rows="4"
                                            placeholder="Describe the purpose and expected result."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="meeting-form-section"
                                data-meeting-form-section="2"
                            >
                                <div class="meeting-section-heading">
                                    <h6>Participants and Invitations</h6>
                                    <p>Identify organizers, required participants, optional invitees, and confirmations.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Organizer</label>
                                        <input type="text" class="form-control" name="organizer">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Chairperson</label>
                                        <input type="text" class="form-control" name="chairperson">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Meeting Secretary</label>
                                        <input type="text" class="form-control" name="meeting_secretary">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Required Participants</label>
                                        <textarea
                                            class="form-control"
                                            name="required_participants"
                                            rows="5"
                                            placeholder="Officials, committee members, offices, and staff"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Optional Invitees</label>
                                        <textarea
                                            class="form-control"
                                            name="optional_invitees"
                                            rows="5"
                                            placeholder="Guests, experts, stakeholders, and observers"
                                        ></textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="meeting-participant-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary"
                                                id="btnImportCommitteeMembers"
                                            >
                                                <i class="bi bi-people"></i>
                                                Import Committee Members
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-outline-primary"
                                                id="btnCheckParticipantConflicts"
                                            >
                                                <i class="bi bi-calendar-x"></i>
                                                Check Conflicts
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary"
                                                id="btnPreviewInvitation"
                                            >
                                                <i class="bi bi-envelope-paper"></i>
                                                Preview Invitation
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="meeting-option-list">
                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Require attendance confirmation from invitees.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>Allow an authorized delegate to attend.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Send a calendar invitation with the meeting notice.</span>
                                    </label>
                                </div>
                            </div>

                            <div
                                class="meeting-form-section"
                                data-meeting-form-section="3"
                            >
                                <div class="meeting-section-heading">
                                    <h6>Agenda and Meeting Materials</h6>
                                    <p>Build the agenda and identify expected actions and supporting files.</p>
                                </div>

                                <div
                                    id="meetingAgendaBuilder"
                                    class="meeting-agenda-builder"
                                >
                                    <div
                                        class="meeting-agenda-item"
                                        data-meeting-agenda-item
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
                                                placeholder="Details or expected action"
                                            ></textarea>
                                        </div>

                                        <button
                                            type="button"
                                            class="agenda-remove-button"
                                            data-remove-meeting-agenda
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    id="btnAddMeetingAgendaItem"
                                >
                                    <i class="bi bi-plus-circle"></i>
                                    Add Agenda Item
                                </button>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">Preparation Requirements</label>
                                        <textarea
                                            class="form-control"
                                            name="preparation_requirements"
                                            rows="4"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Expected Meeting Output</label>
                                        <textarea
                                            class="form-control"
                                            name="expected_output"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="meeting-upload-area">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <strong>Upload meeting materials</strong>
                                    <span>
                                        Agenda, invitations, briefing notes,
                                        presentations, and supporting files
                                    </span>

                                    <input
                                        type="file"
                                        id="meetingDocuments"
                                        name="documents[]"
                                        multiple
                                    >
                                </div>

                                <div
                                    id="meetingSelectedFiles"
                                    class="meeting-file-list"
                                ></div>
                            </div>

                            <div
                                class="meeting-form-section"
                                data-meeting-form-section="4"
                            >
                                <div class="meeting-section-heading">
                                    <h6>Attendance, Documentation, and Outputs</h6>
                                    <p>Configure attendance, minutes, decisions, action items, and follow-up.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Attendance Method</label>
                                        <select class="form-select" name="attendance_method">
                                            <option>Manual Sign-In</option>
                                            <option>Digital Attendance</option>
                                            <option>QR Code</option>
                                            <option>Hybrid Attendance</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Reminder Schedule</label>
                                        <select class="form-select" name="reminder_schedule">
                                            <option>1 Hour Before</option>
                                            <option selected>1 Day Before</option>
                                            <option>3 Days Before</option>
                                            <option>1 Week Before</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Minutes Due</label>
                                        <input type="date" class="form-control" name="minutes_due_date">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Decisions or Resolutions</label>
                                        <textarea
                                            class="form-control"
                                            name="meeting_decisions"
                                            rows="4"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Action Items and Responsible Persons</label>
                                        <textarea
                                            class="form-control"
                                            name="action_items"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="meeting-option-list">
                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Record participant attendance and confirmation status.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Prepare and circulate official meeting minutes.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>Create deadline items from approved meeting actions.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>Publish the approved meeting outcome.</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary me-auto"
                                id="btnPreviousMeetingStep"
                                disabled
                            >
                                <i class="bi bi-arrow-left"></i>
                                Previous
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="btnSaveMeetingDraft"
                            >
                                <i class="bi bi-floppy"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnNextMeetingStep"
                            >
                                Next
                                <i class="bi bi-arrow-right"></i>
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary d-none"
                                id="btnSubmitMeeting"
                            >
                                <i class="bi bi-calendar-check"></i>
                                Coordinate Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
