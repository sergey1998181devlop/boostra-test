<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappSetDataMessage as DataMessage;
use chats\whatsapp\WhatsappCurl AS Curl;
use chats\main\Users;
use chats\whatsapp\WhatsappAddMessageInDb AS AddMessage;
use chats\main\Main;
use chats\whatsapp\WhatsappSettings AS Settings;
use Simpla;

class WhatsappMessages extends Simpla {

    public function __construct() {
        parent::__construct();
        if (!is_dir(Settings::whatsAppUploadDir))
            mkdir(Settings::whatsAppUploadDir);
        if (!is_dir(Settings::whatsAppUploadAudio))
            mkdir(Settings::whatsAppUploadAudio);
        if (!is_dir(Settings::whatsAppUploadVideo))
            mkdir(Settings::whatsAppUploadVideo);
        if (!is_dir(Settings::whatsAppUploadVoice))
            mkdir(Settings::whatsAppUploadVoice);
        if (!is_dir(Settings::whatsAppUploadDocument))
            mkdir(Settings::whatsAppUploadDocument);
        if (!is_dir(Settings::whatsAppUploadImage))
            mkdir(Settings::whatsAppUploadImage);
        if (!is_dir(Settings::whatsAppUploadCallLog))
            mkdir(Settings::whatsAppUploadCallLog);
    }

