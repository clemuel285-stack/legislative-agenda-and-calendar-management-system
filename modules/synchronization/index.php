<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Executive-Legislative Synchronization';
$activeMenu = 'synchronization';
$extraCss   = [appUrl('assets/css/synchronization.css')];
$extraJs    = [appUrl('assets/js/synchronization.js')];

$pdo = db();

$offices = [];
$committees = [];

try {
    $offices = $pdo->query(
        "SELECT id, name FROM offices ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log(
        '[LACMS Synchronization Offices] ' .
        $exception->getMessage()
    );
}

try {
    $committees = $pdo->query(
        "SELECT id, name FROM committees ORDER BY name"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log(
        '[LACMS Synchronization Committees] ' .
        $exception->getMessage()
    );
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
        FIELD(
            li.priority_level,
            'Urgent',
            'High',
            'Normal',
            'Low'
        ),
        li.updated_at DESC
     LIMIT 300"
);

$candidateRows = $stmt->fetchAll();

$urgent = 0;
$high = 0;
$syncReady = 0;

$syncReadyStatuses = [
    'Under Review',
    'Committee Endorsed',
    'For Approval',
    'Approved',
    'Enacted',
    'For Publication',
    'Published',
    'Under Implementation',
];

foreach ($candidateRows as $row) {
    $urgent += $row['priority_level'] === 'Urgent' ? 1 : 0;
    $high += $row['priority_level'] === 'High' ? 1 : 0;

    if (
        in_array(
            $row['current_status'],
            $syncReadyStatuses,
            true
        )
    ) {
        $syncReady++;
    }
}

$cards = [
    [
        'value' => count($candidateRows),
        'label' => 'Synchronization Candidates',
        'icon'  => 'bi-files',
        'class' => '',
    ],
    [
        'value' => $urgent,
        'label' => 'Urgent Measures',
        'icon'  => 'bi-exclamation-octagon',
        'class' => 'urgent',
    ],
    [
        'value' => $high,
        'label' => 'High-Priority Measures',
        'icon'  => 'bi-arrow-up-circle',
        'class' => 'high',
    ],
    [
        'value' => $syncReady,
        'label' => 'Synchronization-Ready',
        'icon'  => 'bi-check2-square',
        'class' => '',
    ],
    [
        'value' => 0,
        'label' => 'Active Sync Records',
        'icon'  => 'bi-arrow-left-right',
        'class' => '',
    ],
    [
        'value' => 0,
        'label' => 'Unresolved Conflicts',
        'icon'  => 'bi-exclamation-triangle',
        'class' => 'conflict',
    ],
];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="sync-page-header">
            <div>
                <div class="sync-eyebrow">
                    <i class="bi bi-arrow-left-right"></i>
                    Intergovernmental Legislative Coordination
                </div>

                <h1>Executive-Legislative Synchronization</h1>

                <p>
                    Align legislative priorities, executive programs,
                    joint schedules, policy positions, implementation
                    responsibilities, and coordinated actions.
                </p>
            </div>

            <div class="sync-header-actions">
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
                    Print Queue
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnCreateSynchronization"
                >
                    <i class="bi bi-plus-circle"></i>
                    Create Sync Record
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php foreach ($cards as $card): ?>
                <div class="col-sm-6 col-xl">
                    <div class="sync-summary-card <?= e($card['class']) ?>">
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
                <section class="sync-panel h-100">
                    <div class="sync-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-diagram-3"></i>
                                Executive-Legislative Alignment Board
                            </h2>

                            <p>
                                Browser-preview synchronization records
                                appear here until backend persistence is
                                connected.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-light"
                            id="btnQuickSynchronization"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Quick Sync
                        </button>
                    </div>

                    <div
                        id="syncBoard"
                        class="sync-board"
                    >
                        <div class="sync-board-column">
                            <div class="sync-board-column-heading">
                                <span class="column-icon legislative">
                                    <i class="bi bi-bank"></i>
                                </span>

                                <div>
                                    <strong>Legislative Priorities</strong>
                                    <small>Selected ordinances and resolutions</small>
                                </div>
                            </div>

                            <div
                                id="syncLegislativeColumn"
                                class="sync-board-column-body"
                            >
                                <div class="sync-board-empty">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>No linked legislative measures</span>
                                </div>
                            </div>
                        </div>

                        <div class="sync-board-column">
                            <div class="sync-board-column-heading">
                                <span class="column-icon executive">
                                    <i class="bi bi-building"></i>
                                </span>

                                <div>
                                    <strong>Executive Commitments</strong>
                                    <small>Programs, plans, and policy positions</small>
                                </div>
                            </div>

                            <div
                                id="syncExecutiveColumn"
                                class="sync-board-column-body"
                            >
                                <div class="sync-board-empty">
                                    <i class="bi bi-clipboard-data"></i>
                                    <span>No executive commitment recorded</span>
                                </div>
                            </div>
                        </div>

                        <div class="sync-board-column">
                            <div class="sync-board-column-heading">
                                <span class="column-icon joint">
                                    <i class="bi bi-people"></i>
                                </span>

                                <div>
                                    <strong>Joint Actions</strong>
                                    <small>Agreed activities and next steps</small>
                                </div>
                            </div>

                            <div
                                id="syncJointColumn"
                                class="sync-board-column-body"
                            >
                                <div class="sync-board-empty">
                                    <i class="bi bi-check2-square"></i>
                                    <span>No joint action recorded</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="sync-panel h-100">
                    <div class="sync-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clipboard-check"></i>
                                Alignment Checklist
                            </h2>

                            <p>
                                Recommended synchronization requirements.
                            </p>
                        </div>
                    </div>

                    <div class="sync-checklist-panel">
                        <?php
                        $checklist = [
                            'Legislative objective clearly identified',
                            'Executive program or policy counterpart identified',
                            'Budget and resource implications reviewed',
                            'Legal and policy compatibility assessed',
                            'Responsible offices and committees assigned',
                            'Joint schedule and decision date agreed',
                        ];
                        ?>

                        <?php foreach ($checklist as $item): ?>
                            <label>
                                <input
                                    type="checkbox"
                                    class="sync-check"
                                >
                                <span><?= e($item) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="sync-progress-block">
                        <div>
                            <span>Alignment Readiness</span>
                            <strong id="syncChecklistValue">0%</strong>
                        </div>

                        <div class="sync-progress-track">
                            <span id="syncChecklistBar"></span>
                        </div>
                    </div>

                    <div class="sync-side-action">
                        <button
                            type="button"
                            class="btn btn-outline-primary w-100"
                            id="btnOpenConflictReview"
                        >
                            <i class="bi bi-exclamation-triangle"></i>
                            Review Alignment Conflicts
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <section class="sync-filter-panel">
            <div class="row g-3">
                <div class="col-lg-4">
                    <label
                        class="form-label"
                        for="syncSearch"
                    >
                        Search Synchronization Queue
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="search"
                            id="syncSearch"
                            class="form-control"
                            placeholder="Reference, title, office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        class="form-label"
                        for="syncTypeFilter"
                    >
                        Record Type
                    </label>

                    <select
                        id="syncTypeFilter"
                        class="form-select"
                    >
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label
                        class="form-label"
                        for="syncPriorityFilter"
                    >
                        Priority
                    </label>

                    <select
                        id="syncPriorityFilter"
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
                        class="form-label"
                        for="syncStatusFilter"
                    >
                        Workflow Status
                    </label>

                    <select
                        id="syncStatusFilter"
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
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        id="btnResetSyncFilters"
                        title="Reset filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="sync-panel">
            <div class="sync-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-inboxes"></i>
                        Executive-Legislative Synchronization Queue
                    </h2>

                    <p>
                        Legislative measures available for executive
                        coordination, policy alignment, and joint planning.
                    </p>
                </div>
            </div>

            <?php include __DIR__ . '/table.php'; ?>
        </section>

        <div
            class="modal fade"
            id="synchronizationModal"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="synchronizationForm">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrfToken()) ?>"
                        >

                        <div class="modal-header">
                            <div>
                                <h5
                                    class="modal-title"
                                    id="synchronizationModalTitle"
                                >
                                    Create Executive-Legislative Sync Record
                                </h5>

                                <small class="text-muted">
                                    Link legislative priorities with
                                    executive programs, evaluate alignment,
                                    and define joint actions.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>
                        </div>

                        <div class="modal-body">
                            <div class="sync-form-steps">
                                <?php
                                $steps = [
                                    1 => 'Link Priorities',
                                    2 => 'Alignment Assessment',
                                    3 => 'Joint Schedule & Actions',
                                    4 => 'Agreement & Communication',
                                ];
                                ?>

                                <?php foreach ($steps as $number => $label): ?>
                                    <button
                                        type="button"
                                        class="sync-step <?= $number === 1 ? 'active' : '' ?>"
                                        data-sync-step="<?= $number ?>"
                                    >
                                        <span><?= $number ?></span>
                                        <?= e($label) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div
                                class="sync-form-section active"
                                data-sync-form-section="1"
                            >
                                <div class="sync-section-heading">
                                    <h6>Link Legislative and Executive Priorities</h6>

                                    <p>
                                        Select the legislative measure and
                                        describe its executive counterpart,
                                        program, policy, or commitment.
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
                                            id="syncMeasureSelect"
                                        >
                                            <option value="">
                                                -- Select legislative measure --
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
                                            class="sync-measure-preview"
                                            id="syncMeasurePreview"
                                        >
                                            <i class="bi bi-file-earmark-text"></i>

                                            <div>
                                                <strong>No legislative measure selected</strong>

                                                <span>
                                                    Select an ordinance or
                                                    resolution to begin
                                                    synchronization.
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Executive Program, Policy, or Commitment
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="executive_program"
                                            id="syncExecutiveProgram"
                                            placeholder="Executive program, policy, plan, project, or commitment"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Lead Executive Office
                                        </label>

                                        <select
                                            class="form-select"
                                            name="executive_office_id"
                                            id="syncExecutiveOffice"
                                        >
                                            <option value="">
                                                -- Select executive office --
                                            </option>

                                            <?php foreach ($offices as $office): ?>
                                                <option
                                                    value="<?= (int)$office['id'] ?>"
                                                    data-name="<?= e($office['name']) ?>"
                                                >
                                                    <?= e($office['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Legislative Objective
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="legislative_objective"
                                            rows="4"
                                            placeholder="Describe the legislative objective and intended result."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Executive Objective
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="executive_objective"
                                            rows="4"
                                            placeholder="Describe the executive program objective and expected implementation result."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="sync-form-section"
                                data-sync-form-section="2"
                            >
                                <div class="sync-section-heading">
                                    <h6>Alignment Assessment</h6>

                                    <p>
                                        Rate each alignment criterion from
                                        1 to 5. The interface calculates an
                                        initial weighted alignment score.
                                    </p>
                                </div>

                                <div class="sync-assessment-layout">
                                    <div class="sync-criteria-list">
                                        <?php
                                        $criteria = [
                                            [
                                                'key' => 'strategic',
                                                'label' => 'Strategic Priority Alignment',
                                                'description' => 'Alignment with approved plans, priorities, and development goals.',
                                                'weight' => 25,
                                            ],
                                            [
                                                'key' => 'legal',
                                                'label' => 'Legal and Policy Compatibility',
                                                'description' => 'Consistency with applicable law, policy, and executive authority.',
                                                'weight' => 20,
                                            ],
                                            [
                                                'key' => 'budget',
                                                'label' => 'Budget and Resource Alignment',
                                                'description' => 'Availability of funding, staff, systems, and operational resources.',
                                                'weight' => 20,
                                            ],
                                            [
                                                'key' => 'implementation',
                                                'label' => 'Implementation Readiness',
                                                'description' => 'Readiness of offices, procedures, timelines, and accountable units.',
                                                'weight' => 20,
                                            ],
                                            [
                                                'key' => 'urgency',
                                                'label' => 'Urgency and Timing Compatibility',
                                                'description' => 'Compatibility of legislative timing with executive implementation needs.',
                                                'weight' => 15,
                                            ],
                                        ];
                                        ?>

                                        <?php foreach ($criteria as $criterion): ?>
                                            <div class="sync-criterion-card">
                                                <div class="sync-criterion-copy">
                                                    <strong><?= e($criterion['label']) ?></strong>
                                                    <span><?= e($criterion['description']) ?></span>
                                                    <small>Weight: <?= (int)$criterion['weight'] ?>%</small>
                                                </div>

                                                <div class="sync-criterion-rating">
                                                    <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                                                        <label>
                                                            <input
                                                                type="radio"
                                                                name="<?= e($criterion['key']) ?>"
                                                                value="<?= $rating ?>"
                                                                data-sync-score
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

                                    <aside class="sync-score-panel">
                                        <span class="score-label">
                                            Initial Alignment Score
                                        </span>

                                        <strong id="syncScoreValue">60</strong>

                                        <span class="score-out-of">
                                            out of 100
                                        </span>

                                        <div class="sync-score-meter">
                                            <div
                                                id="syncScoreBar"
                                                style="width:60%"
                                            ></div>
                                        </div>

                                        <span
                                            class="sync-score-result"
                                            id="syncScoreResult"
                                        >
                                            Partially Aligned
                                        </span>

                                        <p>
                                            The score is a UI-only
                                            recommendation. Final alignment
                                            requires authorized executive
                                            and legislative review.
                                        </p>
                                    </aside>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Areas of Alignment
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="areas_of_alignment"
                                            rows="4"
                                            placeholder="Record common objectives, compatible policies, available resources, and shared outcomes."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Gaps, Conflicts, or Unresolved Issues
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="alignment_gaps"
                                            rows="4"
                                            placeholder="Record policy differences, funding gaps, scheduling conflicts, legal concerns, or unresolved issues."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="sync-form-section"
                                data-sync-form-section="3"
                            >
                                <div class="sync-section-heading">
                                    <h6>Joint Schedule and Actions</h6>

                                    <p>
                                        Assign accountable units, schedule
                                        coordination, and define joint action
                                        items and expected outputs.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Lead Legislative Committee
                                        </label>

                                        <select
                                            class="form-select"
                                            name="legislative_committee_id"
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
                                            Supporting Legislative Office
                                        </label>

                                        <select
                                            class="form-select"
                                            name="legislative_office_id"
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

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Executive Focal Person
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="executive_focal_person"
                                            placeholder="Executive focal person"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Legislative Focal Person
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="legislative_focal_person"
                                            placeholder="Legislative focal person"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Coordination Date
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="coordination_date"
                                            id="syncCoordinationDate"
                                            value="<?= date('Y-m-d') ?>"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Target Decision Date
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            name="target_decision_date"
                                            id="syncTargetDate"
                                            value="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Coordination Mode
                                        </label>

                                        <select
                                            class="form-select"
                                            name="coordination_mode"
                                        >
                                            <option>Joint Meeting</option>
                                            <option>Technical Working Group</option>
                                            <option>Written Coordination</option>
                                            <option>Executive Briefing</option>
                                            <option>Legislative Conference</option>
                                            <option>Hybrid Coordination</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Joint Activity Status
                                        </label>

                                        <select
                                            class="form-select"
                                            name="joint_activity_status"
                                            id="syncActivityStatus"
                                        >
                                            <option>Draft</option>
                                            <option selected>For Coordination</option>
                                            <option>In Discussion</option>
                                            <option>Agreement Reached</option>
                                            <option>On Hold</option>
                                            <option>Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    class="sync-action-builder"
                                    id="syncActionBuilder"
                                >
                                    <div
                                        class="sync-action-item"
                                        data-sync-action-item
                                    >
                                        <span class="action-order">1</span>

                                        <div class="action-fields">
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="action_title[]"
                                                placeholder="Joint action item"
                                            >

                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="action_owner[]"
                                                        placeholder="Responsible office or person"
                                                    >
                                                </div>

                                                <div class="col-md-4">
                                                    <input
                                                        type="date"
                                                        class="form-control"
                                                        name="action_due_date[]"
                                                    >
                                                </div>

                                                <div class="col-md-2">
                                                    <select
                                                        class="form-select"
                                                        name="action_status[]"
                                                    >
                                                        <option>Open</option>
                                                        <option>In Progress</option>
                                                        <option>Completed</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="action-remove-button"
                                            data-remove-sync-action
                                            title="Remove action item"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    id="btnAddSyncAction"
                                >
                                    <i class="bi bi-plus-circle"></i>
                                    Add Joint Action
                                </button>

                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Dependencies and Required Inputs
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="dependencies"
                                            rows="4"
                                            placeholder="List required data, documents, approvals, budget inputs, or technical studies."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Expected Joint Output
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="expected_joint_output"
                                            rows="4"
                                            placeholder="Agreement, revised proposal, policy recommendation, funding commitment, schedule, or implementation plan."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="sync-form-section"
                                data-sync-form-section="4"
                            >
                                <div class="sync-section-heading">
                                    <h6>Agreement and Communication</h6>

                                    <p>
                                        Record the synchronization outcome,
                                        agreed actions, remaining differences,
                                        approval authorities, and communication
                                        requirements.
                                    </p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Alignment Status
                                        </label>

                                        <select
                                            class="form-select"
                                            name="alignment_status"
                                            id="syncAlignmentStatus"
                                        >
                                            <option>Aligned</option>
                                            <option selected>Partially Aligned</option>
                                            <option>Requires Discussion</option>
                                            <option>Conflict</option>
                                            <option>Deferred</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Executive Approval Authority
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="executive_approval_authority"
                                            placeholder="Office or approving official"
                                        >
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">
                                            Legislative Approval Authority
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="legislative_approval_authority"
                                            placeholder="Committee, council, or presiding authority"
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Agreed Actions and Commitments
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="agreed_actions"
                                            rows="5"
                                            placeholder="Record commitments, agreed revisions, funding support, schedule, and implementation responsibilities."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Remaining Differences or Conditions
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="remaining_differences"
                                            rows="5"
                                            placeholder="Record unresolved issues, reservations, conditions, or items requiring further decision."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Internal Coordination Note
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="internal_note"
                                            rows="4"
                                            placeholder="Internal instructions, next steps, and authorized coordination notes."
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Public Communication Summary
                                        </label>

                                        <textarea
                                            class="form-control"
                                            name="public_summary"
                                            rows="4"
                                            placeholder="Approved public-facing summary of the synchronized position or agreement."
                                        ></textarea>
                                    </div>
                                </div>

                                <div class="sync-option-list">
                                    <label>
                                        <input type="checkbox" checked>
                                        <span>
                                            Notify executive and legislative
                                            focal persons after saving.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox" checked>
                                        <span>
                                            Create calendar activities for
                                            approved joint meetings.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>
                                            Create deadline-tracking items
                                            from approved joint actions.
                                        </span>
                                    </label>

                                    <label>
                                        <input type="checkbox">
                                        <span>
                                            Publish the approved public
                                            communication summary.
                                        </span>
                                    </label>
                                </div>

                                <div class="sync-upload-area">
                                    <i class="bi bi-cloud-arrow-up"></i>

                                    <strong>
                                        Upload synchronization documents
                                    </strong>

                                    <span>
                                        Executive plans, policy notes,
                                        legal reviews, budget inputs,
                                        agreements, minutes, and signed files
                                    </span>

                                    <input
                                        type="file"
                                        name="documents[]"
                                        id="syncDocuments"
                                        multiple
                                    >
                                </div>

                                <div
                                    id="syncSelectedFiles"
                                    class="sync-file-list"
                                ></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary me-auto"
                                id="btnPreviousSyncStep"
                                disabled
                            >
                                <i class="bi bi-arrow-left"></i>
                                Previous
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                id="btnSaveSyncDraft"
                            >
                                <i class="bi bi-floppy"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="btn btn-primary"
                                id="btnNextSyncStep"
                            >
                                Next
                                <i class="bi bi-arrow-right"></i>
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary d-none"
                                id="btnSubmitSynchronization"
                            >
                                <i class="bi bi-check2-circle"></i>
                                Save Synchronization
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
