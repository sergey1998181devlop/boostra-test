<?php

/**
 * Usage:
 * Скрипт един для всех потенциальных команд и вызывается в виде:
 * php app/CLI/console.php --command="название команды (также это название класса команды без "Command" на конце)" --a1 --a2 --aN,
 * где --a - аргументы команды.
 * Справочную информацию по команде можно получить:
 * php app/CLI/console.php --command="название команды" --help
 * или
 * php app/CLI/console.php --command="название команды" -h
 */

$dir = realpath(dirname(__FILE__) . '/../../');
define('ROOT_DIR', $dir);//'/var/www/html'; //Изменить в случае отличия
require_once ROOT_DIR . '/vendor/autoload.php';

use App\Containers\SharedSection\DI\Container;
use App\Containers\SharedSection\Services\CommandManager\CommandService;

//Определяем команду
$options = getopt('h', ['command:', 'args:', 'help']);

if (!array_key_exists('command', $options)) {
    die('Не передан флаг "--command"' . PHP_EOL);
}

//Получаем сервис менеджера команд
$commandService = (new Container)->get(CommandService::class);
//Получаем actions сервиса
$commandActions = $commandService->actions();
//Получаем описанный объект команды
$commandObject = $commandActions->getCommand($options['command']);
//Получаем аргументы, необходимые для выполнения команды
$commandArguments = $commandObject->getArguments();
//Создаём массив опций из полученных аргументов для получения их из консоли
$commandOptions = array_map(fn($opt) => $opt . ':', array_keys($commandArguments));
//Получаем значения аргументов команды консоли
$arguments = getopt('', $commandOptions);

//Если есть флаги "--help" или "-h", то выводим только справочную информацию
if (
    array_key_exists('help', $options)
    || array_key_exists("h", $options)
) {
    echo $commandObject->getDescription() . PHP_EOL . PHP_EOL;
    echo 'Аргументы:' . PHP_EOL;
    foreach ($commandArguments as $arg => $description) {
        echo $arg . ": " . $description . PHP_EOL;
    }
    die();
}

//Проверяем заполненность аргументов команды
foreach (array_keys($commandArguments) as $argument) {
    if (empty($arguments[$argument])) {
        die('Не передано значение аргумента "--' . $argument . '"!' . PHP_EOL);
    }
}

//Заполняем аргументы через единый метод, чтобы оставить единообразие классов одного интерфейса и запускаем команду
echo $commandObject
    ->setArguments($arguments)
    ->run() . PHP_EOL;



