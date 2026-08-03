<?php
/**
 * modules/priorities/index.php
 * ------------------------------------------------------------------
 * Legislative Priority Setting Module.
 *
 * Current phase:
 * - Reads ordinance and resolution records from the shared database.
 * - Provides the complete priority-setting interface.
 * - Database write endpoints will be connected later.
 * ------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([
    ROLE_ADMIN,
    ROLE_STAFF,
    ROLE_COMMITTEE,
]);

$pageTitle  = 'Legislative Priority Setting';
$activeMenu = 'priorities';

$extraCss = [
    appUrl('assets/css/priorities.css'),
];

$extraJs = [
    appUrl('assets/js/priorities.js'),
];

$pdo = db();

$offices = [];

try {
    $offices = $pdo->query(
        "SELECT id, name
         FROM offices
         ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log(
        '[LACMS Priorities Offices] ' .
        $exception->getMessage()
    );
}

$summaryStmt = $pdo->query(
    "SELECT
        COUNT(*) AS total_records,

        SUM(
            CASE
                WHEN li.priority_level = 'Urgent'
                THEN 1 ELSE 0
            END
        ) AS urgent_count,

        SUM(
            CASE
                WHEN li.priority_level = 'High'
                THEN 1 ELSE 0
            END
        ) AS high_count,

        SUM(
            CASE
                WHEN li.priority_level = 'Normal'
                THEN 1 ELSE 0
            END
        ) AS normal_count,

        SUM(
            CASE
                WHEN li.priority_level = 'Low'
                THEN 1 ELSE 0
            END
        ) AS low_count

     FROM legislative_items li
     INNER JOIN legislative_item_types lit
        ON lit.id = li.item_type_id

     WHERE li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')"
);

$summary = $summaryStmt->fetch() ?: [];

$stats = [
    'total'  => (int)($summary['total_records'] ?? 0),
    'urgent' => (int)($summary['urgent_count'] ?? 0),
    'high'   => (int)($summary['high_count'] ?? 0),
    'normal' => (int)($summary['normal_count'] ?? 0),
    'low'    => (int)($summary['low_count'] ?? 0),
];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="priority-page-header">
            <div>
                <div class="priority-eyebrow">
                    <i class="bi bi-list-stars"></i>
                    Legislative Agenda Planning
                </div>

                <h1>Legislative Priority Setting</h1>

                <p>
                    Evaluate, rank, classify, and prepare ordinances and
                    resolutions for inclusion in the legislative agenda.
                </p>
            </div>

            <div class="priority-header-actions">
                <a
                    href="report.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-bar-chart"></i>
                    Reports
                </a>

                <a
                    href="print.php"
                    class="btn btn-outline-secondary"
                    target="_blank"
                >
                    <i class="bi bi-printer"></i>
                    Print Priority List
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnOpenPriorityModal"
                >
                    <i class="bi bi-plus-circle"></i>
                    Set Priority
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-xl">
                <div class="priority-summary-card">
                    <span class="summary-icon">
                        <i class="bi bi-files"></i>
                    </span>

                    <strong><?= $stats['total'] ?></strong>
                    <span>Total Legislative Records</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="priority-summary-card urgent">
                    <span class="summary-icon">
                        <i class="bi bi-exclamation-octagon"></i>
                    </span>

                    <strong><?= $stats['urgent'] ?></strong>
                    <span>Urgent Priorities</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="priority-summary-card high">
                    <span class="summary-icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </span>

                    <strong><?= $stats['high'] ?></strong>
                    <span>High Priorities</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="priority-summary-card">
                    <span class="summary-icon">
                        <i class="bi bi-dash-circle"></i>
                    </span>

                    <strong><?= $stats['normal'] ?></strong>
                    <span>Normal Priorities</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl">
                <div class="priority-summary-card low">
                    <span class="summary-icon">
                        <i class="bi bi-arrow-down-circle"></i>
                    </span>

                    <strong><?= $stats['low'] ?></strong>
                    <span>Low Priorities</span>
                </div>
            </div>
        </section>

        <section class="priority-filter-panel">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label
                        for="prioritySearch"
                        class="form-label"
                    >
                        Search Legislative Record
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="search"
                            id="prioritySearch"
                            class="form-control"
                            placeholder="Reference, title, office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        for="priorityTypeFilter"
                        class="form-label"
                    >
                        Record Type
                    </label>

                    <select
                        id="priorityTypeFilter"
                        class="form-select"
                    >
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        for="priorityLevelFilter"
                        class="form-label"
                    >
                        Priority Level
                    </label>

                    <select
                        id="priorityLevelFilter"
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
                    <label
                        for="priorityStatusFilter"
                        class="form-label"
                    >
                        Current Workflow Status
                    </label>

                    <select
                        id="priorityStatusFilter"
                        class="form-select"
                    >
                        <option value="">All Statuses</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Under Review">Under Review</option>
                        <option value="Committee Endorsed">
                            Committee Endorsed
                        </option>
                        <option value="For Approval">
                            For Approval
                        </option>
                        <option value="Approved">Approved</option>
                        <option value="Enacted">Enacted</option>
                        <option value="Published">Published</option>
                        <option value="Under Implementation">
                            Under Implementation
                        </option>
                        <option value="Implemented">
                            Implemented
                        </option>
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="btnResetPriorityFilters"
                        title="Reset filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="priority-table-card">
            <div class="priority-table-header">
                <div>
                    <h2>
                        <i class="bi bi-list-ol"></i>
                        Legislative Priority Registry
                    </h2>

                    <p>
                        Records are initially ordered by current priority
                        level and most recent activity.
                    </p>
                </div>

                <div class="priority-legend">
                    <span>
                        <i class="bi bi-circle-fill urgent"></i>
                        Urgent
                    </span>

                    <span>
                        <i class="bi bi-circle-fill high"></i>
                        High
                    </span>

                    <span>
                        <i class="bi bi-circle-fill normal"></i>
                        Normal
                    </span>

                    <span>
                        <i class="bi bi-circle-fill low"></i>
                        Low
                    </span>
                </div>
            </div>

            <div id="priorityTableContainer">
                <?php include __DIR__ . '/table.php'; ?>
            </div>
        </section>

        <div
            class="modal fade"
            id="priorityModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="priorityForm">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrfToken()) ?>"
                        >

                        <input
                            type="hidden"
                            name="priority_record_id"
                            id="priorityRecordId"
                        >

                        <div class="modal-header">
                            <div>
                                <h5
                                    class="modal-title"
                                    id="priorityModalTitle"
                                >
                                    Set Legislative Priority
                                </h5>

                                <small class="text-muted">
                                    Evaluate the measure and prepare its
                                    recommended agenda ranking.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="priority-form-steps">
                                <button
                                    type="button"
                                    class="priority-step active"
                                    data-priority-step="1"
                                >
                                    <span>1</span>
                                    Candidate Measure
                                </button>

                                <button
                                    type="button"
                                    class="priority-step"
                                    data-priority-step="2"
                                >
                                    <span>2</span>
                                    Evaluation Criteria
                                </button>

                                <button
                                    type="button"
                                    class="priority-step"
                                    data-priority-step="3"
                                >
                                    <span>3</span>
                                    Ranking &amp; Schedule
                                </button>

                                <button
                                    type="button"
                                    class="priority-step"
                                    data-priority-step="4"
                                >
                                    <span>4</span>
                                    Recommendation
                                </button>
                            </div>

                            <div
                                class="priority-form-section active"
                                data-priority-section="1"
                            >
                                <div class="priority-section-heading">
                                    <h6>Select Legislative Candidate</h6>

                                    <p>
                                        Choose the ordinance or resolution
                                        that will be evaluated for agenda
                                        prioritization.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">
                                            Legislative Measure
                                        </label>

                                        <select
                                            class="form-select"
                                            name="legislative_item_id"
                                            id="priorityMeasureSelect"
                                            required
                                        >
                                            <option value="">
                                                -- Select measure --
                                            </option>

                                            <?php
                                            $candidateStmt = $pdo->query(
                                                "SELECT
                                                    li.id,
                                                    li.reference_number,
                                                    li.title,
                                                    li.current_status,
                                                    li.priority_level,
                                                    lit.name AS item_type_name

                                                 FROM legislative_items li
                                                 INNER JOIN legislative_item_types lit
                                                    ON lit.id = li.item_type_id

                                                 WHERE li.deleted_at IS NULL
                                                   AND lit.code IN (
                                                        'ordinance',
                                                        'resolution'
                                                   )

                                                 ORDER BY li.updated_at DESC
                                                 LIMIT 300"
                                            );

                                            $candidateRows =
                                                $candidateStmt->fetchAll();
                                            ?>

                                            <?php foreach ($candidateRows as $candidate): ?>
                                                <option
                                                    value="<?= (int)$candidate['id'] ?>"
                                                    data-reference="<?= e($candidate['reference_number']) ?>"
                                                    data-type="<?= e($candidate['item_type_name']) ?>"
                                                    data-title="<?= e($candidate['title']) ?>"
                                                    data-status="<?= e($candidate['current_status']) ?>"
                                                    data-current-priority="<?= e($candidate['priority_level']) ?>"
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
                                            class="priority-measure-preview"
                                            id="priorityMeasurePreview"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>

                                            <div>
                                                <strong>
                                                    No measure selected
                                                </strong>

                                                <span>
                                                    Select a legislative record
                                                    to review its current
                                                    information.
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Evaluation Date
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="evaluation_date"
                                            value="<?= date('Y-m-d') ?>"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Agenda Cycle
                                        </label>

                                        <select
                                            class="form-select"
                                            name="agenda_cycle"
                                        >
                                            <option value="Current Session">
                                                Current Session
                                            </option>
                                            <option value="Next Session">
                                                Next Session
                                            </option>
                                            <option value="Annual Legislative Agenda">
                                                Annual Legislative Agenda
                                            </option>
                                            <option value="Special Legislative Agenda">
                                                Special Legislative Agenda
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Evaluating Office
                                        </label>

                                        <select
                                            class="form-select"
                                            name="evaluating_office_id"
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
                                </div>
                            </div>

                            <div
                                class="priority-form-section"
                                data-priority-section="2"
                            >
                                <div class="priority-section-heading">
                                    <h6>Priority Evaluation Criteria</h6>

                                    <p>
                                        Rate each criterion from 1 to 5.
                                        The interface calculates an initial
                                        weighted recommendation score.
                                    </p>
                                </div>

                                <div class="priority-score-layout">
                                    <div class="priority-criteria-list">
                                        <?php
                                        $criteria = [
                                            [
                                                'key' => 'urgency',
                                                'label' => 'Urgency and Time Sensitivity',
                                                'description' => 'How quickly must the measure be acted upon?',
                                                'weight' => 25,
                                            ],
                                            [
                                                'key' => 'public_impact',
                                                'label' => 'Public and Social Impact',
                                                'description' => 'How significant is the expected benefit to constituents?',
                                                'weight' => 25,
                                            ],
                                            [
                                                'key' => 'legal_readiness',
                                                'label' => 'Legal and Policy Readiness',
                                                'description' => 'How complete and legally prepared is the proposal?',
                                                'weight' => 20,
                                            ],
                                            [
                                                'key' => 'feasibility',
                                                'label' => 'Financial and Operational Feasibility',
                                                'description' => 'Can the measure be funded and implemented?',
                                                'weight' => 15,
                                            ],
                                            [
                                                'key' => 'alignment',
                                                'label' => 'Strategic and Executive Alignment',
                                                'description' => 'How well does it align with approved plans and executive priorities?',
                                                'weight' => 15,
                                            ],
                                        ];
                                        ?>

                                        <?php foreach ($criteria as $criterion): ?>
                                            <div class="priority-criterion-card">
                                                <div class="criterion-copy">
                                                    <strong>
                                                        <?= e($criterion['label']) ?>
                                                    </strong>

                                                    <span>
                                                        <?= e($criterion['description']) ?>
                                                    </span>

                                                    <small>
                                                        Weight:
                                                        <?= (int)$criterion['weight'] ?>%
                                                    </small>
                                                </div>

                                                <div class="criterion-rating">
                                                    <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                                                        <label>
                                                            <input
                                                                type="radio"
                                                                name="<?= e($criterion['key']) ?>"
                                                                value="<?= $rating ?>"
                                                                data-score-field
                                                                data-score-weight="<?= (int)$criterion['weight'] ?>"
                                                                <?= $rating === 3 ? 'checked' : '' ?>
                                                            >

                                                            <span><?= $rating ?></span>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <aside class="priority-score-panel">
                                        <span class="score-label">
                                            Initial Weighted Score
                                        </span>

                                        <strong id="priorityScoreValue">
                                            60
                                        </strong>

                                        <span class="score-out-of">
                                            out of 100
                                        </span>

                                        <div class="priority-score-meter">
                                            <div
                                                id="priorityScoreBar"
                                                style="width:60%"
                                            ></div>
                                        </div>

                                        <span
                                            class="priority-score-recommendation"
                                            id="priorityScoreRecommendation"
                                        >
                                            Recommended: Normal Priority
                                        </span>

                                        <p>
                                            This is an interface-only
                                            recommendation. Final priority
                                            remains subject to legislative
                                            review and approval.
                                        </p>
                                    </aside>
                                </div>
                            </div>

                            <div
                                class="priority-form-section"
                                data-priority-section="3"
                            >
                                <div class="priority-section-heading">
                                    <h6>Recommended Ranking and Schedule</h6>

                                    <p>
                                        Assign the proposed priority,
                                        agenda placement, and target period.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Recommended Priority
                                        </label>

                                        <select
                                            class="form-select"
                                            name="recommended_priority"
                                            id="recommendedPriority"
                                        >
                                            <option value="Urgent">Urgent</option>
                                            <option value="High">High</option>
                                            <option value="Normal" selected>
                                                Normal
                                            </option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Proposed Rank
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control"
                                            name="proposed_rank"
                                            min="1"
                                            placeholder="Example: 1"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Target Legislative Period
                                        </label>

                                        <select
                                            class="form-select"
                                            name="target_period"
                                        >
                                            <option value="Immediate">
                                                Immediate
                                            </option>
                                            <option value="Within 30 Days">
                                                Within 30 Days
                                            </option>
                                            <option value="Within 60 Days">
                                                Within 60 Days
                                            </option>
                                            <option value="Within 90 Days">
                                                Within 90 Days
                                            </option>
                                            <option value="Current Year">
                                                Current Year
                                            </option>
                                            <option value="Next Legislative Year">
                                                Next Legislative Year
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Target Calendar Date
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="target_calendar_date"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Responsible Committee or Office
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="responsible_unit"
                                            placeholder="Committee, office, or responsible unit"
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Scheduling Considerations
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="scheduling_considerations"
                                            rows="4"
                                            placeholder="Record required hearings, committee availability, dependencies, or scheduling constraints."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="priority-form-section"
                                data-priority-section="4"
                            >
                                <div class="priority-section-heading">
                                    <h6>Priority Recommendation</h6>

                                    <p>
                                        Record the justification, endorsement,
                                        and supporting documents.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">
                                            Priority Justification
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="priority_justification"
                                            rows="5"
                                            placeholder="Explain why the measure should receive the recommended priority."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Recommended Action
                                        </label>

                                        <select
                                            class="form-select"
                                            name="recommended_action"
                                        >
                                            <option value="Include in Agenda">
                                                Include in Agenda
                                            </option>
                                            <option value="Include with Conditions">
                                                Include with Conditions
                                            </option>
                                            <option value="Return for Further Study">
                                                Return for Further Study
                                            </option>
                                            <option value="Defer to Next Cycle">
                                                Defer to Next Cycle
                                            </option>
                                            <option value="Remove from Priority List">
                                                Remove from Priority List
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Recommended By
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="recommended_by"
                                            placeholder="Name, office, committee, or council"
                                        >
                                    </div>
                                </div>

                                <div class="priority-checklist">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="check_documents"
                                            value="1"
                                        >

                                        <span>
                                            Required legislative documents
                                            are complete.
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="check_legal"
                                            value="1"
                                        >

                                        <span>
                                            Legal or policy review has been
                                            considered.
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="check_schedule"
                                            value="1"
                                        >

                                        <span>
                                            Calendar and committee capacity
                                            have been reviewed.
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="check_executive"
                                            value="1"
                                        >

                                        <span>
                                            Executive-legislative alignment
                                            has been assessed when applicable.
                                        </span>
                                    </label>
                                </div>

                                <div class="priority-upload-area">
                                    <i class="bi bi-cloud-arrow-up"></i>

                                    <strong>
                                        Upload supporting documents
                                    </strong>

                                    <span>
                                        Evaluation sheets, legal reviews,
                                        endorsements, and supporting files
                                    </span>

                                    <input
                                        type="file"
                                        name="documents[]"
                                        id="priorityDocuments"
                                        multiple
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                    >
                                </div>

                                <div
                                    id="prioritySelectedFiles"
                                    class="priority-file-list"
                                ></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary me-auto"
                                id="btnPreviousPriorityStep"
                                disabled
                            >
                                <i class="bi bi-arrow-left"></i>
                                Previous
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="btnSavePriorityDraft"
                            >
                                <i class="bi bi-floppy"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnNextPriorityStep"
                            >
                                Next
                                <i class="bi bi-arrow-right"></i>
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary d-none"
                                id="btnSubmitPriority"
                            >
                                <i class="bi bi-check-circle"></i>
                                Save Priority
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
