<?php

namespace App\Service;

use App\Models\Changelog;

class ChangelogService
{
    /**
     * Add a log entry to the changelog table.
     *
     * @param int $managerId The ID of the manager who performed the action.
     * @param string $type The type of the log entry.
     * @param string|null $oldValues The old values before the action.
     * @param string|null $newValues The new values after the action.
     * @param int|null $orderId The ID of the related order (optional).
     * @param int|null $userId The ID of the related user (optional).
     * @param int|null $fileId The ID of the related file (optional).
     */
    public function addLog(
        int $managerId,
        string $type,
        string $oldValues,
        string $newValues,
        ?int $orderId = null,
        ?int $userId = null,
        ?int $fileId = null
    ) {
        $changelog = new Changelog();

        $changelog->insert([
            'manager_id' => $managerId,
            'created' => date('Y-m-d H:i:s'),
            'type' => $type,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'order_id' => $orderId,
            'user_id' => $userId,
            'file_id' => $fileId
        ]);
    }
}
