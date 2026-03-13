<?php

namespace App\Modules\Shared\AdditionalServices\Enum;

use MyCLabs\Enum\Enum;

/**
 * Перечисление ключей дополнительных услуг.
 * Оптимизированная версия с улучшенной типизацией.
 *
 * @method static self TV_MED()
 * @method static self MULTIPOLIS()
 * @method static self PARTIAL_REPAYMENT()
 * @method static self HALF_PARTIAL_REPAYMENT()
 * @method static self REPAYMENT()
 * @method static self ON_ISSUE()
 * @method static self HALF_REPAYMENT()
 * @method static self SO_REPAYMENT()
 * @method static self HALF_SO_REPAYMENT()
 * @method static self SO_PARTIAL_REPAYMENT()
 * @method static self HALF_SO_PARTIAL_REPAYMENT()
 */
class AdditionalServiceKey extends Enum
{
    // Константы ключей
    private const TV_MED = 'additional_service_tv_med';
    private const MULTIPOLIS = 'additional_service_multipolis';
    private const PARTIAL_REPAYMENT = 'additional_service_partial_repayment';
    private const HALF_PARTIAL_REPAYMENT = 'half_additional_service_partial_repayment';
    private const REPAYMENT = 'additional_service_repayment';
    private const ON_ISSUE = 'disable_additional_service_on_issue';
    private const HALF_REPAYMENT = 'half_additional_service_repayment';
    private const SO_REPAYMENT = 'additional_service_so_repayment';
    private const HALF_SO_REPAYMENT = 'half_additional_service_so_repayment';
    private const SO_PARTIAL_REPAYMENT = 'additional_service_so_partial_repayment';
    private const HALF_SO_PARTIAL_REPAYMENT = 'half_additional_service_so_partial_repayment';

    /**
     * @var array<string, array{label: string, stage: string}>
     */
    private const SERVICE_CONFIG = [
        self::TV_MED => [
            'label' => 'Вита-мед',
            'stage' => AdditionalServiceStage::PROLONGATION,
        ],
        self::MULTIPOLIS => [
            'label' => 'Консьерж',
            'stage' => AdditionalServiceStage::PROLONGATION,
        ],
        self::PARTIAL_REPAYMENT => [
            'label' => 'Доп. услуга на частичном закрытии',
            'stage' => AdditionalServiceStage::PARTIAL_REPAYMENT,
        ],
        self::HALF_PARTIAL_REPAYMENT => [
            'label' => 'Доп. услуга на частичном закрытии 50%',
            'stage' => AdditionalServiceStage::PARTIAL_REPAYMENT,
        ],
        self::REPAYMENT => [
            'label' => 'Доп. услуга на закрытии',
            'stage' => AdditionalServiceStage::FULL_REPAYMENT,
        ],
        self::ON_ISSUE => [
            'label' => 'Доп. услуга при выдаче',
            'stage' => AdditionalServiceStage::ISSUE,
        ],
        self::HALF_REPAYMENT => [
            'label' => 'Доп. услуга на закрытии 50%',
            'stage' => AdditionalServiceStage::FULL_REPAYMENT,
        ],
        self::SO_REPAYMENT => [
            'label' => 'Звездный Оракул на закрытии',
            'stage' => AdditionalServiceStage::FULL_REPAYMENT,
        ],
        self::HALF_SO_REPAYMENT => [
            'label' => 'Звездный Оракул на закрытии 50%',
            'stage' => AdditionalServiceStage::FULL_REPAYMENT,
        ],
        self::SO_PARTIAL_REPAYMENT => [
            'label' => 'Звездный Оракул на частичном закрытии',
            'stage' => AdditionalServiceStage::PARTIAL_REPAYMENT,
        ],
        self::HALF_SO_PARTIAL_REPAYMENT => [
            'label' => 'Звездный Оракул на частичном закрытии 50%',
            'stage' => AdditionalServiceStage::PARTIAL_REPAYMENT,
        ],
    ];

    /**
     * Короткие человекочитаемые имена услуг.
     * @var array<string, string>
     */
    private const SHORT_LABELS = [
        self::TV_MED => 'Вита-мед',
        self::MULTIPOLIS => 'Консьерж сервис',
        self::PARTIAL_REPAYMENT => 'Доп. услуга',
        self::HALF_PARTIAL_REPAYMENT => 'Доп. услуга 50%',
        self::REPAYMENT => 'Доп. услуга',
        self::ON_ISSUE => 'Доп. услуга',
        self::HALF_REPAYMENT => 'Доп. услуга 50%',
        self::SO_REPAYMENT => 'Звездный Оракул',
        self::HALF_SO_REPAYMENT => 'Звездный Оракул',
        self::SO_PARTIAL_REPAYMENT => 'Звездный Оракул',
        self::HALF_SO_PARTIAL_REPAYMENT => 'Звездный Оракул',
    ];

