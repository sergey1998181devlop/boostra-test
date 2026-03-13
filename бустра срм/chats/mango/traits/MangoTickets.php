<?php

namespace chats\mango\traits;

use chats\main\Users;
use chats\mango\traits\StandartMethods AS Main;
use chats\mango\traits\tasks\Tasks;
use Simpla;

trait MangoTickets {

    use Main,
        Tasks;

    /**
     * отправка сообщение о готовности рассмотрения заявки
     * при наличии записи разговора
     */
    public function sendMessegeUserOnEmailInTickets($data) {
        dd($data);
    }

    /**
     * Тикет по жалобе на службу взыскания
     */
    public function complaintToTheCollectionService($data) {
        $id = $this->setDataTicket($data, 4, true);
        $this->returnJson($id);
    }

    /**
     * Задача о напоминании звонка
     */
    public function sheduleCallUser($data) {
        $this->sheduleCall($data);
    }

    /**
     * Тикет на технические проблемы
     */
    public function technicalProblems($data) {
        $id = $this->setDataTicket($data, 9);
        $this->returnJson($id);
    }

    /**
     * Тикет на прочее
     */
    public function other($data) {
        dd($data);
    }

    /**
     * Возврат страховки
     */
    public function insuranceRefund($data) {
        $id = $this->setDataTicket($data, 3);
        $this->returnJson($id);
    }

    /**
     * Сохранение тикета в базе
     */
    private function setDataTicket($data, $problemId, $addTask = false) {
        $date = date("Y-m-d H:i:s");
        $main = new Users();
        $id = 0;
        $userInfo = $main->getUserInfoByPhone($data['phone']);
        $creditInfo = $main->getLastCreditInfoByUserId($userInfo->UID);
        if (isset($creditInfo->info)) {
            $info = $creditInfo->info;
            $newTicket = [
                'created' => $date,
                'appeal_date' => $date,
                'source' => 'Звонок',
                'subject' => $problemId,
                'accept_fio' => $data['managerName'],
                'client_fio' => $info->lastname . ' ' . $info->firstname . ' ' . $info->patronymic,
                'client_birth' => $userInfo->birth,
                'client_phone' => $data['phone'],
                'loan_date' => $info->date,
                'loan_summ' => $info->amount,
                'order_number' => $info->{'1c_id'},
                'manager_id' => $data['managerId'],
                'status_id' => 4,
                'close_comment' => '',
                'close_date' => $date
            ];
            settype($data['tiketId'], 'int');
            if ($data['tiketId'] === 0) {
                $new = new Simpla;
                $id = $new->tickets->add_ticket($newTicket);
            } else {
                $id = $data['tiketId'];
            }
            /* создание задачи по данному тикету */
            if ($addTask) {
                #$this->createTaskByTicket((object) ['problemId' => $problemId, 'info' => $info, 'ticketId' => $id, 'ticket' => $newTicket]);
            }
        }
        return $id;
    }

    /**
     * Тикет на дополнительные услуги
     */
    public function additionalServices($data) {
        dd($data);
    }

}
