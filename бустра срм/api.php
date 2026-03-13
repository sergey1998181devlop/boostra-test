<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

include_once __DIR__ . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'Simpla.php';

class Api extends Simpla {

    public function __construct() {
        parent::__construct();
        $keys = array_keys($_GET);
        $values = array_values($_GET);
        if ($keys) {
            for ($i = 0; $i < count($keys); $i++) {
                $args[trim($keys[$i])] = trim($values[$i]);
            }
        }
        if (isset($args['method'])) {
            $method = $args['method'];
        }
        if (method_exists($this, $method)) {
            if (isset($_SESSION['manager_id'])) {
                $manager = $this->managers->get_manager(intval($_SESSION['manager_id']));
                $roles = $this->managers->get_roles();
                if (isset($roles[$manager->role])) {
                    echo json_encode((object) ['result' => true, 'data' => $this->$method($args)]);
                    exit();
                }
            }
        }
        echo json_encode((object) ['result' => false, 'data' => false]);
    }

    private function createNewTask($args) {
        unset($args['method']);
        if ($args['taskDate'] === 'NaN') {
            $args['taskDate'] = 0;
        }
        $args['taskDate'] = date("Y-m-d 00:00:00", time() + (60 * 60 * 24 * (int) $args['taskDate']));
        $data = [];
        foreach ($args AS $key => $value) {
            if ($value === 'undefined' OR $value === 'null') {
                $data[$key] = NULL;
            } else {
                $data[$key] = $value;
            }
        }
        if (!isset($data['managerId'])) {
            if (isset($this->tasks->executorRole[$data['taskType']])) {
                $data['managerId'] = $this->tasks->setExecutor($this->tasks->executorRole[$data['taskType']]);
            } else {
                $data['managerId'] = $_SESSION['manager_id'];
            }
        }
        if (!isset($data['dateComplition'])) {
            $data['dateComplition'] = date("Y-m-d H:i:s", time() + (60 * 60 * 24 * 14));
        } else {
            if ($data['dateComplition'] === 'NaN') {
                $data['dateComplition'] = date("Y-m-d 00:00:00", time() + (60 * 60 * 24 * 14));
            } else {
                $data['dateComplition'] = date("Y-m-d 00:00:00", strtotime($data['dateComplition']));
            }
        }
        $data['taskType'] = $this->tasks->getTaskIdByType($args['taskType']);
        return $this->tasks->addNewTask($data);
    }

