<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
ini_set('mysql.connect_timeout', 600);
ini_set('default_socket_timeout', 600);
ini_set('memory_limit', -1);

date_default_timezone_set('Europe/Moscow');

require_once dirname(__DIR__) . '/api/Simpla.php';

if (!function_exists('config')) {
    require_once dirname(__DIR__) . '/app/Core/Helpers/BaseHelper.php';
}


// Отвалы новые

class DailyDumps extends Simpla
{
    const URL = 'https://kitapi-ru.voximplant.com/api/v3/agentCampaigns/';
    const URL_DNC = 'https://kitapi-ru.voximplant.com/api/v3/dnc/';
    const DOMAIN = 'boostra2023';
    const TOKEN = '92f5c9e3ea66018f60700a1e7f9f51be37d68758895df31b7feafe95b1eb02eb';

    const AppEndContacts = 'appendContacts';
    const AddDncContacts = 'addDncContacts';
    const GetContacts = 'searchContacts';
    const DelContacts = 'deleteDncContact';
    private string $type;

    /**
     * All steps list
     * @var array|string[] $steps
     */
    private array $steps = [
        'personal_data_added' => 'Регистрация',
        'address_data_added' => 'Адрес',
        'accept_data_added' => 'Одобрение',
        'card_added' => 'Карта',
        'files_added' => 'Файлы',
        'additional_data_added' => 'Доп. инфо',
    ];

    /**
     * @var string
     */
    private string $message = '';

    /**
     * @var resource $curl
     */
    private $curl = null;

    private function getDomain(): string
    {
        return config('services.voximplant.domain', self::DOMAIN);
    }

    private function getToken(): string
    {
        return config('services.voximplant.token', self::TOKEN);
    }

