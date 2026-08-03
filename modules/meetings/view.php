<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect(appUrl('modules/meetings/index.php'));
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
    redirect(appUrl('modules/meetings/index.php'));
}

$pageTitle  = 'Meeting ' . $item['reference_number'];
$activeMenu = 'meetings';
$extraCss   = [appUrl('assets/css/meetings.css')];

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="meeting-detail-header">
            <div>
                <a href="index.php" class="meeting-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Meeting Coordination
                </a>

                <div class="meeting-detail-reference">
                    <?= e($item['reference_number']) ?>
                </div>

                <h1><?= e($item['title']) ?></h1>

                <div class="meeting-detail-meta">
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

            <div class="meeting-detail-actions">
                <span
                    class="meeting-priority-badge
                    <?= e(strtolower($item['priority_level'])) ?>"
                >
                    <?= e($item['priority_level']) ?>
                </span>

                <span class="meeting-status-badge">
                    <?= e($item['current_status']) ?>
                </span>

                <a
                    href="index.php?coordinate=<?= (int)$item['id'] ?>"
                    class="btn btn-primary"
                >
                    <i class="bi bi-people"></i>
                    Coordinate Meeting
                </a>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-info-circle"></i>
                                Legislative Record Information
                            </h2>
                        </div>
                    </div>

                    <div class="meeting-information-grid">
                        <div><span>Reference Number</span><strong><?= e($item['reference_number']) ?></strong></div>
                        <div><span>Record Type</span><strong><?= e($item['item_type_name']) ?></strong></div>
                        <div><span>Originating Office</span><strong><?= e($item['originating_office'] ?: 'Not assigned') ?></strong></div>
                        <div><span>Priority</span><strong><?= e($item['priority_level']) ?></strong></div>
                        <div><span>Workflow Status</span><strong><?= e($item['current_status']) ?></strong></div>
                        <div><span>Last Updated</span><strong><?= e(formatDateTime($item['updated_at'])) ?></strong></div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clipboard-check"></i>
                                Coordination Readiness
                            </h2>
                        </div>
                    </div>

                    <div class="meeting-readiness-list">
                        <div><i class="bi bi-check-circle"></i><span>Legislative record available</span></div>
                        <div><i class="bi bi-check-circle"></i><span>Priority classification available</span></div>
                        <div class="pending"><i class="bi bi-clock"></i><span>Meeting schedule pending</span></div>
                        <div class="pending"><i class="bi bi-clock"></i><span>Participants and agenda pending</span></div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <?php
                $sections = [
                    ['icon' => 'bi-calendar-event', 'title' => 'Coordinated Meetings', 'empty_icon' => 'bi-calendar2-plus', 'message' => 'Meeting date, time, mode, venue, organizer, committee, and status will appear here.'],
                    ['icon' => 'bi-card-checklist', 'title' => 'Agenda and Expected Outputs', 'empty_icon' => 'bi-list-check', 'message' => 'Agenda items, presenters, preparation requirements, and expected outputs will appear here.'],
                ];
                ?>

                <?php foreach ($sections as $section): ?>
                    <section class="meeting-panel mb-4">
                        <div class="meeting-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="meeting-detail-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <strong>No saved information yet</strong>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="meeting-panel">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-clock-history"></i>
                                Coordination History
                            </h2>
                        </div>
                    </div>

                    <div class="meeting-history-list">
                        <div class="meeting-history-item">
                            <span><i class="bi bi-file-earmark-text"></i></span>
                            <div>
                                <strong>Legislative record created</strong>
                                <small><?= e(formatDateTime($item['created_at'])) ?></small>
                            </div>
                        </div>

                        <div class="meeting-history-item current">
                            <span><i class="bi bi-people"></i></span>
                            <div>
                                <strong>Available for meeting coordination</strong>
                                <small>
                                    Invitations, confirmations, attendance,
                                    minutes, and action history will appear later.
                                </small>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <?php
                $sideSections = [
                    ['icon' => 'bi-person-check', 'title' => 'Participants', 'empty_icon' => 'bi-people', 'message' => 'Organizer, chairperson, secretary, required participants, and optional invitees are pending.'],
                    ['icon' => 'bi-check2-square', 'title' => 'Attendance and Minutes', 'empty_icon' => 'bi-clipboard-data', 'message' => 'Attendance, confirmations, minutes, decisions, and action items are pending.'],
                    ['icon' => 'bi-paperclip', 'title' => 'Meeting Documents', 'empty_icon' => 'bi-file-earmark-arrow-up', 'message' => 'No agenda, invitation, briefing note, presentation, or attachment has been uploaded.'],
                ];
                ?>

                <?php foreach ($sideSections as $section): ?>
                    <section class="meeting-panel mb-4">
                        <div class="meeting-panel-heading">
                            <div>
                                <h2>
                                    <i class="bi <?= e($section['icon']) ?>"></i>
                                    <?= e($section['title']) ?>
                                </h2>
                            </div>
                        </div>

                        <div class="meeting-side-empty">
                            <i class="bi <?= e($section['empty_icon']) ?>"></i>
                            <span><?= e($section['message']) ?></span>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
