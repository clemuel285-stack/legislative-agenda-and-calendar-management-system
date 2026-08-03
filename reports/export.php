<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
requireRole([ROLE_ADMIN, ROLE_STAFF]);

header('Content-Type: text/csv; charset=UTF-8');
header(
    'Content-Disposition: attachment; filename="lacms_registry_' .
    date('Y-m-d_H-i-s') .
    '.csv"'
);

$out = fopen('php://output','wb');
fwrite($out,"\xEF\xBB\xBF");
fputcsv($out,[
    'Reference','Type','Title','Office',
    'Priority','Status','Created','Updated'
]);

try {
    $stmt = db()->query(
        "SELECT
            li.reference_number,
            lit.name AS item_type_name,
            li.title,
            o.name AS originating_office,
            li.priority_level,
            li.current_status,
            li.created_at,
            li.updated_at
         FROM legislative_items li
         INNER JOIN legislative_item_types lit
            ON lit.id=li.item_type_id
         LEFT JOIN offices o
            ON o.id=li.originating_office_id
         WHERE li.deleted_at IS NULL
           AND lit.code IN ('ordinance','resolution')
         ORDER BY li.updated_at DESC"
    );

    while ($row = $stmt->fetch()) {
        fputcsv($out,[
            $row['reference_number'],
            $row['item_type_name'],
            $row['title'],
            $row['originating_office'] ?: 'Not assigned',
            $row['priority_level'],
            $row['current_status'],
            $row['created_at'],
            $row['updated_at'],
        ]);
    }
} catch (Throwable $exception) {
    error_log('[LACMS Export] '.$exception->getMessage());
    fputcsv($out,['Export failed.']);
}

fclose($out);
exit;
