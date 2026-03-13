<?php

namespace App\Containers\DomainSection\Tickets\UI\CLI\Commands\OneTime;

use App\Containers\DomainSection\Tickets\Actions\CreateTsTicketsAction;
use App\Containers\SharedSection\Contracts\CommandManager\CommandInterface;
use App\Containers\SharedSection\Services\CommandManager\Traits\CommandTrait;

class CreateTsTicketsCommand implements CommandInterface
{
    use CommandTrait;

    public function run(): string
    {
        return 'Создано/обновлено тикетов: ' . (new CreateTsTicketsAction())->execute();
    }

    public function getArguments(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return 'Создаёт тикеты в таблице ts_tickets из таблицы s_mytickets';
    }
}
