<?php

namespace api\traits;

trait getTasksByType {
    
    
    public function getTasksManagersByIds($array) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __myTasks
            WHERE
                manager_id IN (?@)
        ", array_map('intval', (array) $array));
        $this->db->query($query);
        return $this->db->results();
    }
    
    public function setExecutor($problemId) {
        if (isset(self::$problemsRole[$problemId])) {
            return $usersObj->executorRoleSearch(self::$problemsRole[$problemId]);
        }
        return false;
    }
    
    /**
     * поиск исполнителя по роли
     */
    public function executorRoleSearch($role) {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __managers
            WHERE
                role = '" . $role . "'
        ");
        $this->db->query($query);
        $results = $this->db->results();
        if ($results) {
            return $this->executorSearch($results);
        }
        return false;
    }

    /**
     * Поиск исполнителя по наименьшему количеству задач
     */
    public function executorSearch($executors) {
        foreach ($executors as $executor) {
            $managers[] = $executor->id;
        }
        $executorsTasks = $this->getTasksManagersByIds($managers);
        if ($executorsTasks) {
            // подсчет задач у каждого менеджера
            $dataCount = false;
            foreach ($executorsTasks as $executorTask) {
                foreach ($managers as $manager) {
                    if ($executorTask->manager_id === $manager) {
                        if (!isset($dataCount[$manager])) {
                            $dataCount[$manager] = 1;
                        } else {
                            $dataCount[$manager]++;
                        }
                    } else {
                        if (!isset($dataCount[$manager])) {
                            $dataCount[$manager] = 0;
                        }
                    }
                }
            }
            $min = min($dataCount);
            foreach ($dataCount as $key => $count) {
                if ($count === $min) {
                    return $key;
                }
            }
        }
        return $managers[0];
    }

    /**
     * Информация по задаче технические проблемы из формы с фронта
     * @param type $taskInfo
     */
    public function technicalProblems($taskInfo) {
        $obj = (object)[];
        $info = $this->paymentReminderCall($taskInfo);
        $appeal =  $this->appeals->getApeal($taskInfo->ticketId);
        foreach ($info as $key => $value) {
            $obj->$key = $value;
        }
        foreach ($appeal as $key => $value) {
            $obj->$key = $value;
        }
        return $obj;
    }

    /**
     * Информация по задаче возврат страховки или получение справок
     */
    public function insuranceRefundOrReceiptOfCertificates($taskInfo) {
        $obj = (object)[];
        $info = $this->paymentReminderCall($taskInfo);
        $appeal =  $this->appeals->getApeal($taskInfo->ticketId);
        foreach ($info as $key => $value) {
            $obj->$key = $value;
        }
        foreach ($appeal as $key => $value) {
            $obj->$key = $value;
        }
        return $obj;
    }

    /**
     * Информация по задаче Уточнить данные по задаче
     */
    public function refineTaskData($taskInfo) {
        $obj = (object)[];
        $info = $this->paymentReminderCall($taskInfo);
        $appeal =  $this->appeals->getApeal($taskInfo->ticketId);
        foreach ($info as $key => $value) {
            $obj->$key = $value;
        }
        foreach ($appeal as $key => $value) {
            $obj->$key = $value;
        }
        return $obj;
    }
    
    public function callInCaseOfNonPaymentOnTheDatePromisedByTheClient($taskInfo) {
        return $this->insuranceRefund($taskInfo);
    }
    
    /**
     * 
     */
    public function controlOfPaymentOnTheDatePromisedByTheClient($taskInfo) {
        return $this->insuranceRefund($taskInfo);
    }

    /**
     * 
     */
    public function complaintCollectionService($taskInfo) {
        return $this->paymentReminderCall($taskInfo);
    }

    /**
     * Информаия по задаче напоминание о платеже
     */
    public function paymentReminderCall($taskInfo) {
        $credit = (object) [];
        
        $userInfo = $this->users->get_user($taskInfo->userId);
        $creditData = $this->soap->get_user_balance_1c($userInfo->UID, $userInfo->site_id);
        #$ticketInfo = $this->tickets->get_ticket($taskInfo->ticketId);
        $ticketInfo = $this->tickets->getMyTicket($taskInfo->ticketId);
        $credit->anket = $this->tickets->getAnket($taskInfo->ticketId);
        $orderInfo = $this->orders->get_order($creditData->{'Заявка'});
        $balance = $this->users->get_number_balance($creditData->{'Заявка'});
        $credit->taskComments = $this->getTaskComments($taskInfo->id);
        $credit->ticketComments = $this->tickets->getTicketComments($taskInfo->ticketId);
        if ($balance) {
            foreach ($balance as $key => $value) {
                $credit->$key = $value;
            }
        }
        if ($userInfo) {
            foreach ($userInfo as $key => $value) {
                $credit->$key = $value;
            }
        }
        if ($orderInfo) {
            foreach ($orderInfo as $key => $value) {
                $credit->$key = $value;
            }
        }
        if ($ticketInfo) {
            foreach ($ticketInfo as $key => $value) {
                $credit->$key = $value;
            }
        }
        if (isset($creditData->return)) {
            foreach ($creditData->return as $key => $value) {
                $credit->$key = $value;
            }
        }
        foreach ($taskInfo as $key => $value) {
            $credit->$key = $value;
        }
        return $credit;
    }

    /**
     * Информация по задаче Звонок при просрочке платежа
     */
    public function callToClarifyThePaymentDate($taskInfo) {
        return $this->paymentReminderCall($taskInfo);
    }

    public function getTaskComments($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __myTaskComments
            WHERE taskId = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    public function insuranceRefund($taskInfo) {
        $info = $this->paymentReminderCall($taskInfo);
        $insuranceInfo = $this->soap->getInsurance($info->{'НомерЗайма'});
        $data = (object) [];
        foreach ($info as $key => $value) {
            $data->$key = $value;
        }
        foreach ($insuranceInfo as $key => $values) {
            foreach ($values as $property => $value) {
                if ($property === 'НомерПолиса') {
                    $data->insurances[$key]->number = $value;
                }
                if ($property === 'СуммаСтраховки') {
                    $data->insurances[$key]->summ = $value;
                    $data->insurances[$key]->returnSumm = $value;
                }
                if ($property === 'Организация') {
                    $data->insurances[$key]->organizationName = $value;
                }
                if ($property === 'ДатаСтраховки') {
                    $data->insurances[$key]->startDate = date("Y-m-d", strtotime(preg_replace('/(\d{4})(\d{2})(\d{2})/iu', '${1}-${2}-${3} 00:00:00', $value)));
                    $data->insurances[$key]->endDate = date("Y-m-d", strtotime($data->insurances[$key]->startDate) + 60 * 60 * 24 * 14);
                }
                if ($property === 'СуммаВозврата') {
                    if ($value == 0 AND strtotime($data->insurances[$key]->endDate) > time()) {
                        $data->insurances[$key]->status = 'Подлежит возврату';
                    } elseif ($value) {
                        $data->insurances[$key]->status = 'Возвращена';
                    } else {
                        $data->insurances[$key]->status = 'Истек срок возврата';
                    }
                }
            }
        }
        return $data;
    }

}
