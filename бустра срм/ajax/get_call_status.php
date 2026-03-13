<?php

session_start();
chdir('..');

require 'api/Simpla.php';

class VoximplantCallStatus extends Simpla {

    private $data;
    private $response = [];

    public function __construct() {
        $this->data = $this->decodeRequestData();
        $this->logData($this->data);
    }

    public function run() {
        if ($this->validateData()) {
            $this->insertData();
            $task = $this->tasks->getTaskWithPhone($this->data['call_list_data']['phone_number']);
            if ($task) {
                $this->updateTask($task);
                $this->addComment($task);
                $this->response['success'] = 'Task updated';
            } else {
                $this->response['error'] = 'Task not found';
            }
        } else {
            $this->response['error'] = 'Incorrect data';
        }

        $this->outputResponse();
    }

    private function decodeRequestData() {
        $postData = $this->request->post();
        return json_decode($postData, true);
    }

    private function logData($data) {
        $this->logging(__METHOD__, $this->config->back_url.'/ajax/get_call_status.php', "", $data, 'vox-status.txt');
    }

    private function validateData() {
        return !empty($this->data['call_list_data']['phone_number']) &&
            !empty($this->data['call_list_data']['id']) &&
            isset($this->data['call_result']) &&
            !empty($this->data['call_date_time']);
    }

    private function insertData()
    {
        $data = $this->prepareData();
        $query = $this->db->placehold("
            INSERT INTO vox_call_result SET ?%
        ", (array)$data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    private function prepareData()
    {
        $status = $this->getStatus(true);

        return [
            'client_phone' => $this->data['call_list_data']['phone_number'] ?? '',
            'company_phone' => str_replace(';', '', $this->data['aon'] ?? ''),
            'call_result' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ];

    }

    private function getStatus(bool $update = false): int
    {
        $statusArray = $this->getStatusArray();
        $callResult = mb_strtolower($this->data['call_result'], 'UTF-8');
        $dialingStatus = mb_strtolower($this->data['dialing_status'], 'UTF-8');

        $status = $statusArray[$callResult] ?? 2;
        $silence = $this->hasSilence();

        if ($update) {
            $status = $this->determineUpdatedStatus($dialingStatus, $silence);
        }

        return $status;
    }

    private function hasSilence(): bool
    {
        for ($i = 11; $i >= 1; $i--) {
            $key = "decision_{$i}";
            if (!empty($this->data[$key]) && $this->data[$key] === 'Тишина') {
                return true;
            }
        }
        return false;
    }

    private function determineUpdatedStatus(string $dialingStatus, bool $silence): int
    {
        if ($dialingStatus === 'не дозвонились') {
            return 13;
        }

        if ($dialingStatus === 'автоответчик (amd)' || $dialingStatus === 'автоответчик (словари)') {
            return 14;
        }

        if ($dialingStatus === 'успешно') {
            return $silence ? 15 : 16;
        }

        if (!empty($this->data['decision_5']) && $this->data['decision_5'] === 'Клиент сбросил') {
            return 17;
        }

        return $this->getStatusArray()['нет результата'] ?? 2;
    }

    private function updateTask($task) {
        $status = $this->getStatus();
        $this->tasks->update_pr_task($task->id, [
            'status' => $status,
            'vox_call' => 1
        ]);
    }

    private function getStatusArray(): array
    {
        return [
            'отказ от оплаты' => 4,
            'отказ от оплаты: не назвал дату' => 4,
            'отказ от оплаты: указал другую дату' => 4,
            'отказ от оплаты: указал причину' => 4,
            'подтвердил личность' => 7,
            'получил информацию по долгу' => 6,
            'уже оплатил' => 5,
            'умер' => 8,
            'номер в стоп-листе' => 9,
            'контакт с 3 лицом: знает клиента' => 11,
            'в тюрьме' => 8,
            'банкрот' => 8,
            'негатив от абонента' => 10,
            'заявка на рефинансирование' => 12,
            'согласен оплатить' => 3,
            'согласен оплатить: указал другую дату' => 3,
            'согласен оплатить: не назвал дату' => 3,
            'согласен оплатить: оплатит в срок' => 3,
            'нет результата' => 2,
            'недозвон' => 2,
            'не дозвонились' => 13,
            'автоответчик (AMD)' => 14,
            'автоответчик (словари)' => 14,
            'дозвонились(тишина)' => 15,
            'дозвонились' => 16,
            'сброс' => 17,
        ];
    }

    private function addComment($task) {
        $this->comments->add_comment([
            'manager_id' => 50,
            'user_id' => $task->user_id,
            'text' => 'Звонок роботом, статус: '.mb_strtolower($this->data['call_result'], 'UTF-8') . " " . $this->data['call_date_time'],
            'created' => date('Y-m-d H:i:s'),
            'block' => 'vox_status'
        ]);
    }

    private function outputResponse() {
        header('Content-Type: application/json');
        echo json_encode($this->response);
        exit();
    }

}

$voximplantCallStatus = new VoximplantCallStatus();
$voximplantCallStatus->run();

?>
