<?php

namespace chats\main;

use Simpla;
use chats\main\UploadFile AS Upload;
use chats\mango\traits\questions;
use chats\main\Users;

class Main extends Simpla {

    use questions;

    public static $statuses = [
        /**
          1 => 'Отправлено',
          2 => 'Доставлено',
          3 => 'Прочитано',
         */
        'delivered' => 1,
        'viewed' => 2
    ];

    /**
     * Генерируем код авторизации и сохраняем его в базе
     * 
     * @param type $phone
     * @return string Строка для отправки в мессенджере
     */
    public static function loginCodeGenerate($phone) {
        $code = rand(1000, 9999);
        $_SESSION['sms'] = $code;
        $query = $this->db->placehold("
            INSERT INTO __authcodes
            SET code = '" . $code . "',
                phone = '" . $phone . "',
                created = '" . date('Y-m-d H:i:s') . "'
        ");
        $this->db->query($query);
        $msg = 'Ваш код для входа в ЛК на ' . strtolower($_SERVER['SERVER_NAME']) . ': ' . $code;
        return $msg;
    }

    /**
     * Проверка номера телефона в базе месенджеров
     */
    public static function verifyPhone($phone, $typeMessanger) {
        $query = $this->db->placehold("
            SELECT *
            FROM __verify_messangers
            WHERE phone = '" . $phone . "'
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

    /**
     * Получить информацию о пользователе по id в месенджере
     */
    public static function getUserInfoByMessangerId($messangerId) {
        $query = $this->db->placehold("
            SELECT *
            FROM __verify_messangers
            WHERE userIdInMessanger = '" . $messangerId . "'
        ");
        $this->db->query($query);
        $result = $this->db->result();
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Пролучаем новое сообщение и сохраняем его в базе
     * 
     * @param type $data
     * @return boolean
     */
    public static function getMessage($data) {
        $simplaObj = new Simpla;
        $query = $simplaObj->db->placehold("
            INSERT INTO __chats
            SET ?%
        ", (array) $data);
        if ($simplaObj->db->query($query)) {
            $id = $simplaObj->db->insert_id();
            self::updateStatusMessagesByDate($data, $id);
            return $id;
        }
        return false;
    }

    public static function updateStatusMessagesByDate($data, $id) {
        $simplaObj = new Simpla;
        $query = $simplaObj->db->placehold("
                UPDATE __chats 
                SET message_status = 2 
                WHERE id < " . (int) $id . "
                AND chat_type = '" . $data['chat_type'] . "'
                AND user_id = '" . $data['user_id'] . "'
                AND status = '1'
            ");
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'query.log', $query);
        $simplaObj->db->query($query);
    }

    /**
     * Обновляем стату сообщения
     * 
     * @param type $data
     * @return boolean
     */
    public static function getUpdatesStatusMessage($data) {
        $obj = new Simpla();
        if (self::getMessageByMessageId($data['message_id'])) {
            $query = $obj->db->placehold("
                UPDATE  __chats
                SET  message_status = '" . $data['message_status'] . "'
                WHERE message_id = '" . $data['message_id'] . "'
                AND chat_type = '" . $data['chat_type'] . "'
            ");
            $obj->db->query($query);
            return true;
        }
        return false;
    }

    /**
     * Получаем сообщение из базы по его id в мессенджере
     * 
     * @param type $messageId
     * @return boolean
     */
    public static function getMessageByMessageId($messageId) {
        $obj = new Simpla();
        $query = $obj->db->placehold("
            SELECT *
            FROM __chats
            WHERE message_id = '" . $messageId . "'
        ");
        if ($obj->db->query($query)) {
            return $obj->db->result();
        }
        return false;
    }

    /**
     * Отпралляем смс на номер
     * 
     * @param type $phone
     * @param type $text
     */
    public static function sendSms($phone, $text) {
        $obj = new Simpla();
        $convert_msg = iconv('utf8', 'cp1251', (string) $text);
        $result = $obj->notify->send_sms($phone, $convert_msg);
        $this->sms->add_message(array(
            'phone' => $phone,
            'message' => $text,
            'send_id' => $result,
            'created' => date('Y-m-d H:i:s', time()),
        ));
    }

    /**
     * Проверка привязки месенджера
     */
    public static function goodVerifyMessanger($data) {
        $obj = new Simpla;
        $query = $obj->db->placehold("
            INSERT INTO __verify_messangers
            SET ?%", $data);
        $obj->db->query($query);
        return $obj->db->insert_id();
    }

    /**
     * Добавление нового сообщения в базу
     */
    public static function insertMessage($data) {
        $obj = new Simpla();
        $query = $obj->db->placehold("
                INSERT INTO __chats
                SET ?%", $data);
        $obj->db->query($query);
    }

    /**
     * Проверка сообщения для подтверждения месенджера
     */
    public static function checkSmsCode($phone, $code) {
        $time = time() - 5 * 60;
        $obj = new Simpla;
        $query = $obj->db->placehold("
            SELECT *
            FROM __sms_messages
            WHERE phone = '" . $this->curl->preparePhone($phone) . "'
            AND message LIKE '%" . $code . "%'
            AND created > '" . date("Y-m-d H:i:s", $time) . "'
        ");
        $obj->db->query($query);
        $res = $obj->db->result();
        if ($res) {
            return true;
        }
        return false;
    }

    public static function uploadFile($link, $dir) {
        $match = false;
        $stringContent = file_get_contents($link);
        preg_match('/(?<mimeFile>\w{3,5})$/iu', $link, $match);
        if (isset($match['mimeFile'])) {
            $fileName = md5($stringContent) . '.' . $match['mimeFile'];
            $file = $dir . $fileName;
            file_put_contents($file, $stringContent);
            return protocol . baseUrl . str_replace(ROOT, '', $dir) . $fileName;
        }
        return false;
    }

    public function getMessages($data) {
        if (!isset($data['page']))
            $data['page'] = 20;
        if (isset($data['id'])) {
            $query = $this->db->placehold("
                SELECT *
                FROM __chats
                WHERE user_id = '" . $data['id'] . "'
                ORDER BY date DESC
                LIMIT " . (int) $data['page'] . "
            ");
            if ($this->db->query($query)) {
                $result = $this->db->results();
                return ['newMessages' => self::getNewMessages($data['id']), 'cache' => md5(json_encode($result)), 'ResponseCode' => 200, 'user_info' => self::getUserInfoById($data['id']), 'Data' => $result, 'Header' => Curl::$headers[200]];
            }
        }
        return ['ResponseCode' => 200, 'Data' => false, 'Header' => Curl::$headers[200]];
    }

    private static function getNewMessages($userId) {
        $simplaObj = new Simpla;
        $query = $simplaObj->db->placehold("
                SELECT *
                FROM __chats
                WHERE user_id = '" . $userId . "'
                AND message_status = '0'
                AND status = '2'
            ");
        if ($simplaObj->db->query($query)) {
            return $simplaObj->db->results();
        }
        return false;
    }

    public function readTheMessage($data) {
        if (isset($data['id'])) {
            $query = $this->db->placehold("
                UPDATE __chats
                SET message_status = '2'
                WHERE id = '" . $data['id'] . "'
            ");
            $this->db->query($query);
        }
    }

    public static function readAllMessages($data) {
        $userInfo = Users::getUserInfoByPhone(Users::preparePhone($data['phone']));
        $simplaObj = new Simpla;
        $query = $simplaObj->db->placehold("
                UPDATE __chats
                SET message_status = '2'
                WHERE user_id = '" . $userInfo->id . "'
                AND status = 2
                OR phone = '" . Users::preparePhone($data['phone']) . "'
                AND status = 2
            ");
        $simplaObj->db->query($query);
    }

    public static function getUserInfoById($idUser) {
        $simplaObj = new Simpla;

        $cacheKey = 'chats:user_info:' . (int)$idUser;

        return $simplaObj->caches->wrap($cacheKey, 300, function () use ($simplaObj, $idUser) {
            $query = $simplaObj->db->placehold(
                "SELECT * FROM __users WHERE id = ?",
                (int)$idUser
            );
            $simplaObj->db->query($query);
            return $simplaObj->db->result();
        });
    }

    public function getUserInfoByPhone($data) {
        echo json_encode((object) ['Data' => Users::getUserInfoByPhone($data['phone'])]);
        exit();
    }

    public function getLastCreditInfoByUserId($data) {
        echo json_encode((object) ['Data' => Users::getLastCreditInfoByUserId($data['userId'])]);
        exit();
    }

    public function uploadFiles($data) {
        if (!is_dir(uploadDir . 'sent')) {
            mkdir(uploadDir . 'sent');
        }
        if (isset($data['fileType'])) {
            if ($data['fileType'] === 'document') {
                $res = Upload::uploadDocument();
            } elseif ($data['fileType'] === 'video') {
                $res = Upload::uploadVideo();
            } elseif ($data['fileType'] === 'image') {
                $res = Upload::uploadImage();
            }
            echo json_encode((object) $res);
            exit();
        }
        echo json_encode((object) ['Data' => false, 'error' => 'Не указан тип файла']);
        exit();
    }

    public function setQuestionAndAnswers() {
        $questionsAndAnswers = $this->questionsAndAnswers();
        $this->setQuestions($questionsAndAnswers);
        echo true;
    }
    
    public function getUserCreditInfo($uid) {
        return Users::getLastCreditInfoByUserId($uid['uid_']);
    }

}
