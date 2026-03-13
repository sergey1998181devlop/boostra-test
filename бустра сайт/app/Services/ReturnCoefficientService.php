<?php

namespace App\Services;

use App\Core\Cache\CacheInterface;
use App\Dto\ReturnCoefficientDto;
use App\Repositories\ReturnCoefficientRepository;
use Users;

class ReturnCoefficientService
{
    public const SERVICE_CREDIT_DOCTOR = 'credit_doctor';
    public const SERVICE_TV_MEDICAL    = 'tv_medical';
    public const SERVICE_MULTIPOLIS     = 'multipolis';

    public const STAGE_ISSUANCE = 'issuance';
    public const STAGE_PAYMENT  = 'payment';

    private const CACHE_KEY_METRICS    = 'srkv:metrics';
    private const CACHE_KEY_FALLBACK   = 'srkv:metrics:fallback';
    private const CACHE_KEY_RISKY      = 'srkv:risky_values';
    private const CACHE_KEY_MAX_SCORE  = 'srkv:max_score';

    private const CACHE_TTL_PRIMARY  = 86400;  // 24 часа
    private const CACHE_TTL_FALLBACK = 259200; // 72 часа

    private CacheInterface $cache;
    private ReturnCoefficientRepository $repository;
    private Users $users;

    /** @var ReturnCoefficientDto|null|false false = не инициализировано */
    private $metricsCache = false;

    /** @var array<string, bool> Локальный кеш возвратов допов в рамках экземпляра сервиса */
    private array $returnedServiceCache = [];

    public function __construct(
        CacheInterface              $cache,
        ReturnCoefficientRepository $repository,
        Users                       $users
    ) {
        $this->cache      = $cache;
        $this->repository = $repository;
        $this->users      = $users;
    }

    /**
     * Отладочная информация о расчёте коэффициента для конкретного клиента.
     * Используется тестовой ручкой srkv_test_metrics.php.
     *
     * @return array{
     *   is_active: bool,
     *   metrics_summary: array,
     *   user_traits: array<string, string>,
     *   risky_values: array<string, array{value: string, score: float}>,
     *   max_score: float,
     *   user_score: float,
     *   matched_traits: string[],
     *   coefficient: float,
     *   pricing_decision: string,
     * }
     */
    public function getDebugInfo(object $user, object $order): array
    {
        $metrics = $this->getMetrics();

        if ($metrics === null) {
            return ['error' => 'No metrics in cache — set metrics first via ?action=set'];
        }

        $isActive        = $this->isStageActive(self::STAGE_ISSUANCE);
        $riskyWithScores = $this->getRiskyValuesWithScores($metrics);
        $scores          = $riskyWithScores['scores'];
        $maxScore        = $riskyWithScores['max_score'];
        $traits          = $this->resolveUserTraits($user, $order);

        $userScore     = 0.0;
        $matchedTraits = [];

        foreach ($scores as $traitName => $data) {
            if (isset($traits[$traitName]) && $traits[$traitName] === $data['value']) {
                $userScore += $data['score'];
                $matchedTraits[] = $traitName;
            }
        }

        $coefficient = $maxScore > 0 ? round($userScore / $maxScore, 4) : 0.0;

        $thresholds = config('services.srkv.coefficient_thresholds', ['no_sale' => 0.4, 'discount' => 0.1]);
        if ($coefficient >= (float)$thresholds['no_sale']) {
            $decision = 'no_sale (ФД не продаём)';
        } elseif ($coefficient > (float)$thresholds['discount']) {
            $decision = 'discount (скидочная сетка)';
        } else {
            $decision = 'base (базовая сетка)';
        }

        return [
            'is_active'        => $isActive,
            'metrics_summary'  => [
                'conversion_on_issuance' => $metrics->conversionOnIssuance,
                'conversion_on_payment'  => $metrics->conversionOnPayment,
                'overall_return_pct'     => $metrics->overallReturnPct,
            ],
            'user_traits'      => $traits,
            'risky_values'     => $scores,
            'max_score'        => $maxScore,
            'user_score'       => round($userScore, 4),
            'matched_traits'   => $matchedTraits,
            'coefficient'      => $coefficient,
            'pricing_decision' => $decision,
        ];
    }

