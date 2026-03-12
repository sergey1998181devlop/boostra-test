<?php

namespace App\Core\Helpers;

class Collection
{
    protected $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function where($key, $operator = null, $value = null): Collection
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $filtered = array_filter($this->items, function($item) use ($key, $operator, $value) {
            return $this->compare(
                $this->getItemValue($item, $key),
                $operator,
                $value
            );
        });

        return new static(array_values($filtered));
    }

    public function whereIn($key, $values): Collection
    {
        $filtered = array_filter($this->items, function($item) use ($key, $values) {
            return in_array($this->getItemValue($item, $key), (array)$values);
        });

        return new static(array_values($filtered));
    }

    public function first()
    {
        return count($this->items) > 0 ? reset($this->items) : null;
    }

    public function all()
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return array_map(function($item) {
            return (array)$item;
        }, $this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    public function pluck($value, $key = null): array
    {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = $this->getItemValue($item, $value);

            if ($key) {
                $itemKey = $this->getItemValue($item, $key);
                $results[$itemKey] = $itemValue;
            } else {
                $results[] = $itemValue;
            }
        }

        return $results;
    }

    protected function getItemValue($item, $key)
    {
        if (is_object($item)) {
            return $item->$key ?? null;
        }

        return $item[$key] ?? null;
    }

    protected function compare($itemValue, $operator, $value): bool
    {
        switch ($operator) {
            case '=':
            case '==':  return $itemValue == $value;
            case '===': return $itemValue === $value;
            case '!=':
            case '<>':  return $itemValue != $value;
            case '!==': return $itemValue !== $value;
            case '>':   return $itemValue > $value;
            case '>=':  return $itemValue >= $value;
            case '<':   return $itemValue < $value;
            case '<=':  return $itemValue <= $value;
            case 'like':
                return stripos($itemValue ?? '', $value) !== false;
            case 'in':
                return in_array($itemValue, (array)$value);
            default:
                return false;
        }
    }
}