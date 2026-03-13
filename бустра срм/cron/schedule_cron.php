<?php

chdir(dirname(__FILE__) . '/..');
require_once dirname(__FILE__) . '/../api/Simpla.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!function_exists('config')) {
    require_once dirname(__DIR__) . '/app/Core/Helpers/BaseHelper.php';
}
\App\Core\Application\Application::getInstance();

use App\Service\CCTaskScheduleService;
use App\Service\CCTaskService;
use App\Service\ManagerScheduleService;
use App\Service\OrganizationService;
use App\Service\UserBalanceImportService;
use App\Service\VoximplantApiClient;
use App\Service\VoximplantCampaignService;
use App\Service\VoximplantDncService;
use App\Service\VoximplantLogger;

$simpla = new Simpla();

$organizationService = new OrganizationService();
$logger = new VoximplantLogger();
$apiClient = new VoximplantApiClient($organizationService, $logger);
$dncService = new VoximplantDncService($apiClient, $logger, $organizationService);
$campaignService = new VoximplantCampaignService($apiClient, $logger, $organizationService);
$taskService = new CCTaskService($dncService, $campaignService, $logger);
$managerScheduleService = new ManagerScheduleService(
    $simpla->managers,
    $organizationService,
    $logger
);
$balanceImportService = new UserBalanceImportService(
    $simpla->users,
    $simpla->soap,
    $simpla->import1c,
    $simpla->organizations,
    $logger,
    $simpla->db
);
$scheduleService = new CCTaskScheduleService(
    $taskService,
    $managerScheduleService,
    $logger,
    $organizationService,
    $simpla->users,
    $balanceImportService
);

$cronStartTime = microtime(true);
$cronStartDate = date('Y-m-d H:i:s');

if (function_exists('logger')) {
    logger('cc_task_schedule')->info('schedule_cron started', [
        'start_time' => $cronStartDate,
        'timestamp' => $cronStartTime,
    ]);
}

$organizations = $organizationService->getOptions();

foreach ($organizations as $organization) {
    try {
        $scheduleService->createTasksForOrganization((int) $organization['id']);
        $scheduleService->sendTasksToVoximplantForOrganization((int) $organization['id']);
    } catch (\Throwable $e) {
        if (function_exists('logger')) {
            logger('cc_task_schedule')->error('schedule_cron organization failed', [
                'organization_id' => $organization['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

$cronEndTime = microtime(true);
$cronEndDate = date('Y-m-d H:i:s');
$cronDuration = round($cronEndTime - $cronStartTime, 2);

if (function_exists('logger')) {
    logger('cc_task_schedule')->info('schedule_cron finished', [
        'start_time' => $cronStartDate,
        'end_time' => $cronEndDate,
        'duration_seconds' => $cronDuration,
        'organizations_count' => count($organizations),
    ]);
}

header('Content-type: application/json');
echo json_encode(['success' => 1]);
exit;
