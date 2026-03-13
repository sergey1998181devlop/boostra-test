<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';

// */5 * * * * на каждые 5 минут

/*
 * Дополнительное обновление статуса ожидания карты на случай если не сработает микросервис
 */
class UpdateExpiredWaitingOrderStatusCronAction extends Simpla
{
    public function execute()
    {
        // в норме полностью отсутсвуют ордера в статусе ожидания вирт карты более 10 минут
        $orders = $this->getExpiredWaitingVirtualCardOrders();

        if (empty($orders)) {
            $this->log('✅ Просроченных заказов не найдено. Завершение работы.');
            return;
        }

        $ordersCount = count($orders);
        $this->log("📊 Найдено $ordersCount просроченных заказов:");

        $processedOrdersCount = 0;
        foreach ($orders as $order) {
            try {
                $vcStatusData = $this->virtualCard->forUser($order->user_id, $order->id)->status();
                $status = $vcStatusData['status'] ?? null;

                $updateData = ['status' => $this->orders::ORDER_STATUS_SIGNED];
                if ($status === 'active') {
                    $updateData['card_type'] = $this->orders::CARD_TYPE_VIRT;
                    $this->log("✔️ Заказ #$order->id успешно перемещен в signed_vc_order");
                } else {
                    $updateData['card_type'] = $this->orders::CARD_TYPE_SBP;
                    $this->log("✔️ Заказ #$order->id успешно перемещен в signed_sbp_order");
                }

                $this->orders->update_order($order->id, $updateData);
                $this->order_data->set($order->id, $this->order_data::CREATED_AT_VIRTUAL_CARD_TIMESTAMP, 0);

                $processedOrdersCount++;
                usleep(300000); // задержка на 0,3 секунды

            } catch (Exception $e) {
                $this->log("❌ Ошибка обработки заказа #$order->id: " . $e->getMessage(), '❌', 'ERROR');
                $this->log("🔧 Trace: " . $e->getTraceAsString(), '🔧', 'DEBUG');
            }
        }

        $this->log("🎯 Обработка завершена. Всего обработано: $processedOrdersCount заказов");
    }

    private function log($message, $icon = '📝', $type = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "$timestamp $icon [$type] $message\n";

        // Также выводим в консоль при запуске из CLI
        if (php_sapi_name() === 'cli') {
            echo $log_entry;
        }
    }

    private function getExpiredWaitingVirtualCardOrders()
    {
        $orders = $this->orders->getWaitingVirtualCardOrders();

        $filteredOrders = [];
        foreach ($orders as $order) {
            $timestamp = (int)$this->order_data->read($order->id, $this->order_data::CREATED_AT_VIRTUAL_CARD_TIMESTAMP);

            if (!$timestamp) {
                continue;
            }

            $currentTime = time();
            $diffSeconds = $currentTime - $timestamp;
            $diffMinutes = $diffSeconds / 60;

            if ($diffMinutes >= 20) {
                $filteredOrders[] = $order;
            }
        }

        return $filteredOrders;
    }
}

try {
    $cronAction = new UpdateExpiredWaitingOrderStatusCronAction();
    $cronAction->execute();
} catch (Exception $e) {
    // Логируем критические ошибки
    $timestamp = date('Y-m-d H:i:s');
    $error_message = "$timestamp 💥 [CRITICAL] Фатальная ошибка в крон-скрипте: " . $e->getMessage() . "\n";
    $error_message .= "$timestamp 🔥 [CRITICAL] Trace: " . $e->getTraceAsString() . "\n";

    $log_file = dirname(__FILE__).'/../logs/update_virtual_card_status_cron.log';
    file_put_contents($log_file, $error_message, FILE_APPEND | LOCK_EX);

    if (php_sapi_name() === 'cli') {
        echo $error_message;
    }

    exit(1);
}
