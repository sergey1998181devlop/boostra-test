<?php

namespace App\Containers\DomainSection\Tickets\Tables;

use App\Containers\DomainSection\Tickets\DTO\PriorityDTO;
use App\Containers\DomainSection\Tickets\DTO\TsTicketStatusDTO;
use App\Containers\DomainSection\Tickets\Repository\DirectionRepository;
use App\Containers\DomainSection\Tickets\Repository\TsTicketsRepository;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\Contracts\TableInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;
use App\Containers\InfrastructureSection\Table\BaseTable;
use DateTime;
use InvalidArgumentException;

class TsTicketTable extends BaseTable implements TableInterface
{
    private int $subjectId;

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    function getTableName(): string
    {
        return 's_mytickets';
    }

    public static function getInstance(): BaseTable
    {
        return new self(new TsTicketsRepository());
    }

    public function __construct(RepositoryInterface $repository)
    {
        parent::__construct($repository);

        if (!$subjectId = $this->getRepository()->getSubjectIdByName('Подача тикета')) {
            throw new \Exception('Cannot execute query! There is no subject with "Подача тикета" name!');
        }
        $this->subjectId = $subjectId;
    }

    public function getAllPriorities(): ResultDTO
    {
        $query = 'SELECT * FROM `s_mytickets_priority`';
        $result = new SelectResultDTO($query);

        $priorities = $this->getRepository()->execRaw($query)->getResult();

        foreach ($priorities as $priority) {
            $result->pushResult(new PriorityDTO(
                $priority->id,
                $priority->name,
                $priority->color
            ));
        }

        return $result;
    }

    public function getTsAverageReaction(int $priorityId, int $quarter, int $year): ResultDTO
    {
        $dates = $this->getQuarterDateRange($quarter, $year);

        return $this->getRepository()->execRaw(
            'SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, accepted_at)) / 60, 2) AS avg_time FROM s_mytickets ' .
            'WHERE accepted_at IS NOT NULL ' .
            'AND subject_id =  ' . $this->subjectId . ' ' .
            'AND priority_id = ' . $priorityId . ' ' .
            "AND created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "'" .
            'AND is_duplicate = 0'
        );
    }

    public function getAverageReactionByMonth(int $month, int $year = 2025, int $managerId = 0): float
    {
        $dates = $this->getMonthBounds($month, $year);

        $query = 'SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, accepted_at)) / 60, 2) AS avg_time FROM s_mytickets ' .
            'WHERE accepted_at IS NOT NULL ' .
            'AND subject_id =  ' . $this->subjectId . ' ' .
            'AND is_duplicate = 0 '.
            "AND created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "'";

        if (!empty($managerId)) {
            $query .= ' AND manager_id = ' . $managerId;
        }

        $result = current($this->getRepository()->execRaw($query)->getResult())->avg_time;
        return $result ?: 0.00;
    }

    public function getAverageReactionByQuarter(int $quarter, int $year = 2025): ResultDTO
    {
        $dates = $this->getQuarterDateRange($quarter, $year);

        return $this->getRepository()->execRaw(
            'SELECT ROUND(AVG(TIMESTAMPDIFF(SECOND, created_at, accepted_at)) / 60, 2) AS avg_time FROM s_mytickets ' .
            'WHERE accepted_at IS NOT NULL ' .
            'AND subject_id =  ' . $this->subjectId . ' ' .
            "AND created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "' " .
            'AND is_duplicate = 0'
        );
    }

