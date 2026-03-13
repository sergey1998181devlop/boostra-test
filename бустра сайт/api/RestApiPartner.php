<?php

use api\helpers\UserHelper;
use RestApi\PartnerApi;

require_once 'Simpla.php';

/**
 * Класс для работы с Апи партнеров
 */
class RestApiPartner extends Simpla
{
    /** @var string Пользователь новый */
    public const CHECK_USER_RESPONSE_NEW = 'new';

    /** @var string Повторный пользователь (найден в БД) - пока такие не идут дальше по флоу, т.к. не оплачено в bankiru */
    public const CHECK_USER_RESPONSE_REPEAT = 'repeat';

    /** @var string Повторный пользователь (найден в 1С) */
    public const CHECK_USER_RESPONSE_FIND_1C = 'find_1c';

    /** @var string Пользователь отклонен */
    public const CHECK_USER_RESPONSE_CANCEL = 'cancel';

    private const REQUIRED_SCORINGS_TYPE = [
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_UPRID,
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_TERRORIST_CHECK,
    ];

    /**
     * Заявка в работе и создана
     */
    const STATUS_ORDER_PROCESSING = 'PROCESSING';

    /**
     * Заявка отклонена
     */
    const STATUS_ORDER_DECLINED = 'DECLINED';

    /** @var string Заявка одобрена по скорингам */
    const STATUS_ORDER_PRE_APPROVED = 'PRE-APPROVED';

    /**
     * Заявка одобрена верификатором
     */
    const STATUS_ORDER_APPROVED = 'APPROVED';
    /**
     * Заявка выдана
     */
    const STATUS_ORDER_ISSUED = 'ISSUED';

    /**
     * Базовый процент для расчета максимальной суммы кредита
     */
    const BASE_PERCENT = 0.8;

    /**
     * Базовая сумма для расчета максимальной суммы кредита
     */
    const BASE_AMOUNT = 300000;

    /**
     * Базовый период для расчета максимальной суммы кредита
     */
    const BASE_PERIOD = 16;

    /**
     * Тег для логов
     */
    private const LOG_TAG = 'rest_partner_api_model';

    /**
     * Utm метка партнерского апи
     */
    public const UTM_TERM = 'partner-api';

