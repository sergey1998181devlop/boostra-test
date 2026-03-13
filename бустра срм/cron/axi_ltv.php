<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';
require_once dirname(__FILE__).'/../api/Scorings.php';
require_once dirname(__FILE__).'/../scorings/DbrainAxi.php';

/**
 * Крон берёт заявки, которых нет в s_axi_ltv и ищет последний проведенный скоринг акси, потом перезапрашивает для них ltv данные
 *
 * т.е. Подгружаем LTV данные по старым заявкам
 */
class AxiLtvCron extends Simpla
{
    private const ROWS_LIMIT = 100;
    private const LOG_FILE = 'axi_ltv.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', '', 'Начало работы крон: ' . date('Y-m-d H:i:s'), self::LOG_FILE);

        $query = $this->db->placehold("SELECT o.id as order_id, sb.body
                                        FROM s_orders o
                                                 INNER JOIN s_scorings s ON s.order_id = o.id
                                                 INNER JOIN s_scoring_body sb ON sb.scoring_id = s.id
                                                 LEFT JOIN s_axi_ltv ltv on ltv.order_id = o.id
                                        WHERE
                                          # Заявки до вчера
                                            o.date <= SUBDATE(NOW(), 1)
                                        
                                          # Которых еще нет в s_axi_ltv
                                          AND ltv.order_id IS NULL
                                        
                                          # Выбираем последний проведённый скоринг акси
                                          AND s.id = (SELECT MAX(s1.id)
                                                      FROM s_scorings s1
                                                      WHERE s1.order_id = s.order_id
                                                        AND s1.`type` = 17
                                                        AND s1.`status` = 4)
                                        ORDER BY o.date DESC
                                        LIMIT ?;", self::ROWS_LIMIT);

        $this->db->query($query);
        $rows = $this->db->results();
        if (empty($rows)) {
            $this->logging(__METHOD__, '', '', 'Записи отсутствуют', self::LOG_FILE);
            return;
        }

        $this->logging(__METHOD__, '', 'Обрабатываемые заявки', array_column($rows, 'order_id'), self::LOG_FILE);

        $axi = new DbrainAxi();
        foreach ($rows as $row) {
            $body = json_decode($row->body);

            try {
                // Перезапрос ответа, сохранение ответа идёт внутри метода
                $axi->getFullData($body->appId);
            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];
                $this->logging(__METHOD__, '', $row->order_id, ['result' => false, 'error' => $error], self::LOG_FILE);
            }
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);
    }
}

$cron = new AxiLtvCron();
$cron->run();
