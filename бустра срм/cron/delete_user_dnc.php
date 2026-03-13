<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

chdir(dirname(__FILE__));
date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/api/Simpla.php';
require APP_ROOT . '/app/Helpers/PhoneNumberHelper.php';

use App\Core\Application\Container\Container;
use App\Providers\ClientServiceProvider;
use App\Repositories\VoxSiteDncRepository;
use App\Service\VoximplantService;

/**
 * Класс для удаления истекших записей DNC из базы данных и API Voximplant
 *
 * Обрабатывает записи в таблице __user_dnc с истекшим сроком (date_end < NOW()),
 * удаляет соответствующие контакты из DNC-листа Voximplant через API,
 * и очищает записи из базы данных при успешном удалении всех контактов.
 */
class DeleteUserDnc extends Simpla
{
    private const BATCH_SIZE = 1000;
    private const MAX_REQUESTS_PER_SECOND = 20;

    private $stats;
    private $startTime;
    private $rateLimitRequests = [];

    /** @var VoxSiteDncRepository|null */
    private ?VoxSiteDncRepository $voxSiteDncRepository;

    /**
     * Конструктор класса - инициализирует статистику и время начала выполнения
     */
    public function __construct()
    {
        parent::__construct();
        $this->startTime = microtime(true);
        $this->stats = [
            'deleted_contacts' => 0,
            'failed_contacts' => 0,
            'not_found_contacts' => 0,
            'processed_records' => 0,
            'api_requests' => 0,
            'api_errors' => 0,
            'rate_limit_hits' => 0,
            'http_429_errors' => 0
        ];
        $this->voxSiteDncRepository = $this->createVoxSiteDncRepository();
    }

