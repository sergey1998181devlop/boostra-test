<?php

namespace App\Containers\InfrastructureSection\Table;

use App\Containers\InfrastructureSection\Contracts\DtoInterface;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use DateTime;

abstract class BaseTable
{
    private RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function getByPrimary(int $id): DtoInterface
    {
        return $this->repository->getByPrimary($id);
    }

    protected function prepareSelect(array &$select): void
    {
        if (empty($select)) {
            $select = ['*'];
        } else {
            $select = array_map(function ($item) {
                return $this->getTableName() . '.' . $item;
            }, $select);
        }
    }

    public function get(array $select = ['*'], array $filters = [], int $limit = 0, int $offset = 0): ResultDTO
    {
        $this->prepareSelect($select);
        $table = $this->getTableName();

        $query = 'SELECT ' . implode(', ', $select) . ' FROM ' . $table;

        if (!empty($filters)) {
            $query .= ' WHERE ' . $this->getFilters($filters);
        }

        if ($limit > 0) {
            $query .= ' LIMIT ' . $limit;
        }

        if ($offset > 0) {
            $query .= ' OFFSET ' . $offset;
        }

        return $this->repository->exec($query);
    }

    protected function validateConditionValue($condition): string
    {
        if (is_string($condition) || is_int($condition)) {
            return '= ' . $condition;
        }

        if (is_array($condition)) {
            $query = 'IN (';
            foreach ($condition as $item) {
                if ($item instanceof DateTime) {
                    $item = "'" . $item->format('Y-m-d H:i:s') . "'";
                }
                $query .= $item . ', ';
            }
            return rtrim($query, ', ') . ')';
        }

        if ($condition instanceof DateTime) {
            return "= '" . $condition->format('Y-m-d H:i:s') . "'";
        }

        throw new \InvalidArgumentException('Invalid condition value');
    }

    protected function getFilters($filters): string
    {
        $query = '';
        $table = $this->getTableName();

        foreach ($filters as $key => $value) {
            $firstCharacter = substr($key, 0, 1);
            $field = preg_replace('/[^A-Za-z0-9_]/m', '', $key);

            if ($firstCharacter === '!') {
                $query .= 'NOT ' . $table . '.' . $field . ' ' . $this->validateConditionValue($value) . ' ';
            }

            if (in_array($firstCharacter, ['>', '<', '>=', '<=', '<>'])) {
                $query .= $table . '.' . $field . ' ' . $firstCharacter . ' ' .
                $value instanceof DateTime ? "'" . $value->format('Y-m-d H:i:s') . "'" : $value . ' ';
            }

            if (!preg_match("/[^A-Za-z0-9_]/", $firstCharacter)) {
                $query .= $table . '.' . $field . ' ' . $this->validateConditionValue($value) . ' ';
            }

            if ($key === 'OR') {
                $query .= '(';
                foreach ($value as $item) {
                    $query .= $this->getFilters($item);
                    $query .= ' OR ';
                }
                $query = rtrim($query, ' OR ');

                $query .= ')';
            } else {
                $query .= ' AND ';
            }
        }
        $query = rtrim($query, ' AND ');
        $query .= ' ';

        return $query;
    }
}