    private function updateTaskStatus($args) {
        $query = $this->db->placehold("
            UPDATE __myTasks 
            SET 
                taskStatus = " . $args['taskStatus'] . ",
                dateEdit = '" . date("Y-m-d H:i:s") . "'
            WHERE 
                id = " . $args['id'] . "
        ");
        $this->db->query($query);
    }

    private function loggingSurvey($args) {
        unset($args['method']);
        if ($args['answer'] === 'undefined') {
            $args['answer'] = NULL;
        }
        $query = $this->db->placehold("
            INSERT INTO __myAnket SET ?%
        ", (array) $args);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    private function getInfoByContractNumber($args) {
        if (isset($args['order'])) {
            $query = $this->db->placehold(
                    "
                    SELECT
                        *
                    FROM
                        __users,
                        __user_balance
                    WHERE
                        __user_balance.zaim_number LIKE '%" . $args['order'] . "%'
                    AND 
                        __user_balance.user_id = __users.id
                    ORDER BY 
                        __user_balance.zaim_date DESC
                    LIMIT 5
                ");
            $this->db->query($query);
            return $this->db->results();
        }
        return false;
    }

    private function getInfoByContractNumberId($args) {
        if (isset($args['order'])) {
            $query = $this->db->placehold(
                    "
                    SELECT
                        *
                    FROM
                        __users,
                        __user_balance
                    WHERE
                        __user_balance.user_id = __users.id
                    AND 
                        __user_balance.zaim_number LIKE '%" . $args['order'] . "%'
                    ORDER BY 
                        __user_balance.zaim_date DESC
                    LIMIT 5
                ");
            $this->db->query($query);
            return $this->db->results();
        }
        return false;
    }

    private function getInfoByOrderNumber($args) {
        if (isset($args['order'])) {
            $query = $this->db->placehold(
                    "
                    SELECT
                        *
                    FROM
                        __users,
                        __orders
                    WHERE
                        __orders.1c_id LIKE '%" . $args['order'] . "%'
                    AND
                        __orders.user_id = __users.id
                    ORDER BY __orders.id DESC
                    LIMIT 5
                ");
            $this->db->query($query);
            return $this->db->results();
        }
        return false;
    }

    private function newTicketsTag($args) {
        if (isset($args['name']) AND isset($args['color'])) {
            if ($args['name'] AND $args['color']) {
                $data = [
                    'name' => $args['name'],
                    'color' => '#' . $args['color']
                ];
                return $this->tickets->newTicketsTag($data);
            }
        }
        return false;
    }

    private function addCommentTickets($args) {
        $data = [
            'comment' => $args['comment'],
            'managerId' => $args['managerId'],
            'tiсketId' => $args['tiсketId']
        ];
        return $this->tickets->addMyTicketComment($data);
    }

    private function saveTicketTag($args) {
        if (isset($args['id'])) {
            return $this->tickets->saveTicketTag($args);
        }
        return false;
    }

    private function getTicketsTag($args) {
        if (isset($args['tag'])) {
            $data = $this->tickets->getTicketTagById($args['tag']);
            if ($data) {
                return $data;
            }
            return false;
        }
        return false;
    }

    private function getTicketsTags() {
        $data = $this->tickets->getTicketTags();
        if ($data) {
            return $data;
        } else {
            return false;
        }
    }

    private function getInfoByOrderNumberId($args) {
        if (isset($args['order'])) {
            $query = $this->db->placehold("
                SELECT
                    *
                FROM
                    __users,
                    __orders
                WHERE
                    __orders.1c_id = '" . $args['order'] . "'
                AND
                    __orders.user_id = __users.id
            ");
            $this->db->query($query);
            return $this->db->result();
        }
        return false;
    }

    private function getClientInfoByPhone($args) {
        if (isset($args['phone'])) {
            $query = $this->db->placehold("
                    SELECT
                        *
                    FROM 
                        __users
                    WHERE
                        phone_mobile LIKE '%" . $this->curl->preparePhone($args['phone']) . "%' LIMIT 5
                ");
            $this->db->query($query);
            return $this->db->results();
        }
        return false;
    }

    private function getClientInfoById($args) {
        if (isset($args['id'])) {
            $query = $this->db->placehold("
            SELECT *
            FROM __users
            WHERE
                id = '" . $args['id'] . "'"
            );
            $this->db->query($query);
            return $this->db->result();
        }
        return false;
    }

    private function getClientInfoByFio($args) {
        if (isset($args['fio'])) {
            $query = $this->db->placehold("
            SELECT *
            FROM __users
            WHERE
        " . $this->getFioFilter($args['fio']) . " LIMIT 5"
            );
            $this->db->query($query);
            return $this->db->results();
        }
        return false;
    }

    private function getFioFilter($fio) {
        $filtersString = '';
        $fio = explode(' ', $fio);
        if (isset($fio[0]) and!isset($fio[1])) {
            $filtersString .= " lastname LIKE '%" . $fio[0] . "%' ";
            $filtersString .= " OR firstname LIKE '%" . $fio[0] . "%' ";
            $filtersString .= " OR  patronymic LIKE '%" . $fio[0] . "%' ";
        } elseif (isset($fio[0]) and isset($fio[1]) and!isset($fio[2])) {
            $filtersString .= " lastname LIKE '%" . $fio[0] . "%' AND firstname LIKE '%" . $fio[1] . "%' ";
            $filtersString .= " OR firstname LIKE '%" . $fio[0] . "%' AND patronymic LIKE '%" . $fio[1] . "%' ";
            $filtersString .= " OR  patronymic LIKE '%" . $fio[0] . "%' AND lastname LIKE '%" . $fio[1] . "%'";
        } elseif (isset($fio[0]) and isset($fio[1]) and isset($fio[2])) {
            $filtersString .= " lastname LIKE '%" . $fio[0] . "%' AND firstname LIKE '%" . $fio[1] . "%' AND patronymic LIKE '%" . $fio[2] . "%' ";
            $filtersString .= " lastname LIKE '%" . $fio[0] . "%' AND firstname LIKE '%" . $fio[2] . "%' AND patronymic LIKE '%" . $fio[1] . "%' ";
            $filtersString .= " lastname LIKE '%" . $fio[1] . "%' AND firstname LIKE '%" . $fio[2] . "%' AND patronymic LIKE '%" . $fio[0] . "%' ";
            $filtersString .= " lastname LIKE '%" . $fio[1] . "%' AND firstname LIKE '%" . $fio[0] . "%' AND patronymic LIKE '%" . $fio[2] . "%' ";
            $filtersString .= " lastname LIKE '%" . $fio[2] . "%' AND firstname LIKE '%" . $fio[1] . "%' AND patronymic LIKE '%" . $fio[0] . "%' ";
            $filtersString .= " lastname LIKE '%" . $fio[2] . "%' AND firstname LIKE '%" . $fio[0] . "%' AND patronymic LIKE '%" . $fio[1] . "%' ";
        }
        return $filtersString;
    }

    private function saveCommentTasks($args) {
        unset($args['method']);
        $query = $this->db->placehold("
            INSERT INTO __myTaskComments SET ?%
        ", (array) $args);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    private function createNewTicket($args) {
        $ticket['inputChanel'] = $args['inputChanel'];
        $ticket['managerId'] = $args['managerId'];
        $ticket['userId'] = $args['userId'];
        $ticket['status'] = $args['status'];
        $ticket['them'] = $args['them'];
        $ticket['dateCreate'] = date('Y-m-d H:i:s', strtotime($args['dateCreate']));
        $ticket['executorManagerId'] = $args['executorManagerId'];
        $ticket['appealNumber'] = $args['appealNumber'];
        $idTicket = $this->tickets->createNewTicket($ticket);
        if ($ticket['appealNumber']) {
            $this->appeals->updateApeal(['TicketId' => $idTicket], $ticket['appealNumber']);
        }
        if ($args['commentTicket']) {
            $commentTicket['tiсketId'] = $idTicket;
            $commentTicket['comment'] = $args['commentTicket'];
            $commentTicket['managerId'] = $ticket['managerId'];
            $this->tickets->addMyTicketComment($commentTicket);
        }
        return $idTicket;
    }

    private function sendMessageToMessangers($args) {
        var_dump($args);
    }

    private function sendMessageToEmail($args) {
        $headers['MIME-Version'] = '1.0';
        $headers['Content-type'] = 'text/html; charset=utf-8';
        return mail($args['to'], $args['them'], wordwrap($args['text'], 70, "\r\n"), $headers);
    }

    private function sendMessageToSms($args) {
        var_dump($args);
    }
}

$objClass = new Api();
