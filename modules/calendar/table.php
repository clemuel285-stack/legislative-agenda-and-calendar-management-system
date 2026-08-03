<?php
declare(strict_types=1);

$candidateRows = $candidateRows ?? [];
?>

<div class="table-responsive">
    <table class="table calendar-table align-middle mb-0">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Type</th>
                <th>Legislative Measure</th>
                <th>Originating Office</th>
                <th>Priority</th>
                <th>Workflow Status</th>
                <th>Last Updated</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($candidateRows)): ?>
                <tr>
                    <td colspan="8">
                        <div class="calendar-table-empty">
                            <i class="bi bi-calendar2-plus"></i>
                            <strong>No scheduling candidates available</strong>
                            <span>
                                Shared ordinance and resolution records
                                will appear here.
                            </span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($candidateRows as $candidate): ?>
                <?php
                $searchValue = strtolower(
                    ($candidate['reference_number'] ?? '') . ' ' .
                    ($candidate['title'] ?? '') . ' ' .
                    ($candidate['originating_office'] ?? '')
                );
                ?>

                <tr
                    class="calendar-queue-row"
                    data-search="<?= e($searchValue) ?>"
                    data-type="<?= e($candidate['item_type_code']) ?>"
                    data-priority="<?= e($candidate['priority_level']) ?>"
                    data-status="<?= e($candidate['current_status']) ?>"
                >
                    <td>
                        <span class="calendar-reference">
                            <?= e($candidate['reference_number']) ?>
                        </span>
                    </td>

                    <td>
                        <span
                            class="calendar-type-badge
                            <?= $candidate['item_type_code'] === 'ordinance'
                                ? 'ordinance'
                                : 'resolution'
                            ?>"
                        >
                            <?= e($candidate['item_type_name']) ?>
                        </span>
                    </td>

                    <td>
                        <div class="calendar-title-cell">
                            <strong><?= e($candidate['title']) ?></strong>
                            <small>
                                Candidate for calendar placement
                            </small>
                        </div>
                    </td>

                    <td>
                        <?= e(
                            $candidate['originating_office']
                            ?: 'Not assigned'
                        ) ?>
                    </td>

                    <td>
                        <span
                            class="calendar-priority-badge
                            <?= e(strtolower(
                                $candidate['priority_level']
                            )) ?>"
                        >
                            <?= e($candidate['priority_level']) ?>
                        </span>
                    </td>

                    <td>
                        <span class="calendar-status-badge">
                            <?= e($candidate['current_status']) ?>
                        </span>
                    </td>

                    <td>
                        <?= e(formatDateTime(
                            $candidate['updated_at']
                        )) ?>
                    </td>

                    <td>
                        <div class="calendar-row-actions">
                            <a
                                href="view.php?id=<?= (int)$candidate['id'] ?>"
                                class="calendar-action-button"
                                title="Open scheduling details"
                            >
                                <i class="bi bi-eye"></i>
                            </a>

                            <button
                                type="button"
                                class="calendar-action-button"
                                data-schedule-candidate="<?= (int)$candidate['id'] ?>"
                                data-reference="<?= e($candidate['reference_number']) ?>"
                                data-title="<?= e($candidate['title']) ?>"
                                title="Schedule this measure"
                            >
                                <i class="bi bi-calendar-plus"></i>
                            </button>

                            <button
                                type="button"
                                class="calendar-action-button"
                                data-calendar-history="<?= (int)$candidate['id'] ?>"
                                title="View scheduling history"
                            >
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr id="calendarQueueNoResult" class="d-none">
                <td colspan="8">
                    <div class="calendar-table-empty compact">
                        <i class="bi bi-search"></i>
                        <strong>No matching candidates</strong>
                        <span>Adjust the selected filters.</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="calendar-table-footer">
    <span id="calendarQueueCount">
        Showing <?= count($candidateRows) ?> candidate record(s)
    </span>

    <span>
        Saved activities will be separated after backend integration.
    </span>
</div>
