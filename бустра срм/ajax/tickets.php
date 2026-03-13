<?php

require dirname(__DIR__) . '/api/Simpla.php';

session_start();

use App\Enums\TicketChannel;
use App\Enums\TicketStatus;
use App\Enums\TicketSubject;
use Carbon\Carbon;

/**
 * Обработчик AJAX-запросов для работы с тикетами
 *
 * @package TicketSystem
 * @author Development Team
 */
class TicketActions extends Simpla {

    /** @var int ID системного пользователя */
    private const SYSTEM_USER_ID = 50;

    /** @var int Максимальное количество записей для обработки */
    private const DEFAULT_LIMIT = 1000;

    /** @var string Имя файла логов */
    private const LOG_FILE = 'usedesk.log';

    /** @const string Название темы тикета для ТП */
    private const TS_SUBJECT_NAME = 'Подача тикета';

    /** @var array|null Скомпилированные паттерны для очистки email */
    private static $emailCleanupPatterns = null;

    public function __construct() {
        parent::__construct();

        if (self::$emailCleanupPatterns === null) {
            self::$emailCleanupPatterns = [
                // Цитирование и blockquotes
                '/<blockquote[^>]*>.*?<\/blockquote>/is',
                '/<div[^>]*gmail_quote[^>]*>.*?<\/div>/is',
                '/<div[^>]*quote[^>]*>.*?<\/div>/is',
                '/<div[^>]*composeWebView_previouse_content[^>]*>.*?<\/div>/is',

                // Подписи почтовых клиентов
                '/<div[^>]*mail-app-auto-default-signature[^>]*>.*?<\/div>/is',
                '/<div[^>]*mail-app-auto-quote[^>]*>.*?<\/div>/is',

                // Даты и временные метки
                '/.*?(пятница|понедельник|вторник|среда|четверг|суббота|воскресенье),?\s*\d{1,2}\s*(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря)\s*\d{4}.*?от\s*.*?<.*?>.*?:/ui',
                '/.*?(чт|пт|пн|вт|ср|чт|сб|вс),\s*\d{1,2}\s*(июл|янв|фев|мар|апр|май|июн|авг|сен|окт|ноя|дек)\.?\s*\d{4}.*?<.*?>.*?:/ui',
                '/\d{1,2}\.\d{1,2}\.\d{4},\s*\d{1,2}:\d{2},.*?<.*?>.*?:/ui',

                // Подписи мобильных клиентов
                '/--\s*отправлено\s*из\s*.*?почты/ui',
                '/--.*?отправлено\s*из.*?для\s*(android|ios)/ui',
                '/отправлено\s*из\s*мобильной.*?почты/ui',

                // Заголовки переписки
                '/----------------.*?кому:.*?<.*?>.*?:/uis',
                '/тема:.*?от.*?<.*?>.*?:/ui',
                '/кому:.*?<.*?>.*?тема:/ui',

                // CSS стили и служебная информация
                '/border-left[^;]+;[^>]*>/ui',
                '/style="[^"]*"/ui',
                '/id="[^"]*lineBreakAtBeginningOfSignature[^"]*"/ui',

                // Изображения
                '/<img[^>]*>/ui'
            ];
        }
    }

    /**
     * Основная точка входа для обработки AJAX-запросов
     *
     * @return void
     */
    public function fetch(): void {
        $action = $this->request->get('action', 'string');

        $actions = [
            'get_client_tickets' => 'getClientTickets',
            'get_client_loans' => 'getClientLoans',
            'create_ticket_on_repeat_email_contact' => 'createTicketOnRepeatEmailContact',
            'clear_ticket_descriptions' => 'clearTicketDescriptions',
            'get_technical_support_type_id' => 'getTechnicalSupportTicketTypeId',
            'get_new_tickets' => 'getNewTickets',
        ];

        if (!isset($actions[$action])) {
            $this->sendError('Неизвестное действие: ' . $action);
            return;
        }

        try {
            $this->{$actions[$action]}();
        } catch (\Throwable $e) {
            $this->logError('Ошибка выполнения действия: ' . $action, ['exception' => $e->getMessage()]);
            $this->sendError('Внутренняя ошибка сервера');
        }
    }

