<?php

error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Скрипт пакетной отправки email-адресов пользователей в 1С.
 */
class CronSyncUserEmails extends Simpla
{
    private const DEFAULT_BATCH_SIZE = 250;
    private const DEFAULT_EMAILS_PER_CALL = 200;
    private const DEFAULT_SLEEP_MICROSECONDS = 2_000_000;
    private const STOP_HOUR = 8;

    private ?int $limit = null;
    private int $batchSize = self::DEFAULT_BATCH_SIZE;
    private int $emailsPerCall = self::DEFAULT_EMAILS_PER_CALL;
    private int $sleepMicroseconds = self::DEFAULT_SLEEP_MICROSECONDS;
    private bool $dryRun = false;
    private ?int $maxRuntimeMinutes = null;
    private int $startTime = 0;
    private string $lockFile = '';

    public function configure(array $options): void
    {
        if (isset($options['limit'])) {
            $limit = (int)$options['limit'];
            $this->limit = $limit > 0 ? $limit : null;
        }

        if (isset($options['batch-size'])) {
            $batchSize = (int)$options['batch-size'];
            if ($batchSize > 0) {
                $this->batchSize = $batchSize;
            }
        }

        if (isset($options['per-call'])) {
            $perCall = (int)$options['per-call'];
            if ($perCall > 0) {
                $this->emailsPerCall = $perCall;
            }
        }

        if (isset($options['sleep-us'])) {
            $sleep = (int)$options['sleep-us'];
            if ($sleep >= 0) {
                $this->sleepMicroseconds = $sleep;
            }
        }

        if (isset($options['dry-run'])) {
            $this->dryRun = true;
        }

        if (isset($options['no-limit'])) {
            $this->limit = null;
        }

        if (isset($options['max-runtime'])) {
            $runtime = (int)$options['max-runtime'];
            if ($runtime > 0) {
                $this->maxRuntimeMinutes = $runtime;
            } else {
                $this->maxRuntimeMinutes = null;
            }
        }
    }