    /**
     * Рассчитать коэффициент возврата для заявки.
     *
     * @return float 0.0–1.0 (0 = минимальный риск, 1 = максимальный)
     */
    public function calculateReturnCoefficient(object $user, object $order): float
    {
        try {
            $metrics = $this->getMetrics();
            if ($metrics === null) {
                return 1.0; // п. 4.2: нет данных → не можем рассчитать → коэф = 1
            }

            $riskyWithScores = $this->getRiskyValuesWithScores($metrics);
            if (empty($riskyWithScores['scores'])) {
                return 0.0; // рискованных признаков нет → минимальный риск
            }

            return $this->matchUserToRiskyValues(
                $user,
                $order,
                $riskyWithScores['scores'],
                $riskyWithScores['max_score']
            );
        } catch (\Throwable $e) {
            log_error('SRKV: coefficient calculation failed', [
                'user_id' => $user->id ?? null,
                'error'   => $e->getMessage(),
            ]);
            return 1.0; // п. 4.2: ошибка расчёта → коэф = 1 → не продаём
        }
    }

    /**
     * Проверка условий активации для конкретного этапа.
     *
     * @param string $stage 'issuance' или 'payment'
     */
    public function isStageActive(string $stage): bool
    {
        $metrics = $this->getMetrics();
        if ($metrics === null) {
            return false;
        }

        $minReturn = (float)config('services.srkv.min_return_pct', 4.7);
        if ($metrics->overallReturnPct < $minReturn) {
            return false;
        }

        if ($stage === self::STAGE_ISSUANCE) {
            $minConv = (float)config('services.srkv.min_conversion_issuance', 23.5);
            return $metrics->conversionOnIssuance >= $minConv;
        }

        if ($stage === self::STAGE_PAYMENT) {
            $minConv = (float)config('services.srkv.min_conversion_payment', 20.3);
            return $metrics->conversionOnPayment >= $minConv;
        }

        return false;
    }

    /**
     * Коэффициент возврата >= порога no_sale → ФД не продаём.
     * Применяется только к ФД (ВМ имеет фиксированную цену, рассинхрона нет).
     */
    public function isNoSaleByCoefficient(object $user, object $order): bool
    {
        if (!$this->isStageActive(self::STAGE_ISSUANCE)) {
            return false;
        }

        return $this->isNoSaleByCoefficientInternal($user, $order);
    }

    /**
     * Проверка порога no_sale без проверки stage-активации.
     * Используется внутри shouldBlockService(), где stage уже проверен выше.
     */
    private function isNoSaleByCoefficientInternal(object $user, object $order): bool
    {
        $noSale      = (float)config('services.srkv.coefficient_thresholds.no_sale', 0.4);
        $coefficient = $this->calculateReturnCoefficient($user, $order);

        return $coefficient >= $noSale;
    }

    /**
     * Нужно ли заблокировать конкретный доп для пользователя на этапе.
     *
     * Условия: конверсия этапа >= целевой И возвраты >= целевых И пользователь ранее возвращал этот доп.
     *
     * @param int $userId
     * @param string $serviceType ReturnCoefficientService::SERVICE_*
     * @param string $stage ReturnCoefficientService::STAGE_*
     * @param object|null $user Если передан вместе с $order — дополнительно проверяется коэффициент
     * @param object|null $order
     * @return bool
     */
    public function shouldBlockService(
        int     $userId,
        string  $serviceType,
        string  $stage,
        ?object $user  = null,
        ?object $order = null
    ): bool {
        // ВМ (выдача и оплата): выдача >= 23.3% И оплата >= 20.3% И возвраты >= 4.7%
        if ($serviceType === self::SERVICE_TV_MEDICAL) {
            if (!$this->isStageActive(self::STAGE_ISSUANCE) || !$this->isStageActive(self::STAGE_PAYMENT)) {
                return false;
            }
        } elseif (!$this->isStageActive($stage)) {
            return false;
        }

        if ($this->hasEverReturnedService($userId, $serviceType)) {
            return true;
        }

        // Проверка коэффициента no_sale (где применимо)
        if ($user !== null && $order !== null && $this->supportsCoefficientsCheck($serviceType, $stage)) {
            return $this->isNoSaleByCoefficientInternal($user, $order);
        }

        return false;
    }

