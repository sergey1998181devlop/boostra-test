<?php

namespace App\Core\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use InvalidArgumentException;

class LoggerFactory
{
    /**
     * Создает логгер для указанного канала
     *
     * @param string|null $channel
     * @return Logger
     */
    public static function createLogger(string $channel = null): Logger
    {
        $channel = $channel ?: config('logging.default', 'app');
        $channelConfig = config("logging.channels.{$channel}");

        if (!$channelConfig) {
            throw new InvalidArgumentException("Logging channel [{$channel}] is not defined.");
        }

        $logger = new Logger($channel);
        $handler = self::createHandler($channelConfig);
        
        if ($handler) {
            $formatter = self::createFormatter();
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        }

        return $logger;
    }

    /**
     * Создает обработчик для канала
     *
     * @param array $config
     * @return HandlerInterface|null
     */
    private static function createHandler(array $config)
    {
        $driver = $config['driver'] ?? 'single';
        $path = $config['path'] ?? APP_ROOT . '/logs/app.log';
        $level = self::parseLevel($config['level'] ?? 'info');
        $permission = $config['permission'] ?? null;

        switch ($driver) {
            case 'single':
                return new StreamHandler($path, $level, true, $permission);

            case 'daily':
                $days = $config['days'] ?? 7;
                return new RotatingFileHandler(
                    $path,
                    $days,
                    $level,
                    true,
                    $permission,
                    false
                );

            default:
                throw new InvalidArgumentException("Unsupported logging driver [{$driver}].");
        }
    }

    /**
     * Создает форматтер для логов
     *
     * @return LineFormatter
     */
    private static function createFormatter(): LineFormatter
    {
        $format = config('logging.format', '[%datetime%] %channel%.%level_name%: %message% %context% %extra%');
        $dateFormat = config('logging.date_format', 'Y-m-d H:i:s');

        return new LineFormatter($format . "\n", $dateFormat);
    }

    /**
     * Парсит уровень логирования
     *
     * @param string $level
     * @return int
     */
    private static function parseLevel(string $level): int
    {
        $levels = [
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
            'alert' => Logger::ALERT,
            'emergency' => Logger::EMERGENCY,
        ];

        $level = strtolower($level);

        if (!isset($levels[$level])) {
            throw new InvalidArgumentException("Invalid log level [{$level}].");
        }

        return $levels[$level];
    }

}
