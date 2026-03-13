<?php

declare(strict_types=1);

namespace App\Service;

use Database;
use Import1c;
use Organizations;
use Soap1c;
use Users;

/**
 * Сервис импорта балансов пользователей из 1С.
 *
 * Обеспечивает сброс балансов по дате, получение заказов из 1С (PDL и IL),
 * импорт балансов в БД и проверку полноты импорта с логированием.
 */
class UserBalanceImportService
{
    private const LOG_CHANNEL = 'user_balance_import';

    /** @var Users */
    private $users;

    /** @var Soap1c */
    private $soap;

    /** @var Import1c */
    private $import1c;

    /** @var Organizations */
    private $organizations;

    /** @var VoximplantLogger */
    private $logger;

    /** @var Database */
    private $db;

    public function __construct(
        Users $users,
        Soap1c $soap,
        Import1c $import1c,
        Organizations $organizations,
        VoximplantLogger $logger,
        Database $db
    ) {
        $this->users = $users;
        $this->soap = $soap;
        $this->import1c = $import1c;
        $this->organizations = $organizations;
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Сброс балансов для указанной даты оплаты.
     *
     * @param string $date Дата в формате Y-m-d
     * @return array{reset_count: int}
     */
    public function resetBalancesForDate(string $date): array
    {
        $startTime = microtime(true);
        $method = 'resetBalancesForDate';

        $context = ['date' => $date];

        try {
            $this->logger->logRequest(self::LOG_CHANNEL, $method, ['date' => $date], $context);

            $countStartTime = microtime(true);
            $countBefore = $this->getBalancesCountForDate($date);
            $countDuration = microtime(true) - $countStartTime;

            $resetStartTime = microtime(true);
            $this->users->reset_balances_on_payment_date($date);
            $resetDuration = microtime(true) - $resetStartTime;

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess(self::LOG_CHANNEL, $method, [
                'reset_count' => $countBefore,
                'durations' => [
                    'count_before' => round($countDuration, 2),
                    'reset_balances' => round($resetDuration, 2),
                    'total' => round($duration, 2),
                ],
            ], $duration, $context);

            return ['reset_count' => $countBefore];
        } catch (\Throwable $e) {
            $this->logger->logError(self::LOG_CHANNEL, $method, $e, $context);
            throw $e;
        }
    }

    /**
     * Импорт балансов для сайта: сброс, получение заказов из 1С (PDL и IL), обработка и проверка полноты.
     *
     * @param string $siteId ID сайта (например, 'boostra')
     * @param string $company Партнёр для 1С (например, 'Boostra')
     * @return array{orders_pdl: int, orders_il: int, processed_pdl: array, processed_il: array, completeness: array}
     */
    public function importBalancesForSite(string $siteId, string $company = 'Boostra'): array
    {
        $startTime = microtime(true);
        $method = 'importBalancesForSite';

        $context = ['site_id' => $siteId, 'company' => $company];

        try {
            $this->logger->logRequest(self::LOG_CHANNEL, $method, [
                'site_id' => $siteId,
                'company' => $company,
            ], $context);

            $dateToday = date('Y-m-d');
            $dateTodayIl = date('Ymd000000');

            $innsStartTime = microtime(true);
            $siteInns = $this->organizations->get_inns_by_site_id($siteId);
            $allActiveInns = $this->organizations->get_inn_for_recurrents();
            $allInns = array_values(array_unique(array_merge($siteInns, $allActiveInns)));
            $innsDuration = microtime(true) - $innsStartTime;

            $soapStartTime = microtime(true);
            $partner = $this->soap->getPartnerParam($company);
            $ordersPDL = $this->soap->get_orders_by_date_payment($dateToday, $partner, $allInns);
            $ordersIL = $this->soap->get_orders_by_date_payment_Il($dateTodayIl, $partner, $allInns);
            $soapDuration = microtime(true) - $soapStartTime;

            $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_orders_fetched', [
                'orders_pdl' => is_array($ordersPDL) ? count($ordersPDL) : 0,
                'orders_il' => is_array($ordersIL) ? count($ordersIL) : 0,
                'durations' => [
                    'reset_balances' => round($resetDuration, 2),
                    'get_inns' => round($innsDuration, 2),
                    'soap_requests' => round($soapDuration, 2),
                ],
            ], $soapDuration, $context);

            if ((empty($ordersPDL) || !is_array($ordersPDL)) && (empty($ordersIL) || !is_array($ordersIL))) {
                $completenessStartTime = microtime(true);
                $completeness = $this->checkImportCompleteness($dateToday);
                $completenessDuration = microtime(true) - $completenessStartTime;
                
                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess(self::LOG_CHANNEL, $method, [
                    'orders_pdl' => 0,
                    'orders_il' => 0,
                    'skipped' => 'no_orders',
                    'durations' => [
                        'reset_balances' => round($resetDuration, 2),
                        'get_inns' => round($innsDuration, 2),
                        'soap_requests' => round($soapDuration, 2),
                        'check_completeness' => round($completenessDuration, 2),
                        'total' => round($duration, 2),
                    ],
                ], $duration, $context);
                return [
                    'orders_pdl' => 0,
                    'orders_il' => 0,
                    'processed_pdl' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
                    'processed_il' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
                    'completeness' => $completeness,
                ];
            }

            $processPDLStartTime = microtime(true);
            $processedPDL = $this->processOrders(is_array($ordersPDL) ? $ordersPDL : [], false);
            $processPDLDuration = microtime(true) - $processPDLStartTime;

            $processILStartTime = microtime(true);
            $processedIL = $this->processOrders(is_array($ordersIL) ? $ordersIL : [], true);
            $processILDuration = microtime(true) - $processILStartTime;

            $completenessStartTime = microtime(true);
            $completeness = $this->checkImportCompleteness($dateToday);
            $completenessDuration = microtime(true) - $completenessStartTime;

            $duration = microtime(true) - $startTime;
            $result = [
                'orders_pdl' => is_array($ordersPDL) ? count($ordersPDL) : 0,
                'orders_il' => is_array($ordersIL) ? count($ordersIL) : 0,
                'processed_pdl' => $processedPDL,
                'processed_il' => $processedIL,
                'completeness' => $completeness,
                'durations' => [
                    'reset_balances' => round($resetDuration, 2),
                    'get_inns' => round($innsDuration, 2),
                    'soap_requests' => round($soapDuration, 2),
                    'process_pdl' => round($processPDLDuration, 2),
                    'process_il' => round($processILDuration, 2),
                    'check_completeness' => round($completenessDuration, 2),
                    'total' => round($duration, 2),
                ],
            ];

            $this->logger->logSuccess(self::LOG_CHANNEL, $method, $result, $duration, $context);

            return $result;
        } catch (\Throwable $e) {
            $this->logger->logError(self::LOG_CHANNEL, $method, $e, $context);
            throw $e;
        }
    }

    /**
     * Обработка массива заказов: для IL — доп. поля из SOAP, импорт баланса через Import1c.
     *
     * @param array $orders Массив объектов заказов из 1С
     * @param bool $isIl true для IL-заказов
     * @return array{success: int, errors: int, skipped: int}
     */
    public function processOrders(array $orders, bool $isIl): array
    {
        $startTime = microtime(true);
        $method = 'processOrders';

        $stats = ['success' => 0, 'errors' => 0, 'skipped' => 0];

        $this->logger->logRequest(self::LOG_CHANNEL, $method, [
            'count' => count($orders),
            'is_il' => $isIl,
        ], []);

        foreach ($orders as $order) {
            $clientId = $this->getUserIdByZaimId((string)($order->Клиент ?? ''));

            if ($clientId === null) {
                $stats['skipped']++;
                $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_skip', [
                    'reason' => 'user_not_found',
                    'client_uid' => $order->Клиент ?? null,
                ], 0, []);
                continue;
            }

            if ($isIl && !empty($order->НомерЗайма)) {
                try {
                    $ilDetailsStartTime = microtime(true);
                    $ilDetails = $this->soap->get_il_details($order->НомерЗайма);
                    $ilDetailsDuration = microtime(true) - $ilDetailsStartTime;
                    $order->ПросроченныйДолг_ОД = $ilDetails['ПросроченныйДолг_ОД'] ?? null;
                    $order->ПросроченныйДолг_Процент = $ilDetails['ПросроченныйДолг_Процент'] ?? null;
                    $order->БлижайшийПлатеж_Сумма_ОД = $ilDetails['БлижайшийПлатеж_Сумма_ОД'] ?? null;
                    $order->БлижайшийПлатеж_Сумма_Процент = $ilDetails['БлижайшийПлатеж_Сумма_Процент'] ?? null;
                    
                    if ($ilDetailsDuration > 1.0) {
                        $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_il_details_slow', [
                            'order' => $order->НомерЗайма ?? null,
                            'duration' => round($ilDetailsDuration, 2),
                        ], $ilDetailsDuration, []);
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $this->logger->logError(self::LOG_CHANNEL, $method . '_il_details', $e, [
                        'order' => $order->НомерЗайма ?? null,
                    ]);
                    continue;
                }
            }

            try {
                $importStartTime = microtime(true);
                $this->import1c->import_user_balance($clientId, $order);
                $importDuration = microtime(true) - $importStartTime;
                $stats['success']++;
                
                if ($importDuration > 0.5) {
                    $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_import_slow', [
                        'user_id' => $clientId,
                        'order' => $order->НомерЗайма ?? null,
                        'duration' => round($importDuration, 2),
                    ], $importDuration, []);
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->logger->logError(self::LOG_CHANNEL, $method . '_import', $e, [
                    'user_id' => $clientId,
                    'order' => $order->НомерЗайма ?? null,
                ]);
            }
        }

        $duration = microtime(true) - $startTime;
        $this->logger->logSuccess(self::LOG_CHANNEL, $method, $stats, $duration, []);

        return $stats;
    }

    /**
     * Получить user_id по UID клиента из 1С.
     *
     * @param string $clientUid UID клиента
     * @return int|null
     */
    public function getUserIdByZaimId(string $clientUid): ?int
    {
        if ($clientUid === '') {
            return null;
        }

        $query = $this->db->placehold("SELECT id FROM __users WHERE UID = ?", $clientUid);
        $this->db->query($query);
        $id = $this->db->result('id');

        return $id !== null && $id !== false ? (int)$id : null;
    }

    /**
     * Проверка полноты импорта: количество записей с payment_date = date и ненулевыми балансами.
     *
     * @param string $date Дата в формате Y-m-d
     * @param int|null $expectedCount Ожидаемое количество (для расчёта процента)
     * @return array{found: int, expected: int|null, percent: float|null}
     */
    public function checkImportCompleteness(string $date, ?int $expectedCount = null): array
    {
        $startTime = microtime(true);
        $method = 'checkImportCompleteness';
        
        $countStartTime = microtime(true);
        $found = $this->getBalancesCountForDate($date);
        $countDuration = microtime(true) - $countStartTime;

        $result = [
            'found' => $found,
            'expected' => $expectedCount,
            'percent' => null,
            'count_duration' => round($countDuration, 2),
        ];

        if ($expectedCount !== null && $expectedCount > 0) {
            $result['percent'] = round(100.0 * $found / $expectedCount, 2);
            if ($result['percent'] < 80) {
                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_warning', [
                    'found' => $found,
                    'expected' => $expectedCount,
                    'percent' => $result['percent'],
                    'message' => 'Import completeness below 80%',
                    'duration' => round($duration, 2),
                ], $duration, ['date' => $date]);
            }
        }

        return $result;
    }

    /**
     * Количество записей в s_user_balance с payment_date = date и ненулевыми балансами.
     *
     * @param string $date Дата в формате Y-m-d
     * @param int|null $organizationId Фильтр по организации (опционально)
     * @return int
     */
    public function getBalancesCountForDate(string $date, ?int $organizationId = null): int
    {
        $startTime = microtime(true);
        
        $organizationJoin = '';
        $organizationFilter = '';

        if ($organizationId !== null) {
            $organizationJoin = "
                LEFT JOIN s_contracts AS c ON c.number = ub.zaim_number
                LEFT JOIN s_orders AS o ON o.id = c.order_id
            ";
            $organizationFilter = $this->db->placehold(" AND o.organization_id = ?", $organizationId);
        }

        $queryStartTime = microtime(true);
        $query = $this->db->placehold(
            "SELECT COUNT(*) AS cnt FROM __user_balance ub
             $organizationJoin
             WHERE DATE(ub.payment_date) = ?
             AND (ub.ostatok_od > 0 OR ub.ostatok_percents > 0 OR ub.ostatok_peni > 0)
             $organizationFilter",
            $date
        );
        $this->db->query($query);
        $cnt = $this->db->result('cnt');
        $queryDuration = microtime(true) - $queryStartTime;

        $duration = microtime(true) - $startTime;
        if ($duration > 0.5) {
            $this->logger->logSuccess(self::LOG_CHANNEL, 'getBalancesCountForDate_slow', [
                'date' => $date,
                'organization_id' => $organizationId,
                'count' => $cnt !== null && $cnt !== false ? (int)$cnt : 0,
                'duration' => round($duration, 2),
                'query_duration' => round($queryDuration, 2),
            ], $duration, []);
        }

        return $cnt !== null && $cnt !== false ? (int)$cnt : 0;
    }
}
