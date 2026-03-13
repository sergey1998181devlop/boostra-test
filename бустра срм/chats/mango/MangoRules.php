<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;
use stdClass;

class MangoRules extends MangoAccount {

    /**
     * Получить список индивидуальных правил автосекретаря для сотрудника
     */
    public function getAutosecretaryRules($data) {
        $url = 'autosecretary/rules';
        $obj = (object) [
                    /*
                     * ID сотрудника
                     */
                    'user_id' => $data['userid']
        ];
        if (isset($data['rules'])) {
            /*
             * правила автосекретаря сотрудника
             */
            foreach ($data['rules'] as $rule) {
                $obj->rules[] = $rule;
            }
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Изменить статус индивидуальных правил автосекретаря сотрудника
     */
    public function changeStatusRule($data) {
        $url = 'autosecretary/status/change';
        $obj = new stdClass();
        /*
         * ID сотрудника
         */
        $obj->user_id = (int) $data['userId'];
        /*
         * ID правила автосекретаря сотрудника
         */
        $obj->rule_id = (string) $data['ruleId'];
        /*
         * статус правила автосекретаря (0 - выключен, 1 - включен)
         */
        $obj->active = (bool) $data['active'];
        return Curl::sendPost($url, $obj);
    }

}
