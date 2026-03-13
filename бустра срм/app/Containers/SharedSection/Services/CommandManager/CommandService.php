<?php

namespace App\Containers\SharedSection\Services\CommandManager;

use App\Containers\SharedSection\Contracts\CommandManager\ActionsInterface;
use App\Containers\SharedSection\Contracts\CommandManager\ServiceInterface;
use App\Containers\SharedSection\Services\CommandManager\Actions\CommandActions;

class CommandService implements ServiceInterface
{
    private CommandActions $actions;

    public function __construct()
    {
        if (php_sapi_name() !== 'cli') {
            die('Этот скрипт можно запускать только из командной строки!');
        }

        $this->actions = new CommandActions();
    }

    public function actions(): ActionsInterface
    {
        return $this->actions;
    }
}