    private function createVoxSiteDncRepository(): ?VoxSiteDncRepository
    {
        try {
            $container = new Container();
            $provider = new ClientServiceProvider($container);
            $provider->register();
            return $container->make(VoxSiteDncRepository::class);
        } catch (\Exception $e) {
            echo "Ошибка инициализации VoxSiteDncRepository: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Основной метод запуска процесса очистки истекших DNC записей
     * 
     * Получает количество истекших записей, обрабатывает их батчами,
     * удаляет контакты через API и выводит итоговую статистику.
     */
    public function run()
    {
        $totalRecords = $this->getExpiredRecordsCount();

        if ($totalRecords === 0) {
            return;
        }

        $offset = 0;
        while ($offset < $totalRecords) {
            $records = $this->getExpiredRecords(self::BATCH_SIZE, $offset);

            if (empty($records)) break;

            foreach ($records as $record) {
                $this->processRecord($record);
                $this->stats['processed_records']++;
            }

            $offset += self::BATCH_SIZE;
        }

        $this->printSummary();
    }

    /**
     * Обрабатывает одну запись DNC
     * 
     * Получает список контактов из записи, пытается удалить каждый через API.
     * Если все контакты успешно удалены - удаляет запись из БД,
     * иначе обновляет запись, оставляя только неудаленные контакты.
     * 
     * @param object $record Запись из таблицы __user_dnc
     */
    private function processRecord($record)
    {
        $contactIds = json_decode($record->dnc_contact_ids, true);

        if (empty($contactIds)) {
            $this->deleteRecord($record->id);
            return;
        }

        $siteId = isset($record->site_id) && $record->site_id !== '' && $record->site_id !== null
            ? trim((string) $record->site_id)
            : null;

        $remainingIds = [];
        foreach ($contactIds as $contactId) {
            $deleted = false;
            if ($siteId !== null && $siteId !== '' && $this->voxSiteDncRepository !== null) {
                $deleted = $this->deleteContactBySiteId((int) $contactId, $siteId);
            }
            if (!$deleted && ($siteId === null || $siteId === '' || $this->voxSiteDncRepository === null)) {
                $deleted = $this->deleteContact($contactId);
            }
            if (!$deleted) {
                $remainingIds[] = $contactId;
            }
        }

        if (empty($remainingIds)) {
            $this->deleteRecord($record->id);
        } else {
            $this->updateRecordContactIds($record->id, $remainingIds);
        }
    }

    /**
     * Разбор ответа API удаления DNC-контакта и обновление статистики.
     *
     * @param array $result Ответ Voximplant API (deleteDncContact)
     * @return bool true если контакт удален или 404, false при ошибке
     */
    private function interpretDeleteResult(array $result): bool
    {
        if (isset($result['success']) && $result['success']) {
            $this->stats['deleted_contacts']++;
            return true;
        }

        $httpCode = $result['result']['code'] ?? 0;
        if ($httpCode == 404) {
            $this->stats['not_found_contacts']++;
            return true;
        }
        if ($httpCode == 429) {
            $this->stats['http_429_errors']++;
        }

        $this->stats['failed_contacts']++;
        $this->stats['api_errors']++;
        return false;
    }

    /**
     * Удаляет контакт из DNC по настройкам из s_vox_site_dnc для данного site_id (app).
     *
     * @param int $contactId ID контакта в DNC-листе
     * @param string $siteId site_id из записи __user_dnc
     * @return bool true если удален или 404, false при ошибке
     */
    private function deleteContactBySiteId(int $contactId, string $siteId): bool
    {
        $this->enforceRateLimit();
        $this->stats['api_requests']++;

        $row = $this->voxSiteDncRepository->findFirstActiveBySiteId($siteId);
        if ($row === null || empty($row->vox_domain) || empty($row->vox_token)) {
            $this->stats['failed_contacts']++;
            $this->stats['api_errors']++;
            return false;
        }

        try {
            $voxService = VoximplantService::fromVoxSiteDncRow($row);
            $result = $voxService->deleteDncContact($contactId);
            return $this->interpretDeleteResult($result);
        } catch (\Exception $e) {
            $this->stats['failed_contacts']++;
            $this->stats['api_errors']++;
            return false;
        }
    }

    /**
     * Удаляет контакт из DNC-листа через API Voximplant (legacy, дефолтный аккаунт)
     *
     * @param int $contactId ID контакта в DNC-листе
     * @return bool true если контакт успешно удален или не найден, false при ошибке
     */
    private function deleteContact($contactId): bool
    {
        $this->enforceRateLimit();
        $this->stats['api_requests']++;

        try {
            $result = $this->voximplant->deleteDncContact($contactId);
            return $this->interpretDeleteResult($result);
        } catch (Exception $e) {
            $this->stats['failed_contacts']++;
            $this->stats['api_errors']++;
            return false;
        }
    }

    /**
     * Обеспечивает соблюдение rate limit для API запросов
     * 
     * Отслеживает количество запросов за последнюю секунду,
     * при превышении лимита ожидает необходимое время перед следующим запросом.
     */
    private function enforceRateLimit()
    {
        $now = microtime(true);
        
        // Очистка старых запросов
        $this->rateLimitRequests = array_filter($this->rateLimitRequests, function($time) use ($now) {
            return ($now - $time) <= 1.0;
        });

        if (count($this->rateLimitRequests) >= self::MAX_REQUESTS_PER_SECOND) {
            $oldestRequest = min($this->rateLimitRequests);
            $waitTime = 1.0 - ($now - $oldestRequest);
            
            if ($waitTime > 0) {
                $this->stats['rate_limit_hits']++;
                usleep($waitTime * 1000000);
            }
        }

        $this->rateLimitRequests[] = microtime(true);
    }

    /**
     * Получает количество истекших записей DNC
     * 
     * @return int Количество записей с date_end < NOW()
     */
    private function getExpiredRecordsCount(): int
    {
        $query = "SELECT COUNT(*) as count FROM __user_dnc WHERE date_end < NOW()";
        $this->db->query($query);
        $result = $this->db->result();
        return (int)($result->count ?? 0);
    }

    /**
     * Получает батч истекших записей DNC с пагинацией
     * 
     * @param int $limit Количество записей в батче
     * @param int $offset Смещение для пагинации
     * @return array Массив записей из таблицы __user_dnc
     */
    private function getExpiredRecords(int $limit, int $offset): array
    {
        $query = $this->db->placehold(
            "SELECT * FROM __user_dnc WHERE date_end < NOW() ORDER BY date_end ASC LIMIT ? OFFSET ?",
            $limit, $offset
        );
        $this->db->query($query);
        return $this->db->results() ?? [];
    }

    /**
     * Удаляет запись DNC из базы данных
     * 
     * @param int $recordId ID записи в таблице __user_dnc
     */
    private function deleteRecord(int $recordId)
    {
        $query = "DELETE FROM __user_dnc WHERE id = ?";
        $this->db->query($query, $recordId);
    }

    /**
     * Обновляет список contact_ids в записи DNC
     * 
     * @param int $recordId ID записи в таблице __user_dnc
     * @param array $contactIds Массив ID контактов для сохранения в записи
     */
    private function updateRecordContactIds(int $recordId, array $contactIds)
    {
        $contactIdsJson = json_encode($contactIds);
        $query = "UPDATE __user_dnc SET dnc_contact_ids = ? WHERE id = ?";
        $this->db->query($query, $contactIdsJson, $recordId);
    }

    /**
     * Выводит итоговую статистику выполнения очистки DNC
     * 
     * Показывает количество обработанных записей, удаленных контактов,
     * ошибок API, срабатываний rate limit и общее время выполнения.
     */
    private function printSummary()
    {
        $totalTime = microtime(true) - $this->startTime;

        echo "DNC Cleanup Summary:\n";
        echo "- Processed: {$this->stats['processed_records']} records\n";
        echo "- Deleted: {$this->stats['deleted_contacts']} contacts\n";
        echo "- Not found: {$this->stats['not_found_contacts']} contacts\n";
        echo "- Failed: {$this->stats['failed_contacts']} contacts\n";
        echo "- API requests: {$this->stats['api_requests']} (errors: {$this->stats['api_errors']})\n";
        echo "- Rate limit hits: {$this->stats['rate_limit_hits']} (429 errors: {$this->stats['http_429_errors']})\n";
        echo "- Total time: " . round($totalTime, 2) . " seconds\n";
    }
}

$cron = new DeleteUserDnc();
$cron->run();