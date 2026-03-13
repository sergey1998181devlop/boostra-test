<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'off');

header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

use App\Service\VoximplantMissedCallsService;
use App\Service\VoximplantCampaignService;
use App\Service\VoximplantApiClient;
use App\Service\VoximplantLogger;
use App\Service\OrganizationService;

$simpla = new Simpla();

// Инициализация сервисов
$organizationService = new OrganizationService();
$logger = new VoximplantLogger();
$apiClient = new VoximplantApiClient($organizationService, $logger);
$campaignService = new VoximplantCampaignService($apiClient, $logger, $organizationService);
$missedCallsService = new VoximplantMissedCallsService(
    $campaignService,
    $logger,
    $organizationService,
    $simpla->users,
    $simpla->tasks,
    $simpla->db
);

// Получение параметров запроса
$params = [
    'pdsId' => $simpla->request->post('pdsId'),
    'attemptsNumber' => $simpla->request->post('attemptsNumber'),
    'intervalHours' => $simpla->request->post('interval'),
    'organizationId' => $simpla->request->post('organization_id'),
    'dateRange' => $simpla->request->post('date_range') ?? '',
];

// Вызов сервиса
$result = $missedCallsService->sendMissedCallsToPds($params);

// Возврат результата
$simpla->response->json_output($result);
