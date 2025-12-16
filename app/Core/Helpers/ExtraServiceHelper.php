<?php

use App\Services\ReturnExtraService;
use App\Core\Application\Application;

if (!function_exists('extraService')) {
    /**
     * Получить инстанс ReturnExtraService
     *
     * @return ReturnExtraService
     * @throws Exception
     */
    function extraService(): ReturnExtraService {
        return Application::getInstance()->make(ReturnExtraService::class);
    }
} 