<?php

header('Access-Control-Allow-Origin: https://cd.kreditoff-net.ru');
header("Access-Control-Allow-Methods: POST");

function response(string $status = 'error', array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null;
if (!$userId)
    response();

$action = !empty($_POST['action']) ? (string) $_POST['action'] : null;
if (!$action)
    response();

require_once __DIR__ . '/../api/CDoctor.php';
require_once __DIR__ . '/../api/Simpla.php';
$core = new Simpla;

$function_name = $action . '_action';
if (function_exists($function_name))
    $function_name($core, $userId);
else
    response();


/**
 * Конструктор страницы
 */
function construct_action(Simpla $core, int $userId): void
{
    $lessons = check_user_for_lessons($core, $userId) ? $core->credit_doctor->getAllLessons() : $core->credit_doctor->getApiData($userId);

    if (!$lessons)
        response();

    $user = $core->users->get_user($userId);
    response(
        'success',
        [
            'lessons' => $lessons,
            'levels' => $core->credit_doctor->getLevels(),
            'user' => $user
        ]
    );
}

/**
 * Обновление даты графика снижения долговой нагрузки
 */
function update_graph_action(Simpla $core, int $userId): void
{
    $new_graph_date = (new DateTime())->format('Y.m.d');
    $core->users->update_user($userId, [
        'cdoctor_last_graph_update_date' => $new_graph_date
    ]);
    response('success', [
        'updated_date' => $new_graph_date
    ]);
}

/**
 * Проверяем доступ всем урокам по заявкам-csv файл
 * @param Simpla $core
 * @param $userId
 * @return bool
 */
function check_user_for_lessons(Simpla $core, $userId): bool
{
    if ((int)$userId === 932420) {
        return true;
    }

    $userOrderNumbers = $core->contracts->get_user_all_contracts_number($userId);

    $filename = realpath(__DIR__ . '/../files/specials/orders.txt');

    if (file_exists($filename)) {
        $text = str_replace(['\'', '""'], '', file_get_contents($filename));
        $items = explode(", ", $text);
    } else {
        return false;
    }

    return !empty(array_intersect($userOrderNumbers, $items)) || (int)$userId === 742880;
}
