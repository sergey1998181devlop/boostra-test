<?php

require_once 'Simpla.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/autoloader.php';

use ADDONS\DOP\ConciergeApi;
use ADDONS\DOP\DOPApi;
use ADDONS\DOP\StarOracleApi;
use ADDONS\DOP\VitaMedApi;
use boostra\services\UsersAddressService;

/**
 * Сервис для работы с лицензиями дополнительных сервисов
 */
class DOPLicense extends Simpla
{
    // Названия таблиц
    private const LICENSES_TABLE = 's_dop_licenses';

    // Константы типов сервисов
    public const SERVICE_FINANCIAL_DOCTOR = 'financial_doctor';
    public const SERVICE_CONCIERGE = 'concierge';
    public const SERVICE_VITAMED = 'vitamed';
    public const SERVICE_STAR_ORACLE = 'star_oracle';

    /**
     * Возвращает массив всех типов сервисов
     *
     * @return array
     */
    public static function getAllServiceTypes(): array
    {
        return [
            self::SERVICE_FINANCIAL_DOCTOR,
            self::SERVICE_CONCIERGE,
            self::SERVICE_VITAMED,
            self::SERVICE_STAR_ORACLE
        ];
    }

    /**
     * Возвращает читаемое название типа сервиса
     *
     * @param string $type
     * @return string
     */
    public static function getServiceDisplayName(string $type): string
    {
        $names = [
            self::SERVICE_FINANCIAL_DOCTOR => 'Финансовый Доктор',
            self::SERVICE_CONCIERGE => 'Консьерж-сервис',
            self::SERVICE_VITAMED => 'Витамед',
            self::SERVICE_STAR_ORACLE => 'Звездный Оракул'
        ];

        return $names[$type] ?? $type;
    }

    /**
     * Проверяет, является ли строка допустимым типом сервиса
     *
     * @param string $type
     * @return bool
     */
    public static function isValidServiceType(string $type): bool
    {
        return in_array($type, self::getAllServiceTypes());
    }