    private function getNewTickets(): void
    {
        $soundSetting = (string)($this->settings->sound_ticket_notice ?? '');

        if ($soundSetting === '') {
            echo json_encode(['tickets' => false]);
            return;
        }

        $managerId = (int)$this->getManagerId();
        $authorizedCollectionManagers = is_array($this->settings->authorized_collection_managers ?? null)
            ? array_map('intval', $this->settings->authorized_collection_managers)
            : [];
        $authorizedDopyManagers = is_array($this->settings->authorized_dopy_managers ?? null)
            ? array_map('intval', $this->settings->authorized_dopy_managers)
            : [];

        $managerHasCollectionAccess = in_array($managerId, $authorizedCollectionManagers, true);
        $managerHasDopyAccess = in_array($managerId, $authorizedDopyManagers, true);

        $subjectsParentIds = [];

        $needCollection = in_array($soundSetting, ['COLLECTION', 'ALL'], true);
        $needDopy = in_array($soundSetting, ['EXTRAS_AND_OTHERS', 'ALL'], true);

        if ($needCollection && $managerHasCollectionAccess) {
            $subjectsParentIds[] = 9;
        }

        if ($needDopy && $managerHasDopyAccess) {
            $subjectsParentIds[] = 10;
        }

        if (empty($subjectsParentIds)) {
            echo json_encode(['tickets' => false]);
            return;
        }

        $ticketId = $this->request->get('id', 'integer');

        // Ключ кэша по правам доступа (менеджеры с одинаковыми правами разделяют кэш)
        $subjectsHash = md5(implode(',', $subjectsParentIds));
        $cacheKey = 'tickets:new:' . $subjectsHash . ($ticketId > 0 ? ':' . $ticketId : '');

        $result = $this->caches->wrap($cacheKey, 60, function () use ($subjectsParentIds, $ticketId) {
            $this->db->query('SELECT * FROM __mytickets_subjects WHERE parent_id IN (' . implode(',', $subjectsParentIds) . ')');
            $subjects = $this->db->results();
            $subjectIds = [];
            foreach ($subjects as $subject) {
                $subjectIds[] = $subject->id;
            }

            $date = Carbon::now()->startOfDay();

            $query = $this->db->placehold(
                "SELECT id, status_id, manager_id, subject_id FROM __mytickets
                 WHERE status_id = 1 " . ($ticketId > 0 ? "AND id = " . $ticketId : "") . "
                   AND subject_id IN (" . implode(',', array_map('intval', $subjectIds)) . ")
                   AND created_at > ?
                 ORDER BY id DESC LIMIT 1",
                $date->toDateString()
            );

            $this->db->query($query);

            return $this->db->results() ?: [];
        });

        echo json_encode(['tickets' => $result]);
    }
    /**
     * Получение списка тикетов клиента
     *
     * @return void
     */
    private function getClientTickets(): void {
        $clientId = $this->request->get('client_id', 'integer');

        if (!$clientId || $clientId <= 0) {
            $this->sendError('Некорректный ID клиента');
            return;
        }

        try {
            $tickets = $this->tickets->getClientTickets($clientId);
            $this->sendSuccess(['tickets' => $tickets ?: []]);
        } catch (\Throwable $e) {
            $this->logError('Ошибка получения тикетов клиента', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->sendError('Не удалось получить тикеты');
        }
    }

    /**
     * Метод получает ID типа обращения "Техническая поддержка"
     * @return void
     */
    private function getTechnicalSupportTicketTypeId(): void
    {
        $query = $this->db->placehold(
            "SELECT id FROM s_mytickets_subjects WHERE name=?",
            self::TS_SUBJECT_NAME
        );
        $this->db->query($query);
        $result = current($this->db->results());

        $this->response->json_output([
            'success' => true,
            'id' => $result ? (int)$result->id : 0
        ]);
    }

    /**
     * Получение списка займов клиента
     *
     * @return void
     */
    private function getClientLoans(): void {
        $clientId = $this->request->get('client_id', 'integer');

        if (!$clientId || $clientId <= 0) {
            $this->sendError('Некорректный ID клиента');
            return;
        }

        try {
            $loans = $this->orders->get_orders(['user_id' => $clientId]);

            $formattedLoans = array_map(function($loan) {
                return [
                    'id' => $loan->order_id,
                    'amount' => $loan->amount,
                    'date' => date('d.m.Y', strtotime($loan->date)),
                    'status_1c' => $loan->status_1c,
                ];
            }, $loans ?: []);

            $this->sendSuccess(['loans' => $formattedLoans]);
        } catch (\Throwable $e) {
            $this->logError('Ошибка получения займов клиента', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            $this->sendError('Не удалось получить список займов');
        }
    }

    /**
     * Создание дубликата тикета при повторном обращении клиента по email
     *
     * @return void
     */
    public function createTicketOnRepeatEmailContact(): void {
        $requestData = $this->parseAndValidateRequestData();
        if (!$requestData) {
            $this->sendError('Некорректные данные запроса');
            return;
        }

        $ticket = $this->findExistingTicket($requestData);
        if (!$ticket) {
            $this->sendError('Существующий тикет не найден');
            return;
        }

        if ($this->isCollectionsTicket($ticket)) {
            return;
        }

        try {
            $newTicketId = $this->createDuplicateTicket($ticket, $requestData);
            $this->sendManagerNotification($ticket, $newTicketId);
            $this->sendSuccess(['ticket_id' => $newTicketId]);
        } catch (\Throwable $e) {
            $this->logError('Ошибка создания дубликата тикета', ['error' => $e->getMessage()]);
            $this->sendError('Не удалось создать тикет');
        }
    }

    /**
     * Парсинг и валидация данных запроса
     *
     * @return array|null Массив валидных данных или null при ошибке
     */
    private function parseAndValidateRequestData(): ?array {
        $rawData = $this->request->post();
        if (empty($rawData)) {
            $this->logError('Пустые данные запроса');
            return null;
        }

        $dataPost = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError('Ошибка парсинга JSON: ' . json_last_error_msg());
            return null;
        }

        // Validate and sanitize properly
        $source = isset($dataPost['source']) ? trim($dataPost['source']) : '';
        $message = isset($dataPost['message']) ? $this->sanitizeContent($dataPost['message']) : '';
        $email = isset($dataPost['client_email']) ? trim($dataPost['client_email']) : '';
        $phone = isset($dataPost['client_phone']) ? preg_replace('/\D+/', '', $dataPost['client_phone']) : '';

        // Validate email properly
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        // At least one identifier required
        if (empty($source) && empty($phone) && empty($email)) {
            $this->logError('Отсутствуют идентифицирующие данные');
            return null;
        }

        return [
            'source' => $source,
            'message' => $message,
            'client_email' => $email,
            'client_phone' => $phone
        ];
    }

    /**
     * Поиск существующего тикета по source в data или точной ссылке в description
     *
     * @param array $requestData Данные запроса
     * @return object|null Объект тикета или null
     */
    private function findExistingTicket(array $requestData): ?object {
        // Поиск только по source в data
        if (!empty($requestData['source'])) {
            $ticket = $this->findTicketBySource($requestData['source']);
            if ($ticket) {
                return $ticket;
            }
        }

        // Поиск по точной ссылке в description
        if (!empty($requestData['source'])) {
            $ticket = $this->findTicketByUrlInDescription($requestData['source']);
            if ($ticket) {
                return $ticket;
            }
        }

        return null;
    }

    /**
     * Поиск тикета по источнику в data (только для email канала)
     *
     * @param string $source Идентификатор источника
     * @return object|null Объект тикета или null
     */
    private function findTicketBySource(string $source): ?object {
        if (empty($source)) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT t.*,
                   CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) as client_name,
                   u.email as client_email_from_user,
                   u.phone_mobile as client_phone_from_user,
                   s.parent_id as subject_parent_id
            FROM __mytickets t
            JOIN __users u ON u.id = t.client_id
            LEFT JOIN __mytickets_subjects s ON s.id = t.subject_id
            WHERE (t.data->>'$.source' = ? OR t.data->>'$.chat_link' = ?)
            ORDER BY t.created_at DESC
            LIMIT 1
        ", $source, TicketChannel::EMAIL()->getValue());

        $this->db->query($query);
        return $this->db->result() ?: null;
    }

