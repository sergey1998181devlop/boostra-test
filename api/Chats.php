<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Chats
 *
 * @author alexey
 */
class Chats extends Simpla
{

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public function insertMessage($data)
    {
        if (!empty(trim($data['text']))) {
            $query = $this->db->placehold("
                INSERT INTO
                    __chats
                SET
                    ?%
            ", $data);
            $this->db->query($query);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @return void
     */
    public function newCodeGenerate($phone)
    {
        $code = rand(1000, 9999);
        $_SESSION['sms'] = $code;
        $query = $this->db->placehold("
            INSERT INTO
                __authcodes
            SET
                code = '" . $code . "',
                phone = '" . $phone . "',
                created = '" . date('Y-m-d H:i:s') . "'
        ");
        $this->db->query($query);
        $msg = 'Ваш код для входа в ЛК на boostra.ru: ' . $code;
        return $msg;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function lastMessageForWhatsApp()
    {
        $query = $this->db->placehold("SELECT update_id FROM __chats WHERE chat_type = 'whatsapp' ORDER BY id DESC LIMIT 1");
        $this->db->query($query);
        return $this->db->result('update_id');
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $text
     * @return void
     */
    public function sendSms($phone, $text)
    {
        $convert_msg = iconv('utf8', 'cp1251', (string) $text);
        $result = $this->notify->send_sms($phone, $convert_msg);
        $this->sms->add_message(array(
            'phone' => $phone,
            'message' => $text,
            'send_id' => $result,
            'created' => date('Y-m-d H:i:s', time()),
        ));
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $code
     * @return void
     */
    public function checkSmsCode($phone, $code)
    {
        $time = time() - 5 * 60;
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __sms_messages
            WHERE
                phone = '" . $this->curl->preparePhone($phone) . "'
                AND message LIKE '%" . $code . "%'
                AND created > '".date("Y-m-d H:i:s", $time)."'
        ");
        $this->db->query($query);
        $res = $this->db->result();
        if ($res) {
            return true;
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public function goodVerifyMessanger($data)
    {
        $query = $this->db->placehold("
            INSERT INTO
                __verify_messangers
            SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $typeMessanger
     * @return object
     */
    public function verifyPhone($phone, $typeMessanger)
    {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __verify_messangers
            WHERE
                phone = '" . $phone . "'
                AND typeMessanger = '" . $typeMessanger . "'
        ");
        $this->db->query($query);
        $result = $this->db->result();
        if ($result) {
            $this->setUserIdByPhone($phone, $typeMessanger);
            return $result;
        }
        return false;
    }

    public function getUserInfoByPhone($phone, $messangerType)
    {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __verify_messangers
            WHERE
                phone = '" . $phone . "'
                AND typeMessanger = '" . $messangerType . "'
        ");
        $this->db->query($query);
        $result = $this->db->result();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $phone
     * @param [type] $messangerType
     * @return void
     */
    public function setUserIdByPhone($phone, $messangerType)
    {
        $userInfo = $this->getUserInfoByPhone($phone, $messangerType);
        if ($userInfo) {
            $query = $this->db->placehold("
                UPDATE
                    __chats
                SET
                    user_id = '" . $userInfo->user_id . "',
                    phone = '" . $userInfo->phone . "'
                WHERE
                    user_id_in_chat = '" . $userInfo->userIdInMessanger . "'
            ");
            $this->db->query($query);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $messangerId
     * @return object
     */
    public function getUserInfoByMessangerId($messangerId)
    {
        $query = $this->db->placehold("
            SELECT
                *
            FROM
                __verify_messangers
            WHERE
                userIdInMessanger = '" . $messangerId . "'
        ");
        $this->db->query($query);
        $result = $this->db->result();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $messangerId
     * @return void
     */
    public function setUserIdByMessangerId($messangerId)
    {
        $userInfo = $this->getUserInfoByMessangerId($messangerId);
        if ($userInfo) {
            $query = $this->db->placehold("
                UPDATE
                    __chats
                SET
                    user_id = '" . $userInfo->user_id . "',
                    phone = '" . $userInfo->phone . "'
                WHERE
                    user_id_in_chat = '" . $userInfo->userIdInMessanger . "'
            ");
            $this->db->query($query);
        }
    }
}
