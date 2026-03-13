<?php

namespace App\Contracts;

interface SendRecordAnalysisHandlerContract
{
    public function handle(array $comment): bool;
}