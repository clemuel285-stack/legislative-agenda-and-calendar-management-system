<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Meeting Coordination Report';
$activeMenu = 'meetings';
$extraCss   = [appUrl('assets/css/meetings.css')];

$pdo = db();

$byPriority = $pdo->query(
    "SELECT
        li.priority_level,
        COUNT(*) AS total
     FROM legislative_items li
     INNER JOIN legislative_item_types lit
        ON lit.id = li.item_type_id
     WHERE li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')
     GROUP BY li.priority_level
     ORDER BY FIELD(
        li.priority_level,
        'Urgent',
        'High',
        'Normal',
        'Low'
     )"
)->fetchAll();

$byStatus = $pdo->query(
    "SELECT
        li.current_status,
        COUNT(*) AS total
     FROM legislative_items li
     INNER JOIN legislative_item_types lit
        ON lit.id = li.item_type_id
     WHERE li.deleted_at IS NULL
       AND lit.code IN ('ordinance', 'resolution')
     GROUP BY li.current_status
     ORDER BY total DESC, li.current_status"
)->fetchAll();

$total = array_sum(array_map(
    'intval',
    array_column($byPriority, 'total')
));

include __DIR__ . '/../../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="meeting-page-header">
            <div>
                <a href="index.php" class="meeting-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Meeting Coordination
                </a>

                <div class="meeting-eyebrow">
                    <i class="bi bi-bar-chart"></i>
                    Coordination Analytics
                </div>

                <h1>Meeting Coordination Report</h1>

                <p>
                    Review legislative coordination candidates by
                    priority level and workflow status.
                </p>
            </div>

            <div class="meeting-header-actions">
                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()"
                >
                    <i class="bi bi-printer"></i>
                    Print Report
                </button>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php
            $cards = [
                ['value' => $total, 'label' => 'Total Coordination Candidates', 'icon' => 'bi-files'],
                ['value' => 0, 'label' => 'Saved Meeting Records', 'icon' => 'bi-calendar-event'],
                ['value' => 0, 'label' => 'Confirmed Participants', 'icon' => 'bi-person-check'],
            ];
            ?>

            <?php foreach ($cards as $card): ?>
                <div class="col-md-4">
                    <div class="meeting-summary-card">
                        <span class="summary-icon">
                            <i class="bi <?= e($card['icon']) ?>"></i>
                        </span>

                        <strong><?= (int)$card['value'] ?></strong>
                        <span><?= e($card['label']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-bar-chart"></i>
                                Candidates by Priority
                            </h2>
                        </div>
                    </div>

                    <div class="meeting-report-chart">
                        <canvas id="meetingPriorityChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="meeting-panel h-100">
                    <div class="meeting-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-list-check"></i>
                                Workflow Status Summary
                            </h2>
                        </div>
                    </div>

                    <div class="meeting-report-list">
                        <?php if (empty($byStatus)): ?>
                            <div class="meeting-empty-state compact">
                                <i class="bi bi-bar-chart"></i>
                                <strong>No coordination data available</strong>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($byStatus as $row): ?>
                            <div class="meeting-report-list-item">
                                <span><?= e($row['current_status']) ?></span>
                                <strong><?= (int)$row['total'] ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('meetingPriorityChart');

    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: <?= json_encode(
                array_column($byPriority, 'priority_level'),
                JSON_UNESCAPED_UNICODE
            ) ?>,
            datasets: [{
                label: 'Candidates',
                data: <?= json_encode(array_map(
                    'intval',
                    array_column($byPriority, 'total')
                )) ?>,
                backgroundColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#1d6fb8',
                    '#64748b'
                ],
                borderRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
