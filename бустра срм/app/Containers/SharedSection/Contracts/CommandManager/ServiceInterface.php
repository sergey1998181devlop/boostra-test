<?php

namespace App\Containers\SharedSection\Contracts\CommandManager;

interface ServiceInterface
{
    public function actions(): ActionsInterface;
}