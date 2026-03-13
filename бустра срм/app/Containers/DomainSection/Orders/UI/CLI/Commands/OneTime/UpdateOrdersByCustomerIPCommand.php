<?php

namespace App\Containers\DomainSection\Orders\UI\CLI\Commands\OneTime;

use App\Containers\DomainSection\Orders\Actions\UpdateOrdersByCustomerIPAction;
use App\Containers\SharedSection\Contracts\CommandManager\CommandInterface;
use App\Containers\SharedSection\Services\CommandManager\Traits\CommandTrait;

class UpdateOrdersByCustomerIPCommand implements CommandInterface
{
    use CommandTrait;

    /**
     * IP адрес, с которого поступали заблокированные заявки
     * @var string
     */
    protected string $ip;

    /**
     * Количество заявок, для которых будет производиться обновление
     * @var int
     */
    protected int $count;

    /**
     * Команда запуска выполнения кода
     * @return string сообщение о результате выполнения команды
     */
    public function run(): string
    {
        $o = new UpdateOrdersByCustomerIPAction();
        $o->execute($this->ip, $this->count);
        return 'Команда выполнена успешно';
    }

    /**
     * Метод получает массив аргументов команды, которые также являются свойствами описанного объекта
     * @return string[]
     */
    public function getArguments(): array
    {
        return [
            'ip' => 'IP адрес, с которого поступила заявка для разблокировки',
            'count' => 'Количество обновляемых записей за выполнение команды'
        ];
    }

    /**
     * Обязательный метод описания команды для вызова справки из консоли
     * @return string
     */
    public function getDescription(): string
    {
        return 'Команда обновляет заблокированные по IP заявки: меняет им статус на "Без действия", а также инициирует её в очереди заявки';
    }
}