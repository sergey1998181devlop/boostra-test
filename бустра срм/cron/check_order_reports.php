<?php

date_default_timezone_set('Europe/Moscow');

ini_set('memory_limit', '1024M');

define('ROOT', dirname(__DIR__));

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Крон для запуска скоринга проверки актуальности ССП и КИ отчетов
 */
class CheckOrderReports extends Simpla
{
    /** @var int Максимальное кол-во скорингов для обработки одним запуском крона */
    private const MAX_REPORT_SCORINGS_TO_PROCESS = 50;

    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;

    private const LOG_FILE = 'check_order_reports.txt';

    public function run()
    {
        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крон: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);

        $scorings = $this->scorings->get_scorings([
            'type' => $this->scorings::TYPE_REPORT,
            'status' => [$this->scorings::STATUS_NEW, $this->scorings::STATUS_WAIT],
            'limit' => self::MAX_REPORT_SCORINGS_TO_PROCESS
        ]);

        if (empty($scorings)) {
            $this->logging(__METHOD__, '', '', 'Скоринги не найдены. Крон завершен: ' .
                date('Y-m-d H:i:s'), self::LOG_FILE);
            return;
        }

        foreach ($scorings as $key => $scoring) {
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $message = [
                    'Обработано скорингов' => $key,
                    'Не успели обработать скорингов' => count($scorings) - $key,
                ];

                $this->logging(__METHOD__, '', 'Достигнута максимальная продолжительность работы крон: ' .
                    date('Y-m-d H:i:s'),  $message, self::LOG_FILE);
                break;
            }

            $this->report->run_scoring($scoring);
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);
    }
}

$checkOrderReports = new CheckOrderReports();
$checkOrderReports->run();
