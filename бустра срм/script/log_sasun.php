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

    public function printLogsByFioAndBirth()
    {
        $firstName = 'Илья';
        $lastName = 'Земсков';
        $patronymic = 'Ильич';

        $query = $this->db->placehold(
            'SELECT u.id, u.firstname, u.lastname, u.patronymic, u.birth, u.passport_serial, u.Regcity, u.Regstreet
                FROM s_users u
                WHERE firstname = ? AND lastname = ? AND patronymic = ?', $firstName, $lastName, $patronymic
        );

        $this->db->query($query);
        $result = $this->db->results();

        print_r($result);
        die();
    }
}

$test = new Log();
$test->printLogsByFioAndBirth();
