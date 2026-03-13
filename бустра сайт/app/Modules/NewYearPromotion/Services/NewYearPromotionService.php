<?php

namespace App\Modules\NewYearPromotion\Services;

use Simpla;

class NewYearPromotionService extends Simpla
{
    const PROMO_PREFIX = 'newyear26_sms_pay_link';
    const DISCOUNT_HOURS = 72; // Время действия скидки в часах

    // Типы событий
    const EVENT_LK_OPEN = 'lk_open';
    const EVENT_LINK_CLICKED = 'link_clicked';
    const EVENT_DISCOUNT_BUTTON_CLICKED = 'discount_button_clicked';
    const EVENT_PAY_BUTTON_CLICKED = 'pay_button_clicked';
    const EVENT_PAID = 'paid';

    /**
     * Парсит UTM параметр из SMS ссылки
     * Формат: newyear26_sms_pay_link_like_15_20251203
     *
     * @param string $utm
     * @return array|null ['mkk' => 'like', 'bucket' => '15', 'date' => '20251203']
     */
    public function parseUtm(string $utm): ?array
    {
        if (strpos($utm, self::PROMO_PREFIX) !== 0) {
            return null;
        }

        // Убираем префикс
        $rest = substr($utm, strlen(self::PROMO_PREFIX));

        // Формат: _like_15_20251203 или _like_70150_20251203
        if (preg_match('/^_([^_]+)_(\d+)_(\d{8})$/', $rest, $matches)) {
            return [
                'mkk' => $matches[1],
                'bucket' => $matches[2],
                'date' => $matches[3],
            ];
        }

        return null;
    }

    /**
     * Проверяет, участвует ли пользователь в акции
     * Единственный источник истины - баланс из 1С
     *
     * @param int $userId
     * @param int $orderId
     * @param object|null $balance Баланс из 1С (если не передан, будет получен автоматически)
     * @return bool
     */
    public function isUserInPromo(int $userId, int $orderId, ?object $balance = null): bool
    {
        // Проверяем, включена ли новогодняя акция в настройках
        if (empty((int)$this->settings->newyear_promotion_enabled)) {
            return false;
        }

        // Если баланс не передан, пытаемся получить его из БД
        if (empty($balance)) {
            $order = $this->orders->get_crm_order($orderId);
            if (empty($order)) {
                return false;
            }
            
            $user = $this->users->get_user_by_id($userId);
            if (empty($user) || empty($user->uid)) {
                return false;
            }
            
            // Получаем балансы из 1С
            $response_balances = $this->soap->get_user_balances_array_1c($user->uid);
            if (empty($response_balances) || isset($response_balances['errors'])) {
                return false;
            }
            
            // Получаем контракт для получения номера займа
            $contract = $this->contracts->get_contract_by_params(['order_id' => $order->id, 'user_id' => $userId]);
            
            // Ищем баланс по номеру займа или по id_1c заказа
            $balance_1c = null;
            foreach ($response_balances as $response_balance) {
                // Проверяем по номеру займа из контракта
                if (!empty($contract->number) && $response_balance['НомерЗайма'] == $contract->number) {
                    $balance_1c = (object)$response_balance;
                    break;
                }
                if (!empty($order->id_1c) && $response_balance['Заявка'] == $order->id_1c) {
                    $balance_1c = (object)$response_balance;
                    break;
                }
            }
            
            if (empty($balance_1c)) {
                return false;
            }
            
            $balance = $this->users->make_up_user_balance($userId, $balance_1c);
        }
        
        // Проверяем наличие новогодней скидки в балансе из 1С
        // Правила определения новогодней скидки:
        // 1. Год в поле ДатаСкидки меньше 3999 года - это новогодняя скидка
        // 2. ОстатокОД + ОстатокПроцентов больше, чем СуммаСоСкидкой - тогда есть скидка
        
        $hasNewYearDiscountDate = false;
        if (!empty($balance->discount_date)) {
            try {
                $discountDate = new \DateTime($balance->discount_date);
                $year = (int)$discountDate->format('Y');
                
                // Если год больше 3999 - это новогодняя скидка
                if ($year < 3999) {
                    $hasNewYearDiscountDate = true;
                }
            } catch (\Exception $e) {
                // Если не удалось распарсить дату - считаем, что даты нет
                $hasNewYearDiscountDate = false;
            }
        }
        
        // Проверяем, что ОстатокОД + ОстатокПроцентов больше, чем СуммаСоСкидкой
        $ostatokOd = (float)($balance->ostatok_od ?? 0);
        $ostatokPercents = (float)($balance->ostatok_percents ?? 0);
        $sumWithGrace = (float)($balance->sum_with_grace ?? 0);
        
        $hasDiscountAmount = ($ostatokOd + $ostatokPercents) > $sumWithGrace;

        logger('newyear_promo_check')->info('isUserInPromo', [
            'ostatokOd' => $ostatokOd,
            'ostatokPercents' => $ostatokPercents,
            'sumWithGrace' => $sumWithGrace,
            '$ostatokOd + $ostatokPercents' => $ostatokOd + $ostatokPercents,
            'hasDiscountAmount' => $hasDiscountAmount,
            'hasNewYearDiscountDate' => $hasNewYearDiscountDate
        ]);
        
        return $hasNewYearDiscountDate;
    }

