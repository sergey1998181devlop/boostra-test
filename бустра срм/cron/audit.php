<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';
require_once dirname(__FILE__) . '/../api/Scorings.php';

/**
 * Выполнение быстрых скорингов
 */
class ScoringsCron extends Simpla
{
    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;

    /** @var int Максимальное кол-во скорингов для обработки за 1 запуск крона */
    private const MAX_SCORINGS_AMOUNT_TO_PROCESS = 300;

    /** @var array Список скорингов, которые пропускаются при проверке на истечение срока выполнения */
    private const SKIP_OVERTIME_SCORINGS = [
        Scorings::TYPE_UPRID
    ];

    /** @var array Скоринги с увеличенным временем ожидания (5 минут) */
    private const SCORINGS_WITH_EXPANDED_TIME_OUT = [
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_CYBERITY,
    ];

    private const LOG_FILE = 'audit.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', '', 'Начало работы крона', self::LOG_FILE);

        $executionStartTime = microtime(true);

        // 2 минуты, время через которое истекает время ожидания скоринга
        $datetime = date('Y-m-d H:i:s', time() - 120);

        $overtime_scorings = $this->scorings->get_overtime_scorings($datetime);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($overtime_scorings);echo '</pre><hr />';

        if (!empty($overtime_scorings)) {
            foreach ($overtime_scorings as $overtime_scoring) {
                if ($overtime_scoring->type == $this->scorings::TYPE_SCORISTA && !empty($overtime_scoring->scorista_id)) {
                    $this->scorings->update_scoring($overtime_scoring->id, array(
                        'status' => $this->scorings::STATUS_IMPORT,
                    ));
                } else {
                    $skipScoring = false;
                    if (in_array($overtime_scoring->type, self::SCORINGS_WITH_EXPANDED_TIME_OUT)) {
                        $current_time = new DateTime();
                        $scoring_time = new DateTime($overtime_scoring->start_date);
                        $diff = $current_time->diff($scoring_time);
                        $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                        if ($minutes <= 5)
                            $skipScoring = true;
                    }

                    if (!$skipScoring && !in_array($overtime_scoring->type, self::SKIP_OVERTIME_SCORINGS)) {
                        $this->scorings->update_scoring($overtime_scoring->id, array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'Истекло время ожидания',
                            'end_date' => date('Y-m-d H:i:s'),
                        ));
                    }

                    if (!empty($overtime_scoring->order_id))
                        $this->scorings->tryAddScoristaAndAxi($overtime_scoring->order_id);
                }
            }
        }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($overtime_scorings);echo '</pre><hr />';

        $i = 0;
        while ($i < self::MAX_SCORINGS_AMOUNT_TO_PROCESS) {
            $sc_types = [
                $this->scorings::TYPE_BLACKLIST,
                $this->scorings::TYPE_LOCATION,
                $this->scorings::TYPE_AGE,
                $this->scorings::TYPE_WORK,
                $this->scorings::TYPE_LOCATION_IP
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
            if ((int)$scoring->type == $this->scorings::TYPE_AXILINK) {
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

$cron = new ScoringsCron();
$cron->run();
