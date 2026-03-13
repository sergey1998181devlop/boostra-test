<?php

declare(strict_types=1);

namespace App\Service;

use Users;

/**
 * Сервис создания и распределения задач пролонгации по расписанию (крон).
 *
 * Создаёт задачи на день, распределяет по менеджерам, отправляет в Voximplant.
 * Проверяет полноту импорта балансов перед созданием задач.
 */
class CCTaskScheduleService
{
    private const LOG_CHANNEL = 'cc_task_schedule';
    private const PERIOD_ZERO = 'zero';
    private const COMPLETENESS_WARNING_THRESHOLD = 80;

    /** @var CCTaskService */
    private $taskService;

    /** @var ManagerScheduleService */
    private $managerScheduleService;

    /** @var VoximplantLogger */
    private $logger;

    /** @var OrganizationService */
    private $organizationService;

    /** @var Users */
    private $users;

    /** @var UserBalanceImportService */
    private $balanceImportService;

    public function __construct(
        CCTaskService $taskService,
        ManagerScheduleService $managerScheduleService,
        VoximplantLogger $logger,
        OrganizationService $organizationService,
        Users $users,
        UserBalanceImportService $balanceImportService
    ) {
        $this->taskService = $taskService;
        $this->managerScheduleService = $managerScheduleService;
        $this->logger = $logger;
        $this->organizationService = $organizationService;
        $this->users = $users;
        $this->balanceImportService = $balanceImportService;
    }

    /**
     * Создать задачи пролонгации для организации на дату.
     *
     * Проверяет полноту данных импорта, получает задачи, форматирует, распределяет по менеджерам.
     *
     * @param int $organizationId ID организации
     * @param string|null $date Дата Y-m-d (по умолчанию сегодня)
     * @return array{created: int, managers_count: int, tasks_fetched: int, tasks_formatted: int, completeness: array}
     */
    public function createTasksForOrganization(int $organizationId, ?string $date = null): array
    {
        $startTime = microtime(true);
        $method = 'createTasksForOrganization';

        $date = $date ?? date('Y-m-d');
        $context = ['organization_id' => $organizationId, 'date' => $date];

        try {
            $this->logger->logRequest(self::LOG_CHANNEL, $method, [
                'organization_id' => $organizationId,
                'date' => $date,
            ], $context);

            $completenessStartTime = microtime(true);
            $completeness = $this->balanceImportService->checkImportCompleteness($date);
            $completenessDuration = microtime(true) - $completenessStartTime;
            if (isset($completeness['percent']) && $completeness['percent'] < self::COMPLETENESS_WARNING_THRESHOLD) {
                $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_completeness_warning', [
                    'found' => $completeness['found'],
                    'expected' => $completeness['expected'],
                    'percent' => $completeness['percent'],
                    'message' => 'Import completeness below 80%',
                    'duration' => round($completenessDuration, 2),
                ], $completenessDuration, $context);
            }

            $filter = [
                'from' => $date . ' 00:00:00',
                'to' => $date . ' 23:59:59',
                'organization_id' => $organizationId,
            ];

            $fetchStartTime = microtime(true);
            $tasks = $this->users->get_cctasks($filter);
            $fetchDuration = microtime(true) - $fetchStartTime;
            $tasksFetched = is_array($tasks) ? count($tasks) : 0;

            $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_fetched', [
                'tasks_count' => $tasksFetched,
                'duration' => round($fetchDuration, 2),
            ], $fetchDuration, $context);

            $formatStartTime = microtime(true);
            $tasks = $this->taskService->formatTasks(is_array($tasks) ? $tasks : []);
            $formatDuration = microtime(true) - $formatStartTime;
            $tasksFormatted = count($tasks);

            $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_formatted', [
                'tasks_count' => $tasksFormatted,
                'duration' => round($formatDuration, 2),
            ], $formatDuration, $context);

            $managersStartTime = microtime(true);
            $managers = $this->managerScheduleService->getScheduledManagers($date, false, $organizationId);
            $managers = $this->taskService->sortManagers($managers);
            $managersDuration = microtime(true) - $managersStartTime;

            $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_managers_loaded', [
                'managers_count' => count($managers),
                'duration' => round($managersDuration, 2),
            ], $managersDuration, $context);

            $distributeStartTime = microtime(true);
            $createdCount = $this->taskService->distributeTasks(
                $tasks,
                $managers,
                $date,
                self::PERIOD_ZERO
            );
            $distributeDuration = microtime(true) - $distributeStartTime;

            $this->logger->logSuccess(self::LOG_CHANNEL, $method . '_distributed', [
                'created_count' => $createdCount,
                'duration' => round($distributeDuration, 2),
            ], $distributeDuration, $context);

            $duration = microtime(true) - $startTime;
            $result = [
                'created' => $createdCount,
                'managers_count' => count($managers),
                'tasks_fetched' => $tasksFetched,
                'tasks_formatted' => $tasksFormatted,
                'completeness' => $completeness,
                'durations' => [
                    'completeness_check' => round($completenessDuration, 2),
                    'fetch_tasks' => round($fetchDuration, 2),
                    'format_tasks' => round($formatDuration, 2),
                    'load_managers' => round($managersDuration, 2),
                    'distribute_tasks' => round($distributeDuration, 2),
                    'total' => round($duration, 2),
                ],
            ];

            $this->logger->logSuccess(self::LOG_CHANNEL, $method, $result, $duration, $context);

            return $result;
        } catch (\Throwable $e) {
            $this->logger->logError(self::LOG_CHANNEL, $method, $e, $context);
            throw $e;
        }
    }

    /**
     * Отправить задачи в Voximplant для всех менеджеров организации.
     *
     * @param int $organizationId ID организации
     * @param string|null $date Дата Y-m-d (по умолчанию сегодня)
     * @return array<int, array{success: bool}>
     */
    public function sendTasksToVoximplantForOrganization(int $organizationId, ?string $date = null): array
    {
        $startTime = microtime(true);
        $method = 'sendTasksToVoximplantForOrganization';

        $date = $date ?? date('Y-m-d');
        $context = ['organization_id' => $organizationId, 'date' => $date];

        try {
            $this->logger->logRequest(self::LOG_CHANNEL, $method, [
                'organization_id' => $organizationId,
                'date' => $date,
            ], $context);

            $managersLoadStartTime = microtime(true);
            $managers = $this->managerScheduleService->getScheduledManagers($date, false, $organizationId);
            $managerIds = array_map(function ($m) {
                return is_object($m) ? (int)$m->id : (int)$m;
            }, $managers);
            $managersLoadDuration = microtime(true) - $managersLoadStartTime;

            $sendStartTime = microtime(true);
            $results = $this->taskService->sendTasksToVoximplant($managerIds, $organizationId, $date);
            $sendDuration = microtime(true) - $sendStartTime;

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess(self::LOG_CHANNEL, $method, [
                'managers_count' => count($managerIds),
                'sent_count' => count($results),
                'durations' => [
                    'load_managers' => round($managersLoadDuration, 2),
                    'send_to_vox' => round($sendDuration, 2),
                    'total' => round($duration, 2),
                ],
            ], $duration, $context);

            return $results;
        } catch (\Throwable $e) {
            $this->logger->logError(self::LOG_CHANNEL, $method, $e, $context);
            throw $e;
        }
    }
}