    /**
     * Получает данные об участии пользователя в акции
     *
     * @param int $userId
     * @param int $orderId
     * @return object|null
     */
    public function getPromoData(int $userId, int $orderId): ?object
    {
        $query = $this->db->placehold("
            SELECT * FROM s_newyear_promotion_2026 
            WHERE user_id = ? AND order_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ", $userId, $orderId);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ?: null;
    }

    /**
     * Проверяет, активна ли скидка
     * Использует discount_date из 1С как единственный источник истины
     *
     * @param object|null $userBalance
     * @return bool
     */
    public function isDiscountActive(?object $userBalance = null): bool
    {
        // Используем ту же логику, что и getRemainingTime
        $remainingTime = $this->getRemainingTime($userBalance);
        return $remainingTime > 0;
    }

    /**
     * Получает оставшееся время действия скидки в секундах
     * Единственный источник истины - discount_date из 1С
     * Если discount_date отсутствует, возвращает 0 (акция неактивна)
     *
     * @param object|null $userBalance
     * @return int Оставшееся время в секундах, или 0 если акция неактивна
     */
    public function getRemainingTime(?object $userBalance = null): int
    {
        // Единственный источник истины - discount_date из 1С
        if ($userBalance && $userBalance->discount_date) {
            $endTime = strtotime($userBalance->discount_date);
            $currentTime = time();
            $remaining = $endTime - $currentTime;
            
            // Если дата из 1С слишком большая (больше чем 72 часа от текущей даты),
            // считаем акцию неактивной (возвращаем 0)
            $maxAllowedTime = $currentTime + (self::DISCOUNT_HOURS * 3600);
            if ($endTime > $maxAllowedTime) {
                return 0;
            }

            return max(0, $remaining);
        }

        // Если discount_date отсутствует, акция неактивна
        return 0;
    }

    /**
     * Рассчитывает сумму с учётом скидки
     * Единственный источник истины - баланс из 1С (sum_with_grace, sum_od_with_grace, sum_percent_with_grace)
     *
     * @param object $balance Баланс пользователя из 1С
     * @return array ['total_with_discount' => float, 'discount_applied' => float]
     */
    public function calculateTotalWithDiscount(object $balance): array
    {
        // Проверяем, активна ли скидка (использует discount_date из 1С)
        if (!$this->isDiscountActive($balance)) {
            // Если скидка не активна, возвращаем полную сумму долга
            $ostatokOd = (float)($balance->ostatok_od ?? 0);
            $ostatokPercents = (float)($balance->ostatok_percents ?? 0);
            $ostatokPeni = (float)($balance->ostatok_peni ?? 0);
            $penalty = (float)($balance->penalty ?? 0);
            $totalDebt = $ostatokOd + $ostatokPercents + $ostatokPeni + $penalty;
            
            return [
                'total_with_discount' => $totalDebt,
                'discount_applied' => 0,
            ];
        }
        
        // Используем значения из 1С
        // СуммаСоСкидкой - общая сумма к оплате со скидкой
        $totalWithDiscount = (float)($balance->sum_with_grace ?? 0);
        
        // СуммаСоСкидкойПроцент - сумма скидки
        $discountApplied = (float)($balance->sum_percent_with_grace ?? 0);
        
        // Если значения из 1С не переданы, возвращаем без скидки
        if ($totalWithDiscount <= 0) {
            $ostatokOd = (float)($balance->ostatok_od ?? 0);
            $ostatokPercents = (float)($balance->ostatok_percents ?? 0);
            $ostatokPeni = (float)($balance->ostatok_peni ?? 0);
            $penalty = (float)($balance->penalty ?? 0);
            $totalDebt = $ostatokOd + $ostatokPercents + $ostatokPeni + $penalty;
            
            return [
                'total_with_discount' => $totalDebt,
                'discount_applied' => 0,
            ];
        }
        
        return [
            'total_with_discount' => $totalWithDiscount,
            'discount_applied' => $discountApplied,
        ];
    }

    /**
     * Логирует событие воронки
     *
     * @param int $userId
     * @param int $orderId
     * @param string $event Тип события
     * @param array $meta Дополнительные данные
     * @return void
     */
    public function logEvent(int $userId, int $orderId, string $event, array $meta = []): void
    {
        try {
            // Получаем promotion_id, если есть запись в s_newyear_promotion_2026 (опционально)
            $promoData = $this->getPromoData($userId, $orderId);
            $promotionId = !empty($promoData) ? $promoData->id : 'NULL';

            // Логируем событие (promotion_id может быть NULL)
            $query = $this->db->placehold("
                INSERT INTO s_newyear_promotion_2026_events 
                SET promotion_id = ?, 
                    user_id = ?,
                    order_id = ?,
                    event = ?,
                    meta = ?,
                    created_at = NOW()
            ", $promotionId, $userId, $orderId, $event, json_encode($meta, JSON_UNESCAPED_UNICODE));

            $this->db->query($query);

            logger('newyear_promo')->info('Event logged', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'promotion_id' => $promotionId,
                'event' => $event,
                'meta' => $meta,
            ]);

        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error logging event', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'event' => $event,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Логирует открытие ЛК
     *
     * @param int $userId
     * @param int $orderId
     * @param array $meta
     * @return void
     */
    public function logLkOpen(int $userId, int $orderId, array $meta = []): void
    {
        $this->logEvent($userId, $orderId, self::EVENT_LK_OPEN, $meta);
    }

    /**
     * Логирует клик по ссылке из SMS
     *
     * @param int $userId
     * @param int $orderId
     * @param array $meta
     * @return void
     */
    public function logLinkClicked(int $userId, int $orderId, array $meta = []): void
    {
        $this->logEvent($userId, $orderId, self::EVENT_LINK_CLICKED, $meta);
    }

    /**
     * Логирует клик по кнопке активации скидки
     *
     * @param int $userId
     * @param int $orderId
     * @param array $meta
     * @return void
     */
    public function logDiscountButtonClicked(int $userId, int $orderId, array $meta = []): void
    {
        $this->logEvent($userId, $orderId, self::EVENT_DISCOUNT_BUTTON_CLICKED, $meta);
    }
    /**
     * Логирует клик по кнопке оплаты
     *
     * @param int $userId
     * @param int $orderId
     * @param array $meta
     * @return void
     */
    public function logPayButtonClicked(int $userId, int $orderId, array $meta = []): void
    {
        $this->logEvent($userId, $orderId, self::EVENT_PAY_BUTTON_CLICKED, $meta);
    }
    /**
     * Логирует факт оплаты
     *
     * @param int $userId
     * @param int $orderId
     * @param array $meta
     * @return void
     */
    public function logPaid(int $userId, int $orderId, array $meta = []): void
    {
        $this->logEvent($userId, $orderId, self::EVENT_PAID, $meta);
    }

    /**
     * Получает последнее событие определенного типа
     *
     * @param int $userId
     * @param int $orderId
     * @param string $event
     * @return object|null
     */
    public function getLastEvent(int $userId, int $orderId, string $event): ?object
    {
        $query = $this->db->placehold("
            SELECT * FROM s_newyear_promotion_2026_events 
            WHERE user_id = ? AND order_id = ? AND event = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ", $userId, $orderId, $event);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ?: null;
    }

    /**
     * Создает запись об участии в акции
     *
     * @param int $userId
     * @param int $orderId
     * @param array $utmData
     * @return object|null
     */
    public function createPromoRecord(int $userId, int $orderId, array $utmData): ?object
    {
        try {
            $query = $this->db->placehold("
                INSERT INTO s_newyear_promotion_2026 
                SET user_id = ?, 
                    order_id = ?,
                    bucket = ?,
                    send_date = ?,
                    created_at = NOW()
            ", $userId, $orderId, $utmData['bucket'], $utmData['date']);

            $this->db->query($query);
            $promoId = $this->db->insert_id();

            if (empty($promoId)) {
                return null;
            }

            return $this->getPromoData($userId, $orderId);
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error creating promo record', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'utm_data' => $utmData,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Получает сумму скидки для пользователя
     * Скидка берется из balance->discount_amount (из 1С)
     *
     * @param int $userId
     * @param int $orderId
     * @param object $balance
     * @return float
     */
    /**
     * Получает сумму скидки для пользователя
     * Единственный источник истины - баланс из 1С (sum_percent_with_grace)
     *
     * @param object $balance Баланс пользователя из 1С
     * @return float
     */
    public function getDiscountAmount(object $balance): float
    {
        // Проверяем, активна ли скидка (использует discount_date из 1С)
        if (!$this->isDiscountActive($balance)) {
            return 0;
        }
        
        // Возвращаем сумму скидки из 1С (СуммаСоСкидкойПроцент)
        $discountAmount = (float)($balance->sum_percent_with_grace ?? 0);
        
        return $discountAmount > 0 ? $discountAmount : 0;
    }
    
    /**
     * Рассчитывает сумму дополнительных услуг для заказа
     *
     * @param int $orderId
     * @param object $balance
     * @param float|null $amount Сумма платежа
     * @return float
     */
    private function calculateAdditionalServicesAmount(int $orderId, object $balance, ?float $amount = null): float
    {
        if (empty($amount)) {
            // Если сумма не передана, рассчитываем полную сумму долга
            $amount = (float)($balance->ostatok_od ?? 0) 
                    + (float)($balance->ostatok_percents ?? 0) 
                    + (float)($balance->ostatok_peni ?? 0) 
                    + (float)($balance->penalty ?? 0);
        }
        
        $order = $this->orders->get_crm_order($orderId);
        if (empty($order)) {
            return 0;
        }
        
        $servicesAmount = 0;
        $fullAmount = (float)($balance->ostatok_od ?? 0) 
                    + (float)($balance->ostatok_percents ?? 0) 
                    + (float)($balance->ostatok_peni ?? 0) 
                    + (float)($balance->penalty ?? 0);
        
        $isFullPayment = ($amount >= $fullAmount);
        
        // Рассчитываем сумму TV Medical
        if (!empty($order->additional_service_tv_med)) {
            $tvMedical = $this->tv_medical->getVItaMedPrice($amount);
            if ($tvMedical) {
                if ($isFullPayment) {
                    if ($order->additional_service_repayment) {
                        $servicesAmount += $tvMedical->price;
                    } elseif ($order->half_additional_service_repayment) {
                        $servicesAmount += round($tvMedical->price / 2);
                    }
                } else {
                    if ($order->additional_service_partial_repayment) {
                        $servicesAmount += $tvMedical->price;
                    } elseif ($order->half_additional_service_partial_repayment) {
                        $servicesAmount += round($tvMedical->price / 2);
                    }
                }
            }
        }
        
        // Рассчитываем сумму Star Oracle
        if (!empty($order->additional_service_star_oracle)) {
            $starOracle = $this->star_oracle->getStarOraclePrice($amount);
            if ($starOracle) {
                if ($isFullPayment) {
                    if ($order->additional_service_so_repayment) {
                        $servicesAmount += $starOracle->price;
                    } elseif ($order->half_additional_service_so_repayment) {
                        $servicesAmount += round($starOracle->price / 2);
                    }
                } else {
                    if ($order->additional_service_so_partial_repayment) {
                        $servicesAmount += $starOracle->price;
                    } elseif ($order->half_additional_service_so_partial_repayment) {
                        $servicesAmount += round($starOracle->price / 2);
                    }
                }
            }
        }
        
        // Multipolis обычно рассчитывается отдельно и зависит от пролонгаций
        // Для упрощения можем попытаться получить базовую сумму, но это сложнее
        // Пока оставляем 0, если не передан явно через параметры
        
        return $servicesAmount;
    }

    /**
     * Проверяет, было ли событие определенного типа
     *
     * @param int $userId
     * @param int $orderId
     * @param string $event
     * @return bool
     */
    public function hasEvent(int $userId, int $orderId, string $event): bool
    {
        $lastEvent = $this->getLastEvent($userId, $orderId, $event);
        return !empty($lastEvent);
    }
}

