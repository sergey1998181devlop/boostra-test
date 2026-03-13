<?php
/**
 * Миграция согласий БКИ по фактическим запросам КИ.
 *
 * Для каждого клиента с заявкой с 01.01.2026 проверяет факт запроса КИ
 * из ssp_nbki_request_log и приводит user_data[bki_consent] в соответствие.
 *
 * Особенности:
 * - File lock (LOCK_EX | LOCK_NB) — защита от наслоения при запуске по cron
 * - Персистентный курсор — каждый запуск продолжает с места остановки предыдущего
 * - Лимит батчей за запуск — укладывается в интервал cron (по умолчанию 10 батчей)
 * - Логирование в файл, без echo
 *
 * Запуск:
 *   php cron/migrate_bki_consent.php                    — dry-run
 *   php cron/migrate_bki_consent.php --execute          — реальное выполнение
 *
 * @ticket LEGAL-330
 */

error_reporting(-1);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '512M');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class MigrateBkiConsentCron extends Simpla
{
    private const BATCH_SIZE = 500;
    private const BATCHES_PER_RUN = 10;
    private const DATE_FROM = '2026-01-01';
    private const SOURCE = 'migration_legal_330';

    private const LOCK_FILE = '/tmp/migrate_bki_consent.lock';
    private const CURSOR_FILE = '/tmp/migrate_bki_consent_cursor.json';
    private const LOG_FILE = 'migrate_bki_consent.txt';

    /** @var bool */
    private $isExecute;

    /** @var resource|null */
    private $csvFp;

    /** @var array */
    private $stats = [
        'set_consent'    => 0,
        'remove_consent' => 0,
        'already_ok'     => 0,
        'errors'         => 0,
    ];

    /** @var int */
    private $processed = 0;

    public function run(array $argv): void
    {
        $lock = fopen(self::LOCK_FILE, 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            return;
        }

        try {
            $this->isExecute = in_array('--execute', $argv, true);
            $cursor = $this->loadCursor();
            $lastUserId = $cursor['last_user_id'] ?? 0;

            $mode = $this->isExecute ? 'EXECUTE' : 'DRY-RUN';
            $this->log("start mode={$mode} cursor={$lastUserId}");

            $this->openCsv();

            $batchesDone = 0;
            $finished = false;

            while ($batchesDone < self::BATCHES_PER_RUN) {
                $userIds = $this->fetchUserIdBatch($lastUserId);

                if (empty($userIds)) {
                    $finished = true;
                    break;
                }

                $lastUserId = end($userIds);
                $this->processBatch($userIds);
                $batchesDone++;
            }

            $this->saveCursor($finished ? 0 : $lastUserId, $finished);
            $this->closeCsv();

            $this->log(sprintf(
                "done batches=%d processed=%d SET=%d DEL=%d OK=%d ERR=%d cursor=%s",
                $batchesDone,
                $this->processed,
                $this->stats['set_consent'],
                $this->stats['remove_consent'],
                $this->stats['already_ok'],
                $this->stats['errors'],
                $finished ? 'FINISHED' : $lastUserId
            ));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @return int[]
     */
    private function fetchUserIdBatch(int $afterUserId): array
    {
        $query = $this->db->placehold(
            "SELECT DISTINCT user_id FROM __orders
             WHERE date >= ? AND user_id > ? AND user_id IS NOT NULL
             ORDER BY user_id LIMIT ?",
            self::DATE_FROM, $afterUserId, self::BATCH_SIZE
        );
        $this->db->query($query);

        $userIds = [];
        foreach ($this->db->results() as $row) {
            $userIds[] = (int) $row->user_id;
        }

        return $userIds;
    }

    /**
     * @param int[] $userIds
     */
    private function processBatch(array $userIds): void
    {
        $latestOrders = $this->fetchLatestOrders($userIds);
        $nbkiByUser = [];
        $nbkiOrderByUser = [];
        $this->fetchNbkiLogs($userIds, $nbkiByUser, $nbkiOrderByUser);
        $currentConsent = $this->fetchCurrentConsent($userIds);
        $hasHistoricNbki = $this->fetchHistoricNbki($userIds, $nbkiByUser);

        foreach ($userIds as $userId) {
            $this->processed++;
            $latestOrderId = $latestOrders[$userId] ?? null;
            if ($latestOrderId === null) {
                continue;
            }

            $kiDate = $nbkiByUser[$userId] ?? null;
            $kiOrderId = $nbkiOrderByUser[$userId] ?? $latestOrderId;
            $hasKi = ($kiDate !== null);
            $oldRaw = $currentConsent[$userId] ?? null;
            $oldData = json_decode((string) $oldRaw, true);
            $oldConsent = is_array($oldData) && !empty($oldData['consent']);

            if ($hasKi) {
                if ($oldConsent && ($oldData['source'] ?? '') === self::SOURCE) {
                    $this->stats['already_ok']++;
                    continue;
                }

                $newValue = json_encode([
                    'consent'    => true,
                    'timestamp'  => $kiDate,
                    'source'     => self::SOURCE,
                    'order_id'   => $kiOrderId,
                    'ip'         => '',
                    'user_agent' => '',
                ], JSON_UNESCAPED_UNICODE);

                if ($this->isExecute) {
                    $this->user_data->set($userId, 'bki_consent', $newValue);
                }

                $this->writeCsv(['SET', $userId, $kiOrderId, $kiDate, $oldRaw ?? '', $newValue]);
                $this->stats['set_consent']++;
            } else {
                if ($oldRaw === null) {
                    $this->stats['already_ok']++;
                    continue;
                }

                if (isset($hasHistoricNbki[$userId])) {
                    $this->stats['already_ok']++;
                    continue;
                }

                if ($this->isExecute) {
                    $this->user_data->set($userId, 'bki_consent', null);
                }

                $this->writeCsv(['DEL', $userId, $latestOrderId, '', $oldRaw, '']);
                $this->stats['remove_consent']++;
            }
        }
    }

    /**
     * @param int[] $userIds
     * @return array<int, int>
     */
    private function fetchLatestOrders(array $userIds): array
    {
        $query = $this->db->placehold(
            "SELECT user_id, MAX(id) AS order_id FROM __orders
             WHERE user_id IN (?@) AND date >= ?
             GROUP BY user_id",
            $userIds, self::DATE_FROM
        );
        $this->db->query($query);

        $result = [];
        foreach ($this->db->results() as $row) {
            $result[(int) $row->user_id] = (int) $row->order_id;
        }

        return $result;
    }

    /**
     * @param int[] $userIds
     * @param array<int, string> $nbkiByUser
     * @param array<int, int> $nbkiOrderByUser
     */
    private function fetchNbkiLogs(array $userIds, array &$nbkiByUser, array &$nbkiOrderByUser): void
    {
        $query = $this->db->placehold(
            "SELECT o.user_id, l.created_at, l.order_id
             FROM ssp_nbki_request_log l
             INNER JOIN __orders o ON o.id = l.order_id
             WHERE o.user_id IN (?@) AND o.date >= ? AND l.request_type = 'NBKI'
             ORDER BY l.id DESC",
            $userIds, self::DATE_FROM
        );
        $this->db->query($query);

        foreach ($this->db->results() as $row) {
            $uid = (int) $row->user_id;
            if (!isset($nbkiByUser[$uid])) {
                $nbkiByUser[$uid] = $row->created_at;
                $nbkiOrderByUser[$uid] = (int) $row->order_id;
            }
        }
    }

    /**
     * @param int[] $userIds
     * @return array<int, string>
     */
    private function fetchCurrentConsent(array $userIds): array
    {
        $query = $this->db->placehold(
            "SELECT user_id, value FROM __user_data
             WHERE user_id IN (?@) AND `key` = 'bki_consent'",
            $userIds
        );
        $this->db->query($query);

        $result = [];
        foreach ($this->db->results() as $row) {
            $result[(int) $row->user_id] = $row->value;
        }

        return $result;
    }

    /**
     * @param int[] $userIds
     * @param array<int, string> $nbkiByUser
     * @return array<int, bool>
     */
    private function fetchHistoricNbki(array $userIds, array $nbkiByUser): array
    {
        $usersWithoutKi = array_diff($userIds, array_keys($nbkiByUser));
        if (empty($usersWithoutKi)) {
            return [];
        }

        $query = $this->db->placehold(
            "SELECT DISTINCT o.user_id
             FROM ssp_nbki_request_log l
             INNER JOIN __orders o ON o.id = l.order_id
             WHERE o.user_id IN (?@) AND l.request_type = 'NBKI'",
            array_values($usersWithoutKi)
        );
        $this->db->query($query);

        $result = [];
        foreach ($this->db->results() as $row) {
            $result[(int) $row->user_id] = true;
        }

        return $result;
    }

    private function loadCursor(): array
    {
        if (!file_exists(self::CURSOR_FILE)) {
            return ['last_user_id' => 0];
        }

        $data = json_decode(file_get_contents(self::CURSOR_FILE), true);

        return is_array($data) ? $data : ['last_user_id' => 0];
    }

    private function saveCursor(int $lastUserId, bool $finished): void
    {
        file_put_contents(self::CURSOR_FILE, json_encode([
            'last_user_id' => $lastUserId,
            'finished'     => $finished,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]));
    }

    private function openCsv(): void
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $csvFile = $logDir . '/migrate_bki_consent_' . date('Ymd_His') . '.csv';
        $this->csvFp = fopen($csvFile, 'w');
        $this->writeCsv(['action', 'user_id', 'order_id', 'ki_date', 'old_value', 'new_value']);
    }

    private function closeCsv(): void
    {
        if ($this->csvFp) {
            fclose($this->csvFp);
            $this->csvFp = null;
        }
    }

    private function writeCsv(array $fields): void
    {
        if ($this->csvFp) {
            fputcsv($this->csvFp, $fields, ',', '"', '\\');
        }
    }

    private function log(string $message): void
    {
        $this->logging('info', '', '', "[migrate_bki_consent] {$message}", self::LOG_FILE);
    }
}

$cron = new MigrateBkiConsentCron();
$cron->run($argv ?? []);
