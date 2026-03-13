<?php

namespace App\Repositories;

use App\Core\Database\SimplaDatabase;
use App\Dto\VoxRobotCallsDto;

class VoximplantRepository
{
    /** @var \Database */
    private $db;

    public function __construct()
    {
        $this->db = SimplaDatabase::getInstance()->db();
    }

    public function updateVoxRobotCalls(VoxRobotCallsDto $dto) {
        $this->db->query(
            "UPDATE s_vox_robot_calls c
             JOIN (
                 SELECT client_phone, MAX(updated_at) AS max_ts
                 FROM s_vox_robot_calls
                 WHERE client_phone = ?
                 GROUP BY client_phone
             ) t ON c.client_phone = t.client_phone AND c.updated_at = t.max_ts
             SET 
                c.status = ?, 
                c.updated_at = NOW(),
                c.is_redirected_manager = ?, 
                c.type = ?",
            $dto->phone,
            (int)$dto->status,
            $dto->is_redirected_manager,
            $dto->type
        );

        return $this->db->affected_rows();
    }
}