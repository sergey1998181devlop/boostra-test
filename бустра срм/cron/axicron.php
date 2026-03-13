<?php
/**
 * @author Jewish Programmer
 */

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';

class AxiCron extends Simpla
{
    /** @var int Количество скорингов для обработки за раз */
    const SCORINGS_LIMIT = 100;

    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    /**
     * Обрабатываем АксиЛИНК и АксиНБКИ скоринги
     * @return void
     */
    public function run()
    {
        $scorings = $this->scorings->getWaitScorings($this->scorings::TYPE_AXILINK, self::SCORINGS_LIMIT, 'ASC', '1 HOUR');
        $this->setInProcess($scorings);
        foreach ($scorings as $scoring) {
            $this->handleAxiLink($scoring);
        }
        $this->setWaiting($scorings);

        $scorings = $this->scorings->getWaitScorings($this->scorings::TYPE_AXILINK_2, self::SCORINGS_LIMIT, 'ASC', '1 HOUR');
        $this->setInProcess($scorings);
        foreach ($scorings as $scoring) {
            $this->handleAxiNbki($scoring);
        }
        $this->setWaiting($scorings);
    }

    /**
     * Обработка АксиЛИНК скоринга
     * @param $scoring
     * @return void
     */
    public function handleAxiLink($scoring)
    {
        try {
            $this->axilink->getInfo($scoring);
        }
        catch (Exception $e) {
            $this->logError(__METHOD__, $e, $scoring);
        }
    }

    /**
     * Обработка АксиНБКИ скоринга
     * @param $scoring
     * @return void
     */
    public function handleAxiNbki($scoring)
    {
        try {
            $this->dbrainAxi->getInfo($scoring);
            $this->orders->priorApprove($scoring->order_id);
            $this->scorings->tryAddScoristaAndAxi($scoring->order_id);
        }
        catch (Exception $e) {
            $this->logError(__METHOD__, $e, $scoring);
        }
    }

    /**
     * @param string $method
     * @param Exception $error
     * @param object|null $scoring
     * @return void
     */
    private function logError($method, $error, $scoring = null)
    {
        $error_lines = [
            'Ошибка: ' . $error->getMessage(),
            'Файл: ' . $error->getFile(),
            'Строка: ' . $error->getLine(),
            'Подробности: ' . $error->getTraceAsString()
        ];

        $type = 'Пустой тип в $scoring';
        if (!empty($scoring->type))
            $type = $scoring->type;

        $this->logging($method, $type, $scoring, $error_lines, 'axi_cron.txt');
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
            if (!empty($scoring->id) && $scoring->status == $this->scorings::STATUS_WAIT) {
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

new AxiCron;
