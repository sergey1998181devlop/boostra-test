<?php

namespace chats\mango;

use chats\mango\MangoCurl AS Curl;
use chats\mango\MangoAccount;
use stdClass;

class MangoOrganizations extends MangoAccount {

    /**
     * Инициация отчета получения списка организаций
     */
    public function initListOrganization($data) {
        $url = 'vpbx/ab/organizations/init';
        if (!isset($data['query'])) {
            $data['query'] = '';
        }
        $obj = new stdClass();
        /*
         * строка поиска, допустима передача пустой строки
         * для возврата списка всех организаций
         */
        $obj->query = (string) $data['query'];
        if (isset($data['limitRows'])) {
            /*
             * кол-во выбираемых строк (по умолчанию 10, ограничение 500)
             */
            $obj->limit_rows = (int) $data['limitRows'];
        }
        if (isset($data['order'])) {
            /*
             * правило сортировки; порядок следования объектов в массиве
             * определяет порядок сортировки
             */
            foreach ($data['order'] as $key => $value) {
                $obj->order[(string) $key] = (string) $value;
            }
        }
        return Curl::sendPost($value, $obj);
    }

    /**
     * Получить список организаций, постраничное получение
     */
    public function getListOrganization($data) {
        $url = 'vpbx/ab/organizations/cursor';
        $obj = new stdClass();
        /*
         * зашифрованный объект поискового курсора
         */
        $obj->cursor = $data['cursor'];
        if (isset($data['mode'])) {
            $key = array_keys($data['mode']);
            $values = array_values($data['mode']);
            /*
             * режим работы поискового курсора. Для навигации по 
             * результатам поиска используются один из следующих режимов:
             * first-page - отобразить первую страницу результатов поиска
             * last-page - отобразить последнюю страницу результатов поиска
             * current-page - позволяет обновить текущую страницу результатов поиска
             * next-page-{N} - отобразить следующую {N}-ую страницу, относительно
             * текущей страницы результатов поиска, переменная {N} принимает значения
             * в диапазоне от 1 до 9
             * prev-page-{N} - отобразить предыдущую {N}-ую страницу, относительно
             * текущей страницы результатов поиска, переменная {N} принимает значения
             * в диапазоне от 1 до 9
             */
            $obj->mode[$key[0]] = $values[0];
        }
        return Curl::sendPost($url, $obj);
    }

    /**
     * Добавить организацию
     */
    public function createOrganization($data) {
        $url = 'vpbx/ab/organizations/create';
        $obj = (object) [
                    'data' => (array) [
                        /*
                         * название организации
                         */
                        'org_name' => $data['orgName']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать организацию
     */
    public function editOrganization($data) {
        $url = 'vpbx/ab/organizations/update';
        $obj = new stdClass();
        /*
         * id организации
         */
        $obj->data['org_id'] = $data['orgId'];
        /*
         * название организации
         */
        $obj->data['org_name'] = $data['orgName'];
    }

    /**
     * Удалить организацию
     */
    public function removeOrganization($data) {
        $url = 'vpbx/ab/organizations/delete';
        $obj = new stdClass();
        /*
         * id организации
         */
        $obj->data['org_id'] = $data['orgId'];
        return Curl::sendPost($url, $obj);
    }

}
