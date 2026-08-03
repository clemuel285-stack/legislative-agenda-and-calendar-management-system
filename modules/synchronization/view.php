<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(
        appUrl('modules/synchronization/index.php')
    );
}

$stmt = db()->prepare(
    "SELECT
        li.id,
        li.reference_number,
        li.title,
        li.current_status,
        li.priority_level,
        li.created_at,
        li.updated_at,
        lit.name AS item_type_name,
        o.name AS originating_office

     FROM legislative_items li
     INNER JOIN legislative_item_types lit
        ON lit.id = li.item_type_id
     LEFT JOIN offices o
        ON o.id = li.originating_office_id

     WHERE li.id = :id
       AND li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')
     LIMIT 1"
);

$stmt->execute([
    ':id' => $id,
]);

$item = $stmt->fetch();

if (!$item) {
    setFlash(
        'warning',
        'Legislative record not found.'
    );

    redirect(
        appUrl('modules/synchronization/index.php')
    );
}

$pageTitle  = 'Synchronization ' . $item['reference_number'];
$activeMenu = 'synchronization';

$extraCss = [
    appUrl('assets/css/synchronization.css'),
];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="sync-detail-header">
            <div>
                <a
                    href="index.php"
                    class="sync-back-link"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Synchronization
                </a>

                <div class="sync-detail-reference">
                    <?= e($item['reference_number']) ?>
                </div>

                <h1><?= e($item['title']) ?></h1>

                <div class="sync-detail-meta">
                    <span>
                        <i class="bi bi-file-earmark-text"></i>
                        <?= e($item['item_type_name']) ?>
                    </span>

                    <span>
                        <i class="bi bi-building"></i>
                        <?= e(
                            $item['originating_office']
                            ?: 'Not assigned'
                        ) ?>
                    </span>
                </div>
            </div>

            <div class="sync-detail-actions">
                <span
                    class="sync-priority-badge
                    <?= e(strtolower(
                        $item['priority_level']
                    )) ?>"
                >
                    <?= e($item['priority_level']) ?>
                </span>

                <span class="sync-status-badge">
                    <?= e($item['current_status']) ?>
                </span>

                <a
                    href="index.php?sync=<?= (int)$item['id'] ?>"
                    class="btn btn-primary"
                >
                    <i class="bi bi-arrow-left-right"></i>
                    Create Sync Record
                </a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <section class="sync-panel h-100">
                    <div class="sync-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-info-circle"></i>
                                Legislative Record Information
                            </h2>
                        </div>
                    </div>

                    <div class="sync-information-grid">
                        <div>
                            <span>Reference Number</span>
                            <strong>
                                <?= e($item['reference_number']) ?>
                            </strong>
                        </div>

                        <div>
                            <span>Record Type</span>
                            <strong>
                                <?= e($item['item_type_name']) ?>
                            </strong>
                        </div>

                        <div>
                            <span>Originating Office</span>
                            <strong>
                                <?= e(
                                    $item['originating_office']
                                    ?: 'Not assigned'
                                ) ?>
                            </strong>
                        </div>

                        <div>
                            <span>Priority</span>
                            <strong>
                                <?= e($item['priority_level']) ?>
                            </strong>
                        </div>

                        <div>
                            <span>Workflow Status</span>
                            <strong>
                                <?= e($item['current_status']) ?>
                            </strong>
                        </div>

                        <div>
                            <span>Last Updated</span>
                            <strong>
                                <?= e(formatDateTime(
                                    $item['updated_at']
                                )) ?>
                            </strong>
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
                                Synchronization Readiness
                            </h2>
                        </div>
                    </div>

                    <div class="sync-readiness-list">
                        <div>
                            <i class="bi bi-check-circle"></i>
                            <span>Legislative record available</span>
                        </div>

                        <div>
                            <i class="bi bi-check-circle"></i>
                            <span>Priority classification available</span>
                        </div>

                        <div class="pending">
                            <i class="bi bi-clock"></i>
                            <span>Executive counterpart pending</span>
                        </div>

                        <div class="pending">
                            <i class="bi bi-clock"></i>
                            <span>Joint action and agreement pending</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <?php
                $sections = [
                    [
                        'icon' => 'bi-building',
                        'title' => 'Executive Program or Commitment',
                        'empty_icon' => 'bi-clipboard-data',
                        'message' => 'Executive program, lead office, policy objective, and implementation commitment will appear here.',
                    ],
                    [
                        'icon' => 'bi-bar-chart-steps',
                        'title' => 'Alignment Assessment',
                        'empty_icon' => 'bi-graph-up-arrow',
                        'message' => 'Strategic, legal, budget, implementation, and timing alignment results will appear here.',
                    ],
                    [
                        'icon' => 'bi-check2-square',
                        'title' => 'Joint Actions and Schedule',
                        'empty_icon' => 'bi-calendar2-plus',
                        'message' => 'Joint meetings, focal persons, action items, owners, target dates, and expected outputs will appear here.',
                    ],
                ];
                ?>

                <?php foreach ($sections as $section): ?>
                    <section class="sync-panel mb-4">
                        <div class="sync-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="sync-detail-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <strong>No saved information yet</strong>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="sync-panel">
                    <div class="sync-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clock-history"></i>
                                Synchronization History
                            </h2>
                        </div>
                    </div>

                    <div class="sync-history-list">
                        <div class="sync-history-item">
                            <span>
                                <i class="bi bi-file-earmark-text"></i>
                            </span>

                            <div>
                                <strong>
                                    Legislative record created
                                </strong>

                                <small>
                                    <?= e(formatDateTime(
                                        $item['created_at']
                                    )) ?>
                                </small>
                            </div>
                        </div>

                        <div class="sync-history-item current">
                            <span>
                                <i class="bi bi-arrow-left-right"></i>
                            </span>

                            <div>
                                <strong>
                                    Available for executive-legislative
                                    synchronization
                                </strong>

                                <small>
                                    Alignment reviews, agreements,
                                    joint actions, conflicts, and approvals
                                    will appear after backend implementation.
                                </small>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <?php
                $sideSections = [
                    [
                        'icon' => 'bi-people',
                        'title' => 'Focal Persons and Offices',
                        'empty_icon' => 'bi-person-plus',
                        'message' => 'Executive and legislative focal persons, offices, and committees are pending.',
                    ],
                    [
                        'icon' => 'bi-exclamation-triangle',
                        'title' => 'Alignment Gaps and Conflicts',
                        'empty_icon' => 'bi-shield-exclamation',
                        'message' => 'No policy, legal, funding, resource, or scheduling conflict has been recorded.',
                    ],
                    [
                        'icon' => 'bi-patch-check',
                        'title' => 'Agreement and Approval',
                        'empty_icon' => 'bi-clipboard-check',
                        'message' => 'Agreed commitments, conditions, approval authorities, and public summary are pending.',
                    ],
                    [
                        'icon' => 'bi-paperclip',
                        'title' => 'Synchronization Documents',
                        'empty_icon' => 'bi-file-earmark-arrow-up',
                        'message' => 'No policy note, executive plan, legal review, agreement, minutes, or signed document has been uploaded.',
                    ],
                ];
                ?>

                <?php foreach ($sideSections as $section): ?>
                    <section class="sync-panel mb-4">
                        <div class="sync-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="sync-side-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
