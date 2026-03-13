<?php

namespace App\Modules\BRReport\Services;

use App\Modules\BRReport\Dto\BRReportFilterDto;
use App\Modules\BRReport\Dto\BRReportItemDto;
use App\Modules\BRReport\Mappers\BRReportMapper;
use App\Modules\BRReport\Repositories\BRReportRepository;
use Carbon\Carbon;
use Generator;
use Simpla;

/**
 * Сервис для работы с отчетом БР (Банк России)
 *
 * Оркестрирует взаимодействие между репозиторием и маппером
 */
class BRReportService
{
    /** @var BRReportRepository */
    private $repository;

    /** @var BRReportMapper */
    private $mapper;

    public function __construct(Simpla $db)
    {
        $this->repository = new BRReportRepository($db);
        $this->mapper = new BRReportMapper();
    }

    /**
     * Получить данные отчета с пагинацией
     *
     * @param BRReportFilterDto $filter
     * @return BRReportItemDto[]
     */
    public function getReportData(BRReportFilterDto $filter): array
    {
        $rows = $this->repository->getResults($filter);
        return $this->mapper->mapRowsToDtos($rows);
    }

    /**
     * Получить все данные отчета (без пагинации)
     *
     * @param BRReportFilterDto $filter
     * @return BRReportItemDto[]
     */
    public function getAllReportData(BRReportFilterDto $filter): array
    {
        $rows = $this->repository->getAllResults($filter);
        return $this->mapper->mapRowsToDtos($rows);
    }

    /**
     * Получить данные отчета чанками для экспорта
     *
     * @param BRReportFilterDto $filter
     * @param int $chunkSize
     * @return Generator
     */
    public function getChunkedReportData(BRReportFilterDto $filter, int $chunkSize = 100): Generator
    {
        foreach ($this->repository->getChunkedResults($filter, $chunkSize) as $row) {
            yield $this->mapper->mapRowToDto($row);
        }
    }

    /**
     * Получить общее количество записей
     *
     * @param BRReportFilterDto $filter
     * @return int
     */
    public function getTotals(BRReportFilterDto $filter): int
    {
        return $this->repository->getTotals($filter);
    }

    /**
     * Подготовить данные для отображения отчета
     *
     * @param BRReportFilterDto $filter
     * @return array
     */
    public function prepareReportForDisplay(BRReportFilterDto $filter): array
    {
        $items = $this->getReportData($filter);
        $totals = $this->getTotals($filter);
        $pagesNum = (int)ceil($totals / $filter->getLimit());

        return [
            'headers' => $this->mapper->getReportHeaders(),
            'rows' => $this->mapper->prepareReportRows($items),
            'totals' => $totals,
            'pages_num' => $pagesNum,
            'current_page' => $filter->getPage(),
        ];
    }

    /**
     * Получить заголовки отчета
     *
     * @return array
     */
    public function getReportHeaders(): array
    {
        return $this->mapper->getReportHeaders();
    }

    /**
     * Получить конфигурацию колонок
     *
     * @return array
     */
    public function getColumns(): array
    {
        return BRReportMapper::REPORT_COLUMNS;
    }

    /**
     * Подготовить строки для экспорта
     *
     * @param BRReportItemDto[] $items
     * @return array
     */
    public function prepareRowsForExport(array $items): array
    {
        return $this->mapper->prepareReportRows($items);
    }

    /**
     * Получить значение колонки для экспорта
     *
     * @param BRReportItemDto $item
     * @param string $key
     * @return string
     */
    public function getColumnValue(BRReportItemDto $item, string $key): string
    {
        return $this->mapper->getColumnValue($item, $key);
    }

    /**
     * Получить диапазон дат текущего квартала
     *
     * @param Carbon|null $date Дата для расчета квартала (по умолчанию текущая)
     * @return array{start: string, end: string}
     */
    public function getQuarterRange(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();

        return [
            'start' => $date->copy()->startOfQuarter()->format('Y-m-d'),
            'end' => $date->copy()->endOfQuarter()->format('Y-m-d'),
        ];
    }

    /**
     * Получить номер квартала (1-4) для даты
     *
     * @param Carbon|null $date Дата для расчета (по умолчанию текущая)
     * @return int
     */
    public function getQuarterNumber(?Carbon $date = null): int
    {
        return ($date ?? Carbon::now())->quarter;
    }

    /**
     * Получить SQL WHERE условие для фильтрации по текущему кварталу
     *
     * @param string $dateColumn Название колонки с датой
     * @param Carbon|null $date Дата для расчета квартала (по умолчанию текущая)
     * @return string SQL условие (PDO compatible)
     */
    public function getQuarterWhereClause(string $dateColumn = 't.created_at', ?Carbon $date = null): string
    {
        $range = $this->getQuarterRange($date);

        return sprintf(
            "DATE(%s) BETWEEN '%s' AND '%s'",
            $dateColumn,
            $range['start'],
            $range['end']
        );
    }

    /**
     * Получить римское обозначение квартала
     *
     * @param int $quarterNumber Номер квартала (1-4)
     * @return string Римское обозначение (I, II, III, IV)
     */
    public function getQuarterRoman(int $quarterNumber): string
    {
        $romanNumerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        return $romanNumerals[$quarterNumber] ?? '';
    }

    /**
     * Получить полное название периода для отчета
     *
     * @param Carbon|null $date Дата для расчета квартала (по умолчанию текущая)
     * @return string Например: "IV квартал 2025"
     */
    public function getQuarterPeriodName(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $quarter = $this->getQuarterNumber($date);
        $year = $date->year;

        return sprintf('%s квартал %d', $this->getQuarterRoman($quarter), $year);
    }
}
