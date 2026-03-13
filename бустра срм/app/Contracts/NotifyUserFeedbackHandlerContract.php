<?php

namespace App\Contracts;

interface NotifyUserFeedbackHandlerContract
{
    public function handle(array $feedback): void;
}