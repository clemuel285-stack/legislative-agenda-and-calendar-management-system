<?php
declare(strict_types=1);

$moduleTitle = 'Deadline Tracking Module';
$moduleKey = 'deadlines';
$moduleIcon = 'bi-alarm';
$moduleDescription = 'Monitors important legislative deadlines, due dates, assigned responsibilities, and approaching time-sensitive activities to reduce missed deadlines.';
$primaryAction = 'New Deadline';
$problemLink = 'Missed deadlines occur because the current process lacks automated reminders and centralized monitoring of due dates and legislative tasks.';

$moduleFeatures = [
    [
        'icon' => 'bi-calendar2-check',
        'title' => 'Deadline Registration',
        'description' => 'Record important due dates for legislative tasks, meetings, documents, and activities.',
    ],
    [
        'icon' => 'bi-person-check',
        'title' => 'Responsible Assignment',
        'description' => 'Assign a responsible user, committee, office, or staff member.',
    ],
    [
        'icon' => 'bi-alarm',
        'title' => 'Approaching Due Dates',
        'description' => 'Prepare warning states for upcoming deadlines.',
    ],
    [
        'icon' => 'bi-stars',
        'title' => 'AI Reminder Linkage',
        'description' => 'Connect deadlines to future AI-assisted email reminder generation.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Overdue Monitoring',
        'description' => 'Identify overdue or unresolved legislative tasks.',
    ],
    [
        'icon' => 'bi-search',
        'title' => 'Search & Filter',
        'description' => 'Find deadlines by due date, assignee, committee, status, priority, or keyword.',
    ],
];

$moduleSections = [
    [
        'icon' => 'bi-list-task',
        'title' => 'Deadline Registry',
        'description' => 'Centralized list of legislative deadlines and tasks.',
    ],
    [
        'icon' => 'bi-alarm',
        'title' => 'Due Soon',
        'description' => 'Deadlines approaching their target date.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Overdue',
        'description' => 'Past-due tasks requiring attention.',
    ],
    [
        'icon' => 'bi-person-check',
        'title' => 'Assignments',
        'description' => 'Responsible users, committees, and offices.',
    ],
    [
        'icon' => 'bi-stars',
        'title' => 'Reminder Schedule',
        'description' => 'Future AI-assisted reminder timing and content.',
    ],
    [
        'icon' => 'bi-check2-circle',
        'title' => 'Completed Deadlines',
        'description' => 'Completed tasks and closed deadline records.',
    ],
];

$workflowSteps = [
    [
        'title' => 'Create Deadline',
        'description' => 'A legislative deadline or due date is registered.',
    ],
    [
        'title' => 'Assign',
        'description' => 'Responsibility is assigned to a user, office, or committee.',
    ],
    [
        'title' => 'Monitor',
        'description' => 'System tracks time remaining and deadline status.',
    ],
    [
        'title' => 'Remind',
        'description' => 'Future automated email reminders are generated before due dates.',
    ],
    [
        'title' => 'Escalate',
        'description' => 'Overdue or urgent items are highlighted for attention.',
    ],
    [
        'title' => 'Complete',
        'description' => 'Task is marked completed and retained in activity history.',
    ],
];

$quickActions = [
    [
        'icon' => 'bi-calendar2-plus',
        'title' => 'Create Deadline',
        'description' => 'Register a new legislative due date.',
    ],
    [
        'icon' => 'bi-alarm',
        'title' => 'Due Soon',
        'description' => 'View approaching deadlines.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Overdue',
        'description' => 'Review past-due tasks.',
    ],
];

$recordFields = [
    'Deadline title',
    'Related agenda / meeting / activity',
    'Due date and time',
    'Responsible user / office / committee',
    'Priority',
    'Reminder schedule',
    'Deadline status',
    'Completion date',
    'Overdue status',
    'Remarks',
];

$previousModule = [
    'label' => 'Meeting Coordination Module',
    'href' => 'modules/meetings/index.php',
];
$nextModule = null;

require __DIR__ . '/../../includes/lacms_scope_module.php';
