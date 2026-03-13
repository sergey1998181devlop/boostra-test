<?php

namespace chats\mango\traits\tasks;

use Simpla;
use chats\main\Users;
use chats\whatsapp\WhatsappMessages AS WA;
use chats\viber\ViberMessages AS VI;

trait Tasks {

    public static $runProblemsMethos = [
        3 => 'insuranceTask',
        9 => 'technicalTask',
        4 => 'collectionServiceTask'
    ];

    /**
     * Создание новой задачи и дампа по тикету
     */
    public function createTaskByTicket($data) {
        if (isset(self::$runProblemsMethos[$data->problemId])) {
            $method = self::$runProblemsMethos[$data->problemId];
            if (method_exists($this, $method)) {
                $this->$method($data);
                file_put_contents(mangoLogsDir . 'tiketData_' . $data->ticketId . '.json', json_encode($data));
            }
        }
    }

    public function insuranceTask($data) {
        $obj = $this->getDataGenerateForTaskByTiket($data);
        #$this->tasks->add_pr_task($obj);
    }

    public function technicalTask($data) {
        $obj = $this->getDataGenerateForTaskByTiket($data);
        #$this->tasks->add_pr_task($obj);
    }

    public function collectionServiceTask($data) {
        $simplaObj = new Simpla();
        $date = $simplaObj->tickets->setCountDayOverdue($data->info->payment_date);
        if ($date > 3 AND $date < 100) {
            $data->info->prolongation_count = $date;
            $obj = $this->getDataGenerateForTaskByTiket($data);
           # $this->tasks->add_pr_task($obj);
        }
    }

    public function sheduleCall($data) {
        $obj = false;
        $file = mangoLogsDir . 'tiketData_' . $data['tiketId'] . '.json';
        if (is_file($file)) {
            $obj = json_decode(file_get_contents($file));
            $ticketData = [
                'tiketId' => 0,
                'managerName' => $obj->ticket->accept_fio,
                'phone' => $obj->info->phone_mobile,
                'managerId' => $obj->ticket->manager_id
            ];
            $this->setDataTicket($ticketData, $obj->problemId, $addTask = true);
            unlink($file);
        }
    }

    public function sendMessageUsers($data) {
        $simplaObj = new Simpla();
        $userInfo = $simplaObj->users->getUserInfoByUserId($data['userId']);
        $messangers = $simplaObj->users->getMessangersInfoByUserId($data['userId'], $userInfo->phone_mobile);
        $message = 'тестовый текст';
        $data['id'] = $data['userId'];
        $data['text'] = $message;
        if ($messangers) {
            foreach ($messangers as $userMessanger) {
                if ($userMessanger->typeMessanger === 'viber') {
                    $vi = new VI;
                    $vi->sendText($data);
                }
            }
        } else {
            $wa = new WA;
            $wa->sendText($data);
            $site_id = $simplaObj->users->get_site_id_by_user_id($data['userId']);
            $simplaObj->smssender->send_sms($userInfo->phone_mobile, $message, $site_id);
        }
    }

    private function getDataGenerateForTaskByTiket($data) {
        $taskTime = time() + 60 * 60 * 24;
        $taskDate = date("Y-m-d 00:00:00", $taskTime);
        $simplaObj = new Simpla();
        $obj = (object) [
                    'number' => $data->info->zaim_number,
                    'ticketId' => $data->ticketId,
                    'task_date' => $taskDate,
                    'user_id' => $data->info->user_id,
                    'user_balance_id' => $data->info->id,
                    'manager_id' => $this->setExecutor($data->problemId),
                    'status' => 0,
                    'close' => 0,
                    'prolongation' => $data->info->prolongation_count,
                    'created' => date("Y-m-d H:i:s", time()),
                    'od_start' => $data->info->ostatok_od,
                    'percents_start' => $data->info->ostatok_percents,
                    'period' => 'zero',
                    'paid' => 0,
                    'timezone' => $simplaObj->users->get_timezone($data->info->Regregion)
        ];
        return $obj;
    }

    public function setExecutor($problemId) {
        $usersObj = new Users();
        if (isset($usersObj::$problemsRole[$problemId])) {
            return $usersObj->executorRoleSearch($usersObj::$problemsRole[$problemId]);
        }
        return false;
    }

}
