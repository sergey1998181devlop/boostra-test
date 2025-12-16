<?php

require_once('View.php');

class OverdueSliderView extends View
{
    public function fetch()
    {
        return $this->log();
    }

    public function log()
    {
        $data = $_POST;

        try {
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
            $orderId = isset($data['order_id']) ? (int)$data['order_id'] : null;
            $action = $data['action'] ?? null;
            $overdueDay = isset($data['overdue_day']) ? (int)$data['overdue_day'] : null;
            $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];

            if (empty($userId) || empty($orderId) || empty($action)) {
                $this->logging(__METHOD__, 'log', $data, ['error' => 'Missing required parameters'], $this->overdue_slider_service::LOG_FILE_NAME);
                echo json_encode([
                    'error' => true,
                    'message' => 'Missing required parameters'
                ]);
                die;
            }

            $this->overdue_slider_service->logEvent($userId, $orderId, $action, $overdueDay, $meta);

            echo json_encode(['success' => true]);
            die;
        } catch (\Throwable $t) {
            $this->logging(__METHOD__, 'log', $data, ['error' => $t->getMessage()], $this->overdue_slider_service::LOG_FILE_NAME);

            echo json_encode([
                'success' => false,
                'message' => $t->getMessage()
            ]);
            die;
        }
    }
}