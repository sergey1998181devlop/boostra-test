<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'off');

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

// Получение параметров запроса
$date = $simpla->request->post('date') ?? null;
$managerIds = $simpla->request->post('managers') ?? [];
$plus = !empty($simpla->request->post('plus'));
$organizationIdInput = $simpla->request->post('organization_id') ?? null;
$organizationId = ($organizationIdInput !== null && $organizationIdInput !== '' && is_numeric($organizationIdInput))
    ? (int) $organizationIdInput
    : null;

// Валидация
if (empty($date) || empty($managerIds)) {
    $simpla->response->json_output(['success' => false, 'error' => 'Не указаны дата или менеджеры']);
    exit();
}

$result = $service->addSchedule($date, $managerIds, $plus, $organizationId);

if ($result) {
    $simpla->response->json_output('success');
} else {
    $simpla->response->json_output(['success' => false, 'error' => 'Ошибка при сохранении расписания']);
}

exit();
