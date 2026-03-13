<?php

ini_set('display_errors', 'on');
error_reporting(-1);

ini_set('max_execution_time', ' 115');
ini_set('memory_limit', '2048M');

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require_once __DIR__ . '/../api/Simpla.php';

/** Класс генерирует авто- и кросс-заявки по базе */
class GenerateOrders extends Simpla
{
    /** @var int Лимит для кол-ва новых договоров за 1 запуск крона */
    private const LIMIT = 30;

    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME =  115;

    /** @var string Лог файл */
    private const LOG_FILE = 'generate_orders.txt';


    public function run()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крона', self::LOG_FILE);

        $orderRecords = $this->getRecords([
            'status' => 'NEW'
        ]);

        if (empty($orderRecords)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены', self::LOG_FILE);
            return;
        }

        foreach ($orderRecords as $orderRecord) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            $timeDuration = microtime(true) - $executionStartTime;
            if ($timeDuration > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон. Время выполнения: ' . $timeDuration . ' секунд', self::LOG_FILE);
                break;
            }

            $order = $this->getOrder($orderRecord);
            if (empty($order)) {
                $this->updateRecord((int)$orderRecord->id, [
                    'status' => 'NOT_FOUND',
                    'date_update' => date('Y-m-d H:i:s')
                ]);
                $this->logging(__METHOD__, '', 'Записи не найдены', ['order_id' => $orderRecord->order_id], self::LOG_FILE);
                continue;
            }

            try {
                $this->generateOrder($order, $orderRecord);
            } catch (Throwable $error) {
                $this->updateRecord((int)$orderRecord->id, [
                    'status' => 'ERROR',
                    'date_update' => date('Y-m-d H:i:s')
                ]);
                $this->logging(__METHOD__, '', 'Фатальная ошибка', ['record_id' => $orderRecord->id, 'error' => $error], self::LOG_FILE);
            }

            sleep(2);
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен', self::LOG_FILE);
    }

    private function getRecords(array $where = [])
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM generate_orders WHERE $conditions LIMIT "  . self::LIMIT);

        return $this->db->results();
    }

    private function updateRecord(int $id, array $data): void
    {
        $query = $this->db->placehold("UPDATE generate_orders SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    private function getOrder(stdClass $orderRecord): ?stdClass
    {
        if (!empty($orderRecord->order_id)) {
            $order = $this->orders->get_order((int)$orderRecord->order_id);
            return $order ?: null;
        }

        if (!empty($orderRecord->contract_number)) {
            $contract = $this->contracts->get_contract_by_params(['number' => $orderRecord->contract_number]);
            if (empty($contract)) {
                return null;
            }

            $order = $this->orders->get_order((int)$contract->order_id);
            return $order ?: null;
        }

        return null;
    }

    private function generateOrder(stdClass $order, stdClass $orderRecord): void
    {
        if ($orderRecord->order_type_to_generate === $this->orders::UTM_SOURCE_CROSS_ORDER) {
            $this->generateCrossOrder($order, $orderRecord);
        } elseif ($orderRecord->order_type_to_generate === $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE) {
            $this->generateAutoApproveOrder($order, $orderRecord);
        } else {
            $this->updateRecord((int)$orderRecord->id, [
                'status' => 'ERROR',
                'date_update' => date('Y-m-d H:i:s')
            ]);
            $this->logging(__METHOD__, '', 'Некорректный тип заявки для генерации', ['order_record' => $orderRecord], self::LOG_FILE);
        }
    }

    private function generateCrossOrder(stdClass $order, stdClass $orderRecord): void
    {
        $this->cross_orders->create((int)$order->order_id, true);

        $this->updateRecord((int)$orderRecord->id, [
            'status' => 'SUCCESS',
            'date_update' => date('Y-m-d H:i:s')
        ]);

        $this->logging(__METHOD__, '', 'Попытка создания кросс-ордера завершена', ['order_id' => (int)$order->order_id], self::LOG_FILE);
    }

    private function generateAutoApproveOrder(stdClass $order, stdClass $orderRecord): void
    {
        $autoApproveNkRowId = $this->orders_auto_approve->addAutoApproveNK([
            'parent_order_id' => (int)$order->order_id,
            'user_id' => (int)$order->user_id,
            'status' => $this->orders_auto_approve::STATUS_CRON_NEW,
            'date_cron' => date('Y-m-d H:i:s'),
            'validate_scoring' => 1,
        ]);

        $this->updateRecord((int)$orderRecord->id, [
            'status' => 'SUCCESS',
            'auto_approve_nk_id' => $autoApproveNkRowId,
            'date_update' => date('Y-m-d H:i:s')
        ]);

        $this->logging(__METHOD__, '', 'Попытка создания автозаявки завершена', ['order_id' => (int)$order->order_id, 'auto_approve_nk_row_id' => $autoApproveNkRowId], self::LOG_FILE);
    }
}

$generateOrders = new GenerateOrders();
$generateOrders->run();

