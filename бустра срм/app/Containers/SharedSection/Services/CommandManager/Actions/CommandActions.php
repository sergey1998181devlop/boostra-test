<?php

namespace App\Containers\SharedSection\Services\CommandManager\Actions;

use App\Containers\SharedSection\Contracts\CommandManager\ActionsInterface;
use App\Containers\SharedSection\Contracts\CommandManager\CommandInterface;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class CommandActions implements ActionsInterface
{
    /**
     * Метод получает объект класса переданной команды
     * @param string $commandName
     * @return CommandInterface
     * @throws InvalidArgumentException
     */
    public function getCommand(string $commandName): CommandInterface
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT_DIR . '/app/Containers/DomainSection/'));
        $regex = new RegexIterator($iterator, '/^.+Command\.php$/i', RegexIterator::GET_MATCH);

        foreach ($regex as $file => $value) {
            $this->loadClass($file);
        }

        foreach (get_declared_classes() as $class) {
            if (
                is_subclass_of($class, CommandInterface::class)
                && preg_match('/^.+' . $commandName . 'Command' . '/m', $class) === 1
            ) {
                return new $class();
            }
        }

        die('Command ' . $commandName . ' not found.' . PHP_EOL);
    }

    /**
     * Метод подключает класс по названию файла
     * @param string $class
     * @return void
     */
    private function loadClass(string $class): void
    {
        include_once $class;
    }
}