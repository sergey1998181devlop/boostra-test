<?php

require_once('View.php');

class TicketView extends View
{
    public function fetch()
    {
        $this->redirectIfNotLoggedIn();

        if ($this->request->get('action') == 'add') {
            $this->add();
        }
    }

    private function add(): void
    {
        $data = new stdClass();
        $data->user_id = $this->user->id;
        $data->fio = $this->request->post('feedback_name', 'string');
        $data->email = $this->request->post('feedback_email');
        $data->phone = $this->request->post('feedback_phone', 'string');
        $data->topic = $this->request->post('feedback_topic', 'integer');
        $data->text = $this->request->post('feedback_text', 'string');
        $data->files = $this->request->files('feedback_file');
        
        try {
            $ticketId = $this->tickets->handleRequest($data);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Обращение #' . $ticketId . ' успешно создано. В ближайшее время с вами свяжется наш менеджер. Спасибо за обращение!',
                'ticket_id' => $ticketId
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function sendJsonResponse(array $response)
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");
        
        echo json_encode($response);
        exit;
    }
}