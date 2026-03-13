<?php

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();
header('Content-Type: application/json; charset=utf-8');

$phone = $simpla->request->get('phone');
$status = $simpla->request->get('status');
$typeOfButton = $simpla->request->get('type_of_button');
$currentDate = date("Y-m-d");
$currentDateTime = date('Y-m-d H:i:s');

if (empty($phone)) {
    exit();
}
$taskStatuses = $simpla->tasks->get_pr_statuses();
$taskData = $simpla->tasks->getTaskWithPhone($phone);
if (empty($taskData)) {
    exit();
}

$crmStatus = ($status != "200" && $status != "3")
    ? 2
    : (!empty($typeOfButton) ? 3 : 1);

$voxCall = ($crmStatus === 3
    && $taskData->period === 'period_one_two'
    && empty($simpla->users->getZaimListAsp($taskData->number)));

$simpla->tasks->updateTaskStatus($taskData->user_id, $crmStatus, $voxCall, $currentDate);

$commentText = sprintf(
    "Системный комментарий. %s был совершен IVR-звонок.\nСтатус : %s",
    $currentDateTime,
    $taskStatuses[$crmStatus] ?? 'Неизвестный статус'
);

$block = ($taskData->period === 'period_one_two') ? 'cctasks_one_two' : 'cctasks';

$simpla->soap->send_comment([
    'user_uid' => $taskData->uid,
    'manager' => 50,
    'text' => $commentText,
    'created' => $currentDateTime,
    'number' => $taskData->number,
]);

$existingComment = $simpla->comments->get_comments([
    'user_id' => $taskData->user_id,
    'block' => $block,
    'created' => $currentDate,
]);

if (!empty($existingComment)) {
    $simpla->comments->update_comment($existingComment[0]->id, [
        'text' => $commentText,
        'created' => $currentDateTime,
    ]);
} else {
    $simpla->comments->add_comment([
        'manager_id' => 50,
        'user_id' => $taskData->user_id,
        'block' => $block,
        'text' => $commentText,
        'created' => $currentDateTime,
    ]);
}

exit();
