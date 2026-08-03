<?php
/**
 * LACMS sidebar navigation.
 *
 * The $activeMenu variable controls the highlighted item.
 */

declare(strict_types=1);

$activeMenu = $activeMenu ?? '';

$navigationGroups = [
    [
        'label' => 'Overview',
        'items' => [
            [
                'key'   => 'dashboard',
                'label' => 'Dashboard',
                'icon'  => 'bi-speedometer2',
                'url'   => appUrl('dashboard.php'),
            ],
        ],
    ],
    [
        'label' => 'Agenda and Calendar',
        'items' => [
            [
                'key'   => 'priorities',
                'label' => 'Legislative Priority Setting',
                'icon'  => 'bi-list-stars',
                'url'   => appUrl('modules/priorities/index.php'),
            ],
            [
                'key'   => 'calendar',
                'label' => 'Calendar Scheduling',
                'icon'  => 'bi-calendar-week',
                'url'   => appUrl('modules/calendar/index.php'),
            ],
            [
                'key'   => 'meetings',
                'label' => 'Meeting Coordination',
                'icon'  => 'bi-people',
                'url'   => appUrl('modules/meetings/index.php'),
            ],
            [
                'key'   => 'deadlines',
                'label' => 'Deadline Tracking',
                'icon'  => 'bi-alarm',
                'url'   => appUrl('modules/deadlines/index.php'),
            ],
            [
                'key'   => 'synchronization',
                'label' => 'Executive-Legislative Sync',
                'icon'  => 'bi-arrow-left-right',
                'url'   => appUrl(
                    'modules/synchronization/index.php'
                ),
            ],
        ],
    ],
    [
        'label' => 'Administration',
        'items' => [
            [
                'key'   => 'reports',
                'label' => 'Reports & Analytics',
                'icon'  => 'bi-bar-chart-line',
                'url'   => appUrl('reports/index.php'),
            ],
            [
                'key'   => 'activity_logs',
                'label' => 'Activity Logs',
                'icon'  => 'bi-clock-history',
                'url'   => appUrl('pages/activity_logs.php'),
            ],
            [
                'key'   => 'users',
                'label' => 'User Management',
                'icon'  => 'bi-people-fill',
                'url'   => appUrl('pages/users.php'),
                'admin_only' => true,
            ],
        ],
    ],
];
?>

<aside
    class="sidebar"
    id="sidebar"
>
    <div class="sidebar-brand">
        <a href="<?= e(appUrl('dashboard.php')) ?>">
            <span class="sidebar-brand-icon">
                <i class="bi bi-calendar3"></i>
            </span>

            <span class="sidebar-brand-copy">
                <strong>LACMS</strong>
                <small>
                    Agenda &amp; Calendar
                </small>
            </span>
        </a>

        <button
            type="button"
            class="sidebar-close-button"
            id="sidebarClose"
            aria-label="Close navigation"
        >
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-system-status">
        <span class="status-dot"></span>

        <div>
            <strong>System Online</strong>
            <small>Shared legislative database</small>
        </div>
    </div>

    <nav class="sidebar-navigation">
        <?php foreach ($navigationGroups as $group): ?>
            <div class="sidebar-group">
                <div class="sidebar-group-label">
                    <?= e($group['label']) ?>
                </div>

                <?php foreach ($group['items'] as $item): ?>
                    <?php
                    $adminOnly = (bool)($item['admin_only'] ?? false);

                    if ($adminOnly && !isAdmin()) {
                        continue;
                    }

                    $isActive = $activeMenu === $item['key'];
                    ?>

                    <a
                        href="<?= e($item['url']) ?>"
                        class="sidebar-link
                               <?= $isActive ? 'active' : '' ?>"
                    >
                        <span class="sidebar-link-icon">
                            <i class="bi <?= e($item['icon']) ?>"></i>
                        </span>

                        <span class="sidebar-link-label">
                            <?= e($item['label']) ?>
                        </span>

                        <?php if ($isActive): ?>
                            <span class="sidebar-active-marker"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer-icon">
            <i class="bi bi-shield-check"></i>
        </div>

        <div>
            <strong>Shared Access Enabled</strong>
            <small>
                Session: <?= e(SESSION_NAME) ?>
            </small>
        </div>
    </div>
</aside>
