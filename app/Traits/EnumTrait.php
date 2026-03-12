<?php

namespace app\Traits;

trait EnumTrait
{
    private static array $instances = [];

    /**
     * Get or create instance
     */
    protected static function getInstance(string $value)
    {
        if (!isset(self::$instances[$value])) {
            self::$instances[$value] = new self($value);
        }
        return self::$instances[$value];
    }

    /**
     * Get all available payment types
     *
     * @return array
     */
    public static function getAll(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    /**
     * Check if value is valid payment type
     *
     * @param mixed $value
     * @return bool
     */
    public static function isValid($value): bool
    {
        return in_array($value, self::getAll(), true);
    }

    /**
     * Get all payment type values
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_values(self::getAll());
    }

    /**
     * Get all payment type keys
     *
     * @return array
     */
    public static function getKeys(): array
    {
        return array_keys(self::getAll());
    }

    public static function fromValue(string $value)
    {
        if (!self::isValid($value)) {
            return null;
        }

        return self::getInstance($value);
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}