    /**
     * Отправка текстового сообщения
     *
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $text Строка текста сообщения
     * @param string $quotedMsgId Строка идентификатор сообщения, на которое отвечают
     * @param array $mentionedPhones Массив номеров контактов для упомянания в сообщении
     * @return string(json_encode)  Строка json с результатом выполнения запроса
     */
    public function sendText(array $data) {
        $phone = false;
        $chatId = false;
        $text = false;
        $quotedMsgId = false;
        $mentionedPhones = [];
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        } else {
            $data['id'] = false;
        }
        Main::readAllMessages($data);
        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if (isset($data['text']))
            $text = $data['text'];
        if (isset($data['quotedMsgId']))
            $quotedMsgId = $data['quotedMsgId'];
        if (isset($data['mentionedPhones'])) {
            $mPs = explode(',', $data['mentionedPhones']);
            foreach ($mPs as $mP) {
                $mentionedPhones[] = Users::preparePhone($mP);
            }
        }
        return self::sendDataText($phone, $chatId, $text, $quotedMsgId, $mentionedPhones);
    }

    /**
     * Отправка файла
     *
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $body Ссылка на файл или файл в base64
     * @param string $filename Имя отправляемого файла
     * @param string $caption Текст под файлом
     * @param string $quotedMsgId Идентификатор цитируемого сообщения
     * @param boolean $cached Попытаться отправить загруженный ранее файл вместо загрузки при каждом запросе
     * @return void
     */
    public function sendFile(array $data) {

        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);

        $phone = false;
        $chatId = false;
        $text = false;
        $quotedMsgId = false;
        $caption = false;
        $cached = true;
        $filename = false;
        if ($data['phone'])
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if ($data['text'])
            $text = $data['text'];
        if (isset($data['quotedMsgId']))
            $quotedMsgId = $data['quotedMsgId'];
        if (isset($data['caption']))
            $caption = $data['caption'];
        if (isset($data['filename']))
            $filename = $data['filename'];
        return self::sendDataFile($text, $filename, $chatId, $phone, $quotedMsgId, $caption, $cached);
    }

    /**
     *
     * @param string $audio Ссылка на аудиофайл ogg в кодеке opus или base64 ogg-файл в кодеке opus
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $quotedMsgId Идентификатор цитируемого сообщения из списка (ответ на определенное сообщение)
     * @return void
     */
    public function sendPTT(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $quotedMsgId = false;
        $audio = false;
        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if (isset($data['text']))
            $audio = $data['text'];
        if (isset($data['audio']))
            $quotedMsgId = $data['audio'];
        return self::sendDataPtt($audio, $phone, $chatId, $quotedMsgId);
    }

    /**
     * @param string $body HTTP или HTTPS ссылка
     * @param string $previewBase64 Изображение в кодировке Base64 для превью ссылки
     * @param string $title Заголовок отправляемой ссылки
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $description Описание отправляемой ссылки
     * @param string $text Содержащий ссылку текст (ВНИМАНИЕ Должен содержать указанную в "body" ссылку для коректной работы.)
     * @param string $quotedMsgId Идентификатор цитируемого сообщения
     * @param array $mentionedPhones
     * @return void
     */
    public function sendLink(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $body = false;
        $previewBase64 = false;
        $title = false;
        $text = false;
        $description = false;
        $quotedMsgId = false;
        $mentionedPhones = [];

        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if (isset($data['body']))
            $body = $data['body'];
        if (isset($data['preview']))
            $previewBase64 = base64_encode(file_get_contents($data['preview']));
        if (isset($data['title']))
            $title = $data['title'];
        if (isset($data['text']))
            $text = $data['text'];
        if (isset($data['description']))
            $description = $data['description'];
        if (isset($data['quotedMsgId']))
            $quotedMsgId = $data['quotedMsgId'];
        if (isset($data['mentionedPhones'])) {
            $mPs = explode(',', $data['mentionedPhones']);
            foreach ($mPs as $mP) {
                $mentionedPhones[] = Users::preparePhone($mP);
            }
        }
        return self::sendDataLink($phone, $chatId, $body, $previewBase64, $title, $text, $description, $quotedMsgId, $mentionedPhones);
    }

    /**
     *
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param array $contactId Массив ID контактов в мессенджере
     * @return void
     */
    public function sendContact(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $contactId = [];
        if (isset($data['phone'])) {
            $phone = $data['phone'];
        }
        if (isset($data['chatId'])) {
            $chatId = $data['chatId'];
        }
        if (isset($data['contacts'])) {
            $contacts = explode(',', $data['contacts']);
            foreach ($contacts as $contact) {
                $contactId[] = $contact;
            }
        }
        return self::sendDataContact($phone, $chatId, $contactId);
    }

    /**
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param integer $lat Широта
     * @param integer $lng Долгота
     * @param string $address Текст под сообщением с локацией. Поддерживает две строки. Чтобы использовать две строки, используйте символ "\n".
     * @return void
     */
    public function sendLocation(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $lng = false;
        $lat = false;
        $address = false;
        $phone = false;
        $chatId = false;

        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if (isset($data['lng']))
            $lng = (float) $data['lng'];
        if (isset($data['lat']))
            $lat = (float) $data['lat'];
        if (isset($data['address']))
            $address = $data['address'];

        return self::sendDataLocation($lng, $lat, $address, $phone, $chatId);
    }

    /**
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $vcard Текстовое содержимое vcard 3.0
     * @return void
     */
    public function sendVCard(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $vcard = false;

        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        if (isset($data['vcard']))
            $vcard = $data['vcard'];

        return self::sendDataVcard($phone, $chatId, $vcard);
    }

    /**
     * Пересылка сообщения в новый или существующий чат.
     *
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $messageId Массив ID сообщений
     * @return void
     */
    public function forwardMessage(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $messageId = false;
        if ($data['phone']) {
            $phone = $data['phone'];
        }
        if ($data['messageId']) {
            $messageId = $data['messageId'];
        }
        if ($data['chatId']) {
            $chatId = $data['chatId'];
        }
        $objData = DataMessage::setDataMessage('forward', $phone, '', $chatId, '', [], '', '', false, '', '', '', '', '', [], '', '', '', '', [], '', $messageId);
        $response = Curl::sendPost($objData, 'forwardMessage');
        if ($response) {
            
        }
        return $response;
    }

    /**
     * Отправка товара в новый или существующий чат.
     *
     * @param array $data Массив с информацией о пользователе (номер телефона | идентификатор чата)
     * @param string $productId Ид товара
     * @param string $filename Имя отправляемого файла
     * @param string $body Ссылка на файл или файл в base64
     * @return void
     */
    public function sendProduct(array $data) {
        if (isset($data['id'])) {
            $res = Main::getUserInfoById($data['id']);
            $data['phone'] = $res->phone_mobile;
            $data['id'] = $res->id;
        }
        Main::readAllMessages($data);
        $phone = false;
        $chatId = false;
        $productId = false;
        $filename = false;
        $body = false;
        if (isset($data['phone']))
            $phone = $data['phone'];
        if (isset($data['filename']))
            $filename = $data['filename'];
        if (isset($data['productId']))
            $productId = $data['productId'];
        if (isset($data['body']))
            $body = $data['body'];
        if (isset($data['chatId']))
            $chatId = $data['chatId'];
        $dataObj = DataMessage::setDataMessage('product', $phone, $body, $chatId, '', [], $filename, '', false, '', '', '', '', '', [], 0, 0, '', '', [], $productId);
        $response = Curl::sendPost($dataObj, 'sendProduct');
        if ($response) {
            
        }
        return $response;
    }

    /**
     * Удаляет сообщение из Whatsapp
     *
     * @param string $messageId Идентификатор из списка сообщений
     * @return void
     */
    public function deleteMessage(string $messageId) {
        $data = new \stdClass();
        $data->messageId = $messageId;
        $response = Curl::sendPost($data, 'deleteMessage');
        if ($response) {
            
        }
        return $response;
    }

    /**
     * Получить список сообщений отсортированных по времени в порядке убывания.
     * 
     * @param int $page Номер страницы, начиная с 0. По умолчанию - 0.
     * @param int $count Количество сообщений на странице результатов, по умолчанию - 100.
     * @param string $chatId Фильтровать сообщения по chatId . Идентификатор чата
     * @return type
     */
    public function messagesHistory(int $page = 0, int $count = 100, string $chatId = '') {
        $data = [];
        if ($page) {
            $data['page'] = $page;
        }
        if ($count) {
            $data['count'] = $count;
        }
        if ($chatId) {
            $data['chatId'] = $chatId;
        }
        $response = Curl::sendPost($data, 'messages');
        if ($response) {
            
        }
        return $response;
    }

    /**
     * Получить список сообщений.
     * 
     * Для получения только новых сообщений передайте параметр lastMessageNumber из последнего запроса.
     * Файлы из сообщений гарантированно хранятся лишь 30 дней и могут быть удалены. 
     * Скачивайте файлы сразу при получении на свой сервер.
     * 
     * @param int $lastMessageNumber Номер сообщения после которого нужно получить новые сообщения
     * @param int $last Отображает последние 100 сообщений. Если передан этот параметр, то lastMessageNumber игнорируется.
     * @param string $chatId Идентификатор чата
     * @param int $limit Устанавливает длину списка сообщений. По умолчанию 100. При значении 0 вернет все сообщения.
     * @param int $min_time Фильтрует сообщения, полученные после указанного времени. (метка времени time())
     * @param int $max_time Фильтрует сообщения, полученные до указанного времени. (метка времени time())
     */
    public function messages(int $lastMessageNumber = 0, bool $last = false, string $chatId = '', int $limit = 100, int $min_time = 0, int $max_time = 0) {
        $data = new \stdClass;
        if ($lastMessageNumber) {
            $data->lastMessageNumber = $lastMessageNumber;
        }
        if ($last) {
            $data->last = $last;
        }
        if ($chatId) {
            $data->chatId = $chatId;
        }
        if ($limit) {
            $data->limit = $limit;
        }
        if ($max_time) {
            $data->max_time = $max_time;
        }
        if ($min_time) {
            $data->min_time = $min_time;
        }
        $response = Curl::sendPost($data, 'messages');
        if ($response) {
            
        }
        return $response;
    }

    private static function sendDataText($phone, $chatId, $text, $quotedMsgId, $mentionedPhones) {
        if ($phone OR $chatId) {
            if ($text) {
                $data = DataMessage::setDataMessage('text', $phone, $text, $chatId, $quotedMsgId, $mentionedPhones);
                $response = Curl::sendPost($data, 'sendMessage');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан текст сообщения'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан ни один из обязательных параметр phone или chatId'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataFile($text, $filename, $chatId, $phone, $quotedMsgId, $caption, $cached) {
        if ($text AND $filename) {
            if ($chatId OR $phone) {
                $data = DataMessage::setDataMessage('file', $phone, $text, $chatId, $quotedMsgId, [], $filename, $caption, $cached);
                $response = Curl::sendPost($data, 'sendFile');
                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан ни один из обязательных параметр phone или chatId'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан ни один из необходимых параметров text или filename'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataPtt($audio, $phone, $chatId, $quotedMsgId) {
        if ($audio) {
            if ($phone OR $chatId) {
                $data = DataMessage::setDataMessage('audio', $phone, '', $chatId, $quotedMsgId, [], '', '', false, $audio);
                $response = Curl::sendPost($data, 'sendPTT');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан ни один из необходимых параметров text или filename'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан обязательный параметр audio'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataLink($phone, $chatId, $body, $previewBase64, $title, $text, $description, $quotedMsgId, $mentionedPhones) {
        if ($body AND $previewBase64 AND $title) {
            if ($phone OR $chatId) {
                $data = DataMessage::setDataMessage(
                                'link', $phone, $body,
                                $chatId, $quotedMsgId, $mentionedPhones,
                                '', '', false,
                                '', $previewBase64, $title,
                                $description, $text
                );
                $response = Curl::sendPost($data, 'sendLink');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан ни один из необходимых параметров text или filename'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан один из обязательных параметров title, body или preview'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataContact($phone, $chatId, $contactId) {
        if (count($contactId) > 0) {
            if ($phone OR $chatId) {
                $data = DataMessage::setDataMessage('contact', $phone, '', $chatId, '', [], '', '', '', '', '', '', '', '', $contactId);
                $response = Curl::sendPost($data, 'sendContact');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан один из необходимых параметров phone или chatId'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан необходимый параметр contacts'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataLocation($lng, $lat, $address, $phone, $chatId) {
        if ($lat AND $lng AND $address) {
            if ($phone OR $chatId) {
                $data = DataMessage::setDataMessage('location', $phone, '', $chatId, '', [], '', '', '', '', '', '', '', '', [], $lat, $lng, $address);
                $response = Curl::sendPost($data, 'sendLocation');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан один из необходимых параметров phone или chatId'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан один из необходимых параметров lat, lng или address'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    private static function sendDataVcard($phone, $chatId, $vcard) {
        if ($vcard) {
            if ($phone OR $chatId) {
                $data = DataMessage::setDataMessage('vcard', $phone, '', $chatId, '', [], '', '', '', '', '', '', '', '', [], '', '', '', $vcard);
                $response = Curl::sendPost($data, 'sendVCard');

                return $response;
            } else {
                return (object) ['ResponseCode' => '200', 'Data' => (object) [
                                'error' => 'Не передан один из необходимых параметров phone или chatId'
                            ], 'Header' => Curl::$headers[200]];
            }
        } else {
            return (object) ['ResponseCode' => '200', 'Data' => (object) [
                            'error' => 'Не передан обязательный параметр vcard'
                        ], 'Header' => Curl::$headers[200]];
        }
    }

    public function getLastMessageNumber() {
        $query = $this->db->placehold(
                "
                    SELECT 
                        update_id 
                    FROM 
                        __chats 
                    WHERE 
                        chat_type = 'whatsapp' 
                    ORDER BY 
                        id DESC 
                    LIMIT 1
                ");
        $this->db->query($query);
        return $this->db->result('update_id');
    }

}
