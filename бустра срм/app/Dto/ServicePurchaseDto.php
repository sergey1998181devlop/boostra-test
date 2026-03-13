<?php

namespace App\Dto;

class ServicePurchaseDto
{
    /** @var int */
    public $order_id;
    /** @var int */
    public $user_id;
    /** @var string */
    public $date_added;
    /** @var float */
    public $amount;
    /** @var float */
    public $amount_total_returned;

    public static function fromDbRow(object $row): self
    {
        $dto = new self();
        $dto->order_id = (int)$row->order_id;
        $dto->user_id = (int)$row->user_id;
        $dto->date_added = (string)($row->date_added ?? $row->created ?? '');
        $dto->amount = isset($row->amount) ? (float)$row->amount : 0.0;
        $dto->amount_total_returned = isset($row->amount_total_returned) ? (float)$row->amount_total_returned : 0.0;
        return $dto;
    }
}


