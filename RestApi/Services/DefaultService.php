<?php

namespace RestApi\Services;

use api\enums\ProfessionEnum;
use api\helpers\JWTHelper;
use RestApi\Helpers\ServiceHelper;
use RestApi\Interfaces\PartnerServiceInterface;
use RestApi\PartnerApi;
use Simpla;

require_once dirname(__DIR__, 2) . '/api/Simpla.php';

/**
 * Базовый сервис для дефолтных подключений партнеров
 */
class DefaultService extends Simpla implements PartnerServiceInterface
{
    /**
     * Тег для логов
     */
    private const LOG_TAG = 'ping3_service';

    /**
     * id партнера
     * @var string
     */
    protected string $partner = '';

    /**
     * Созданный или найденный пользователь
     * @var object|null
     */
    protected ?object $client = null;

    public function __construct()
    {
        parent::__construct();
        $this->partner = $this->request->get('partner', 'string');
    }

    public function getToken(): array
    {
        $password = $this->request->getJsonInput('password');

        if (!empty($password) && $this->config->partner_api_passwords[$this->partner] === $password) {
            $secret_key = $this->config->jwt['partner_key'];
            $token = JWTHelper::generateToken($secret_key, $this->partner);

            return [
                'token' => $token,
                'exp' => 3600,
            ];
        }

        return [];
    }

    public function checkDouble(): array
    {
        $phone = $this->users->clear_phone($this->request->getJsonInput('phone'));
        $series = $this->request->getJsonInput('passport')['series'] ?? '';
        $number = $this->request->getJsonInput('passport')['number'] ?? '';

        // Проверим если статус уже отдавали вернем его
//        $status = $this->rest_api_partner->getUserStatus($phone);
//        if (!empty($status)) {
//            return compact('status');
//        }

        $status = $this->rest_api_partner->checkUser(['phone_mobile' => $phone, 'passport_serial' => $series . $number]);
        $this->rest_api_partner->addUserStatusLog($phone, $status);
        return compact('status');
    }

    public function applicationForDecisions(): array
    {
        $json = $this->request->getAllJsonInput();
        $phone = $this->users->clear_phone($json['contact']['phone']);

        // Получим решение из базы из предыдущего шага
        $user_status = $this->rest_api_partner->getUserStatus($phone);

        // Если пропущен перед этим шаг выведем ошибку
        if (empty($user_status)) {
            return [
                "errors" => [
                    "message" => "The previous check-double step was not completed",
                ]
            ];
        }

        // Если статус, мы отказали ранее, вернем ошибку
        if ($user_status === \RestApiPartner::CHECK_USER_RESPONSE_CANCEL) {
            return [
                "errors" => [
                    "message" => "This user was rejected in previos step",
                ]
            ];
        }

        $passport_serial = $this->users->getClearPassport($json['passport']['series'] . $json['passport']['number']);
        $user_status = $this->rest_api_partner->checkUser(['phone_mobile' => $phone, 'passport_serial' => $passport_serial]);

        // Кому платим за ПК устанавливаем в настройках
        if ($user_status === $this->rest_api_partner::CHECK_USER_RESPONSE_REPEAT && !in_array($this->partner, $this->settings->partner_api_repeat_client_utm_sources ?? [])) {
            return [
                "status" => $this->rest_api_partner::STATUS_ORDER_DECLINED,
                "errors" => [
                    "message" => "Repeat user is not allowed",
                ]
            ];
        }

        // Проверяем был ли он в базе
        $user_type = $this->ping3_data->getPing3Data($this->ping3_data::USER_TYPE, $phone);
        $clientNotFoundDB = is_numeric($user_type) && (int)$user_type === $this->ping3_data::USER_TYPE_NEW;

        // Создаем нового пользователя
        $user_data = $this->getUserFields($clientNotFoundDB);
        $user_id = $this->rest_api_partner->addUser($user_data);

        if (empty($user_id)) {
            $this->open_search_logger->create('При вызове метода applicationForDecisions, не вернулся пользователь', [
                'request_uid' => PartnerApi::$request_uid,
                'user_data' => $user_data,
            ], self::LOG_TAG, \OpenSearchLogger::LOG_LEVEL_INFO, 'ping3');

            return [
                "errors" => [
                    "message" => "User not initialize",
                ]
            ];
        }

        // Проверяем флоу крос ордера cross_order, если пометили заявку на обработку то вернем ее
        if ($cross_order_order_id = $this->ping3_data->getPing3Data($this->ping3_data::REPEAT_HAS_CRM_CROSS_ORDER, $user_id)) {
            return $this->autoOrderProcess($cross_order_order_id, $user_id, $user_status);
        }

        // Проверяем флоу автозаявки crm_auto_approve, если пометили заявку на обработку то вернем ее
        if ($crm_auto_approve_order_id = $this->ping3_data->getPing3Data($this->ping3_data::REPEAT_HAS_CRM_AUTO_APPROVE, $user_id)) {
            return $this->autoOrderProcess($crm_auto_approve_order_id, $user_id, $user_status);
        }

        // Создаем заявку
        $order_data = $this->getOrderFields($user_id);
        $order_id = $this->rest_api_partner->addOrder($order_data, $this->getPartner());

        // Сохраняем служебную информацию от партнера
        if ($order_id) {
            $this->saveOrderMetaData($order_id);
            $this->order_data->set($order_id, $this->order_data::PING3_USER_STATUS, $user_status);
        }

        $status = $order_id ? $this->rest_api_partner::STATUS_ORDER_PROCESSING : $this->rest_api_partner::STATUS_ORDER_DECLINED;

        return [
            "status" => $status,
            "application-id" => $order_id
        ];
    }

