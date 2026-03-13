<?php

namespace App\Modules\AdditionalServiceRecovery\Application\DTO;

use DateTime;

/**
 * Class RecoveryFilterRequest
 * DTO для передачи параметров фильтра в сервис отчётности.
 */
final class RecoveryFilterRequest
{
    /**
     * @var DateTime|null
     */
    private ?DateTime $dateFrom;

    /**
     * @var DateTime|null
     */
    private ?DateTime $dateTo;

    /**
     * @var int[]
     */
    private array $ruleIds;

    /**
     * @param DateTime|null $dateFrom Начальная дата периода
     * @param DateTime|null $dateTo Конечная дата периода
     * @param int[] $ruleIds Массив ID правил для фильтрации
     */
    public function __construct(?DateTime $dateFrom, ?DateTime $dateTo, array $ruleIds = [])
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->ruleIds = $ruleIds;
    }

    /**
     * @return DateTime|null
     */
    public function getDateFrom(): ?DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTo(): ?DateTime
    {
        return $this->dateTo;
    }

    /**
     * @return int[]
     */
    public function getRuleIds(): array
    {
        return $this->ruleIds;
    }
}
