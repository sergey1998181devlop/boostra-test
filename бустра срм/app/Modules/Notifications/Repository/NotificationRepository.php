<?php

namespace App\Modules\Notifications\Repository;

use App\Modules\Notifications\DTO\NotificationDTO;
use Database;

/**
 * Репозиторий для работы с уведомлениями в БД
 */
class NotificationRepository
{
    /**
     * @var Database БД из Simpla
     */
    private $db;

    /**
     * Конструктор
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Сохранить новое уведомление
     *
     * @param NotificationDTO $notification
     * @return int ID созданного уведомления
     */
    public function create(NotificationDTO $notification): int
    {
        $data = [
            'from_user' => $notification->from_user,
            'to_user' => $notification->to_user,
            'subject' => $notification->subject,
            'message' => $notification->message,
            'is_read' => $notification->is_read ? 1 : 0,
            'created_at' => $notification->created_at ?: date('Y-m-d H:i:s'),
        ];

        $query = $this->db->placehold("INSERT INTO __managers_notifications SET ?%", $data);
        $this->db->query($query);

        return $this->db->insert_id();
    }

    /**
     * Обновить статус уведомления на прочитанное
     *
     * @param int $id
     * @return bool
     */
    public function markAsRead(int $id): bool
    {
        $query = $this->db->placehold("
            UPDATE __managers_notifications SET `is_read` = 1 WHERE id = ?
        ", $id);

        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }
    
    /**
     * Получить уведомления для конкретного менеджера
     *
     * @param int $managerId
     * @param bool|null $onlyUnread Фильтр по прочитанным/непрочитанным (null - все)
     * @param int $limit Ограничение количества
     * @param int $offset Смещение
     * @return array
     */
    public function findByManager(int $managerId, ?bool $onlyUnread = null, int $limit = 20, int $offset = 0): array
    {
        $conditions = ["to_user = ?"];
        $params = [$managerId];

        if ($onlyUnread !== null) {
            $conditions[] = "is_read = ?";
            $params[] = $onlyUnread ? 1 : 0;
        }

        $where = implode(' AND ', $conditions);

        $query = $this->db->placehold("
            SELECT * FROM __managers_notifications 
            WHERE $where
            ORDER BY created_at DESC
            LIMIT ?, ?
        ", [...$params, $offset, $limit]);

        $this->db->query($query);
        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        $notifications = [];
        foreach ($results as $result) {
            $notifications[] = NotificationDTO::fromArray((array)$result);
        }

        return $notifications;
    }

    /**
     * Получить количество непрочитанных уведомлений для менеджера
     *
     * @param int $managerId
     * @return int
     */
    public function countUnreadByManager(int $managerId): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(*) as count 
            FROM __managers_notifications 
            WHERE to_user = ? AND is_read = 0
        ", $managerId);

        $this->db->query($query);
        $result = $this->db->result('count');

        return (int)$result;
    }
}