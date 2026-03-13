<?php

namespace api\traits;

/**
 * Обработка дубликатов тикетов
 */
trait TicketDuplicatesHandlerTrait
{
    /**
     * Получение parent_id темы по её id
     *
     * @param int $subjectId ID темы обращения
     * @return int ID родительской темы или тот же ID, если тема уже родительская
     */
    protected function getSubjectParentId(int $subjectId): int
    {
        $subject = $this->getSubjectById($subjectId);
        if (!$subject) {
            return $subjectId;
        }

        return $subject->parent_id > 0 ? $subject->parent_id : $subject->id;
    }

    /**
     * Обработка дубликатов при создании нового тикета
     *
     * @param int $newTicketId ID нового тикета
     * @param array $data Данные тикета
     * @return void
     */
    protected function handleDuplicatesOnCreation(int $newTicketId, array $data): void
    {
        if (empty($data['subject_id'])) {
            return;
        }

        $relatedTickets = $this->findAllRelatedTickets($data);

        $relatedTicketIds = [];
        foreach ($relatedTickets as $ticket) {
            if ($ticket->id != $newTicketId) {
                $relatedTicketIds[] = $ticket->id;
            }
        }

        if (empty($relatedTicketIds)) {
            return;
        }

        $this->markAllAsDuplicates($relatedTicketIds, $newTicketId);
        $this->updateStatusForRelatedTickets($newTicketId);

        $this->logTicketHistory(
            $newTicketId,
            'duplicates',
            '',
            implode(',', $relatedTicketIds),
            $this->getManagerId() ?: 50,
            'Найдены и помечены дублирующиеся тикеты: ' . implode(', ', $relatedTicketIds)
        );
    }

    /**
     * Поиск всех связанных тикетов (с одинаковым клиентом и родительской темой)
     *
     * @param array $data Данные тикета
     * @return array Массив связанных тикетов
     */
    protected function findAllRelatedTickets(array $data): array
    {
        return !empty($data['client_id'])
            ? $this->findAllTicketsForClient($data['client_id'], $data['subject_id'])
            : $this->findAllTicketsByPersonalData($data);
    }

