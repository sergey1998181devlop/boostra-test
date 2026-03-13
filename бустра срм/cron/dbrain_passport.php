<?php
/**
 * @author Jewish Programmer
 */

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';
require_once dirname(__FILE__).'/../scorings/Dbrain_passport.php';

class DbrainPassportCron extends Simpla
{
    /** @var int Количество скорингов для обработки за раз */
    const SCORINGS_LIMIT = 50;

    private $dbrainScoring;

    public function __construct()
    {
        parent::__construct();

        $this->dbrainScoring = new Dbrain_passport();

        $this->run();
    }

    /**
     * Обрабатываем АксиЛИНК и АксиНБКИ скоринги
     * @return void
     */
    public function run()
    {
        // Получение ответов на запросы
        $scorings = $this->scorings->getWaitScorings($this->scorings::TYPE_DBRAIN_PASSPORT, self::SCORINGS_LIMIT, 'ASC', '1 HOUR');
        $this->setInProcess($scorings);
        foreach ($scorings as $scoring) {
            $this->dbrainScoring->run_scoring($scoring);
        }
        $this->setWaiting($scorings);

        // Новые запросы
        $scorings = $this->scorings->getNewScorings($this->scorings::TYPE_DBRAIN_PASSPORT, self::SCORINGS_LIMIT, 'ASC', '2 HOUR');
        $this->setInProcess($scorings);
        foreach ($scorings as $scoring) {
            $this->dbrainScoring->run_scoring($scoring);
        }
        $this->setWaiting($scorings);
    }

    /**
     * Устанавливает скорингам статус 2 (В процессе обработки)
     * @param array $scorings
     * @return void
     */
    private function setInProcess(array $scorings)
    {
        $ids = [];
        foreach ($scorings as $scoring) {
            if ($scoring->status == $this->scorings::STATUS_WAIT || $scoring->status == $this->scorings::STATUS_NEW) {
                $ids[] = $scoring->id;
            }
        }

        if (empty($ids))
            return;

        $this->db->query('UPDATE __scorings SET status = ? WHERE id IN (?@)', $this->scorings::STATUS_PROCESS, $ids);
    }

    /**
     * Переносит скоринги **без решения** на следующий запуск крона. Им будет возвращён статус 7 (В ожидании обработки)
     * @param array $scorings
     * @return void
     */
    private function setWaiting(array $scorings)
    {
        $ids = [];
        foreach ($scorings as $old_scoring) {
            // Получаем актуальный скоринг из бд
            $scoring = $this->scorings->get_scoring($old_scoring->id);
            // Если скоринг всё ещё без решения - оставим его на следующий запуск крона
            if ($scoring->status == $this->scorings::STATUS_PROCESS) {
                $ids[] = $scoring->id;
            }
        }

        if (empty($ids))
            return;

        $this->db->query('UPDATE __scorings SET status = ? WHERE id IN (?@)', $this->scorings::STATUS_WAIT, $ids);
    }
}

new DbrainPassportCron();