    /**
     * Если найден cross_order или автозаявка crm_auto_approve выполним общие действия
     *
     * @param int $order_id
     * @param int $user_id
     * @param string $user_status
     * @return array
     */
    private function autoOrderProcess(int $order_id, int $user_id, string $user_status): array
    {
        // добавим статус, который отдали при создании заявки
        $this->order_data->set($order_id, $this->order_data::PING3_USER_STATUS, $user_status);

        // присваиваем автозаявке признак ping3, для сбора статистики
        $this->order_data->set($order_id, $this->order_data::ORDER_FROM_PARTNER, $this->getPartner());
        return [
            "link" => ServiceHelper::getUserAuthLink($this, $user_id, $this->partner),
            "status" => $this->rest_api_partner::STATUS_ORDER_APPROVED,
            "application-id" => $order_id
        ];
    }

    public function checkDecisions(): array
    {
        $order_id = $this->request->get('id', 'integer');
        $order = $this->orders->get_order($order_id);
        $order_status = $this->rest_api_partner->checkOrder($order_id);

        // Если статус выдано, но мы не возвращали статус перед этим одобрения вернем его
        if ($order_status === $this->rest_api_partner::STATUS_ORDER_ISSUED) {
            $hasApprovedStatus = $this->rest_api_partner->hasOrderLogStatus($order_id, $this->rest_api_partner::STATUS_ORDER_APPROVED);
            if (!$hasApprovedStatus) {
                $order_status = $this->rest_api_partner::STATUS_ORDER_APPROVED;
            }
        }

        // Если статус в процессе, проверим наличие скоринга Акси2
        if ($order_status === $this->rest_api_partner::STATUS_ORDER_PROCESSING) {
            $this->rest_api_partner->checkAxi2($order_id, $order->user_id);
        }

        $this->rest_api_partner->addOrderStatusLog($order_id, $order_status);

        return [
            "application-id" => $order->id,
            "status" => $order_status,
            "limit" => $order->amount,
            "duration-days" => $order->period,
            "ratePercentPerDay" => $order->percent,
            "link" => $this->visibleLink($order_status) ? ServiceHelper::getUserAuthLink($this, $order->user_id, $this->partner) : null,
        ];
    }