    /**
     * Поиск всех тикетов для клиента с указанной темой или той же родительской категорией
     *
     * @param int $clientId ID клиента
     * @param int $subjectId ID темы обращения
     * @return array Массив тикетов
     */
    protected function findAllTicketsForClient(int $clientId, int $subjectId): array
    {
        $parentId = $this->getSubjectParentId($subjectId);

        $query = $this->db->placehold("
            SELECT t.id, t.created_at, t.status_id
            FROM __mytickets t
            JOIN __mytickets_subjects s ON t.subject_id = s.id
            WHERE
                t.client_id = ? AND
                (s.id = ? OR s.parent_id = ?)
            ORDER BY t.created_at
        ", $clientId, $parentId, $parentId);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Поиск всех тикетов по персональным данным и теме
     *
     * @param array $data Данные тикета
     * @return array Массив тикетов
     */
    protected function findAllTicketsByPersonalData(array $data): array
    {
        $personalData = $this->extractPersonalData($data);
        if (empty($personalData)) {
            return [];
        }

        [$fullName, $birthdate] = $personalData;
        $subjectId = $data['subject_id'];

        $parentId = $this->getSubjectParentId($subjectId);

        $query = $this->db->placehold("
            SELECT t.id, t.created_at, t.status_id
            FROM __mytickets t
            JOIN __mytickets_subjects s ON t.subject_id = s.id
            WHERE
                t.client_id IS NULL
                AND JSON_UNQUOTE(JSON_EXTRACT(t.data,'$.fio')) = ?
                AND JSON_UNQUOTE(JSON_EXTRACT(t.data,'$.birth')) = ?
                AND (s.id = ? OR s.parent_id = ?)
            ORDER BY t.created_at
        ", $fullName, $birthdate, $parentId, $parentId);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Извлекает персональные данные из массива данных тикета
     *
     * @param array $data Данные тикета
     * @return array|null Массив [fullName, birthdate] или null если данные отсутствуют
     */
    protected function extractPersonalData(array $data): ?array
    {
        if (empty($data['data'])) {
            return null;
        }

        $jsonData = is_string($data['data']) ? json_decode($data['data'], true) : $data['data'];

        $fullName = $jsonData['fio'] ?? null;
        $birthdate = $jsonData['birth'] ?? null;

        return ($fullName && $birthdate) ? [$fullName, $birthdate] : null;
    }

    /**
     * Пометка всех указанных тикетов как дубликатов основного тикета
     *
     * @param array $ticketIds Массив ID тикетов для маркировки
     * @param int $mainTicketId ID основного тикета
     * @return bool Результат операции
     */
    protected function markAllAsDuplicates(array $ticketIds, int $mainTicketId): bool
    {
        if (empty($ticketIds)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
        $params = array_merge([$mainTicketId], $ticketIds);

        $query = $this->db->placehold("
            UPDATE __mytickets
            SET is_duplicate = 1, main_ticket_id = ?, is_highlighted = 0
            WHERE id IN ($placeholders)
        ", ...$params);

        $this->db->query($query);
        $this->updateDuplicatesCount($mainTicketId);

        return true;
    }

    /**
     * Обновление счетчика дублирующихся тикетов
     *
     * @param int $mainTicketId ID главного тикета
     * @return void
     */
    protected function updateDuplicatesCount(int $mainTicketId): void
    {
        $query = $this->db->placehold("
            SELECT COUNT(*) as count
            FROM __mytickets
            WHERE main_ticket_id = ? AND is_duplicate = 1
        ", $mainTicketId);

        $this->db->query($query);
        $count = $this->db->result('count');

        $this->db->query("
            UPDATE __mytickets
            SET duplicates_count = ?, is_duplicate = 0, main_ticket_id = NULL
            WHERE id = ?
        ", $count ?? 0, $mainTicketId);
    }

    /**
     * Обновление статуса для всех связанных тикетов
     *
     * @param int $mainTicketId ID главного тикета
     * @return bool Результат операции
     */
    public function updateStatusForRelatedTickets(int $mainTicketId): bool
    {
        $targetStatus = self::DUPLICATE;
        $sourceStatuses = [self::NEW, self::IN_WORK, self::ON_HOLD, self::REQUEST_DETAILS];

        $placeholders = implode(',', array_fill(0, count($sourceStatuses), '?'));
        $params = array_merge([$mainTicketId], $sourceStatuses);

        // Получаем текущие статусы дублей
        $query = $this->db->placehold("
        SELECT id, status_id
        FROM __mytickets
        WHERE
            main_ticket_id = ?
            AND is_duplicate = 1
            AND status_id IN ($placeholders)
    ", ...$params);

        $this->db->query($query);
        $currentStatuses = [];
        foreach ($this->db->results() as $result) {
            $currentStatuses[$result->id] = $result->status_id;
        }

        if (empty($currentStatuses)) {
            return true;
        }

        $updateParams = array_merge([$targetStatus, $mainTicketId], $sourceStatuses);
        $updateQuery = $this->db->placehold("
            UPDATE __mytickets
            SET status_id = ?, is_highlighted = 0
            WHERE
                main_ticket_id = ?
                AND is_duplicate = 1
                AND status_id IN ($placeholders)
        ", ...$updateParams);

        $this->db->query($updateQuery);

        foreach ($currentStatuses as $ticketId => $oldStatus) {
            $this->logTicketHistory(
                $ticketId,
                'status_id',
                $oldStatus,
                $targetStatus,
                $this->getManagerId() ?: 50,
                'Автоматическое обновление статуса дублирующегося тикета на "Дубликаты"'
            );
        }

        return true;
    }

    /**
     * Получение дублирующихся тикетов для указанного основного тикета
     *
     * @param int $mainTicketId ID основного тикета
     * @return array Массив дублирующихся тикетов
     */
    public function getDuplicateTickets(int $mainTicketId): array
    {
        $query = $this->db->placehold("
            SELECT
                tick.id,
                tick.created_at,
                tick.status_id,
                tick.subject_id,
                tick.manager_id,
                stat.name AS status_name,
                subj.name AS subject_name,
                man.name AS manager_name
            FROM __mytickets AS tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id
            LEFT JOIN __managers AS man ON man.id = tick.manager_id
            WHERE
                tick.main_ticket_id = ? AND
                tick.is_duplicate = 1
            ORDER BY tick.created_at DESC
        ", $mainTicketId);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получение комментариев из связанных (дублирующихся) тикетов
     *
     * @param int $mainTicketId ID главного тикета
     * @return array Массив комментариев
     */
    public function getRelatedTicketsComments(int $mainTicketId): array
    {
        $query = $this->db->placehold("
            SELECT
                com.id,
                com.text,
                com.created_at,
                man.name as manager_name,
                tick.id as ticket_id,
                tick.created_at as ticket_created_at
            FROM __mytickets_comments com
            LEFT JOIN __mytickets tick ON tick.id = com.ticket_id
            LEFT JOIN __managers man ON man.id = com.manager_id
            WHERE
                (tick.id = ? OR tick.main_ticket_id = ?)
                AND com.is_show = 1
            ORDER BY com.created_at DESC
        ", $mainTicketId, $mainTicketId);

        $this->db->query($query);

        return $this->db->results() ?: [];
    }
}
