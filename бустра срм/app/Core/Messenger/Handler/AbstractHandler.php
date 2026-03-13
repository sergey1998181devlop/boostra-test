<?php

namespace App\Core\Messenger\Handler;

abstract class AbstractHandler
{
    /**
     * Возвращает приоритет обработчика.
     * Чем выше число, тем раньше он будет выполнен.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 0;
    }
    
    /**
     * Записывает сообщение в системный лог.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        error_log(sprintf('[%s] %s %s', strtoupper($level), $message, json_encode($context)));
    }
}