    /**
     * Создает новую лицензию и связывает ее с заказом и пользователем
     *
     * @param string $serviceType Тип сервиса
     * @param array $licenseData Данные лицензии
     * @return int ID созданной лицензии
     */
    public function createLicense(string $serviceType, array $licenseData): int
    {
        if (!self::isValidServiceType($serviceType)) {
            return 0;
        }

        try {
            $licenseData['service_type'] = $serviceType;

            $query = $this->db->placehold(
                "INSERT INTO " . self::LICENSES_TABLE . " SET ?%",
                $licenseData
            );
            $this->db->query($query);

            return $this->db->insert_id();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Создает лицензию с использованием соответствующего API сервиса
     *
     * @param object $dop_license
     * @return string|null ID созданной лицензии или null в случае ошибки
     */
    public function getLicenseKey(object $dop_license): ?string
    {
        $attempts = (int)($dop_license->attempts ?? 0) + 1;
        
        // Проверяем валидность типа сервиса
        if (!self::isValidServiceType($dop_license->service_type)) {

            $this->updateLicenseKey(
                (int)$dop_license->id,
                [
                    'status' => 'ERROR',
                    'api_response' => "Неизвестный тип сервиса: {$dop_license->service_type}",
                    'attempts' => $attempts,
                ]
            );
            return null;
        }


        // Получаем соответствующий API класс для типа сервиса
        $apiInstance = $this->getApiInstanceForServiceType($dop_license->service_type);

        $contract = $this->contracts->get_contract_by_params(['order_id' => (int)$dop_license->order_id]);
        $organization = $this->organizations->get_organization((int)$contract->organization_id);
        $user = $this->users->get_user((int)$dop_license->user_id);
        $address = (new UsersAddressService())->getUserAddress((int)$user->registration_address_id);
        
        if ($address){
            $fullAddress = $address->region . ', ' . $address->city . ', ' . $address->street . ', д. ' . $address->building;
        }elseif (!empty($user->Regregion) || !empty($user->Regcity) || !empty($user->Regstreet) || !empty($user->Reghousing)) {
            $fullAddress = $user->Regregion . ' ' . $user->Regregion_shorttype . '., ' . $user->Regcity . ' ' . $user->Regstreet_shorttype . '., ' . $user->Regstreet . ',' . $user->Reghousing;
        } else {
            $fullAddress = '';
        }

        if (empty($contract) || empty($organization) || empty($user) || empty($fullAddress) || !($dop_license->amount > 0)) {
            $this->updateLicenseKey(
                (int)$dop_license->id,
                [
                    'status' => 'ERROR',
                    'api_body' => serialize(['contract' => $contract, 'organization', 'user' => $user, 'address' => $address, 'amount' => $dop_license->amount]),
                    'api_response' => 'Не удалось получить данные для создания лицензии',
                    'attempts' => $attempts,
                ]
            );

            return null;
        }

        $apiBody = [
            'contract' => $contract->number,
            'contractSum' => $contract->amount,
            'contractDate' => $contract->issuance_date,
            'contractOrg' => $organization->inn,
            'username' => $user->firstname . ' ' . $user->lastname . ' ' . $user->patronymic,
            'birthday' => $user->birth,
            'address' => $fullAddress,
            'passport' => $fullAddress,
            'phone' => $user->phone_mobile,
            'email' => $user->email,
            'price' => $dop_license->amount,
        ];

        $licenseData = $apiInstance->makeKey($apiBody);

        $license = [
            'license_key' => $licenseData['key'] ?? null,
            'status' => !empty($licenseData['key']) ? 'SUCCESS' : 'ERROR',
            'api_response' => serialize($licenseData),
            'attempts' => $attempts,
            'api_body' => serialize($apiBody),
            'tariff' => $licenseData['tariff'] ?? null,
        ];


        if (!empty($licenseData['ending'])) {
            $license['ending'] = date('Y-m-d H:i:s', $licenseData['ending'] / 1000);
        }

        $this->updateLicenseKey((int)$dop_license->id, $license);

        return (string)($licenseData['key'] ?? '');
    }

    /**
     * @param string $serviceType
     * @param array $licenseData
     * @return string|null
     */
    public function createLicenseWithKey(string $serviceType, array $licenseData): ?string
    {
        try {
            $licenseId = $this->createLicense($serviceType, $licenseData);

            if ($licenseId) {
                $license = new stdClass();

                $license->service_type = $serviceType;
                $license->id = $licenseId;
                $license->order_id = $licenseData['order_id'];
                $license->user_id = $licenseData['user_id'];
                $license->amount = $licenseData['amount'];
                $license->attempts = 0;

                return $this->getLicenseKey($license);
            }

            return null;
            
        } catch (Exception $e) {
            $this->logging(
                __METHOD__,
                '',
                '',
                $e->getMessage(),
                'dop_api.txt'
            );
            return null;
        }

        
    }

    /**
     * Обновляет данные лицензии в базе данных
     *
     * @param int $licenseKeyId Тип сервиса
     * @param array $licenseData Данные лицензии для обновления
     * @return int ID обновленной лицензии
     */
    public function updateLicenseKey(int $licenseKeyId, array $licenseData): int
    {
        $query = $this->db->placehold(
            "UPDATE " . self::LICENSES_TABLE . " SET ?% WHERE id = ?",
            $licenseData,
            $licenseKeyId
        );
        $this->db->query($query);

        return $licenseKeyId;
    }

    /**
     * Получает экземпляр API класса для указанного типа сервиса
     *
     * @param string $serviceType Тип сервиса
     * @return DOPApi Экземпляр API класса
     * @throws InvalidArgumentException Если API класс не найден
     */
    private function getApiInstanceForServiceType(string $serviceType): DOPApi
    {
        switch ($serviceType) {
//            case self::SERVICE_FINANCIAL_DOCTOR:
//                return new FinancialDoctorApi();

            case self::SERVICE_STAR_ORACLE:
                return new StarOracleApi();

            case self::SERVICE_CONCIERGE:
                return new ConciergeApi();

            case self::SERVICE_VITAMED:
                return new VitaMedApi();

            default:
                throw new InvalidArgumentException("API класс для сервиса {$serviceType} не найден");
        }
    }


    public function getAllLicenseKeys(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM " . self::LICENSES_TABLE . " WHERE 1
                 -- {{where}}";


        if (isset($filter_data['filter_empty_license_key'])) {
            $where[] = $this->db->placehold("license_key is null");
        }

        if (!empty($filter_data['filter_service_type'])) {
            $where[] = $this->db->placehold("service_type = ?", $filter_data['filter_service_type']);
        }
        if (!empty($filter_data['filter_order_id'])) {
            $where[] = $this->db->placehold("order_id = ?", (int)$filter_data['filter_order_id']);
        }
        
        if (!empty($filter_data['filter_user_id'])) {
            $where[] = $this->db->placehold("user_id = ?", (int)$filter_data['filter_user_id']);
        }
        
        if (!empty($filter_data['filter_service_id'])) {
            $where[] = $this->db->placehold("service_id = ?", (int)$filter_data['filter_service_id']);
        }

        if (!empty($filter_data['filter_status'])) {
            $where[] = $this->db->placehold("status = ?", $filter_data['filter_status']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        if (!empty($filter_data['order_by'])) {
            $query .= PHP_EOL . "ORDER BY " . trim($filter_data['order_by']);
        }

        if (!empty($filter_data['limit'])) {
            $query .= PHP_EOL . "LIMIT " . (int)$filter_data['limit'];
        }

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        }

        return $this->db->result();
    }

}