    public function getTsAverageResolution(int $priorityId, int $quarter, int $year): float
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getAverageSlaResolution($dates['start'], $dates['end'], $priorityId);
    }

    public function getAverageResolutionByMonth(int $month, int $year = 2025, int $managerId = 0): float
    {
        $dates = $this->getMonthBounds($month, $year);
        return $this->getRepository()->getAverageSlaResolution($dates['start'], $dates['end'], 0, $managerId);
    }

    public function getAverageResolutionByQuarter(int $quarter, int $year = 2025): float
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getAverageSlaResolution($dates['start'], $dates['end']);
    }

    public function getByDirectionForSla(int $directionId, $quarter, int $year = 2025, array $select = ['id']): ResultDTO
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        $this->prepareSelect($select);

        return $this->getRepository()->execRaw(
            'SELECT ' . implode(', ', $select) . ' FROM s_mytickets ' .
            'WHERE subject_id =  ' . $this->subjectId . ' ' .
            'AND direction_id = ' . $directionId . ' ' .
            "AND created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "' " .
            'AND is_duplicate = 0'
        );
    }

    /**
     * Получает диапазон дат квартала
     *
     * @param int $quarter Номер квартала (1-4)
     * @param int $year Год
     * @return array Массив с начальной и конечной датой квартала
     * @throws InvalidArgumentException
     */
    private function getQuarterDateRange(int $quarter, int $year): array
    {
        // Проверяем валидность входных данных
        if ($quarter < 1 || $quarter > 4) {
            throw new InvalidArgumentException("Номер квартала должен быть от 1 до 4");
        }

        if ($year < 1970 || $year > 2200) {
            throw new InvalidArgumentException("Год должен быть в диапазоне 1970-2200");
        }

        // Определяем месяцы квартала
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        // Создаем начальную дату (первый день первого месяца квартала)
        $startDate = DateTime::createFromFormat('Y-m-d', sprintf('%d-%02d-01', $year, $startMonth));
        $startDate->setTime(0, 0, 0);

        // Создаем конечную дату (последний день последнего месяца квартала)
        $endDate = DateTime::createFromFormat('Y-m-d', sprintf('%d-%02d-01', $year, $endMonth));
        $endDate->modify('last day of this month');
        $endDate->setTime(23, 59, 59);

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    public function getPercentInSlaReactionByPriority(int $priorityId, int $quarter, int $year): ResultDTO
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getPercentSlaReactionByPriority($priorityId, $dates['start'], $dates['end']);
    }

    public function getPercentInSlaReactionByMonth(int $month, int $year, int $managerId = 0): float
    {
        $dates = $this->getMonthBounds($month, $year);
        $result = $this->getRepository()->getPercentSlaReaction($dates['start'], $dates['end'], $managerId);

        return current($result->getResult())->sla_percentage ?: 0;
    }

    public function getPercentInSlaReactionByQuarter(int $quarter, int $year): ResultDTO
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getPercentSlaReaction($dates['start'], $dates['end']);
    }

    public function getPercentInSlaResolutionByPriority(int $priorityId, int $quarter, int $year): float
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getPercentSlaResolutionByPriority($priorityId, $dates['start'], $dates['end']);
    }

    public function getPercentInSlaResolutionByMonth(int $month, int $year, int $managerId = 0): float
    {
        $dates = $this->getMonthBounds($month, $year);
        return $this->getRepository()->getPercentSlaResolution($dates['start'], $dates['end'], $managerId);
    }

    public function getPercentInSlaResolutionByQuarter(int $quarter, int $year): float
    {
        $dates = $this->getQuarterDateRange($quarter, $year);
        return $this->getRepository()->getPercentSlaResolution($dates['start'], $dates['end']);
    }

    public function getStatusByCode(string $code): TsTicketStatusDTO
    {
        $result = $this->getRepository()->execRaw("SELECT * FROM ts_ticket_statuses WHERE code = '" . $code . "'")->getResult();
        if (empty($result)) {
            throw new \Exception('Cannot find status with code = "' . $code . '"');
        }
        $status = current($result);

        return new TsTicketStatusDTO(
            $status->id,
            $status->name,
            $status->code
        );
    }

    public function getAllStatuses(): array
    {
        $result = $this->getRepository()->execRaw('SELECT * FROM ts_ticket_statuses')->getResult();
        $statuses = [];

        foreach ($result as $status) {
            $statuses[] = new TsTicketStatusDTO(
                $status->id,
                $status->name,
                $status->code
            );
        }

        return $statuses;
    }

    public function getTicketsByStatusAndMonth(int $statusId, int $month, int $year, array $select = []): ResultDTO
    {
        $this->prepareSelect($select);

        $dates = $this->getMonthBounds($month, $year);

        return $this->getRepository()->execRaw(
            'SELECT ' . implode(', ', $select) . ' FROM ts_tickets' .
            ' JOIN s_mytickets ON s_mytickets.id = ts_tickets.ticket_id ' .
            " WHERE s_mytickets.created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "'" .
            ' AND ts_tickets.status_id = ' . $statusId .
            ' AND s_mytickets.subject_id = ' . $this->subjectId .
            ' AND s_mytickets.is_duplicate = 0'
        );
    }

    public function getTicketsByStatusAndQuarter(int $statusId, int $quarter, int $year, array $select = []): ResultDTO
    {
        $this->prepareSelect($select);

        $dates = $this->getQuarterDateRange($quarter, $year);

        return $this->getRepository()->execRaw(
            'SELECT ' . implode(', ', $select) . ' FROM ts_tickets' .
            ' JOIN s_mytickets ON s_mytickets.id = ts_tickets.ticket_id ' .
            " WHERE s_mytickets.created_at BETWEEN '" . $dates['start']->format('Y-m-d H:i:s') . "' AND '" . $dates['end']->format('Y-m-d H:i:s') . "'" .
            ' AND ts_tickets.status_id = ' . $statusId .
            ' AND s_mytickets.subject_id = ' . $this->subjectId .
            ' AND s_mytickets.is_duplicate = 0'
        );
    }

    private function getMonthBounds(int $month, int $year = 2025): array
    {
        $startDate = DateTime::createFromFormat('Y-m-d', sprintf('%d-%02d-01', $year, $month))
            ->setTime(0, 0, 0);

        $endDate = DateTime::createFromFormat('Y-m-d', sprintf('%d-%02d-01', $year, $month))
            ->modify('last day of this month')
            ->setTime(23, 59, 59);

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    public function isTsTicketExists(int $ticketId): bool
    {
        return count($this->getRepository()->execRaw(
                'SELECT id FROM ts_tickets WHERE ticket_id = ' . $ticketId
            )->getResult()) > 0;
    }

    public function addTsTicket(int $ticketId, int $statusId): void
    {
        $this->getRepository()->execRaw(
            'INSERT INTO ts_tickets (ticket_id, status_id) VALUES (' . $ticketId . ', ' . $statusId . ')',
        );
    }

    public function updateTsTicketStatus(int $ticketId, int $statusId): void
    {
        $this->getRepository()->execRaw(
            'UPDATE ts_tickets SET status_id = ' . $statusId . ' WHERE ticket_id = ' . $ticketId,
        );
    }

    public function deleteTsTicket(int $ticketId): void
    {
        $this->getRepository()->execRaw(
            'DELETE FROM ts_tickets WHERE ticket_id = ' . $ticketId,
        );
    }

    public function isTechnicalSupport(int $ticketId): bool
    {
        return $this->getRepository()->getByPrimary($ticketId)->getSubjectId() === $this->subjectId;
    }

    public function initTsTicket(int $ticketId): void
    {
        if (!$this->isTechnicalSupport($ticketId)) {
            return;
        }

        $technicalSupportDirectionId = (new DirectionRepository())->getByCode('technical_support')->getId();
        $ticket = $this->getByPrimary($ticketId);
        $statuses = $this->getAllStatuses();
        $map = [];
        foreach ($statuses as $status) {
            $map[$status->getCode()] = $status->getId();
        }

        //Устанавливаем направление "Тех. поддержка"
        if ($ticket->getDirectionId() != $technicalSupportDirectionId) {
            $this->updateTicketDirection($ticketId, $technicalSupportDirectionId);
        }

        if (!$this->isTsTicketExists($ticket->getId())) {
            if (!empty($ticket->getCreatedAt()) && empty($ticket->getAcceptedAt())) {
                $this->addTsTicket($ticket->getId(), $map['waiting_acceptance']);
                return;
            }

            if (
                !empty($ticket->getClosedAt())
                && $ticket->getDirectionId() != $technicalSupportDirectionId
            ) {
                $this->addTsTicket($ticket->getId(), $map['sent_to_others']);
            }

            switch ($ticket->getStatusId()) {
                case $this->getRepository()->getStatusByName('В работе'):
                    $this->addTsTicket($ticket->getId(), $map['in_work']);
                    return;
                case $this->getRepository()->getStatusByName('Ожидание'):
                    $this->addTsTicket($ticket->getId(), $map['waiting_clarification']);
                    return;
                case $this->getRepository()->getStatusByName('Урегулирован'):
                    $this->addTsTicket($ticket->getId(), $map['solved']);
            }
        } else {
            if (!empty($ticket->getCreatedAt()) && empty($ticket->getAcceptedAt())) {
                $this->updateTsTicketStatus($ticket->getId(), $map['waiting_acceptance']);
                return;
            }

            if (
                !empty($ticket->getClosedAt())
                && $ticket->getDirectionId() != $technicalSupportDirectionId
            ) {
                $this->updateTsTicketStatus($ticket->getId(), $map['sent_to_others']);
            }

            switch ($ticket->getStatusId()) {
                case $this->getRepository()->getStatusByName('В работе'):
                    $this->updateTsTicketStatus($ticket->getId(), $map['in_work']);
                    return;
                case $this->getRepository()->getStatusByName('Ожидание'):
                    $this->updateTsTicketStatus($ticket->getId(), $map['waiting_clarification']);
                    return;
                case $this->getRepository()->getStatusByName('Урегулирован'):
                    $this->updateTsTicketStatus($ticket->getId(), $map['solved']);
            }
        }
    }

    public function getClosedTicketsByManagerAndMonth(int $managerId, int $month, int $year = 2025): int
    {
        $statusId = $this->getStatusByCode('solved')->getId();
        $dates = $this->getMonthBounds($month, $year);

        return $this->getRepository()->getClosedTickets($dates['start'], $dates['end'], $statusId, $this->subjectId, $managerId);
    }

    public function updateTicketDirection(int $ticketId, int $directionId): void
    {
        $this->getRepository()->execRaw(
            'UPDATE s_mytickets SET direction_id = ' . $directionId . ' WHERE id = ' . $ticketId,
        );
    }
}
