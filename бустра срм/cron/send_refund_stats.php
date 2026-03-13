<?php

ini_set('error_reporting', 0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', 0);

chdir(dirname(__FILE__));

date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';

use App\Handlers\SendRefundStatsHandler;

class RefundStatsCron
{
    private SendRefundStatsHandler $handler;
    private ?string $testDate;

    public function __construct(?string $testDate = null)
    {
        $this->handler = new SendRefundStatsHandler();
        $this->testDate = $testDate;
    }

    /**
     * Метод для отправки статистики возвратов в ТГ
     * @return void
     */
    public function run(): void
    {
        try {
            if ($this->testDate) {
                $this->handler->handleTest($this->testDate);
            } else {
                $this->handler->handle();
            }
        } catch (\Exception $e) {
            echo "Ошибка при выполнении.";
            error_log("Ошибка отправки статистики возвратов: " . $e->getMessage());
        }
    }
}

$options = getopt('', ['date:']);
$testDate = $options['date'] ?? null;

$cron = new RefundStatsCron($testDate);
$cron->run();