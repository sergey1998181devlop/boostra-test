<?php

namespace App\Dto;
use App\Enums\LicenseServiceType;

/**
 * DTO для отправки SMS с лицензионным ключом
 */
class SendLicenseSmsDto
{
    /** @var int */
    public $order_id;

    /** @var string */
    public $type;


    /** @var string */
    public $phone;

    /** @var int */
    public $manager_id;

    /**
     * @param array $data
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        $dto = new self();
        $dto->order_id = isset($data['order_id']) ? (int)$data['order_id'] : null;
        $dto->type = $data['type'] ?? '';
        $dto->phone = formatPhoneNumber($data['phone'] ?? '');
        $dto->manager_id = isset($data['manager_id']) ? (int)$data['manager_id'] : null;
        return $dto;
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->order_id)) {
            $errors[] = 'Не указан ID заказа';
        }

        if (empty($this->type)) {
            $errors[] = 'Не указан тип SMS';
        }


        if (empty($this->phone)) {
            $errors[] = 'Не указан номер телефона';
        }

        if (empty($this->manager_id)) {
            $errors[] = 'Не указан ID менеджера';
        }

        if (!empty($this->type) && !LicenseServiceType::validate($this->type)) {
            $errors[] = 'Некорректный тип SMS';
        }

        return $errors;
    }
}
