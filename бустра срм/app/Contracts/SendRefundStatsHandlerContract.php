<?php

namespace App\Contracts;

interface SendRefundStatsHandlerContract
{
    /**
     * Отправляет статистику возвратов за текущий или прошедший день
     * @return void
     */
    public function handle(): void;

    /**
     * Отправляет статистику возвратов за указанную дату (для тестирования)
     * @param string $date Дата в формате Y-m-d
     * @return void
     */
    public function handleTest(string $date): void;
}