    public function cron()
    {
        $message = &$this->message;

        $start = microtime(true);

        // пересобрать под case для удобства
        if ($this->type == 'MIN-DNC') {
            $arrayUsersToVox = [];
            $datetime = date('Y-m-d H:i:s');

            $userPhones = $this->getListActualVox(["ongoing"], 963);

            $message .= " <b>🟢 Крон каждые 3 минуты</b>\n текущее $datetime \n";
            $message .= "В вокс - <b>" . count($userPhones) . "</b> ☎️ \n";


            $idsNew = '';

            //запись в бд и отправка в вокс
            $users = $this->getMissing($userPhones);
            if (count($users) > 0) {
                foreach ($users as $item) {
                    $step = $this->getCurrentStep($item);

                    $arrayUsersToVox[] = [
                        'id' => $item["user_id"],
                        'UTC' => $item["UTC"],
                        'phone' => $item["phone_mobile"],
                        'lastname' => $item["lastname"],
                        'firstname' => $item["firstname"],
                        'patronymic' => $item["patronymic"],
                        'step' => $step,
                    ];

                    $idsNew .= "{$item['user_id']} - $step \n";
                }

                $message .= "Новых для записи  - <b> " . count($users) . "</b>\n $idsNew";

                $usersChunks = array_chunk($arrayUsersToVox, 50);
                foreach ($usersChunks as $usersChunk) {
                    $this->send([
                        "access_token" => $this->getToken(),
                        "campaign_id" => 963,
                        'rows' => json_encode($usersChunk),
                    ], self::AppEndContacts);
                }

                $this->writeToDBMMissing($users);
            }


            // отвалы с параметрами
            $usersForPar = $this->getMissing($userPhones, true);


            if (count($usersForPar) > 0) {

                $message .= "Отвалы с параметрами - <b>" . count($usersForPar) . "</b> 👨‍🔧️ \n";

                foreach ($usersForPar as $user) {
                    if ($user["continue_order"] == 1) {
                        $resAnalize = $this->analyzeParamNot($user);
                        $message .= "{$user['user_id']} - <b> параметр нет! </b> - статус  {$resAnalize} 👨‍🔧️ \n";
                    } elseif ($user["continue_order"] == 2) {
                        $user['hour_date_add'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        $message .= "{$user['user_id']} - <b> параметр да! </b> 👨‍🔧️ \n";
                        $this->updateMissingHour($user);
                    } else {

                    }
                }

            }
            // тут передается $message внутри класса
//            $this->sendTelegram();
            dd(number_format(microtime(true) - $start) . ' sec  - BOOSTRA -> PDS');


        } elseif ($this->type == 'PDS-DNC') {
            // пункт 3 - а

            $this->PdsToDnc(963, 332);
            $this->PdsToDnc(832, 342);
            dd(number_format(microtime(true) - $start) . ' sec  - PDS -> DNC');

        } elseif ($this->type == 'DNC-DEL') {
            // пункт 3 - b


            $userPhones = $this->getListActualVox(["canceled", "failed"], 963, true);

            //пересобрать под генератор
            $userPhonesArRemove = array_chunk($userPhones, 30);
            foreach ($userPhonesArRemove as $arrayPhone) {
                $this->deleteContact($arrayPhone);
            }

            $itemCount = count($userPhones);

            $datetime = date('H:i:s');
            $this->message = "------------------------- \n";
            $this->message .= "---- {$datetime} ---- \n";
            $this->message .= "-------------------------- \n";
            $this->message .= "⚪️ ️Система очистки DNC - 332 \n";
            $message .= "⚪️ Удалено $itemCount ☎️ ";
            $this->sendTelegram();

            dd(number_format(microtime(true) - $start) . ' sec  - DNC -> DEL');

        } elseif ($this->type == 'HOUR-PDS') {

            $datetime = date('Y-m-d H:i:s');
            $message .= " <b>🔵 Крон каждые 10 минуты (с параметрами)</b>\n текущее $datetime \n";


            $query = $this->db->placehold("SELECT * FROM __missing_activity WHERE hour_date_add < '$datetime' AND send = 0");
            $this->db->query($query);
            $arrMiss = json_decode(json_encode($this->db->results()), true);


            $message .= "Отправка <b>чез час кол-во - " . count($arrMiss) . "</b>  \n";


            if (!$arrMiss || (count($arrMiss) < 1)) {
                $message .= 'Ещё нет данных для отправки ';
//                $this->sendTelegram();
                return;
            }

            foreach ($arrMiss as $item) {
                $resHour = $this->getHourMissing($item);
                $message .= "{$item['user_id']} - <b>через час</b> - статус $resHour \n";
            }

//            $this->sendTelegram();


        } elseif ($this->type == 'STATUS-PDS') {
            // пункт 6 - 10 минут
            // статус 1 - не успешный
            // статус 2 - успешный
            $this->failedMissings();


        } elseif ($this->type == 'RING-MISSING') {
            $this->ringMissings();
        } else {
            die();
        }


    }

    /**
     * Получаем список актуальных на данным момент контактов
     * ---- добавить выборку за день сюда (*) from - to
     *
     * @param $users
     * @return array
     */
    private function getListActualVox(array $status, int $compaing_id, bool $days = false)
    {


        $date_today = $days ? date('Y-m-d 00:00:00', strtotime('-4 day')) : date('Y-m-d 00:00:00', strtotime('-1 day'));
        $date_later = date('Y-m-d 23:59:59', strtotime('+2 day'));

        // массив для вывода всех со стасусом "ongoing
        $userPhones = [];

        $count_pages = $this->send([
            "access_token" => $this->getToken(),
            "status" => json_encode($status),
            "from" => $date_today,
            "to" => $date_later,
            "campaign_id" => $compaing_id,
            "per-page" => 50,
            "page" => 1,

        ], self::GetContacts);
        if (array_key_exists('_meta', $count_pages)) {
            $pagesCountMain = $count_pages["_meta"]["pageCount"];
        } else {
            return [];
        }

        $arrayPages = array_fill(1, $pagesCountMain, 'pear');
        foreach ($arrayPages as $key => $itemAr) {
            sleep(3);
            // запрос данных
            $res = $this->send([
                "access_token" => $this->getToken(),
                "status" => json_encode($status),
                "from" => $date_today,
                "to" => $date_later,
                "campaign_id" => $compaing_id,
                "per-page" => 50,
                "page" => $key,
            ], self::GetContacts);

            if (array_key_exists('_meta', $res)) {
                // запишем в новый массив только телефоны
                foreach ($res["result"] as $item) {
                    $userPhones[] = $item["phone"];
                }

            }


        }
        return $userPhones;
    }

    /**
     * Get current step
     * @param array $item
     * @return string
     */
    private function getCurrentStep(array $item): string
    {
        foreach ($this->steps as $key => $step) {
            if (!$item[$key]) {
                return $step;
            }
        }

        return '';
    }

    /**
     * Весь список из PDS -> DNC
     * пунтк 3 ч (a)
     *
     * @return void
     */
    private function PdsToDnc($pds_id, $dnc_id)
    {
        $datetime = date('Y-m-d');
        $this->message .= "------------------------- \n";
        $this->message .= "---- {$datetime} ---- \n";
        $this->message .= "-------------------------- \n";
        $this->message .= "🔵 Очистка списка PDS - {$pds_id} \n";
        $this->message .= "🔵 DNC - {$dnc_id} \n";

        $userPhones = $this->getListActualVox(["ongoing", "failed"], $pds_id);

        if (count($userPhones) < 1){
            $this->message .= "🔵 Кол-во для DNC 0 ☎";
            $this->sendTelegram();
            return;
        }

        $userPhonesChunk = array_chunk($userPhones, 50);
        foreach ($userPhonesChunk as $key => $item) {
            $res = $this->addToDnc($item, $dnc_id);
            if (array_key_exists('success', $res)) {
                $status = $res["success"] ? 'успешно' : 'провал';
                $this->message .= "🔷 {$key} набор - {$status} \n";
            }
        }

        $item_count = count($userPhones);

        $this->message .= "🔵 Кол-во для DNC $item_count ☎";
        $this->sendTelegram();
    }

    /**
     * Item PDS[$item] -> DNC
     *
     * @param array $dnc
     * @return void
     */
    private function addToDnc(array $dnc, $dnc_id): array
    {

        return $this->send([
            'id' => $dnc_id,
            'contacts' => json_encode($dnc),
            'comment' => 'тест - vl',
            "access_token" => $this->getToken(),
        ], self::AddDncContacts, true);
    }

    /**
     * * DNC -> DEL
     * по дням доделать
     * @param array $numsDel
     * @return void
     */
    private function deleteContact(array $numsDel): void
    {
        if ($numsDel && is_array($numsDel)) {
            foreach ($numsDel as $number) {
                $this->send([
                    'list_id' => 332,
                    'number' => $number,
                    "access_token" => $this->getToken(),
                ], self::DelContacts, true);
            }
        }
    }

    /**
     * Получение отвалов за 3 минуты
     *
     * @return array
     */
    private function getMissing(array $userPhones, bool $continue = false)
    {
        // подбор дат (текущий день  + дата без 5 минут)
        $today = date('Y-m-d 00:00:00');
        $withFiveMdate = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $withTenMdate = date('Y-m-d H:i:s', strtotime('-10 minutes'));

        $sql_continue = !$continue ? '' : 'NOT';
        $sql_param = !$continue ? '' : "AND s_users.missing_manager_update_date < ' $withTenMdate '";

        $arrNumbersIn = empty($userPhones) ? ['1'] : $userPhones;

        //запрос в бд
        $query = $this->db->placehold("SELECT 
            id as user_id,
            s_users.phone_mobile,
            COALESCE(NULLIF(tz.timezone, ''), '+00:00') AS UTC,
            s_users.missing_real_date,
            s_users.personal_data_added,
            s_users.address_data_added,
            s_users.accept_data_added,
            s_users.card_added,
            s_users.files_added,
            s_users.additional_data_added,
            s_users.continue_order,
            s_users.address_data_added_date,
            s_users.accept_data_added_date,
            s_users.card_added_date,
            s_users.files_added_date,
            s_users.additional_data_added_date,
            s_users.missing_manager_update_date,
             s_users.lastname,
           s_users.firstname,
           s_users.patronymic
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.created > '$today'  
            AND s_users.missing_real_date between '$withTenMdate' AND '$withFiveMdate' 
            AND s_users.continue_order IS $sql_continue NULL
            $sql_param
            AND s_users.phone_mobile NOT IN (?@)", (array)$arrNumbersIn);
        $query .= $this->db->placehold(' AND 
            (s_users.personal_data_added = 0
            OR s_users.address_data_added = 0
            OR s_users.accept_data_added = 0
            OR s_users.card_added = 0
            OR s_users.files_added = 0
            OR s_users.additional_data_added = 0
            ) AND (
                    (NOW() > s_users.created + INTERVAL 300 SECOND  AND s_users.personal_data_added = 0)
                    OR (NOW() > s_users.personal_data_added_date + INTERVAL 300 SECOND AND s_users.address_data_added = 0)
                    OR (NOW() > s_users.address_data_added_date + INTERVAL 300 SECOND AND s_users.accept_data_added = 0)
                    OR (NOW() > s_users.accept_data_added_date + INTERVAL 300 SECOND AND s_users.files_added = 0)
                    OR (NOW() > s_users.files_added_date + INTERVAL 300 SECOND AND s_users.card_added = 0)
                    OR (NOW() > s_users.card_added_date + INTERVAL 300 SECOND AND s_users.additional_data_added = 0)
                )
            ');

        $this->db->query($query);
        $db_results = $this->db->results();

        $json = json_encode($db_results);
        return json_decode($json, true);
    }

    /**
     * Получение отвалов за 3 минуты
     *
     * @return bool
     */
    private function getHourMissing(array $userHour): bool
    {
        $userId = (int)$userHour["user_id"];
        $userPhone = $userHour["phone_mobile"];
        $withFiveMdate = date('Y-m-d H:i:s', strtotime('-70 minutes'));

        $query = $this->db->placehold("SELECT 
            id as user_id
            FROM __users 
            LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id
            WHERE s_users.missing_real_date > '$withFiveMdate' 
            AND s_users.id  = $userId");
        $query .= $this->db->placehold(' AND 
            (s_users.personal_data_added = 0
            OR s_users.address_data_added = 0
            OR s_users.accept_data_added = 0
            OR s_users.card_added = 0
            OR s_users.files_added = 0
            OR s_users.additional_data_added = 0
            )
            AND (
                    (NOW() > s_users.created + INTERVAL 300 SECOND  AND s_users.personal_data_added = 0)
                    OR (NOW() > s_users.personal_data_added_date + INTERVAL 300 SECOND AND s_users.address_data_added = 0)
                    OR (NOW() > s_users.address_data_added_date + INTERVAL 300 SECOND AND s_users.accept_data_added = 0)
                    OR (NOW() > s_users.accept_data_added_date + INTERVAL 300 SECOND AND s_users.files_added = 0)
                    OR (NOW() > s_users.files_added_date + INTERVAL 300 SECOND AND s_users.card_added = 0)
                    OR (NOW() > s_users.card_added_date + INTERVAL 300 SECOND AND s_users.additional_data_added = 0)
                )
            ');// LIMIT
        $this->db->query($query);
        // тебе нужен один result ? или results ? можно добавить LIMIT 1 если один нужен
        // result() - ведь мне только 1 и вернёт
        $res = json_decode(json_encode($this->db->result()), true);

        if (!$res) {
            $query_us_set = $this->db->placehold("UPDATE __missing_activity SET `send` = 1 WHERE user_id = $userId");
            $this->db->query($query_us_set);
            // потому что typehint : string
            return false;
        }


        $resVox = $this->send([
            "access_token" => $this->getToken(),
            "status" => json_encode(["ongoing"]),
            "campaign_id" => 963,
            "phone" => $userPhone,
        ], self::GetContacts);

        if ((!empty($resVox['success']) && $resVox["success"] != false) ||
            (!empty($resVox['result']) && count($resVox["result"]) > 0)
        ) {
            return false;
        }

        $query_us_set = $this->db->placehold("UPDATE __missing_activity SET `send` = 1 WHERE user_id = $userId");
        $this->db->query($query_us_set);


        $this->send([
            "access_token" => $this->getToken(),
            "campaign_id" => 963,
            'rows' => json_encode([['id' => $userId, 'UTC' => $userHour["UTC"], 'phone' => $userHour["phone_mobile"]]]),
        ], self::AppEndContacts);
        return true;
    }

    /**
     * запись в бд
     *
     * continue_order (1) - НЕТ
     * continue_order (2) - ДА
     *
     * @param array $data
     * @return mixed
     */
    private function writeToDBMMissing(array $data): bool
    {
        if (count($data) < 1) {
            return false;
        }

        // тоже пересоберётся, чтобы избавиться от этих 9 строчек кода
        unset($data[0]["lastname"]);
        unset($data[0]["firstname"]);
        unset($data[0]["patronymic"]);

        unset($data[0]["personal_data_added"]);
        unset($data[0]["address_data_added"]);
        unset($data[0]["accept_data_added"]);
        unset($data[0]["card_added"]);
        unset($data[0]["files_added"]);
        unset($data[0]["additional_data_added"]);


        $sqlValls = [];
        $arrKeys = '(`' . implode('`,`', array_keys($data[0])) . '`)';


        foreach ($data as $item) {
            $itemSQL = '(';
            $itemSQL .= $item["user_id"] . ',';
            $itemSQL .= $item["phone_mobile"] . ',';
            $itemSQL .= '"' . $item["UTC"] . '",';
            $itemSQL .= $item["missing_real_date"] ? '"' . $item["missing_real_date"] . '",' : 'null,';
            $itemSQL .= $item["continue_order"] ? '"' . $item["continue_order"] . '",' : 'null,';
            $itemSQL .= $item["address_data_added_date"] ? '"' . $item["address_data_added_date"] . '",' : 'null,';
            $itemSQL .= $item["accept_data_added_date"] ? '"' . $item["accept_data_added_date"] . '",' : 'null,';
            $itemSQL .= $item["card_added_date"] ? '"' . $item["card_added_date"] . '",' : 'null,';
            $itemSQL .= $item["files_added_date"] ? '"' . $item["files_added_date"] . '",' : 'null,';
            $itemSQL .= $item["additional_data_added_date"] ? '"' . $item["additional_data_added_date"] . '",' : 'null,';
            $itemSQL .= $item["missing_manager_update_date"] ? '"' . $item["missing_manager_update_date"] . '",' : 'null';
            $itemSQL .= ')';

            $sqlValls[] = $itemSQL;

            unset($itemSQL);
        }
        $arr = implode(',', $sqlValls);

        $query = $this->db->placehold(" INSERT INTO __missing_activity $arrKeys VALUES $arr ");
        $this->db->query($query);
        return true;
    }

    /***
     * Если статус НЕТ - продолжит оформлять
     * будет пересобрана или опущен по логике (т.к если попал в отвал с этим статусом, значит что-то изменил в этапах)
     *
     * @param array $user
     * @return void
     */
    private function analyzeParamNot(array $user)
    {

        $this->send([
            "access_token" => $this->getToken(),
            "campaign_id" => 963,
            'rows' => json_encode([['id' => $user["user_id"], 'UTC' => $user["UTC"], 'phone' => $user["phone_mobile"]]]),
        ], self::AppEndContacts);

        /*
        unset($user["continue_order"]);
        $userId = (int) $user["user_id"];

        $query = $this->db->placehold("SELECT * FROM __missing_activity WHERE user_id = $userId ");
        $this->db->query($query);
        $arrMiss = json_decode(json_encode($this->db->result()), true);
        if (!$arrMiss) {
            return 'отклонён';
        }

        // тут для diff чистим не нужные ключи
        // вот тут по усовию, если подскажешь то думаю что вообще может убрать этот метод
        unset(
            $arrMiss["continue_order"],
            $user["continue_order"],
            $user["missing_real_date"],
            $user["personal_data_added"],
            $user["address_data_added"],

        );
        unset();
        unset();
        unset();
        unset();
        unset($user["accept_data_added"]);
        unset($user["card_added"]);
        unset($user["files_added"]);
        unset($user["additional_data_added"]);
        unset($user["lastname"]);
        unset($user["firstname"]);
        unset($user["patronymic"]);


        $inter = array_diff_assoc($user, $arrMiss);

        if (count($inter) > 0) {
            $this->data = [
                "access_token" => $this->getToken(),
                "campaign_id" => 963,
                'rows' => json_encode([['id' => $userId, 'UTC' => $arrMiss["UTC"], 'phone' => $arrMiss["phone_mobile"]]]),
            ];

            $this->send(self::AppEndContacts);
            return 'добавлен';
        } else {
            return 'отклонён';
        }
     */

    }

    /**
     * Изменить время отправки отвала на час
     *
     * @param $user
     * @return void
     */
    private function updateMissingHour($user)
    {
        $dateHour = $user['hour_date_add'];
        $userId = $user['user_id'];

        $query = $this->db->placehold("UPDATE __missing_activity SET `hour_date_add` = '$dateHour' WHERE user_id = $userId");
        $this->db->query($query);
//        $res = json_decode(json_encode($this->db->result()), true);
    }

    /**
     * ловим и пишем отвалы со статусом failed
     *
     * @return void
     */
    private function failedMissings()
    {

        $res = $this->getListActualVox(["failed"], 963);
        if (($res == false) || (count($res) < 1)) {
            $this->message .= "<b>Спиок пуст</b>️\n";
            return;
        }


        $query = $this->db->placehold("SELECT user_id FROM __missing_activity WHERE phone_mobile IN (?@) AND failed_status = 0", $res);
        $this->db->query($query);
        $arrMissFaills = json_decode(json_encode($this->db->results('user_id')), true);


        if (!$arrMissFaills || (count($arrMissFaills) < 1)) {
            return;
        }

        foreach ($arrMissFaills as $user_id) {
            $query_us = $this->db->placehold("UPDATE __users SET `call_status` = 1 WHERE id = $user_id");
            $this->db->query($query_us);

            $query_us_mis = $this->db->placehold("UPDATE __missing_activity SET `failed_status` = 1 WHERE user_id = $user_id");
            $this->db->query($query_us_mis);
        }

    }

    private function ringMissings() {
        $pdsNumbers = [
            'created' => [
                'pds' => 1,
                'numbers' => []
            ],
            'personal_data_added_date' => [
                'pds' => 48763,
                'numbers' => []
            ],
            'address_data_added_date' => [
                'pds' => 2,
                'numbers' => []
            ],
            'accept_data_added_date' => [
                'pds' => 3,
                'numbers' => []
            ],
            'card_added_date' => [
                'pds' => 48762,
                'numbers' => []
            ],
            'files_added_date' => [
                'pds' => 4,
                'numbers' => []
            ],
            'additional_data_added_date' => [
                'pds' => 5,
                'numbers' => []
            ],
        ];
        $start =  date('Y-m-d 00:00:00 ', strtotime("-2 days"));
        $end =  date('Y-m-d 23:59:59 ', strtotime("-2 days"));

        $query = $this->db->placehold(" 
            SELECT phone_mobile AS phone,
                COALESCE(NULLIF(tz.timezone, ''), '+03:00') AS UTC,
                created,personal_data_added_date,address_data_added_date,accept_data_added_date,card_added_date,files_added_date,additional_data_added_date,
                   CASE
                    WHEN personal_data_added = 0 THEN 'created'
                    WHEN address_data_added = 0 THEN 'personal_data_added_date'
                    WHEN accept_data_added = 0 THEN 'address_data_added_date'
                    WHEN card_added = 0 THEN 'accept_data_added_date'
                    WHEN files_added = 0 THEN 'card_added_date'
                    WHEN additional_data_added = 0 THEN 'files_added_date'
                END AS last_stage from s_users
                    LEFT JOIN s_time_zones tz ON tz.time_zone_id = s_users.timezone_id              
                Where (personal_data_added = 0 AND created BETWEEN '$start' AND '$end')
                OR  (address_data_added = 0 AND personal_data_added_date BETWEEN '$start' AND '$end')
                OR (accept_data_added = 0 AND address_data_added_date BETWEEN '$start' AND '$end')
                OR (card_added = 0 AND accept_data_added_date BETWEEN '$start' AND '$end')
                OR (files_added = 0 AND card_added_date BETWEEN '$start' AND '$end') 
                OR (additional_data_added = 0 AND files_added_date BETWEEN '$start' AND '$end')
        ");

        $this->db->query($query);
        $ringMissings = json_decode(json_encode($this->db->results()), true);
        foreach ($ringMissings as $missing) {
            $column = $missing['last_stage'];
            unset($missing['last_stage']);
            unset($missing['created']);
            unset($missing['personal_data_added_date']);
            unset($missing['address_data_added_date']);
            unset($missing['accept_data_added_date']);
            unset($missing['card_added_date']);
            unset($missing['files_added_date']);
            unset($missing['files_added_date']);
            unset($missing['additional_data_added_date']);
            $pdsNumbers[$column]['numbers'][] = $missing;
        }

        foreach ($pdsNumbers as $data) {
            if (!empty($data['numbers']) && mb_strlen($data['pds'])>3) {
                $data = [
                    "campaign_id" => $data['pds'],
                    'rows' => json_encode($data['numbers']),
                ];
                $this->voximplant->sendRobocompany($data);
            }
        }
    }
    private function getCurl()
    {
        if (empty($this->curl)) {
            $this->curl = curl_init();
        }
    }

    /**
     * Send API
     *
     * @param $data
     * @return array
     */
    private function send(array $data, string $method, bool $is_dnc = false)
    {
        $data["domain"] = $this->getDomain();
        $url = !$is_dnc ? self::URL : self::URL_DNC;

        // 2 раза вызов curl_init
        $this->getCurl();
        curl_setopt_array($this->curl, [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PHP-MCAPI/2.0',
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url . $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);

        $result = curl_exec($this->curl);
//        curl_close($this->curl);
        return json_decode($result, true);
    }


    /**
     * Send To Telegram
     *
     * @param $msg
     * @param $token
     * @return void
     */
    private function sendTelegram($token = '6142806749:AAFE3hsCxU6hEzf6qAR-nD4IePKzdoZJEmw')
    {
//        return;

        $data = array(
            'chat_id' => -845657640,
            'text' => $this->message,
            'parse_mode' => 'html',
        );
        if ($token != '') {
            $this->getCurl();
            curl_setopt_array($this->curl, array(
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1, // тут нужен тебе return ? (нет)
                CURLOPT_POST => 1,
                CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/sendMessage',
                CURLOPT_POSTFIELDS => $data
            ));

            curl_exec($this->curl);
            curl_close($this->curl);

        }
    }

    public function setType($type)
    {
        $this->type = $type;
    }

}

// тут вот такую проверку можно сделать чтобы удостовериться в том что arg есть
$dailyDumps = new DailyDumps();
if (!empty($_SERVER['argv'][1])) {
    $dailyDumps->setType($_SERVER['argv'][1]);
    $dailyDumps->cron();
} else {
    $dailyDumps->logging('DailyDumps', 'cron', $_SERVER['argv'], [], 'daily_dumps.txt');
}
