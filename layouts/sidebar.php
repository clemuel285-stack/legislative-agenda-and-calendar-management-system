<?php
declare(strict_types=1);
$activeMenu = $activeMenu ?? '';

$moduleItems = [
    ['key'=>'agendas','label'=>'Legislative Agenda Management','icon'=>'bi-list-check','url'=>appUrl('modules/agendas/index.php')],
    ['key'=>'calendar','label'=>'Calendar Scheduling','icon'=>'bi-calendar-week','url'=>appUrl('modules/calendar/index.php')],
    ['key'=>'meetings','label'=>'Meeting Coordination','icon'=>'bi-people','url'=>appUrl('modules/meetings/index.php')],
    ['key'=>'deadlines','label'=>'Deadline Tracking','icon'=>'bi-alarm','url'=>appUrl('modules/deadlines/index.php')],
];

$automationItems = [
    ['key'=>'ai_email_reminders','label'=>'AI-Based Email Reminders','icon'=>'bi-stars','url'=>appUrl('pages/ai_email_reminders.php'),'badge'=>'PLANNED'],
    ['key'=>'meeting_notifications','label'=>'Meeting Notifications','icon'=>'bi-envelope-paper','url'=>appUrl('pages/meeting_notifications.php'),'badge'=>'EMAIL'],
    ['key'=>'search','label'=>'Search & Filter','icon'=>'bi-search','url'=>appUrl('pages/search.php'),'badge'=>''],
];

$adminItems = [
    ['key'=>'reports','label'=>'Dashboard & Reports','icon'=>'bi-bar-chart-line','url'=>appUrl('reports/index.php'),'admin_only'=>false],
    ['key'=>'activity_logs','label'=>'Activity Logs','icon'=>'bi-clock-history','url'=>appUrl('pages/activity_logs.php'),'admin_only'=>false],
    ['key'=>'users','label'=>'Role-Based User Management','icon'=>'bi-person-gear','url'=>appUrl('pages/users.php'),'admin_only'=>true],
];
?>
<aside class="lacms-sidebar" id="lacmsSidebar">
    <div class="lacms-sidebar-brand">
        <span><i class="bi bi-calendar3"></i></span>
        <div><strong>LACMS</strong><small>Agenda & Calendar</small></div>
    </div>

    <div class="lacms-office-card">
        <i class="bi bi-building"></i>
        <div><strong>LGU of Manila</strong><small>Centralized Legislative Coordination</small></div>
    </div>

    <nav class="lacms-sidebar-nav">
        <div class="lacms-sidebar-section">Overview</div>
        <a href="<?= e(appUrl('dashboard.php')) ?>" class="lacms-sidebar-link <?= $activeMenu==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>

        <div class="lacms-sidebar-section">Agenda & Calendar Management</div>
        <?php foreach ($moduleItems as $item): ?>
            <a href="<?= e($item['url']) ?>" class="lacms-sidebar-link <?= $activeMenu===$item['key']?'active':'' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                <?php if ($activeMenu===$item['key']): ?><b></b><?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="lacms-sidebar-section">Automation & Communication</div>
        <?php foreach ($automationItems as $item): ?>
            <a href="<?= e($item['url']) ?>" class="lacms-sidebar-link <?= $activeMenu===$item['key']?'active':'' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                <?php if ($item['badge']!==''): ?><em><?= e($item['badge']) ?></em><?php endif; ?>
                <?php if ($activeMenu===$item['key']): ?><b></b><?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="lacms-sidebar-section">Administration</div>
        <?php foreach ($adminItems as $item): ?>
            <?php if ($item['admin_only'] && !isAdmin()) continue; ?>
            <a href="<?= e($item['url']) ?>" class="lacms-sidebar-link <?= $activeMenu===$item['key']?'active':'' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                <?php if ($activeMenu===$item['key']): ?><b></b><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="lacms-sidebar-footer">
        <i class="bi bi-shield-check"></i>
        <div><strong>Secure Shared Access</strong><small>Role-based authenticated session</small></div>
    </div>
</aside>
<div class="lacms-sidebar-backdrop" id="lacmsSidebarBackdrop"></div>
