<?php

/**
 * Крон-скрипт для заполнения value_hash в таблице s_order_data
 *
 * Берёт 5000 последних записей с value_hash = NULL и вычисляет хэш.
 * Колонка "updated" при этом не обновляется.
 */

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class FillOrderDataValueHashCron extends Simpla
{
    /** Общее количество записей для обработки за один запуск */
    private const TOTAL_LIMIT = 1000;

    public function run(): void
    {
        echo date('Y-m-d H:i:s') . " Запуск заполнения value_hash для s_order_data...\n";

        $this->updateHashes();

        echo date('Y-m-d H:i:s') . " Готово.\n";
    }

    /**
     * Обновляет value_hash для записей напрямую в SQL
     * UNHEX(MD5(value)) в MySQL эквивалентен md5($value, true) в PHP
     */
    private function updateHashes(): void
    {
        $query = $this->db->placehold("
            UPDATE __order_data
            SET value_hash = UNHEX(MD5(`value`))
            WHERE value_hash IS NULL
            ORDER BY updated DESC
            LIMIT ?
        ", self::TOTAL_LIMIT);

        $this->db->query($query);
    }
}

$cron = new FillOrderDataValueHashCron();
$cron->run();
