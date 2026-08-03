<?php
declare(strict_types=1);

$candidateRows = $candidateRows ?? [];
?>

<div class="table-responsive">
    <table class="table deadline-table align-middle mb-0">
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
                        <div class="deadline-empty-state">
                            <i class="bi bi-alarm"></i>
                            <strong>No deadline candidates available</strong>
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
                    class="deadline-data-row"
                    data-search="<?= e($search) ?>"
                    data-type="<?= e($row['item_type_code']) ?>"
                    data-priority="<?= e($row['priority_level']) ?>"
                    data-status="<?= e($row['current_status']) ?>"
                >
                    <td>
                        <span class="deadline-reference">
                            <?= e($row['reference_number']) ?>
                        </span>
                    </td>

                    <td>
                        <span
                            class="deadline-type-badge
                            <?= $row['item_type_code'] === 'ordinance'
                                ? 'ordinance'
                                : 'resolution'
                            ?>"
                        >
                            <?= e($row['item_type_name']) ?>
                        </span>
                    </td>

                    <td>
                        <div class="deadline-title-cell">
                            <strong><?= e($row['title']) ?></strong>
                            <small>Available for deadline tracking</small>
                        </div>
                    </td>

                    <td>
                        <?= e($row['originating_office'] ?: 'Not assigned') ?>
                    </td>

                    <td>
                        <span
                            class="deadline-priority-badge
                            <?= e(strtolower($row['priority_level'])) ?>"
                        >
                            <?= e($row['priority_level']) ?>
                        </span>
                    </td>

                    <td>
                        <span class="deadline-status-badge">
                            <?= e($row['current_status']) ?>
                        </span>
                    </td>

                    <td><?= e(formatDateTime($row['updated_at'])) ?></td>

                    <td>
                        <div class="deadline-row-actions">
                            <a
                                href="view.php?id=<?= (int)$row['id'] ?>"
                                class="deadline-action-button"
                                title="View details"
                            >
                                <i class="bi bi-eye"></i>
                            </a>

                            <button
                                type="button"
                                class="deadline-action-button"
                                data-create-deadline="<?= (int)$row['id'] ?>"
                                data-reference="<?= e($row['reference_number']) ?>"
                                data-title="<?= e($row['title']) ?>"
                                title="Create deadline"
                            >
                                <i class="bi bi-alarm"></i>
                            </button>

                            <button
                                type="button"
                                class="deadline-action-button"
                                data-deadline-history="<?= (int)$row['id'] ?>"
                                title="Deadline history"
                            >
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr id="deadlineNoFilterResult" class="d-none">
                <td colspan="8">
                    <div class="deadline-empty-state compact">
                        <i class="bi bi-search"></i>
                        <strong>No matching deadline records</strong>
                        <span>Adjust the search or selected filters.</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="deadline-table-footer">
    <span id="deadlineRecordCount">
        Showing <?= count($candidateRows) ?> candidate record(s)
    </span>

    <span>
        Deadline records will be stored during backend implementation.
    </span>
</div>
