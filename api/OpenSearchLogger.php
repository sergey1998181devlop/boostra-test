<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Для хранения логов в OpenSearch
 * При сохранении логов обязательно соблюдайте, типизацию в массивах, иначе лог может не записаться
 */
class OpenSearchLogger extends Simpla
{
    protected ?\OpenSearch\Client $client;

    private Logger $logger;

    /**
     * Стандартный уровень для служебных сообщений
     */
    public const LOG_LEVEL_INFO = 'info';

    /**
     * Уровень дебага при отладке
     */
    public const LOG_LEVEL_DEBUG = 'debug';

    /**
     * Уровень не критичной ошибки
     */
    public const LOG_LEVEL_WARNING = 'warn';

    /**
     * Уровень критической ошибки
     */
    public const LOG_LEVEL_ERROR = 'error';

    public function __construct()
    {
        parent::__construct();

        // Основной логгер для ошибок OpenSearch
        $this->logger = new Logger('open_search');
        $this->logger->pushHandler(new StreamHandler($this->config->root_dir . '/logs/open_search_logger_errors.log', Logger::INFO));

        // Simple Setup
        $this->client = \OpenSearch\ClientBuilder::create()
            ->setHosts(['https://rc1a-thd98tt469h5ebat.mdb.yandexcloud.net:9200'])
            ->setBasicAuthentication($this->config->open_search['login'], $this->config->open_search['password'])
            ->setSSLVerification($this->config->root_dir . '/files/certs/root_opensearch.crt')
            ->build();
    }

    /**
     *  Отправка данных в OpenSearch
     *
     * @param string $message сообщение для лога
     * @param array $data любые данные в виде массива, в OpenSearch можно будет их сортировать
     * @param string|null $tag в нашем случае это имя файла в основном из метода \Simpla::logging
     * @param string $service_name сервис, для чего лог, 1с, сайт, какое-либо апи стороннее
     * @param string $log_level error, debug, warn, info
     * @return void
     */
    public function create(string $message, array $data = [], string $tag = null, string $log_level = self::LOG_LEVEL_INFO, string $service_name = 'site')
    {
        // Для каждого сервиса свой индекс, например для CRM
        $index = $this->config->open_search['stack'] . '-' . $service_name . '-logs-' . date('Y.m');

        $body = [
            '@timestamp' => date('c'),
            'domain' => $this->config->main_domain,
            'message' => $message,
            'log_level' => $log_level,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'tag' => $tag,
        ];

        // Добавляем развернутые данные
        $requestData = array_merge($data, $body);

        try {
            $this->client->create(
                [
                    'index' => $index,
                    'body' => $requestData
                ]
            );
        } catch (\Exception $e) {
            // Резервное сохранение самого лога в файл
            $this->saveToBackupLog($data, $body, $index, $tag, $service_name);

//            // Логируем ошибку OpenSearch
//            $this->logger->error("OpenSearch Error", [
//                'exception' => [
//                    'message' => $e->getMessage(),
//                    'code' => $e->getCode(),
//                    'file' => $e->getFile(),
//                    'line' => $e->getLine(),
//                    'trace' => $e->getTraceAsString()
//                ],
//                'index' => $index,
//                'original_message' => $message,
//                'tag' => $tag
//            ]);
        }
    }

    /**
     * Резервное сохранение лога в файл при ошибках OpenSearch
     */
    private function saveToBackupLog(array $data, array $body, string $index, string $tag, string $service_name): void
    {
//        // Создаем логгер с динамическим именем файла
//        $filename = $this->getBackupFilename($service_name, $tag);
//        $backupLogger = new Logger('open_search_backup');
//        $backupLogger->pushHandler(new StreamHandler($filename, Logger::INFO));
//
//        // Сохраняем в JSON формате для удобства чтения
//        $backupLogger->info("BACKUP_LOG", array_merge($data, $body));

        // Отправим в openSearch, резервную копию сырыми данными (строкой)
        $requestData = array_merge(['rawData' => json_encode($data, JSON_UNESCAPED_UNICODE)], $body);

        try {
            $this->client->create(
                [
                    'index' => $index,
                    'body' => $requestData
                ]
            );
        } catch (\Exception $e) {
            // Логируем ошибку OpenSearch
//            $this->logger->error("OpenSearch send backup error", [
//                'exception' => [
//                    'message' => $e->getMessage(),
//                    'code' => $e->getCode(),
//                    'file' => $e->getFile(),
//                    'line' => $e->getLine(),
//                    'trace' => $e->getTraceAsString()
//                ],
//            ]);
        }
    }

    /**
     * Генерирует имя файла для резервного лога
     */
    private function getBackupFilename(string $service_name, string $tag): string
    {
        $baseDir = $this->config->root_dir . '/logs/opensearch/' . date('Y-m');

        // Создаем директорию если не существует
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        // Формируем имя файла
        $filename = $service_name;
        if (!empty($tag)) {
            $filename .= '_' . $tag;
        }

        // Добавляем дату для ротации
        $filename .= '_' . date('Y-m-d') . '.log';

        return $baseDir . DIRECTORY_SEPARATOR .  $filename;
    }

    /**
     * Проверяет индекс
     *
     * @param string $index
     * @return void
     */
//    private function ensureIndexExists(string $index)
//    {
//        if (!$this->client->indices()->exists(['index' => $index])) {
//            $this->client->indices()->create(
//                [
//                    'index' => $index,
//                    'body' => [
//                        'mappings' => [
//                            'dynamic' => true,
//                            'properties' => [
//                                '@timestamp' => [
//                                    'type' => 'date'
//                                ],
//                                'domain' => [
//                                    'type' => 'keyword'
//                                ],
//                                'message' => [
//                                    'type' => 'text',
//                                    'fields' => [
//                                        'keyword' => [
//                                            'type' => 'keyword'
//                                        ]
//                                    ]
//                                ],
//                                'log_level' => [
//                                    'type' => 'keyword'
//                                ],
//                                'data' => [
//                                    'type' => 'object',
//                                    'dynamic' => true
//                                ],
//                                'ip' => [
//                                    'type' => 'ip'
//                                ],
//                                'tag' => [
//                                    'type' => 'keyword'
//                                ]
//                            ]
//                        ],
//                        'settings' => [
//                            'number_of_shards' => 1,
//                            'number_of_replicas' => 1,
//                            'refresh_interval' => '1s'
//                        ]
//                    ]
//                ]
//            );
//        }
//    }
}
