<?php

namespace App\Service;

use Users;
use Tasks;
use Database;

/**
 * Сервис для отправки недозвонов в PDS кампанию Voximplant
 * 
 * Обеспечивает отправку недозвонов в PDS для перезвона с полным логированием
 * всех операций
 */
class VoximplantMissedCallsService
{
    private VoximplantCampaignService $campaignService;
    private VoximplantLogger $logger;
    private OrganizationService $organizationService;
    private Users $users;
    private Tasks $tasks;
    private Database $db;

    public function __construct(
        VoximplantCampaignService $campaignService,
        VoximplantLogger $logger,
        OrganizationService $organizationService,
        Users $users,
        Tasks $tasks,
        Database $db
    ) {
        $this->campaignService = $campaignService;
        $this->logger = $logger;
        $this->organizationService = $organizationService;
        $this->users = $users;
        $this->tasks = $tasks;
        $this->db = $db;
    }

    /**
     * Отправка недозвонов в PDS кампанию
     * 
     * @param array $params Параметры запроса:
     *   - pdsId: int|null ID PDS кампании (если не указан, берется из конфига)
     *   - attemptsNumber: int Количество попыток перезвона
     *   - intervalHours: float Интервал между звонками в часах
     *   - organizationId: int|null ID организации
     *   - dateRange: string Диапазон дат в формате "YYYY.MM.DD - YYYY.MM.DD"
     * @return array Результат операции
     */
    public function sendMissedCallsToPds(array $params): array
    {
        $startTime = microtime(true);
        $method = 'sendMissedCallsToPds';

        // Валидация входных данных
        $validationResult = $this->validateMissedCallsRequest($params);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $pdsId = $params['pdsId'] ?? null;
        $attemptsNumber = (int) $params['attemptsNumber'];
        $intervalHours = (float) $params['intervalHours'];
        $organizationIdInput = $params['organizationId'] ?? null;
        $dateRange = $params['dateRange'] ?? '';

        // Резолвим organization ID
        $organizationId = null;
        if ($organizationIdInput !== null && $organizationIdInput !== '' && is_numeric($organizationIdInput)) {
            $organizationId = (int) $organizationIdInput;
            $organizationId = $this->organizationService->resolveOrganizationId($organizationId);
        } else {
            $organizationId = $this->organizationService->getDefaultId();
        }

        if (!$this->organizationService->exists($organizationId)) {
            $error = 'Неверный идентификатор организации';
            $this->logger->logError('voximplant_missed_calls', $method, 
                new \Exception($error), ['organization_id' => $organizationId]);
            return ['success' => false, 'error' => $error];
        }

        $organizationName = $this->organizationService->getLabel($organizationId);

        // Парсим диапазон дат
        $dateRangeResult = $this->parseDateRange($dateRange);
        if (!$dateRangeResult['success']) {
            $this->logger->logError('voximplant_missed_calls', $method, 
                new \Exception($dateRangeResult['error']), [
                    'organization_id' => $organizationId,
                    'date_range' => $dateRange,
                ]);
            return [
                'success' => false,
                'error' => $dateRangeResult['error'],
                'organization_name' => $organizationName
            ];
        }

        $taskDateFrom = $dateRangeResult['date_from'];
        $taskDateTo = $dateRangeResult['date_to'];

        // Получаем или резолвим PDS ID
        if (empty($pdsId) || !is_numeric($pdsId)) {
            $pdsId = $this->organizationService->getCallbackCampaignId($organizationId);
            if (empty($pdsId)) {
                $error = 'Не указан ID кампании Vox и не найден в конфигурации для ' . $organizationName;
                $this->logger->logError('voximplant_missed_calls', $method, 
                    new \Exception($error), [
                        'organization_id' => $organizationId,
                    ]);
                return [
                    'success' => false,
                    'error' => $error,
                    'organization_name' => $organizationName
                ];
            }
        }
        $pdsId = (int) $pdsId;

        // Конвертируем интервал из часов в минуты
        $intervalMinutes = (int)($intervalHours * 60);

        $context = [
            'pds_id' => $pdsId,
            'attempts_number' => $attemptsNumber,
            'interval_hours' => $intervalHours,
            'interval_minutes' => $intervalMinutes,
            'organization_id' => $organizationId,
            'organization_name' => $organizationName,
            'date_from' => $taskDateFrom,
            'date_to' => $taskDateTo,
        ];

        try {
            $this->logger->logRequest('voximplant_missed_calls', $method, [
                'pds_id' => $pdsId,
                'attempts_number' => $attemptsNumber,
                'interval_hours' => $intervalHours,
            ], $context);

            // Проверяем на дубликаты отправки
            $today = date("Y-m-d");
            if ($taskDateFrom <= $today && $taskDateTo >= $today) {
                $exists = $this->tasks->existsMissedCallsForOrganization($organizationId);
                if (!empty($exists)) {
                    $error = 'На сегодня контакты уже отправлены для этой МКК. Выберите другой диапазон дат или дождитесь следующего дня.';
                    $this->logger->logError('voximplant_missed_calls', $method, 
                        new \Exception($error), $context);
                    return [
                        'success' => false,
                        'error' => $error,
                        'organization_name' => $organizationName
                    ];
                }
            }

            // Получаем недозвоны
            $filter = [
                'task_date_from' => $taskDateFrom,
                'task_date_to' => $taskDateTo,
                'missed_calls' => true,
                'period' => 'zero',
                'organization_id' => $organizationId
            ];
            $users = $this->users->get_users_ccprolongations($filter);

            if (empty($users)) {
                $error = 'Нет номеров для перезвона';
                $this->logger->logError('voximplant_missed_calls', $method, 
                    new \Exception($error), $context);
                return [
                    'success' => false,
                    'error' => $error,
                    'organization_name' => $organizationName
                ];
            }

            // Форматируем пользователей
            $formattedUsers = $this->formatUsersForMissedCalls($users);

            // Отправляем в PDS
            $result = $this->campaignService->sendToPdsById(
                $formattedUsers,
                $pdsId,
                $organizationId
            );

            if (!$result['success']) {
                $error = $result['error'] ?? 'Ошибка при отправке';
                $this->logger->logError('voximplant_missed_calls', $method, 
                    new \Exception($error), $context);
                return [
                    'success' => false,
                    'error' => $error,
                    'organization_name' => $organizationName
                ];
            }

            // Сохраняем информацию об отправке
            $this->saveMissedCallsRecord([
                'attempts_count' => $attemptsNumber,
                'interval_time' => $intervalMinutes,
                'last_send' => date('Y-m-d H:i:00'),
                'attempts_made' => 1,
                'created' => date('Y-m-d'),
                'robo_number' => $pdsId,
                'organization_id' => $organizationId,
            ]);

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('voximplant_missed_calls', $method, [
                'pds_id' => $pdsId,
                'users_count' => count($formattedUsers),
                'attempts_number' => $attemptsNumber,
            ], $duration, $context);

            return [
                'success' => true,
                'count' => count($formattedUsers),
                'organization_name' => $organizationName
            ];

        } catch (\Throwable $e) {
            $this->logger->logError('voximplant_missed_calls', $method, $e, $context);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'organization_name' => $organizationName
            ];
        }
    }

    /**
     * Валидация параметров запроса
     * 
     * @param array $params Параметры запроса
     * @return array Результат валидации
     */
    public function validateMissedCallsRequest(array $params): array
    {
        $attemptsNumber = $params['attemptsNumber'] ?? null;
        $intervalHours = $params['intervalHours'] ?? null;

        if (empty($attemptsNumber) || empty($intervalHours) || 
            !is_numeric($attemptsNumber) || !is_numeric($intervalHours)) {
            return [
                'success' => false,
                'error' => 'Переданы неправильные параметры'
            ];
        }

        if ($attemptsNumber <= 0) {
            return [
                'success' => false,
                'error' => 'Количество попыток должно быть больше нуля'
            ];
        }

        if ($intervalHours <= 0) {
            return [
                'success' => false,
                'error' => 'Интервал должен быть больше нуля'
            ];
        }

        return ['success' => true];
    }

    /**
     * Парсинг диапазона дат
     * 
     * @param string $dateRange Диапазон дат в формате "YYYY.MM.DD - YYYY.MM.DD"
     * @return array Результат парсинга с date_from и date_to
     */
    private function parseDateRange(string $dateRange): array
    {
        // Значения по умолчанию
        $taskDateFrom = date("Y-m-d");
        $taskDateTo = date("Y-m-d");

        if (empty($dateRange)) {
            return [
                'success' => true,
                'date_from' => $taskDateFrom,
                'date_to' => $taskDateTo,
            ];
        }

        // Разделяем по " - " (пробел-дефис-пробел)
        $filterDateArray = preg_split('/\s*-\s*/', trim($dateRange), 2);
        
        if (count($filterDateArray) < 2) {
            return [
                'success' => false,
                'error' => 'Неверный формат диапазона дат. Ожидается формат: YYYY.MM.DD - YYYY.MM.DD. Получено: ' . htmlspecialchars($dateRange)
            ];
        }

        // Преобразуем точки в дефисы
        $taskDateFromRaw = str_replace('.', '-', trim($filterDateArray[0]));
        $taskDateToRaw = str_replace('.', '-', trim($filterDateArray[1]));

        // Валидация и нормализация дат
        $dateFromObj = \DateTime::createFromFormat('Y-m-d', $taskDateFromRaw);
        $dateToObj = \DateTime::createFromFormat('Y-m-d', $taskDateToRaw);

        if (!$dateFromObj || !$dateToObj) {
            return [
                'success' => false,
                'error' => 'Неверный формат диапазона дат. Ожидается формат: YYYY.MM.DD - YYYY.MM.DD. Получено: ' . htmlspecialchars($dateRange)
            ];
        }

        return [
            'success' => true,
            'date_from' => $dateFromObj->format('Y-m-d'),
            'date_to' => $dateToObj->format('Y-m-d'),
        ];
    }

    /**
     * Форматирование пользователей для отправки недозвонов
     * 
     * @param array $users Массив пользователей
     * @return array Отформатированные пользователи, отсортированные по timezone
     */
    public function formatUsersForMissedCalls(array $users): array
    {
        // Используем метод из VoximplantCampaignService для форматирования
        $formattedUsers = $this->campaignService->formatUsers($users);

        // Сортируем по timezone (используем логику из Voximplant::compareTimezone)
        usort($formattedUsers, function($a, $b) {
            $timezonePattern = "/^(\+|-)\d{2}:\d{2}$/";
            $currentTimezone = $a->UTC ?? '+00:00';
            $nextTimezone = $b->UTC ?? '+00:00';

            if (!preg_match($timezonePattern, $currentTimezone) || !preg_match($timezonePattern, $nextTimezone)) {
                return 0;
            }

            if ($currentTimezone === '+12:00' && $nextTimezone !== '+12:00') {
                return -1;
            }

            if ($currentTimezone !== '+12:00' && $nextTimezone === '+12:00') {
                return 1;
            }

            return strcmp($nextTimezone, $currentTimezone);
        });

        return $formattedUsers;
    }

    /**
     * Сохранение записи об отправке недозвонов
     * 
     * @param array $data Данные для сохранения
     * @return void
     */
    private function saveMissedCallsRecord(array $data): void
    {
        // Проверяем, есть ли поле organization_id в таблице
        $checkColumn = $this->db->placehold("SHOW COLUMNS FROM missed_calls LIKE 'organization_id'");
        $this->db->query($checkColumn);
        $columnExists = $this->db->result();

        // Удаляем organization_id из данных, если поле не существует
        if (!$columnExists && isset($data['organization_id'])) {
            unset($data['organization_id']);
        }

        $query = $this->db->placehold("
            INSERT INTO missed_calls SET ?%
        ", $data);
        $this->db->query($query);
    }
}

