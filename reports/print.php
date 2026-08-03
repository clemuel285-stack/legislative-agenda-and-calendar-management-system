<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN, ROLE_STAFF]);

$pdo = db();
$summary = [];
$statusRows = [];

try {
    $summary = $pdo->query(
        "SELECT
            COUNT(*) AS total,
            SUM(li.priority_level='Urgent') AS urgent,
            SUM(lit.code='ordinance') AS ordinances,
            SUM(lit.code='resolution') AS resolutions
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id=li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')"
    )->fetch() ?: [];

    $statusRows = $pdo->query(
        "SELECT li.current_status AS label, COUNT(*) AS total
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id=li.item_type_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
         GROUP BY li.current_status
         ORDER BY total DESC"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('[LACMS Reports Print] '.$exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LACMS Reports Summary</title>
<style>
body{font-family:Arial,sans-serif;color:#0f172a;padding:24px;font-size:12px}
header{text-align:center;border-bottom:3px solid #0b3d6e;padding-bottom:12px;margin-bottom:18px}
h1{color:#0b3d6e;margin:0}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px}
.card{border:1px solid #cbd5e1;border-top:4px solid #eab308;padding:12px;border-radius:8px}
.card strong{display:block;font-size:20px;color:#0b3d6e}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #cbd5e1;padding:8px;text-align:left}
th{background:#0b3d6e;color:#fff}
.no-print{text-align:right;margin-bottom:12px}
@media print{.no-print{display:none}body{padding:0}@page{margin:12mm}}
</style>
</head>
<body onload="window.print()">
<div class="no-print"><button onclick="window.print()">Print</button></div>
<header>
    <h1>Legislative Agenda and Calendar Management System</h1>
    <p>Reports &amp; Analytics Summary</p>
    <p>Generated <?= e(date('F j, Y g:i A')) ?></p>
</header>
<div class="cards">
    <div class="card"><strong><?= (int)($summary['total'] ?? 0) ?></strong><span>Total Records</span></div>
    <div class="card"><strong><?= (int)($summary['urgent'] ?? 0) ?></strong><span>Urgent</span></div>
    <div class="card"><strong><?= (int)($summary['ordinances'] ?? 0) ?></strong><span>Ordinances</span></div>
    <div class="card"><strong><?= (int)($summary['resolutions'] ?? 0) ?></strong><span>Resolutions</span></div>
</div>
<table>
<thead><tr><th>Status</th><th>Total</th></tr></thead>
<tbody>
<?php foreach ($statusRows as $row): ?>
<tr><td><?= e($row['label']) ?></td><td><?= (int)$row['total'] ?></td></tr>
<?php endforeach; ?>
<?php if (!$statusRows): ?><tr><td colspan="2">No data available.</td></tr><?php endif; ?>
</tbody>
</table>
</body>
</html>
