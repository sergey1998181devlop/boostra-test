<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Services;

use DateTime;
use Exception;

class FindzenBannerService
{
    /** @var string - куда будет переход при клике на баннер */
    private const TARGET_URL = 'https://finzen-service.ru';

    private const OVERDUE_DAYS = 9;

    /**
     * @return string целевой URL для перехода
     */
    public function getTargetUrl(): string
    {
        return self::TARGET_URL;
    }

    /**
     * Формирует уникальную per-user ссылку на ФинДзен
     *
     * @param string $baseUrl базовый URL ФинДзен (из settings->url_findzen)
     * @param string $externalId uid клиента из 1С
     * @param int $isSafeFlow 1 = безопасный (ручной SMS), 0 = опасный (авто-подпись)
     * @return string
     */
    public function getUniqueTargetUrl(string $baseUrl, string $externalId, int $isSafeFlow = 1): string
    {
        return $baseUrl . '/auth.php?fio=Иванов_Иван&app_source=boostra&external_id=' . $externalId . '&safe_flow=' . $isSafeFlow;
    }

    /**
     * @return int количество дней просрочки, которое включает баннер
     */
    public function getOverdueDays(): int
    {
        return self::OVERDUE_DAYS;
    }

    /**
     * Считает "чистые" дни просрочки на основе логики due_days
     *
     * @param array $responseBalances Массив балансов из 1С (soap->get_user_balances_array_1c)
     * @param string|null $zaimNumber Номер текущего займа пользователя
     * @return int
     * @throws Exception
     */
    public function getClearDueDays(array $responseBalances, ?string $zaimNumber): int
    {
        if (empty($zaimNumber)) {
            return 0;
        }

        $currentLoan = array_values(array_filter($responseBalances, function ($item) use ($zaimNumber) {
            return isset($item['НомерЗайма']) && $item['НомерЗайма'] === $zaimNumber;
        }));

        if (!isset($currentLoan[0]) || empty($currentLoan[0]['ПланДата'])) {
            return 0;
        }

        // Разница между датой возврата и текущей датой (полночь)
        $planDate = new DateTime($currentLoan[0]['ПланДата']);
        $today = new DateTime(date('Y-m-d 00:00:00'));
        $diff = date_diff($planDate, $today);

        if ($diff->invert === 1) {
            return 0; // Плановая дата еще не наступила - просрочки нет
        }

        if ($diff->days === 0) {
            return 0; // В день плановой даты (разница 0) просрочки тоже нет
        }

        return (int) $diff->days; // Фактические дни просрочки
    }
}