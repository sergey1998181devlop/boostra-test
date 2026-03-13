<?php
ini_set('max_execution_time', '1200');
ini_set('default_socket_timeout', 5);

require_once(__DIR__ . '/../api/Simpla.php');

/**
 * API для работы с автоодобрениями
 * Class OrdersAutoApprove
 */
class OrdersAutoApprove extends Simpla
{

    /**
     * Статус нового задания в КРОН
     */
    public const STATUS_CRON_NEW = 'NEW';

    /**
     * Статус выполняется
     */
    public const STATUS_CRON_PROCESS = 'PROCESS';

    /**
     * Статус когда пройдена скориста
     */
    public const STATUS_PROGRESS = 'PROGRESS';

    /**
     * Создает задание в CRON для добавления заявок НК
     * @param $data
     * @return mixed
     */
    public function addAutoApproveNK($data)
    {
        if (empty($data['user_id']))
            return false;

        $query = $this->db->placehold("INSERT INTO s_auto_approve_nk SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Ищет активное задание
     * @param int $user_id
     * @return false|int
     */
    public function getActiveTask(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM __auto_approve_nk WHERE user_id = ? AND status IN(?@) ORDER BY id DESC LIMIT 1", $user_id, [
            self::STATUS_CRON_NEW,
            self::STATUS_CRON_PROCESS,
            self::STATUS_PROGRESS,
        ]);

        $this->db->query($query);
        return $this->db->result();
    }
}
