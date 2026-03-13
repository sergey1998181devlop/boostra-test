<?php

declare(strict_types=1);

chdir('..');

require 'api/Simpla.php';

use App\Service\CallListService;
use App\Service\VoximplantLogger;

$simpla = new Simpla();

// Инициализация сервисов
$logger = new VoximplantLogger();
$service = new CallListService(
    $simpla->tasks,
    $simpla->orders,
    $simpla->tv_medical,
    $logger,
    $simpla->config->root_dir,
    $simpla->config->root_url
);

// Получение параметров запроса
$organizationId = $simpla->request->get('organization_id');
$params = [
    'dateRange' => $simpla->request->get('dataRange') ?? '',
    'managerId' => $simpla->request->get('manager'),
    'plus' => (bool) $simpla->request->get('plus'),
    'organizationId' => $organizationId ? (int) $organizationId : null,
];

// Вызов сервиса
$result = $service->generateCallListReport($params);

// Возврат результата
echo json_encode($result);

