<?php

namespace chats\main;

use Simpla;

class Users extends Simpla {

    public static $problemsRole = [
        4 => 'yurist',
        9 => 'developer',
        3 => 'yurist'
    ];

    /**
     * Приведение номера телефона к необходимому типу и формату
     *
     * @param string|integer $phone Номер абонента получателя
     * @return integer
     */
    public static function preparePhone($phone) {
        settype($phone, 'string');
        $phonePrepare = preg_replace('/\D+/iu', '', trim($phone));
        if ($phonePrepare[0] == '8') {
            $phonePrepare[0] = '7';
        }
        settype($phonePrepare, 'integer');
        return (integer) $phonePrepare;
    }

    public static function getUserInfoByPhone($data) {
        $simplaObj = new Simpla;
        $phone = self::preparePhone($data);
        $query = $simplaObj->db->placehold
                ("
                    SELECT
                        *
                    FROM
                        __users
                    WHERE
                        __users.phone_mobile = '" . $phone . "'
                ");
        $simplaObj->db->query($query);
        $res = $simplaObj->db->result();
        if ($res) {
            return $res;
        }
        return false;
    }

    /**
     * Поиск пользователя по телефону с фильтрацией по site_id
     *
     * @param string|int $phone
     * @param string|null $siteId
     * @return object|false
     */
    public static function getUserInfoByPhoneAndSite($phone, $siteId = null) {
        $simplaObj = new Simpla;
        $phonePrepared = self::preparePhone($phone);

        $siteCondition = '';
        if ($siteId && $siteId !== 'all') {
            $siteCondition = " AND u.site_id = '" . $simplaObj->db->escape($siteId) . "'";
        }

        $query = $simplaObj->db->placehold("
            SELECT
                u.*,
                o.id as active_order_id,
                o.amount as loan_amount,
                o.1c_status as loan_status
            FROM
                __users u
            LEFT JOIN __orders o ON o.user_id = u.id
                AND o.1c_status LIKE '%выдан%'
            WHERE
                u.phone_mobile = '" . $phonePrepared . "'" . $siteCondition . "
            ORDER BY o.date DESC
            LIMIT 1
        ");

        $simplaObj->db->query($query);
        $res = $simplaObj->db->result();

        return $res ?: false;
    }

    public static function getLastCreditInfoByUserId($uid) {
        $credit = self::getLastCreditByUserIdIn1cDataBase($uid);
        if ($credit) {
            $res = $credit;
        } else {
            $res = self::getLastCreditByUserIdInLocalDataBase($uid);
        }
        if ($res) {
            return (object) ['info' => $res];
        }
        return false;
    }

    public static function getLastCreditByUserIdIn1cDataBase($uid) {
        // TODO: Проверить, используется ли метод в проекте
        $simplaObj = new Simpla;
        return $simplaObj->soap->get_user_balance_1c($uid);
    }

    public static function getLastCreditByUserIdInLocalDataBase($uid) {
        $simplaObj = new Simpla;
        $query = $simplaObj->db->placehold("
                SELECT *
                FROM
                    __users,
                    __user_balance,
                    __orders
                WHERE
                    __users.UID = '" . $uid . "'
                AND
                   __orders.user_id = __users.id
                AND
                   __orders.1c_status LIKE '%выдан%'
                AND
                   __user_balance.user_id = __users.id
                AND
                   __user_balance.zaim_date > __orders.date
                ORDER BY __orders.date DESC LIMIT 1");
        $simplaObj->db->query($query);
        return $simplaObj->db->result();
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

}
