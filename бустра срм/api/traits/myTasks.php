<?php

namespace api\traits;

use api\traits\Sorts;
use api\traits\setPages;
use api\traits\descriptionOfTasksAndTypes;
use api\traits\getTasksByType;

trait myTasks {

    use Sorts,
        setPages,
        descriptionOfTasksAndTypes,
        getTasksByType;

    /**
     * Получить наименование задачи по ее типу
     */
    public function getTaskNameByType($taskType) {
        if (isset($this->taskNames[$taskType])) {
            return $this->taskNames[$taskType];
        }
        return false;
    }

    /**
     * Получить тип задачи по ее наименованию
     */
    public function getTaskTypeByName($taskName) {
        foreach ($this->taskNames AS $key => $value) {
            if ($value === $taskName) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Получить id задачи
     * @param string $statusName
     * @return int
     */
    public function getTaskIdByType(string $statusName) {
        foreach ($this->actionsByTaskType AS $key => $value) {
            if ($value == $statusName) {
                return $key;
            }
        }
    }

    /**
     * Получить наименование статуса задачи
     * @param type $idStatus
     * @return string
     */
    public function getStatusNameById(int $idStatus) {
        foreach ($this->taskStatuses as $key => $value) {
            if ($key === $idStatus) {
                return $value;
            }
        }
        return 'Новая';
    }

    /**
     * Добавить новую задачу в базу
     * @param type $data
     * @return int id
     */
    public function addNewTask($data) {
        $query = $this->db->placehold("
            INSERT INTO __myTasks SET ?%
        ", (array) $data);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    /**
     * Обновить Задачу по id
     * @param type $id
     * @param type $data
     * @return int
     */
    public function updateTaskById(int $id, array $data) {
        $query = $this->db->placehold("
            UPDATE __myTasks SET ?% WHERE id = ?
        ", (array) $data, (int) $id);
        $this->db->query($query);
        return $id;
    }

    /**
     * Удалить задачу с данным id
     * @param int $id
     */
    public function deleteTaskById(int $id) {
        $query = $this->db->placehold("
            DELETE FROM __myTasks WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
    }

    /**
     * Получить задачи для менеджера
     * @return type
     */
    public function getMyTasks(int $idManager) {
        $query = $this->db->placehold("
            SELECT
                * 
            FROM 
                __myTasks
            WHERE
                managerId = " . $idManager . "
            AND
                taskDate < '" . date("Y-m-d H:i:s") . "'
            " . $this->sortMyTasks()
        );
        $query .= $this->getLimit($query);
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

    /**
     * Получить информацию о задаче по ее типу
     * @param type $taskInfo
     */
    public function getMyTaskByType($taskInfo) {
        if (isset($this->actionsByTaskType[$taskInfo->taskType])) {
            $method = $this->actionsByTaskType[$taskInfo->taskType];
            if (method_exists($this, $method)) {
                return $this->$method($taskInfo);
            }
        }
        return $this->paymentReminderCall($taskInfo);
    }

    /**
     * Получить задачу
     * @return type
     */
    public function getMyTasksById(int $id) {
        $query = $this->db->placehold("
            SELECT
                * 
            FROM 
                __myTasks
            WHERE
                id = " . $id
        );
        $this->db->query($query);
        $result = $this->db->result();
        return $result;
    }
    /**
     * Получить задачу
     * @return type
     */
    public function getMyTasksByTicketId(int $id) {
        $query = $this->db->placehold("
            SELECT
                * 
            FROM 
                __myTasks
            WHERE
                ticketId = " . $id ." 
            ORDER BY taskStatus ASC, taskDate ASC, id DESC
            
        ");
        $this->db->query($query);
        $result = $this->db->results();
        return $result;
    }

}
