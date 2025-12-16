<?php

namespace App\Core\Models;

use App\Core\Database\BaseDatabase;
use App\Core\Models\Traits\ModelDebug;
use App\Core\Models\Traits\ModelQuery;
use Generator;

class BaseModel
{
    use ModelQuery, ModelDebug;

    /**
     * Table name
     */
    public string $table = '';

    /**
     * Store query data.
     */
    public $data;

    /**
     * Store DB instance.
     */
    protected $db;

    public function __construct()
    {
        $this->db = BaseDatabase::getInstance()->db();
        $this->getTableName();
    }

    /**
     * When the model is called it will extract the table name from the model.
     */
    public function getTableName(): void
    {
        if (!$this->table) {
            $explodedClassName = explode("\\", get_called_class());
            $this->table = strtolower($explodedClassName[array_key_last($explodedClassName)]);
        }
    }

    /**
     * Get query data in json format.
     */
    public function getJson(string $wrap = null): BaseModel
    {
        if ($wrap) {
            $this->data = json_encode([$wrap => $this->data]);
        } else {
            $this->data = json_encode($this->data);
        }
        return $this;
    }

    /**
     * Get query Data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Генератор для выборки записей порциями.
     *
     * @param int $pageSize Размер порции данных.
     * @param array|null $columns Массив выбираемых полей.
     * @param array|null $where Массив условий выборки.
     * @param array|null $join Массив join-условий.
     *
     * @return Generator Генератор, который возвращает объекты сущности из бд по одной записи.
     */
    public function eachChunk(
        int $pageSize, ?array $columns = null, ?array $where = null, ?array $join = null
    ): Generator
    {
        $offset = 0;

        while (true) {
            $conditionsWithLimit = $where;
            $conditionsWithLimit["LIMIT"] = [$offset, $pageSize];

            if (!empty($join)) {
                $this->select($join, $columns, $conditionsWithLimit);
            } else {
                $this->select($columns, $conditionsWithLimit);
            }
            $records = $this->getData();

            if (empty($records)) {
                break;
            }

            foreach ($records as $record) {
                yield $record;
            }

            $offset += $pageSize;
        }
    }
}
