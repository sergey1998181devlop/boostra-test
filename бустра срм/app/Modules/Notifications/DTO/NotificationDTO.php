<?php

namespace App\Modules\Notifications\DTO;

/**
 * DTO для уведомлений менеджеров
 */
class NotificationDTO
{
    /**
     * @var int|null ID уведомления
     */
    public ?int $id = null;

    /**
     * @var int ID менеджера, от которого отправлено уведомление
     */
    public int $from_user;

    /**
     * @var int ID менеджера, которому отправлено уведомление
     */
    public int $to_user;

    /**
     * @var string Тема уведомления
     */
    public string $subject;

    /**
     * @var string Текст сообщения
     */
    public string $message;

    /**
     * @var bool Прочитано ли уведомление
     */
    public bool $is_read = false;

    /**
     * @var string Дата и время создания
     */
    public string $created_at;

    /**
     * Создание DTO из массива данных
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    /**
     * Преобразование DTO в массив
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_user' => $this->from_user,
            'to_user' => $this->to_user,
            'subject' => $this->subject,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}