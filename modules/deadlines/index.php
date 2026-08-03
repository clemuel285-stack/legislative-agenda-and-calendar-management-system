<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Deadline Tracking';
$activeMenu = 'deadlines';
$extraCss   = [appUrl('assets/css/deadlines.css')];
$extraJs    = [appUrl('assets/js/deadlines.js')];

$pdo = db();

$offices = [];
$committees = [];

try {
    $offices = $pdo->query(
        "SELECT id, name FROM offices ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Deadlines Offices] ' . $exception->getMessage());
}

try {
    $committees = $pdo->query(
        "SELECT id, name FROM committees ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Deadlines Committees] ' . $exception->getMessage());
}

$stmt = $pdo->query(
    "SELECT
        li.id,
        li.reference_number,
        li.title,
        li.current_status,
        li.priority_level,
        li.created_at,
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
$trackingReady = 0;

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
            'Enacted',
            'For Publication',
            'Published',
            'Under Implementation'
        ],
        true
    )) {
        $trackingReady++;
    }
}

$cards = [
    ['value' => count($candidateRows), 'label' => 'Tracking Candidates', 'icon' => 'bi-files', 'class' => ''],
    ['value' => $urgent, 'label' => 'Urgent Measures', 'icon' => 'bi-exclamation-octagon', 'class' => 'urgent'],
    ['value' => $high, 'label' => 'High-Priority Measures', 'icon' => 'bi-arrow-up-circle', 'class' => 'high'],
    ['value' => $trackingReady, 'label' => 'Tracking-Ready Records', 'icon' => 'bi-check2-square', 'class' => ''],
    ['value' => 0, 'label' => 'Due Soon', 'icon' => 'bi-hourglass-split', 'class' => 'warning'],
    ['value' => 0, 'label' => 'Overdue', 'icon' => 'bi-alarm-fill', 'class' => 'overdue'],
];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="deadline-page-header">
            <div>
                <div class="deadline-eyebrow">
                    <i class="bi bi-alarm"></i>
                    Legislative Timeline Control
                </div>

                <h1>Deadline Tracking</h1>

                <p>
                    Monitor legislative due dates, milestones, reminders,
                    dependencies, escalations, completion evidence, and
                    overdue actions.
                </p>
            </div>

            <div class="deadline-header-actions">
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
                    id="btnCreateDeadline"
                >
                    <i class="bi bi-plus-circle"></i>
                    Create Deadline
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php foreach ($cards as $card): ?>
                <div class="col-sm-6 col-xl">
                    <div class="deadline-summary-card <?= e($card['class']) ?>">
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
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-calendar2-check"></i>
                                Active Deadline Timeline
                            </h2>

                            <p>
                                Browser-preview deadlines appear here until
                                database persistence is connected.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-light"
                            id="btnQuickDeadline"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Quick Deadline
                        </button>
                    </div>

                    <div id="deadlinePreviewList" class="deadline-preview-list">
                        <div class="deadline-empty-state">
                            <i class="bi bi-hourglass"></i>
                            <strong>No deadlines recorded yet</strong>
                            <span>Create a deadline to preview it here.</span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-shield-exclamation"></i>
                                Deadline Risk Monitor
                            </h2>

                            <p>
                                Review completion and escalation readiness.
                            </p>
                        </div>
                    </div>

                    <div class="deadline-risk-list">
                        <div class="deadline-risk-item safe">
                            <span><i class="bi bi-check-circle"></i></span>
                            <div>
                                <strong>No saved overdue items</strong>
                                <small>Overdue deadlines will appear here.</small>
                            </div>
                        </div>

                        <div class="deadline-risk-item">
                            <span><i class="bi bi-bell"></i></span>
                            <div>
                                <strong>Reminder rules pending</strong>
                                <small>Configure reminders when creating deadlines.</small>
                            </div>
                        </div>

                        <div class="deadline-risk-item">
                            <span><i class="bi bi-diagram-3"></i></span>
                            <div>
                                <strong>Dependencies not yet recorded</strong>
                                <small>Dependency risks will be evaluated later.</small>
                            </div>
                        </div>
                    </div>

                    <div class="deadline-side-action">
                        <button
                            type="button"
                            class="btn btn-outline-primary w-100"
                            id="btnRunDeadlineRiskCheck"
                        >
                            <i class="bi bi-shield-check"></i>
                            Run Risk Check
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <section class="deadline-filter-panel">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label" for="deadlineSearch">
                        Search Deadline Queue
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="search"
                            id="deadlineSearch"
                            class="form-control"
                            placeholder="Reference, title, office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="deadlineTypeFilter">
                        Record Type
                    </label>

                    <select id="deadlineTypeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="deadlinePriorityFilter">
                        Priority
                    </label>

                    <select id="deadlinePriorityFilter" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="Urgent">Urgent</option>
                        <option value="High">High</option>
                        <option value="Normal">Normal</option>
                        <option value="Low">Low</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="deadlineStatusFilter">
                        Workflow Status
                    </label>

                    <select id="deadlineStatusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Committee Endorsed">Committee Endorsed</option>
                        <option value="For Approval">For Approval</option>
                        <option value="Approved">Approved</option>
                        <option value="Enacted">Enacted</option>
                        <option value="Published">Published</option>
                        <option value="Under Implementation">Under Implementation</option>
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="btnResetDeadlineFilters"
                        title="Reset filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="deadline-panel">
            <div class="deadline-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-inboxes"></i>
                        Legislative Deadline Queue
                    </h2>

                    <p>
                        Legislative measures available for deadline,
                        milestone, reminder, and escalation tracking.
                    </p>
                </div>
            </div>

            <?php include __DIR__ . '/table.php'; ?>
        </section>

        <div
            class="modal fade"
            id="deadlineModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="deadlineForm">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrfToken()) ?>"
                        >

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="deadlineModalTitle">
                                    Create Legislative Deadline
                                </h5>

                                <small class="text-muted">
                                    Configure deadline details, ownership,
                                    reminders, escalation, and completion.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="deadline-form-steps">
                                <?php
                                $steps = [
                                    1 => 'Deadline Details',
                                    2 => 'Ownership & Dependencies',
                                    3 => 'Reminders & Escalation',
                                    4 => 'Completion & Evidence',
                                ];
                                ?>

                                <?php foreach ($steps as $number => $label): ?>
                                    <button
                                        type="button"
                                        class="deadline-step <?= $number === 1 ? 'active' : '' ?>"
                                        data-deadline-step="<?= $number ?>"
                                    >
                                        <span><?= $number ?></span>
                                        <?= e($label) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="deadline-form-section active"
                                data-deadline-form-section="1"
                            >
                                <div class="deadline-section-heading">
                                    <h6>Deadline Information</h6>
                                    <p>Define the deadline type, title, measure, due date, status, and priority.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Deadline Type</label>

                                        <select
                                            class="form-select"
                                            name="deadline_type"
                                            id="deadlineType"
                                        >
                                            <option>Document Submission</option>
                                            <option>Committee Review</option>
                                            <option>Meeting Preparation</option>
                                            <option>Agenda Inclusion</option>
                                            <option>Approval Action</option>
                                            <option>Publication Requirement</option>
                                            <option>Implementation Milestone</option>
                                            <option>Response Required</option>
                                            <option>Compliance Requirement</option>
                                            <option>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label">Deadline Title</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="deadline_title"
                                            id="deadlineTitle"
                                            required
                                            placeholder="Enter deadline title"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Related Legislative Measure</label>

                                        <select
                                            class="form-select"
                                            name="legislative_item_id"
                                            id="deadlineMeasureSelect"
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
                                            class="deadline-measure-preview"
                                            id="deadlineMeasurePreview"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>
                                            <div>
                                                <strong>No legislative measure linked</strong>
                                                <span>
                                                    The deadline can remain independent
                                                    or be linked to an ordinance or resolution.
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Due Date</label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="due_date"
                                            id="deadlineDueDate"
                                            value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Due Time</label>

                                        <input
                                            type="time"
                                            class="form-control"
                                            name="due_time"
                                            id="deadlineDueTime"
                                            value="17:00"
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Deadline Priority</label>

                                        <select
                                            class="form-select"
                                            name="deadline_priority"
                                            id="deadlinePriority"
                                        >
                                            <option>Urgent</option>
                                            <option>High</option>
                                            <option selected>Normal</option>
                                            <option>Low</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>

                                        <select
                                            class="form-select"
                                            name="deadline_status"
                                            id="deadlineStatus"
                                        >
                                            <option selected>Open</option>
                                            <option>In Progress</option>
                                            <option>At Risk</option>
                                            <option>Completed</option>
                                            <option>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Description and Required Deliverable</label>

                                        <textarea
                                            class="form-control"
                                            name="deadline_description"
                                            rows="4"
                                            placeholder="Describe the deadline, required deliverable, and expected result."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="deadline-form-section"
                                data-deadline-form-section="2"
                            >
                                <div class="deadline-section-heading">
                                    <h6>Ownership and Dependencies</h6>
                                    <p>Assign responsibility and record prerequisite tasks, milestones, and dependency risks.</p>
                                </div>

                                <div class="row g-3">
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

                                    <div class="col-md-6">
                                        <label class="form-label">Primary Owner</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="primary_owner"
                                            placeholder="Person responsible for completion"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Supporting Persons or Units</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="supporting_units"
                                            placeholder="Supporting offices, staff, or external units"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Dependencies</label>

                                        <textarea
                                            class="form-control"
                                            name="dependencies"
                                            rows="5"
                                            placeholder="List approvals, documents, meetings, decisions, or external requirements needed first."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Dependency Risks</label>

                                        <textarea
                                            class="form-control"
                                            name="dependency_risks"
                                            rows="5"
                                            placeholder="Describe possible delays, blockers, unavailable resources, or unresolved issues."
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="deadline-milestone-builder" id="deadlineMilestoneBuilder">
                                    <div class="deadline-milestone-item" data-deadline-milestone>
                                        <span class="milestone-order">1</span>

                                        <div class="milestone-fields">
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="milestone_title[]"
                                                placeholder="Milestone title"
                                            >

                                            <input
                                                type="date"
                                                class="form-control"
                                                name="milestone_date[]"
                                            >
                                        </div>

                                        <button
                                            type="button"
                                            class="milestone-remove-button"
                                            data-remove-deadline-milestone
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    id="btnAddDeadlineMilestone"
                                >
                                    <i class="bi bi-plus-circle"></i>
                                    Add Milestone
                                </button>
                            </div>

                            <div
                                class="deadline-form-section"
                                data-deadline-form-section="3"
                            >
                                <div class="deadline-section-heading">
                                    <h6>Reminders and Escalation</h6>
                                    <p>Configure reminder timing, recipients, escalation thresholds, and overdue behavior.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">First Reminder</label>

                                        <select class="form-select" name="first_reminder">
                                            <option>1 Day Before</option>
                                            <option selected>3 Days Before</option>
                                            <option>1 Week Before</option>
                                            <option>2 Weeks Before</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Second Reminder</label>

                                        <select class="form-select" name="second_reminder">
                                            <option>None</option>
                                            <option selected>1 Day Before</option>
                                            <option>3 Days Before</option>
                                            <option>1 Week Before</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Overdue Reminder</label>

                                        <select class="form-select" name="overdue_reminder">
                                            <option>Immediately</option>
                                            <option selected>Daily Until Completed</option>
                                            <option>Every 3 Days</option>
                                            <option>Weekly</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Reminder Recipients</label>

                                        <textarea
                                            class="form-control"
                                            name="reminder_recipients"
                                            rows="4"
                                            placeholder="Owner, office, committee, supervisors, and other recipients"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Escalation Recipients</label>

                                        <textarea
                                            class="form-control"
                                            name="escalation_recipients"
                                            rows="4"
                                            placeholder="Officials or supervisors who should receive escalation notices"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Escalate When</label>

                                        <select class="form-select" name="escalation_rule">
                                            <option>1 Day Overdue</option>
                                            <option selected>3 Days Overdue</option>
                                            <option>7 Days Overdue</option>
                                            <option>When Marked At Risk</option>
                                            <option>Custom Rule</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Escalation Instruction</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="escalation_instruction"
                                            placeholder="Action required after escalation"
                                        >
                                    </div>
                                </div>

                                <div class="deadline-option-list">
                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Send reminders to the primary owner.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox" checked>
                                        <span>Notify the responsible office and committee.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>Escalate automatically when the deadline becomes overdue.</span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>Create a dashboard alert for at-risk deadlines.</span>
                                    </label>
                                </div>
                            </div>

                            <div
                                class="deadline-form-section"
                                data-deadline-form-section="4"
                            >
                                <div class="deadline-section-heading">
                                    <h6>Completion and Evidence</h6>
                                    <p>Define completion criteria, verification, closing notes, and supporting evidence.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Completion Criteria</label>

                                        <textarea
                                            class="form-control"
                                            name="completion_criteria"
                                            rows="5"
                                            placeholder="State the conditions required to consider this deadline complete."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Verification Method</label>

                                        <textarea
                                            class="form-control"
                                            name="verification_method"
                                            rows="5"
                                            placeholder="Approval, signed document, submission receipt, meeting outcome, or other evidence."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Completion Percentage</label>

                                        <input
                                            type="number"
                                            class="form-control"
                                            name="completion_percentage"
                                            min="0"
                                            max="100"
                                            value="0"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Completed Date</label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="completed_date"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Verified By</label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="verified_by"
                                            placeholder="Verifier or approving authority"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Completion or Closing Notes</label>

                                        <textarea
                                            class="form-control"
                                            name="closing_notes"
                                            rows="4"
                                            placeholder="Record completion results, delays, exceptions, or closing remarks."
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="deadline-upload-area">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <strong>Upload completion evidence</strong>
                                    <span>
                                        Submission receipt, signed document,
                                        approval, report, screenshot, or supporting file
                                    </span>

                                    <input
                                        type="file"
                                        id="deadlineDocuments"
                                        name="documents[]"
                                        multiple
                                    >
                                </div>

                                <div
                                    id="deadlineSelectedFiles"
                                    class="deadline-file-list"
                                ></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary me-auto"
                                id="btnPreviousDeadlineStep"
                                disabled
                            >
                                <i class="bi bi-arrow-left"></i>
                                Previous
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="btnSaveDeadlineDraft"
                            >
                                <i class="bi bi-floppy"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnNextDeadlineStep"
                            >
                                Next
                                <i class="bi bi-arrow-right"></i>
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary d-none"
                                id="btnSubmitDeadline"
                            >
                                <i class="bi bi-check2-circle"></i>
                                Create Deadline
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
