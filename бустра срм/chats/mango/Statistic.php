<?php

namespace chats\mango;

use chats\mango\MangoAccount;
use chats\mango\MangoCurl AS Curl;
use stdClass;

class Statistic extends MangoAccount {

    /**
     * Запуск формирования статистики
     * В ответе на запрос приходит ключ, с помощью которого можно будет получить статистику 
     * по завершению ее построения
     */
    public function startStats($data) {
        $url = 'vpbx/stats/request';
        $obj = (object) [
                    /*
                     * предоставить статистику с указанного времени. Формат данных — timestamp 
                     * (Unix время, часовой пояс UTC+3), даёт возможность указать время с точностью до одной 
                     * секунды (обязательное поле)
                     */
                    'date_from' => (int) $data['dateFrom'],
                    /*
                     * предоставить статистику по указанное время. Формат идентичен date_from (обязательное поле)
                     */
                    'date_to' => (int) $date['dateTo']
        ];
        if (isset($data['fields'])) {
            /*
             * Позволяет указать какие поля и в каком порядке необходимо включить в выгрузку. 
             * Значение по умолчанию: "records, start, finish, answer, from_extension, 
             * from_number, to_extension, to_number, disconnect_reason, line_number, 
             * location, create, entry_id"
             */
            $obj->fields = (string) $data['fields'];
        }
        if (isset($data['fromNumber'])) {
            /*
             * данные, относящиеся строго к вызывающему абоненту
             * номер вызывающего абонента (строка) (для PSTN номеров в формате E164)
             */
            $obj->from['number'] = (string) $data['fromNumber'];
        }
        if (isset($data['fromExtension'])) {
            /*
             * данные, относящиеся строго к вызывающему абоненту
             * идентификатор сотрудника ВАТС для вызывающего абонента
             */
            $obj->from['extension'] = (string) $data['fromExtension'];
        }
        if (isset($data['toExtension'])) {
            /*
             * данные, относящиеся строго к вызываемому абоненту
             * идентификатор сотрудника ВАТС для вызываемого абонента
             */
            $obj->to['extension'] = (string) $data['toExtension'];
        }
        if (isset($data['toNumber'])) {
            /*
             * данные, относящиеся строго к вызываемому абоненту
             * номер вызываемого абонента (строка) (для PSTN номеров в формате E164)
             */
            $obj->to['number'] = (string) $data['toNumber'];
        }
        if (isset($data['callPartyNumber'])) {
            /*
             * данные, относящиеся к вызываемому или вызывающему абоненту. 
             * Использование поля допустимо только без заполнения полей to и from
             * номер абонента (строка) (для PSTN номеров в формате E164)
             */
            $obj->call_party['number'] = $data['callPartyNumber'];
        }
        if (isset($data['callPartyExtension'])) {
            /*
             * данные, относящиеся к вызываемому или вызывающему абоненту. 
             * Использование поля допустимо только без заполнения полей to и from
             * идентификатор сотрудника ВАТС
             */
            $obj->call_party['extension'] = $data['callPartyExtension'];
        }
        if (isset($data['requestId'])) {
            /*
             * идентификатор запроса (строка не более 128 байт), опциональное поле. 
             * Формируется внешней системой. ВАТС никак не обрабатывает этот идентификатор, не
             * анализирует и не полагается на уникальность его значения. Идентификатор можно
             * использовать для связи запроса с результатом его выполнения и возможными
             * последующими событиями, которые появляются в результате обработки запроса
             */
            $obj->request_id = (string) $data['requestId'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получение статистики вызовов
     * Сформированные данные возвращаются в формате CSV.
     */
    public function getStats($data) {
        $url = 'vpbx/result/stats';
        $obj = (object) [
                    /*
                     * ключ, созданный при обработке запроса от внешней системы 
                     * на получение статистики
                     */
                    'key' => $data['key'],
                    /*
                     * значение request_id, полученное от внешней системы при обработке запроса
                     * на построение статистики
                     */
                    'request_id' => $data['requestId']
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Запуск формирования статистики вызовов
     * В ответе на запрос приходит ключ, с помощью которого можно будет получить статистику 
     * по завершению ее построения
     */
    public function startStatsCalls($data) {
        $url = 'vpbx/stats/calls/request/';
        $obj = (object) [
                    /*
                     * дата/время начала выборки, строка совместимая с форматом
                     * класса DateTime
                     */
                    'start_date' => (string) $data['startDate'],
                    /*
                     * дата/время окончания выборки, строка совместимая с форматом
                     * класса DateTime
                     */
                    'end_date' => (string) $data['endDate'],
                    /*
                     * лимит выборки, целочисленное 
                     * (допустимые значения - 1,5,10,20,50,100,500,1000,2000,5000)
                     */
                    'limit' => (int) $data['limit'],
                    /*
                     * смещение начала выборки, целочисленное
                     */
                    'offset' => $data['offset']
        ];
        if (isset($data['userIds'])) {
            /*
             * идентификаторы сотрудников участвовавших в звонке, 
             * опциональный, массив целочисленных значений
             */
            foreach ($data['userIds'] as $userId) {
                $obj->user_ids[] = (int) $userId;
            }
        }
        if (isset($data['groupIds'])) {
            /*
             * идентификаторы групп участвовавших в звонке,
             * опциональный, массив целочисленных значений
             */
            foreach ($data['groupIds'] as $group) {
                $obj->group_ids[] = (int) $group;
            }
        }
        if (isset($data['contextType'])) {
            /*
             * cтатус звонка: 
             * 1 – входящий;
             * 2 – исходящий;
             * 3 – внутренний
             */
            foreach ($data['contextType'] as $type) {
                $obj->context_type[] = (int) $type;
            }
        }
        if (isset($data['contextStatus'])) {
            /*
             * признак успешности звонка: 1 – успешный, 0 – неуспешный
             */
            $obj->context_status = (int) $data['contextStatus'];
        }
        if (isset($data['recallStatus'])) {
            /*
             * признак успешности перезвона для входящих: 
             * 0 - неуспешный перезвон
             * 1 - успешный перезвон
             * 2 - нет перезвона
             */
            $obj->recall_status = (int) $data['recallStatus'];
        }
        if (isset($data['search'])) {
            /*
             * поисковая строка (минимум 3 символа, фильтрует по
             * вхождениям в номерах внешних/внутренних)
             */
            $obj->search_string = (string) $data['search'];
        }
        if (isset($data['extParams'])) {
            /*
             * получить данные КЦ: 0 - нет, 1 - да, получить
             */
            $obj->ext_params = (int) $data['extParams'];
        }
        if (isset($data['contextCostFull'])) {
            /*
             * стоимость по всему звонку
             */
            $obj->ext_fields['context_cost_full'] = (string) $data['contextCostFull'];
        }
        if (isset($data['contextCostTariff'])) {
            /*
             * стоимость без услуг по звонку
             */
            $obj->ext_fields['context_cost_tariff'] = (string) $data['contextCostTariff'];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получение статистики вызовов
     * Результат (расширенная статистика)
     */
    public function getStatsCalls($data) {
        $url = 'vpbx/stats/calls/result/';
        $obj = (object) [
                    /*
                     * ключ, созданный при обработке запроса от внешней системы на получение статистики
                     */
                    'key' => (string) $data['key']
        ];
        return Curl::sendPost($url, $obj);
    }

}
