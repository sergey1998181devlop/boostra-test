<?php

namespace chats\whatsapp;

use \stdClass;
use chats\main\Users;

class WhatsappSetDataMessage {

    /**
     * Установка тела сообщения
     *
     * @param integer $phone Номер телефона получателя
     * @param string $body Текст сообщения
     * @param string $chatId Идентификатор чата
     * @param string $quotedMsgId Идентификатор цитируемого сообщения из списка (для ответа на определенное сообщение)
     * @param array|string $mentionedPhones Телефонные номера упомянутых контактов в массиве или перечисленные через запятую
     * @return object
     */
    public static function setDataMessage(string $type, $phone = 0, string $body = '',
            string $chatId = '', string $quotedMsgId = '', array $mentionedPhones = [],
            string $filename = '', string $caption = '', bool $cached = true,
            string $audio = '', string $previewBase64 = '', string $title = '',
            string $description = '', string $text = '', array $contactId = [],
            string $lat = '', string $lng = '', string $address = '', string $vcard = '',
            array $messageId = [], string $productId = ''
    ) {
        if ($type === 'text') {
            return self::setDataText($body, $phone, $chatId, $quotedMsgId, $mentionedPhones);
        } elseif ($type === 'file') {
            return self::setDataFile($body, $filename, $phone, $chatId, $quotedMsgId, $caption, $cached);
        } elseif ($type === 'audio') {
            return self::setDataPTT($audio, $phone, $chatId, $quotedMsgId);
        } elseif ($type === 'link') {
            return self::setDataLink($body, $previewBase64, $title, $chatId, $phone, $description, $text, $quotedMsgId, $mentionedPhones);
        } elseif ($type === 'contact') {
            return self::setDataContact($contactId, $phone, $chatId);
        } elseif ($type === 'location') {
            return self::setDataLocation($lat, $lng, $address, $phone, $chatId);
        } elseif ($type === 'vcard') {
            return self::setDataVcard($vcard, $phone, $chatId);
        } elseif ($type === 'forward') {
            return self::setDataForward($messageId, $phone, $chatId);
        } elseif ($type === 'product') {
            return self::setDataProduct($productId, $filename, $body, $chatId, $phone);
        }
    }

    /**
     *
     * @param string $productId Ид товара
     * @param string $filename Имя отправляемого файла
     * @param string $body Ссылка на файл или файл в base64
     * @param string $chatId ID чата из списка
     * @param integer $phone Номер телефона
     * @return void
     */
    private static function setDataProduct(string $productId, string $filename, string $body = '', string $chatId = '', $phone = 0) {
        $data = new \stdClass();
        $data->phone = (integer) Users::preparePhone($phone);
        $data->chatId = $chatId;
        $data->body = $body;
        $data->productId = $productId;
        $data->filename = $filename;
        return $data;
    }

    /**
     * Пересылка сообщения в новый или существующий чат.
     * 
     * @param array $messageId Массив ID сообщений
     * @param integer $phone Номер телефона
     * @param string $chatId ID чата из списка
     * @return void
     */
    private static function setDataForward(array $messageId, $phone, string $chatId) {
        $data = new \stdClass();
        $data->phone = (integer) Users::preparePhone($phone);
        $data->chatId = $chatId;
        $data->messageId = $messageId;
        return $data;
    }