    /**
     * Получаем поля для пользователя
     *
     * @param bool $new_client Был ли клиент в базе
     * @return array
     */
    public function getUserFields(bool $new_client = true): array
    {
        $json = $this->request->getAllJsonInput();
        $phone = $this->users->clear_phone($json['contact']['phone']);

        // Базовые данные пользователя
        $data = [
            'firstname' => $json['passport']['first-name'],
            'lastname' => $json['passport']['last-name'],
            'patronymic' => $json['passport']['middle-name'] ?? '',
            'phone_mobile' => $phone,
            'birth' => $json['passport']['birth-day'],
            'birth_place' => $json['passport']['birth-place'],
            'gender' => $json['passport']['gender'],
            'reg_ip' => $_SERVER['REMOTE_ADDR'],
            'passport_issued' => $json['passport']['issued-by'],
            'passport_serial' => $json['passport']['series'] . ' ' . $json['passport']['number'],
            'subdivision_code' => $json['passport']['division-code'],
            'passport_date' => $json['passport']['issue-date'],
        ];

        // Данные о занятости
        if (!empty($json['employment'])) {
            $employment = $this->getEmploymentData($json['employment'], $new_client);
            $data = array_merge($data, $employment);
        }

        // Данные заявки
        if (!empty($json['application'])) {
            $application = $this->getApplicationData($json['application'], $new_client);
            $data = array_merge($data, $application);
        }

        // Адрес регистрации
        if (!empty($json['registration-address'])) {
            $regAddress = $this->getRegistrationAddressData($json['registration-address'], $new_client);
            $data = array_merge($data, $regAddress);
        }

        // Фактический адрес
        if (!empty($json['actual-address'])) {
            $factAddress = $this->getActualAddressData($json['actual-address'], $new_client);
            $data = array_merge($data, $factAddress);
        }

        if ($new_client) {
            $orderFields = $this->getOrderContractField();
            $date = date('Y-m-d H:i:s');
            $serviceFields = [
                'utm_source' => $this->partner,
                'utm_medium' => 'Site',
                'utm_campaign' => '',
                'utm_content' => '',
                'utm_term' => $this->rest_api_partner::UTM_TERM,
                'webmaster_id' => '',
                'click_hash' => $orderFields['click_hash'],
                'first_loan_period' => $orderFields['period'],
                'first_loan_amount' => $orderFields['amount'],
                'first_loan' => 1,
                'reg_ip' => $_SERVER['REMOTE_ADDR'],
                'enabled' => 1,
                'use_b2p' => 1,
                'missing_real_date' => $date,
                'service_sms' => 1,
                'service_insurance' => 0,
                'sms' => rand(1000, 9999),
            ];

            $data = array_merge($data, $serviceFields);
        }

        return $data;
    }

    /**
     * Формирует данные о работе
     *
     * @param array $json Массив входящих данных с работой
     * @param bool $new_client Есть ли клиент в базе данных
     * @return array
     */
    protected function getEmploymentData(array $json, bool $new_client): array
    {
        if ($new_client)
        {
            $employment = [
                'workplace' => $json['employment']['organization-name'] ?? '',
                'work_phone' => $json['employment']['phone'] ?? '',
                'work_address' => $json['employment']['organization-address']['address-name'] ?? '',
                'income_base' => $json['employment']['monthly-income'] ?? '',
            ];

            if ($profession = $this->getProfession($json['employment']['employment-position-type'] ?? '')) {
                $employment['profession'] = $profession;
            }
        } else {
            $employment = [];

            if (!empty($json['employment']['organization-name'])) {
                $employment['workplace'] = $json['employment']['organization-name'];
            }

            if (!empty($json['employment']['phone'])) {
                $employment['work_phone'] = $json['employment']['phone'];
            }

            if (!empty($json['employment']['organization-address']['address-name'])) {
                $employment['work_address'] = $json['employment']['organization-address']['address-name'];
            }

            if (!empty($json['employment']['monthly-income'])) {
                $employment['income_base'] = $json['employment']['monthly-income'];
            }

            if (!empty($json['employment']['employment-position-type'])) {
                $employment['profession'] = $this->getProfession($json['employment']['employment-position-type']);
            }
        }

        return $employment;
    }

    /**
     * Формирует данные фактического адреса
     *
     * @param array $json Массив входящих данных фактического адреса
     * @param bool $new_client Есть ли клиент в базе данных
     * @return array
     */
    protected function getActualAddressData(array $json, bool $new_client): array
    {
        if ($new_client) {
            $address = [
                'Faktindex' => $json['index'] ?? '',
                'Faktregion' => $json['region'] ?? '',
                'Faktcity' => $json['city'] ?? '',
                'Faktstreet' => $json['street'] ?? '',
                'Fakthousing' => $json['house'] ?? '',
                'Faktbuilding' => $json['build'] ?? '',
                'Faktroom' => $json['flat'] ?? '',
            ];
        } else {
            $address = [];

            if (!empty($json['index'])) {
                $address['Faktindex'] = $json['index'];
            }

            if (!empty($json['region'])) {
                $address['Faktregion'] = $json['region'];
            }

            if (!empty($json['city'])) {
                $address['Faktcity'] = $json['city'];
            }

            if (!empty($json['street'])) {
                $address['Faktstreet'] = $json['street'];
            }

            if (!empty($json['house'])) {
                $address['Fakthousing'] = $json['house'];
            }

            if (!empty($json['build'])) {
                $address['Faktbuilding'] = $json['build'];
            }

            if (!empty($json['flat'])) {
                $address['Faktroom'] = $json['flat'];
            }
        }

        return $address;
    }

