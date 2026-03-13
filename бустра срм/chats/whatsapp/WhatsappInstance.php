<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappCurl AS Curl;
use chats\whatsapp\WhatsappSetDataInstance AS Data;

class WhatsappInstance {

    /**
     * Получить статус аккаунта и QR код для авторизации.
     * 
     * @param bool $info Получить полную информацию о текущем статусе аккаунта
     * @param bool $no_wakeup Игнорировать автопробуждение аккаунта
     * @return string(json_encode) 
     */
    public function status($data) {
        if (isset($data['info'])) {
            $obj['full'] = false;
        } else {
            $obj['full'] = true;
        }
        if (isset($data['no_wakeup'])) {
            $obj['no_wakeup'] = true;
        } else {
            $obj['no_wakeup'] = false;
        }
        return Curl::sendGet($data, 'status');
    }

    /**
     * Сброс очереди отправки сообщений
     */
    public function clearMessagesQueue() {
        return Curl::sendPost((object) [], 'clearMessagesQueue');
    }

    /**
     * Прямая ссылка на QR-код в виде изображения, а не base64.
     * 
     * @return string(json_encode) 
     */
    public function qr_code() {
        return Curl::sendGet([], 'qr_code');
    }

    /**
     * Получить настройки аккаунта
     * 
     * @return type 
     */
    public function getSettings() {
        return Curl::sendGet([], 'settings');
    }

    /**
     * Получить ip-адрес инстанса
     * 
     * @return type
     */
    public function outputIP() {
        return Curl::sendGet([], 'outputIP');
    }

    /**
     * Получить информацию об авторизованном пользователе
     * 
     * @return type
     */
    public function me() {
        return Curl::sendGet([], 'me');
    }

    /**
     * Возвращает список ярлыков (лэйблов)
     * 
     * @return type
     */
    public function labelsList() {
        return Curl::sendGet([], 'labelsList');
    }

    /**
     * Выйти из аккаунта и запросить новый QR-код.
     * 
     * @return type
     */
    public function logout() {
        $data = new \stdClass();
        return Curl::sendPost($data, 'logout');
    }

    /**
     * Возвращает активную сессию, если к устройству подключили другой экземпляр Web Whatsapp.
     * 
     * @return type
     */
    public function takeover() {
        $data = new \stdClass();
        return Curl::sendPost($data, 'takeover');
    }

    /**
     * Обновить QR-код после истечения срока его действия
     * 
     * @return type
     */
    public function expiry() {
        $data = new \stdClass();
        return Curl::sendPost($data, 'takeover');
    }

    /**
     * Повторить попытку синхронизации с устройством не дожидаясь новой попытки.
     * 
     * @return type
     */
    public function retry() {
        $data = new \stdClass();
        return Curl::sendPost($data, 'retry');
    }

    /**
     * Перезагрузить Ваш аккаунт Whatsapp.
     * 
     * @return type
     */
    public function reboot() {
        $data = new \stdClass();
        return Curl::sendPost($data, 'reboot');
    }

    /**
     * Создает ярлык (лэйбл). Только Whatsapp Business
     * 
     * @param string $name
     * @return type
     */
    public function createLabel(string $name) {
        $data = Data::setDataInstance('createLabel', '', $name);
        return Curl::sendPost($data, 'createLabel');
    }

    public function updateLabel(string $labelId, array $data) {
        $name = false;
        $color = false;
        if (isset($data['name'])) {
            $name = $data['name'];
        }
        if (isset($data['color'])) {
            $color = $data['color'];
        }
        $obj = Data::setDataInstance('updateLabel', '', $name, $color, $labelId);
        return Curl::sendPost($obj, 'updateLabel');
    }

    public function removeLabel($labelId) {
        $data = Data::setDataInstance('removeLabel', '', '', '', $labelId);
        return Curl::sendPost($data, 'removeLabel');
    }

}
