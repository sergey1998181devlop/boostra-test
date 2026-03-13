<?php

namespace App\Dto;

class DocumentDto
{
    /** @var int */
    public $id;
    /** @var int */
    public $order_id;
    /** @var string */
    public $type;
    /** @var mixed */
    public $params;
    /** @var string|null */
    public $created_at;

    public static function fromDbRow(object $row): self
    {
        $dto = new self();
        $dto->id = (int)$row->id;
        $dto->order_id = (int)$row->order_id;
        $dto->type = (string)$row->type;
        $dto->params = $row->params ?? null;
        $dto->created_at = $row->created_at ?? ($row->created ?? null);
        return $dto;
    }
}


