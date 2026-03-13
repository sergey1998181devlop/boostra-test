<?php

use App\Enums\AutoApproveOrders;

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '1200');

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Проверяет решение акси в автоодобрениях
 */
class ValidateAutoApproveOrders extends Simpla
{
    private const LOG_FILE = 'validate_auto_approve_orders.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', '', 'Начало работы крон', self::LOG_FILE);

        $noValidatedOrders = $this->orders_auto_approve->getNoValidatedAutoApproveOrders();
        if (empty($noValidatedOrders)) {
            $this->logging(__METHOD__, '', '', 'Записи не найдены. Крон завершен', self::LOG_FILE);
            return;
        }

        foreach ($noValidatedOrders as $order) {
            try {
                $this->orders_auto_approve->handleAxiDecision((int)$order->order_id, (int)$order->auto_approve_id);
            } catch (Throwable $error) {
                $this->logging(__METHOD__, '', 'Ошибка при проверке автозаявки', ['order' => $order, 'error' => $error], self::LOG_FILE);
                $this->orders_auto_approve->updateAutoApproveOrder((int)$order->auto_approve_id, ['status' => $this->orders_auto_approve::STATUS_ERROR, 'reason' => AutoApproveOrders::REASON_GENERATE_ORDER_ERROR]);
            }
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен', self::LOG_FILE);
    }
}

(new ValidateAutoApproveOrders())->run();