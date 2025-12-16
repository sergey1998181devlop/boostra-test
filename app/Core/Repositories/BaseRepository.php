<?php

namespace App\Core\Repositories;

use App\Core\Models\BaseModel;

/**
 * Base Repository with Laravel-like methods
 * Базовый репозиторий для всех модулей с методами по аналогии с Laravel ORM
 */
abstract class BaseRepository
{
    protected BaseModel $model;
    protected string $table;

    public function __construct()
    {
        $this->model = new BaseModel();
        $this->model->table = $this->getTable();
    }

    /**
     * Get table name for repository
     * Должен быть переопределён в дочернем классе
     *
     * @return string
     */
    abstract protected function getTable(): string;

    /**
     * Find record by ID
     * Аналог: Model::find($id)
     *
     * @param int $id
     * @return object|null
     */
    public function find(int $id)
    {
        return $this->model
            ->get(['id' => $id])
            ->getData();
    }

    /**
     * Find record by ID or fail
     * Аналог: Model::findOrFail($id)
     *
     * @param int $id
     * @return object
     * @throws \Exception
     */
    public function findOrFail(int $id)
    {
        $result = $this->find($id);
        
        if (!$result) {
            throw new \Exception("Record with ID {$id} not found in table {$this->model->table}");
        }
        
        return $result;
    }

    /**
     * Get all records
     * Аналог: Model::all()
     *
     * @param array $columns
     * @return array
     */
    public function all(array $columns = [])
    {
        return $this->model
            ->select($columns)
            ->getData();
    }

    /**
     * Get first record
     * Аналог: Model::first()
     *
     * @param array $conditions
     * @param array|null $joins
     * @return object|null
     */
    public function first(array $conditions = [], ?array $joins = null)
    {
        $normalizedConditions = $this->normalizeConditions($conditions);
        
        return $this->model
            ->get($normalizedConditions, $joins)
            ->getData();
    }

    /**
     * Get records with conditions
     * Аналог: Model::where(...)->get()
     *
     * Поддерживает два формата:
     * 1. Ассоциативный массив: ['field' => 'value'] или ['field' => ['operator', 'value']]
     * 2. Laravel-like массив: [['field', 'operator', 'value']] или [['field', 'value']]
     *
     * @param array $conditions
     * @param array|null $joins
     * @param mixed $orderBy
     * @param string $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function where(
        array $conditions = [],
        ?array $joins = null,
        $orderBy = null,
        string $order = 'desc',
        ?int $limit = null,
        ?int $offset = null
    ) {
        $normalizedConditions = $this->normalizeConditions($conditions);
        
        return $this->model
            ->select($normalizedConditions, $joins, $orderBy, $order, $offset, $limit)
            ->getData();
    }

    /**
     * Normalize conditions to support Laravel-like syntax
     * Поддерживаемые форматы:
     * - ['field' => 'value'] -> ['field' => 'value']
     * - ['field' => ['>', 10]] -> ['field' => ['>', 10]]
     * - [['field', 'value']] -> ['field' => 'value']
     * - [['field', '=', 'value']] -> ['field' => ['=', 'value']]
     * - [['field', '>', 10]] -> ['field' => ['>', 10]]
     *
     * @param array $conditions
     * @return array
     */
    protected function normalizeConditions(array $conditions): array
    {
        // Если пустой массив, вернуть как есть
        if (empty($conditions)) {
            return $conditions;
        }

        // Проверяем, является ли это Laravel-like синтаксисом
        // (массив массивов, где первый элемент - строка с именем поля)
        $isLaravelSyntax = isset($conditions[0]) && is_array($conditions[0]);

        if (!$isLaravelSyntax) {
            // Уже в правильном формате
            return $conditions;
        }

        // Преобразуем Laravel-like синтаксис
        $normalized = [];

        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                continue;
            }

            $count = count($condition);

