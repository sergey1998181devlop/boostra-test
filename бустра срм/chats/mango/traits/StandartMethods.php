<?php

namespace chats\mango\traits;

use stdClass;
use chats\mango\MangoCurl AS Curl;

trait StandartMethods {

    /**
     * Получить текущий режим ч/б списка
     */
    public function getBwListStatus() {
        return $this->requestPost('vpbx/bwlists/state/');
    }

    /**
     * Получить список номеров в ч/б списке
     */
    public function getBwListNumbers() {
        return $this->requestPost('vpbx/bwlists/numbers/');
    }

    /**
     * отправка пост запроса с пустым телом объекта
     */
    private function requestPost($url) {
        $obj = new stdClass();
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получить набор пользовательских полей
     */
    public function getCustomFilds() {
        return $this->requestPost('vpbx/ab/custom_fields/');
    }

    /**
     * Получение баланса
     */
    public function getBalance() {
        return $this->requestPost('vpbx/account/balance');
    }

    /**
     * Получение списка номеров ВАТС
     */
    public function getIncominglines() {
        return $this->requestPost('vpbx/incominglines');
    }

    /**
     * Получение списка мелодий и звуковых сообщений
     */
    public function getAudiofiles() {
        return $this->requestPost('vpbx/audiofiles');
    }

    /**
     * Получить список ролей
     */
    public function getRoles() {
        return $this->requestPost('vpbx/roles');
    }

    /**
     * Получить sip учетные записи сотрудников
     */
    public function getSips() {
        return $this->requestPost('vpbx/sips');
    }

    /**
     * Получить настроенные домены
     */
    public function getDomains() {
        return $this->requestPost('vpbx/domains');
    }

    /**
     * Запрос номеров sip-trunk'ов
     */
    public function getTrunksNumbers() {
        return $this->requestPost('vpbx/trunks/numbers');
    }

    /**
     * Получить текущий режим ч/б списка
     */
    public function getBwlistsStatus() {
        return $this->requestPost('vpbx/bwlists/state/');
    }

    /**
     * Получить список номеров в ч/б списке
     */
    public function getBwlistsNumbers() {
        return $this->requestPost('vpbx/bwlists/numbers/');
    }

    /**
     * Получение списка кампаний ИО (созданных вручную оператором
     * Контакт Центра или при помощи соответствующего запроса к API)
     */
    public function getCampaignList() {
        return $this->requestPost('vpbx/campaign/list');
    }

    /**
     * Получение списка тематик по продукту
     * @return type
     */
    public function getTags() {
        return $this->requestPost('vpbx/tags');
    }

}
