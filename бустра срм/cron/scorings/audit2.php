<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../../api/Simpla.php';
require_once dirname(__FILE__) . '/../../api/Scorings.php';

/**
 * Выполнение медленных скорингов
 */
class ScoringsCron2 extends Simpla
{
    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;

    /** @var int Максимальное кол-во скорингов для обработки за 1 запуск крона */
    private const MAX_SCORINGS_AMOUNT_TO_PROCESS = 150;

    private const LOG_FILE = 'audit2.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', '', 'Начало работы крона', self::LOG_FILE);

        $executionStartTime = microtime(true);

        $i = 0;
        while ($i < self::MAX_SCORINGS_AMOUNT_TO_PROCESS) {
            $sc_types = [
                //$this->scorings::TYPE_AXILINK,
                //$this->scorings::TYPE_AXILINK_2,
                $this->scorings::TYPE_FINKARTA,
                $this->scorings::TYPE_PYTON_NBKI,
                $this->scorings::TYPE_PYTON_SMP,
            ];

            $scoring = $this->scorings->get_new_scoring($sc_types);

            if (empty($scoring)) {
                break;
            }

            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', 'Достигнута максимальная продолжительность работы крона: ' .
                    date('Y-m-d H:i:s'), 'Обработано скорингов: ' . ($i + 1), self::LOG_FILE);
                break;
            }

            $this->scorings->update_scoring($scoring->id, array(
                'status' => $this->scorings::STATUS_PROCESS,
                'start_date' => date('Y-m-d H:i:s')
            ));
            $scoringType = $this->scorings->get_type((int)$scoring->type);
            $classname = $scoringType->name;
            if ($scoring->type == $this->scorings::TYPE_AXILINK_2) {
                $classname = 'dbrainAxi';
            }

            if (empty($scoringType->active)) {
                $this->scorings->update_scoring($scoring->id, array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'Скоринг отключен',
                    'start_date' => date('Y-m-d H:i:s'),
                    'end_date' => date('Y-m-d H:i:s'),
                ));
                continue;
            }

            $scoring_result = $this->{$classname}->run_scoring($scoring->id);
            if (in_array((int)$scoring->type, [$this->scorings::TYPE_AXILINK, $this->scorings::TYPE_BLACKLIST])) {
                continue;
            }

            if (!empty($scoring->order_id)) {
                $this->scorings->tryAddScoristaAndAxi($scoring->order_id);
            }

            $i++;
        }

        $this->logging(__METHOD__, '', '', 'Завершение работы крона', self::LOG_FILE);
    }
}

$cron = new ScoringsCron2();
$cron->run();