    /**
     * 1. Проверяем пользователя в базе и в 1с
     *
     * @param array $data ['phone_mobile' => 79672333822, 'passport_serial' => '4819337594', 'email' => 'test@test.ru']
     * @return string Один из возможных статусов пользователя:
     *
     *  "new" Пользователь новый
     *
     *  "decline" Пользователь отклонен
     *
     *  "repeat" Повторный пользователь
     */
    public function checkUser(array $data): string
    {
        if (!$this->validatePhoneAndPassport($data['phone_mobile'], $data['passport_serial'])) {
            $this->open_search_logger->create('Проверка validatePhoneAndPassport не прошла', [
                'phone_mobile' => $data['phone_mobile'],
                'passport_serial' => $data['passport_serial'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
        }

        $phone_mobile = $this->formatPhone($data['phone_mobile']);
        $passport_serial = $this->formatPassport($data['passport_serial']);

        // 1. Поиск в БД по номеру телефону
        $countUsersByPhone = $this->users->getUsersCountByPhone($phone_mobile);

        if ($this->isMultipleUsersFound($countUsersByPhone)) {
            $this->open_search_logger->create('Найдено несколько пользователей по телефону', [
                'phone_mobile' => $phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
        }

        // 2. Поиск в БД по номеру паспорта
        $countUsersByPassport = $this->users->getUsersCountByPassport($passport_serial);

        if ($this->isMultipleUsersFound($countUsersByPassport)) {
            $this->open_search_logger->create('Найдено несколько пользователей по паспорту', [
                'phone_mobile' => $phone_mobile,
                'passport_serial' => $passport_serial,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
        }

        // 3. Поиск в 1C по номеру телефона
        $soapUserUidByPhoneResult = $this->getUserUidFrom1C($phone_mobile);
        $usersInDbByPhone = $this->users->get_user($phone_mobile);
        $usersInDbByPassport = $this->users->getUserByPassport($passport_serial);

        // new: пользователь не найден ни в БД, ни в 1С
        if (!$this->isUserExist($usersInDbByPhone, $usersInDbByPassport, $soapUserUidByPhoneResult)) {
            $this->open_search_logger->create('Проверка isUserExist прошла успешно, отдаем ответ о новом пользователе', [
                'phone_mobile' => $phone_mobile,
                'usersInDbByPhone' => !empty($usersInDbByPhone),
                'usersInDbByPassport' => !empty($usersInDbByPassport),
                'soapUserUidByPhoneResult' => !empty($soapUserUidByPhoneResult),
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            $this->ping3_data->addPing3Data($this->ping3_data::USER_TYPE, $phone_mobile, $this->ping3_data::USER_TYPE_NEW);
            return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_NEW, $data);
        }

        $userInDbByPhone = $usersInDbByPhone;
        $userInDbByPassport = $usersInDbByPassport;

        if (!$this->isTheSameUser($userInDbByPhone, $userInDbByPassport, $soapUserUidByPhoneResult, $passport_serial)) {
            $this->open_search_logger->create('Найдено несколько совпадений по разным данным', [
                'phone_mobile' => $phone_mobile,
                'passport_serial' => $passport_serial,
                'userInDbByPhone' => !empty($userInDbByPhone),
                'usersInDbByPassport' => !empty($usersInDbByPassport),
                'soapUserUidByPhoneResult' => !empty($soapUserUidByPhoneResult),
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
        }

        // пользователь есть в БД тут обработка тех кто есть в базе
        if (!empty($userInDbByPhone)) {
            $this->ping3_data->addPing3Data($this->ping3_data::USER_TYPE, $phone_mobile, $this->ping3_data::USER_TYPE_REPEAT);

            // Проверим, заявки пользователя
            $orders = $this->orders->get_orders(['user_id' => (int)$userInDbByPhone->id]);

            // Есть активная заявка
            if ($order = $this->userHasActiveOrder($orders, $phone_mobile)) {
                $status = $this->checkActiveOrder($order);
                $this->open_search_logger->create("При проверке userHasActiveOrder, принято решение - $status", [
                    'phone_mobile' => $phone_mobile,
                    'order_id' => $order->id,
                    'status' => $order->status,
                    '1c_status' => $order->status_1c,
                    'utm_source' => $order->utm_source,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

                // Если статус повторника и есть автозаявка, обработаем ее в дальнейшем
                if ($status === self::CHECK_USER_RESPONSE_REPEAT) {
                    $property_auto_order = $order->utm_source === Orders::UTM_SOURCE_CROSS_ORDER ? $this->ping3_data::REPEAT_HAS_CRM_CROSS_ORDER : $this->ping3_data::REPEAT_HAS_CRM_AUTO_APPROVE;
                    $this->ping3_data->addPing3Data($property_auto_order, $userInDbByPhone->id, $order->id);
                }

                return $this->returnCheckUserResponse($status, $data);
            }

            // Проверим ЧС
            if ($this->users->checkUserBlackList($userInDbByPhone)) {
                $this->open_search_logger->create('Пользователь в ЧС', [
                    'phone_mobile' => $phone_mobile,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
            }

            // Проверим мораторий
            if ($this->users->hasUserMoratorium($userInDbByPhone)) {
                $this->open_search_logger->create('Найден мораторий по пользователю', [
                    'user_id' => $userInDbByPhone->id,
                    'phone_mobile' => $phone_mobile,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
            }

            // Без заявок отправляем его как нового клиента
            if (empty($orders)) {
                $this->open_search_logger->create('У пользователя который есть в базе, нет заявок', [
                    'phone_mobile' => $phone_mobile,
                    'user_id' => $userInDbByPhone->id,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_NEW, $data);
            }

            $lastOrder = $this->orders->get_last_order((int)$userInDbByPhone->id);
            // Проверим мораторий по заявке
            if ((int)$lastOrder->status === Orders::ORDER_STATUS_CRM_REJECT && $this->users->hasReasonBasedMoratorium($lastOrder)) {
                $this->open_search_logger->create('Найден мораторий по заявке', [
                    'order_id' => $lastOrder->id,
                    'phone_mobile' => $phone_mobile,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
            }

            $this->open_search_logger->create('Обработка ПК, который есть в базе', [
                'phone_mobile' => $phone_mobile,
                'user_id' => $userInDbByPhone->id,
                'have_close_credits' => $lastOrder->have_close_credits,
                'last_order_id' => $lastOrder->id,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            // Проверяем флажок пользователя на ПК, НК
            if ($lastOrder->have_close_credits == 1) {
                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_REPEAT, $data);
            } else {
                return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_NEW, $data);
            }
        }

        // repeat: пользователь есть в 1C
        if (!empty($soapUserUidByPhoneResult->uid)) {
            //  Т.к. пользователь найден в 1С, но его нет в базе данных отметим это и сохраним себе
            $this->ping3_data->addPing3Data($this->ping3_data::USER_FIND_1C, $phone_mobile, 1);

            // Проверим кредитную историю в 1С
            $credits_history = $this->soap->get_user_credits($soapUserUidByPhoneResult->uid);
            $have_close_credits = !empty($credits_history);

            if ($have_close_credits) {
                $status = $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_REPEAT, $data);
            } else {
                $status = $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_NEW, $data);
            }

            $this->open_search_logger->create('Пользователь найден в 1С', [
                'have_close_credits' => $have_close_credits,
                'phone_mobile' => $phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            return $status;
        }

        return $this->returnCheckUserResponse(self::CHECK_USER_RESPONSE_CANCEL, $data);
    }

    /**
     * @param stdClass $order
     * @return string
     */
    private function checkActiveOrder(stdClass $order): string
    {
        // Если любой статус кроме одобренной, возвращаем cancel
        if ((int)$order->status !== Orders::ORDER_STATUS_CRM_APPROVED) {
            return self::CHECK_USER_RESPONSE_CANCEL;
        }

        // Если не автозаявка и не крос ордер, в отказ
        if (!in_array($order->utm_source, [ Orders::UTM_RESOURCE_AUTO_APPROVE,  Orders::UTM_SOURCE_CROSS_ORDER])) {
            return self::CHECK_USER_RESPONSE_CANCEL;
        }

        // Для надежности проверим статус 1с, что она одобрена
        if ($order->status_1c === Orders::ORDER_1C_STATUS_APPROVED) {
            return self::CHECK_USER_RESPONSE_REPEAT;
        }

        // Если не автозаявка, отбиваем отказ
        return self::CHECK_USER_RESPONSE_CANCEL;
    }

    /**
     * Проверка на активную заявку
     * @param $orders
     * @param $phone_mobile
     * @return mixed
     */

    private function userHasActiveOrder($orders, $phone_mobile)
    {
        if (empty($orders)) {
            return false;
        }

        foreach ($orders as $order) {
            if ($this->isOrderActive($order)) {
                $this->open_search_logger->create('Найдена активная заявка userHasActiveOrder', [
                    'order_id' => $order->id,
                    'phone_mobile' => $phone_mobile,
                    'status' => $order->status,
                    '1c_status' => $order->status_1c,
                    '1c_id' => $order->id_1c,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
                return $order;
            }
        }

        return false;
    }

    private function validatePhoneAndPassport($phone_mobile, $passport_serial): bool
    {
        if (empty($phone_mobile) || empty($passport_serial)) {
            return false;
        }

        return true;
    }

    private function formatPhone(string $phone): string
    {
        return $this->users->clear_phone($phone, '7');
    }

    private function formatPassport(string $passport): string
    {
        return preg_replace('/\D+/', '', $passport);
    }

    private function getUserUidFrom1C(string $phone): ?stdClass
    {
        try {
            $soapUserUidByPhoneResult = $this->soap->get_uid_by_phone($phone);
        } catch (SoapFault $error) {
            $this->open_search_logger->create('Некорректный ответ из 1С в catch get_uid_by_phone', [
                'phone_mobile' => $phone,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_WARNING, 'ping3');
            return null;
        }

        $this->open_search_logger->create('Ответ из 1C по get_uid_by_phone', [
            'response' => $soapUserUidByPhoneResult,
            'phone_mobile' => $phone,
            'request_uid' => PartnerApi::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        if (!isset($soapUserUidByPhoneResult->result) || empty($soapUserUidByPhoneResult->uid)) {
            $this->open_search_logger->create('Некорректный ответ из 1С get_uid_by_phone', [
                'response' => $soapUserUidByPhoneResult,
                'phone_mobile' => $phone,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            return null;
        }

        return $soapUserUidByPhoneResult;
    }

    private function isMultipleUsersFound(int $total): bool
    {
        if ($total > 1) {
            return true;
        }

        return false;
    }

    private function isUserExist(
        $usersInDbByPhone,
        $usersInDbByPassport,
        $soapUserUidByPhoneResult
    ): bool
    {
        return !empty($usersInDbByPhone) || !empty($usersInDbByPassport) || !empty($soapUserUidByPhoneResult->uid);
    }

    /**
     * Проверяем, что пользователь, найденный по номеру телефона в БД, по номеру паспорта в БД и по номеру
     * телефона в 1С один и тот же
     */
    private function isTheSameUser(
        $userInDbByPhone,
        $userInDbByPassport,
        $soapUserUidByPhoneResult,
        string $passportSerialFromRequest
    ): bool
    {
        // Если найден пользователь по номеру телефона в БД, то проверяем, что его номер паспорта такой же
        if (!empty($userInDbByPhone)) {
            $passportSerialIDb = $this->formatPassport($userInDbByPhone->passport_serial);
            if ($passportSerialIDb !== $passportSerialFromRequest) {
                return false;
            }

            if (!empty($soapUserUidByPhoneResult)) {
                if ($userInDbByPhone->uid !== $soapUserUidByPhoneResult->uid) {
                    return false;
                }
            }

            return true;
        }

        if (!empty($userInDbByPassport)) {
            return false;
        }

        return true;
    }

    private function addUserToDb(array $params): int
    {
        $currentDate = date('Y-m-d H:i:s');

        $registrationSteps = [
            'personal_data_added' => 0,
            'address_data_added' => 0,
            'accept_data_added' => 1,
            'accept_data_added_date' => $currentDate,
            'card_added' => 0,
            'files_added' => 0,
            'additional_data_added' => 0,
            'additional_data_added_date' => $currentDate,
        ];

        $params = array_merge($params, $registrationSteps);

        if (!empty($params['lastname']) && !empty($params['firstname'])) {
            $params['personal_data_added'] = 1;
            $params['personal_data_added_date'] = $currentDate;
        }

        if (
            !empty($params['Regregion']) &&
            !empty($params['Regcity']) &&
            !empty($params['Faktregion']) &&
            !empty($params['Faktcity'])
        ) {
            $params['address_data_added'] = 1;
            $params['address_data_added_date'] = $currentDate;
        }

        if (
            !empty($params['profession']) &&
            !empty($params['workplace']) &&
            !empty($params['work_address']) &&
            !empty($params['income_base'])
        ) {
            $params['additional_data_added'] = 1;
            $params['additional_data_added_date'] = $currentDate;
        }

        $params['phone_mobile'] = $this->formatPhone($params['phone_mobile']);
        $params['passport_serial'] = $this->formatPassport($params['passport_serial']);
        $params['passport_serial'] = $this->users->tryFormatPassportSerial($params['passport_serial']);

        // Создание пользователя
        $userId = $this->users->add_user($params);

        $this->open_search_logger->create('Данные при создании пользователя addUserToDb', [
            'add_user_data' => $params,
            'user_id' => $userId,
            'phone_mobile' => $params['phone_mobile'],
            'request_uid' => PartnerApi::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        // Добавление флагов, чтобы заявка шла по флоу автовыдачи 2.0
        $this->setAutoConfirm2Flags((int)$userId);

        return (int)$userId;
    }

    /**
     * Добавление флагов, чтобы клиент шел по флоу автовыдачи 2.0
     *
     * @param int $userId
     * @return void
     */
    /**
     * Добавление флагов, чтобы клиент шел по флоу автовыдачи 2.0
     *
     * @param int $userId
     * @return void
     */
    private function setAutoConfirm2Flags(int $userId)
    {
        $activeFlow = $this->user_data->read($userId, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW);
        $hasAutoConfirmFlow = $this->user_data->read($userId, $this->user_data::AUTOCONFIRM_FLOW);
        $hasAutoConfirm2Flow = $this->user_data->read($userId, $this->user_data::AUTOCONFIRM_2_FLOW);

        // Если активный флоу установлен, но флаги удалены - шаг подписания уже пройден
        if (!empty($activeFlow) && empty($hasAutoConfirmFlow) && empty($hasAutoConfirm2Flow)) {
            return;
        }

        $this->user_data->set($userId, $this->user_data::IS_REJECTED_NK, 0);
        $this->user_data->set($userId, $this->user_data::AUTOCONFIRM_FLOW, 1);
        $this->user_data->set($userId, $this->user_data::AUTOCONFIRM_2_FLOW, 1);
        $this->user_data->set($userId, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW, 'AUTOCONFIRM_2_FLOW');
    }


    private function importUserFrom1C(int $userId, string $userUid)
    {
        try {
            $details = $this->soap->get_client_details($userUid);
            $this->import1c->import_user($userId, $details);
        } catch (SoapFault $error) {
            $this->open_search_logger->create('Возникла ошибка при импорте пользователя из 1С', [
                'uid' => $userUid,
                'user_id' => $userId,
                'error' => $error->getMessage(),
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_WARNING, 'ping3');
        }
    }

    private function returnCheckUserResponse(string $response, array $data): string
    {
        return $response;
    }

    /**
     * 2. Добавляем данные по анкете, которую прислали
     *
     * @param array $userData
     * @return int user_id
     */
    public function addUser(array $userData): int
    {
        if (empty($userData['firstname']) || empty($userData['lastname']) || empty($userData['phone_mobile'])) {
            return $this->returnAddUserResponse(0);
        }

        // Если найден в 1с, обработаем его и сделаем импорт
        if ($this->ping3_data->getPing3Data($this->ping3_data::USER_FIND_1C, $userData['phone_mobile'])) {
            $this->open_search_logger->create('AddUser добавление нового пользователя из 1С', [
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->handleFind1CResponse($userData);
        }

        $userType = $this->ping3_data->getPing3Data($this->ping3_data::USER_TYPE, $userData['phone_mobile']);

        if (!is_numeric($userType) || !in_array((int)$userType, [$this->ping3_data::USER_TYPE_NEW, $this->ping3_data::USER_TYPE_REPEAT])) {
            $this->open_search_logger->create('AddUser дне найден признак в базе ping3_data::USER_TYPE', [
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnAddUserResponse(0);
        }

        // Если новый клиент
        if ((int)$userType === $this->ping3_data::USER_TYPE_NEW) {
            $this->open_search_logger->create('AddUser обработка нового пользователя которого не было в базе', [
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->handleNewResponse($userData);
        }

        // Если повторный клиент
        if ((int)$userType === $this->ping3_data::USER_TYPE_REPEAT) {
            $this->open_search_logger->create('AddUser обработка повторного пользователя который был в базе', [
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->handleRepeatResponse($userData);
        }

        return 0;
    }

    private function handleNewResponse(array $userData): int
    {
        $user = $this->users->get_user($userData['phone_mobile']);
        if (!empty($user)) {
            $this->open_search_logger->create('Пользователь найден в базе по номеру телефона', [
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnAddUserResponse(0);
        }

        // Создаем пользователя данными от партнера
        $userId = $this->addUserToDb($userData);
        $this->user_data->set($userId, $this->user_data::PARTNER_USER_RESPONSE, self::CHECK_USER_RESPONSE_NEW);
        return $this->returnAddUserResponse($userId);
    }

    private function handleRepeatResponse(array $userData): int
    {
        $user = $this->users->get_user((string)$userData['phone_mobile']);

        if (empty($user)) {
            return $this->returnAddUserResponse(0);
        }

        // Обновляем пользователя данными от партнера
        $this->users->update_user((int)$user->id, $userData);
        $this->user_data->set((int)$user->id, $this->user_data::PARTNER_USER_RESPONSE, self::CHECK_USER_RESPONSE_REPEAT);
        $user = $this->users->get_user((int)$user->id);

        // Актуализируем все данные, и проверим обязательность полей
        if (!$this->users->validateUserFields($user)) {
            $requiredFields = UserHelper::REQUIRE_FIELDS;
            $badFields = [];

            foreach ($requiredFields as $keyField => $fields) {
                if (empty($user->$keyField)) {
                    $badFields[] = $keyField;
                }

                if (empty($fields)) {
                    continue;
                }

                foreach ($fields as $field) {
                    if (empty($user->$field)) {
                        $badFields[] = $field;
                    }
                }
            }

            $this->open_search_logger->create('При проверке validateUserFields найдены отсутствующие поля, вызов handleRepeatResponse', [
                'user_id' => $user->id,
                'phone_mobile' => $userData['phone_mobile'],
                'bad_repeat_user_fields' => $badFields,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
        }

        // Если пользователь отвал
        if (!$this->users->isRegistrationCompleted($user)) {
            $this->setAutoConfirm2Flags((int)$user->id);
        }

        $this->validateStepPhotoRepeatClient($user);

        $this->open_search_logger->create('Данные при обновлении пользователя handleRepeatResponse', [
            'update_user_data' => $userData,
            'user_id' => $user->id,
            'phone_mobile' => $userData['phone_mobile'],
            'request_uid' => PartnerApi::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        return $this->returnAddUserResponse((int)$user->id);
    }

    /**
     * Валидируем шаг с фото, для старых клиентов
     *
     * @param object $user
     * @return void
     */
    private function validateStepPhotoRepeatClient(object $user): void
    {
        // Проверяем фото в базе данных
        if (!empty($user->files_added)) {
            $files = $this->users->get_user_files((int)$user->id);
            $hasScoristaSkipFiles = $this->user_data->read($user->id, $this->user_data::FLAG_STEP_FILES);

            // Если флаг проставлен, а фото нет и нет шага от скористы на пропуск сбросим шаг анкеты
            if (empty($files) && empty($hasScoristaSkipFiles)) {
                $this->users->update_user((int)$user->id, ['files_added' => 0]);
            }
        }
    }

    private function handleFind1CResponse(array $userData): int
    {
        $user = $this->users->get_user($userData['phone_mobile']);
        if (!empty($user)) {
            $this->open_search_logger->create('В ходе импорта handleFind1CResponse из 1С, был найден пользователь', [
                'user_id' => $user->id,
                'phone_mobile' => $userData['phone_mobile'],
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnAddUserResponse($user->id);
        }

        // Создаем пользователя данными от партнера
        $userId = $this->addUserToDb($userData);

        // Импортируем данные из 1С
        $soapUserUidByPhoneResult = $this->getUserUidFrom1C($userData['phone_mobile']);

        if (!empty($soapUserUidByPhoneResult->uid)) {
            $this->importUserFrom1C($userId, $soapUserUidByPhoneResult->uid);

            // Снова обновляем пользователя данными от партнера
            $this->users->update_user($userId, $userData);
        }

        $this->user_data->set($userId, $this->user_data::PARTNER_USER_RESPONSE, self::CHECK_USER_RESPONSE_FIND_1C);

        if ($userId) {
            $user = $this->users->get_user($userId);
            $this->validateStepPhotoRepeatClient($user);
        }

        return $this->returnAddUserResponse($userId);
    }

    private function returnAddUserResponse(int $userId): int
    {
        return $userId;
    }

    /**
     * 3. Добавляем заявку
     *
     * @param array $data
     * @param string $partner utm метка партнера
     * @return int|null order_id
     * @throws SoapFault
     */
    public function addOrder(array $data, string $partner): ?int
    {
        $user = $this->users->get_user((int)$data['user_id']);

        if (empty($user)) {
            return $this->returnAddOrderResponse(null);
        }

        $orders = $this->orders->get_orders([
            'user_id' => (int)$user->id,
        ]);

        if ($find_active_order = $this->userHasActiveOrder($orders, $user->phone_mobile)) {
            // Если есть заявка от партнера вернем ее
            if ($find_active_order->utm_source === $data['utm_source']) {
                return $this->returnAddOrderResponse($find_active_order->id);
            } else {
                $this->open_search_logger->create('Найдена активная заявка в AddOrder', [
                    'order_id' => $find_active_order->id,
                    'utm_source' => $data['utm_source'],
                    'phone_mobile' => $user->phone_mobile,
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
                return $this->returnAddOrderResponse(null);
            }
        }

        if (!empty($orders)) {
            foreach ($orders as $order) {
                // Проверим мораторий
                if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
                    if ($this->users->hasActiveMoratorium($user, $order)) {
                        $this->open_search_logger->create('Найдена мораторий по заявке заявка', [
                            'order_id' => $order->id,
                            'utm_source' => $data['utm_source'],
                            'phone_mobile' => $user->phone_mobile,
                            'request_uid' => PartnerApi::$request_uid,
                        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
                        return $this->returnAddOrderResponse(null);
                    }
                }
            }
        }

        // Создание заявки
        $orderId = $this->orders->add_order($data);
        if (empty($orderId)) {
            $this->open_search_logger->create('Ошибка создания заявки', [
                'data' => $data,
                'phone_mobile' => $user->phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return $this->returnAddOrderResponse((int)$orderId);
        }

        $this->open_search_logger->create('Успешное создание новой заявки в AddOrder', [
            'order_id' => $orderId,
            'new_order_data' => $data,
            'request_uid' => PartnerApi::$request_uid,
        ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

        $this->order_data->set((int)$orderId, $this->order_data::ORDER_FROM_PARTNER, $partner);
        $order = $this->orders->get_order($orderId);

        // Добавление заявки в 1C
        $this->addOrderTo1C($order, $user);

        // Добавим скоринги
        $this->addScorings((int)$orderId, $user);

        // Проставим complete
        $this->setComplete($order, $user);

        return $this->returnAddOrderResponse((int)$orderId);
    }

    /**
     * Показываем заявку верификаторам
     *
     * @param stdClass $order
     * @param stdClass $user
     * @return void
     * @throws SoapFault
     */
    private function setComplete(stdClass $order, stdClass $user)
    {
        if (empty($order->id_1c)) {
            $this->open_search_logger->create('Ошибка флага complete, отсутсвует id 1c', [
                'order_id' => $order->id,
                'phone_mobile' => $user->phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return;
        }

        // Если все поля заполнены в анкете показываем заявку верификаторам, в этом случае у нас автовыдачи нет!
        // потому что смс пользователя мы не можем получить
        $userRegistrationComplete = $this->users->isRegistrationCompleted($user);
        if ($userRegistrationComplete) {
            $this->open_search_logger->create('Проставлен complete', [
                'order_id' => $order->id,
                'phone_mobile' => $user->phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            $this->soap->set_order_complete($order->id);
        }
    }

    /**
     * Имеет ли пользователь открытую заявку
     *
     * @param stdClass $order
     * @return bool
     */
    private function isOrderActive(stdClass $order): bool
    {
        // Заявка отказана
        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
            return false;
        }

        // Заявка закрыта
        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_ISSUED && in_array($order->status_1c, [
                $this->orders::ORDER_1C_STATUS_CLOSED,
                $this->orders::ORDER_1C_STATUS_REJECTED_TECH,
                $this->orders::ORDER_1C_STATUS_REJECTED,
                $this->orders::ORDER_1C_STATUS_UNDEFINED,
            ])) {
            return false;
        }

        return true;
    }

    /**
     * Отправка заявки в 1С
     *
     * @param stdClass $order
     * @param stdClass $user
     * @return void
     * @throws SoapFault
     */
    private function addOrderTo1C(stdClass $order, stdClass $user): void
    {
        if ($this->hasOtherOrders($order->id, $user->id)) {
            $this->sendRepeatOrderTo1C($order);
        } else {
            $this->sendNewOrderTo1C($order, $user);
        }
    }

    /**
     * Отправка повторной заявки в 1С
     * @param stdClass $order
     * @return void
     * @throws SoapFault
     */
    private function sendRepeatOrderTo1C(stdClass $order)
    {
        $soap_zayavka = $this->soap->send_repeat_zayavka(
            [
                'amount' => $order->amount,
                'period' => $order->period,
                'user_id' => $order->user_id,
                'card' => $this->orders::CARD_TYPE_CARD,
                'b2p' => $order->b2p,
                'order_uid' => $order->order_uid,
                'organization_id' => $order->organization_id,
                'utm_source' => $order->utm_source,
                'utm_medium' => $order->utm_medium,
                'utm_campaign' => $order->utm_campaign,
                'utm_content' => $order->utm_content,
                'utm_term' => $order->utm_term,
                'webmaster_id' => $order->webmaster_id,
                'click_hash' => $order->click_hash,
            ]
        );

        if (empty($soap_zayavka->return->id_zayavka)) {
            $this->open_search_logger->create('Ошибка отправки повторной заявки в 1С', [
                'order_id' => $order->id,
                'response' => $soap_zayavka,
                'user_id' => $order->user_id,
                'request_uid' => PartnerApi::$request_uid,
            ],self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            $this->orders->update_order($order->id, [
                    'status' => $this->orders::ORDER_STATUS_CRM_REJECT,
                    'note' => strval($soap_zayavka->return->Error)
                ]
            );
        } elseif(str_contains($soap_zayavka->return->id_zayavka, 'Не принято')) {
            $this->open_search_logger->create('Ошибка отправки повторной заявки в 1С, не принято', [
                'order_id' => $order->id,
                'response' => $soap_zayavka,
                'user_id' => $order->user_id,
                'request_uid' => PartnerApi::$request_uid,
            ],self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            $this->orders->update_order($order->id, ['status' => $this->orders::ORDER_STATUS_CRM_REJECT]);
        } else {
            $order->id_1c = $soap_zayavka->return->id_zayavka;
            $this->orders->update_order($order->id, ['1c_id' => $soap_zayavka->return->id_zayavka, 'status' => $this->orders::ORDER_STATUS_CRM_NEW]);
        }
    }

    /**
     * Отправка новой заявки в 1с
     * @param stdClass $order
     * @param stdClass $user
     * @return void
     */
    private function sendNewOrderTo1C(stdClass $order, stdClass $user)
    {
        $orderId = $order->id;

        $recommendationAmount = $this->users->get_recomendation_amount($user);
        if ($recommendationAmount <= 0) {
            $this->open_search_logger->create('Не найдена рекомендованная сумма при отправки новой заявки', [
                'order_id' => $order->id,
                'phone_mobile' => $user->phone_mobile,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            return;
        }

        $this->order_data->set($orderId, $this->order_data::USER_AMOUNT, $user->first_loan_amount ?: $recommendationAmount);

        $organization = $this->organizations->get_organization($order->organization_id);
        $order_1c = [
            'УИД' => $order->order_uid,
            'ДатаЗаявки' => date('YmdHis', strtotime($order->date)),
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
            'Regregion' => (string)trim($user->Regregion . ' ' . $user->Regregion_shorttype),
            'Regdistrict' => (string)$user->Regdistrict,
            'Regcity' => (string)trim($user->Regcity . ' ' . $user->Regcity_shorttype),
            'Reglocality' => '',
            'Regstreet' => (string)trim($user->Regstreet . ' ' . $user->Regstreet_shorttype),
            'Regbuilding' => (string)$user->Regbuilding,
            'Reghousing' => (string)$user->Reghousing,
            'Regroom' => (string)$user->Regroom,

            'АдресФактическогоПроживанияИндекс' => (string)$user->Faktindex,
            'Faktregion' => (string)trim($user->Faktregion . ' ' . $user->Faktregion_shorttype),
            'Faktdistrict' => (string)$user->Faktdistrict,
            'Faktcity' => (string)trim($user->Faktcity . ' ' . $user->Faktcity_shorttype),
            'Faktlocality' => '',
            'Faktstreet' => (string)trim($user->Faktstreet . ' ' . $user->Faktstreet_shorttype),
            'Faktbuilding' => (string)$user->Faktbuilding,
            'Fakthousing' => (string)$user->Fakthousing,
            'Faktroom' => (string)$user->Faktroom,

            'site_id' => 'Boostra',
            'partner_id' => '',
            'partner_name' => 'Boostra',

            'amount' => (string)$recommendationAmount,
            'period' => empty($user->first_loan_period) ? 16 : (string)$user->first_loan_period,

            'utm_source' => $order->utm_source,
            'utm_medium' => $order->utm_medium,
            'utm_campaign' => $order->utm_campaign,
            'utm_content' => $order->utm_content,
            'utm_term' => $order->utm_term,
            'webmaster_id' => $order->webmaster_id,
            'click_hash' => $order->click_hash,

            'id' => '',
            'car' => '',
            'IntervalNumber' => '',
            'СтатусCRM' => '',
            'СуммаCRM' => (string)$recommendationAmount,
            'УИД_CRM' => $order->order_uid,

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
        if ($contactpersons = $this->contactpersons->get_contactpersons(['user_id' => $user->id])) {
            foreach ($contactpersons as $contactperson) {
                $contact_person_name[] = (string)$contactperson->name;
                $contact_person_phone[] = (string)$contactperson->phone;
                $contact_person_relation[] = (string)$contactperson->relation;
            }
        }
        $order_1c['КонтактноеЛицоФИО'] = json_encode($contact_person_name);
        $order_1c['КонтактноеЛицоТелефон'] = json_encode($contact_person_phone);
        $order_1c['КонтактноеЛицоРодство'] = json_encode($contact_person_relation);

        if ($user->work_scope == 'Пенсионер') {
            $order_1c['Занятость'] = '';
            $order_1c['Профессия'] = '';
            $order_1c['МестоРаботы'] = '';
            $order_1c['СтажРаботы'] = '';
            $order_1c['ШтатРаботы'] = '';
            $order_1c['ТелефонОрганизации'] = '';
            $order_1c['ФИОРуководителя'] = '';
            $order_1c['АдресРаботы'] = '';
        } else {
            $order_1c['Занятость'] = (string)$user->employment;
            $order_1c['Профессия'] = (string)$user->profession;
            $order_1c['МестоРаботы'] = (string)$user->workplace;
            $order_1c['СтажРаботы'] = (string)$user->experience;
            $order_1c['ШтатРаботы'] = (string)$user->work_staff;
            $order_1c['ТелефонОрганизации'] = (string)$user->work_phone;
            $order_1c['ФИОРуководителя'] = (string)$user->workdirector_name;
            $order_1c['АдресРаботы'] = $user->Workindex . ' ' . $user->Workregion . ', ' . $user->Workcity . ', ул.' . $user->Workstreet . ', д.' . $user->Workhousing;
            if (!empty($user->Workbuilding))
                $order_1c['АдресРаботы'] .= '/' . $user->Workbuilding;
            if (!empty($user->Workroom))
                $order_1c['АдресРаботы'] .= ', оф.' . $user->Workroom;
        }

        $order_1c = (object)$order_1c;
        $resp = $this->soap->send_loan($order_1c);
        if ($resp->return->id_zayavka == 'Не принято') {
            sleep(3);
            $resp = $this->soap->send_loan($order_1c);
        }

        $j = 10;
        do {
            sleep(2);

            // Получение и сохранение s_users.UID из 1С
            try {
                $uid_resp = $this->soap->get_uid_by_phone($user->phone_mobile);
            } catch (SoapFault $error) {
                $this->open_search_logger->create('Не получилось создать пользователя при отправки новой заявки', [
                    'order_id' => $order->id,
                    'phone_mobile' => $user->phone_mobile,
                    'error' => $error->getMessage(),
                    'request_uid' => PartnerApi::$request_uid,
                ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
            }

            $j--;
        } while (empty($uid_resp->uid) && $j > 0);

        if (!empty($uid_resp->uid)) {
            $order->id_1c = $resp->return->id_zayavka;
            $this->orders->update_order($orderId, ['status' => $this->orders::ORDER_STATUS_CRM_NEW, '1c_id' => $resp->return->id_zayavka]);
            $this->users->update_user($user->id, ['uid' => $uid_resp->uid]);
        } else if (isset($uid_resp)) {
            $this->open_search_logger->create('Не получилось создать пользователя при отправки новой заявки', [
                'order_id' => $order->id,
                'phone_mobile' => $user->phone_mobile,
                'response' => $uid_resp,
                'request_uid' => PartnerApi::$request_uid,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');
        }
    }

    private function returnAddOrderResponse(?int $orderId):? int
    {
        return $orderId;
    }

    /**
     * 4. Добавляем основные скоринги
     *
     * @param int $orderId
     * @param stdClass $user
     * @return void
     */
    private function addScorings(int $orderId, stdClass $user): void
    {
        $scorings = $this->scorings->get_scorings([
            'order_id' => $orderId,
        ]);

        if (!empty($scorings)) {
            return;
        }

        foreach (self::REQUIRED_SCORINGS_TYPE as $requiredScoringType) {
            $this->scorings->add_scoring([
                'order_id' => $orderId,
                'user_id' => $user->id,
                'status' => $this->scorings::STATUS_NEW,
                'created' => date('Y-m-d H:i:s'),
                'type' => $requiredScoringType,
            ]);
        }
    }

    /**
     * 5. Проверка решения по заявке
     *
     * @param int $orderId
     * @return string
     *
     * "DUPLICATE",
     * "PROCESSING" - Скоринги в работе,
     * "DECLINED" - Заявка отклонена,
     * "APPROVED" - Заявка одобрена,
     * "PRE-APPROVED",
     * "ISSUED" - Заявка выдана
     */
    public function checkOrder(int $orderId): string
    {
        $order = $this->orders->get_order($orderId);

        if (empty($order)) {
            return $this->returnCheckOrderResponse(self::STATUS_ORDER_PROCESSING, ['order_id' => $orderId]);
        }

        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
            return $this->returnCheckOrderResponse(self::STATUS_ORDER_DECLINED, ['order' => $order]);
        }

        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_APPROVED) {
            return $this->returnCheckOrderResponse(self::STATUS_ORDER_APPROVED, ['order' => $order]);
        }

        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_ISSUED) {
            return $this->returnCheckOrderResponse(self::STATUS_ORDER_ISSUED, ['order' => $order]);
        }

        $scorings = $this->scorings->get_scorings([
            'order_id' => $orderId,
             'type' => Scorings::TYPE_AXILINK_2,
        ]);

        $finishedScoringsStatuses = [
            $this->scorings::STATUS_STOPPED,
            $this->scorings::STATUS_COMPLETED,
            $this->scorings::STATUS_ERROR,
        ];

        foreach ($scorings as $scoring) {
            if (!in_array((int)$scoring->status, $finishedScoringsStatuses)) {
                return $this->returnCheckOrderResponse(self::STATUS_ORDER_PROCESSING, ['order' => $order]);
            }
        }

        if ($approved_scoring = $this->getApprovedScoring($scorings)) {
            // Если заявка одобрена скорингом проставим и проверим срок
            $autoconfirm_amount = $this->scorings->getAmountByTypeFromBodyScoring($approved_scoring);
            if ($autoconfirm_amount) {
                $this->orders->update_order($orderId, ['period' => $autoconfirm_amount <= 30000 ? self::BASE_PERIOD : 84]);
            }

            return $this->returnCheckOrderResponse(self::STATUS_ORDER_PRE_APPROVED, ['order' => $order]);
        }

        return $this->returnCheckOrderResponse(self::STATUS_ORDER_PROCESSING, ['order' => $order]);
    }

    /**
     * Если есть скоринг который дает одобрение к выдачам, отсылаем результат
     * @param array $scorings
     * @return object|null
     */
    private function getApprovedScoring(array $scorings): ?object
    {
        $types = $this->scorings::APPROVED_SCORING_TYPES;
        foreach ($scorings as $scoring) {
            if ((int)$scoring->status === $this->scorings::STATUS_COMPLETED && !empty($scoring->success) && in_array($scoring->type, $types)) {
                return $scoring;
            }
        }

        return null;
    }

    private function returnCheckOrderResponse(string $response, array $data): string
    {
        return $response;
    }

    /**
     * Добавляет запись в лог запросов
     * @param array $data
     * @return mixed
     */
    public function addLog(array $data)
    {
        $sql = $this->db->placehold("INSERT INTO __partner_api_logs SET ?%", $data);
        $this->db->query($sql);

        return $this->db->insert_id();
    }

    /**
     * Для удобства добавляем решение в базу, чтобы не дергать 1С
     * @param string $phone
     * @param string $status
     * @return mixed
     */
    public function addUserStatusLog(string $phone, string $status)
    {
        $sql = $this->db->placehold("INSERT INTO __partner_api_users SET ?%", compact('phone', 'status'));
        $this->db->query($sql);

        return $this->db->insert_id();
    }

    /**
     * Добавляет лог статусов, которые возвращаем
     * @param int $order_id
     * @param string $status
     * @return mixed
     */
    public function addOrderStatusLog(int $order_id, string $status)
    {
        $sql = $this->db->placehold("INSERT INTO __partner_api_orders_log SET ?%", compact('order_id', 'status'));
        $this->db->query($sql);

        return $this->db->insert_id();
    }

    /**
     * Проверяет, возвращали ли мы определенный статус по заявке
     *
     * необходимо на случай когда заявка вдруг выдана, а мы не возвращали статус approved
     * @param int $order_id
     * @param string $status
     * @return bool
     */
    public function hasOrderLogStatus(int $order_id, string $status): bool
    {
        $sql = $this->db->placehold("SELECT EXISTS(select * FROM __partner_api_orders_log WHERE order_id =  ? AND status = ?) as result", $order_id, $status);
        $this->db->query($sql);

        return !empty($this->db->result('result'));
    }

    /**
     * Получает решение по пользователю из self::checkUser
     * @param string $phone
     * @return string
     */
    public function getUserStatus(string $phone): string
    {
        $sql = $this->db->placehold("select status  FROM __partner_api_users WHERE phone =  ? ORDER BY id DESC LIMIT 1", $phone);
        $this->db->query($sql);

        return $this->db->result('status');
    }

    /**
     * Проверяем была ли акси и добавим ее если нет
     * @param int $order_id
     * @param int $user_id
     * @return void
     */
    public function checkAxi2(int $order_id, int $user_id)
    {
        $axi = $this->scorings->get_scorings(
            [
                'type' => $this->scorings::TYPE_AXILINK_2,
                'order_id' => $order_id
            ]
        );

        if (empty($axi)) {
            $this->scorings->add_scoring(
                [
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'type' => $this->scorings::TYPE_AXILINK_2,
                    'status' => $this->scorings::STATUS_NEW,
                    'created' => date('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * Проверяем есть ли другие заявки кроме, этой что передаем в параметрах
     *
     * @param int $order_id
     * @param int $user_id
     * @return bool
     */
    private function hasOtherOrders(int $order_id, int $user_id): bool
    {
        $sql = $this->db->placehold("SELECT EXISTS(select * FROM __orders WHERE id != ? AND user_id = ? LIMIT 1) as result", $order_id, $user_id);
        $this->db->query($sql);
        return !empty($this->db->result('result'));
    }
}
