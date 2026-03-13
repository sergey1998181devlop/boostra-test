<?php

namespace App\Containers\SharedSection\Services\CommandManager\Traits;

use App\Containers\SharedSection\Contracts\CommandManager\CommandInterface;

trait CommandTrait
{
    /**
     * Обязательный метод установки свойств объекта через аргументы команды из консоли
     * @param array $arguments
     * @return CommandInterface
     */
    public function setArguments(array $arguments): CommandInterface
    {
        foreach ($arguments as $argument => $value) {
            $this->$argument = $value;
        }

        return $this;
    }
}