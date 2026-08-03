<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

requireRole([ROLE_ADMIN, ROLE_STAFF, ROLE_COMMITTEE]);

$pageTitle  = 'Calendar Scheduling Report';
$activeMenu = 'calendar';
$extraCss   = [appUrl('assets/css/calendar.css')];

$pdo = db();

$byPriority = $pdo->query(
    "SELECT li.priority_level, COUNT(*) AS total
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
    "SELECT li.current_status, COUNT(*) AS total
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
        <section class="calendar-page-header">
            <div>
                <a href="index.php" class="calendar-back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Calendar Scheduling
                </a>

                <div class="calendar-eyebrow">
                    <i class="bi bi-bar-chart"></i>
                    Scheduling Analytics
                </div>

                <h1>Calendar Scheduling Report</h1>

                <p>
                    Review legislative scheduling candidates by priority
                    and workflow status.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-primary"
                onclick="window.print()"
            >
                <i class="bi bi-printer"></i>
                Print Report
            </button>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="calendar-summary-card">
                    <span class="summary-icon">
                        <i class="bi bi-files"></i>
                    </span>
                    <strong><?= (int)$total ?></strong>
                    <span>Total Candidates</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="calendar-summary-card">
                    <span class="summary-icon">
                        <i class="bi bi-calendar-check"></i>
                    </span>
                    <strong>0</strong>
                    <span>Saved Activities</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="calendar-summary-card conflict">
                    <span class="summary-icon">
                        <i class="bi bi-calendar-x"></i>
                    </span>
                    <strong>0</strong>
                    <span>Detected Conflicts</span>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="calendar-panel h-100">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-bar-chart"></i>
                                Candidates by Priority
                            </h2>
                        </div>
                    </div>

                    <div class="calendar-report-chart">
                        <canvas id="calendarPriorityChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="calendar-panel h-100">
                    <div class="calendar-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-list-check"></i>
                                Workflow Status Summary
                            </h2>
                        </div>
                    </div>

                    <div class="calendar-report-list">
                        <?php foreach ($byStatus as $row): ?>
                            <div class="calendar-report-list-item">
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
    const canvas = document.getElementById('calendarPriorityChart');

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
