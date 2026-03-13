<?php

namespace Tests\Unit\Helpers;

/**
 * FakePdoStatement - имитирует PDOStatement для тестирования
 */
class FakePdoStatement
{
    /** @var int */
    private $rowCount = 0;

    /** @var array */
    private $fetchResult = [];

    /** @var array */
    private $fetchAllResult = [];

    /**
     * @param int $count
     * @return self
     */
    public function setRowCount(int $count): self
    {
        $this->rowCount = $count;
        return $this;
    }

    /**
     * @param array $result
     * @return self
     */
    public function setFetchResult(array $result): self
    {
        $this->fetchResult = $result;
        return $this;
    }

    /**
     * @param array $result
     * @return self
     */
    public function setFetchAllResult(array $result): self
    {
        $this->fetchAllResult = $result;
        return $this;
    }

    /**
     * @return int
     */
    public function rowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @param int $mode
     * @return array|false
     */
    public function fetch(int $mode = \PDO::FETCH_ASSOC)
    {
        return empty($this->fetchResult) ? false : $this->fetchResult;
    }

    /**
     * @param int $mode
     * @return array
     */
    public function fetchAll(int $mode = \PDO::FETCH_ASSOC): array
    {
        return $this->fetchAllResult;
    }
}
