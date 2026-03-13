<?php

namespace chats\viber;

use Simpla;

class ViberSetDataUser {

    public static function getUserId($data) {
        if (isset($data['id'])) {
            return self::getUserIdInMessangerByIdInCRM($data['id']);
        } elseif (isset($data['phone'])) {
            return self::getUserIdInMessangerByUserPhoneInCRM($data['phone']);
        } elseif (isset($data['chatId'])) {
            return self::getUserIdInMessangerByChatIdInCRM($data['chatId']);
        }
    }

    /**
     * Получить id пользователя в мессенджере по id в CRM
     */
    private static function getUserIdInMessangerByIdInCRM($id) {
        $simplaObj = new Simpla();
        $query = $simplaObj->db->placehold("
            SELECT 
                userIdInMessanger 
            FROM 
                __verify_messangers
            WHERE 
                user_id = '" . $id . "'
            AND 
                typeMessanger = 'viber'
        ");
        $simplaObj->db->query($query);
        return $simplaObj->db->result('userIdInMessanger');
    }

    /**
     * Получить информацию о пользователе по id в CRM
     */
    public static function getUserInfoByIdInCRM($id) {
        $simplaObj = new Simpla();
        $query = $simplaObj->db->placehold("
            SELECT 
                * 
            FROM 
                __verify_messangers
            WHERE 
                user_id = '" . $id . "'
            AND 
                typeMessanger = 'viber'
        ");
        $simplaObj->db->query($query);
        return $simplaObj->db->result();
    }

    /**
     * Получить id пользователя в мессенджере по номеру телефона в CRM
     */
    private static function getUserIdInMessangerByUserPhoneInCRM($phone) {
        $simplaObj = new Simpla();
        $query = $simplaObj->db->placehold("
            SELECT 
                userIdInMessanger 
            FROM 
                __verify_messangers
            WHERE 
                phone = '" . $phone . "'
            AND 
                typeMessanger = 'viber'
        ");
        $simplaObj->db->query($query);
        return $simplaObj->db->result('userIdInMessanger');
    }

    /**
     * Получить id пользователя в мессенджере по id чата в CRM
     */
    private static function getUserIdInMessangerByChatIdInCRM($chatId) {
        $simplaObj = new Simpla();
        $query = $simplaObj->db->placehold("
            SELECT 
                userIdInMessanger 
            FROM 
                __verify_messangers
            WHERE 
                chatId = '" . $chatId . "'
            AND 
                typeMessanger = 'viber'
        ");
        $this->db->query($query);
        return $this->db->result('userIdInMessanger');
    }

}
