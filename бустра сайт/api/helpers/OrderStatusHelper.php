<?php

/**
 * Хелпер для работы со статусами заявок
 *
 * Централизует логику определения состояния заявки для отображения в ЛК.
 * Использует константы из Orders.php вместо магических чисел.
 *
 * @see Orders - константы статусов
 */

require_once __DIR__ . '/../Orders.php';
require_once __DIR__ . '/../Simpla.php';

class OrderStatusHelper
{
    /** Причины отказа, для которых показываем кнопку "Узнай причину отказа за 49р" */
    public const PAID_REASON_IDS = [1, 5, 7, 9, 12, 14, 18, 19, 22, 23, 28, 38];
    /** Технические причины отказа, которые не показываем в ЛК */
    public const HIDDEN_REASON_IDS = [10, 34, 36, 65];
    /** Причины отказа, при которых нельзя показывать форму подачи новой заявки */
    public const NO_NEW_ORDER_FORM_REASON_IDS = [65];

    /** Статусы ожидания автоперевода */
    public const STATUS_GROUP_WAITING_TRANSFER = [
        Orders::ORDER_STATUS_CRM_AUTOCONFIRM,  // 15
        Orders::STATUS_WAIT_VIRTUAL_CARD,      // 16
        Orders::STATUS_WAIT_PDN_CALCULATION,   // 18
    ];

    /** Статусы для блока "подписан / выдан" */
    public const STATUS_GROUP_ISSUED = [
        Orders::STATUS_SIGNED,             // 8
        Orders::STATUS_PROCESS,            // 9
        Orders::STATUS_WAIT_CARD,          // 14
        Orders::STATUS_WAIT_VIRTUAL_CARD,  // 16
    ];

    /** Статусы, при которых не показываем accept_credit (даже если 1С=Одобрено) */
    public const STATUS_GROUP_EXCLUDE_FROM_APPROVED = [
        Orders::STATUS_REJECTED,           // 3
        Orders::STATUS_SIGNED,             // 8
        Orders::STATUS_PROCESS,            // 9
        Orders::STATUS_CONFIRMED,          // 10
        Orders::STATUS_NOT_ISSUED,         // 11
        Orders::STATUS_WAIT,               // 13
        Orders::STATUS_WAIT_CARD,          // 14
        Orders::STATUS_WAIT_VIRTUAL_CARD,  // 16
        Orders::STATUS_COOLING,            // 17
    ];

    /** Активные статусы заявки, блокирующие подачу новой */
    public const STATUS_GROUP_ACTIVE = [
        Orders::STATUS_NEW,                    // 1
        Orders::STATUS_APPROVED,               // 2
        Orders::STATUS_SIGNED,                 // 8
        Orders::STATUS_PROCESS,                // 9
        Orders::STATUS_CONFIRMED,              // 10
        Orders::STATUS_WAIT_CARD,              // 14
        Orders::STATUS_WAIT_VIRTUAL_CARD,      // 16
        Orders::ORDER_STATUS_CRM_WAITING,      // 4
        Orders::ORDER_STATUS_CRM_CORRECTION,   // 5
        Orders::ORDER_STATUS_CRM_CORRECTED,    // 6
        Orders::STATUS_COOLING,                // 17
        Orders::ORDER_STATUS_CRM_AUTOCONFIRM,  // 15
        Orders::STATUS_NOT_ISSUED,             // 11
        Orders::STATUS_WAIT_PDN_CALCULATION,   // 18
    ];

