<?php

namespace Tests\Unit\Helpers;

/**
 * FakeMedoo - имитирует интерфейс Medoo\Medoo для тестирования
 */
class FakeMedoo
{
    /** @var \PDO|null */
    public $pdo;

    /** @var array */
    public $inserts = [];

    /** @var array */
    public $updates = [];

    /** @var array */
    public $hasChecks = [];

    /** @var array */
    public $queries = [];

    /** @var array */
    public $selects = [];

    /** @var array */
    private $hasResults = [];

    /** @var array */
    private $selectResults = [];

    /** @var int */
    private $insertId = 1;

    /** @var int */
    private $countResult = 0;

    /** @var array */
    private $getResult = [];

    /** @var array */
    private $queryResults = [];

    /**
     * Установить результат для has()
     *
     * @param string $table
     * @param array $where
     * @param bool $result
     */
    public function setHasResult(string $table, array $where, bool $result): void
    {
        $key = $table . ':' . json_encode($where);
        $this->hasResults[$key] = $result;
    }

    /**
     * Установить результат для select()
     *
     * @param string $table
     * @param array $result
     */
    public function setSelectResult(string $table, array $result): void
    {
        $this->selectResults[$table] = $result;
    }

    /**
     * Установить результат для count()
     *
     * @param int $count
     */
    public function setCountResult(int $count): void
    {
        $this->countResult = $count;
    }

    /**
     * Установить результат для get()
     *
     * @param array $result
     */
    public function setGetResult(array $result): void
    {
        $this->getResult = $result;
    }

    /**
     * Установить результат для query()
     *
     * @param string $sqlPattern
     * @param FakePdoStatement $result
     */
    public function setQueryResult(string $sqlPattern, FakePdoStatement $result): void
    {
        $this->queryResults[$sqlPattern] = $result;
    }

    /**
     * Установить ID для следующего insert
     *
     * @param int $id
     */
    public function setNextInsertId(int $id): void
    {
        $this->insertId = $id;
    }

    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insert(string $table, array $data): int
    {
        $this->inserts[] = ['table' => $table, 'data' => $data];
        return $this->insertId++;
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int
     */
    public function update(string $table, array $data, array $where): int
    {
        $this->updates[] = ['table' => $table, 'data' => $data, 'where' => $where];

        // Проверяем, есть ли настроенный результат для этой таблицы
        $key = $table . ':' . json_encode($where);
        if (isset($this->hasResults[$key]) && $this->hasResults[$key]) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function has(string $table, array $where): bool
    {
        $key = $table . ':' . json_encode($where);
        $this->hasChecks[] = ['table' => $table, 'where' => $where];
        return $this->hasResults[$key] ?? false;
    }

    /**
     * @param string $table
     * @param mixed $columns
     * @param array $where
     * @return array
     */
    public function select(string $table, $columns, array $where = []): array
    {
        $this->selects[] = ['table' => $table, 'columns' => $columns, 'where' => $where];
        return $this->selectResults[$table] ?? [];
    }

    /**
     * @param string $table
     * @return int
     */
    public function count(string $table): int
    {
        return $this->countResult;
    }

    /**
     * @param string $table
     * @param mixed $columns
     * @return array
     */
    public function get(string $table, $columns): array
    {
        return $this->getResult;
    }

    /**
     * @param string $sql
     * @return FakePdoStatement|null
     */
    public function query(string $sql)
    {
        $this->queries[] = $sql;

        // Ищем подходящий результат по паттерну
        foreach ($this->queryResults as $pattern => $result) {
            if (strpos($sql, $pattern) !== false) {
                return $result;
            }
        }

        return new FakePdoStatement();
    }

    /**
     * @return \PDO
     */
    public function pdo(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO('sqlite::memory:');
        }
        return $this->pdo;
    }

    /**
     * Сбросить все записанные вызовы
     */
    public function reset(): void
    {
        $this->inserts = [];
        $this->updates = [];
        $this->hasChecks = [];
        $this->queries = [];
        $this->selects = [];
    }
}