    /**
     * Поиск тикета по точной ссылке в description (только для email канала)
     *
     * @param string $url Точная ссылка для поиска
     * @return object|null Объект тикета или null
     */
    private function findTicketByUrlInDescription(string $url): ?object {
        if (empty($url)) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT t.*,
                   CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) as client_name,
                   u.email as client_email_from_user,
                   u.phone_mobile as client_phone_from_user,
                   s.parent_id as subject_parent_id
            FROM __mytickets t
            JOIN __users u ON u.id = t.client_id
            LEFT JOIN __mytickets_subjects s ON s.id = t.subject_id
            WHERE t.description LIKE ? AND t.chanel_id = ?
            ORDER BY t.created_at DESC
            LIMIT 1
        ", '%' . $url . '%', TicketChannel::EMAIL()->getValue());

        $this->db->query($query);
        return $this->db->result() ?: null;
    }

    /**
     * Проверяет, относится ли тикет к категории "Взыскания"
     *
     * @param object $ticket Объект тикета с полями subject_id и subject_parent_id
     * @return bool True, если тикет относится к взысканиям
     */
    private function isCollectionsTicket(object $ticket): bool {
        $collectionsId = TicketSubject::COLLECTIONS()->getValue();

        return $ticket->subject_id == $collectionsId
            || (isset($ticket->subject_parent_id) && $ticket->subject_parent_id == $collectionsId);
    }

    /**
     * Создание дубликата тикета
     *
     * @param object $ticket Оригинальный тикет
     * @param array $requestData Данные нового обращения
     * @return int ID созданного тикета
     * @throws \RuntimeException При ошибке создания
     */
    private function createDuplicateTicket(object $ticket, array $requestData): int {
        $newTicketData = [
            'client_id' => $ticket->client_id,
            'chanel_id' => TicketChannel::EMAIL()->getValue(),
            'subject_id' => $ticket->subject_id,
            'status_id' => TicketStatus::NEW()->getValue(),
            'priority_id' => $ticket->priority_id,
            'company_id' => $ticket->company_id,
            'order_id' => $ticket->order_id,
            'initiator_id' => self::SYSTEM_USER_ID,
            'responsible_person_id' => $ticket->responsible_person_id,
            'description' => $requestData['message'],
            'data' => json_encode(['source' => $requestData['source']]),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $newTicketId = $this->tickets->createNewTicket($newTicketData);

        if (!$newTicketId) {
            throw new \RuntimeException('Не удалось создать тикет');
        }

        return $newTicketId;
    }

    /**
     * Отправка уведомления менеджеру о повторном обращении
     *
     * @param object $ticket Оригинальный тикет
     * @param int $newTicketId ID нового тикета
     * @return void
     */
    private function sendManagerNotification(object $ticket, int $newTicketId): void {
        if (!$ticket->manager_id || $ticket->manager_id == $this->getManagerId()) {
            return;
        }

        $baseUrl = $this->config->back_url;
        $message = sprintf(
            "Клиент отправил повторное обращение по вашему тикету %s/tickets/%d.\n" .
            "Создан новый тикет-дубликат: %s/tickets/%d\n" .
            "Клиент: %s",
            $baseUrl, $ticket->id,
            $baseUrl, $newTicketId,
            $ticket->client_name ?? 'Не указан'
        );

        $this->notificationsManagers->sendNotification([
            'from_user' => $this->getManagerId(),
            'to_user' => $ticket->manager_id,
            'subject' => "Повторное обращение клиента",
            'message' => $message
        ]);
    }

    /**
     * Очистка описаний тикетов от HTML и истории переписки
     * Обрабатывает тикеты батчами для оптимизации памяти
     *
     * @return void
     */
    private function clearTicketDescriptions(): void {
        try {
            $batchSize = 100;
            $offset = 0;
            $totalUpdated = 0;
            $totalChecked = 0;

            do {
                $query = $this->db->placehold("
                    SELECT id, description
                    FROM __mytickets
                    WHERE description IS NOT NULL
                    AND description != ''
                    AND initiator_id = ?
                    AND chanel_id = ?
                    LIMIT ?, ?
                ", self::SYSTEM_USER_ID, TicketChannel::EMAIL()->getValue(), $offset, $batchSize);

                $this->db->query($query);
                $tickets = $this->db->results();

                if (empty($tickets)) {
                    break;
                }

                foreach ($tickets as $ticket) {
                    $cleanDescription = $this->sanitizeContent($ticket->description);

                    if ($cleanDescription !== $ticket->description) {
                        $updateQuery = $this->db->placehold("
                            UPDATE __mytickets
                            SET description = ?
                            WHERE id = ?
                        ", $cleanDescription, $ticket->id);

                        $this->db->query($updateQuery);
                        $totalUpdated++;
                    }
                }

                $totalChecked += count($tickets);
                $offset += $batchSize;

                // Ограничение на количество запросов
                if ($totalChecked > self::DEFAULT_LIMIT) {
                    break;
                }

            } while (count($tickets) === $batchSize);

            $this->sendSuccess([
                'message' => "Очищено описание у {$totalUpdated} тикетов",
                'updated_count' => $totalUpdated,
                'checked_count' => $totalChecked
            ]);

        } catch (\Throwable $e) {
            $this->logError('Ошибка очистки описаний тикетов', ['error' => $e->getMessage()]);
            $this->sendError('Не удалось очистить описания тикетов');
        }
    }

    /**
     * Очистка контента от HTML, истории переписки и нормализация
     *
     * @param string $content Исходный контент
     * @return string Очищенный контент
     */
    private function sanitizeContent(string $content): string {
        if (empty($content)) {
            return '';
        }

        // Step 1: Remove email history
        $content = $this->removeEmailHistory($content);

        // Step 2: Strip HTML tags
        $content = strip_tags($content);

        // Step 3: Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Step 4: Normalize whitespace
        $content = preg_replace('/\s+/', ' ', trim($content));

        return $content;
    }

    /**
     * Удаление истории email переписки
     *
     * @param string $content Исходный контент
     * @return string Контент без истории переписки
     */
    private function removeEmailHistory(string $content): string {
        return preg_replace(self::$emailCleanupPatterns, '', $content);
    }

    /**
     * Логирование ошибок
     *
     * @param string $message Сообщение об ошибке
     * @param array $context Контекст ошибки
     * @return void
     */
    private function logError(string $message, array $context = []): void {
        $logData = array_merge(['error' => $message, 'timestamp' => time()], $context);
        $this->logging(__METHOD__, 'error', json_encode($logData, JSON_UNESCAPED_UNICODE), $message, self::LOG_FILE);
    }

    /**
     * Отправка успешного ответа
     *
     * @param array $data Данные для ответа
     * @return void
     */
    private function sendSuccess(array $data): void {
        $this->response->json_output(array_merge(['success' => true], $data));
    }

    /**
     * Отправка ответа с ошибкой
     *
     * @param string $message Сообщение об ошибке
     * @return void
     */
    private function sendError(string $message): void {
        $this->response->json_output([
            'success' => false,
            'message' => $message
        ]);
    }
}

$controller = new TicketActions();
$controller->fetch();
