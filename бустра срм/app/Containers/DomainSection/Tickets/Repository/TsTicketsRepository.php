<?php

namespace App\Containers\DomainSection\Tickets\Repository;

require_once 'api/Tickets.php';

use App\Containers\DomainSection\Tickets\DTO\ClientDTO;
use App\Containers\DomainSection\Tickets\DTO\TicketDTO;
use App\Containers\DomainSection\Tickets\Tables\SlaTable;
use App\Containers\InfrastructureSection\Contracts\RepositoryInterface;
use App\Containers\InfrastructureSection\DTO\ResultDTO\InsertResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\ResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\SelectResultDTO;
use App\Containers\InfrastructureSection\DTO\ResultDTO\UpdateResultDTO;
use DateTime;
use DateTimeZone;

class TsTicketsRepository extends \Tickets implements RepositoryInterface
{
    public function getByPrimary(int $id): TicketDTO
    {
        $ticket = $this->getTicketById($id);
        return new TicketDTO(
            (new ClientDTO(
                (int)$ticket->client_id,
                '', '', '',
                (string)$ticket->client_phone,
                (string)$ticket->client_email,
                (string)$ticket->client_birth,
                (string)$ticket->client_full_name
            )),
            (int)$ticket->id,
            (int)$ticket->chanel_id,
            (int)$ticket->department_id,
            (int)$ticket->manager_id,
            (int)$ticket->subject_id,
            (int)$ticket->status_id,
            (string)$ticket->description,
            $ticket->data ?: [],
            (string)$ticket->created_at ?: '',
            (string)$ticket->updated_at ?: '',
            (int)$ticket->priority_id,
            (int)$ticket->client_status,
            (bool)$ticket->is_repeat,
            (int)$ticket->order_id,
            (int)($ticket->initiator_id ?? 0),
            (int)$ticket->company_id,
            (string)$ticket->accepted_at ?: '',
            (string)$ticket->closed_at ?: '',
            (int)$ticket->working_time,
            (int)$ticket->responsible_person_id,
            (bool)$ticket->is_duplicate,
            (int)$ticket->main_ticket_id,
            (int)$ticket->duplicates_count,
            (bool)$ticket->feedback_received,
            (bool)$ticket->notify_user,
            (string)$ticket->final_comment,
            (int)$ticket->direction_id
        );
    }

