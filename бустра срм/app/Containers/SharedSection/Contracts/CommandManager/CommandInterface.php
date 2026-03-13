<?php

namespace App\Containers\SharedSection\Contracts\CommandManager;

interface CommandInterface
{
    public function run(): string;

    public function getArguments(): array;

    public function getDescription(): string;

    public function setArguments(array $arguments): self;
}