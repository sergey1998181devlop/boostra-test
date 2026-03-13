<?php

use api\services\FileStorageService;

require_once __DIR__ . '/../lib/autoloader.php';

/**
 * Бизнес логика короткого флоу регистрации с использованием кабутека
 *
 * @see https://tracker.yandex.ru/BOOSTRARU-3554 BOOSTRARU-3554
 */
class ShortFlow extends Simpla
{
    /** Не прошёл скоринги, переход на продажу в бонон */
    public const STAGE_BONON = 'bonon'; // из ajax/check_scorings_nk.php не видит константы в view/ShortRegisterView.php, поэтому добавил еще сюда

    /** Регистрация завершена, переадресация в ЛК */
    public const STAGE_FINAL = 'final'; // из ajax/check_scorings_nk.php не видит константы в view/ShortRegisterView.php, поэтому добавил еще сюда

    /** @var string Успешная верификация */
    private const CYBERITY_SUCCESS_VERIFICATION_RESULT = 'GREEN';

    /** @var string Неуспешная верификация */
    private const CYBERITY_FAILED_VERIFICATION_RESULT = 'RED';

    public const USERDATA_SHORT_FLOW = 'is_short_flow';
    public const USERDATA_STAGE = 'short_flow_stage';

    /** @var string URL API сервиса */
    private const BASE_URL = 'https://api.cyberity.ru';

    /** @var string Уровень проверки (уровни проверок позволяют детально настроить процесс верификации Ваших пользователей) */
    private const BASIC_CHECK_LEVEL = 'basic-kyc-level';

    /** @var string Длительность существование токена (в секундах) */
    private const TOKEN_AVAILABILITY_DURATION = 600;

    /** @var string Лог-файл */
    private const LOG_FILE = 'short_flow.txt';

