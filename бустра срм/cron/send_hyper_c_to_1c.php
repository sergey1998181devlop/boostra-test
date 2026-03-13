<?php

ini_set('display_errors', 'on');
error_reporting(-1);

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2048M');

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require_once __DIR__ . '/../api/Simpla.php';

/**
 * Крон отправляет данные скоринга hyper_c в 1С
 */
class SendHyperCTo1C extends Simpla
{
    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 3600;

    /** @var string Лог файл */
    private const LOG_FILE = 'send_hyper_c_to_1c.txt';

    public function run(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крона', self::LOG_FILE);

        $this->resetIncorrectIsOrderDecisionWithHyperC();

        $orders = $this->getOrdersWithHyperCResult();
        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Не найдено заявок для отправки хайпера в 1С', self::LOG_FILE);
            return;
        }

        foreach ($orders as $order) {
            $timeDuration = microtime(true) - $executionStartTime;
            if ($timeDuration > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон. Время выполнения: ' . $timeDuration . ' секунд', self::LOG_FILE);
                break;
            }

            if (empty($order->id_1c)) {
                $this->logging(__METHOD__, '', 'Заявка без 1c_id', ['order' => $order], self::LOG_FILE);
                continue;
            }

            try {
                $this->handleOrder($order);
            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];
                $this->logging(__METHOD__, '', 'Ошибка при обработке записи', ['order_id' => $order->order_id, 'error' => $error], self::LOG_FILE);
            }
        }

        $this->logging(__METHOD__, '', '', 'Завершение работы крона', self::LOG_FILE);
    }

    /**
     * Сбрасываем значение $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C, где он установлен некорректно
     */
    private function resetIncorrectIsOrderDecisionWithHyperC(): void
    {
        // Берем записи, где $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C установлено некорректно:
        // 1. Хайпер одобрил, а мы отказали
        // 2. Хайпер отказал, а мы одобрили
        $query = $this->db->placehold("
            SELECT
                o.id AS order_id,
                o.status AS order_status,
                hc.decision AS hyper_c_decision,
                od.value AS is_order_decision_with_hyper_c
            FROM __orders o
                INNER JOIN __order_data od ON od.order_id = o.id AND od.key = ? AND od.value = '1'
                LEFT JOIN __hyper_c hc ON hc.order_id = o.id
            WHERE (hc.decision = 'Approve' AND o.status NOT IN (2, 10, 11) AND o.reason_id != 36)
               OR (hc.decision = 'Decline' AND o.status != 3)
        ", $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C);

        $this->db->query($query);
        $orders = $this->db->results();
        if (empty($orders)) {
            $this->logging(__METHOD__, '', 'Заявки для обнуления ' . $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C .  ' не найдены', ['orders' => $orders], self::LOG_FILE);
            return;
        }

        $this->logging(__METHOD__, '', 'Найдены заявки для обнуления ' . $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C, ['orders' => $orders], self::LOG_FILE);

        $ordersId = array_column($orders, 'order_id');

        // Сбрасываем значение $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C для найденных заявок
        $query = $this->db->placehold("
            UPDATE __order_data
            SET value = '0'
            WHERE order_id IN (?@) AND `key` = ?
        ", $ordersId, $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C);

        $this->db->query($query);
    }

    private function handleOrder(stdClass $order): void
    {
        $params = [];

        if (in_array($order->hyper_c_decision, ['Approve', 'approved'])) {
            $decision = 'Одобрено';
        } else if (in_array($order->hyper_c_decision, ['Decline', 'Reject', 'rejected'])) {
            $decision = 'Отказано';
        } else {
            $this->logging(__METHOD__, '','Некорректное решение hyper_c', ['order' => $order, 'decision' => $order->hyper_c_decision], self::LOG_FILE);
            return;
        }

        $params['Хайпер'] = $decision;

        if ($order->hyper_c_model_version !== null) {
            $params['ВерсияМодели'] = $order->hyper_c_model_version;
        }

        // Верификатор принимал решение по заявке с учетом хайпера или нет
        if ($order->is_order_decision_with_hyper_c !== null) {
            $params['СтатусРешенияХайпер'] = !empty($order->is_order_decision_with_hyper_c) ? 'Да' : 'Нет';
        }

        $this->soap->updateApplicationField((string)$order->id_1c, $params);
    }

    /**
     * Получить решения hyper_c за последний час
     */
    private function getOrdersWithHyperCResult(): array
    {
        $dateFrom = date('Y-m-d H:00:00', strtotime('-1 hour'));
        $dateTo = date('Y-m-d H:59:59', strtotime('-1 hour'));

        // Берем решения хайпера
        $query = $this->db->placehold("
            SELECT DISTINCT
              o.id AS order_id,
              o.`1c_id` AS id_1c,
              hc.decision AS hyper_c_decision,
              hc.model_version AS hyper_c_model_version,
              od.value AS is_order_decision_with_hyper_c
            FROM __orders o
                INNER JOIN __hyper_c hc ON hc.order_id = o.id
                LEFT JOIN __order_data od ON od.order_id = o.id AND od.key = 'is_order_decision_with_hyper_c'
            WHERE hc.decision IS NOT NULL AND hc.end_date BETWEEN ? AND ?
      ", $dateFrom, $dateTo);

        $this->db->query($query);

        return $this->db->results() ?: [];
    }
}

$sendHyperCTo1C = new SendHyperCTo1C();
$sendHyperCTo1C->run();
