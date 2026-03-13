<?php

use api\handlers\CreateComplaintTicketHandler;

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/api/handlers/CreateComplaintTicketHandler.php';

class CreateComplaintTicket extends Simpla
{
    public function run()
    {
        $rawBody = $this->request->post();
        $params = json_decode($rawBody, true) ?: [];

        $this->log('create_complaint_ticket_request', [
            'payload_raw' => $rawBody,
            'payload_parsed' => $params
        ]);

        $result = (new CreateComplaintTicketHandler())->handle($params);
        
        $this->response->json_output($result);
    }

    /**
     * Обертка для метода логирования
     */
    private function log(string $type, array $data = []): void
    {
        $this->logging(
            $type,
            'ajax/create_complaint_ticket.php',
            [],
            $data,
            'complaint_tickets.txt'
        );
    }
}

(new CreateComplaintTicket())->run();