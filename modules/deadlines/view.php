<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(appUrl('modules/deadlines/index.php'));
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

$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('warning', 'Legislative record not found.');
    redirect(appUrl('modules/deadlines/index.php'));
}

$pageTitle  = 'Deadline ' . $item['reference_number'];
$activeMenu = 'deadlines';
$extraCss   = [appUrl('assets/css/deadlines.css')];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="deadline-detail-header">
            <div>
                <a href="index.php" class="deadline-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Deadline Tracking
                </a>

                <div class="deadline-detail-reference">
                    <?= e($item['reference_number']) ?>
                </div>

                <h1><?= e($item['title']) ?></h1>

                <div class="deadline-detail-meta">
                    <span>
                        <i class="bi bi-file-earmark-text"></i>
                        <?= e($item['item_type_name']) ?>
                    </span>

                    <span>
                        <i class="bi bi-building"></i>
                        <?= e($item['originating_office'] ?: 'Not assigned') ?>
                    </span>
                </div>
            </div>

            <div class="deadline-detail-actions">
                <span
                    class="deadline-priority-badge
                    <?= e(strtolower($item['priority_level'])) ?>"
                >
                    <?= e($item['priority_level']) ?>
                </span>

                <span class="deadline-status-badge">
                    <?= e($item['current_status']) ?>
                </span>

                <a
                    href="index.php?create=<?= (int)$item['id'] ?>"
                    class="btn btn-primary"
                >
                    <i class="bi bi-alarm"></i>
                    Create Deadline
                </a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-info-circle"></i>
                                Legislative Record Information
                            </h2>
                        </div>
                    </div>

                    <div class="deadline-information-grid">
                        <div>
                            <span>Reference Number</span>
                            <strong><?= e($item['reference_number']) ?></strong>
                        </div>

                        <div>
                            <span>Record Type</span>
                            <strong><?= e($item['item_type_name']) ?></strong>
                        </div>

                        <div>
                            <span>Originating Office</span>
                            <strong><?= e($item['originating_office'] ?: 'Not assigned') ?></strong>
                        </div>

                        <div>
                            <span>Priority</span>
                            <strong><?= e($item['priority_level']) ?></strong>
                        </div>

                        <div>
                            <span>Workflow Status</span>
                            <strong><?= e($item['current_status']) ?></strong>
                        </div>

                        <div>
                            <span>Last Updated</span>
                            <strong><?= e(formatDateTime($item['updated_at'])) ?></strong>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clipboard-check"></i>
                                Tracking Readiness
                            </h2>
                        </div>
                    </div>

                    <div class="deadline-readiness-list">
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
                            <span>Deadline date and owner pending</span>
                        </div>

                        <div class="pending">
                            <i class="bi bi-clock"></i>
                            <span>Reminder and escalation rules pending</span>
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
                        'icon' => 'bi-calendar2-check',
                        'title' => 'Tracked Deadlines and Milestones',
                        'empty_icon' => 'bi-hourglass-split',
                        'message' => 'Due dates, status, progress, milestones, and responsible owners will appear here.'
                    ],
                    [
                        'icon' => 'bi-diagram-3',
                        'title' => 'Dependencies and Risks',
                        'empty_icon' => 'bi-shield-exclamation',
                        'message' => 'Dependencies, blockers, risks, and required prerequisite actions will appear here.'
                    ],
                ];
                ?>

                <?php foreach ($sections as $section): ?>
                    <section class="deadline-panel mb-4">
                        <div class="deadline-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="deadline-detail-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <strong>No saved information yet</strong>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="deadline-panel">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clock-history"></i>
                                Deadline History
                            </h2>
                        </div>
                    </div>

                    <div class="deadline-history-list">
                        <div class="deadline-history-item">
                            <span><i class="bi bi-file-earmark-text"></i></span>

                            <div>
                                <strong>Legislative record created</strong>
                                <small><?= e(formatDateTime($item['created_at'])) ?></small>
                            </div>
                        </div>

                        <div class="deadline-history-item current">
                            <span><i class="bi bi-alarm"></i></span>

                            <div>
                                <strong>Available for deadline tracking</strong>
                                <small>
                                    Reminder, escalation, status, completion,
                                    and evidence history will appear later.
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
                        'icon' => 'bi-person-check',
                        'title' => 'Ownership',
                        'empty_icon' => 'bi-person-plus',
                        'message' => 'Primary owner, responsible office, committee, and supporting units are pending.'
                    ],
                    [
                        'icon' => 'bi-bell',
                        'title' => 'Reminders and Escalation',
                        'empty_icon' => 'bi-bell-slash',
                        'message' => 'Reminder recipients, escalation thresholds, and overdue behavior are pending.'
                    ],
                    [
                        'icon' => 'bi-paperclip',
                        'title' => 'Completion Evidence',
                        'empty_icon' => 'bi-file-earmark-arrow-up',
                        'message' => 'No submission receipt, approval, report, or completion document has been uploaded.'
                    ],
                ];
                ?>

                <?php foreach ($sideSections as $section): ?>
                    <section class="deadline-panel mb-4">
                        <div class="deadline-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="deadline-side-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
