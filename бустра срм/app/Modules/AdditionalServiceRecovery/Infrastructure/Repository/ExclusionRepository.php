<?php

namespace App\Modules\AdditionalServiceRecovery\Infrastructure\Repository;

use App\Modules\AdditionalServiceRecovery\Application\DTO\ExclusionRequest;
use Database;

class ExclusionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function add(ExclusionRequest $request): void
    {
        $data = [
            'user_id' => $request->userId,
            'order_id' => !empty($request->orderId) ? $request->orderId : null,
            'service_key' => !empty($request->serviceKey) ? $request->serviceKey : null,
            'reason' => $request->reason,
            'manager_id' => $request->managerId,
            'expires_at' => $request->expiresAt ? $request->expiresAt->format('Y-m-d H:i:s') : null,
            'deleted_at' => null
        ];

        $this->db->query("INSERT INTO s_service_recovery_exclusions SET ?%", $data);
    }

    public function deactivate(int $exclusionId): void
    {
        $this->db->query("UPDATE s_service_recovery_exclusions SET deleted_at = NOW() WHERE id = ?", $exclusionId);
    }

    public function findAllActive(): array
    {
        $sql = "
            SELECT 
                e.*,
                u.firstname,
                u.lastname,
                m.name as manager_name
            FROM s_service_recovery_exclusions e
            LEFT JOIN s_users u ON u.id = e.user_id
            LEFT JOIN s_managers m ON m.id = e.manager_id
            WHERE e.deleted_at IS NULL
            ORDER BY e.created_at DESC
        ";
        $this->db->query($sql);
        return $this->db->results();
    }
}
