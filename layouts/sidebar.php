<?php
declare(strict_types=1);
$activeMenu = $activeMenu ?? '';

$moduleItems = [
    ['key'=>'agendas','label'=>'Legislative Agenda Management','icon'=>'bi-list-check','url'=>appUrl('modules/agendas/index.php'),'permission'=>'lacms.agendas.view'],
    ['key'=>'calendar','label'=>'Calendar Scheduling','icon'=>'bi-calendar-week','url'=>appUrl('modules/calendar/index.php'),'permission'=>'lacms.calendar.view'],
    ['key'=>'meetings','label'=>'Meeting Coordination','icon'=>'bi-people','url'=>appUrl('modules/meetings/index.php'),'permission'=>'lacms.meetings.view'],
    ['key'=>'deadlines','label'=>'Deadline Tracking','icon'=>'bi-alarm','url'=>appUrl('modules/deadlines/index.php'),'permission'=>'lacms.deadlines.view'],
    ['key'=>'synchronization','label'=>'Executive-Legislative Sync','icon'=>'bi-arrow-left-right','url'=>appUrl('modules/synchronization/index.php'),'permission'=>'lacms.sync.view'],
];

$automationItems = [
    ['key'=>'ai_email_reminders','label'=>'AI-Assisted Email Reminders','icon'=>'bi-stars','url'=>appUrl('pages/ai_email_reminders.php'),'badge'=>'AI','permission'=>'lacms.notifications.view'],
    ['key'=>'meeting_notifications','label'=>'Meeting Notifications','icon'=>'bi-envelope-paper','url'=>appUrl('pages/meeting_notifications.php'),'badge'=>'LIVE','permission'=>'lacms.notifications.view'],
    ['key'=>'search','label'=>'Search & Filter','icon'=>'bi-search','url'=>appUrl('pages/search.php'),'badge'=>'','permission'=>'lacms.dashboard.view'],
];

$adminItems = [
    ['key'=>'reports','label'=>'Reports & Workflow','icon'=>'bi-bar-chart-line','url'=>appUrl('reports/index.php'),'permission'=>'lacms.reports.view'],
    ['key'=>'activity_logs','label'=>'Activity Logs','icon'=>'bi-clock-history','url'=>appUrl('pages/activity_logs.php'),'permission'=>'lacms.activity_logs.view'],
    ['key'=>'users','label'=>'Role-Based User Management','icon'=>'bi-person-gear','url'=>appUrl('pages/users.php'),'permission'=>'lacms.users.manage'],
    ['key'=>'system_health','label'=>'System Health','icon'=>'bi-heart-pulse','url'=>appUrl('pages/system_health.php'),'permission'=>'lacms.system_health.view'],
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
        <?php if(lacmsHasPermission('lacms.dashboard.view')): ?><a href="<?= e(appUrl('dashboard.php')) ?>" class="lacms-sidebar-link <?= $activeMenu==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a><?php endif; ?>

        <div class="lacms-sidebar-section">Agenda & Calendar Management</div>
        <?php foreach ($moduleItems as $item): ?>
            <?php if (!lacmsHasPermission($item['permission'])) continue; ?>
            <a href="<?= e($item['url']) ?>" class="lacms-sidebar-link <?= $activeMenu===$item['key']?'active':'' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                <?php if ($activeMenu===$item['key']): ?><b></b><?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="lacms-sidebar-section">Automation & Communication</div>
        <?php foreach ($automationItems as $item): ?>
            <?php if (!lacmsHasPermission($item['permission'])) continue; ?>
            <a href="<?= e($item['url']) ?>" class="lacms-sidebar-link <?= $activeMenu===$item['key']?'active':'' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
                <?php if ($item['badge']!==''): ?><em><?= e($item['badge']) ?></em><?php endif; ?>
                <?php if ($activeMenu===$item['key']): ?><b></b><?php endif; ?>
            </a>
        <?php endforeach; ?>

        <div class="lacms-sidebar-section">Administration</div>
        <?php foreach ($adminItems as $item): ?>
            <?php if (!lacmsHasPermission($item['permission'])) continue; ?>
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
