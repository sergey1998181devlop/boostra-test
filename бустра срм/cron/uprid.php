<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';

class UpridCron extends Simpla
{
    const NEW_SCORINGS_LIMIT = 50;

    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        // Запускаем новые скоринги, после первого запуска на подготовку ответа сервиса может уйти несколько минут
        if ($newScorings = $this->scorings->get_scorings([
            'type' => $this->scorings::TYPE_UPRID,
            'status' => $this->scorings::STATUS_NEW,
            'limit' => self::NEW_SCORINGS_LIMIT
        ])) {
            $this->runScorings($newScorings, true);
        }

        // Для старых скорингов проверяем наличие ответа.
        if ($waitScorings = $this->getOldScorings()) {
            $this->runScorings($waitScorings);
        }
    }

    /**
     * Запуск массива скорингов
     * @param $scorings
     * @param $isNew
     * @return void
     */
    private function runScorings($scorings, $isNew = false)
    {
        // Сначала отмечаем скоринги как взятые в работу, чтобы они не попали в другой крон
        foreach ($scorings as $scoring) {
            $update = ['status' => $this->scorings::STATUS_PROCESS];
            if ($isNew)
                $update['start_date'] = date('Y-m-d H:i:s');

            $this->scorings->update_scoring($scoring->id, $update);
        }

        // Потом запускаем их
        foreach ($scorings as $scoring) {
            try {
                $this->uprid->run_scoring($scoring->id);
            }
            catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }
    }

    /**
     * Получение скорингов по прогрессивной шкале ожидания ответа
     * @return array|false
     */
    private function getOldScorings()
    {
        $query = "
                SELECT sc.* FROM s_scorings sc
                LEFT JOIN s_user_data ud ON ud.user_id = sc.user_id AND ud.`key` = ?
                WHERE
                    sc.type = ?
                    AND sc.status = ?
                    AND (
                        ud.user_id IS NULL 
                        OR STR_TO_DATE(ud.`value`, '%Y-%m-%d %H:%i:%s') <= NOW()
                        )
                    LIMIT 50
                ";
        $this->db->query($query, $this->uprid::USER_DATA_PAUSE_UPRID_UNTIL, $this->scorings::TYPE_UPRID, $this->scorings::STATUS_WAIT);
        return $this->db->results();
    }
}

$cron = new UpridCron();
$cron->run();