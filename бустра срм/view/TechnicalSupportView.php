<?php

use App\Containers\DomainSection\Tickets\Repository\DirectionRepository;
use App\Containers\DomainSection\Tickets\Repository\PriorityRepository;
use App\Containers\DomainSection\Tickets\Tables\ManagerTable;
use App\Containers\DomainSection\Tickets\Tables\SlaTable;
use App\Containers\DomainSection\Tickets\Tables\TsTicketTable;

require_once 'View.php';
require_once 'Exports/TicketStatsExport.php';
require_once 'Exports/TicketsExport.php';

class TechnicalSupportView extends View
{
    private const SECTION = 'technical_support';

    public function fetch(): string
    {
        $action = $this->request->method('post')
            ? $this->request->post('action', 'string')
            : $this->request->get('action', 'string');

        if ($this->request->method('post')) {
            switch ($action) {
                case 'sla_save':
                    $this->slaCreate();
                    break;
            }
        } else {
            switch ($action) {
                case 'analytics_tickets':
                    return $this->analyticsTicketsView();
                case 'analytics_operators':
                    return $this->analyticsOperatorsView();
                case 'sla_list':
                    return $this->slaView();
                case 'sla_create':
                    return $this->slaCreateView();
            }
        }

        return $this->ticketsListView();
    }

    private function ticketsListView(): string
    {
        $itemsPerPage = 100;
        $currentPage = max(1, (int)$this->request->get('page', 'integer'));

        $filter = $this->tickets->prepareFilters($this->request->get('search'));
        $filter['sort'] = $this->tickets->prepareSort($this->request->get('sort'));
        $filter['limit'] = $itemsPerPage;
        $filter['page'] = $currentPage;
        $filter['search'] = array_merge(
            $filter['search'] ?? [],
            ['subject_id' => TsTicketTable::getInstance()->getSubjectId()]
        );

        $tickets = $this->tickets->getAllTickets($filter);
        $ticketsCount = $tickets['total_count'];
        $totalPages = (int)ceil($ticketsCount / $itemsPerPage);

        $subjects = $this->tickets->getMainAndChildSubjects();

        $this->design->assign_array([
            'sort' => $this->request->get('sort') ?? 'date',
            'filters' => $filter['search'] ?? [],
            'current_page_num' => $currentPage,
            'total_pages_num' => $totalPages,
            'total_items' => $ticketsCount,
            'items' => $tickets['data'],
            'subjects' => $subjects,
            'companies' => $this->tickets->getCompanies(),
            'directions' => $this->tickets->getDirections(),
            'priorities' => $this->tickets->getPriorities(),
            'statuses' => $this->tickets->getStatuses(),
            'responsible_persons' => $this->tickets->getUniqueResponsiblePersonNames(),
            'responsible_groups' => $this->tickets->getUniqueGroups(),
        ]);

        return $this->design->fetch(self::SECTION . '/tickets/list.tpl');
    }