    public function exec(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));
        $results = $this->db->results();

        if (strtolower(strtok($query, ' ')) === 'select') {
            $result = new SelectResultDTO($query);
            foreach ($results as $ticket) {
                $result->pushResult(new TicketDTO(
                    (new ClientDTO(
                        $ticket->client_id ?: 0,
                        '', '', '',
                        $ticket->client_phone ?: '',
                        $ticket->client_email ?: '',
                        $ticket->client_birth ?: '',
                        $ticket->client_full_name ?: ''
                    )),
                    (int)$ticket->id,
                    (int)$ticket->chanel_id,
                    (int)$ticket->department_id,
                    (int)$ticket->manager_id,
                    (int)$ticket->subject_id,
                    (int)$ticket->status_id,
                    (string)$ticket->description,
                    $ticket->data ? json_decode($ticket->data, true) : [],
                    (string)$ticket->created_at,
                    (string)$ticket->updated_at,
                    (int)$ticket->priority_id,
                    (int)$ticket->client_status,
                    (bool)$ticket->is_repeat,
                    (int)$ticket->order_id,
                    (int)($ticket->initiator_id ?? 0),
                    (int)$ticket->company_id,
                    (string)$ticket->accepted_at,
                    (string)($ticket->closed_at),
                    (int)$ticket->working_time,
                    (int)$ticket->responsible_person_id,
                    (bool)$ticket->is_duplicate,
                    (int)$ticket->main_ticket_id,
                    (int)$ticket->duplicates_count,
                    (bool)$ticket->feedback_received,
                    (bool)$ticket->notify_user,
                    (string)$ticket->final_comment,
                    (int)$ticket->direction_id
                ));
            }

            return $result;
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }

    public function execRaw(string $query): ResultDTO
    {
        $this->db->query($this->db->placehold($query));

        if (strtolower(strtok($query, ' ')) === 'select') {
            $results = $this->db->results();
            return (new SelectResultDTO($query))->setResult((array)$results);
        } elseif (strtolower(strtok($query, ' ')) === 'insert') {
            return (new InsertResultDTO($query))->setResult((int)$this->db->insert_id());
        } elseif (strtolower(strtok($query, ' ')) === 'update') {
            return (new UpdateResultDTO($query));
        }

        throw new \Exception('Cannot execute query: ' . $query);
    }

    /**
     * Получение ID темы обращения по её названию
     * @param string $name
     * @return int
     */
    public function getSubjectIdByName(string $name): int
    {
        $query = $this->db->placehold('SELECT id FROM s_mytickets_subjects WHERE name = ?', $name);
        $this->db->query($query);
        return $this->db->result('id') ?? 0;
    }

    public function getStatusByName(string $name): int
    {
        $query = $this->db->placehold('SELECT id FROM s_mytickets_statuses WHERE name = ?', $name);
        $this->db->query($query);
        return $this->db->result('id') ?? 0;
    }

    public function getPercentSlaReactionByPriority(int $priorityId, DateTime $dateStart, DateTime $dateEnd): ResultDTO
    {
        $selectedYear = (int)$dateStart->format('Y');
        $quarter = ceil($dateStart->format('m') / 3);

        $query = $this->db->placehold("
            SELECT
                p.name as priority_name,
                COUNT(*) as total_tickets,
                SUM(CASE WHEN TIMESTAMPDIFF(SECOND, t.created_at, t.accepted_at) / 60 <= s.reaction_minutes THEN 1 ELSE 0 END) as within_sla,
                ROUND(
                    (SUM(CASE WHEN TIMESTAMPDIFF(SECOND, t.created_at, t.accepted_at) / 60 <= s.reaction_minutes THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2
                ) as sla_percentage
            FROM s_mytickets t
                JOIN s_mytickets_priority p ON t.priority_id = p.id
                JOIN ts_sla s ON t.priority_id = s.priority_id
            WHERE t.accepted_at IS NOT NULL
                AND t.created_at IS NOT NULL
                AND t.subject_id = " . $this->getSubjectIdByName('Подача тикета') . "
                AND p.id = ?
                AND s.quarter = ?
                AND s.year = ?
                AND t.created_at BETWEEN '" . $dateStart->format('Y-m-d H:i:s') . "' AND '" . $dateEnd->format('Y-m-d H:i:s') . "'
                AND t.is_duplicate = 0
            GROUP BY p.id, p.name;", $priorityId, $quarter, $selectedYear);

        $this->db->query($query);
        return (new SelectResultDTO($query))->setResult($this->db->results() ?: []);
    }

    public function getAverageSlaResolution(DateTime $dateStart, DateTime $dateEnd, int $priorityId = 0, int $managerId = 0): float
    {
        // Получаем только ID закрытых тикетов
        $query = "SELECT id FROM s_mytickets WHERE " .
            "status_id = " . $this->getStatusByName('Урегулирован') .
            " AND created_at BETWEEN '" . $dateStart->format('Y-m-d H:i:s') . "' AND '" . $dateEnd->format('Y-m-d H:i:s') . "'" .
            " AND subject_id = " . $this->getSubjectIdByName('Подача тикета').
            ' AND is_duplicate = 0';

        if ($priorityId > 0) {
            $query .= " AND priority_id = " . $priorityId;
        }

        if ($managerId > 0) {
            $query .= " AND manager_id = " . $managerId;
        }

        $tickets = $this->exec($query)->getResult();

        if (empty($tickets)) {
            return 0.0;
        }

        $totalWorkingTimeSeconds = 0;
        $totalTickets = count($tickets);

        foreach ($tickets as $ticket) {
            $workingTimeData = $this->calculateWorkingTime($ticket->getId());
            $totalWorkingTimeSeconds += $workingTimeData['closed_time'];
        }

        // Возвращаем среднее время в часах
        $averageHours = ($totalWorkingTimeSeconds / $totalTickets) / 60 / 60;

        return round($averageHours, 2);
    }

    public function getPercentSlaResolutionByPriority(int $priorityId, DateTime $dateStart, DateTime $dateEnd): float
    {
        // Получаем максимальное разрешённое время из SLA
        $sla = SlaTable::getInstance()->getByPriorityAndQuarter(
            $priorityId, ceil($dateStart->format('m') / 3), $dateEnd->format('Y')
        );
        $maxResolutionMinutes = $sla->getResolutionMinutes() ?? 0;

        if ($maxResolutionMinutes <= 0) {
            return 0.0;
        }

        // Получаем только ID закрытых тикетов
        $tickets = $this->exec(
            "SELECT id FROM s_mytickets WHERE " .
            "status_id = " . $this->getStatusByName('Урегулирован') .
            " AND priority_id = " . $priorityId .
            " AND created_at BETWEEN '" . $dateStart->format('Y-m-d H:i:s') . "' AND '" . $dateEnd->format('Y-m-d H:i:s') . "'" .
            " AND subject_id = " . $this->getSubjectIdByName('Подача тикета') .
            ' AND is_duplicate = 0'
        )->getResult();

        if (empty($tickets)) {
            return 0.0;
        }

        $compliantTickets = 0;
        $totalClosedTickets = count($tickets);

        foreach ($tickets as $ticket) {
            $workingTimeData = $this->calculateWorkingTime($ticket->getId());
            $workingTimeMinutes = $workingTimeData['closed_time'] / 60;

            if ($workingTimeMinutes <= $maxResolutionMinutes) {
                $compliantTickets++;
            }
        }

        return round(($compliantTickets / $totalClosedTickets) * 100, 2);
    }

    public function getPercentSlaReaction(DateTime $dateStart, DateTime $dateEnd, int $managerId = 0): ResultDTO
    {
        $selectedYear = (int)$dateStart->format('Y');
        $quarter = ceil($dateStart->format('m') / 3);

        $query = $this->db->placehold("
            SELECT
                ROUND(
                    (SUM(CASE WHEN TIMESTAMPDIFF(SECOND, t.created_at, t.accepted_at) / 60 <= s.reaction_minutes THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2
                ) as sla_percentage
            FROM s_mytickets t
                JOIN s_mytickets_priority p ON t.priority_id = p.id
                JOIN ts_sla s ON t.priority_id = s.priority_id
            WHERE t.accepted_at IS NOT NULL
                AND t.created_at IS NOT NULL
                AND t.subject_id = " . $this->getSubjectIdByName('Подача тикета') . "
                AND s.quarter = ?
                AND s.year = ?
                AND t.is_duplicate = 0
                AND t.created_at BETWEEN '" . $dateStart->format('Y-m-d H:i:s') . "' AND '" . $dateEnd->format('Y-m-d H:i:s') . "'
        ", $quarter, $selectedYear);

        if (!empty($managerId)) {
            $query .= $this->db->placehold(' AND t.manager_id = ?', $managerId);
        }

        $this->db->query($query);
        return (new SelectResultDTO($query))->setResult($this->db->results() ?: []);
    }

    public function getPercentSlaResolution(DateTime $dateStart, DateTime $dateEnd, int $managerId = 0): float
    {
        // Получаем максимальное разрешённое время из SLA
        $sla = SlaTable::getInstance()->getByQuarter(
            ceil($dateStart->format('m') / 3), $dateEnd->format('Y')
        );
        $maxResolutionMinutes = $sla->getResolutionMinutes() ?? 0;

        if ($maxResolutionMinutes <= 0) {
            return 0.0;
        }

        // Получаем только ID закрытых тикетов
        $query = $this->db->placehold(
            "SELECT id FROM s_mytickets WHERE " .
            "status_id = ?" .
            " AND created_at BETWEEN ? AND ?" .
            ' AND is_duplicate = 0' .
            " AND subject_id = ?",
            $this->getStatusByName('Урегулирован'), $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $this->getSubjectIdByName('Подача тикета'));

        if (!empty($managerId)) {
            $query .= $this->db->placehold(' AND manager_id = ?', $managerId);
        }

        $tickets = $this->exec($query)->getResult();

        if (empty($tickets)) {
            return 0.0;
        }

        $compliantTickets = 0;
        $totalClosedTickets = count($tickets);

        foreach ($tickets as $ticket) {
            $workingTimeData = $this->calculateWorkingTime($ticket->getId());
            $workingTimeMinutes = $workingTimeData['closed_time'] / 60;

            if ($workingTimeMinutes <= $maxResolutionMinutes) {
                $compliantTickets++;
            }
        }

        return round(($compliantTickets / $totalClosedTickets) * 100, 2);
    }

    public function getClosedTickets(DateTime $dateStart, DateTime $dateEnd, int $statusId, int $subjectId, int $managerId = 0): int
    {
        $query = $this->db->placehold(
            "SELECT
                COUNT(tickets.id) as count
            FROM s_mytickets tickets
                JOIN ts_tickets on tickets.id = ts_tickets.ticket_id
            WHERE tickets.closed_at IS NOT NULL
                AND tickets.created_at BETWEEN ? AND ?
                AND ts_tickets.status_id = ?
                AND tickets.subject_id = ?",
            $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $statusId, $subjectId);

        if (!empty($managerId)) {
            $query .= $this->db->placehold(' AND tickets.manager_id = ?', $managerId);
        }
        $this->db->query($query);

        return intval($this->db->result()->count);
    }
}
