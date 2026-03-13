<?php

use App\Service\OrganizationService;

session_start();
chdir('..');

require_once dirname(__FILE__) . '/../api/Simpla.php';

if (!function_exists('config')) {
    require_once dirname(__FILE__) . '/../app/Core/Helpers/BaseHelper.php';
}

$organizationService = new OrganizationService();

$cliOptions = getopt('', ['organization::', 'organization-id::']);
$organizationId = null;

if ($cliOptions !== false) {
    if (isset($cliOptions['organization-id'])) {
        $organizationId = (int) $cliOptions['organization-id'];
    } elseif (isset($cliOptions['organization'])) {
        $organizationOption = $cliOptions['organization'];

        if (is_numeric($organizationOption)) {
            $organizationId = (int) $organizationOption;
        } else {
            $organizationId = $organizationService->resolveOrganizationIdByCode((string) $organizationOption);
        }
    }
}

if ($organizationId === null) {
    $organizationId = $organizationService->getDefaultId();
}

if (! $organizationService->exists($organizationId)) {
    error_log('[recall_pds] Unknown organization identifier provided.');
    exit(1);
}

$organizationLabel = $organizationService->getLabel($organizationId);

$start = date('Y-m-d H:i:00', strtotime(' - 7 minutes'));
$end = date('Y-m-d H:i:59');

$users = new Users();
$voximplant = new Voximplant();
$filter['recall_start'] = $start;
$filter['recall_end'] = $end;
$filter['organization_id'] = $organizationId;
$recall_tasks = $users->get_users_ccprolongations($filter);
$dncCache = [];

$pdsArray = [];
foreach ($recall_tasks as $task) {
    $managerId = (int) $task->manager_id;

    if (!array_key_exists($managerId, $dncCache)) {
        $managerDnc = $voximplant->getDncNumbers('ongoing', 'recall', $managerId);
        $dncCache[$managerId] = $managerDnc[$managerId] ?? [];
    }

    if (!empty($dncCache[$managerId]) && !in_array($task->phone, $dncCache[$managerId])) {
        $pdsArray[$managerId][] = $task;
    }
}

$manager_ids = array_keys($pdsArray);

foreach ($manager_ids as $id) {

    $users = json_decode(json_encode($pdsArray[$id]));
    $voximplant->sendPds($users, $id);

}

unset($users);

if (!empty($pdsArray)) {
    logger('voximplant')->error(sprintf(
        '[recall_pds] Processed organization %s (ID %d) managers: %s',
        $organizationLabel,
        $organizationId,
        implode(',', $manager_ids)
    ));
}


exit();
