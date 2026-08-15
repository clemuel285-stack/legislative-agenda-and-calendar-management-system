<?php
declare(strict_types=1);

$moduleTitle = 'Legislative Agenda Management Module';
$moduleKey = 'agendas';
$moduleIcon = 'bi-list-check';
$moduleDescription = 'Allows authorized users to create, organize, update, prioritize, and manage legislative agendas in one centralized workspace.';
$primaryAction = 'New Agenda';
$problemLink = 'Difficulty in organizing legislative agendas and related meeting schedules because agenda records are handled through fragmented manual processes.';

$moduleFeatures = [
    [
        'icon' => 'bi-file-earmark-plus',
        'title' => 'Create Legislative Agenda',
        'description' => 'Create a structured agenda for a session, committee meeting, or legislative activity.',
    ],
    [
        'icon' => 'bi-list-ol',
        'title' => 'Agenda Item Organization',
        'description' => 'Arrange agenda items, sequence, topic, sponsor, committee, and supporting references.',
    ],
    [
        'icon' => 'bi-arrow-down-up',
        'title' => 'Reorder & Prioritize',
        'description' => 'Prepare sequence-based ordering and priority arrangement for agenda items.',
    ],
    [
        'icon' => 'bi-pencil-square',
        'title' => 'Agenda Updates',
        'description' => 'Revise agenda details, items, notes, and status before finalization.',
    ],
    [
        'icon' => 'bi-link-45deg',
        'title' => 'Meeting Linkage',
        'description' => 'Connect an agenda to its scheduled meeting or legislative event.',
    ],
    [
        'icon' => 'bi-search',
        'title' => 'Search & Filter',
        'description' => 'Locate agendas by date, committee, meeting, status, title, or keyword.',
    ],
];

$moduleSections = [
    [
        'icon' => 'bi-inboxes',
        'title' => 'Agenda Registry',
        'description' => 'Centralized list of draft, scheduled, finalized, and archived agendas.',
    ],
    [
        'icon' => 'bi-list-ol',
        'title' => 'Agenda Items',
        'description' => 'Detailed items, sequence, presenters, and references.',
    ],
    [
        'icon' => 'bi-calendar-link',
        'title' => 'Linked Meetings',
        'description' => 'View the meeting or event associated with an agenda.',
    ],
    [
        'icon' => 'bi-pencil-square',
        'title' => 'Draft & Revision',
        'description' => 'Prepare and update agendas before finalization.',
    ],
    [
        'icon' => 'bi-check2-circle',
        'title' => 'Finalized Agendas',
        'description' => 'Client-ready view for approved or finalized agenda sets.',
    ],
    [
        'icon' => 'bi-clock-history',
        'title' => 'Agenda History',
        'description' => 'Future revision and activity history for accountability.',
    ],
];

$workflowSteps = [
    [
        'title' => 'Create Agenda',
        'description' => 'Authorized user opens a new legislative agenda.',
    ],
    [
        'title' => 'Add Items',
        'description' => 'Agenda items, descriptions, proponents, and references are added.',
    ],
    [
        'title' => 'Organize',
        'description' => 'Items are ordered and grouped for the intended meeting.',
    ],
    [
        'title' => 'Link Schedule',
        'description' => 'Agenda is connected to a calendar event or meeting.',
    ],
    [
        'title' => 'Finalize',
        'description' => 'Agenda is reviewed and marked ready for circulation.',
    ],
    [
        'title' => 'Notify',
        'description' => 'Future email notification informs participants of the finalized agenda.',
    ],
];

$quickActions = [
    [
        'icon' => 'bi-file-earmark-plus',
        'title' => 'Create Agenda',
        'description' => 'Start a new legislative agenda.',
    ],
    [
        'icon' => 'bi-inboxes',
        'title' => 'Agenda Registry',
        'description' => 'Browse all agenda records.',
    ],
    [
        'icon' => 'bi-calendar-link',
        'title' => 'Linked Meetings',
        'description' => 'Navigate agendas connected to scheduled meetings.',
    ],
];

$recordFields = [
    'Agenda title',
    'Agenda date',
    'Meeting or session type',
    'Committee / responsible body',
    'Agenda item sequence',
    'Agenda item title and description',
    'Sponsor / presenter',
    'Supporting reference',
    'Agenda status',
    'Linked calendar event',
];

$previousModule = null;
$nextModule = [
    'label' => 'Calendar Scheduling Module',
    'href' => 'modules/calendar/index.php',
];

require __DIR__ . '/../../includes/lacms_scope_module.php';
