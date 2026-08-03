<?php
declare(strict_types=1);

$candidateRows = $candidateRows ?? [];
?>

<div class="table-responsive">
    <table class="table meeting-table align-middle mb-0">
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
                        <div class="meeting-empty-state">
                            <i class="bi bi-people"></i>
                            <strong>No coordination candidates available</strong>
                            <span>Shared legislative records will appear here.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($candidateRows as $row): ?>
                <?php
                $search = strtolower(
                    ($row['reference_number'] ?? '') . ' ' .
                    ($row['title'] ?? '') . ' ' .
                    ($row['originating_office'] ?? '')
                );
                ?>

                <tr
                    class="meeting-data-row"
                    data-search="<?= e($search) ?>"
                    data-type="<?= e($row['item_type_code']) ?>"
                    data-priority="<?= e($row['priority_level']) ?>"
                    data-status="<?= e($row['current_status']) ?>"
                >
                    <td>
                        <span class="meeting-reference">
                            <?= e($row['reference_number']) ?>
                        </span>
                    </td>

                    <td>
                        <span
                            class="meeting-type-badge
                            <?= $row['item_type_code'] === 'ordinance'
                                ? 'ordinance'
                                : 'resolution'
                            ?>"
                        >
                            <?= e($row['item_type_name']) ?>
                        </span>
                    </td>

                    <td>
                        <div class="meeting-title-cell">
                            <strong><?= e($row['title']) ?></strong>
                            <small>Available for meeting coordination</small>
                        </div>
                    </td>

                    <td>
                        <?= e($row['originating_office'] ?: 'Not assigned') ?>
                    </td>

                    <td>
                        <span
                            class="meeting-priority-badge
                            <?= e(strtolower($row['priority_level'])) ?>"
                        >
                            <?= e($row['priority_level']) ?>
                        </span>
                    </td>

                    <td>
                        <span class="meeting-status-badge">
                            <?= e($row['current_status']) ?>
                        </span>
                    </td>

                    <td><?= e(formatDateTime($row['updated_at'])) ?></td>

                    <td>
                        <div class="meeting-row-actions">
                            <a
                                href="view.php?id=<?= (int)$row['id'] ?>"
                                class="meeting-action-button"
                                title="View details"
                            >
                                <i class="bi bi-eye"></i>
                            </a>

                            <button
                                type="button"
                                class="meeting-action-button"
                                data-coordinate-meeting="<?= (int)$row['id'] ?>"
                                data-reference="<?= e($row['reference_number']) ?>"
                                data-title="<?= e($row['title']) ?>"
                                title="Coordinate meeting"
                            >
                                <i class="bi bi-people"></i>
                            </button>

                            <button
                                type="button"
                                class="meeting-action-button"
                                data-meeting-history="<?= (int)$row['id'] ?>"
                                title="Meeting history"
                            >
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr id="meetingNoFilterResult" class="d-none">
                <td colspan="8">
                    <div class="meeting-empty-state compact">
                        <i class="bi bi-search"></i>
                        <strong>No matching coordination records</strong>
                        <span>Adjust the search or selected filters.</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="meeting-table-footer">
    <span id="meetingRecordCount">
        Showing <?= count($candidateRows) ?> candidate record(s)
    </span>

    <span>
        Meeting records will be stored during backend implementation.
    </span>
</div>