    /**
     * Формирует данные адреса регистрации
     *
     * @param array $json Массив входящих данных адреса регистрации
     * @param bool $new_client Есть ли клиент в базе данных
     * @return array
     */
    protected function getRegistrationAddressData(array $json, bool $new_client): array
    {
        if ($new_client) {
            $address = [
                'Regindex' => $json['index'] ?? '',
                'Regregion' => $json['region'] ?? '',
                'Regcity' => $json['city'] ?? '',
                'Regstreet' => $json['street'] ?? '',
                'Reghousing' => $json['house'] ?? '',
                'Regbuilding' => $json['build'] ?? '',
                'Regroom' => $json['flat'] ?? '',
            ];
        } else {
            $address = [];

            if (!empty($json['index'])) {
                $address['Regindex'] = $json['index'];
            }

            if (!empty($json['region'])) {
                $address['Regregion'] = $json['region'];
            }

            if (!empty($json['city'])) {
                $address['Regcity'] = $json['city'];
            }

            if (!empty($json['street'])) {
                $address['Regstreet'] = $json['street'];
            }

            if (!empty($json['house'])) {
                $address['Reghousing'] = $json['house'];
            }

            if (!empty($json['build'])) {
                $address['Regbuilding'] = $json['build'];
            }

            if (!empty($json['flat'])) {
                $address['Regroom'] = $json['flat'];
            }
        }

        return $address;
    }

    /**
     * Формирует данные анкеты
     *
     * @param array $json Массив входящих данных анкеты
     * @param bool $new_client Есть ли клиент в базе данных
     * @return array
     */
    protected function getApplicationData(array $json, bool $new_client): array
    {
        if ($new_client) {
            $application = [
                'snils' => $json['snils'] ?? '',
                'inn' => $json['client-inn'] ?? '',
                'education' => $this->getEducationId($json['education'] ?? ''),
                'marital_status' => $this->getMaritalStatusId($json['marital-status'] ?? ''),
            ];
        } else {
            $application = [];

            if (!empty($json['snils'])) {
                $application['snils'] = $json['snils'];
            }

            if (!empty($json['client-inn'])) {
                $application['inn'] = $json['client-inn'];
            }

            if (!empty($json['education'])) {
                $application['education'] = $this->getEducationId($json['education']);
            }

            if (!empty($json['marital-status'])) {
                $application['marital_status'] = $this->getMaritalStatusId($json['marital-status']);
            }
        }

        return $application;
    }

