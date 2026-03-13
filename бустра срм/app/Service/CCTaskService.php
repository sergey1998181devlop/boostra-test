<?php

namespace App\Service;

use Users;
use Tasks;
use Managers;
use Orders;
use Comments;
use Soap1c;

/**
 * Сервис для работы с задачами пролонгации
 * 
 * Обеспечивает бизнес-логику работы с задачами:
 * - Обновление статусов
 * - Добавление перспектив и перезвонов
 * - Распределение задач между менеджерами
 * - Форматирование задач
 */
class CCTaskService
{
    private VoximplantDncService $dncService;
    private VoximplantCampaignService $campaignService;
    private VoximplantLogger $logger;
    private Users $users;
    private Tasks $tasks;
    private Managers $managers;
    private Orders $orders;
    private Comments $comments;
    private Soap1c $soap;

    public function __construct(
        VoximplantDncService $dncService,
        VoximplantCampaignService $campaignService,
        VoximplantLogger $logger
    ) {
        $this->dncService = $dncService;
        $this->campaignService = $campaignService;
        $this->logger = $logger;
        $this->users = new Users();
        $this->tasks = new Tasks();
        $this->managers = new Managers();
        $this->orders = new Orders();
        $this->comments = new Comments();
        $this->soap = new Soap1c();
    }

    /**
     * Обновление статуса задачи
     * 
     * @param int $taskId ID задачи
     * @param int $status Новый статус
     * @return bool Результат операции
     */
    public function updateTaskStatus(int $taskId, int $status): bool
    {
        $startTime = microtime(true);
        $method = 'updateTaskStatus';

        $context = [
            'task_id' => $taskId,
            'status' => $status,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'task_id' => $taskId,
                'status' => $status,
            ], $context);

            $this->tasks->update_pr_task($taskId, ['status' => $status]);

            // Если статус = 4 (отказ), добавляем номер в DNC
            if ($status == 4) {
                $taskData = $this->users->get_users_ccprolongations([
                    'task_id' => $taskId,
                    'date' => date('Y-m-d')
                ]);

                if (!empty($taskData) && isset($taskData[0]->manager_id) && isset($taskData[0]->phone)) {
                    $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                    $this->dncService->addToDnc($company, ["'" . $taskData[0]->phone . "'"]);
                }
            }

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('cc_task', $method, [
                'task_id' => $taskId,
                'status' => $status,
            ], $duration, $context);

