<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN, ROLE_STAFF]);

$pageTitle = 'Reports & Analytics';
$activeMenu = 'reports';
$extraCss = [appUrl('assets/css/administration.css')];
$extraJs = [appUrl('assets/js/administration.js')];

$pdo = db();

$summary = [
    'total' => 0,
    'urgent' => 0,
    'active' => 0,
    'completed' => 0,
    'ordinances' => 0,
    'resolutions' => 0,
];

$statusRows = [];
$priorityRows = [];
$typeRows = [];
$monthlyRows = [];
$registryRows = [];

try {
    $row = $pdo->query(
        "SELECT
            COUNT(*) AS total,
            SUM(li.priority_level = 'Urgent') AS urgent,
            SUM(li.current_status IN (
                'Submitted','Under Review','Committee Endorsed',
                'For Approval','Approved','Enacted','For Publication',
                'Published','Under Implementation'
            )) AS active,
            SUM(li.current_status IN (
                'Implemented','Rejected','Repealed'
            )) AS completed,
            SUM(lit.code = 'ordinance') AS ordinances,
            SUM(lit.code = 'resolution') AS resolutions
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id = li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')"
    )->fetch();

    if ($row) {
        foreach ($summary as $key => $value) {
            $summary[$key] = (int)($row[$key] ?? 0);
        }
    }

    $statusRows = $pdo->query(
        "SELECT li.current_status AS label, COUNT(*) AS total
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id = li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
         GROUP BY li.current_status
         ORDER BY total DESC, li.current_status"
    )->fetchAll();

    $priorityRows = $pdo->query(
        "SELECT COALESCE(li.priority_level,'Unspecified') AS label,
                COUNT(*) AS total
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id = li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
         GROUP BY li.priority_level
         ORDER BY FIELD(
            li.priority_level,'Urgent','High','Normal','Low'
         )"
    )->fetchAll();

    $typeRows = $pdo->query(
        "SELECT lit.name AS label, COUNT(*) AS total
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id = li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
         GROUP BY lit.id, lit.name
         ORDER BY lit.name"
    )->fetchAll();

    $monthlyRows = $pdo->query(
        "SELECT DATE_FORMAT(li.created_at,'%Y-%m') AS month_key,
                DATE_FORMAT(li.created_at,'%b %Y') AS label,
                COUNT(*) AS total
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id = li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
           AND li.created_at >= DATE_SUB(CURDATE(),INTERVAL 11 MONTH)
         GROUP BY month_key,label
         ORDER BY month_key"
    )->fetchAll();

    $registryRows = $pdo->query(
        "SELECT
            li.reference_number,
            li.title,
            li.current_status,
            li.priority_level,
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
           AND lit.code IN ('ordinance','resolution')
         ORDER BY li.updated_at DESC
         LIMIT 500"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Reports] ' . $exception->getMessage());
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="app-wrapper">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="main-content">

        <section class="admin-page-header">
            <div>
                <div class="admin-eyebrow">
                    <i class="bi bi-bar-chart-line"></i>
                    LACMS Administration
                </div>

                <h1>Reports &amp; Analytics</h1>

                <p>
                    Review legislative activity, priorities, workflow
                    distribution, module reports, and the shared registry.
                </p>
            </div>

            <div class="admin-header-actions">
                <a href="export.php" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    Export CSV
                </a>

                <a href="print.php" class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer"></i>
                    Print Summary
                </a>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <?php
            $cards = [
                [$summary['total'],'Total Legislative Records','bi-files'],
                [$summary['urgent'],'Urgent Priority','bi-exclamation-octagon'],
                [$summary['active'],'Active Workflow','bi-arrow-repeat'],
                [$summary['completed'],'Completed or Closed','bi-check2-circle'],
                [$summary['ordinances'],'Ordinances','bi-file-earmark-text'],
                [$summary['resolutions'],'Resolutions','bi-journal-check'],
            ];
            ?>

            <?php foreach ($cards as [$value,$label,$icon]): ?>
                <div class="col-sm-6 col-xl">
                    <div class="admin-summary-card">
                        <span class="summary-icon">
                            <i class="bi <?= e($icon) ?>"></i>
                        </span>
                        <strong><?= (int)$value ?></strong>
                        <span><?= e($label) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="admin-panel mb-4">
            <div class="admin-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-grid"></i>
                        Module Report Center
                    </h2>
                    <p>Open the detailed report page of each module.</p>
                </div>
            </div>

            <div class="admin-module-grid">
                <?php
                $modules = [
                    ['Legislative Priority Setting','bi-list-stars','modules/priorities/report.php'],
                    ['Calendar Scheduling','bi-calendar-week','modules/calendar/report.php'],
                    ['Meeting Coordination','bi-people','modules/meetings/report.php'],
                    ['Deadline Tracking','bi-alarm','modules/deadlines/report.php'],
                    ['Executive-Legislative Sync','bi-arrow-left-right','modules/synchronization/report.php'],
                ];
                ?>

                <?php foreach ($modules as [$title,$icon,$href]): ?>
                    <a
                        href="<?= e(appUrl($href)) ?>"
                        class="admin-module-card"
                    >
                        <span><i class="bi <?= e($icon) ?>"></i></span>
                        <div>
                            <strong><?= e($title) ?></strong>
                            <small>Open detailed module analytics.</small>
                        </div>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <section class="admin-panel h-100">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-graph-up"></i>
                                Monthly Legislative Activity
                            </h2>
                        </div>
                    </div>
                    <div class="admin-chart-area">
                        <canvas id="lacmsMonthlyChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <section class="admin-panel h-100">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-pie-chart"></i>
                                Record Type Distribution
                            </h2>
                        </div>
                    </div>
                    <div class="admin-chart-area">
                        <canvas id="lacmsTypeChart"></canvas>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <section class="admin-panel h-100">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-list-check"></i>
                                Workflow Status Summary
                            </h2>
                        </div>
                    </div>

                    <div class="admin-stat-list">
                        <?php foreach ($statusRows as $row): ?>
                            <div class="admin-stat-row">
                                <span><?= e($row['label']) ?></span>
                                <strong><?= (int)$row['total'] ?></strong>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$statusRows): ?>
                            <div class="admin-empty-state compact">
                                <i class="bi bi-bar-chart"></i>
                                <strong>No workflow data available</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="col-xl-6">
                <section class="admin-panel h-100">
                    <div class="admin-panel-heading">
                        <div>
                            <h2>
                                <i class="bi bi-flag"></i>
                                Priority Distribution
                            </h2>
                        </div>
                    </div>
                    <div class="admin-chart-area">
                        <canvas id="lacmsPriorityChart"></canvas>
                    </div>
                </section>
            </div>
        </div>

        <section class="admin-filter-panel">
            <div class="row g-3">
                <div class="col-lg-5">
                    <label class="form-label" for="adminRegistrySearch">
                        Search Legislative Registry
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="search"
                            id="adminRegistrySearch"
                            class="form-control"
                            placeholder="Reference, title, or office..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="adminRegistryType">
                        Type
                    </label>
                    <select id="adminRegistryType" class="form-select">
                        <option value="">All Types</option>
                        <option value="ordinance">Ordinance</option>
                        <option value="resolution">Resolution</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="adminRegistryPriority">
                        Priority
                    </label>
                    <select id="adminRegistryPriority" class="form-select">
                        <option value="">All Priorities</option>
                        <option>Urgent</option>
                        <option>High</option>
                        <option>Normal</option>
                        <option>Low</option>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label class="form-label" for="adminRegistryStatus">
                        Status
                    </label>
                    <select id="adminRegistryStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($statusRows as $row): ?>
                            <option value="<?= e($row['label']) ?>">
                                <?= e($row['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-1 d-flex align-items-end">
                    <button
                        type="button"
                        id="btnResetAdminRegistry"
                        class="btn btn-outline-secondary w-100"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <h2>
                        <i class="bi bi-table"></i>
                        Shared Legislative Registry
                    </h2>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Office</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registryRows as $row): ?>
                            <?php
                            $search = strtolower(
                                ($row['reference_number'] ?? '') . ' ' .
                                ($row['title'] ?? '') . ' ' .
                                ($row['originating_office'] ?? '')
                            );
                            ?>
                            <tr
                                class="admin-registry-row"
                                data-search="<?= e($search) ?>"
                                data-type="<?= e($row['item_type_code']) ?>"
                                data-priority="<?= e($row['priority_level']) ?>"
                                data-status="<?= e($row['current_status']) ?>"
                            >
                                <td>
                                    <span class="admin-reference">
                                        <?= e($row['reference_number']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-soft-badge">
                                        <?= e($row['item_type_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-title-cell">
                                        <strong><?= e($row['title']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?= e($row['originating_office'] ?: 'Not assigned') ?>
                                </td>
                                <td>
                                    <span
                                        class="admin-priority-badge
                                        <?= e(strtolower($row['priority_level'])) ?>"
                                    >
                                        <?= e($row['priority_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-status-badge">
                                        <?= e($row['current_status']) ?>
                                    </span>
                                </td>
                                <td><?= e(formatDateTime($row['updated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$registryRows): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="admin-empty-state">
                                        <i class="bi bi-inboxes"></i>
                                        <strong>No legislative records available</strong>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr id="adminRegistryNoResult" class="d-none">
                            <td colspan="7">
                                <div class="admin-empty-state compact">
                                    <i class="bi bi-search"></i>
                                    <strong>No matching records</strong>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-table-footer">
                <span id="adminRegistryCount">
                    Showing <?= count($registryRows) ?> record(s)
                </span>
            </div>
        </section>

<script>
window.LACMS_REPORT_DATA = <?= json_encode([
    'monthly' => [
        'labels' => array_column($monthlyRows, 'label'),
        'values' => array_map('intval', array_column($monthlyRows, 'total')),
    ],
    'types' => [
        'labels' => array_column($typeRows, 'label'),
        'values' => array_map('intval', array_column($typeRows, 'total')),
    ],
    'priorities' => [
        'labels' => array_column($priorityRows, 'label'),
        'values' => array_map('intval', array_column($priorityRows, 'total')),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