    /**
     * Флаги для отображения заявки в ЛК.
     *
     * @param array $order
     * @param object|null $reason Объект причины отказа (опционально)
     * @param array|null $mainOrder Основной займ (для проверки cross_order)
     * @return array<string,mixed>
     */
    public static function getViewFlags(array $order, ?object $reason = null, ?array $mainOrder = null): array
    {
        $status   = (int)($order['status'] ?? 0);
        $status1C = (string)($order['1c_status'] ?? ($order['status_1c'] ?? ''));
        $reasonId = (int)($order['reason_id'] ?? 0);

        $isApproved1C = $status1C === Orders::ORDER_1C_STATUS_APPROVED;
        $isClosed1C   = $status1C === Orders::ORDER_1C_STATUS_CLOSED;
        $isIssued1C   = $status1C === Orders::ORDER_1C_STATUS_CONFIRMED;

        $isSignedWaitingTransfer = $status === Orders::STATUS_SIGNED
            && !$isIssued1C
            && $status !== Orders::STATUS_WAIT_CARD;

        $isConfirmedWaitingTransfer = $status === Orders::STATUS_CONFIRMED
            && !$isIssued1C;

        $reasonBlockDate = self::calculateReasonBlockDate($reason, $order['date'] ?? '');
        $hasReasonBlock = $reasonBlockDate !== null;

        $isTransferDelay = $status === Orders::STATUS_WAIT;
        $isCrossOrder = ($order['utm_source'] ?? '') === Orders::UTM_SOURCE_CROSS_ORDER;
        if ($isCrossOrder && !empty($mainOrder) && !self::isOrderIssued($mainOrder)) {
            $isTransferDelay = false;
        }

        return [
            // CRM статусы
            'is_empty'         => $status === 0,
            'is_new'           => $status === Orders::STATUS_NEW,
            'is_approved'      => $status === Orders::STATUS_APPROVED,
            'is_rejected'      => $status === Orders::STATUS_REJECTED,
            'is_photo_error'   => $status === Orders::ORDER_STATUS_CRM_CORRECTION,
            'is_signed'        => $status === Orders::STATUS_SIGNED,
            'is_process'       => $status === Orders::STATUS_PROCESS,
            'is_confirmed'     => $status === Orders::STATUS_CONFIRMED,
            'is_not_issued'    => $status === Orders::STATUS_NOT_ISSUED,
            'is_transfer_delay'=> $isTransferDelay,
            'is_wait_card'     => $status === Orders::STATUS_WAIT_CARD,
            'is_cooling_off'   => $status === Orders::STATUS_COOLING,
            'is_crm_waiting'   => $status === Orders::ORDER_STATUS_CRM_WAITING,
            'is_corrected'     => $status === Orders::ORDER_STATUS_CRM_CORRECTED,

            // Составные UI-состояния
            'is_waiting_transfer' => !$isClosed1C && (
                    in_array($status, self::STATUS_GROUP_WAITING_TRANSFER, true)
                    || $isSignedWaitingTransfer
                    || $isConfirmedWaitingTransfer
                ),
            'is_show_accept_credit' => $isApproved1C
                && !in_array($status, self::STATUS_GROUP_EXCLUDE_FROM_APPROVED, true),
            'is_show_issued_block' => ($isIssued1C && ($status === Orders::STATUS_SIGNED || $status === Orders::STATUS_CONFIRMED))
                || (in_array($status, self::STATUS_GROUP_ISSUED, true) && $status !== Orders::STATUS_SIGNED && $status !== Orders::STATUS_CONFIRMED)
                || ($isClosed1C && $status === Orders::STATUS_CONFIRMED),
            'is_show_loan_form' => $isClosed1C,

            // Статусы 1С
            'is_1c_approved' => $isApproved1C,
            'is_1c_issued'   => $isIssued1C,
            'is_1c_closed'   => $isClosed1C,

            // Прочее
            'can_show_paid_reason' => self::canShowPaidRejectReason($order),
            'is_hidden_reject_reason' => ($status === Orders::STATUS_REJECTED)
                && in_array($reasonId, self::HIDDEN_REASON_IDS, true),
            'is_cross_order'       => $isCrossOrder,

            // Флаги моратория для конкретной заявки
            'has_reason_block'   => $hasReasonBlock,
            'reason_block_date'  => $reasonBlockDate,
        ];
    }

    /**
     * Можно ли показать кнопку "Узнай причину отказа"
     *
     * @param array $order Массив данных заявки
     * @return bool
     */
    public static function canShowPaidRejectReason(array $order): bool
    {
        $reasonId = (int)($order['reason_id'] ?? 0);
        $paymentRefuser = !empty($order['payment_refuser']);

        return in_array($reasonId, self::PAID_REASON_IDS)
            && !$paymentRefuser
            && $reasonId > 0;
    }

    /**
     * Рассчитывает дату окончания моратория для отображения в ЛК.
     *
     * @param object|null $reason Объект причины отказа
     * @param string $orderDate Дата заявки
     * @return int|string|null 999 для бессрочного, дата Y-m-d H:i:s если активен, null если нет/истёк
     */
    public static function calculateReasonBlockDate(?object $reason, string $orderDate)
    {
        if (empty($reason) || empty($orderDate)) {
            return null;
        }
        $maratory = (int)$reason->maratory;
        if ($maratory <= 0) {
            return null;
        }
        if ($maratory === 999) {
            return 999;
        }
        $until = strtotime($orderDate) + 86400 * $maratory;
        return time() < $until ? date('Y-m-d H:i:s', $until) : null;
    }