    private function analyticsTicketsView(): string
    {
        $selectedYear = (int)$this->request->get('year', 'int') ?: (int)date('Y');
        $quarter = (int)$this->request->get('quarter', 'int') ?: ceil((int)date('m') / 3);

        $table = TsTicketTable::getInstance();
        $slaTable = SlaTable::getInstance();

        $availableYears = $slaTable->getAvailableYears();

        if (!in_array($selectedYear, $availableYears)) {
            $selectedYear = (int)date('Y');
        }

        $selectedYears = [];
        foreach ($availableYears as $year) {
            $selectedYears[] = [
                'selected' => $year === $selectedYear,
                'value' => $year,
            ];
        }

        $quarters = $slaTable->getQuarterMap();
        $quarters[array_search($quarter, array_column($quarters, 'id'))]['selected'] = true;

        $slaQuarters = [
            'quarters' => $quarters,
            'years' => $selectedYears
        ];

        $sla = $slaTable->getByQuarter($quarter, $selectedYear);
        $slaName = empty($sla->getId()) ? '<label style="color: red">Не удалось найти подходящий SLA</label>' : $sla->getName();
        $this->design->assign('sla_statistic_quarter', $slaName);
        $this->design->assign('sla_quarters', $slaQuarters);

        $minSlaReactionPercent = $sla->getTotalReactionPercent();
        $minSlaResolutionPercent = $sla->getTotalResolutionPercent();

        $this->design->assign('sla_min_reaction_percent', $minSlaReactionPercent);
        $this->design->assign('sla_min_resolution_percent', $minSlaResolutionPercent);

        //Таблица "Статистика SLA"
        $priorities = $table->getAllPriorities()->getResult();

        $slaStatistic = [];
        foreach ($priorities as $priority) {
            $slaStatistic[] = [
                'priority_id' => $priority->getId(),
                'priority_name' => $priority->getName(),
                'priority_color' => $priority->getColor(),
                'average_reaction' => current($table->getTsAverageReaction($priority->getId(), $quarter, $selectedYear)->getResult())->avg_time ?? "0.00",
                'average_resolve' => $table->getTsAverageResolution($priority->getId(), $quarter, $selectedYear) ?: "0.00",
                'percent_reaction' => current($table->getPercentInSlaReactionByPriority($priority->getId(), $quarter, $selectedYear)->getResult())->sla_percentage ?? "0.00",
                'percent_resolve' => $table->getPercentInSlaResolutionByPriority($priority->getId(), $quarter, $selectedYear) ?: "0.00",
            ];
        }

        $this->design->assign('sla_statistic', $slaStatistic);

        //Таблица "Общая статистика по тикетам"
        $ticketsStat = [];
        $statuses = [];
        $ticketsStatTotal = [];

        foreach ($table->getAllStatuses() as $status) {
            $statuses[] = $status->toArray();
            $ticketsStatTotal['statuses'][$status->getCode()] = count($table->getTicketsByStatusAndQuarter($status->getId(), $quarter, $selectedYear, ['id'])->getResult());
        }

        $ticketsStatTotal['average_reaction']['minutes'] = floatval(current($table->getAverageReactionByQuarter($quarter, $selectedYear)->getResult())->avg_time ?? "0.00");
        $ticketsStatTotal['average_resolve']['hours'] = floatval($table->getAverageResolutionByQuarter($quarter, $selectedYear) ?: "0.00");
        $ticketsStatTotal['average_reaction']['percent'] = floatval(current($table->getPercentInSlaReactionByQuarter($quarter, $selectedYear)->getResult())->sla_percentage ?? "0.00");
        $ticketsStatTotal['average_resolve']['percent'] = floatval($table->getPercentInSlaResolutionByQuarter($quarter, $selectedYear) ?: "0.00");

        for ($startMonth = ($quarter - 1) * 3 + 1; $startMonth <= ($quarter - 1) * 3 + 3; $startMonth++) {
            $data = [
                'month' => $startMonth . '.' . $selectedYear,
                'average_reaction' => [
                    'minutes' => $table->getAverageReactionByMonth($startMonth, $selectedYear) ?: '0.00',
                    'percent' => $table->getPercentInSlaReactionByMonth($startMonth, $selectedYear) ?: "0.00",
                ],
                'average_resolve' => [
                    'hours' => floatval($table->getAverageResolutionByMonth($startMonth, $selectedYear) ?: "0.00"),
                    'percent' => floatval($table->getPercentInSlaResolutionByMonth($startMonth, $selectedYear) ?: "0.00"),
                ]
            ];

            foreach ($statuses as $status) {
                $data[$status['code']] = count($table->getTicketsByStatusAndMonth($status['id'], $startMonth, $selectedYear, ['id'])->getResult());
            }

            $ticketsStat[] = $data;
        }

        $this->design->assign('statuses', $statuses);
        $this->design->assign('tickets_statistic', $ticketsStat);
        $this->design->assign('tickets_statistic_total', $ticketsStatTotal);

        //Таблица "Переданные тикеты"
        $sharedTickets = [];
        $directions = (new DirectionRepository())->getAll()->getResult();
        foreach ($directions as $direction) {
            $sharedTickets[] = [
                'direction' => $direction->getName(),
                'count' => count($table->getByDirectionForSla($direction->getId(), $quarter, $selectedYear)->getResult()),
            ];
        }

        $this->design->assign('shared_tickets', $sharedTickets);

        return $this->design->fetch(self::SECTION . '/analytics/tickets.tpl');
    }