    private const SCORINGS_LIST = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_TERRORIST_CHECK,
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_UPRID,
        Scorings::TYPE_REPORT,
    ];
    
    /**
     * Список utm_source которые проходят короткое флоу.
     *
     * Источник => Шанс
     */
    public const ALLOWED_UTM_SOURCES = [
        'Boostra' => 100,
    ];

    public function getAccessToken(): string
    {
        $phone = $_SESSION['phone'];

        $url = '/resources/accessTokens';

        $data = [
            'userId' => $phone,
            'levelName' => self::BASIC_CHECK_LEVEL,
            'ttlInSecs' => (string)self::TOKEN_AVAILABILITY_DURATION
        ];

        $url .= '?' . http_build_query($data);

        $headers = $this->getHeaders($url, 'POST');

        $response = $this->request(self::BASE_URL . $url, 'POST', [], $headers);

        $response = json_decode($response);

        if (empty($response->token)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка получения токена', json_encode($response)], self::LOG_FILE);
            return json_encode([
                'success' => false,
                'message' => 'Произошла ошибка. Повторите попытку позже. При сохранении ошибки обратитесь в техническую поддержку'
            ]);
        }

        return json_encode([
            'success' => true,
            'access_token' => $response->token
        ]);
    }

    /**
     * Проходит ли источник по короткому флоу.
     * @param string $utm_source
     * @return bool
     */
    public function isShortFlowSource(string $utm_source): bool
    {
        $chance = self::ALLOWED_UTM_SOURCES[$utm_source] ?? 0;
        return $chance > 0 && mt_rand(1, 100) <= $chance;
    }

    /**
     * Признак прохождения (сейчас или в прошлом) клиента по короткому флоу регистрации
     * @param int|string|null $user_id
     * @return bool
     */
    public function isShortFlowUser($user_id): bool
    {
        $is_short_flow = $this->user_data->read((int)$user_id, self::USERDATA_SHORT_FLOW);
        return !empty($is_short_flow);
    }

    /**
     * Включен ли короткий флоу (глобально)
     * @return bool
     */
    public function isShortFlowEnabled(): bool
    {
        $is_enabled = $this->settings->short_flow_enabled;
        return !empty($is_enabled);
    }

    /**
     * Текущая стадия прохождения короткого флоу.
     * @param int $user_id
     * @return string|null
     */
    public function getRegisterStage(int $user_id): ?string
    {
        return $this->user_data->read($user_id, self::USERDATA_STAGE);
    }

    public function setRegisterStage($user_id, $stage)
    {
        $this->user_data->set($user_id, self::USERDATA_STAGE, $stage);

        if ($stage === self::STAGE_FINAL) {
           $lastOrder = $this->orders->get_last_order((int)$user_id);

           // Если ранее не была создана заявка (т.к. раньше заявка создавалась лишь после этапа работы)
           if (empty($lastOrder)) {

               // Добавление заявки
               $orderId = $this->addOrder($user_id);

               // Добавление скорингов
               $this->addScorings($user_id, $orderId);

               $user = $this->users->get_user($user_id);

               // Сохранение фото паспорта и скана лица из кабутека
               $this->saveFiles($user);
               $this->users->update_user($user_id, [
                   'files_added'       => 1,
                   'files_added_date'  => date('Y-m-d H:i:s'),
               ]);

               $lastOrder = $this->orders->get_last_order((int)$user_id);
           }

           // Отображение заявки из короткого флоу верификаторам
           $this->soap->set_order_complete((int)$lastOrder->id);

           $this->users->update_user($user_id, [
               'card_added'        => 1,
               'card_added_date'   => date('Y-m-d H:i:s')
           ]);
        }
    }

    public function addOrder(int $userId): int
    {
        $order_dto = new stdClass();
        $order_dto->user_id = $userId;
        $order_dto->ip = $_SERVER['REMOTE_ADDR'];
        $order_dto->juicescore_session_id = $_SESSION['juicescore_session_id'] ?? '';
        $order_dto->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"];
        $order_dto->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"];
        $order_dto->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"];
        $order_dto->utm_content = empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"];
        $order_dto->utm_term = empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"];
        $order_dto->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"];
        $order_dto->click_hash = empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"];

        $order_id = $this->addOrderToDb($order_dto);

        // USERAGENT
        $useragent = $this->request->post('juicescore_useragent') ?? $_SERVER['HTTP_USER_AGENT'];
        if (!empty($useragent))
            $this->order_data->set($order_id, $this->order_data::USERAGENT, $useragent);

        if (!empty($_SESSION['vid'])) {
            $this->order_data->set(
                $order_id,
                $this->order_data::VISITOR_ID,
                $_SESSION['vid']
            );
        }

        // Финкарта
        $this->orders->saveFinkartaFp($order_id, $this->request->post('finkarta_fp'));

        return (int)$order_id;
    }

    public function addScorings(int $userId, int $orderId = 0)
    {
        $activeScoringsType = $this->scorings->get_types(['active' => 1]);
        $activeScoringsTypeId = array_column($activeScoringsType, 'id');

        $addedScoringsType = [];
        if ($orderId !== 0) {
            $addedScorings = $this->scorings->get_scorings([
                'order_id' => $orderId
            ]);

            if (empty($addedScorings)) {
                $addedScorings = [];
            }

            $addedScoringsType = array_column($addedScorings, 'type');
        }

        foreach (self::SCORINGS_LIST as $scoringType) {

            // Если скоринг выключен - не добавляем его
            if (!in_array($scoringType, $activeScoringsTypeId)) {
                continue;
            }

            // Если скоринг уже добавлен для заявки - не добавляем скоринг
            if (in_array($scoringType, $addedScoringsType)) {
                continue;
            }

            $this->scorings->add_scoring([
                'user_id' => $userId,
                'order_id' => $orderId,
                'type' => $scoringType,
            ]);
        }
    }

    /**
     * Создание первой заявки клиента
     *
     * @param stdClass $order_dto
     * @return number order_id в случае успеха
     * @throws DateMalformedStringException
     * @throws SoapFault
     */
    public function addOrderToDb(stdClass $order_dto): int
    {
        $user_id = $order_dto->user_id;
        $user = $this->users->get_user($user_id);

        if ($lastOrder = $this->orders->get_last_order($user_id)) {
            $this->logging(__METHOD__, '', ['order_id' => $lastOrder->id], 'У клиента уже есть заявка', self::LOG_FILE);
            return (int)$lastOrder->id;
        }

        $recomendation_amount = $this->users->get_recomendation_amount($user);
        if ($recomendation_amount <= 0) {
            $this->logging(__METHOD__, '', ['recomendation_amount' => $recomendation_amount], 'Некорректная рекомендуемая сумма', self::LOG_FILE);
            return (int)$lastOrder->id;
        }

        $this->users->update_user($user_id, [
            'accept_data_added' => 1,
            'accept_data_added_date' => date('Y-m-d H:i:s'),
            'missing_real_date' => date('Y-m-d H:i:s'),
        ]);

        $period = $user->first_loan_period ?: 16;
        $order = [
            'status' => 1,
            'amount' => $recomendation_amount,
            'period' => $period,
            'user_id' => $user_id,
            'first_loan' => 1,
            'date' => date('Y-m-d H:i:s'),
            'percent' => $period > $this->orders::MAX_PERIOD ? $this->orders::BASE_PERCENTS : 0,
            'organization_id' => $this->organizations->get_base_organization_id(['user_id' => $user_id]),
            'order_uid' => exec($this->config->root_dir . 'generic/uidgen'),
            'b2p' => $user->use_b2p ? 1 : 0
        ];

        foreach ($order_dto as $field => $value) {
            $order[$field] = $value;
        }

        $order_id = $this->orders->add_order($order);
        $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $user->first_loan_amount ?: $recomendation_amount);

        $organization = $this->organizations->get_organization($order['organization_id']);
        $order_1c = [
            'УИД' => $order['order_uid'],
            'ДатаЗаявки' => date('YmdHis', strtotime($order['date'])),
            'ИННОрганизации' => $organization->inn,
            'lastname' => (string)$user->lastname,
            'firstname' => (string)$user->firstname,
            'patronymic' => (string)$user->patronymic,
            'birth' => (string)$user->birth,
            'phone_mobile' => (string)$user->phone_mobile,
            'email' => (string)$user->email,
            'passport_serial' => (string)$user->passport_serial,
            'passport_date' => (string)$user->passport_date,
            'subdivision_code' => (string)$user->subdivision_code,
            'passport_issued' => (string)$user->passport_issued,

            'АдресРегистрацииИндекс' => (string)$user->Regindex,
            'Regregion' => (string)trim($user->Regregion.' '.$user->Regregion_shorttype),
            'Regdistrict' => (string)$user->Regdistrict,
            'Regcity' => (string)trim($user->Regcity.' '.$user->Regcity_shorttype),
            'Reglocality' => '',
            'Regstreet' => (string)trim($user->Regstreet.' '.$user->Regstreet_shorttype),
            'Regbuilding' => (string)$user->Regbuilding,
            'Reghousing' => (string)$user->Reghousing,
            'Regroom' => (string)$user->Regroom,

            'АдресФактическогоПроживанияИндекс' => (string)$user->Faktindex,
            'Faktregion' => (string)trim($user->Faktregion.' '.$user->Faktregion_shorttype),
            'Faktdistrict' => (string)$user->Faktdistrict,
            'Faktcity' => (string)trim($user->Faktcity.' '.$user->Faktcity_shorttype),
            'Faktlocality' => '',
            'Faktstreet' => (string)trim($user->Faktstreet.' '.$user->Faktstreet_shorttype),
            'Faktbuilding' => (string)$user->Faktbuilding,
            'Fakthousing' => (string)$user->Fakthousing,
            'Faktroom' => (string)$user->Faktroom,

            'site_id' => 'Boostra',
            'partner_id' => '',
            'partner_name' => 'Boostra',

            'amount' => (string)$recomendation_amount,
            'period' => empty($user->first_loan_period) ? 16 : (string)$user->first_loan_period,

            'utm_source' => $order_dto->utm_source,
            'utm_medium' => $order_dto->utm_medium,
            'utm_campaign' => $order_dto->utm_campaign,
            'utm_content' => $order_dto->utm_content,
            'utm_term' => $order_dto->utm_term,
            'webmaster_id' => $order_dto->webmaster_id,
            'click_hash' => $order_dto->click_hash,

            'id' => '',
            'car' => '',
            'IntervalNumber' => '',
            'СтатусCRM' => '',
            'СуммаCRM' => (string)$recomendation_amount,
            'УИД_CRM' => $order['order_uid'],

            'МестоРождения' => (string)$user->birth_place,
            'ГородскойТелефон' => (string)$user->landline_phone,
            'Пол' => (string)$user->gender,
            'ДевичьяФамилияМатери' => '',

            'СфераРаботы' => (string)$user->work_scope,

            'ДоходОсновной' => (string)$user->income_base,
            'ДоходДополнительный' => (string)$user->income_additional,
            'ДоходСемейный' => (string)$user->income_family,
            'ФинансовыеОбязательства' => (string)$user->obligation,
            'ПлатежиПоКредитамВМесяц' => (string)$user->other_loan_month,
            'СколькоКредитов' => (string)$user->other_loan_count,
            'КредитнаяИстория' => (string)$user->credit_history,
            'МаксимальноОдобренныйРанееКредит' => (string)$user->other_max_amount,
            'ПоследнийОдобренныйРанееКредит' => (string)$user->other_last_amount,
            'БылоЛиБанкротство' => (string)$user->bankrupt,
            'Образование' => (string)$user->education,
            'СемейноеПоложение' => '',
            'КоличествоДетей' => (string)$user->childs_count,
            'НаличиеАвтомобиля' => (string)$user->have_car,
            'НаличиеНедвижимости' => (int)$user->has_estate,
            'ВК' => (string)$user->social_vk,
            'Инст' => (string)$user->social_inst,
            'Фейсбук' => (string)$user->social_fb,
            'ОК' => (string)$user->social_ok,

            'ServicesSMS' => 0,
            'ServicesInsure' => $user->service_insurance,
            'ServicesReason' => 0,
            'ОтказНаСайте' => 0,
            'ПричинаОтказаНаСайте' => ''
        ];

        $contact_person_name = $contact_person_phone = $contact_person_relation = [];
        if ($contactpersons = $this->contactpersons->get_contactpersons(['user_id'=>$user->id])) {
            foreach ($contactpersons as $contactperson) {
                $contact_person_name[]      = (string)$contactperson->name;
                $contact_person_phone[]     = (string)$contactperson->phone;
                $contact_person_relation[]  = (string)$contactperson->relation;
            }
        }
        $order_1c['КонтактноеЛицоФИО']      = json_encode($contact_person_name);
        $order_1c['КонтактноеЛицоТелефон']  = json_encode($contact_person_phone);
        $order_1c['КонтактноеЛицоРодство']  = json_encode($contact_person_relation);

        if ($user->work_scope == 'Пенсионер')
        {
            $order_1c['Занятость'] = '';
            $order_1c['Профессия'] = '';
            $order_1c['МестоРаботы'] = '';
            $order_1c['СтажРаботы'] = '';
            $order_1c['ШтатРаботы'] = '';
            $order_1c['ТелефонОрганизации'] = '';
            $order_1c['ФИОРуководителя'] = '';
            $order_1c['АдресРаботы'] = '';
        }
        else
        {
            $order_1c['Занятость'] = (string)$user->employment;
            $order_1c['Профессия'] = (string)$user->profession;
            $order_1c['МестоРаботы'] = (string)$user->workplace;
            $order_1c['СтажРаботы'] = (string)$user->experience;
            $order_1c['ШтатРаботы'] = (string)$user->work_staff;
            $order_1c['ТелефонОрганизации'] = (string)$user->work_phone;
            $order_1c['ФИОРуководителя'] = (string)$user->workdirector_name;
            $order_1c['АдресРаботы'] = $user->Workindex.' '.$user->Workregion.', '.$user->Workcity.', ул.'.$user->Workstreet.', д.'.$user->Workhousing;
            if (!empty($user->Workbuilding))
                $order_1c['АдресРаботы'] .= '/'.$user->Workbuilding;
            if (!empty($user->Workroom))
                $order_1c['АдресРаботы'] .= ', оф.'.$user->Workroom;
        }

        $order_1c = (object)$order_1c;
        $resp = $this->soap->send_loan($order_1c);
        if ($resp->return->id_zayavka == 'Не принято') {
            sleep(3);
            $order_1c->utm_source = 'Boostra';
            $resp = $this->soap->send_loan($order_1c);
        }

        $j = 10;
        do {
            sleep(2);

            // Получение и сохранение s_users.UID из 1С
            $uid_resp = $this->soap->get_uid_by_phone($user->phone_mobile);
            $j--;
        } while (empty($uid_resp->uid) && $j > 0);

        if (!empty($uid_resp->uid)) {
            $this->orders->update_order($order_id, ['status' => 1, '1c_id' => $resp->return->id_zayavka]);
            $this->users->update_user($user->id, ['uid' => $uid_resp->uid]);
        }

        $order = $this->orders->get_order($order_id);
        /** @var string[] $old_scorings Типы скорингов которые нужно пересоздать */
        $old_scorings = [];
        $rejectedScoringType = null;
        if ($user_scorings = $this->scorings->get_scorings(['user_id' => $user->id])) {
            foreach ($user_scorings as $scoring) {

                // Обновляем у ранее добавленных скорингов (если есть) order_id
                $this->scorings->update_scoring($scoring->id, ['order_id' => $order_id]);

                // Отдельно отслеживаем завершённую скористу и акси
                if (in_array($scoring->type, [
                        $this->scorings::TYPE_SCORISTA,
                        $this->scorings::TYPE_AXILINK_2
                    ])
                    && $scoring->status == $this->scorings::STATUS_COMPLETED
                    && $scoring->success == 1) {
                    // Обновляем балл в заявке, если он пустой
                    // ИЛИ если это скориста (Затираем балл акси)
                    if (empty($order->scorista_ball) || $scoring->type == $this->scorings::TYPE_SCORISTA) {
                        $this->orders->update_order($order->id, [
                            'scorista_ball' => $scoring->scorista_ball
                        ]);
                    }
                }

                if ($scoring->status == $this->scorings::STATUS_COMPLETED
                    && !empty(AccountView::CAN_REJECT_SCORINGS[$user_scoring->type])) {
                    // Выбираем первую не пустую дату (Обычно end_date)
                    $end_date = new DateTime($scoring->end_date ?? $scoring->start_date ?? $scoring->created);
                    // Сравниваем с текущей датой
                    $now = new DateTime();
                    $interval = $now->diff($end_date);
                    // Если с времени выполнения скоринга прошло больше суток - он старый
                    $is_old = $interval->days >= 1 || $interval->h >= 24;

                    if ($is_old) {
                        // Пересоздаём старый скоринг
                        $old_scorings[] = $scoring->type;
                    }
                    else if ($scoring->success == 0) {
                        if (
                            (
                                $scoring->type == $this->scorings::TYPE_SCORISTA &&
                                !$this->scorings->isScoristaAllowed($order)
                            ) || $this->scorings->isHyperEnabledForOrder($order)
                        ) {
                            continue;
                        }

                        // Скоринг провален
                        $rejectedScoringType = $scoring->type;
                    }
                }
            }
        }

        if (!empty($rejectedScoringType)) {
            $this->rejectOrder((int)$order_id, (int)$rejectedScoringType);
        }
        else {
            foreach ($old_scorings as $type) {
                $this->scorings->add_scoring([
                    'user_id' => $user->id,
                    'order_id' => $order_id,
                    'type' => $type,
                ]);
            }

            if (empty($old_scorings)) {
                $this->scorings->tryAddScoristaAndAxi($order_id);
            }

            $this->scorings->tryAddScoristaAndAxi($order_id);
        }

        return (int)$order_id;
    }

    /**
     * Отказ по заявке по указанной причине.
     * Используется для скорингов которые могут отказать, но завершились до появления заявки.
     * @param int $order_id
     * @param int $scoring_type
     * @return void
     * @throws SoapFault
     */
    function rejectOrder(int $order_id, int $scoring_type)
    {
        $order = $this->orders->get_order($order_id);
        if ($order->status != $this->orders::STATUS_NEW)
            return;

        $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        $reason_id =  AccountView::CAN_REJECT_SCORINGS[$scoring_type];

        $update_order = [
            'status' => $this->orders::STATUS_REJECTED,
            'manager_id' => $tech_manager->id,
            'reason_id' => $reason_id,
            'reject_date' => date('Y-m-d H:i:s')
        ];
        $this->orders->update_order($order_id, $update_order);
        $this->leadgid->reject_actions($order_id);

        $changeLogs = Helpers::getChangeLogs($update_order, $order);
        $this->changelogs->add_changelog([
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ]);

        $reason = $this->reasons->get_reason($reason_id);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND, $tech_manager->name_1c, 0, 1, $reason->admin_name);

        if (!empty($order->is_user_credit_doctor))
            $this->soap->send_credit_doctor($order->id_1c);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order->order_id);

        // Останавливаем выполнения других скорингов по этой заявки
        $type = $this->scorings->get_type($scoring_type);
        $this->scorings->stopOrderScorings($order->order_id, ['string_result' => 'Причина: скоринг ' . $type->title]);
    }

    /**
     * Получить заголовки для запросов
     *
     * API: https://developers.cyberity.ru/api-reference/#app-tokens
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param bool $isJson
     * @return string[]
     */
    private function getHeaders(string $url, string $method = 'GET', array $data = [], bool $isJson = true): array
    {
        $curTimestamp = time();

        $signature = $curTimestamp . $method . $url;

        if (!empty($data)) {
            $signature .= json_encode($data);
        }

        $signatureHash = hash_hmac('sha256', $signature, $this->cyberity->getSecretKey());

        $headers = [
            'X-App-Token: ' . $this->cyberity->getToken(),
            'X-App-Access-Sig: ' . $signatureHash,
            'X-App-Access-Ts: ' . $curTimestamp,
        ];

        if ($isJson) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
        }

        return $headers;
    }

    /**
     * Выполнить запрос
     *
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return bool|string
     */
    private function request(string $url, string $method = 'GET', array $data = [], array $headers = [])
    {
        $curl = curl_init();

        $curlData = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
        ];

        if (!empty($data)) {
            $curlData[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt_array($curl, $curlData);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        if (strpos($url, '/resources/inspections/') !== false) {
            $this->logging(__METHOD__, $url, $data, ['response' => 'Получение фото', 'session' => $_SESSION], self::LOG_FILE);
        } else {
            $this->logging(__METHOD__, $url, $data, ['response' => $response, 'session' => $_SESSION], self::LOG_FILE);
        }

        $error = curl_error($curl);

        if (!empty($error)) {
            $this->logging(__METHOD__, $url, $data, ['error' => $error, 'session' => $_SESSION], self::LOG_FILE);
        }

        curl_close($curl);

        return $response;
    }

    /**
     * Получение данных клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-applicant-data
     *
     * @param string $phone
     * @return stdClass|null
     */
    public function getVerificationResponse(string $phone): ?stdClass
    {
        $url = '/resources/applicants/-;externalUserId=' . $phone . '/one';
        $headers = $this->getHeaders($url);
        $verificationResponse = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        $verificationResponse = json_decode($verificationResponse);

        if (empty($verificationResponse) || empty($verificationResponse->id) || empty($verificationResponse->inspectionId)) {
            return null;
        }

        return $verificationResponse;
    }

    /**
     *
     * Получаем и сохраняем фото паспорта и скана лица
     *
     * @param stdClass $user
     * @return bool
     */
    public function saveFiles(stdClass $user): bool
    {
        // 1. Для получения фото паспорта и скана лица нужно предварительно получить inspectionId клиента
        $verificationResponse = $this->getVerificationResponse($user->phone_mobile);

        if (empty($verificationResponse)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
            return false;
        }
        // 2. Для получения фото паспорта и скана лица нужно предварительно получить id этих файлов
        $applicantStatus = $this->getApplicantStatus($verificationResponse->id);

        if (empty($applicantStatus)) {
            $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить статус верификации', $_SESSION], self::LOG_FILE);
            return false;
        }

        // 3. Получение и сохранение фото паспорта и скана лица
        try {
            $this->savePassportPhoto($applicantStatus, $verificationResponse->inspectionId, $user->id);
        } catch (Throwable $e) {
        }

        try {
            $this->saveFaceScanPhoto($applicantStatus, $verificationResponse->inspectionId, $user->id);
        } catch (Throwable $e) {
        }

        $userVerification = $this->cyberity->getUserVerification([
            'user_id' => (int)$user->id
        ]);

        if (empty($userVerification)) {
            $this->cyberity->insertUserVerification([
                'user_id' => (int)$user->id,
                'applicant_id' => $verificationResponse->id,
                'inspection_id' => $verificationResponse->inspectionId,
                'status' => $this->cyberity::STATUS_PROGRESS,
                'date_create' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
                'date_start_verification' => date('Y-m-d H:i:s'),
            ]);
        } else if ($userVerification->status !== $this->cyberity::STATUS_COMPLETED) {
            $this->cyberity->updateUserVerification((int)$userVerification->id, [
                'applicant_id' => $verificationResponse->id,
                'inspection_id' => $verificationResponse->inspectionId,
                'status' => $this->cyberity::STATUS_PROGRESS,
                'date_update' => date('Y-m-d H:i:s'),
                'date_start_verification' => date('Y-m-d H:i:s'),
            ]);
        }

        $scorings = $this->getCyberityScorings($user);

        if (empty($scorings)) {
            $this->addCyberityScoring((int)$user->id);
            $scorings = $this->getCyberityScorings($user);
        }

        if (empty($scorings)) {
            $this->logging(__METHOD__, '', 'Скоринги кабутека не найдены. Не удалось добавить скоринг', $verificationResponse, self::LOG_FILE);
            return true;
        }

        $this->saveCyberityScoring($scorings, $verificationResponse, $applicantStatus);

        return true;
    }

    private function getCyberityScorings(stdClass $user): array
    {
        $scorings = $this->scorings->get_scorings([
            'user_id' => (int)$user->id,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => [
                $this->scorings::STATUS_NEW,
                $this->scorings::STATUS_PROCESS,
                $this->scorings::STATUS_STOPPED,
                $this->scorings::STATUS_ERROR
            ]
        ]);

        return $scorings ?: [];
    }

    /**
     * Сохранить результат верификации в скоринг TYPE_CYBERITY
     *
     * @param array $scorings
     * @param stdClass $verificationResponse
     * @param stdClass $applicantStatus
     * @return void
     */
    private function saveCyberityScoring(array $scorings, stdClass $verificationResponse, stdClass $applicantStatus): void
    {
        if (empty($verificationResponse->review->reviewResult->reviewAnswer)) {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка! Некорректный ответ из сервиса'
            ];
        } elseif ($verificationResponse->review->reviewResult->reviewAnswer === self::CYBERITY_SUCCESS_VERIFICATION_RESULT) {
            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'string_result' => 'Успешная верификация'
            ];
        } elseif ($verificationResponse->review->reviewResult->reviewAnswer === self::CYBERITY_FAILED_VERIFICATION_RESULT) {
            $stringResult = 'Неуспешная верификация';

            if (!empty($verificationResponse->review->reviewResult->clientComment)) {
                $stringResult .= '. Причина: ' . $verificationResponse->review->reviewResult->clientComment;
            }

            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 0,
                'string_result' => $stringResult
            ];
        } else {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка! Некорректный результат верификации'
            ];
        }

        $update['end_date'] = date('Y-m-d H:i:s');

        $update['body'] = json_encode([
            'verificationResponse' => $verificationResponse,
            'applicantStatus' => $applicantStatus
        ]);

        foreach ($scorings as $scoring) {
            $this->scorings->update_scoring((int)$scoring->id, $update);
        }
    }

    /**
     * Добавление скоринга TYPE_CYBERITY
     *
     * @param int $userId
     * @return void
     */
    private function addCyberityScoring(int $userId)
    {
        $lastOrder = $this->orders->get_last_order($userId);

        $newScoringData = array(
            'user_id' => $userId,
            'order_id' => (int)$lastOrder->id,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => $this->scorings::STATUS_PROCESS,
            'created' => date('Y-m-d H:i:s'),
            'start_date' => date('Y-m-d H:i:s')
        );

        $this->scorings->add_scoring($newScoringData);
    }

    /**
     * Получение и сохранение фото паспорта
     *
     * @param stdClass $applicantStatus
     * @param string $inspectionId
     * @param int $userId
     * @return void
     */
    private function savePassportPhoto(stdClass $applicantStatus, string $inspectionId, int $userId): void
    {
        $fileCounter = 1;

        // Получение и сохранение фото паспорта
        foreach ($applicantStatus->IDENTITY->imageIds as $photoId) {
            $photoBinary = $this->getApplicantPhoto($inspectionId, $photoId);

            if (empty($photoBinary)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Фотография паспорта не получена', $_SESSION], self::LOG_FILE);
                continue;
            }

            $isPhotoSaved = $this->savePhoto($userId, $photoBinary, 'passport' . $fileCounter++);
            if (!$isPhotoSaved) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка при сохранении фотографии паспорта', $_SESSION], self::LOG_FILE);
            }
        }
    }

    /**
     * Получение и сохранение скана лица
     *
     * @param stdClass $applicantStatus
     * @param string $inspectionId
     * @param int $userId
     * @return void
     */
    private function saveFaceScanPhoto(stdClass $applicantStatus, string $inspectionId, int $userId): void
    {
        // face_1 скрыто в crm (зачем?), поэтому начинаем с 2
        $fileCounter = 2;

        // Получение и сохранение скана лица
        foreach ($applicantStatus->SELFIE->imageIds as $photoId) {
            $photoBinary = $this->getApplicantPhoto($inspectionId, $photoId);

            if (empty($photoBinary)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Скан лица не получен', $_SESSION], self::LOG_FILE);
                continue;
            }

            $isPhotoSaved = $this->savePhoto($userId, $photoBinary, 'face' . $fileCounter++);

            if (!$isPhotoSaved) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Ошибка при сохранении скана лица', $_SESSION], self::LOG_FILE);
            }
        }
    }

    /**
     * Получение фотографии паспорта и скана лица клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-document-images
     *
     * @param string $inspectionId
     * @param int $photoId
     * @return null|string (binary)
     */
    private function getApplicantPhoto(string $inspectionId, int $photoId): ?string
    {
        $url = '/resources/inspections/' . $inspectionId . '/resources/' . $photoId;
        $headers = $this->getHeaders($url, 'GET', [], false);
        $photoBinary = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        if (empty($photoBinary)) {
            return null;
        }

        return $photoBinary;
    }

    /**
     * Сохранение фото паспорта или скана лица
     *
     * @param int $userId
     * @param string $photoBinary
     * @param string $type
     * @return bool
     */
    public function savePhoto(int $userId, string $photoBinary, string $type): bool
    {
        $ext = 'png';

        do {
            $new_filename = md5(microtime() . rand()) . '.' . $ext;
        } while ($this->users->check_filename($new_filename));

        // Проверяем сохраняем в папку загрузки
        $file_local_path = $this->config->root_dir . $this->config->original_images_dir . $new_filename; // Пример: /var/www/html/files/users/1cfa200358a5a4c2f35c38beadac2c1c.png
        $file_uploaded = file_put_contents($file_local_path, $photoBinary);
        if (!$file_uploaded) {
            return false;
        }

        $s3_name = 'S3/' . date('Ymd') . '/' . $new_filename;

        $fileStorage = new FileStorageService(
            $this->config->s3['endpoint'],
            $this->config->s3['region'],
            $this->config->s3['key'],
            $this->config->s3['secret'],
            $this->config->s3['Bucket']
        );
        $fileStorage->putFile($file_local_path, $s3_name);

        $file_id = $this->users->add_file([
            'user_id' => $userId,
            'name' => $new_filename,
            'type' => $type,
            'status' => 0,
            's3_name' => $s3_name,
        ]);

        $returned = $this->soap->soap_send_files($userId);

        if ($returned->return == 'OK') {
            $this->users->update_file($file_id, ['status' => 1]);
        }

        return true;
    }

    /**
     * Получение статуса заявления клиента
     *
     * API: https://developers.cyberity.ru/api-reference/#getting-applicant-status-api
     *
     * @param string $applicantId
     * @return stdClass|null
     */
    private function getApplicantStatus(string $applicantId): ?stdClass
    {
        $url = '/resources/applicants/' . $applicantId . '/requiredIdDocsStatus';
        $headers = $this->getHeaders($url);
        $applicantStatus = $this->request(self::BASE_URL . $url, 'GET', [], $headers);

        return json_decode($applicantStatus);
    }

    /**
     * Сохранение этапа верификации
     *
     * @param string $newStatus
     * @return string
     */
    public function changeApplicationStatus(string $newStatus): string
    {
        $phone = $_SESSION['phone'];

        $userVerification = $this->cyberity->getUserVerification([
            'phone' => $phone
        ]);

        if (empty($userVerification)) {

            $verificationResponse = $this->short_flow->getVerificationResponse($phone);

            if (empty($verificationResponse)) {
                $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
                return json_encode(['success' => false]);
            }

            $this->cyberity->insertUserVerification([
                'phone' => $phone,
                'applicant_id' => $verificationResponse->id,
                'inspection_id' => $verificationResponse->inspectionId,
                'status' => $newStatus,
                'date_create' => date('Y-m-d H:i:s'),
                'date_update' => date('Y-m-d H:i:s'),
            ]);
        } else if ($newStatus !== $this->cyberity::STATUS_STARTED_APPLICATION && $userVerification->status !== $this->cyberity::STATUS_COMPLETED) {

            $update = [
                'status' => $newStatus,
                'date_update' => date('Y-m-d H:i:s'),
            ];

            if (empty($userVerification->applicant_id)) {
                $verificationResponse = $this->short_flow->getVerificationResponse($phone);

                if (empty($verificationResponse)) {
                    $this->logging(__METHOD__, '', $_REQUEST, ['Не удалось получить данные пользователя', $_SESSION], self::LOG_FILE);
                    return json_encode(['success' => false]);
                }

                $update['applicant_id'] = $verificationResponse->id;
                $update['inspection_id'] = $verificationResponse->inspectionId;
            }

            $this->cyberity->updateUserVerification((int)$userVerification->id, $update);
        }

        return json_encode(['success' => true]);
    }
}