<?php

namespace App\Handlers;

use App\Contracts\ExtraServiceLicenseKeyHandlerContract;
use App\Core\Application\Facades\DB;
use App\Models\Order;
use DateTime;
use Throwable;

class ExtraServiceLicenseKeyHandler implements ExtraServiceLicenseKeyHandlerContract
{
    /**
     * Конфигурация типов услуг
     * @var array<string, array{name: string, policy: string}>
     */
    private array $services = [
        'credit_doctor' => ['name' => 'Финансовый доктор', 'policy' => 'CREDIT_DOCTOR_POLICY'],
        'star_oracle' => ['name' => 'Звёздный оракул', 'policy' => 'STAR_ORACLE_POLICY'],
        'vita_med' => ['name' => 'Вита-мед', 'policy' => 'ACCEPT_TELEMEDICINE'],
        'concierge' => ['name' => 'Консьерж сервис', 'policy' => 'DOC_MULTIPOLIS'],
    ];

    /**
     * Маппинг русских названий на английские ключи
     * @var array<string, string>
     */
    private array $serviceTypeNormalization = [
        'финансовый доктор' => 'credit_doctor',
        'кредитный доктор' => 'credit_doctor',
        'звездный оракул' => 'star_oracle',
        'вита-мед' => 'vita_med',
        'телемедицина' => 'vita_med',
        'консьерж-сервис' => 'concierge',
        'консьерж сервис' => 'concierge',
        'мультиполис' => 'concierge',
    ];

    /**
     * Получить лицензионный ключ для дополнительной услуги
     *
     * @param string $contractNumber Номер договора
     * @param string $serviceType Тип услуги
     * @return array{success: bool, message: string, license_key?: string, service_name?: string, created?: string}
     */
    public function getLicenseKey(string $contractNumber, string $serviceType): array
    {
        // Нормализуем тип услуги
        $normalizedServiceType = $this->normalizeServiceType($serviceType);

        if (!$normalizedServiceType) {
            return [
                'success' => false,
                'message' => 'Некорректный тип программного обеспечения',
                'http_code' => 422,
            ];
        }

        $contractNumber = formatReferenceNumber($contractNumber);

        $orderID = (new Order())->get(
            ["[>]s_contracts" => ["id" => "order_id"]],
            ["s_orders.id"],
            ["s_contracts.number" => $contractNumber]
        )->getData()['id'] ?? null;

        if (empty($orderID)) {
            return [
                'success' => false,
                'message' => 'Не найдена заявка клиента, соответствующая номеру договора: ' . $contractNumber,
                'http_code' => 404,
            ];
        }

        try {
            $recompensedService = (new AdditionalServicesHandler())
                ->getByOrderId($orderID, $normalizedServiceType)['data'][$normalizedServiceType] ?? [];

            if (!empty($recompensedService)) {
                // Уже возвращено
                if (!empty($recompensedService['return_date'])) {
                    return [
                        'success' => true,
                        'message' => 'Программное обеспечение ' . $this->services[$normalizedServiceType]['name'] . ' уже возвращено, Дата возврата: ' . $recompensedService['return_date'],
                        'http_code' => 200,
                    ];
                }

                // Срок более 30 дней с момента подключения
                if (!empty($recompensedService['date_added'])) {
                    $ts = strtotime((string)$recompensedService['date_added']);
                    if ($ts && (time() - $ts) > (30 * 24 * 60 * 60)) {
                        return [
                            'success' => false,
                            'message' => 'Нельзя отказаться от программного обеспечения по истечении 30 дней с момента его подключения',
                            'http_code' => 200,
                        ];
                    }
                }
            }

            $policies = DB::db()->select('s_documents', '*', [
                'order_id' => $orderID,
                'type' => $this->services[$normalizedServiceType]['policy'],
            ]);

            if (empty($policies)) {
                return [
                    'success' => false,
                    'message' => 'Не найдено Программное обеспечение ' . $this->services[$normalizedServiceType]['name'],
                    'http_code' => 404,
                ];
            }

            $licenseKey = null;
            $created = null;

            foreach ($policies as $policy) {
                $params = unserialize($policy['params']);

                if (!empty($params)) {
                    if (is_object($params)) {
                        $licenseKey = $params->license_key ?? null;
                    } elseif (is_array($params)) {
                        $licenseKey = $params['license_key'] ?? null;
                    }
                }

                if (!empty($licenseKey)) {
                    $created = (new DateTime($policy['created']))->format('d.m.Y');
                    break;
                }
            }

            if (empty($licenseKey)) {
                return [
                    'success' => false,
                    'message' => 'Лицензионный ключ для программного обеспечения ' . $this->services[$normalizedServiceType]['name'] . ' отсутствует',
                    'http_code' => 404,
                ];
            }

            return [
                'success' => true,
                'message' => 'Ваш лицензионный ключ для программного обеспечения ' . $this->services[$normalizedServiceType]['name'] . ': ' . $licenseKey . ' , Дата оформления: ' . $created,
                'license_key' => $licenseKey,
                'service_name' => $this->services[$normalizedServiceType]['name'],
                'created' => $created,
                'http_code' => 200,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Внутренняя ошибка: ' . $e->getMessage(),
                'http_code' => 500,
            ];
        }
    }

    /**
     * Нормализует тип услуги: преобразует русские названия в английские ключи
     *
     * @param string $serviceType Тип услуги
     * @return string|null Нормализованный тип услуги или null если не найден
     */
    private function normalizeServiceType(string $serviceType): ?string
    {
        $lowerType = mb_strtolower(trim($serviceType));

        // Если это русское название — преобразуем в английский ключ
        if (isset($this->serviceTypeNormalization[$lowerType])) {
            return $this->serviceTypeNormalization[$lowerType];
        }

        // Если это уже английский ключ и он существует — возвращаем как есть
        if (isset($this->services[$lowerType])) {
            return $lowerType;
        }

        return null;
    }
}