    public function run(): void
    {
        $this->lockFile = sys_get_temp_dir() . '/sync_user_emails_to_1c.lock';
        
        if ($this->isLocked()) {
            $this->logInfo('SYNC_SKIPPED_ALREADY_RUNNING', [
                'lock_file' => $this->lockFile,
            ]);
            exit(0);
        }
        
        $this->createLock();

        register_shutdown_function(function() {
            $this->removeLock();
        });
        
        try {
            $this->startTime = time();
            $processedEmails = 0;
            $lastProcessedId = 0;

        while (true) {
            if ($this->shouldStopByTime()) {
                $this->logInfo('SYNC_STOPPED_BY_TIME', [
                    'processed_emails' => $processedEmails,
                    'runtime_minutes' => round((time() - $this->startTime) / 60, 2),
                ]);
                break;
            }

            if ($this->shouldStopByHour()) {
                $this->logInfo('SYNC_STOPPED_BY_HOUR', [
                    'processed_emails' => $processedEmails,
                    'current_hour' => (int)date('H'),
                ]);
                break;
            }

            $remaining = $this->limit !== null ? $this->limit - $processedEmails : null;
            if ($remaining !== null && $remaining <= 0) {
                break;
            }

            $rowsLimit = $remaining !== null ? min($this->batchSize, $remaining) : $this->batchSize;
            $rows = $this->fetchUnsyncedEmails($lastProcessedId, $rowsLimit);

            if (empty($rows)) {
                break;
            }

            $lastProcessedId = (int)end($rows)->id;
            $grouped = $this->groupEmailsByUid($rows);

            foreach ($grouped as $uid => $items) {
                $chunks = array_chunk($items, $this->emailsPerCall);

                foreach ($chunks as $chunk) {
                    if ($this->shouldStopByTime() || $this->shouldStopByHour()) {
                        break 3;
                    }

                    $validChunk = [];
                    foreach ($chunk as $item) {
                        $email = trim($item['email'] ?? '');
                        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $validChunk[] = $item;
                        }
                    }

                    if (empty($validChunk)) {
                        continue;
                    }

                    $emails = array_column($validChunk, 'email');
                    $ids = array_column($validChunk, 'id');

                    $processedEmails += count($validChunk);

                    $this->logInfo('SYNC_ATTEMPT', [
                        'uid' => $uid,
                        'emails' => $emails,
                        'ids' => $ids,
                        'dry_run' => $this->dryRun,
                    ]);

                    if (!$this->dryRun) {
                        try {
                            $rawResponse = $this->soap->sendAdditionalEmail($uid, $emails);
                            $payload = $rawResponse['response'] ?? $rawResponse;

                            $this->logInfo('SYNC_RESPONSE', ['uid' => $uid, 'emails' => $emails, 'response' => $rawResponse]);

                            if (!is_array($payload)) {
                                $this->logError('SYNC_INVALID_RESPONSE', [
                                    'uid' => $uid,
                                    'emails' => $emails,
                                    'ids' => $ids,
                                    'response' => $rawResponse,
                                ]);
                                continue;
                            }

                            if (empty($payload['КонтрагентНайден'])) {
                                $this->logError('SYNC_CONTRAGENT_NOT_FOUND', [
                                    'uid' => $uid,
                                    'emails' => $emails,
                                    'ids' => $ids,
                                    'response' => $payload,
                                ]);
                                continue;
                            }

                            $results = $this->mapResultsByEmail($payload);
                            $successIds = [];
                            $successEmails = [];

                            foreach ($validChunk as $item) {
                                $email = trim($item['email']);
                                $id = $item['id'];

                                $result = $results[mb_strtolower($email)] ?? null;

                                if ($result === null) {
                                    $this->logInfo('SYNC_EMAIL_RESULT_MISSING', [
                                        'uid' => $uid,
                                        'email' => $email,
                                        'id' => $id,
                                        'response' => $payload,
                                    ]);
                                    $successIds[] = $id;
                                    continue;
                                }

                                if (!empty($result['Ошибки'])) {
                                    $this->logError('SYNC_EMAIL_ERRORS', [
                                        'uid' => $uid,
                                        'email' => $email,
                                        'id' => $id,
                                        'errors' => $result['Ошибки'],
                                        'response' => $payload,
                                    ]);
                                    continue;
                                }

                                $successIds[] = $id;
                                $successEmails[] = $email;
                            }

                            if (!empty($successIds)) {
                                $this->markEmailsSynced($successIds);
                                $this->logInfo('SYNC_SUCCESS', ['uid' => $uid, 'emails' => $successEmails, 'ids' => $successIds]);
                            }
                        } catch (\Throwable $e) {
                            $this->logError('SYNC_FAILED', [
                                'uid' => $uid,
                                'emails' => $emails,
                                'ids' => $ids,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    if ($this->limit !== null && $processedEmails >= $this->limit) {
                        break 3;
                    }

                    if ($this->sleepMicroseconds > 0) {
                        usleep($this->sleepMicroseconds);
                    }
                }
            }
        }

            $this->logInfo('SYNC_FINISHED', [
                'processed_emails' => $processedEmails,
                'limit' => $this->limit,
                'runtime_minutes' => round((time() - $this->startTime) / 60, 2),
            ]);
        } finally {
            $this->removeLock();
        }
    }

    /**
     * Проверяет, нужно ли остановить выполнение по времени выполнения
     */
    private function shouldStopByTime(): bool
    {
        if ($this->maxRuntimeMinutes === null) {
            return false;
        }

        $elapsedMinutes = (time() - $this->startTime) / 60;
        return $elapsedMinutes >= $this->maxRuntimeMinutes;
    }

    /**
     * Проверяет, нужно ли остановить выполнение по времени суток (8:00)
     */
    private function shouldStopByHour(): bool
    {
        $currentHour = (int)date('H');
        return $currentHour >= self::STOP_HOUR;
    }

    /**
     * @param int $lastId
     * @param int $limit
     *
     * @return array<int, object>
     */
    private function fetchUnsyncedEmails(int $lastId, int $limit): array
    {
        $query = $this->db->placehold(
            "
            SELECT ue.id, ue.user_id, ue.email, u.uid
            FROM __user_emails AS ue
            INNER JOIN __users AS u ON u.id = ue.user_id
            WHERE ue.is_active = 1
              AND TRIM(ue.email) <> ''
              AND LENGTH(TRIM(ue.email)) > 0
              AND ue.synced_at IS NULL
              AND ue.source IN (?, ?, ?, ?)
              AND ue.id > ?
            ORDER BY ue.id ASC
            LIMIT ?
        ",
            UserEmails::SOURCE_COMPLAINT_EMAIL,
            UserEmails::SOURCE_FEEDBACK_EMAIL,
            UserEmails::SOURCE_USER_TICKET_EMAIL,
            UserEmails::SOURCE_NBKI_EMAIL,
            $lastId,
            $limit
        );

        $this->db->query($query);

        return $this->db->results() ?: [];
    }

    /**
     * @param array<int, object> $rows
     *
     * @return array<string, array<int, array{id:int,user_id:int,email:string}>>
     */
    private function groupEmailsByUid(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $uid = $row->uid;

            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [];
            }

            $grouped[$uid][] = [
                'id' => (int)$row->id,
                'user_id' => (int)$row->user_id,
                'email' => $row->email,
            ];
        }

        return $grouped;
    }

    /**
     * @param array<int, int> $ids
     *
     * @return void
     */
    private function markEmailsSynced(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = $this->db->placehold(
            "UPDATE __user_emails SET synced_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders)",
            ...$ids
        );

        $this->db->query($query);
    }

    /**
     * @param string $event
     * @param array $context
     *
     * @return void
     */
    private function logInfo(string $event, array $context = []): void
    {
        $this->logging('INFO', $event, '', $context, 'sync_user_emails.txt');
    }

    /**
     * @param string $event
     * @param array $context
     *
     * @return void
     */
    private function logError(string $event, array $context = []): void
    {
        $this->logging('ERROR', $event, '', $context, 'sync_user_emails.txt');
    }

    /**
     * Строит карту результатов обработки email-адресов из ответа 1С.
     *
     * @param array $response Ответ 1С
     *
     * @return array<string, array<string, mixed>>
     */
    private function mapResultsByEmail(array $response): array
    {
        if (empty($response['РезультатыПоEmail']) || !is_array($response['РезультатыПоEmail'])) {
            return [];
        }

        $map = [];

        foreach ($response['РезультатыПоEmail'] as $item) {
            if (!is_array($item) || empty($item['Email'])) {
                continue;
            }

            $map[mb_strtolower($item['Email'])] = $item;
        }

        return $map;
    }

    /**
     * Проверяет, запущен ли уже процесс
     */
    private function isLocked(): bool
    {
        if (!file_exists($this->lockFile)) {
            return false;
        }

        $lockTime = filemtime($this->lockFile);
        $maxLockAge = 60 * 60;
        
        if (time() - $lockTime > $maxLockAge) {
            @unlink($this->lockFile);
            return false;
        }

        $pid = @file_get_contents($this->lockFile);
        if ($pid && function_exists('posix_kill')) {
            if (!@posix_kill((int)$pid, 0)) {
                @unlink($this->lockFile);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Создает lock-файл
     */
    private function createLock(): void
    {
        $pid = getmypid();
        file_put_contents($this->lockFile, $pid);
    }

    /**
     * Удаляет lock-файл
     */
    private function removeLock(): void
    {
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }
    }
}

$options = getopt('', [
    'limit::',
    'batch-size::',
    'per-call::',
    'sleep-us::',
    'dry-run::',
    'no-limit',
    'max-runtime::'
]);

$start = microtime(true);

$cron = new CronSyncUserEmails();
$cron->configure($options);
$cron->run();

$end = microtime(true);

$timeWorked = $end - $start;
exit(
    date('c', $start)
    . ' - '
    . date('c', $end)
    . ' :: script '
    . __FILE__
    . ' work '
    . number_format($timeWorked, 4)
    . " s.\n"
);

