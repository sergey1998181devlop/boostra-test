<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;
use stdClass;

class MangoGroups extends MangoAccount {

    /**
     * Получить список групп
     */
    public function getGroups($data) {
        $url = 'vpbx/groups';
        $obj = new stdClass();
        if (isset($data['groupId'])) {
            /*
             * если указано, то возвращается информаци о данной группе
             */
            $obj->group_id = $data['groupId'];
        }
        if (isset($data['operatorId'])) {
            /*
             * id сотрудника (необязательный). Если указан, то возвращается только 
             * список групп, куда включен сотрудник. Иначе - возвращаются все группы. Получить
             * значение operator_id можно запросом списка сотрудников, в ответе на который
             * возвращается параметр general.user_id
             */
            $obj->operator_id = $data['operatorId'];
        }
        if (isset($data['operatorExtension'])) {
            /*
             * нутренний номер сотрудника (необязательный). Если указан, то
             * возвращается только список групп, куда включен сотрудник.
             * Иначе - возвращаются все группы
             */
            $obj->operator_extension = $data['operatorExtension'];
        }
        if (isset($data['show_users'])) {
            /*
             * признак, выводить ли в ответет сотрудников в группах/группе.
             * Если указан (0 - нет / 1 - да), то в ответе возвращается вместе со
             * списком сотрудников. Иначе - только список групп
             */
            $obj->show_users = $data['show_users'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Добавить новую группу
     */
    public function createGroup($data) {
        $url = 'vpbx/group/create';
        $obj = (object) [
                    # имя группы
                    'name' => (string) $data['name'],
                    # примечание к группе
                    'description' => (string) $data['description'],
                    # короткий номер группы
                    'extension' => (int) $data['extension'],
                    /*
                     * Алгоритм распределения звонков в группе
                     * 
                     * 0 : ALG_SERIAL_PRIOR - Последовательный обзвон
                     * 1 : ALG_PARALLEL_PRIOR - Параллельный по приоритету (по квалификации)
                     * 2 : ALG_PARALLEL - Одновременно всем свободным
                     * 3 : ALG_RANDOM - Судя из названия, в случайном порядке
                     * 5 : ALG_MOST_IDLE - Равномерный (наиболее свободному)
                     */
                    'dial_alg_group' => (int) $data['dialAlgGroup'],
                    /*
                     * Алгоритм дозвона до сотрудников в группе
                     * 
                     * 1 : ALG_M_ALL - На все контакты сотрудника одновременно
                     * 2 : ALG_M_MAIN - На основные номера сотрудников
                     * 3 : ALG_M_SIP - Только на SIP-учетные записи сотрудника
                     * 4 : ALG_M_LINE - На все контакты сотрудника по-очереди
                     * 5 : ALG_M_CARD - Как настроено в карточке сотрудника
                     */
                    'dial_alg_users' => (int) $data['dialAlgUsers'],
                    /*
                     * Переадресовывать звонки на "знакомого" сотрудника
                     * 
                     * 0 - Нет
                     * 1 - Да
                     */
                    'auto_redirect' => (bool) $data['autoRedirect'],
                    /*
                     * статус опции "Автоматически перезванивать по пропущенным звонкам"
                     */
                    'auto_dial' => $data['auto_dial'],
                    /*
                     * id исходящей линии для автоперезвона
                     */
                    'line_id' => $data['lineId'],
                    /*
                     * статус опции "До ответа оператора осталось ... минут"
                     */
                    'use_dynamic_ivr' => (bool) $data['useDynamicIvr'],
                    /*
                     * статус опции "Ваш номер в очереди ..."
                     */
                    'use_dynamic_seq_num' => (bool) $data['useDynamicSeqNum'],
                    /*
                     * идентификатор выбранной мелодии во время ожидания ответа
                     */
                    'melody_id' => $data['melodyId'],
        ];
        if (isset($data['operators'])) {
            for ($i = 0; $i < count($data['operators']); $i++) {
                /*
                 * массив сотрудников в группе
                 */
                $obj->operators[$i] = [
                    'id' => $data['operators'][$i]['id'],
                    'priority' => $data['operators'][$i]['priority']
                ];
            }
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать группу
     */
    public function editGroup($data) {
        $url = 'vpbx/group/update';
        $obj = (object) [
                    # id группы для редактирования
                    'group_id' => $data['groupId'],
                    # имя группы
                    'name' => (string) $data['name'],
                    # примечание к группе
                    'description' => (string) $data['description'],
                    # короткий номер группы
                    'extension' => (int) $data['extension'],
                    /*
                     * Алгоритм распределения звонков в группе
                     * 
                     * 0 : ALG_SERIAL_PRIOR - Последовательный обзвон
                     * 1 : ALG_PARALLEL_PRIOR - Параллельный по приоритету (по квалификации)
                     * 2 : ALG_PARALLEL - Одновременно всем свободным
                     * 3 : ALG_RANDOM - Судя из названия, в случайном порядке
                     * 5 : ALG_MOST_IDLE - Равномерный (наиболее свободному)
                     */
                    'dial_alg_group' => (int) $data['dialAlgGroup'],
                    /*
                     * Алгоритм дозвона до сотрудников в группе
                     * 
                     * 1 : ALG_M_ALL - На все контакты сотрудника одновременно
                     * 2 : ALG_M_MAIN - На основные номера сотрудников
                     * 3 : ALG_M_SIP - Только на SIP-учетные записи сотрудника
                     * 4 : ALG_M_LINE - На все контакты сотрудника по-очереди
                     * 5 : ALG_M_CARD - Как настроено в карточке сотрудника
                     */
                    'dial_alg_users' => (int) $data['dialAlgUsers'],
                    /*
                     * Переадресовывать звонки на "знакомого" сотрудника
                     * 
                     * 0 - Нет
                     * 1 - Да
                     */
                    'auto_redirect' => (bool) $data['autoRedirect'],
                    /*
                     * статус опции "Автоматически перезванивать по пропущенным звонкам"
                     */
                    'auto_dial' => $data['auto_dial'],
                    /*
                     * id исходящей линии для автоперезвона
                     */
                    'line_id' => $data['lineId'],
                    /*
                     * статус опции "До ответа оператора осталось ... минут"
                     */
                    'use_dynamic_ivr' => (bool) $data['useDynamicIvr'],
                    /*
                     * статус опции "Ваш номер в очереди ..."
                     */
                    'use_dynamic_seq_num' => (bool) $data['useDynamicSeqNum'],
                    /*
                     * идентификатор выбранной мелодии во время ожидания ответа
                     */
                    'melody_id' => $data['melodyId'],
        ];
        if (isset($data['operators'])) {
            for ($i = 0; $i < count($data['operators']); $i++) {
                /*
                 * массив сотрудников в группе
                 */
                $obj->operators[$i] = [
                    'id' => $data['operators'][$i]['id'],
                    'priority' => $data['operators'][$i]['priority']
                ];
            }
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удалить группу
     */
    public function removeGroup($data) {
        $url = 'vpbx/group/delete';
        $obj = (object) [
                    'group_id' => $data['groupId']
        ];
        return Curl::sendPost($url, $obj);
    }

}
