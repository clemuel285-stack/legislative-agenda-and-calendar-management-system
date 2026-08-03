<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Deadline Tracking Report';
$activeMenu = 'deadlines';
$extraCss   = [appUrl('assets/css/deadlines.css')];

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

        <section class="deadline-page-header">
            <div>
                <a href="index.php" class="deadline-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Deadline Tracking
                </a>

                <div class="deadline-eyebrow">
                    <i class="bi bi-bar-chart"></i>
                    Timeline Analytics
                </div>

                <h1>Deadline Tracking Report</h1>

                <p>
                    Review deadline candidates by legislative priority
                    and current workflow status.
                </p>
            </div>

            <div class="deadline-header-actions">
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
                ['value' => $total, 'label' => 'Total Tracking Candidates', 'icon' => 'bi-files', 'class' => ''],
                ['value' => 0, 'label' => 'Saved Deadlines', 'icon' => 'bi-alarm', 'class' => ''],
                ['value' => 0, 'label' => 'Overdue Deadlines', 'icon' => 'bi-exclamation-octagon', 'class' => 'overdue'],
            ];
            ?>

            <?php foreach ($cards as $card): ?>
                <div class="col-md-4">
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

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-bar-chart"></i>
                                Candidates by Priority
                            </h2>
                        </div>
                    </div>

                    <div class="deadline-report-chart">
                        <canvas id="deadlinePriorityChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="deadline-panel h-100">
                    <div class="deadline-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-list-check"></i>
                                Workflow Status Summary
                            </h2>
                        </div>
                    </div>

                    <div class="deadline-report-list">
                        <?php if (empty($byStatus)): ?>
                            <div class="deadline-empty-state compact">
                                <i class="bi bi-bar-chart"></i>
                                <strong>No tracking data available</strong>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($byStatus as $row): ?>
                            <div class="deadline-report-list-item">
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
    const canvas = document.getElementById('deadlinePriorityChart');

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