            if ($count === 2) {
                // [['field', 'value']] -> ['field' => 'value']
                [$field, $value] = $condition;
                $normalized[$field] = $value;
            } elseif ($count === 3) {
                // [['field', '=', 'value']] -> ['field' => 'value'] (если оператор =)
                // [['field', '>', 'value']] -> ['field' => ['>', 'value']]
                [$field, $operator, $value] = $condition;
                
                if ($operator === '=') {
                    $normalized[$field] = $value;
                } else {
                    $normalized[$field] = [$operator, $value];
                }
            }
        }

        return $normalized;
    }

    /**
     * Create new record
     * Аналог: Model::create($data)
     *
     * @param array $data
     * @return int Last insert ID
     */
    public function create(array $data)
    {
        return $this->model
            ->insert($data)
            ->getData();
    }

    /**
     * Update record(s)
     * Аналог: Model::where(...)->update($data)
     *
     * @param array $data
     * @param array $where
     * @return int Affected rows count
     */
    public function update(array $data, array $where)
    {
        $normalizedWhere = $this->normalizeConditions($where);
        
        return $this->model
            ->update($data, $normalizedWhere)
            ->getData();
    }

    /**
     * Update or create record
     * Аналог: Model::updateOrCreate($attributes, $values)
     *
     * @param array $attributes - условия поиска
     * @param array $values - данные для обновления/создания
     * @return int
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $existing = $this->first($attributes);
        
        if ($existing) {
            $this->update($values, $attributes);
            return $existing->id ?? 0;
        }
        
        return $this->create(array_merge($attributes, $values));
    }

    /**
     * Delete record(s)
     * Аналог: Model::where(...)->delete()
     *
     * @param array $where
     * @return int Affected rows count
     */
    public function delete(array $where)
    {
        $normalizedWhere = $this->normalizeConditions($where);
        
        return $this->model
            ->delete($normalizedWhere)
            ->getData();
    }

    /**
     * Count records
     * Аналог: Model::count()
     *
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = [])
    {
        $normalizedConditions = $this->normalizeConditions($conditions);
        
        $result = $this->model
            ->select($normalizedConditions, null, null, 'desc', null, null, true)
            ->getData();
        
        return (int) ($result[0]->count ?? 0);
    }

    /**
     * Check if record exists
     * Аналог: Model::where(...)->exists()
     *
     * @param array $conditions
     * @return bool
     */
    public function exists(array $conditions)
    {
        $normalizedConditions = $this->normalizeConditions($conditions);
        return $this->count($normalizedConditions) > 0;
    }

    /**
     * Paginate results
     * Аналог: Model::paginate($perPage)
     *
     * @param int $perPage
     * @param int $page
     * @param array $conditions
     * @param array|null $joins
     * @param mixed $orderBy
     * @param string $order
     * @return array
     */
    public function paginate(
        int $perPage = 15,
        int $page = 1,
        array $conditions = [],
        ?array $joins = null,
        $orderBy = null,
        string $order = 'desc'
    ) {
        $offset = ($page - 1) * $perPage;
        
        $items = $this->where($conditions, $joins, $orderBy, $order, $perPage, $offset);
        $total = $this->count($conditions);
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Pluck specific column values
     * Аналог: Model::pluck('column')
     *
     * @param string $column
     * @param array $conditions
     * @return array
     */
    public function pluck(string $column, array $conditions = [])
    {
        $normalizedConditions = $this->normalizeConditions($conditions);
        $results = $this->where($normalizedConditions);
        
        return array_map(function($item) use ($column) {
            return $item->$column ?? null;
        }, $results);
    }

    /**
     * Get chunk of records with generator
     * Аналог: Model::chunk($size, callback)
     *
     * @param int $pageSize
     * @param array|null $columns
     * @param array|null $conditions
     * @param array|null $joins
     * @return \Generator
     */
    public function chunk(
        int $pageSize,
        ?array $columns = null,
        ?array $conditions = null,
        ?array $joins = null
    ) {
        return $this->model->eachChunk($pageSize, $columns, $conditions, $joins);
    }

    /**
     * Execute raw query
     * Аналог: DB::raw($query)
     *
     * @param string $sql
     * @param mixed ...$params
     * @return array
     */
    public function query(string $sql, ...$params)
    {
        return $this->model
            ->query($sql, ...$params)
            ->results()
            ->getData();
    }

    /**
     * Get single result from raw query
     *
     * @param string $sql
     * @param mixed ...$params
     * @return object|null
     */
    public function queryFirst(string $sql, ...$params)
    {
        return $this->model
            ->query($sql, ...$params)
            ->result()
            ->getData();
    }

    /**
     * Begin transaction
     * Аналог: DB::beginTransaction()
     */
    public function beginTransaction()
    {
        $this->model->db->query('START TRANSACTION');
    }

    /**
     * Commit transaction
     * Аналог: DB::commit()
     */
    public function commit()
    {
        $this->model->db->query('COMMIT');
    }

    /**
     * Rollback transaction
     * Аналог: DB::rollback()
     */
    public function rollback()
    {
        $this->model->db->query('ROLLBACK');
    }

    /**
     * Insert or update record (MySQL REPLACE)
     *
     * @param array $data
     * @return int
     */
    public function replace(array $data)
    {
        return $this->model
            ->replace($data)
            ->getData();
    }

    /**
     * Get latest records ordered by ID
     * Аналог: Model::latest()
     *
     * @param int $limit
     * @param array $conditions
     * @return array
     */
    public function latest(int $limit = 10, array $conditions = [])
    {
        return $this->where($conditions, null, 'id', 'desc', $limit);
    }

    /**
     * Get oldest records ordered by ID
     * Аналог: Model::oldest()
     *
     * @param int $limit
     * @param array $conditions
     * @return array
     */
    public function oldest(int $limit = 10, array $conditions = [])
    {
        return $this->where($conditions, null, 'id', 'asc', $limit);
    }
}