    private function analyticsOperatorsView(): string
    {
        $selectedYear = (int)$this->request->get('year', 'int') ?: (int)date('Y');
        $selectedMonth = (int)$this->request->get('month', 'int') ?: (int)date('m');

        $ticketTable = TsTicketTable::getInstance();
        $slaTable = SlaTable::getInstance();
        $managerTable = ManagerTable::getInstance();

        $availableYears = $slaTable->getAvailableYears();

        if (!in_array($selectedYear, $availableYears)) {
            $selectedYear = (int)date('Y');
        }

        $selectedYears = [];
        foreach ($availableYears as $year) {
            $selectedYears[] = [
                'selected' => $year === $selectedYear,
                'value' => $year,
            ];
        }

        $months = $slaTable->getMonthMap();
        $months[array_search($selectedMonth, array_column($months, 'id'))]['selected'] = true;

        $slaMonths = [
            'months' => $months,
            'years' => $selectedYears
        ];

        $quarter = ceil($selectedMonth / 3);
        $sla = $slaTable->getByQuarter($quarter, $selectedYear);
        $slaName = empty($sla->getId()) ? '<label style="color: red">Не удалось найти подходящий SLA</label>' : $sla->getName();

        $this->design->assign('sla_name', $slaName);
        $this->design->assign('sla_months', $slaMonths);
        $this->design->assign('sla_min_reaction_percent', $sla->getTotalReactionPercent());
        $this->design->assign('sla_min_resolution_percent', $sla->getTotalResolutionPercent());

        $managers = $managerTable->getTechnicalSupportOperators();
        $tableData = [];
        foreach ($managers as $manager) {
            $tableData[] = [
                'name' => $manager->getName(),
                'closed_tickets' => $ticketTable->getClosedTicketsByManagerAndMonth($manager->getId(), $selectedMonth, $selectedYear),
                'reaction_minutes' => $ticketTable->getAverageReactionByMonth($selectedMonth, $selectedYear, $manager->getId()) ?: '0.00',
                'resolution_hours' => $ticketTable->getAverageResolutionByMonth($selectedMonth, $selectedYear, $manager->getId()) ?: '0.00',
                'reaction_percentage' => $ticketTable->getPercentInSlaReactionByMonth($selectedMonth, $selectedYear, $manager->getId()) ?: '0.00',
                'resolution_percentage' => $ticketTable->getPercentInSlaResolutionByMonth($selectedMonth, $selectedYear, $manager->getId()) ?: '0.00',
            ];
        }

        $this->design->assign('statistic', $tableData);
        return $this->design->fetch(self::SECTION . '/analytics/operators.tpl');
    }

    private function slaView(): string
    {
        $table = SlaTable::getInstance();
        $priorityRepo = new PriorityRepository();

        $sla = [];
        foreach ($table->getAll() as $dto) {
            $sla[] = [
                'name' => $dto->getName(),
                'number' => $dto->getQuarter(),
                'year' => $dto->getYear(),
                'priority' => $priorityRepo->getByPrimary($dto->getPriorityId())->toArray(),
                'reactionMinutes' => $dto->getReactionMinutes(),
                'reactionPercent' => $dto->getReactionPercent(),
                'resolutionMinutes' => $dto->getResolutionMinutes(),
                'resolutionPercent' => $dto->getResolutionPercent(),
            ];
        }

        $this->design->assign('sla', $sla);

        return $this->design->fetch(self::SECTION . '/sla/list.tpl');
    }

    private function slaCreateView(): string
    {
        $table = SlaTable::getInstance();

        $priorities = (new PriorityRepository())->getAll()->getResult();
        $prioritiesToShow = [];
        foreach ($priorities as $priority) {
            $prioritiesToShow[] = [
                'id' => $priority->getId(),
                'name' => $priority->getName(),
                'color' => $priority->getColor()
            ];
        }

        $this->design->assign('quarterMap', $table->getQuarterMap());
        $this->design->assign('priorities', $prioritiesToShow);

        return $this->design->fetch(self::SECTION . '/sla/create.tpl');
    }

    private function slaCreate()
    {
        $rawData = $this->request->post();
        parse_str($rawData, $dataPost);

//        $table = SlaTable::getInstance();
//        $sla = $table->add(new SlaDTO(
//            0,
//            '',
//            (int)$rawData['quarter'],
//            (int)$rawData['year'],
//            (int)$rawData['priority'],
//            (int)$rawData['react_limit_minutes'],
//            (int)$rawData['react_limit_percents'],
//            (int)$rawData['resolve_limit_minutes'],
//            (int)$rawData['resolve_limit_percents'],
//        ))->getResult();
//        var_dump($sla->getQuery());
//        var_dump($sla->toArray());

//        $query = $this->db->placehold("INSERT INTO ts_sla SET ?%", ['Весна 2025', 2, 2025, 2, 2, 2, 2, 2]);
//        $this->db->query($query);

        $this->response->json_output([
            'status' => false,
//            'id' => $this->db->insert_id(),
        ]);
    }
}
