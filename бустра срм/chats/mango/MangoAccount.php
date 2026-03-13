<?php

namespace chats\mango;

use chats\mango\MangoCurl AS Curl;
use chats\mango\traits\StandartMethods;
use chats\mango\traits\mangoCall;
use Simpla;
use stdClass;

class MangoAccount extends Simpla {

    use StandartMethods, mangoCall;

    public function returnJson($data) {
        echo json_encode((object) ['Data' => $data]);
        exit();
    }

    /**
     * Получить группу по id
     */
    public function getGroupById($data) {
        $url = 'vpbx/ab/group';
        $obj = new stdClass();
        /*
         * ID группы
         */
        $obj->group_id = $data['groupId'];
        return Curl::sendPost($url, $obj);
    }

    /**
     * инициация отчета для получения списка групп
     */
    public function initListGroups($data) {
        $url = 'vpbx/ab/groups/init';
        if (!isset($data['query'])) {
            $data['query'] = '';
        }
        $obj = new stdClass();
        /*
         * строка поиска, допустима передача пустой строки для возврата списка всех групп
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
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получить список групп, постраничное получение
     */
    public function getListGroups($data) {
        $url = 'vpbx/ab/groups/cursor';
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
     * Добавить группу
     */
    public function createAddresGroup($data) {
        $url = 'vpbx/ab/groups/create/';
        $obj = (object) [
                    'data' => (array) [
                        /*
                         * название группы
                         */
                        'group_name' => (string) $data['groupName']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать группу
     */
    public function editAddresGroup($data) {
        $url = 'vpbx/ab/groups/update';
        $obj = (object) [
                    'data' => (array) [
                        /*
                         * id группы
                         */
                        'group_id' => $data['groupId'],
                        /*
                         * название группы
                         */
                        'group_name' => (string) $data['groupName']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удалить группу
     */
    public function removeAddresGroup($data) {
        $url = '';
        $obj = (object) [
                    'data' => (array) [
                        /*
                         * id группы
                         */
                        'group_id' => $data['groupId']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получить контакт по id
     */
    public function getContactById($data) {
        $url = 'vpbx/ab/contact';
        if (!isset($data['contactExtFields'])) {
            $data['contactExtFields'] = true;
        }
        $obj = new stdClass();
        /*
         * ID контакта
         */
        $obj->contact_id = $data['contactId'];
        /*
         * признак необходимости возвращать значения пользовательских 
         * полей (custom_values) и поля идентификатор персонального сотрудника (user_id )
         */
        $obj->contact_ext_fields = (bool) $data['contactExtFields'];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получить список контактов, инициация отчета
     */
    public function initContactList($data) {
        $url = 'vpbx/ab/contact/init';
        if (!isset($data['contactExtFields'])) {
            $data['contactExtFields'] = true;
        }
        if (!isset($data['query'])) {
            $data['query'] = '';
        }
        $obj = new stdClass();
        /*
         * строка поиска, допустима передача пустой строки для возврата списка всех групп
         */
        $obj->query = (string) $data['query'];
        /*
         * признак необходимости возвращать значения пользовательских 
         * полей (custom_values) и поля идентификатор персонального сотрудника (user_id )
         */
        $obj->contact_ext_fields = (bool) $data['contactExtFields'];
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
        return Curl::sendPost($url, $obj);
    }

    /**
     * Получить список контактов, постраничное получение
     */
    public function getContactList($data) {
        $url = 'vpbx/ab/contact/cursor';
        if (!isset($data['contactExtFields'])) {
            $data['contactExtFields'] = true;
        }
        $obj = new stdClass();
        /*
         * зашифрованный объект поискового курсора
         */
        $obj->cursor = $data['cursor'];
        /*
         * признак необходимости возвращать значения пользовательских 
         * полей (custom_values) и поля идентификатор персонального сотрудника (user_id )
         */
        $obj->contact_ext_fields = (bool) $data['contactExtFields'];
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
     * Добавить контакт
     */
    public function createContact($data) {
        $url = 'vpbx/ab/contacts/create/';
        include __DIR__ . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'defaultValues.php';
        $obj = (object) [
                    'data' => (object) [
                        'type' => (int) $data['type'],
                        'name' => (string) $data['name'],
                        'office' => (string) $data['office'],
                        'site' => (string) $data['site'],
                        'org' => $data['org'],
                        'importance' => (int) $data['importance'],
                        'comment' => (string) $data['comment'],
                        'birthday' => (string) $data['birthday'],
                        'sex' => (int) $data['sex'],
                        'phones' => (object) [
                            'phone_id' => (string) $data['phoneId'],
                            'type' => (int) $data['phonesType'],
                            'phone' => (string) $data['phone'],
                            'comment' => (string) $data['phonesComment'],
                            'ext' => (string) $data['phonesExt'],
                            'is_default' => (bool) $data['phonesDefault']
                        ],
                        'groups' => (object) [
                            'group_id' => (string) $data['groupId'],
                            'group_name' => (string) $data['groupName']
                        ],
                        'nets' => (object) [
                            'net_id' => (string) $data['netId'],
                            'net' => (string) $data['net'],
                            'uname' => (string) $data['netUname']
                        ],
                        'messengers' => (object) [
                            'mgr_id' => (string) $data['mgrId'],
                            'mgr' => (int) $data['mgr'],
                            'uname' => (string) $data['mgrUname']
                        ],
                        'in_favorites' => (object) [
                            $data['inFavorit']
                        ],
                        'custom_values' => (object) [
                            'custom_value_id' => (int) $data['customValueId'],
                            'custom_field_id' => (int) $data['customFieldId'],
                            'type' => (int) $data['customType'],
                            'text' => (string) $data['customText'],
                            'list_items' => (object) [
                                'enum_id' => (int) $data['customListEnumId'],
                                'order' => (int) $data['customListOrder'],
                                'name' => (string) $data['customListName']
                            ],
                        ],
                        'on_error' => (string) $data['contactOnError']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Редактировать контакт
     */
    public function editContact($data) {
        $url = 'vpbx/ab/contacts/update';
        include __DIR__ . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'defaultValues.php';
        $obj = (object) [
                    'data' => (object) [
                        'type' => (int) $data['type'],
                        'contact_id' => $data['contactId'],
                        'name' => (string) $data['name'],
                        'office' => (string) $data['office'],
                        'site' => (string) $data['site'],
                        'org' => $data['org'],
                        'importance' => (int) $data['importance'],
                        'comment' => (string) $data['comment'],
                        'birthday' => (string) $data['birthday'],
                        'sex' => (int) $data['sex'],
                        'phones' => (object) [
                            'phone_id' => (string) $data['phoneId'],
                            'type' => (int) $data['phonesType'],
                            'phone' => (string) $data['phone'],
                            'comment' => (string) $data['phonesComment'],
                            'ext' => (string) $data['phonesExt'],
                            'is_default' => (bool) $data['phonesDefault']
                        ],
                        'groups' => (object) [
                            'group_id' => (string) $data['groupId'],
                            'group_name' => (string) $data['groupName']
                        ],
                        'nets' => (object) [
                            'net_id' => (string) $data['netId'],
                            'net' => (string) $data['net'],
                            'uname' => (string) $data['netUname']
                        ],
                        'messengers' => (object) [
                            'mgr_id' => (string) $data['mgrId'],
                            'mgr' => (int) $data['mgr'],
                            'uname' => (string) $data['mgrUname']
                        ],
                        'in_favorites' => (object) [
                            $data['inFavorit']
                        ],
                        'custom_values' => (object) [
                            'custom_value_id' => (int) $data['customValueId'],
                            'custom_field_id' => (int) $data['customFieldId'],
                            'type' => (int) $data['customType'],
                            'text' => (string) $data['customText'],
                            'list_items' => (object) [
                                'enum_id' => (int) $data['customListEnumId'],
                                'order' => (int) $data['customListOrder'],
                                'name' => (string) $data['customListName']
                            ],
                        ],
                        'on_error' => (string) $data['contactOnError']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

    /**
     * Удалить контакт
     */
    public function removeContact($data) {
        $url = 'vpbx/ab/contacts/delete';
        $obj = (object) [
                    'data' => (array) [
                        'contact_id' => $data['contactId']
                    ]
        ];
        return Curl::sendPost($url, $obj);
    }

}
