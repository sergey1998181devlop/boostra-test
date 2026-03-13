<?php

namespace App\Handlers;

use App\Contracts\AdditionalServicesHandlerContract;
use App\Enums\ReceiptPaymentType;
use App\Enums\AdditionalServiceReturnStatus;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use App\Models\CreditDoctor;
use App\Models\Multipolis;
use App\Models\StarOracle;
use App\Models\TVMedical;
use App\Models\OrderData;

class AdditionalServicesHandler implements AdditionalServicesHandlerContract
{
    private array $serviceTypes = [
        'credit_doctor' => CreditDoctor::class,
        'star_oracle' => StarOracle::class,
        'multipolis' => Multipolis::class,
        'tv_medical' => TVMedical::class,
    ];

    private array $baseFields = [
        'credit_doctor' => ['order_id', 'date_added', 'is_penalty'],
        'star_oracle' => ['order_id', 'date_added'],
        'multipolis' => ['order_id', 'date_added'],
        'tv_medical' => ['order_id', 'date_added'],
    ];

    private array $returnFields = ['return_date', 'return_transaction_id', 'return_by_manager_id'];
    private array $statusFields = ['status', 'return_status'];
    private array $userFields = ['user_id'];

    /**
     * Кэш включенных услуг по order_id
     * @var array<int, array<string, bool>>
     */
    private array $activeByOrderCache = [];

    /**
     * Маппинг русских названий на английские ключи
     * @var array<string, string>
     */
    private array $serviceTypeNormalization = [
        'финансовый доктор' => 'credit_doctor',
        'кредитный доктор' => 'credit_doctor',
        'звездный оракул' => 'star_oracle',
        'консьерж-сервис' => 'multipolis',
        'мультиполис' => 'multipolis',
        'вита-мед' => 'tv_medical',
        'телемедицина' => 'tv_medical',
    ];

    /**
     * Соответствие типов услуг ключам в OrderData
     * Ключ присутствует и value=1 => отключено; иначе включено
     * @var array<string, string[]>
     */
    private array $orderDataKeysMap = [
        'multipolis' => [OrderData::ADDITIONAL_SERVICE_MULTIPOLIS],
        'tv_medical' => [OrderData::ADDITIONAL_SERVICE_TV_MED],
        'star_oracle' => [
            OrderData::ADDITIONAL_SERVICE_SO_REPAYMENT,
            OrderData::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
            OrderData::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
            OrderData::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
        ],
        'credit_doctor' => [OrderData::DISABLE_ADDITIONAL_SERVICE_ON_ISSUE],
    ];

    public function getByOrderId(int $orderId, ?string $serviceType = null, bool $isReturned = true): array
    {
        $result = [];

        if ($serviceType) {
            $serviceType = $this->normalizeServiceType($serviceType);
            if (!$serviceType) {
                return ['success' => false, 'message' => 'Не верно указан тип услуги'];
            }
            $types = [$serviceType];
        } else {
            $types = array_keys($this->serviceTypes);
        }

        foreach ($types as $type) {
            $model = $this->serviceTypes[$type];
            $fields = $this->getFields($type, false, $isReturned);
            $services = $isReturned
                ? (new $model())->getReturnedByOrderId($orderId, $fields)
                : (new $model())->getActiveByOrderId($orderId, $fields);

            foreach ($services as $service) {
                $result[$type][] = $this->formatServiceData($type, $service);
            }
        }

        return ['success' => true, 'data' => $result];
    }

    public function getByUserId(int $userId, bool $isReturned = true): array
    {
        $result = [];

        foreach (array_keys($this->serviceTypes) as $type) {
            $model = $this->serviceTypes[$type];
            $fields = $this->getFields($type, true, $isReturned);
            $services = $isReturned
                ? (new $model())->getReturnedByUserId($userId, $fields)
                : (new $model())->getActiveByUserId($userId, $fields);

            foreach ($services as $service) {
                $result[] = $this->formatServiceData($type, $service, true);
            }
        }

        return ['success' => true, 'data' => $result];
    }

