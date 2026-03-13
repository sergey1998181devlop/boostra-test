<?php

namespace App\Containers\SharedSection\Contracts\CommandManager;

interface ActionsInterface
{
    public function getCommand(string $commandName): CommandInterface;
}