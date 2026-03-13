<?php

namespace App\Modules\BRReport\Dto;

/**
 * DTO для фильтрации данных отчета БР
 */
class BRReportFilterDto
{
    /** @var string */
    private $dateFrom;

    /** @var string */
    private $dateTo;

    /** @var int */
    private $page;

    /** @var int */
    private $limit;

    /** @var string */
    private $orderBy;

    /** @var string */
    private $filtersWhere;

    public function __construct(
        string $dateFrom,
        string $dateTo,
        int $page = 1,
        int $limit = 15,
        string $orderBy = 't.created_at DESC',
        string $filtersWhere = ''
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->page = max(1, $page);
        $this->limit = $limit;
        $this->orderBy = $orderBy;
        $this->filtersWhere = $filtersWhere;
    }

    public function getDateFrom(): string
    {
        return $this->dateFrom;
    }

    public function getDateTo(): string
    {
        return $this->dateTo;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getFiltersWhere(): string
    {
        return $this->filtersWhere;
    }
}
