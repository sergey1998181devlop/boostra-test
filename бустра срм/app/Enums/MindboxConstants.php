<?php

namespace App\Enums;

/**
 * Константы для интеграции с Mindbox
 * Используются в MindBoxCron, MindboxExportService, CsvGenerator
 */
class MindboxConstants
{
    /**
     * Начало импорта по умолчанию
     */
    public const START_DATE_USERS = '2025-07-01';
    public const START_DATE_ORDERS = '2025-08-01';

    /**
     * Статусы заказов, при которых передаётся стоимость ордера.
     * Для всех остальных статусов передаётся 0.
     */
    public const ORDER_STATUSES_WITH_AMOUNT = [
        '5.Выдан',
        '6.Закрыт',
    ];

    /**
     * Маппинг статусов заказов для основного займа (L1)
     */
    public const ORDER_STATUS_MAP = [
        '2.Отказано' => 'Rejection',
        '3.Одобрено' => 'Approved',
        '5.Выдан' => 'Active',
        '7.Технический отказ' => 'Annulment',
        '6.Закрыт' => 'Closed',
    ];

    /**
     * Дефолтный статус заказа, если не найден маппинг
     */
    public const DEFAULT_ORDER_STATUS = 'Application';

    /**
     * Маппинг типов услуг на product_id
     */
    public const ORDER_LINE_MAP = [
        'star_oracle' => 'L6',
        'vitamed' => 'L5',
        'concierge' => 'L4',
        'credit_doctor' => 'L3',
        'prolongation' => 'L2',
        'loan_body' => 'L1',
    ];

    /**
     * Статусы дополнительных услуг, которые считаются отмененными
     */
    public const ADDON_CANCELLED_STATUSES = [
        'CANCELLED',
        'REMOVED',
        'ERROR',
    ];

    /**
     * Статусы дополнительных услуг
     */
    public const ADDON_STATUS_SUCCESS = 'SUCCESS';
    public const ADDON_STATUS_NEW = 'NEW';

    /**
     * Статус для отмененных услуг
     */
    public const CANCELLED_STATUS = 'Annulment';

    /**
     * Статус для одобренных услуг
     */
    public const APPROVED_STATUS = 'Approved';

    /**
     * Размер batch для обработки данных (export)
     */
    public const DEFAULT_BATCH_SIZE = 50000;

    /**
     * Размер batch для обработки пользователей (cron)
     */
    public const BATCH_SIZE_USER = 100;

    /**
     * Размер batch для обработки заказов (cron)
     */
    public const BATCH_SIZE_ORDER = 100;

    /**
     * Timeout для Mindbox SDK (секунды)
     */
    public const MINDBOX_SDK_TIMEOUT = 10;

    /**
     * Операции по обработке пользователя в cron
     * Обрабатываются в указанном порядке
     */
    public const USER_OPERATIONS = ['user_created', 'user_authorized', 'user_updated'];

    /**
     * Операции по обработке заказов в cron
     * Обрабатываются в указанном порядке
     */
    public const ORDER_OPERATIONS = ['order_created_mb', 'order_send_mb'];

    /**
     * Маппинг операций по заказам
     */
    public const ORDER_OPERATION_MAP = [
        'order_created_mb' => ['operation' => '7step', 'sync' => true],
        'order_send_mb' => ['operation' => 'ZakazIzmenenieDannyx', 'sync' => false],
    ];

    /**
     * Маппинг операций по клиентам
     */
    public const USER_OPERATION_MAP = [
        'user_created' => ['operation' => 'registerCustomer', 'sync' => true],
        'user_authorized' => ['operation' => 'authorizeCustomer', 'sync' => true],
        'user_updated' => ['operation' => 'editCustomer', 'sync' => false],
    ];

    /**
     * Проверка, должна ли передаваться цена для статуса
     * @param string|null $status
     * @return bool
     */
    public static function shouldIncludePayments(?string $status): bool
    {
        return in_array($status, self::ORDER_STATUSES_WITH_AMOUNT, true);
    }

    /**
     * Получить статус заказа для Mindbox
     * @param string|null $status1c
     * @return string
     */
    public static function getOrderStatus(?string $status1c): string
    {
        return self::ORDER_STATUS_MAP[$status1c] ?? self::DEFAULT_ORDER_STATUS;
    }

    /**
     * Получить статус дополнительной услуги
     * @param string|null $addonStatus
     * @param string|null $loanStatus
     * @return string
     */
    public static function getAddonStatus(?string $addonStatus, ?string $loanStatus): string
    {
        if ($addonStatus === self::ADDON_STATUS_SUCCESS) {
            return $loanStatus;
        }

        if (in_array($addonStatus, self::ADDON_CANCELLED_STATUSES, true)) {
            return self::CANCELLED_STATUS;
        }

        if ($addonStatus === self::ADDON_STATUS_NEW) {
            return self::APPROVED_STATUS;
        }

        return self::DEFAULT_ORDER_STATUS;
    }
}