    /**
     * @param string $vcard Текстовое содержимое vcard 3.0
     * @param integer $phone Номер телефона
     * @param string $chatId ID чата из списка
     * @return void
     */
    private static function setDataVcard(string $vcard, $phone, string $chatId) {
        $data = new \stdClass();
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        $data->vcard = file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'chats' . DIRECTORY_SEPARATOR . 'vcards' . DIRECTORY_SEPARATOR . $vcard);
        return $data;
    }

    /**
     * @param integer $lat Широта
     * @param integer $lng Долгота
     * @param string $address Текст под сообщением с локацией. Поддерживает две строки. Чтобы использовать две строки, используйте символ "\n".
     * @param integer $phone Номер телефона
     * @param string $chatId ID чата из списка
     * @return void
     */
    private static function setDataLocation($lat, $lng, string $address, $phone = 0, string $chatId = '') {
        $data = new \stdClass();
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        $data->lat = (float) $lat;
        $data->lng = (float) $lng;
        $data->address = (string) $address;
        return $data;
    }

    /**
     * @param array $contactId Массив ID контактов из месенджеров
     * @param integer $phone Номер телефона
     * @param string $chatId ID чата из списка
     * @return void
     */
    private static function setDataContact(array $contactId, string $phone = '', string $chatId = '') {
        $data = new \stdClass();
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        if ($contactId) {
            foreach ($contactId as $value) {
                if (!empty($value)) {
                    $contacts = $value;
                }
            }
        }
        $data->contactId = $contacts;
        return $data;
    }

    /**
     *
     * @param string $body Текст сообщения
     * @param integer $phone Номер получателя
     * @param string $chatId Id чата
     * @param string $quotedMsgId Идентификатор цитируемого сообщения из списка (ответ на определенное сообщение)
     * @param array|string $mentionedPhones Телефонные номера упомянутых контактов в массиве или перечисленные через запятую
     * @return object
     */
    private static function setDataText(string $body, $phone = 0, string $chatId = '', string $quotedMsgId = '', array $mentionedPhones = []) {

        echo $body;
        $data = new \stdClass();
        $data->body = $body;
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        if ($quotedMsgId)
            $data->quotedMsgId = $quotedMsgId;
        if (count($mentionedPhones) > 0)
            $data->mentionedPhones = $mentionedPhones;
        return $data;
    }

    /**
     *
     * @param string $body Ссылка на файл
     * @param string $filename Имя отправляемого файла
     * @param boolean $phone Номер получателя
     * @param boolean $chatId Id чата
     * @param string $quotedMsgId Идентификатор цитируемого сообщения из списка (ответ на определенное сообщение)
     * @param string $caption Текст под файлом
     * @param boolean $cached Попытаться отправить загруженный ранее файл вместо загрузки при каждом запросе
     * @return object
     */
    private static function setDataFile(string $body, string $filename, $phone = 0, string $chatId = '', string $quotedMsgId = '', string $caption = '', bool $cached = true) {
        $data = new \stdClass();
        $data->body = $body;
        $data->filename = $filename;
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        if ($cached)
            $data->cached = $cached;
        if (!empty($caption))
            $data->caption = $caption;
        if ($quotedMsgId)
            $data->quotedMsgId = $quotedMsgId;
        return $data;
    }

    /**
     *
     * @param string $audio Ссылка на аудиофайл ogg в кодеке opus или base64 ogg-файл в кодеке opus
     * @param boolean $phone Номер получателя
     * @param string $chatId Id чата
     * @param string $quotedMsgId Идентификатор цитируемого сообщения из списка (ответ на определенное сообщение)
     * @return object
     */
    private static function setDataPTT(string $audio, $phone = 0, string $chatId = '', string $quotedMsgId = '') {
        $data = new \stdClass();
        $data->audio = $audio;
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        if ($quotedMsgId)
            $data->quotedMsgId = $quotedMsgId;
        return $data;
    }

    /**
     * @param string $body HTTP или HTTPS ссылка
     * @param string $previewBase64 Изображение в кодировке Base64 для превью ссылки
     * @param string $title Заголовок отправляемой ссылки
     * @param boolean $chatId ID чата из списка (Обязателен если не указан phone)
     * @param boolean $phone Номер телефона (Обязателен если не указан chatId)
     * @param string $description Описание отправляемой ссылки
     * @param string $text Содержащий ссылку текст (ВНИМАНИЕ Должен содержать указанную в "body" ссылку для коректной работы.)
     * @param string $quotedMsgId Идентификатор цитируемого сообщения
     * @param array $mentionedPhones
     * @return object
     */
    private static function setDataLink(
            string $body, string $previewBase64, string $title,
            string $chatId = '', $phone = 0, string $description = '',
            string $text = '', string $quotedMsgId = '', array $mentionedPhones = []
    ) {
        $data = new \stdClass();
        $data->body = $body;
        $data->previewBase64 = $previewBase64;
        $data->title = $title;
        if ($phone)
            $data->phone = (integer) Users::preparePhone($phone);
        if ($chatId)
            $data->chatId = $chatId;
        if ($description)
            $data->description = $description;
        if ($text)
            $data->text = $text . ' ' . $body;
        if ($quotedMsgId)
            $data->quotedMsgId = $quotedMsgId;
        if (count($mentionedPhones) > 0)
            $data->mentionedPhones = $mentionedPhones;
        return $data;
    }

}