    private function getFields(string $type, bool $includeUserId = false, bool $isReturned = false): array
    {
        $fields = $this->baseFields[$type] ?? [];

        if (!$isReturned) {
            $fields = array_merge($fields, $this->statusFields);
        }
        
        $fields = array_merge($fields, $this->returnFields);
        
        if ($includeUserId) {
            $fields = array_merge($fields, $this->userFields);
        }

        return $fields;
    }

    private function formatServiceData(string $type, array $service, bool $includeStatus = false): array
    {
        [$serviceName, $paymentDescription] = [
            AdditionalServiceKey::getShortLabelByType($type, (bool)($service['is_penalty'] ?? false)),
            ReceiptPaymentType::getPaymentDescription(
                ReceiptPaymentType::getReturnTypeByServiceType($type, (bool)($service['is_penalty'] ?? false))
            ),
        ];

        $result = [
            'service_name' => $serviceName,
            'payment_description' => $paymentDescription,
            'date_added' => $service['date_added'],
        ];

        if ($includeStatus) {
            $result['order_id'] = $service['order_id'];
            $result['service_type'] = $type;
            $isReturned = $this->isReturnedService($service);
            $isActive = $this->isServiceActiveForOrder($service['order_id'], $type);
            $result['is_active'] = $isActive;
            $result['is_returned'] = $isReturned;
            $result['return_date'] = $isReturned ? ($service['return_date'] ?? null) : null;
            $result['return_transaction_id'] = $isReturned ? ($service['return_transaction_id'] ?? null) : null;
            $result['return_by_manager_id'] = $isReturned ? ($service['return_by_manager_id'] ?? null) : null;
        } else {
            $result['return_date'] = $service['return_date'] ?? null;
            $result['return_transaction_id'] = $service['return_transaction_id'] ?? null;
            $result['return_by_manager_id'] = $service['return_by_manager_id'] ?? null;
        }

        return $result;
    }

    private function isReturnedService(array $service): bool
    {
        return ($service['return_status'] ?? 0) == AdditionalServiceReturnStatus::RETURNED
            && !empty($service['return_transaction_id']);
    }

    private function isServiceActiveForOrder(int $orderId, string $type): bool
    {
        // Кэш на order_id: множество отключенных ключей
        if (!isset($this->activeByOrderCache[$orderId])) {
            $rows = (new OrderData())->select('key', [
                'order_id' => $orderId,
                'value' => 1,
            ])->getData();

            $disabledKeys = [];
            foreach ($rows as $row) {
                if (is_array($row)) {
                    if (isset($row['key'])) $disabledKeys[] = $row['key'];
                    elseif (isset($row[0])) $disabledKeys[] = $row[0];
                } else {
                    $disabledKeys[] = $row;
                }
            }

            // Кэшируем как set отключенных ключей
            $this->activeByOrderCache[$orderId] = array_fill_keys($disabledKeys, true);
        }

        $disabledSet = $this->activeByOrderCache[$orderId];
        $keys = $this->orderDataKeysMap[$type] ?? [];

        // Если нет известных ключей — считаем включенной
        if (empty($keys)) return true;

        // Сервис включен, если есть ключ, который НЕ в списке отключенных
        foreach ($keys as $k) {
            if (empty($disabledSet[$k])) return true;
        }
        return false;
    }

    /**
     * Нормализует тип услуги: преобразует русские названия в английские ключи (используется для suvvy)
     */
    private function normalizeServiceType(?string $serviceType): ?string
    {
        if (!$serviceType) {
            return null;
        }

        $lowerType = mb_strtolower(trim($serviceType));

        // Если это русское название — преобразуем в английский ключ
        if (isset($this->serviceTypeNormalization[$lowerType])) {
            return $this->serviceTypeNormalization[$lowerType];
        }

        // Если это уже английский ключ и он существует — возвращаем как есть
        if (isset($this->serviceTypes[$lowerType])) {
            return $lowerType;
        }

        return null;
    }
}