            return true;

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return false;
        }
    }

    /**
     * Добавление перспективы к задаче
     * 
     * @param int $taskId ID задачи
     * @param string $perspectiveDate Дата перспективы
     * @param string|null $commentText Текст комментария
     * @param int $managerId ID менеджера
     * @return bool Результат операции
     */
    public function addPerspective(int $taskId, string $perspectiveDate, ?string $commentText, int $managerId): bool
    {
        $startTime = microtime(true);
        $method = 'addPerspective';

        $context = [
            'task_id' => $taskId,
            'perspective_date' => $perspectiveDate,
            'manager_id' => $managerId,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'task_id' => $taskId,
                'perspective_date' => $perspectiveDate,
            ], $context);

            $status = 3;
            $this->tasks->update_pr_task($taskId, [
                'status' => $status,
                'perspective_date' => $perspectiveDate
            ]);

            // Добавляем номер в DNC
            $taskData = $this->users->get_users_ccprolongations([
                'task_id' => $taskId,
                'date' => date('Y-m-d')
            ]);

            if (!empty($taskData) && isset($taskData[0]->manager_id) && isset($taskData[0]->phone)) {
                $company = $this->managers->getCompany((int)$taskData[0]->manager_id);
                $this->dncService->addToDnc($company, ["'" . $taskData[0]->phone . "'"]);
            }

            // Добавляем комментарий если указан
            if ($commentText) {
                $task = $this->tasks->get_pr_task($taskId);
                if ($task) {
                    $balance = $this->users->get_user_balance($task->user_id);
                    $order_id = null;
                    $order_id_1c = null;

                    if (!empty($balance->zayavka) && ($order_id = $this->orders->get_order_1cid($balance->zayavka))) {
                        $order_id_1c = $balance->zayavka;
                    } elseif ($order = $this->orders->get_user_last_order($task->user_id)) {
                        $order = (array)$order;
                        $order_id_1c = $order['1c_id'];
                        $order_id = $order['id'];
                    }

                    $comment = [
                        'manager_id' => $managerId,
                        'user_id' => $task->user_id,
                        'order_id' => $order_id,
                        'block' => 'cc_prolongation',
                        'text' => $commentText,
                        'created' => date('Y-m-d H:i:s'),
                    ];

                    if ($comment_id = $this->comments->add_comment($comment)) {
                        $this->soap->send_comment([
                            'manager' => $this->managers->get_manager($managerId)->name_1c ?? '',
                            'text' => $commentText,
                            'created' => date('Y-m-d H:i:s'),
                            'number' => $order_id_1c
                        ]);
                    }
                }
            }

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('cc_task', $method, [
                'task_id' => $taskId,
                'perspective_date' => $perspectiveDate,
            ], $duration, $context);

            return true;

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return false;
        }
    }

    /**
     * Добавление перезвона к задаче
     * 
     * @param int $taskId ID задачи
     * @param string|null $recallDate Дата перезвона (null если не звонить)
     * @return array Результат операции с флагом exists если номер уже в DNC
     */
    public function addRecall(int $taskId, ?string $recallDate): array
    {
        $startTime = microtime(true);
        $method = 'addRecall';

        $context = [
            'task_id' => $taskId,
            'recall_date' => $recallDate,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'task_id' => $taskId,
                'recall_date' => $recallDate,
            ], $context);

            $status = 1;
            $this->tasks->update_pr_task($taskId, [
                'status' => $status,
                'recall_date' => $recallDate,
            ]);

            $taskData = $this->users->get_users_ccprolongations([
                'task_id' => $taskId,
                'date' => date('Y-m-d')
            ]);

            if (empty($taskData) || !isset($taskData[0]->manager_id) || !isset($taskData[0]->phone)) {
                return ['success' => false, 'error' => 'Task data not found'];
            }

            $managerId = (int)$taskData[0]->manager_id;
            $phone = $taskData[0]->phone;

            // Удаляем из DNC
            $this->dncService->removeFromDnc($managerId, $phone);

            // Проверяем, есть ли номер в DNC для перезвона
            $dnc = $this->dncService->getDncNumbers('ongoing', 'checkRecall', $managerId);

            if (in_array($phone, $dnc)) {
                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess('cc_task', $method, [
                    'task_id' => $taskId,
                    'exists_in_dnc' => true,
                ], $duration, $context);

                return ['exists' => true];
            }

            // Обновляем задачу еще раз (на случай если нужно)
            $this->tasks->update_pr_task($taskId, [
                'status' => $status,
                'recall_date' => $recallDate,
            ]);

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('cc_task', $method, [
                'task_id' => $taskId,
                'recall_date' => $recallDate,
            ], $duration, $context);

            return ['success' => true];

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Форматирование задач (удаление дубликатов по номеру займа)
     * 
     * @param array $tasks Массив задач
     * @return array Отформатированные задачи
     */
    public function formatTasks(array $tasks): array
    {
        $numbers = [];

        foreach ($tasks as $key => $task) {
            if (!isset($numbers[$task->zaim_number])) {
                $numbers[$task->zaim_number] = [];
            }
            $numbers[$task->zaim_number][$key] = $task;
        }

        // Оставляем только номера с несколькими задачами
        $numbers = array_filter($numbers, function($var) {
            return count($var) > 1;
        });

        // Для каждого номера оставляем задачу с максимальным user_id
        foreach ($numbers as $number => $numberTasks) {
            $user_id = null;
            $k = null;
            
            foreach ($numberTasks as $numberTaskKey => $numberTask) {
                if (empty($user_id)) {
                    $user_id = $numberTask->user_id;
                    $k = $numberTaskKey;
                } elseif ($numberTask->user_id > $user_id) {
                    $user_id = $numberTask->user_id;
                    unset($tasks[$k]);
                    $k = $numberTaskKey;
                } else {
                    unset($tasks[$numberTaskKey]);
                }
            }
        }

        return $tasks;
    }

    /**
     * Получение timezone для пользователя
     * 
     * @param object $user Объект пользователя
     * @param bool $useFaktregion Использовать Faktregion вместо Regregion
     * @return int Timezone
     */
    public function getUserTimezone(object $user, bool $useFaktregion = true): int
    {
        // Специальный случай для пользователя 170906
        if ($user->id == 170906) {
            return 4;
        }

        $region = $useFaktregion ? ($user->Faktregion ?? null) : ($user->Regregion ?? null);
        return $this->users->get_timezone($region);
    }

    /**
     * Сравнение timezone для сортировки (аналог compareTimezone из Voximplant)
     * 
     * @param object $a Первый объект с UTC
     * @param object $b Второй объект с UTC
     * @return int Результат сравнения
     */
    public function compareTimezone(object $a, object $b): int
    {
        $timezonePattern = "/^(\+|-)\d{2}:\d{2}$/";

        if (!preg_match($timezonePattern, $a->UTC ?? '') || !preg_match($timezonePattern, $b->UTC ?? '')) {
            return 0;
        }

        // +12:00 всегда первый
        if (($a->UTC ?? '') === "+12:00" && ($b->UTC ?? '') !== "+12:00") {
            return -1;
        } elseif (($a->UTC ?? '') !== "+12:00" && ($b->UTC ?? '') === "+12:00") {
            return 1;
        } else {
            // Сортировка по убыванию
            return strcmp($b->UTC ?? '', $a->UTC ?? '');
        }
    }

    /**
     * Сортировка менеджеров по имени (с учетом дня месяца)
     * 
     * @param array $managers Массив менеджеров
     * @return array Отсортированные менеджеры
     */
    public function sortManagers(array $managers): array
    {
        $day = (int)date('d');
        $isEven = ($day % 2 == 0);

        usort($managers, function($a, $b) use ($isEven) {
            if ($isEven) {
                return strcmp($a->name ?? '', $b->name ?? '');
            } else {
                return strcmp($b->name ?? '', $a->name ?? '');
            }
        });

        return $managers;
    }

    /**
     * Распределение задач между менеджерами
     * 
     * @param array $tasks Массив задач
     * @param array $managers Массив ID менеджеров
     * @param string $taskDate Дата задачи
     * @param string $period Период
     * @return int Количество созданных задач
     */
    public function distributeTasks(array $tasks, array $managers, string $taskDate, string $period): int
    {
        $startTime = microtime(true);
        $method = 'distributeTasks';

        $context = [
            'tasks_count' => count($tasks),
            'managers_count' => count($managers),
            'task_date' => $taskDate,
            'period' => $period,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'tasks_count' => count($tasks),
                'managers_count' => count($managers),
            ], $context);

            $formattedTasks = $this->formatTasks($tasks);
            $sortedManagers = $this->sortManagers($managers);

            $i = 0;
            $max_i = count($sortedManagers);
            $createdCount = 0;

            foreach ($formattedTasks as $t) {
                $user = $this->users->get_user((int)$t->user_id);
                $timezone = $this->getUserTimezone($user, true);

                $existingTask = $this->tasks->existingTask($t->zaim_number, false, $taskDate);
                if (empty($existingTask)) {
                    $managerId = is_object($sortedManagers[$i]) ? $sortedManagers[$i]->id : $sortedManagers[$i];
                    $this->tasks->add_pr_task([
                        'number' => $t->zaim_number,
                        'user_id' => $t->user_id,
                        'task_date' => $taskDate,
                        'user_balance_id' => $t->id,
                        'manager_id' => $managerId,
                        'close' => 0,
                        'prolongation' => 0,
                        'created' => date('Y-m-d H:i:s'),
                        'od_start' => $t->loan_type == 'IL' 
                            ? $t->overdue_debt_od_IL + $t->next_payment_od 
                            : $t->ostatok_od,
                        'percents_start' => $t->loan_type == 'IL' 
                            ? $t->overdue_debt_percent_IL + $t->next_payment_percent 
                            : $t->ostatok_percents,
                        'period' => $period,
                        'status' => 0,
                        'timezone' => $timezone,
                    ]);

                    $createdCount++;
                    $i++;
                    if ($i == $max_i) {
                        $i = 0;
                    }
                }
            }

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('cc_task', $method, [
                'created_count' => $createdCount,
            ], $duration, $context);

            return $createdCount;

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return 0;
        }
    }

    /**
     * Распределение задач на текущего менеджера
     * 
     * Если задач еще нет - создает новые задачи для текущего менеджера.
     * Если задачи уже есть - перераспределяет часть задач от других менеджеров.
     * 
     * @param string $period Период задач
     * @param int $currentManagerId ID текущего менеджера
     * @param int|null $organizationId ID организации
     * @param array $filter Фильтр для получения задач
     * @return array Результат операции (success, error)
     */
    public function distributeTasksToMe(
        string $period,
        int $currentManagerId,
        ?int $organizationId = null,
        array $filter = []
    ): array {
        $startTime = microtime(true);
        $method = 'distributeTasksToMe';

        $context = [
            'period' => $period,
            'current_manager_id' => $currentManagerId,
            'organization_id' => $organizationId,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'period' => $period,
                'current_manager_id' => $currentManagerId,
            ], $context);

            $taskDate = date('Y-m-d');
            $params = [
                'period' => $period,
                'task_date' => $taskDate,
                'organization_id' => $organizationId,
            ];

            $prTasks = $this->tasks->get_pr_tasks($params)["data"];

            if (empty($prTasks)) {
                // Создаем новые задачи для текущего менеджера
                $tasks = $this->users->get_cctasks($filter);
                $tasks = $this->formatTasks($tasks);

                $createdCount = 0;
                foreach ($tasks as $t) {
                    $user = $this->users->get_user((int)$t->user_id);
                    // Используем Regregion для distribute_me (как в оригинале)
                    $timezone = $this->getUserTimezone($user, false);

                    $this->tasks->add_pr_task([
                        'number' => $t->zaim_number,
                        'user_id' => $t->user_id,
                        'task_date' => $taskDate,
                        'user_balance_id' => $t->id,
                        'manager_id' => $currentManagerId,
                        'close' => 0,
                        'prolongation' => 0,
                        'created' => date('Y-m-d H:i:s'),
                        'od_start' => $t->ostatok_od,
                        'percents_start' => $t->ostatok_percents,
                        'period' => $period,
                        'status' => 0,
                        'timezone' => $timezone,
                    ]);
                    $createdCount++;
                }

                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess('cc_task', $method, [
                    'created_count' => $createdCount,
                    'action' => 'created_new_tasks',
                ], $duration, $context);

                return ['success' => true, 'created_count' => $createdCount];
            } else {
                // Перераспределяем задачи от других менеджеров
                $managersPrTasks = [];
                foreach ($prTasks as $prt) {
                    if (!isset($managersPrTasks[$prt->manager_id])) {
                        $managersPrTasks[$prt->manager_id] = [];
                    }

                    if (empty($prt->status) && empty($prt->close) && empty($prt->prolongation)) {
                        $managersPrTasks[$prt->manager_id][] = $prt;
                    }
                }

                $coef = 1 / (count($managersPrTasks) + 1);
                $redistribute = [];

                foreach ($managersPrTasks as $mpt) {
                    shuffle($mpt);
                    $sliceCount = intval(count($mpt) * $coef);
                    $redistribute = array_merge($redistribute, array_slice($mpt, 0, $sliceCount));
                }

                $redistributedCount = 0;
                foreach ($redistribute as $rt) {
                    $this->tasks->update_pr_task($rt->id, ['manager_id' => $currentManagerId]);
                    $redistributedCount++;
                }

                $duration = microtime(true) - $startTime;
                $this->logger->logSuccess('cc_task', $method, [
                    'redistributed_count' => $redistributedCount,
                    'action' => 'redistributed_from_others',
                ], $duration, $context);

                return ['success' => true, 'redistributed_count' => $redistributedCount];
            }

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    /**
     * Отправка задач в Voximplant для менеджеров
     *
     * @param array $managerIds Массив ID менеджеров
     * @param int|null $organizationId ID организации
     * @param string $taskDate Дата задачи
     * @return array Результаты отправки
     */
    public function sendTasksToVoximplant(array $managerIds, ?int $organizationId, string $taskDate): array
    {
        $startTime = microtime(true);
        $method = 'sendTasksToVoximplant';

        $context = [
            'managers_count' => count($managerIds),
            'organization_id' => $organizationId,
            'task_date' => $taskDate,
        ];

        try {
            $this->logger->logRequest('cc_task', $method, [
                'managers_count' => count($managerIds),
            ], $context);

            $results = [];

            foreach ($managerIds as $managerId) {
                $managerData = $this->managers->get_manager($managerId);
                if (!$managerData) {
                    continue;
                }

                // Используем старый метод Voximplant для обратной совместимости
                // В будущем можно заменить на прямой вызов campaignService
                $voximplant = new \Voximplant();
                $voximplant->sendCcprolongations(
                    $managerId,
                    false,
                    $managerData->role,
                    false,
                    $organizationId,
                    $taskDate
                );

                $results[$managerId] = ['success' => true];
            }

            $duration = microtime(true) - $startTime;
            $this->logger->logSuccess('cc_task', $method, [
                'sent_count' => count($results),
            ], $duration, $context);

            return $results;

        } catch (\Throwable $e) {
            $this->logger->logError('cc_task', $method, $e, $context);
            return [];
        }
    }
}

