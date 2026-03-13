<?php

namespace App\Modules\TicketAssignment\Enums;

/**
 * Типы тикетов для автоназначения
 */
class TicketType
{
    public const COLLECTION = 'collection';
    public const ADDITIONAL_SERVICES = 'additional_services';
    
    // ID родительских тем
    public const COLLECTION_PARENT_ID = 9;
    public const ADDITIONAL_SERVICES_PARENT_ID = 10;

    /**
     * Получить все доступные типы тикетов
     */
    public static function getAll(): array
    {
        return [
            self::COLLECTION,
            self::ADDITIONAL_SERVICES
        ];
    }

    /**
     * Проверить существование типа тикета
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::getAll(), true);
    }

    /**
     * Получить тип тикета по subject_id или parent_id
     */
    public static function getBySubject(int $subjectId, ?int $parentId = null): ?string
    {
        // ID темы "Допы и прочее" = 10
        if ($subjectId === self::ADDITIONAL_SERVICES_PARENT_ID || $parentId === self::ADDITIONAL_SERVICES_PARENT_ID) {
            return self::ADDITIONAL_SERVICES;
        }
        
        // ID темы "Взыскание" = 9
        if ($subjectId === self::COLLECTION_PARENT_ID || $parentId === self::COLLECTION_PARENT_ID) {
            return self::COLLECTION;
        }

        return null;
    }
}


