<?php

namespace App\Dto;

class UserBalanceDto
{
    /** @var int */
    public $user_id;
    /** @var string|null */
    public $zaim_number;
    /** @var float|int */
    public $zaim_summ;

    public static function fromDbRow(object $row): self
    {
        $dto = new self();
        $dto->user_id = (int)$row->user_id;
        $dto->zaim_number = $row->zaim_number ?? null;
        $dto->zaim_summ = isset($row->zaim_summ) ? (float)$row->zaim_summ : 0;
        return $dto;
    }
}


