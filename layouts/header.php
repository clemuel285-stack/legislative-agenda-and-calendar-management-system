<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Dashboard';
$activeMenu = $activeMenu ?? '';
$extraCss = $extraCss ?? [];
$extraJs = $extraJs ?? [];

$user = currentUser();
$fullName = trim((string)($user['full_name'] ?? 'Authorized User'));
$email = trim((string)($user['email'] ?? ''));
$role = normalizeRole((string)($user['role'] ?? ''));

$roleLabel = match ($role) {
    'ADMIN' => 'Administrator',
    'STAFF' => 'Legislative Staff',
    'COMMITTEE' => 'Committee Member',
    default => ucwords(strtolower(str_replace('_', ' ', $role ?: 'Authorized User'))),
};

$subsystems = [
    ['short'=>'ORLMS','name'=>'Ordinance & Resolution Life Cycle','url'=>'http://localhost/orlms/','icon'=>'bi-file-earmark-text','active'=>false],
    ['short'=>'LACMS','name'=>'Legislative Agenda & Calendar','url'=>'http://localhost/lacms/','icon'=>'bi-calendar3','active'=>true],
    ['short'=>'VQDSS','name'=>'Voting, Quorum & Decisions','url'=>'http://localhost/vqdss/','icon'=>'bi-check2-square','active'=>false],
    ['short'=>'LPH','name'=>'Public Hearing & Consultation','url'=>'http://localhost/lph/','icon'=>'bi-people','active'=>false],
    ['short'=>'CEPFMS','name'=>'Citizen Engagement & Public Feedback','url'=>'http://localhost/cepfms/','icon'=>'bi-chat-square-heart','active'=>false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | LACMS</title>
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap/bootstrap.min.css','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap-icons/bootstrap-icons.css','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css')) ?>">
<link rel="stylesheet" href="<?= e(appUrl('assets/css/lacms-shell.css')) ?>">
<?php foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= e($css) ?>">
<?php endforeach; ?>
</head>
<body>
<header class="lacms-topbar">
    <button type="button" class="lacms-menu-button" id="lacmsSidebarToggle" aria-label="Toggle navigation">
        <i class="bi bi-list"></i>
    </button>

    <a href="<?= e(appUrl('dashboard.php')) ?>" class="lacms-topbar-brand">
        <span><i class="bi bi-calendar3"></i></span>
        <div>
            <strong>LACMS</strong>
            <small>Local Government Unit of Manila · Legislative Coordination</small>
        </div>
    </a>

    <div class="lacms-topbar-actions">
        <a href="<?= e(appUrl('pages/search.php')) ?>" class="lacms-topbar-icon" title="Global Search">
            <i class="bi bi-search"></i>
        </a>

        <a href="<?= e(appUrl('pages/ai_email_reminders.php')) ?>" class="lacms-topbar-icon planned" title="AI-Based Email Reminders">
            <i class="bi bi-stars"></i>
        </a>

        <div class="dropdown">
            <button type="button" class="lacms-system-switcher dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-grid-3x3-gap"></i><span>Subsystems</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end lacms-subsystem-menu">
                <div class="lacms-subsystem-heading">Legislative Services Management System</div>
                <?php foreach ($subsystems as $system): ?>
                    <a href="<?= e($system['url']) ?>" class="lacms-subsystem-item <?= $system['active'] ? 'active' : '' ?>">
                        <span><i class="bi <?= e($system['icon']) ?>"></i></span>
                        <div><strong><?= e($system['short']) ?></strong><small><?= e($system['name']) ?></small></div>
                        <?php if ($system['active']): ?><i class="bi bi-check-circle-fill"></i><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="lacms-user-button dropdown-toggle" data-bs-toggle="dropdown">
                <span class="lacms-avatar"><?= e(strtoupper(substr($fullName,0,1))) ?></span>
                <span class="lacms-user-copy"><strong><?= e($fullName) ?></strong><small><?= e($roleLabel) ?></small></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($email !== ''): ?>
                    <li><span class="dropdown-item-text small text-muted"><?= e($email) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="<?= e(appUrl('dashboard.php')) ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= e(appUrl('pages/search.php')) ?>"><i class="bi bi-search me-2"></i>Search Records</a></li>
                <li><a class="dropdown-item" href="<?= e(appUrl('pages/ai_email_reminders.php')) ?>"><i class="bi bi-stars me-2"></i>AI Email Reminders</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= e(appUrl('logout.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</header>
