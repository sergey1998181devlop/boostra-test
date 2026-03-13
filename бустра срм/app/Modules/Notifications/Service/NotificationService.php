<?php

namespace App\Modules\Notifications\Service;

use App\Modules\Notifications\DTO\NotificationDTO;
use App\Modules\Notifications\Repository\NotificationRepository;

/**
 * Сервис для работы с уведомлениями менеджеров
 */
class NotificationService
{
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $repository;

    /**
     * Конструктор
     *
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Отправить уведомление
     *
     * @param array $params Параметры уведомления
     * @return int ID созданного уведомления
     */
    public function sendNotification(array $params): int
    {
        if (empty($params['to_user'])) {
            throw new \InvalidArgumentException('Получатель уведомления не указан');
        }

        if (empty($params['subject'])) {
            throw new \InvalidArgumentException('Тема уведомления не указана');
        }

        if (empty($params['message'])) {
            throw new \InvalidArgumentException('Сообщение уведомления не указано');
        }
        
        $notification = new NotificationDTO();
        $notification->from_user = $params['from_user'] ?? 1;
        $notification->to_user = $params['to_user'];
        $notification->subject = $params['subject'];
        $notification->message = $params['message'];
        $notification->is_read = $params['is_read'] ?? false;
        $notification->created_at = date('Y-m-d H:i:s');
        
        return $this->repository->create($notification);
    }

    /**
     * Отправить уведомление нескольким менеджерам
     *
     * @param array $managerIds Массив ID менеджеров
     * @param string $subject Тема
     * @param string $message Сообщение
     * @param int|null $fromUser Отправитель (null - система)
     * @return array Массив ID созданных уведомлений
     */
    public function sendNotificationToMultipleManagers(
        array $managerIds,
        string $subject,
        string $message,
        ?int $fromUser = null
    ): array {
        $notificationIds = [];

        foreach ($managerIds as $managerId) {
            $params = [
                'to_user' => $managerId,
                'subject' => $subject,
                'message' => $message
            ];

            if ($fromUser !== null) {
                $params['from_user'] = $fromUser;
            }

            $notificationIds[] = $this->sendNotification($params);
        }

        return $notificationIds;
    }

    /**
     * Отметить уведомление как прочитанное
     *
     * @param int $id
     * @return bool
     */
    public function markAsRead(int $id): bool
    {
        return $this->repository->markAsRead($id);
    }

    /**
     * Отметить все уведомления менеджера как прочитанные
     *
     * @param int $managerId
     * @return int Количество обновленных уведомлений
     */
    public function markAllAsRead(int $managerId): int
    {
        $unreadNotifications = $this->repository->findByManager($managerId, false);
        $counter = 0;

        foreach ($unreadNotifications as $notification) {
            if ($this->repository->markAsRead($notification->id)) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Получить уведомления менеджера
     *
     * @param int $managerId
     * @param bool|null $onlyUnread
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getManagerNotifications(
        int $managerId,
        ?bool $onlyUnread = null,
        int $limit = 20,
        int $offset = 0
    ): array {
        return $this->repository->findByManager($managerId, $onlyUnread, $limit, $offset);
    }

    /**
     * Получить количество непрочитанных уведомлений
     *
     * @param int $managerId
     * @return int
     */
    public function getUnreadCount(int $managerId): int
    {
        return $this->repository->countUnreadByManager($managerId);
    }
}