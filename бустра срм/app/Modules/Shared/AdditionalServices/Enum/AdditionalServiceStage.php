<?php

namespace App\Modules\Shared\AdditionalServices\Enum;

use MyCLabs\Enum\Enum;

/**
 * Перечисление этапов дополнительных услуг.
 *
 * @method static self PROLONGATION()
 * @method static self PARTIAL_REPAYMENT()
 * @method static self FULL_REPAYMENT()
 * @method static self ISSUE()
 */
class AdditionalServiceStage extends Enum
{
    public const PROLONGATION = 'prolongation';
    public const PARTIAL_REPAYMENT = 'partial_repayment';
    public const FULL_REPAYMENT = 'full_repayment';
    public const ISSUE = 'issue';

    /**
     * @var array<string, array{label: string, order: int}>
     */
    private const STAGE_CONFIG = [
        self::ISSUE => [
            'label' => 'Выдача',
            'order' => 0,
        ],
        self::PROLONGATION => [
            'label' => 'Пролонгация',
            'order' => 1,
        ],
        self::PARTIAL_REPAYMENT => [
            'label' => 'Частичное погашение',
            'order' => 2,
        ],
        self::FULL_REPAYMENT => [
            'label' => 'Полное погашение',
            'order' => 3,
        ],
    ];

    /**
     * Получить название этапа для отображения.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::STAGE_CONFIG[$this->getValue()]['label'];
    }

    /**
     * Получить порядок сортировки этапа.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return self::STAGE_CONFIG[$this->getValue()]['order'];
    }

    /**
     * Получить список всех этапов с названиями.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function keyLabelList(): array
    {
        $list = [];
        foreach (self::STAGE_CONFIG as $value => $config) {
            $list[] = [
                'key' => $value,
                'label' => $config['label'],
            ];
        }
        return $list;
    }

    /**
     * Получить отсортированный список этапов.
     *
     * @return array<int, array{value: string, label: string, order: int}>
     */
    public static function getSortedList(): array
    {
        $list = [];
        foreach (self::STAGE_CONFIG as $value => $config) {
            $list[] = [
                'key' => $value,
                'label' => $config['label'],
                'order' => $config['order'],
            ];
        }

        usort($list, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return $list;
    }

    /**
     * Проверить, является ли этап этапом погашения (частичного или полного).
     *
     * @return bool
     */
    public function isRepayment(): bool
    {
        return in_array($this->getValue(), [
            self::PARTIAL_REPAYMENT,
            self::FULL_REPAYMENT,
        ], true);
    }
}