    public const LABEL_FINANCIAL_DOCTOR = 'Финансовый доктор';
    public const LABEL_CREDIT_DOCTOR = 'Кредитный доктор';
    public const LABEL_STAR_ORACLE = 'Звездный Оракул';
    public const LABEL_MULTIPOLIS = 'Консьерж сервис';
    public const LABEL_TV_MED = 'Вита-мед';

    /**
     * Получить название услуги.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::SERVICE_CONFIG[$this->getValue()]['label'];
    }

    /**
     * Короткое имя услуги (без уточнения этапа/процента).
     */
    public function getShortLabel(): string
    {
        return self::SHORT_LABELS[$this->getValue()] ?? $this->getLabel();
    }

    /**
     * Короткое имя по типу услуги из handler'а (unified API).
     */
    public static function getShortLabelByType(string $type, bool $isPenalty = false): string
    {
        if ($type === 'credit_doctor') {
            return $isPenalty ? self::LABEL_CREDIT_DOCTOR : self::LABEL_FINANCIAL_DOCTOR;
        }
        $map = [
            'star_oracle' => self::SHORT_LABELS[self::SO_REPAYMENT] ?? self::LABEL_STAR_ORACLE,
            'multipolis' => self::SHORT_LABELS[self::MULTIPOLIS] ?? self::LABEL_MULTIPOLIS,
            'tv_medical' => self::SHORT_LABELS[self::TV_MED] ?? self::LABEL_TV_MED,
        ];
        return $map[$type] ?? '';
    }

    /**
     * Получить этап, к которому привязана услуга.
     *
     * @return AdditionalServiceStage
     */
    public function getStage(): AdditionalServiceStage
    {
        return new AdditionalServiceStage(self::SERVICE_CONFIG[$this->getValue()]['stage']);
    }

    /**
     * Возвращает полный список услуг в виде массива.
     *
     * @return array<int, array{key: string, label: string}>
     */
    public static function keyLabelList(): array
    {
        $list = [];
        foreach (self::SERVICE_CONFIG as $key => $config) {
            $list[] = [
                'key' => $key,
                'label' => $config['label'],
            ];
        }
        return $list;
    }

    /**
     * Возвращает услуги, сгруппированные по этапам.
     *
     * @return array<string, array<int, string>>
     */
    public static function getServicesByStage(): array
    {
        $grouped = [];
        foreach (self::SERVICE_CONFIG as $key => $config) {
            $stage = $config['stage'];
            if (!isset($grouped[$stage])) {
                $grouped[$stage] = [];
            }
            $grouped[$stage][] = $key;
        }
        return $grouped;
    }

    /**
     * Получить все услуги для конкретного этапа.
     *
     * @param AdditionalServiceStage|string $stage
     * @return array<int, string>
     */
    public static function getServicesForStage($stage): array
    {
        $stageValue = $stage instanceof AdditionalServiceStage ? $stage->getValue() : $stage;
        $servicesByStage = self::getServicesByStage();
        return $servicesByStage[$stageValue] ?? [];
    }

    /**
     * Получить все доступные этапы.
     *
     * @return array<int, AdditionalServiceStage>
     */
    public static function getAllStages(): array
    {
        return array_map(function ($value) {
            return new AdditionalServiceStage($value);
        }, AdditionalServiceStage::values());
    }

    /**
     * Возвращает список ключей услуг с префиксом "additional_service_".
     *
     * @return array<int, string>
     */
    public static function getAdditionalServiceList(): array
    {
        return array_values(array_filter(array_keys(self::SERVICE_CONFIG), function ($key) {
            return strpos($key, 'additional_service_') === 0 && strpos($key, 'half_') !== 0;
        }));
    }

    /**
     * Возвращает список ключей услуг с префиксом "half_additional_service_".
     *
     * @return array<int, string>
     */
    public static function getHalfAdditionalServiceList(): array
    {
        return array_values(array_filter(array_keys(self::SERVICE_CONFIG), function ($key) {
            return strpos($key, 'half_additional_service_') === 0;
        }));
    }

    /**
     * Проверить, существует ли ключ услуги.
     *
     * @param string $key
     * @return bool
     */
    public static function isValidKey($key): bool
    {
        return isset(self::SERVICE_CONFIG[$key]);
    }
}