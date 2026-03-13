<?php

use api\services\OrderService;

require_once('Simpla.php');

class OverdueSliderService extends Simpla
{
    const LOG_FILE_NAME = 'overdue_slider.txt';

    public function logEvent($userId, $orderId, $action, ?int $overdueDay = null, array $meta = []): void
    {
        try {
            $user = $this->users->get_user($userId);

            if (empty($user)) {
                $this->logging(__METHOD__, 'logEvent', [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'action' => $action,
                    'meta' => $meta,
                ], ['error' => 'User not found'], self::LOG_FILE_NAME);

                return;
            }

            if (empty($overdueDay)) {
                $order = $this->orders->get_order($orderId);

                if (empty($order)) {
                    $this->logging(__METHOD__, 'logEvent', [
                        'user_id' => $userId,
                        'order_id' => $orderId,
                        'action' => $action,
                        'meta' => $meta,
                    ], ['error' => 'Order not found'], self::LOG_FILE_NAME);

                    return;
                }

                $balance = $this->users->get_user_balance_1c_normalized($order->user_id, function ($balance) use ($order) {
                    return $balance['Заявка'] == $order->id_1c;
                });

                if (empty($balance)) {
                    $this->logging(__METHOD__, 'logEvent', [
                        'user_id' => $userId,
                        'order_id' => $orderId,
                        'action' => $action,
                        'meta' => $meta,
                    ], ['error' => 'User balance not found'], self::LOG_FILE_NAME);

                    return;
                }

                $overdueDay = OrderService::calculateDueDays($balance->payment_date);

                // Если нет просрочки — выходим
                if ($overdueDay === null || $overdueDay <= 0) {
                    return;
                }
            }

            $query = $this->db->placehold("INSERT INTO s_overdue_slider_events SET ?%", [
                'created_at' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'order_id' => $orderId,
                'overdue_day' => $overdueDay,
                'action' => $action,
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'nk_pk' => $user->loan_history ? 'ПК' : 'НК',
            ]);

            $this->db->query($query);

        } catch (\Throwable $throwable) {
            $this->logging(__METHOD__, 'logEvent', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'action' => $action,
                'meta' => $meta,
            ], ['error' => $throwable->getMessage()], self::LOG_FILE_NAME);
        }
    }

    public function hasRecentInteraction(int $userId, int $orderId, int $hours = 48): bool
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        $query = $this->db->placehold("SELECT COUNT(*) as cnt FROM s_overdue_slider_events WHERE user_id = ? AND order_id = ? AND created_at >= ?", $userId, $orderId, $since);
        $this->db->query($query);

        return (int)$this->db->result('cnt') > 0;
    }

    public function logPaid(int $userId, int $orderId)
    {
        if ($this->hasRecentInteraction($userId, $orderId)) {
            $this->logEvent($userId, $orderId, 'paid_after', null, ['diff_hours' => 48]);
        }
    }

    public function hasInteract(int $userId): bool
    {
        $query = $this->db->placehold("SELECT COUNT(*) as cnt FROM s_overdue_slider_events WHERE user_id = ? and action = 'slider_first'", $userId);
        $this->db->query($query);

        return (int)$this->db->result('cnt') > 0;
    }

    public function hasClicked(int $userId): bool
    {
        $query = $this->db->placehold("SELECT COUNT(*) as cnt FROM s_overdue_slider_events WHERE user_id = ? and action = 'info_click'", $userId);
        $this->db->query($query);

        return (int)$this->db->result('cnt') > 0;
    }
}