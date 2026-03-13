<?php

namespace App\Containers\SharedSection\DI;

use Exception;
use Psr\Log\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Класс для генерации контейнеров.
 * @package App\Shared\DI
 */
class Container
{
    private array $objects = [];

    /**
     * Метод получает зарегистрированный класс контейнера
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->objects[$id]) || class_exists($id);
    }

    /**
     * Метод возвращает инстанс класса по его ключу.
     *
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        try {
            $this->objects[$id] =
                isset($this->objects[$id])
                    ? $this->objects[$id]()         // "Старый" подход
                    : $this->prepareObject($id);    // "Новый" подход

            return $this->objects[$id];
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @param string $class
     * @return mixed
     * @throws ReflectionException
     */
    private function prepareObject(string $class): object
    {
        $classReflector = new ReflectionClass($class);

        // Получаем рефлектор конструктора класса, проверяем - есть ли конструктор
        // Если конструктора нет - сразу возвращаем экземпляр класса
        $constructReflector = $classReflector->getConstructor();
        if (empty($constructReflector)) {
            return new $class;
        }

        // Получаем рефлекторы аргументов конструктора
        // Если аргументов нет - сразу возвращаем экземпляр класса
        $constructArguments = $constructReflector->getParameters();
        if (empty($constructArguments)) {
            return new $class;
        }

        // Перебираем все аргументы конструктора, собираем их значения
        $args = [];
        foreach ($constructArguments as $argument) {
            // Получаем тип аргумента
            $argumentType = $argument->getType()->getName();
            // Получаем сам аргумент по его типу из контейнера
            $args[$argument->getName()] = $this->get($argumentType);
        }

        // И возвращаем экземпляр класса со всеми зависимостями
        return new $class(...$args);
    }
}
