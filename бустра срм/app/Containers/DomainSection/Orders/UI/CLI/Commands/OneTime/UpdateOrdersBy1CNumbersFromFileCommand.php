<?php

namespace App\Containers\DomainSection\Orders\UI\CLI\Commands\OneTime;

use App\Containers\DomainSection\Orders\Actions\UpdateOrdersBy1CNumbersAction;
use App\Containers\SharedSection\Contracts\CommandManager\CommandInterface;
use App\Containers\SharedSection\Services\CommandManager\Traits\CommandTrait;

class UpdateOrdersBy1CNumbersFromFileCommand implements CommandInterface
{
    use CommandTrait;

    /**
     * Размер пакета для обработки заказов (количество записей за один проход)
     * @var int
     */
    private const CHUNK_SIZE = 1000;

    public function run(): string
    {
        $command = new UpdateOrdersBy1CNumbersAction();
        $command->execute(self::CHUNK_SIZE);

        return 'Заявки были успешно обновлены';
    }

    public function getArguments(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return 'Обновляет заявки по списку номеров из 1C';
    }
}