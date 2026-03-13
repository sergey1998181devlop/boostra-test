<?php
session_start();
chdir('..');

require_once dirname(__DIR__) . '/api/Simpla.php';

class NotificationManager extends Simpla {
    private $manager_id;

    public function __construct() {
        parent::__construct();
        $this->manager_id = $this->getManagerId();
    }

    public function handle() {
        $action = $this->request->post('action', 'get');

        try {
            switch ($action) {
                case 'get':
                    return $this->getNotifications();
                case 'mark_read':
                    return $this->markAsRead();
                default:
                    return $this->jsonResponse('error', 'Неизвестное действие');
            }
        } catch (Exception $e) {
            $this->jsonResponse('error', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function getNotifications() {
        $query = $this->db->placehold("
                SELECT * FROM s_managers_notifications 
                WHERE to_user = ? 
                AND is_read = 0
                ORDER BY id DESC 
                LIMIT 10 
                FOR UPDATE
            ", $this->manager_id);

        $this->db->query($query);

        $this->jsonResponse('success', null, $this->db->results());
    }

    private function markAsRead() {
        $notification_ids = $this->request->post('notification_ids') ?? [];

        if (empty($notification_ids)) {
            $query = $this->db->placehold("
                UPDATE s_managers_notifications 
                SET is_read = 1
                WHERE to_user = ?
                AND is_read = 0
            ", $this->manager_id);
        } else {
            $query = $this->db->placehold("
                UPDATE s_managers_notifications 
                SET is_read = 1
                WHERE to_user = ?
                AND is_read = 0
                AND id IN (?@)
            ", $this->manager_id, $notification_ids);
        }

        $this->db->query($query);
        $affected = $this->db->affected_rows();

        $this->jsonResponse(
            'success',
            'Уведомления помечены прочитанными',
            ['affected_count' => $affected]
        );
    }
    
    private function jsonResponse($status, $message = null, $data = null) {
        $response = ['status' => $status];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}

$controller = new NotificationManager();
$controller->handle();
