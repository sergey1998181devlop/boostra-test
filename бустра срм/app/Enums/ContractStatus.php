<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

final class ContractStatus extends Enum
{
    private const SIGNED = 1;
    private const ISSUED = 2;
    private const CLOSED = 3;
    private const EXPIRED = 4;

    private const LABELS = [
        self::SIGNED => 'Подписан',
        self::ISSUED => 'Выдан',
        self::CLOSED => 'Закрыт',
        self::EXPIRED => 'Просрочен',
    ];

    public function getLabel(): string
    {
        return ContractStatus::LABELS[$this->getValue()];
    }
}
