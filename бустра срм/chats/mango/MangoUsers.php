<?php

namespace chats\mango;

use chats\mango\MangoCurl AS Curl;
use chats\mango\MangoAccount;
use stdClass;

class MangoUsers extends MangoAccount {

    /**
     * Запрос списка сотрудников ВАТС
     */
    public function getUsers($data) {
        $url = 'vpbx/config/users/request';
        $obj = new stdClass();
        if (isset($data['extension'])) {
            /*
             * идентификатор сотрудника ВАТС, настройки которого запрашиваются.
             * Для получения полного списка сотрудников параметр не передается.
             */
            $obj->extension = $data['extension'];
        }
        if (isset($data['userId'])) {
            /*
             * id сотрудника
             */
            $obj->ext_fields['general']->user_id = $data['userId'];
        }
        if (isset($data['sips'])) {
            foreach ($data['sips'] as $sip) {
                /*
                 * массив SIP-учеток сотрудника
                 */
                $obj->ext_fields['general']->sips[] = $sip;
            }
        }
        if (isset($data['groups'])) {
            /*
             * группы в которых состоит сотрудник (id-номер группы)
             */
            $obj->ext_fields['groups'] = $data['groups'];
        }
        if (isset($data['accessRoleId'])) {
            /*
             * id-номер роли сотрудника
             */
            $obj->ext_fields['access_role_id'] = $data['accessRoleId'];
        }
        if (isset($data['dialAlg'])) {
            /*
             * алгоритм дозвона
             */
            $obj->ext_fields['telephony']->dial_alg = $data['dialAlg'];
        }
        if (isset($data['schedule'])) {
            /*
             * расписание в формате аналогичного запроса в общей шине
             */
            $obj->ext_fields['numbers']->schedule = $data['schedule'];
        }
        if (isset($data['lineId'])) {
            /*
             * исходящий номер (значение, id линии)
             */
            $obj->ext_fields['telephony']->line_id = $data['lineId'];
        }
        if (isset($data['trunkNumberId'])) {
            /*
             * id номера sip-trunk'a исходящего номера
             */
            $obj->ext_fields['telephony']->trunk_number_id = $data['trunkNumberId'];
        }
        if (isset($data['mobile'])) {
            /*
             * мобильный телефон
             */
            $obj->ext_fields['general']->mobile = (string) $data['mobile'];
        }
        if (isset($data['login'])) {
            /*
             * логин
             */
            $obj->ext_fields['general']->login = (string) $data['login'];
        }
        if (isset($data['useStatus'])) {
            /*
             * учитывать статус сотрудника в Контакт-центре при распределении вызовов на него
             */
            $obj->ext_fields['general']->use_status = $data['useStatus'];
        }
        if (isset($data['useCcNumbers'])) {
            /*
             * принимать вызовы на номер(а) выбранные в Контакт-центре
             */
            $obj->ext_fields['general']->use_cc_numbers = $data['useCcNumbers'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Создать сотрудника
     */
    public function createUser($data) {
        $url = 'vpbx/member/create';
        $obj = new stdClass();
        /*
         * [обязательное] ФИО сотрудника
         */
        $obj->name = (string) $data['name'];
        /*
         * id роли сотрудника
         */
        $obj->access_role_id = $data['accessRoleId'];
        /*
         * внутренний номер сотрудника
         */
        $obj->extension = $data['extension'];
        if (isset($data['login']) AND isset($data['password'])) {
            /*
             * логин
             */
            $obj->login = (string) $data['login'];
            /*
             * пароль
             */
            $obj->password = (string) $data['password'];
        }
        if (isset($data['email'])) {
            /*
             * адрес электронной почты
             */
            $obj->email = (string) $data['email'];
        }
        if (isset($data['mobile'])) {
            /*
             * мобильный телефон
             */
            $obj->mobile = $data['mobile'];
        }
        if (isset($data['department'])) {
            /*
             * отдел
             */
            $obj->department = $data['department'];
        }
        if (isset($data['useStatus'])) {
            /*
             * учитывать статус сотрудника в Контакт-центре при распределении 
             * вызовов на него
             */
            $obj->use_status = $data['useStatus'];
        }
        if (isset($data['useCcNumbers'])) {
            /*
             * принимать вызовы на номер(а) выбранные в Контакт-центре
             */
            $obj->use_cc_numbers = $data['useCcNumbers'];
        }
        if (isset($data['lineId'])) {
            /*
             * исходящий номер (значение, id линии, можно использовать все линии,
             * кроме линий с region = "sip")
             */
            $obj->line_id = $data['lineId'];
        }
        if (isset($data['trunkNumberId'])) {
            /*
             * id номера sip-trunk'a исходящего (у номера поле options 
             * должно быть 4 или 6) номера SIP-TRUNK
             */
            $obj->trunk_number_id = (int) $data['trunkNumberId'];
        }
        if (isset($data['dialAlg'])) {
            /*
             * алгоритм дозвона
             */
            $obj->dial_alg = (int) $data['dialAlg'];
        }
        if (isset($data['number'])) {
            /*
             * зависит от protocol: PSTN-номер, sip-номер, FMC-номер
             */
            $obj->numbers['number'] = (string) $data['number'];
        }
        if (isset($data['protocol'])) {
            /*
             * протокол номера телефона, возможные значения:
             * tel – PSTN номер
             * sip – sip-номер
             * fmc – FMC номер
             */
            $obj->numbers['protocol'] = (string) $data['protocol'];
        }
        if (isset($data['waitSec'])) {
            /*
             * время ожидания ответа, специальное значение 0 – действуют 
             * общие ограничение платформы или оператора связи
             */
            $obj->numbers['wait_sec'] = $data['waitSec'];
        }
        if (isset($data['status'])) {
            /*
             * статус номера, возможные значения: on – активен, off – выключен
             */
            $obj->numbers['status'] = $data['status'];
        }
        if (isset($data['scheduleFrom'])) {
            /*
             * дата начала, "2019-05-23 12:50:25" (UTC)
             */
            $obj->numbers['schedule']->from = (string) $data['scheduleFrom'];
        }
        if (isset($data['scheduleUntil'])) {
            /*
             * дата окончания, "2019-05-23 17:25:45" (UTC)
             */
            $obj->numbers['schedule']->until = $data['scheduleUntil'];
        }
        if (isset($data['scheduleItemsType'])) {
            /*
             * варианты дней ['AllDays', 'WorkingDays', 'Holidays', 
             * 'SpecificDate', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
             * 'Saturday', 'Sunday']
             */
            $obj->numbers['schedule']->items->type = $data['scheduleType'];
        }
        if (isset($data['scheduleItemsSpecificDate'])) {
            /*
             * дата, "2019-05-23 14:25:45" (UTC), если type = SpecificDate
             */
            $obj->numbers['schedule']->items->specific_date = $data['scheduleItemsSpecificDate'];
        }
        if (isset($data['scheduleItemsFrom'])) {
            /*
             * время начала (по московскому времени), формат: "12:25"
             */
            $obj->numbers['schedule']->items->from = $data['scheduleItemsFrom'];
        }
        if (isset($data['scheduleItemsUntil'])) {
            /*
             * время окончания (по московскому времени), формат: "18:25"
             */
            $obj->numbers['schedule']->items->until = $data['scheduleItemsUntil'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать сотрудника
     */
    public function editUser($data) {
        $url = 'vpbx/member/update';
        $obj = new stdClass();

        /*
         * ID сотрудника
         */
        $obj->user_id = (int) $data['userId'];

        if (isset($data['name'])) {
            /*
             * [обязательное] ФИО сотрудника
             */
            $obj->name = (string) $data['name'];
        }
        if (isset($data['accessRoleId'])) {
            /*
             * id роли сотрудника
             */
            $obj->access_role_id = $data['accessRoleId'];
        }
        if (isset($data['extension'])) {
            /*
             * внутренний номер сотрудника
             */
            $obj->extension = $data['extension'];
        }
        if (isset($data['login']) AND isset($data['password'])) {
            /*
             * логин
             */
            $obj->login = (string) $data['login'];
            /*
             * пароль
             */
            $obj->password = (string) $data['password'];
        }
        if (isset($data['email'])) {
            /*
             * адрес электронной почты
             */
            $obj->email = (string) $data['email'];
        }
        if (isset($data['mobile'])) {
            /*
             * мобильный телефон
             */
            $obj->mobile = $data['mobile'];
        }
        if (isset($data['department'])) {
            /*
             * отдел
             */
            $obj->department = $data['department'];
        }
        if (isset($data['useStatus'])) {
            /*
             * учитывать статус сотрудника в Контакт-центре при распределении 
             * вызовов на него
             */
            $obj->use_status = $data['useStatus'];
        }
        if (isset($data['useCcNumbers'])) {
            /*
             * принимать вызовы на номер(а) выбранные в Контакт-центре
             */
            $obj->use_cc_numbers = $data['useCcNumbers'];
        }
        if (isset($data['lineId'])) {
            /*
             * исходящий номер (значение, id линии, можно использовать все линии,
             * кроме линий с region = "sip")
             */
            $obj->line_id = $data['lineId'];
        }
        if (isset($data['trunkNumberId'])) {
            /*
             * id номера sip-trunk'a исходящего (у номера поле options 
             * должно быть 4 или 6) номера SIP-TRUNK
             */
            $obj->trunk_number_id = (int) $data['trunkNumberId'];
        }
        if (isset($data['dialAlg'])) {
            /*
             * алгоритм дозвона
             */
            $obj->dial_alg = (int) $data['dialAlg'];
        }
        if (isset($data['number'])) {
            /*
             * зависит от protocol: PSTN-номер, sip-номер, FMC-номер
             */
            $obj->numbers['number'] = (string) $data['number'];
        }
        if (isset($data['protocol'])) {
            /*
             * протокол номера телефона, возможные значения:
             * tel – PSTN номер
             * sip – sip-номер
             * fmc – FMC номер
             */
            $obj->numbers['protocol'] = (string) $data['protocol'];
        }
        if (isset($data['waitSec'])) {
            /*
             * время ожидания ответа, специальное значение 0 – действуют 
             * общие ограничение платформы или оператора связи
             */
            $obj->numbers['wait_sec'] = $data['waitSec'];
        }
        if (isset($data['status'])) {
            /*
             * статус номера, возможные значения: on – активен, off – выключен
             */
            $obj->numbers['status'] = $data['status'];
        }
        if (isset($data['scheduleFrom'])) {
            /*
             * дата начала, "2019-05-23 12:50:25" (UTC)
             */
            $obj->numbers['schedule']->from = (string) $data['scheduleFrom'];
        }
        if (isset($data['scheduleUntil'])) {
            /*
             * дата окончания, "2019-05-23 17:25:45" (UTC)
             */
            $obj->numbers['schedule']->until = $data['scheduleUntil'];
        }
        if (isset($data['scheduleItemsType'])) {
            /*
             * варианты дней ['AllDays', 'WorkingDays', 'Holidays', 
             * 'SpecificDate', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
             * 'Saturday', 'Sunday']
             */
            $obj->numbers['schedule']->items->type = $data['scheduleType'];
        }
        if (isset($data['scheduleItemsSpecificDate'])) {
            /*
             * дата, "2019-05-23 14:25:45" (UTC), если type = SpecificDate
             */
            $obj->numbers['schedule']->items->specific_date = $data['scheduleItemsSpecificDate'];
        }
        if (isset($data['scheduleItemsFrom'])) {
            /*
             * время начала (по московскому времени), формат: "12:25"
             */
            $obj->numbers['schedule']->items->from = $data['scheduleItemsFrom'];
        }
        if (isset($data['scheduleItemsUntil'])) {
            /*
             * время окончания (по московскому времени), формат: "18:25"
             */
            $obj->numbers['schedule']->items->until = $data['scheduleItemsUntil'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удалить сотрудника
     */
    public function removeUser($data) {
        $url = 'vpbx/member/delete';
        $obj = (object) [
                    'user_id' => (int) $data['userId']
        ];
        return Curl::sendPost($url, $obj);
    }

}
