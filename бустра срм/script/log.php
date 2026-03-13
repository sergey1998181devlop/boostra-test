<?php

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2048M');

chdir('..');
define('ROOT', __DIR__);
date_default_timezone_set('Europe/Moscow');

require 'api/Simpla.php';

class Log extends Simpla
{
    public function printUserByFio()
    {
        $firstName = '';
        $lastName = '';
        $patronymic = '';
        $birth = '';

        $query = $this->db->placehold(
            'SELECT id, firstname, lastname, patronymic, birth
                FROM s_users 
                WHERE firstname = ? AND lastname = ? AND patronymic = ? AND birth = ?', $firstName, $lastName, $patronymic, $birth
        );

        $this->db->query($query);
        $result = $this->db->result();

        print_r($result);
        die();
    }

    public function printUserById()
    {
        $userId = 1;

        $query = $this->db->placehold(
            'SELECT id, firstname, lastname, patronymic, birth
                FROM s_users 
                WHERE id = ?', $userId
        );

        $this->db->query($query);
        $result = $this->db->results();

        print_r($result);
        die();
    }

    public function printContactByUserId()
    {
        $userId = 1;

        $query = $this->db->placehold(
            'SELECT c.id, c.number
                FROM s_contracts c
                INNER JOIN s_users u ON u.id = c.user_id
                WHERE c.id = ?', $userId
        );

        $this->db->query($query);
        $result = $this->db->results();

        print_r($result);
        die();
    }

    public function printLogs()
    {
        $firstName = 'Александр';
        $lastName = 'Хайкин';
        $patronymic = 'Сергеевич';

        $query = $this->db->placehold(
            "SELECT u.id,
                   u.firstname,
                   u.lastname,
                   u.patronymic,
                   p.pdn,
                   p.amp_report_link,
                   p.credit_history_link
                    FROM s_users u
                             INNER JOIN s_orders o ON u.id = o.user_id
                             INNER JOIN pdn_calculation p ON o.id = p.order_id
                             INNER JOIN pti_app_creditreport cr ON cr.report_file LIKE CONCAT(REGEXP_SUBSTR(p.credit_history_link, 'media.*[^\.xml]'), '%')
                             INNER JOIN pti_app_loan l ON l.credit_report_id = cr.id
                             INNER JOIN pti_app_payment ap ON ap.loan_id = l.id
                    WHERE u.firstname = ?
                      AND u.lastname = ?
                      AND u.patronymic = ?;", $firstName, $lastName, $patronymic
        );

        $this->db->query($query);
        $result = $this->db->results();

        print_r($result);
        die();
    }



    public function printLog()
    {
        $firstName = 'Александр';
        $lastName = 'Хайкин';
        $patronymic = 'Сергеевич';

        $query = $this->db->placehold(
            "SELECT u.id,
                   u.firstname,
                   u.lastname,
                   u.patronymic,
                   p.pdn,
                   p.amp_report_link,
                   p.credit_history_link
                    FROM s_users u
                             INNER JOIN s_orders o ON u.id = o.user_id
                             INNER JOIN pdn_calculation p ON o.id = p.order_id
                             INNER JOIN pti_app_creditreport cr ON cr.report_file LIKE CONCAT(REGEXP_SUBSTR(p.credit_history_link, 'media.*[^\.xml]'), '%')
                             INNER JOIN pti_app_loan l ON l.credit_report_id = cr.id
                             INNER JOIN pti_app_payment ap ON ap.loan_id = l.id
                    WHERE u.firstname = ?
                      AND u.lastname = ?
                      AND u.patronymic = ?;", $firstName, $lastName, $patronymic
        );

        $this->db->query($query);
        $result = $this->db->results();

        print_r($result);
        die();
    }




    public function log()
    {
        $firstName = 'Анастасия';
        $lastName = 'Скакунова';
        $patronymic = 'Ивановна';

        $query = $this->db->placehold(
            'SELECT o.id, o.date, u.firstname, u.lastname, u.patronymic
                FROM s_orders o
                INNER JOIN s_users u ON u.id = o.user_id 
                WHERE firstname = ? AND lastname = ? AND patronymic = ?
                ORDER BY id DESC', $firstName, $lastName, $patronymic
        );

        $this->db->query($query);
        $result = $this->db->results();

        echo '<pre>';
        print_r($result);
        die();
    }
}

$test = new Log();
$test->log();
