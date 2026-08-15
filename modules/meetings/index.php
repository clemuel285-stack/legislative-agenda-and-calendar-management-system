<?php
declare(strict_types=1);

$moduleTitle = 'Meeting Coordination Module';
$moduleKey = 'meetings';
$moduleIcon = 'bi-people';
$moduleDescription = 'Supports planning and organization of legislative meetings through participant management, schedule coordination, agenda linkage, communication, and change notifications.';
$primaryAction = 'Coordinate Meeting';
$problemLink = 'Inefficient communication among legislators and staff makes meeting planning, participant coordination, and schedule updates difficult to manage consistently.';

$moduleFeatures = [
    [
        'icon' => 'bi-calendar-event',
        'title' => 'Meeting Planning',
        'description' => 'Prepare meeting date, time, type, venue, purpose, and coordination details.',
    ],
    [
        'icon' => 'bi-people',
        'title' => 'Participant Management',
        'description' => 'Identify legislators, staff, committee members, guests, and required participants.',
    ],
    [
        'icon' => 'bi-list-check',
        'title' => 'Agenda Linkage',
        'description' => 'Connect the meeting with its legislative agenda and agenda items.',
    ],
    [
        'icon' => 'bi-envelope-paper',
        'title' => 'Meeting Notifications',
        'description' => 'Prepare email notices for schedules, updates, changes, and reminders.',
    ],
    [
        'icon' => 'bi-arrow-repeat',
        'title' => 'Rescheduling & Changes',
        'description' => 'Represent controlled changes to meeting time, venue, participants, or status.',
    ],
    [
        'icon' => 'bi-search',
        'title' => 'Search & Filter',
        'description' => 'Locate meetings by date, committee, status, venue, participant, or keyword.',
    ],
];

$moduleSections = [
    [
        'icon' => 'bi-inboxes',
        'title' => 'Meeting Registry',
        'description' => 'Central list of scheduled, completed, postponed, and cancelled meetings.',
    ],
    [
        'icon' => 'bi-people',
        'title' => 'Participants',
        'description' => 'Legislators, staff, committees, guests, and attendance planning.',
    ],
    [
        'icon' => 'bi-list-check',
        'title' => 'Meeting Agenda',
        'description' => 'Linked agenda and discussion items.',
    ],
    [
        'icon' => 'bi-envelope-paper',
        'title' => 'Notification Center',
        'description' => 'Future schedule and update email communication.',
    ],
    [
        'icon' => 'bi-arrow-repeat',
        'title' => 'Schedule Changes',
        'description' => 'Rescheduling and update navigation.',
    ],
    [
        'icon' => 'bi-clock-history',
        'title' => 'Meeting History',
        'description' => 'Future coordination, notification, and status history.',
    ],
];

$workflowSteps = [
    [
        'title' => 'Plan Meeting',
        'description' => 'Staff records meeting purpose, schedule, and venue.',
    ],
    [
        'title' => 'Add Participants',
        'description' => 'Required legislators, staff, and guests are selected.',
    ],
    [
        'title' => 'Attach Agenda',
        'description' => 'A relevant legislative agenda is linked.',
    ],
    [
        'title' => 'Confirm',
        'description' => 'Meeting details are reviewed before communication.',
    ],
    [
        'title' => 'Notify',
        'description' => 'Future email notifications are sent to participants.',
    ],
    [
        'title' => 'Coordinate Changes',
        'description' => 'Any schedule or participant update triggers future revised notices.',
    ],
];

$quickActions = [
    [
        'icon' => 'bi-calendar-event',
        'title' => 'Plan Meeting',
        'description' => 'Open the meeting coordination workspace.',
    ],
    [
        'icon' => 'bi-people',
        'title' => 'Participant List',
        'description' => 'Manage meeting participants.',
    ],
    [
        'icon' => 'bi-envelope-paper',
        'title' => 'Notification Center',
        'description' => 'Review future meeting notification actions.',
    ],
];

$recordFields = [
    'Meeting title',
    'Meeting type',
    'Date and time',
    'Venue / virtual link',
    'Committee or responsible body',
    'Participants',
    'Linked agenda',
    'Meeting status',
    'Notification status',
    'Change / reschedule remarks',
];

$previousModule = [
    'label' => 'Calendar Scheduling Module',
    'href' => 'modules/calendar/index.php',
];
$nextModule = [
    'label' => 'Deadline Tracking Module',
    'href' => 'modules/deadlines/index.php',
];

require __DIR__ . '/../../includes/lacms_scope_module.php';
