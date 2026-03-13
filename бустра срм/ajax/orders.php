<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$result = [];

switch ($simpla->request->get('action', 'string')):
    case 'save_fssp':
        $fssp_status = $simpla->request->post('fssp_status');
        $order_id = $simpla->request->post('order_id', 'integer');
        $user_id = $simpla->request->post('user_id', 'integer');

        $simpla->fssp_api->deleteFsspByOrderId($order_id);
        $fssp_orders = $simpla->request->post('fssp_order');

        if (!empty($fssp_orders) && !empty($fssp_status)) {
            foreach ($fssp_orders as $fssp_order) {
                $fssp_order['user_id'] = $user_id;
                $fssp_order['order_id'] = $order_id;

                $simpla->fssp_api->addFsspByOrder($fssp_order);
            }
            $result['success'] = true;
        }

        break;
    case 'unblock_asp':
        $order_id = $simpla->request->post('order_id', 'integer');
        $manager_id = $simpla->request->post('manager_id', 'integer');
        $manager = $simpla->managers->get_manager($manager_id);

        if (in_array($manager->role, ['developer', 'contact_center_plus', 'admin', 'opr', 'ts_operator'])) {
            if ($order_id && $manager_id)  {
                $order =  $simpla->orders->get_order($order_id);
                $simpla->orders->update_order($order_id, ['accept_try' => '0']);
                $simpla->changelogs->add_changelog(
                    [
                        'manager_id' => $manager_id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'unblock_asp',
                        'old_values' => (string)$order->accept_try,
                        'new_values' => '0',
                        'order_id' => $order_id,
                        'user_id' => $order->user_id,
                    ]
                );

                $simpla->users->update_user($order->user_id, ['blocked' => 0]);
                $simpla->changelogs->add_changelog(
                    [
                        'manager_id' => $manager_id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'blocked',
                        'old_values' => 1,
                        'new_values' => 0,
                        'user_id' => $order->user_id,
                    ]
                );


                $result['success'] = true;
            } else {
                $result['error'] = 'Incorrect order ID or manager ID';
            }
        } else {
            $result['error'] = 'Not enough access rights to perform this operation';
        }
        break;
    case 'disable_check_reports_for_loan':
        $orderId = $simpla->request->post('order_id', 'integer');
        $managerId = $simpla->request->post('manager_id', 'integer');

        if (empty($orderId) || empty($managerId)) {
            $result = [
                'success' => false,
                'message' => 'Не указан id заявки или менеджер'
            ];
        } else {
            $oldValue = (int)$simpla->order_data->read($orderId, $simpla->order_data::DISABLE_CHECK_REPORTS_FOR_LOAN);
            $newValue = empty($oldValue) ? 1 : 0;
            $simpla->order_data->set($orderId, $simpla->order_data::DISABLE_CHECK_REPORTS_FOR_LOAN, $newValue);
            addLogging($orderId, $managerId, $oldValue, $newValue, $simpla);

            $result = [
                'success' => true,
                'message' => getLoggingText($newValue)
            ];
        }
        break;
    default:
        $result['error'] = 'undefined_action';
endswitch;

/**
 * Добавляет логирование
 *
 * @param int $orderId
 * @param int $managerId
 * @param int $oldValue
 * @param int $newValue
 * @param Simpla $simpla
 * @return void
 */
function addLogging(int $orderId, int $managerId, int $oldValue, int $newValue, Simpla $simpla): void {
    $order = $simpla->orders->get_order($orderId);

    addFileLogging($order, $managerId, $oldValue, $newValue, $simpla);
    addClientLogging($order, $managerId, $newValue, $simpla);
    addOrderLogging($order, $managerId, $oldValue, $newValue, $simpla);
}

/**
 * Добавляет логирование в файл
 *
 * @param stdClass $order
 * @param int $managerId
 * @param int $oldValue
 * @param int $newValue
 * @param Simpla $simpla
 * @return void
 */
function addFileLogging(stdClass $order, int $managerId, int $oldValue, int $newValue, Simpla $simpla): void {
    $simpla->logging(__METHOD__, '', ['order_id' => $order->order_id, 'manager_id' => $managerId], [
        'old_value' => $oldValue,
        'new_value' => $newValue,
        'result' => getLoggingText($newValue)
    ], 'disable_check_reports_for_loan.txt');
}

/**
 * Добавляет логирование на страницу клиента
 *
 * @param stdClass $order
 * @param int $managerId
 * @param int $newValue
 * @param Simpla $simpla
 * @return void
 */
function addClientLogging(stdClass $order, int $managerId, int $newValue, Simpla $simpla): void {
    $simpla->comments->add_comment([
        'manager_id' => $managerId,
        'user_id' => $order->user_id,
        'order_id' => $order->order_id,
        'block' => 'disable_check_reports',
        'text' => getLoggingText($newValue),
        'created' => date('Y-m-d H:i:s'),
    ]);
}

/**
 * Получить текст для лога
 *
 * @param string $newValue
 * @return string
 */
function getLoggingText(string $newValue): string {
    return empty($newValue) ?
        'Включена проверка актуальности ССП и КИ отчетов при выдаче займов' :
        'Отключена проверка актуальности ССП и КИ отчетов при выдаче займов';
}

/**
 * Добавляет логирование на страницу заявки
 *
 * @param stdClass $order
 * @param int $managerId
 * @param int $oldValue
 * @param int $newValue
 * @param Simpla $simpla
 * @return void
 */
function addOrderLogging(stdClass $order, int $managerId, int $oldValue, int $newValue, Simpla $simpla): void {
    $simpla->changelogs->add_changelog([
        'manager_id' => $managerId,
        'created' => date('Y-m-d H:i:s'),
        'type' => 'disable_check_reports',
        'old_values' => $oldValue,
        'new_values' => $newValue,
        'order_id' => $order->order_id,
        'user_id' => $order->user_id,
    ]);
}

$simpla->response->json_output($result);
