<?php

namespace App\Dto;

class VoxRobotCallsDto
{
    /** @var string */
    public $phone;
    /** @var string */
    public $status;
    /** @var string */
    public $is_redirected_manager;
    /** @var string */
    public $type;

    public static function fromRequest(array $data): self
    {
        $dto = new self();
        $dto->phone = $data['phone'];
        $dto->status = $data['status'] ?? '1';
        $dto->is_redirected_manager = $data['is_redirected_manager'] ?? '0';
        $dto->type = $data['type'] ?? 'missing';

        return $dto;
    }
}