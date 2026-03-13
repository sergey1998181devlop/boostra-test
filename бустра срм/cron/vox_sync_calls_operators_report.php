<?php

ini_set('max_execution_time', '600');
ini_set('mysql.connect_timeout', '600');
ini_set('default_socket_timeout', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__DIR__) . '/api/Simpla.php';

if (!function_exists('config')) {
    require_once dirname(__DIR__) . '/app/Core/Helpers/BaseHelper.php';
}

class VoxSyncCallsReportCron extends Simpla
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 1000;

    public function run(): array
    {
        $results = [
            'users' => ['count' => 0, 'failed_requests' => 0, 'error' => null],
            'users_to_tqm' => ['count' => 0, 'failed_requests' => 0, 'error' => null],
            'queues' => ['count' => 0, 'failed_requests' => 0, 'error' => null],
            'calls' => ['count' => 0, 'failed_requests' => 0, 'error' => null],
        ];

        // Импорт операторов
        try {
            $results['users'] = $this->importUsers();
        } catch (Throwable $e) {
            $results['users']['error'] = $e->getMessage();
            error_log("[VoxSync] importUsers failed: " . $e->getMessage());
        }

        // Экспорт операторов в TQM
        try {
            $results['users_to_tqm'] = $this->exportUsersToTqm();
        } catch (Throwable $e) {
            $results['users_to_tqm']['error'] = $e->getMessage();
            error_log("[VoxSync] exportUsersToTqm failed: " . $e->getMessage());
        }

        // Импорт очередей
        try {
            $results['queues'] = $this->importQueues();
        } catch (Throwable $e) {
            $results['queues']['error'] = $e->getMessage();
            error_log("[VoxSync] importQueues failed: " . $e->getMessage());
        }

        // Импорт звонков
        try {
            $results['calls'] = $this->importCalls();
        } catch (Throwable $e) {
            $results['calls']['error'] = $e->getMessage();
            error_log("[VoxSync] importCalls failed: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Выполняет callable с ретраями при ошибках сети/API
     * @throws Throwable
     */
    private function withRetry(callable $callback, string $operationName, int $maxRetries = null, int $delayMs = null)
    {
        $maxRetries = $maxRetries ?? self::MAX_RETRIES;
        $delayMs = $delayMs ?? self::RETRY_DELAY_MS;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $callback();
            } catch (Throwable $e) {
                $lastError = $e;
                $error = $e->getMessage();

                // Если это сетевая ошибка или ошибка cURL - ретраим
                $isNetworkError = stripos($error, 'cURL error') !== false
                    || stripos($error, 'Could not resolve host') !== false
                    || stripos($error, 'Connection timed out') !== false
                    || stripos($error, 'API request failed') !== false;

                if (!$isNetworkError || $attempt === $maxRetries) {
                    throw $e;
                }

                error_log("[VoxSync] {$operationName} attempt {$attempt}/{$maxRetries} failed: {$error}. Retrying in {$delayMs}ms...");
                usleep($delayMs * 1000);
            }
        }

        throw $lastError;
    }

    private function importUsers(): array
    {
        $page = 1;
        $perPage = 50;
        $count = 0;
        $failedRequests = 0;

        do {
            try {
                $response = $this->withRetry(
                    function () use ($page, $perPage) {
                        return $this->voximplant->searchUsers([
                            'page' => $page,
                            'per-page' => $perPage,
                            'sort' => '-id',
                        ]);
                    },
                    "importUsers page {$page}"
                );

                if (empty($response['success']) || empty($response['result']) || !is_array($response['result'])) {
                    break;
                }

                foreach ($response['result'] as $user) {
                    if (is_array($user)) {
                        $this->voxUsers->upsert($user);
                        $count++;
                    }
                }

                $pagesCount = isset($response['_meta']['pageCount']) ? (int) $response['_meta']['pageCount'] : 1;
                $page++;
            } catch (Throwable $e) {
                $failedRequests++;
                error_log("[VoxSync] Failed to fetch users page {$page}: " . $e->getMessage());
                break;
            }
        } while ($page <= $pagesCount);

        return ['count' => $count, 'failed_requests' => $failedRequests];
    }

    private function exportUsersToTqm(): array
    {
        // Получаем всех включенных операторов с подразделениями
        $this->db->query("
            SELECT u.*, d.name AS department_name
            FROM __vox_users u
            LEFT JOIN __vox_user_departments d ON d.id = u.department_id
            WHERE u.is_call_analysis = 1
            ORDER BY u.full_name
        ");
        $users = $this->db->results();

        if (empty($users)) {
            error_log("No enabled users found in __vox_users - skipping TQM sync");
            return ['count' => 0, 'failed_requests' => 0, 'error' => null];
        }

        $count = 0;
        $failedRequests = 0;

        foreach ($users as $user) {
            try {
                $this->withRetry(
                    function () use ($user) {
                        $this->exportUserToTqm($user);
                    },
                    "exportUserToTqm user {$user->vox_user_id}"
                );
                $count++;
            } catch (Throwable $e) {
                $failedRequests++;
                error_log("[VoxSync] Failed to export user {$user->vox_user_id} to TQM: " . $e->getMessage());
            }
        }

        error_log("VoxSyncCallsReportCron sync users to TQM completed: {$count} successful, {$failedRequests} errors");

        return ['count' => $count, 'failed_requests' => $failedRequests];
    }

    /**
     * @throws Exception
     */
    private function exportUserToTqm($user): void
    {
        if (empty($user->vox_user_id)) {
            throw new Exception("Empty vox_user_id");
        }

        // Парсим ФИО
        $nameParts = $this->parseFullName($user->full_name);

        // Подготавливаем данные для отправки в TQM
        $tqmData = [
            'id' => (string)$user->vox_user_id,
            'firstName' => $nameParts['firstName'],
            'lastName' => $nameParts['lastName'],
            'middleName' => $nameParts['middleName'],
            'email' => $user->email,
            'organizationalUnitId' => isset($user->department_name) ? $user->department_name : '',
            'roleId' => '2', // Агент
        ];

        // Отправляем через сервис TQM
        $service = new \App\Service\TinkoffTqmService();
        $result = $service->exportUserToTqm($tqmData);

        if (!$result) {
            throw new Exception("Failed to import user to TQM");
        }
    }

    /**
     * Парсит полное имя на части
     *
     * @param string|null $fullName Полное имя (Фамилия Имя Отчество или Фамилия И.О.)
     * @return array ['firstName', 'lastName', 'middleName']
     */
    private function parseFullName(?string $fullName): array
    {
        $result = [
            'firstName' => '',
            'lastName' => '',
            'middleName' => '',
        ];

        if (empty($fullName)) {
            return $result;
        }

        // Убираем лишние пробелы
        $fullName = trim($fullName);

        // Проверяем, есть ли инициалы (Фамилия И.О.)
        if (preg_match('/^(\S+)\s+([А-ЯЁA-Z])\.([А-ЯЁA-Z])\.?$/u', $fullName, $matches)) {
            $result['lastName'] = $matches[1];
            $result['firstName'] = $matches[2];
            $result['middleName'] = $matches[3];
            return $result;
        }

        // Проверяем формат "Фамилия И. О."
        if (preg_match('/^(\S+)\s+([А-ЯЁA-Z])\.\s+([А-ЯЁA-Z])\.$/u', $fullName, $matches)) {
            $result['lastName'] = $matches[1];
            $result['firstName'] = $matches[2];
            $result['middleName'] = $matches[3];
            return $result;
        }

        // Разбиваем по пробелам
        $parts = preg_split('/\s+/', $fullName);

        if (empty($parts)) {
            return $result;
        }

        // Первый элемент - фамилия
        $result['lastName'] = $parts[0];

        // Второй элемент - имя
        if (isset($parts[1])) {
            $result['firstName'] = $parts[1];
        }

        // Третий элемент - отчество
        if (isset($parts[2])) {
            $result['middleName'] = $parts[2];
        }

        return $result;
    }

    private function importQueues(): array
    {
        $page = 1;
        $perPage = 50;
        $count = 0;
        $failedRequests = 0;

        do {
            try {
                $response = $this->withRetry(
                    function () use ($page, $perPage) {
                        return $this->voximplant->searchQueues([
                            'page' => $page,
                            'per-page' => $perPage,
                            'sort' => '-id',
                        ]);
                    },
                    "importQueues page {$page}"
                );

                if (empty($response['success']) || empty($response['result']) || !is_array($response['result'])) {
                    break;
                }

                foreach ($response['result'] as $queue) {
                    if (is_array($queue)) {
                        $this->voxQueues->upsert($queue);
                        $count++;
                    }
                }

                $pagesCount = isset($response['_meta']['pageCount']) ? (int) $response['_meta']['pageCount'] : 1;
                $page++;
            } catch (Throwable $e) {
                $failedRequests++;
                error_log("[VoxSync] Failed to fetch queues page {$page}: " . $e->getMessage());
                break;
            }
        } while ($page <= $pagesCount);

        return ['count' => $count, 'failed_requests' => $failedRequests];
    }

    private function importCalls(): array
    {
        $enabledQueueIds = $this->voxQueues->getEnabledQueueIds();
        if (empty($enabledQueueIds)) {
            error_log("No enabled queues found - skipping call sync");
            return ['count' => 0, 'failed_requests' => 0];
        }

        $from = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $to = date('Y-m-d H:i:s');

        $page = 1;
        $perPage = 50;
        $count = 0;
        $failedRequests = 0;

        do {
            try {
                $response = $this->withRetry(
                    function () use ($from, $to, $page, $perPage, $enabledQueueIds) {
                        return $this->voximplant->searchCallsPaginated($from, $to, $page, $perPage, [
                            'with_tags' => true,
                            'with_comments' => false,
                            'queue_ids' => json_encode($enabledQueueIds),
                            'sort' => 'id',
                        ]);
                    },
                    "importCalls page {$page}"
                );

                if (empty($response['success']) || empty($response['result']) || !is_array($response['result'])) {
                    break;
                }

                foreach ($response['result'] as $call) {
                    if (is_array($call)) {
                        if (empty($call['user_id'])) {
                            continue;
                        }

                        if (!$this->voxCalls->existsByVoxCallId($call['id'])) {
                            $this->voxCalls->save($this->mapVoxCallToDto($call));
                        } else {
                            $this->voxCalls->updateReportMeta($call);
                        }
                        $count++;
                    }
                }

                // Логируем обработку страницы
                $pageCount = isset($response['_meta']['pageCount']) ? (int) $response['_meta']['pageCount'] : 1;
                $totalCount = isset($response['_meta']['totalCount']) ? (int) $response['_meta']['totalCount'] : $perPage * $pageCount;
                error_log("[VoxSync] ImportCalls page {$page}/{$pageCount}: processed {$count} calls (total {$totalCount})");

                $page++;
            } catch (Throwable $e) {
                $failedRequests++;
                error_log("[VoxSync] Failed to fetch calls page {$page}: " . $e->getMessage());
                break;
            }
        } while ($page <= $pageCount);

        return ['count' => $count, 'failed_requests' => $failedRequests];
    }

    private function mapVoxCallToDto(array $call): stdClass
    {
        $dto = new stdClass();
        $dto->id = $call['id'] ?? null;
        $dto->call_cost = $call['call_cost'] ?? null;
        $dto->call_result_code = $call['call_result_code'] ?? null;
        $dto->datetime_start = $call['datetime_start'] ?? null;
        $dto->duration = $call['duration'] ?? null;
        $dto->is_incoming = $call['is_incoming'] ?? null;
        $dto->phone_a = $call['phone_a'] ?? null;
        $dto->phone_b = $call['phone_b'] ?? null;
        $dto->scenario_id = $call['scenario_id'] ?? null;
        $dto->tags = $call['tags'] ?? null;
        $dto->user_id = $call['user_id'] ?? null;
        $dto->queue_id = $call['queue_id'] ?? null;
        $dto->record_url = $call['record_url'] ?? null;

        $callData = json_decode($call['call_data'], true);
        if (isset($callData['assessment'])) {
            $dto->assessment = $callData['assessment'];
        }

        return $dto;
    }
}

$cron = new VoxSyncCallsReportCron();

try {
    $cron->logging(__METHOD__, 'vox_sync_calls_operators_report.php', [], ['action' => 'started'], 'vox_sync_calls_report.log');

    $result = $cron->run();
    $hasErrors = !empty($result['users']['error'])
        || !empty($result['users_to_tqm']['error'])
        || !empty($result['queues']['error'])
        || !empty($result['calls']['error']);

    $logData = [
        'success' => $hasErrors ? 0 : 1,
        'synced' => [
            'users' => [
                'count' => $result['users']['count'],
                'failed_requests' => $result['users']['failed_requests'],
                'error' => $result['users']['error'],
            ],
            'users_to_tqm' => [
                'count' => $result['users_to_tqm']['count'],
                'failed_requests' => $result['users_to_tqm']['failed_requests'],
                'error' => $result['users_to_tqm']['error'],
            ],
            'queues' => [
                'count' => $result['queues']['count'],
                'failed_requests' => $result['queues']['failed_requests'],
                'error' => $result['queues']['error'],
            ],
            'calls' => [
                'count' => $result['calls']['count'],
                'failed_requests' => $result['calls']['failed_requests'],
                'error' => $result['calls']['error'],
            ],
        ],
    ];

    $cron->logging(__METHOD__, 'vox_sync_calls_operators_report.php', [], $logData, 'vox_sync_calls_report.log');
    echo json_encode($logData);
} catch (Throwable $e) {
    $cron->logging(__METHOD__, 'vox_sync_calls_operators_report.php', [], ['success' => 0, 'error' => $e->getMessage()], 'vox_sync_calls_report.log');
    echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
}
