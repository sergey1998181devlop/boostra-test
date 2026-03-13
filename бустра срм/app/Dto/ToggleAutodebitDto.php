<?php

namespace App\Dto;

use App\Core\Application\Request\Request;
use App\Repositories\ManagerRepository;

/**
 * DTO для переключения автодебета
 */
class ToggleAutodebitDto
{
    public const AUTODEBIT_DISABLED = 0;
    public const AUTODEBIT_ENABLED = 1;

    /** @var int */
    public int $orderId;

    /** @var int|null */
    public ?int $value;

    /** @var int */
    public int $managerId;

    /** @var int|null */
    public ?int $userId;

    /**
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        $dto = new self();
        $dto->orderId = (int)$request->getParam('id');
        $valueRaw = $request->input('value') ?? $request->json('value');
        $dto->value = $valueRaw !== null ? (int)$valueRaw : null;
        $dto->managerId = (int)($request->input('manager_id') ?? $request->json('manager_id') ?? ManagerRepository::MANAGER_SYSTEM_ID);
        
        return $dto;
    }

    /**
     * Валидирует входные данные DTO
     * @return array Массив ошибок валидации
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->orderId)) {
            $errors[] = 'Не указан ID заказа';
        }

        if (is_null($this->value)) {
            $errors[] = 'Недостаточно параметров: value — обязательный параметр';
        } elseif (!in_array($this->value, [self::AUTODEBIT_DISABLED, self::AUTODEBIT_ENABLED], true)) {
            $errors[] = 'Некорректное значение параметра value, ожидается 0 или 1';
        }

        return $errors;
    }
}
