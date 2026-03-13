<?php

namespace App\Dto;

class ExtraServicesInformDto
{
    /** @var int */
    public $user_id;
    /** @var string */
    public $contract;
    /** @var int */
    public $order_id;
    /** @var int */
    public $manager_id;
    /** @var string */
    public $service_name;
    /** @var string */
    public $sms_phone;
    /** @var int|null */
    public $sms_template_id;
    /** @var string */
    public $sms_type;
    /** @var string|null */
    public $license_key;
    /** @var string */
    public $created_at;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->user_id = (int)$data['user_id'];
        $dto->contract = (string)$data['contract'];
        $dto->order_id = (int)$data['order_id'];
        $dto->manager_id = (int)$data['manager_id'];
        $dto->service_name = (string)$data['service_name'];
        $dto->sms_phone = (string)$data['sms_phone'];
        $dto->sms_template_id = isset($data['sms_template_id']) ? (int)$data['sms_template_id'] : null;
        $dto->sms_type = (string)$data['sms_type'];
        $dto->license_key = $data['license_key'] ?? null;
        $dto->created_at = (string)$data['created_at'];
        return $dto;
    }

    public function toDbArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'contract' => $this->contract,
            'order_id' => $this->order_id,
            'manager_id' => $this->manager_id,
            'service_name' => $this->service_name,
            'sms_phone' => $this->sms_phone,
            'sms_template_id' => $this->sms_template_id,
            'sms_type' => $this->sms_type,
            'license_key' => $this->license_key,
            'created_at' => $this->created_at,
        ];
    }
}


