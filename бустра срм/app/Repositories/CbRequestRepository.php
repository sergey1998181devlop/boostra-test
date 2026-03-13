<?php

namespace App\Repositories;

use App\Models\CbRequest;
use App\Models\CbRequestHistory;

class CbRequestRepository
{
    private CbRequest $cbRequest;
    private CbRequestHistory $cbRequestHistory;

    public function __construct()
    {
        $this->cbRequest = new CbRequest();
        $this->cbRequestHistory = new CbRequestHistory();
    }

    /**
     * @param array $data
     * @return int ID созданного запроса
     */
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $statement = $this->cbRequest->insert($data)->getData();
        if (!$statement) {
            return 0;
        }

        $id = (int) $this->cbRequest->db->id();
        return $id > 0 ? $id : 0;
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $statement = $this->cbRequest->update($data, ['id' => $id])->getData();
        if (!$statement) {
            return false;
        }

        return true;
    }

    /**
     * @param string $externalId
     * @return object|null
     */
    public function findByExternalId(string $externalId): ?object
    {
        $row = $this->cbRequest
            ->get('*', ['external_id' => $externalId])
            ->getData();

        if (!$row) {
            return null;
        }

        return (object) $row;
    }

    /**
     * @param int $requestId
     * @param int|null $managerId
     * @param string $action
     * @param string $details
     */
    public function logHistory(int $requestId, ?int $managerId, string $action, string $details): void
    {
        $this->cbRequestHistory->insert([
            'request_id' => $requestId,
            'manager_id' => $managerId,
            'action' => $action,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