    /**
     * Определяет тип отображения моратория для UI.
     *
     * @param object|null $reason Объект причины отказа
     * @return string 'warning' (жёлтый, <=10 дней), 'error' (красный, >10 дней), 'none' (нет моратория)
     */
    public static function getMoratoriumDisplayType(?object $reason): string
    {
        $maratory = !empty($reason) ? (int)$reason->maratory : 0;
        if ($maratory <= 0) {
            return 'none';
        }
        if ($maratory <= 10) {
            return 'warning';
        }
        return 'error';
    }

    /**
     * Проверяет, есть ли активная заявка, блокирующая подачу новой.
     *
     * @param array $order Массив данных заявки
     * @return bool
     */
    public static function hasActiveOrder(array $order): bool
    {
        if (empty($order)) {
            return false;
        }

        $status1C = (string)($order['1c_status'] ?? ($order['status_1c'] ?? ''));
        if ($status1C === Orders::ORDER_1C_STATUS_CLOSED) {
            return false;
        }

        return in_array((int)($order['status'] ?? 0), self::STATUS_GROUP_ACTIVE, true);
    }

    /**
     * Проверяет, выдан ли займ (основной займ для cross_order).
     *
     * @param array $order Массив данных заявки
     * @return bool
     */
    public static function isOrderIssued(array $order): bool
    {
        if (empty($order)) {
            return false;
        }
        $status = (int)($order['status'] ?? 0);
        $status1C = (string)($order['1c_status'] ?? ($order['status_1c'] ?? ''));

        return in_array($status, self::STATUS_GROUP_ISSUED, true)
            || $status1C === Orders::ORDER_1C_STATUS_CONFIRMED;
    }

    /**
     * Рассчитывает флаги для отображения формы новой заявки и блока моратория.
     *
     * @param array $ctx Контекст с параметрами:
     *   - last_order: array
     *   - user: object
     *   - reason: object|null
     *   - reason_block: mixed
     *   - repeat_loan_block: mixed
     *   - next_loan_mandatory: mixed
     *   - new_order_maratorium: mixed
     *   - order1c_status: string
     *   - files_ok: bool
     * @return array{show_moratorium_only: bool, can_show_new_order_form: bool}
     */
    public static function getNewOrderFormFlags(array $ctx): array
    {
        $lastOrder = $ctx['last_order'] ?? [];
        $user = $ctx['user'];
        $reason = $ctx['reason'] ?? null;
        $reasonBlock = $ctx['reason_block'] ?? null;
        $repeatLoanBlock = $ctx['repeat_loan_block'] ?? null;
        $nextLoanMandatory = $ctx['next_loan_mandatory'] ?? null;
        $newOrderMaratorium = $ctx['new_order_maratorium'] ?? null;
        $order1cStatus = (string)($ctx['order1c_status'] ?? '');
        $filesOk = (bool)($ctx['files_ok'] ?? false);

        $maratory = !empty($reason) ? (int)$reason->maratory : 0;
        $displayType = self::getMoratoriumDisplayType($reason);
        $showMoratoriumOnly = ($displayType === 'warning');

        $lastOrderReasonId = (int)($lastOrder['reason_id'] ?? 0);
        $blockedByReason = in_array($lastOrderReasonId, self::NO_NEW_ORDER_FORM_REASON_IDS, true);
        $allowNewOrderByReason = ($maratory === 0) && !$blockedByReason;

        $canShowNewOrderForm = false;
        if (!empty($lastOrder) && (int)$lastOrder['status'] === Orders::STATUS_REJECTED) {
            $canShowNewOrderForm = !empty($nextLoanMandatory)
                || (empty($reasonBlock) && empty($repeatLoanBlock));
        }

        if ($allowNewOrderByReason) {
            $canShowNewOrderForm = true;
        }

        if (self::hasActiveOrder($lastOrder)) {
            $canShowNewOrderForm = false;
        }

        $hasFormRestriction = empty($user->not_rating_maratorium_valid)
            && ((isset($reasonBlock) && $reasonBlock !== 999)
                || !empty($repeatLoanBlock)
                || !empty($newOrderMaratorium));
        if ($hasFormRestriction && !$allowNewOrderByReason) {
            $canShowNewOrderForm = false;
        }

        if ($reasonBlock !== null) {
            $canShowNewOrderForm = false;
        }

        if ($reasonBlock === null
            && !self::hasActiveOrder($lastOrder)
            && empty($repeatLoanBlock)
            && empty($newOrderMaratorium)
            && !empty($lastOrder)) {
            $canShowNewOrderForm = true;
        }

        if ($order1cStatus === Orders::ORDER_1C_STATUS_CLOSED && $maratory === 0 && $filesOk) {
            $canShowNewOrderForm = true;
        }

        return [
            'show_moratorium_only' => $showMoratoriumOnly,
            'can_show_new_order_form' => $canShowNewOrderForm,
        ];
    }
}