    /**
     * Коэффициент проверяется только для ФД на выдаче.
     * ВМ и КС: только история возвратов.
     */
    private function supportsCoefficientsCheck(string $serviceType, string $stage): bool
    {
        return $serviceType === self::SERVICE_CREDIT_DOCTOR && $stage === self::STAGE_ISSUANCE;
    }

    /**
     * Возвращал ли клиент конкретный доп когда-либо.
     */
    public function hasEverReturnedService(int $userId, string $serviceType): bool
    {
        $cacheKey = $userId . ':' . $serviceType;
        if (array_key_exists($cacheKey, $this->returnedServiceCache)) {
            return $this->returnedServiceCache[$cacheKey];
        }

        switch ($serviceType) {
            case self::SERVICE_CREDIT_DOCTOR:
                $result = $this->repository->hasEverReturnedDoctor($userId);
                break;
            case self::SERVICE_TV_MEDICAL:
                $result = $this->repository->hasEverReturnedTvMedical($userId);
                break;
            case self::SERVICE_MULTIPOLIS:
                $result = $this->repository->hasEverReturnedConcierge($userId);
                break;
            default:
                $result = false;
                break;
        }

        $this->returnedServiceCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Применить готовый DTO метрик (например, из DB-крона) — сохранить в кеш.
     *
     * @return bool true если успешно сохранено
     */
    public function applyMetrics(ReturnCoefficientDto $dto): bool
    {
        try {
            $this->persistMetrics($dto);
            $this->metricsCache = $dto;

            log_info('SRKV: applyMetrics — metrics applied', [
                'conversion_issuance' => $dto->conversionOnIssuance,
                'conversion_payment'  => $dto->conversionOnPayment,
                'overall_return_pct'  => $dto->overallReturnPct,
            ]);

            return true;
        } catch (\Throwable $e) {
            log_error('SRKV: applyMetrics failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Принудительно обновить метрики из 1С и записать в кеш.
     * Используется кроном (ежедневно в 06:00).
     *
     * @return bool true если метрики успешно получены и закешированы
     */
    public function refreshMetrics(): bool
    {
        $dto = $this->repository->fetchMetricsFrom1C();

        if ($dto === null) {
            log_warning('SRKV: cron refresh — 1C API returned null');
            return false;
        }

        $this->persistMetrics($dto);
        $this->metricsCache = $dto;

        log_info('SRKV: cron refresh — metrics updated', [
            'conversion_issuance' => $dto->conversionOnIssuance,
            'conversion_payment'  => $dto->conversionOnPayment,
            'overall_return_pct'  => $dto->overallReturnPct,
        ]);

        return true;
    }

    /**
     * Пересчитать рискованные значения и баллы и сохранить в кеш.
     * Вызывается кроном сразу после refreshMetrics() — п.2.1, п.3.1 ТЗ.
     *
     * @return bool true если расчёт выполнен успешно
     */
    public function recalculateRiskyValues(): bool
    {
        try {
            $metrics = $this->getMetrics();
            if ($metrics === null) {
                log_warning('SRKV: recalculate — no metrics available');
                return false;
            }

            $result = $this->getRiskyValuesWithScores($metrics);

            log_info('SRKV: risky values recalculated', [
                'risky_count' => count($result['scores']),
                'max_score'   => $result['max_score'],
            ]);

            return true;
        } catch (\Throwable $e) {
            log_error('SRKV: recalculate risky values failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Конверсия на выдаче из метрик.
     */
    public function getConversionOnIssuance(): float
    {
        $metrics = $this->getMetrics();
        return $metrics ? $metrics->conversionOnIssuance : 0.0;
    }

    /**
     * Конверсия на оплате из метрик.
     */
    public function getConversionOnPayment(): float
    {
        $metrics = $this->getMetrics();
        return $metrics ? $metrics->conversionOnPayment : 0.0;
    }

    // ─── Private ────────────────────────────────────────────────────────

    /**
     * Получить метрики: Redis → API 1С → fallback.
     */
    private function getMetrics(): ?ReturnCoefficientDto
    {
        if ($this->metricsCache !== false) {
            return $this->metricsCache;
        }

        try {
            $cached = $this->cache->get(self::CACHE_KEY_METRICS);
            if ($cached instanceof ReturnCoefficientDto) {
                $this->metricsCache = $cached;
                return $cached;
            }
        } catch (\Throwable $e) {
            log_warning('SRKV: Redis get failed', ['error' => $e->getMessage()]);
        }

        $dto = $this->repository->fetchMetricsFrom1C();

        if ($dto !== null) {
            $this->persistMetrics($dto);
            $this->metricsCache = $dto;
            return $dto;
        }

        // Fallback: последние успешные метрики
        try {
            $fallback = $this->cache->get(self::CACHE_KEY_FALLBACK);
            if ($fallback instanceof ReturnCoefficientDto) {
                $this->metricsCache = $fallback;
                return $fallback;
            }
        } catch (\Throwable $e) {
            log_warning('SRKV: Redis fallback get failed', ['error' => $e->getMessage()]);
        }

        $this->metricsCache = null;
        return null;
    }

    /**
     * Сохранить метрики в основной и fallback кеш.
     */
    private function persistMetrics(ReturnCoefficientDto $dto): void
    {
        try {
            $ttl = (int)config('services.srkv.cache_ttl', self::CACHE_TTL_PRIMARY);
            $this->cache->set(self::CACHE_KEY_METRICS, $dto, $ttl);
            $this->cache->set(self::CACHE_KEY_FALLBACK, $dto, self::CACHE_TTL_FALLBACK);

            // Сбросить кеш рискованных значений — они пересчитаются
            $this->cache->delete(self::CACHE_KEY_RISKY);
            $this->cache->delete(self::CACHE_KEY_MAX_SCORE);
        } catch (\Throwable $e) {
            log_warning('SRKV: Redis persist failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Получить рискованные значения + баллы (с кешем).
     *
     * @return array{scores: array<string, array{value: string, score: float}>, max_score: float}
     */
    private function getRiskyValuesWithScores(ReturnCoefficientDto $metrics): array
    {
        try {
            $cached = $this->cache->get(self::CACHE_KEY_RISKY);
            $maxScore = $this->cache->get(self::CACHE_KEY_MAX_SCORE);

            if (is_array($cached) && is_float($maxScore)) {
                return ['scores' => $cached, 'max_score' => $maxScore];
            }
        } catch (\Throwable $e) {
            // продолжаем расчёт
        }

        $riskyValues = $this->determineRiskyValues($metrics);
        $scores      = $this->calculateScores($riskyValues, $metrics->overallReturnPct);

        $maxScore = 0.0;
        foreach ($scores as $item) {
            $maxScore += $item['score'];
        }
        $maxScore = round($maxScore, 4);

        try {
            $ttl = (int)config('services.srkv.cache_ttl', self::CACHE_TTL_PRIMARY);
            $this->cache->set(self::CACHE_KEY_RISKY, $scores, $ttl);
            $this->cache->set(self::CACHE_KEY_MAX_SCORE, $maxScore, $ttl);
        } catch (\Throwable $e) {
            // не критично
        }

        return ['scores' => $scores, 'max_score' => $maxScore];
    }

    /**
     * Определить рискованное значение для каждого признака.
     * Если все значения одинаковые — рискованного нет.
     * Иначе — любой из максимальных.
     *
     * @return array<string, array{value: string, return_pct: float}>
     */
    private function determineRiskyValues(ReturnCoefficientDto $metrics): array
    {
        $risky = [];

        foreach ($metrics->getTraitGroups() as $traitName => $values) {
            if (empty($values)) {
                continue;
            }

            $maxPct   = max($values);
            $tiedKeys = array_keys(array_filter($values, fn($pct) => $pct == $maxPct));
            $maxCount = count($tiedKeys);

            // Все значения одинаковые — рискованного нет
            if ($maxCount === count($values)) {
                continue;
            }

            // Не все одинаковые — любой из максимальных
            $risky[$traitName] = [
                'value'      => $tiedKeys[array_rand($tiedKeys)],
                'return_pct' => $maxPct,
            ];
        }

        return $risky;
    }

    /**
     * Рассчитать баллы: балл = % рискованного - общий %.
     *
     * @return array<string, array{value: string, score: float}>
     */
    private function calculateScores(array $riskyValues, float $overallReturn): array
    {
        $scores = [];

        foreach ($riskyValues as $traitName => $data) {
            $score = $data['return_pct'] - $overallReturn;

            if ($score > 0) {
                $scores[$traitName] = [
                    'value' => $data['value'],
                    'score' => round($score, 4),
                ];
            }
        }

        return $scores;
    }

    /**
     * Сопоставить признаки клиента с рискованными, вернуть коэффициент.
     */
    private function matchUserToRiskyValues(
        object $user,
        object $order,
        array  $scores,
        float  $maxScore
    ): float {
        if ($maxScore <= 0) {
            return 0.0;
        }

        $traits   = $this->resolveUserTraits($user, $order);
        $userScore = 0.0;

        foreach ($scores as $traitName => $data) {
            if (isset($traits[$traitName]) && $traits[$traitName] === $data['value']) {
                $userScore += $data['score'];
            }
        }

        return round($userScore / $maxScore, 4);
    }

    /**
     * Извлечь 6 признаков клиента из user/order.
     *
     * @return array<string, string>
     */
    private function resolveUserTraits(object $user, object $order): array
    {
        return [
            'client_type'  => $this->resolveClientType($user),
            'loan_type'    => $this->resolveLoanType($order),
            'platform'     => $this->resolvePlatform($order),
            'gender'       => $this->resolveGender($user),
            'score'        => $this->resolveScoreGroup($order),
            'source'       => $this->resolveSource($order),
        ];
    }

    private function resolveClientType(object $user): string
    {
        return empty($user->loan_history) ? 'nk' : 'pk';
    }

    private function resolveLoanType(object $order): string
    {
        return strtolower($order->loan_type ?? 'pdl');
    }

    private function resolvePlatform(object $order): string
    {
        $term = trim($order->utm_term ?? '');

        if ($term === '') {
            return 'site';
        }

        $term = strtolower($term);

        if (strpos($term, 'android') !== false) {
            return 'android';
        }

        if (strpos($term, 'ios') !== false) {
            return 'ios';
        }

        return 'site';
    }

    private function resolveGender(object $user): string
    {
        return strtolower($user->gender ?? 'male');
    }

    private function resolveScoreGroup(object $order): string
    {
        $score = (int)($order->scorista_ball ?? 0);
        return $score < 600 ? 'lt600' : 'gte600';
    }

    private function resolveSource(object $order): string
    {
        $source = strtolower(trim($order->utm_source ?? ''));

        if ($source === 'cross_order') {
            return 'cross_order';
        }

        if ($source === 'crm_auto_approve') {
            return 'auto_approve';
        }

        if ($source === '' || $this->users->is_organic($order->utm_source ?? '')) {
            return 'organic';
        }

        return 'other';
    }

}
