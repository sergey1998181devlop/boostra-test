<?php

namespace App\Dto;

/**
 * DTO для отправки SMS через шаблоны
 */
class SendSmsDto
{
    /** @var string */
    public $phone;

    /** @var int */
    public $template_id;

    /** @var array */
    public $params = [];

    /** @var string */
    public $message;

    /**
     * @param array $data
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        $dto = new self();
        $dto->phone = formatPhoneNumber($data['phone'] ?? '');
        $dto->template_id = isset($data['template_id']) ? (int)$data['template_id'] : null;
        $dto->params = $data['params'] ?? [];
        return $dto;
    }

    /**
     * @return array
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->phone)) {
            $errors[] = 'Не указан номер телефона';
        }

        if (empty($this->template_id)) {
            $errors[] = 'Не указан ID шаблона';
        }

        return $errors;
    }
}