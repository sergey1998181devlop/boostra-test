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

use App\Service\VoximplantManagerService;
use App\Service\OrganizationService;
use App\Service\VoximplantLogger;

$simpla = new Simpla();

// Инициализация сервисов
$organizationService = new OrganizationService();
$logger = new VoximplantLogger();
$managers = new Managers();
$service = new VoximplantManagerService($managers, $organizationService, $logger);

// Получение параметров запроса
$managerId = (int) $simpla->request->post('manager_id', 'integer');
$pds = (int) $simpla->request->post('pds', 'integer');
$dnc = (int) $simpla->request->post('dnc', 'integer');
$period = $simpla->request->post('period') ?: 'false';
$plus = (bool) $simpla->request->post('plus');

$organizationIdRaw = $simpla->request->post('organization_id');
$organizationId = ($organizationIdRaw !== null && $organizationIdRaw !== '' && is_numeric($organizationIdRaw))
    ? (int) $organizationIdRaw
    : null;

// Вызов сервиса
$result = $service->addOrUpdateVoxManager(
    $managerId,
    $pds,
    $dnc,
    $organizationId,
    $plus,
    $period
);

// Формирование ответа
if ($result['success']) {
    $simpla->response->json_output(['name' => $result['name']]);
} else {
    $simpla->response->json_output([
        'success' => false,
        'error' => $result['error'] ?? 'Произошла ошибка при сохранении настроек'
    ]);
}
?>
