<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'Off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

session_start();
chdir('..');

require 'api/Simpla.php';

use App\Service\ManagerScheduleService;
use App\Service\OrganizationService;
use App\Service\VoximplantLogger;

$simpla = new Simpla();

// Инициализация сервисов
$organizationService = new OrganizationService();
$logger = new VoximplantLogger();
$managers = new Managers();
$service = new ManagerScheduleService($managers, $organizationService, $logger);

$plus = (bool) $simpla->request->get('plus');
$minus = $simpla->request->get('minus') ? true : 'false';
$organizationIdRaw = $simpla->request->get('organization_id');
$organizationId = (is_numeric($organizationIdRaw) && $organizationIdRaw !== '') ? (int) $organizationIdRaw : null;

// Получение менеджеров в зависимости от параметров
if ($date = $simpla->request->get('date')) {
    $companyManagers = $service->getScheduledManagers($date, $plus, $organizationId);
} elseif ($simpla->request->get('bool')) {
    $companyManagers = $service->getManagers($plus, $minus, $organizationId);
} else {
    $companyManagers = $service->getCompanyManagers($plus, $minus, $organizationId);
}

$simpla->response->json_output(['managers' => $companyManagers]);
?>
