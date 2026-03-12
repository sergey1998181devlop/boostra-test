<?php

/**
 * Крон: обновление метрик СРКВ из 1С.
 *
 * Запускается ежедневно в 06:00 (данные в 1С обновляются к этому времени).
 * Получает метрики возвратов и конверсий, сохраняет в Redis-кеш.
 * При штатной работе все запросы к СРКВ идут из кеша.
 */

error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application\Application;
use App\Services\ReturnCoefficientService;

$simpla = new Simpla();

try {
    $app = Application::getInstance();
    /** @var ReturnCoefficientService $service */
    $service = $app->make(ReturnCoefficientService::class);

    $success = $service->refreshMetrics();

    if (!$success) {
        echo date('Y-m-d H:i:s') . " SRKV: failed to refresh metrics from 1C\n";
        exit(1);
    }

    echo date('Y-m-d H:i:s') . " SRKV: metrics refreshed successfully\n";

    // п.2.1, п.3.1: сразу пересчитываем рискованные значения и баллы
    $recalculated = $service->recalculateRiskyValues();

    if ($recalculated) {
        echo date('Y-m-d H:i:s') . " SRKV: risky values recalculated successfully\n";
    } else {
        echo date('Y-m-d H:i:s') . " SRKV: risky values recalculation failed (will retry on first request)\n";
    }
} catch (Throwable $e) {
    log_error('SRKV cron: fatal error', ['error' => $e->getMessage()]);
    echo date('Y-m-d H:i:s') . " SRKV cron error: " . $e->getMessage() . "\n";
    exit(1);
}
