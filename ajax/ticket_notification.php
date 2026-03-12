<?php

error_reporting(-1);
ini_set('display_errors', 'Off');

date_default_timezone_set('Europe/Moscow');

session_start();
require_once('../api/Simpla.php');
require_once('../vendor/autoload.php');

class TicketNotificationAjax extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    private function run(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->request->json_output([
                'success' => false,
                'message' => 'Неверный метод запроса'
            ]);
        }

        $userId = $this->request->post('user_id', 'integer');
        $type = $this->request->post('type', 'string');

        if (is_null($userId)) {
            $this->request->json_output([
                'success' => false,
                'message' => 'Не задан User ID'
            ]);
        }

        if (empty($type)) {
            $type = 'pause';
        }

        try {
            // Определяем статус тикета в зависимости от типа уведомления
            $statusId = ($type === 'resolved') ? 4 : 3;

            $tickets = $this->db->select('s_mytickets', [
                'client_id' => $userId,
                'notify_user' => 1,
                'status_id' => $statusId
            ]);
        } catch (Exception $e) {
            $this->request->json_output([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        if (count($tickets) > 0) {
            $ticketIds = [];
            foreach ($tickets as $ticket) {
                $ticketIds[] = $ticket->id;
            }

            try {
                $this->db->query('UPDATE s_mytickets SET notify_user = 0 WHERE id IN (?@)', $ticketIds);
                $this->request->json_output([
                    'success' => true,
                    'message' => 'Уведомления по тикетам клиента отключены'
                ]);
            } catch (Exception $e) {
                $this->request->json_output([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            $this->request->json_output([
                'success' => false,
                'message' => 'Нет тикетов для уведомления клиента'
            ]);
        }
    }
}

new TicketNotificationAjax();