    /**
     * Получаем поля заявки
     *
     * @param int $user_id
     * @return array
     */
    public function getOrderFields(int $user_id): array
    {
        $this->client = $this->users->get_user($user_id);

        $orderFields = $this->getOrderContractField();
        $userHasOrders = $this->users->userHasOrders($user_id);
        $have_close_credits = 0;

        if (!empty($this->client->uid)) {
            $credits_history = $this->soap->get_user_credits($this->client->uid);
            $have_close_credits = (int)!empty($credits_history);
        }

        return array_merge([
            'user_id' => $user_id,
            'utm_source' => $this->partner,
            'utm_medium' => 'Site',
            'utm_campaign' => '',
            'utm_content' => '',
            'utm_term' => $this->rest_api_partner::UTM_TERM,
            'webmaster_id' => '',
            'b2p' => 1,
            'first_loan' => (int)!$userHasOrders,
            'have_close_credits' => $have_close_credits,
            'date' => date('Y-m-d H:i:s'),
            'order_uid' => exec($this->config->root_dir . 'generic/uidgen'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'organization_id' => $this->organizations->get_base_organization_id(),
        ], $orderFields);
    }

    /**
     * Получить ид образования
     *
     * @param string $value
     * @return int
     */
    public function getEducationId(string $value): int
    {
        $text = preg_replace('/-/','_', $value);
        switch ($text) {
            case 'higher':
            case 'two_higher':
                return \Users::EDUCATION_HIGHER;
            case 'secondary':
                return \Users::EDUCATION_SECONDARY;
            case 'secondary_special':
                return \Users::EDUCATION_SECONDARY_SPECIAL;
            case 'incomplete_higher':
                return \Users::EDUCATION_INCOMPLETE_HIGHER;
            default:
                return \Users::EDUCATION_OTHER;
        }
    }

    /**
     * Семейный статус
     *
     * @param string $value
     * @return string|null
     */
    public function getMaritalStatusId(string $value): ?string
    {
        $text = preg_replace('/-/','_', $value);
        switch ($text) {
            case 'married':
                return \Users::MARITAL_STATUS_MARRIED;
            case 'divorced':
                return \Users::MARITAL_STATUS_DIVORCED;
            case 'civil_marriage':
                return \Users::MARITAL_STATUS_CIVIL_MARRIAGE;
            case 'widower':
                return \Users::MARITAL_STATUS_WIDOWER;
            case 'single':
                return \Users::MARITAL_STATUS_SINGLE;
            default:
                return null;
        }
    }

    /**
     * Профессия
     *
     * @param string $value
     * @return string|null
     */
    public function getProfession(string $value): ?string
    {
        $text = preg_replace('/-/','_', $value);
        switch ($text) {
            case 'not_working':
                return ProfessionEnum::NOT_WORKING;
            case 'owner':
                return ProfessionEnum::BUSINESS_OWNER;
            case 'senior_specialist':
            case 'specialist':
                return ProfessionEnum::SPECIALIST;
            case 'head_of_division':
            case 'higher_management_level':
            case 'manager':
                return ProfessionEnum::MANAGER;
            case 'unskilled_worker':
                return ProfessionEnum::WORK_UNOFFICIALLY;
            case 'work_officially':
                return ProfessionEnum::WORK_OFFICIALLY;
            case 'civil_servant':
                return ProfessionEnum::CIVIL_SERVANT;
            case 'municipal_employee':
                return ProfessionEnum::MUNICIPAL_EMPLOYEE;
            case 'self_employment':
                return ProfessionEnum::SELF_EMPLOYMENT;
            case 'individual_entrepreneur':
                return ProfessionEnum::INDIVIDUAL_ENTREPRENEUR;
            case 'student':
                return ProfessionEnum::STUDENT;
            case 'retired':
                return ProfessionEnum::RETIRED;
            default:
                return null;
        }
    }

    /**
     * Базовые параметры заявки
     * @return array
     */
    private function getOrderContractField(): array
    {
        $json = $this->request->getAllJsonInput();
        $amount = $json['order-data']['amount'] ?? \RestApiPartner::BASE_AMOUNT;
        $period = max($json['order-data']['period'] ?? \RestApiPartner::BASE_PERIOD, 16);
        $percent = $json['order-data']['percent'] ?? \RestApiPartner::BASE_PERCENT;
        $click_hash = $json['order-data']['click-id'] ?? '';

        return compact('amount', 'period', 'percent', 'click_hash');
    }

    /**
     * Возвращает партнера
     * @return string
     */
    public function getPartner(): string
    {
        return $this->partner;
    }

    /**
     * Сохраняет мета данные от клиента партнера
     * @param int $order_id
     * @return void
     */
    protected function saveOrderMetaData(int $order_id)
    {
        $json = $this->request->getJsonInput('order-data');
        if (!empty($json['meta']) && is_array($json['meta'])) {
            foreach ($json['meta'] as $key => $value) {
                $this->order_data->set($order_id, 'ping3_meta_' . $key, $value);
            }
        }
    }

    /**
     * Показываем ли ссылку, в статусах
     *
     * @param string $status
     * @return bool
     */
    protected function visibleLink(string $status): bool
    {
        return in_array($status, [
            $this->rest_api_partner::STATUS_ORDER_APPROVED,
            $this->rest_api_partner::STATUS_ORDER_PRE_APPROVED,
            $this->rest_api_partner::STATUS_ORDER_ISSUED,
        ]);
    }
}

