<?php
declare(strict_types=1);

$moduleTitle = 'Calendar Scheduling Module';
$moduleKey = 'calendar';
$moduleIcon = 'bi-calendar-week';
$moduleDescription = 'Provides a centralized calendar for scheduling legislative meetings, events, deadlines, and official activities while reducing conflicts and human error.';
$primaryAction = 'Schedule Activity';
$problemLink = 'Increased risk of scheduling conflicts and human error because legislative events and meetings are maintained through separate manual schedules.';

$moduleFeatures = [
    [
        'icon' => 'bi-calendar-plus',
        'title' => 'Schedule Legislative Activity',
        'description' => 'Create meetings, sessions, events, deadlines, and other calendar entries.',
    ],
    [
        'icon' => 'bi-calendar3',
        'title' => 'Calendar Views',
        'description' => 'Prepare month, week, day, and agenda-style calendar navigation.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Conflict Detection',
        'description' => 'Represent future checking for overlapping meetings, rooms, participants, and schedules.',
    ],
    [
        'icon' => 'bi-geo-alt',
        'title' => 'Venue Management',
        'description' => 'Record venue, meeting room, virtual link, or hybrid meeting information.',
    ],
    [
        'icon' => 'bi-people',
        'title' => 'Participant Availability',
        'description' => 'Prepare participant and attendee schedule coordination.',
    ],
    [
        'icon' => 'bi-search',
        'title' => 'Search & Filter',
        'description' => 'Locate events by date, type, committee, venue, status, or keyword.',
    ],
];

$moduleSections = [
    [
        'icon' => 'bi-calendar3',
        'title' => 'Master Calendar',
        'description' => 'Centralized legislative calendar for all authorized users.',
    ],
    [
        'icon' => 'bi-calendar2-week',
        'title' => 'Week & Day View',
        'description' => 'Focused schedule views for operational planning.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Conflict Review',
        'description' => 'Future schedule-conflict checking and resolution.',
    ],
    [
        'icon' => 'bi-geo-alt',
        'title' => 'Venues',
        'description' => 'Meeting rooms, council facilities, and virtual locations.',
    ],
    [
        'icon' => 'bi-people',
        'title' => 'Participant Schedule',
        'description' => 'Future participant availability and attendance planning.',
    ],
    [
        'icon' => 'bi-clock-history',
        'title' => 'Schedule Changes',
        'description' => 'Future change history and rescheduling audit trail.',
    ],
];

$workflowSteps = [
    [
        'title' => 'Create Event',
        'description' => 'User enters the meeting or legislative activity.',
    ],
    [
        'title' => 'Set Schedule',
        'description' => 'Date, time, venue, participants, and event type are recorded.',
    ],
    [
        'title' => 'Check Conflicts',
        'description' => 'Future automation reviews overlapping schedules and resource conflicts.',
    ],
    [
        'title' => 'Confirm',
        'description' => 'Schedule is reviewed and confirmed by authorized staff.',
    ],
    [
        'title' => 'Notify',
        'description' => 'Participants receive future email notification of the event.',
    ],
    [
        'title' => 'Update',
        'description' => 'Rescheduling or changes trigger future notices and activity history.',
    ],
];

$quickActions = [
    [
        'icon' => 'bi-calendar-plus',
        'title' => 'Schedule Activity',
        'description' => 'Add a legislative meeting or event.',
    ],
    [
        'icon' => 'bi-calendar3',
        'title' => 'Master Calendar',
        'description' => 'Open the centralized legislative calendar.',
    ],
    [
        'icon' => 'bi-exclamation-triangle',
        'title' => 'Conflict Review',
        'description' => 'Review possible schedule conflicts.',
    ],
];

$recordFields = [
    'Event / meeting title',
    'Activity type',
    'Date',
    'Start and end time',
    'Venue / virtual link',
    'Committee / office',
    'Participants',
    'Linked agenda',
    'Event status',
    'Notification status',
];

$previousModule = [
    'label' => 'Legislative Agenda Management Module',
    'href' => 'modules/agendas/index.php',
];
$nextModule = [
    'label' => 'Meeting Coordination Module',
    'href' => 'modules/meetings/index.php',
];

require __DIR__ . '/../../includes/lacms_scope_module.php';
