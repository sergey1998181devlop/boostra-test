<?php

namespace api\enums;

abstract class AbstractEnum
{
    protected string $value;

    protected function __construct(string $value)
    {
        if (!in_array($value, static::getAvailableValues(), true)) {
            throw new \InvalidArgumentException(sprintf(
                'The wrong value of "%s". Permissible values: %s',
                $value,
                implode(', ', static::getAvailableValues())
            ));
        }

        $this->value = $value;
    }

    abstract public static function getAvailableValues(): array;

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}