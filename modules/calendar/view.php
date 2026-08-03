<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(appUrl('modules/calendar/index.php'));
}

$pdo = db();

$stmt = $pdo->prepare(
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
     WHERE li.id = :id
       AND li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')
     LIMIT 1"
);

$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('warning', 'Legislative record not found.');
    redirect(appUrl('modules/calendar/index.php'));
}

$pageTitle  = 'Schedule ' . $item['reference_number'];
$activeMenu = 'calendar';
$extraCss   = [appUrl('assets/css/calendar.css')];
$extraJs    = [appUrl('assets/js/calendar.js')];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">
        <section class="calendar-detail-header">
            <div>
                <a href="index.php" class="calendar-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Calendar Scheduling
                </a>

                <div class="calendar-detail-reference">
                    <?= e($item['reference_number']) ?>
                </div>

                <h1><?= e($item['title']) ?></h1>

                <div class="calendar-detail-meta">
                    <span>
                        <i class="bi bi-file-earmark-text"></i>
                        <?= e($item['item_type_name']) ?>
                    </span>

                    <span>
                        <i class="bi bi-building"></i>
                        <?= e(
                            $item['originating_office']
                            ?: 'Originating office not assigned'
                        ) ?>
                    </span>
                </div>
            </div>

            <div class="calendar-detail-actions">
                <span
                    class="calendar-priority-badge
                    <?= e(strtolower($item['priority_level'])) ?>"
                >
                    <?= e($item['priority_level']) ?>
                </span>

                <span class="calendar-status-badge">
                    <?= e($item['current_status']) ?>
                </span>

                <a
                    href="index.php?schedule=<?= (int)$item['id'] ?>"
                    class="btn btn-primary"
                >
                    <i class="bi bi-calendar-plus"></i>
                    Schedule Activity
                </a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <section class="calendar-panel h-100">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-info-circle"></i>
                                Legislative Record Information
                            </h2>
                        </div>
                    </div>

                    <div class="calendar-information-grid">
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
                            <strong>
                                <?= e(
                                    $item['originating_office']
                                    ?: 'Not assigned'
                                ) ?>
                            </strong>
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
                            <strong>
                                <?= e(formatDateTime($item['updated_at'])) ?>
                            </strong>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="calendar-panel h-100">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-calendar-check"></i>
                                Calendar Readiness
                            </h2>
                        </div>
                    </div>

                    <div class="calendar-readiness-list">
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
                            <span>Saved activity not yet connected</span>
                        </div>

                        <div class="pending">
                            <i class="bi bi-clock"></i>
                            <span>Venue and participants pending</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <?php
                $emptyPanels = [
                    [
                        'title' => 'Scheduled Activities',
                        'icon'  => 'bi-calendar-event',
                        'empty_icon' => 'bi-calendar2-plus',
                        'message' => 'No saved calendar activity',
                        'detail' => 'Event dates, times, venues, organizers, recurrence, and event status will appear here.',
                    ],
                    [
                        'title' => 'Meeting Agenda',
                        'icon'  => 'bi-card-checklist',
                        'empty_icon' => 'bi-list-check',
                        'message' => 'No agenda items recorded',
                        'detail' => 'Agenda items, descriptions, expected outputs, and preparation notes will appear here.',
                    ],
                ];
                ?>

                <?php foreach ($emptyPanels as $panel): ?>
                    <section class="calendar-panel mb-4">
                        <div class="calendar-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($panel['icon']) ?>"></i>
                                    <?= e($panel['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="calendar-detail-empty">
                            <i class="bi <?= e($panel['empty_icon']) ?>"></i>
                            <strong><?= e($panel['message']) ?></strong>
                            <span><?= e($panel['detail']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="calendar-panel">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clock-history"></i>
                                Scheduling History
                            </h2>
                        </div>
                    </div>

                    <div class="calendar-history-list">
                        <div class="calendar-history-item">
                            <span>
                                <i class="bi bi-file-earmark-text"></i>
                            </span>

                            <div>
                                <strong>Legislative record created</strong>
                                <small>
                                    <?= e(formatDateTime($item['created_at'])) ?>
                                </small>
                            </div>
                        </div>

                        <div class="calendar-history-item current">
                            <span>
                                <i class="bi bi-calendar-plus"></i>
                            </span>

                            <div>
                                <strong>Available for calendar placement</strong>
                                <small>
                                    Scheduling history will appear after
                                    calendar records are stored.
                                </small>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <?php
                $sidePanels = [
                    [
                        'title' => 'Coordination',
                        'icon' => 'bi-people',
                        'empty_icon' => 'bi-person-plus',
                        'text' => 'Organizer, committee, participants, and invitees have not yet been assigned.',
                    ],
                    [
                        'title' => 'Conflict Review',
                        'icon' => 'bi-calendar-x',
                        'empty_icon' => 'bi-shield-check',
                        'text' => 'Conflict checking will compare stored events, venues, participants, committees, and time ranges.',
                    ],
                    [
                        'title' => 'Calendar Documents',
                        'icon' => 'bi-paperclip',
                        'empty_icon' => 'bi-file-earmark-arrow-up',
                        'text' => 'No agenda, invitation, notice, or supporting document has been uploaded.',
                    ],
                ];
                ?>

                <?php foreach ($sidePanels as $panel): ?>
                    <section class="calendar-panel mb-4">
                        <div class="calendar-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($panel['icon']) ?>"></i>
                                    <?= e($panel['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="calendar-side-empty">
                            <i class="bi <?= e($panel['empty_icon']) ?>"></i>
                            <span><?= e($panel['text']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
