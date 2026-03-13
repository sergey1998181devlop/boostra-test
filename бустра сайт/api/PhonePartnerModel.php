<?php

require_once 'Simpla.php';

/**
 * Class для работы с телефонами которые нам присылают по апи
 */
class PhonePartnerModel extends Simpla
{
    /**
     * Телефона в базе нет это новый клиент для нас
     */
    public const CLIENT_TYPE_NEW = 'new';

    /**
     * Телефон есть в базе данных, старый клиент
     */
    public const CLIENT_TYPE_OLD = 'old';

    /**
     * Статус записи, требует обработки cron
     */
    public const CRON_STATUS_NEW = 'new';

    /**
     * Статус записи, обработка cron успешно завершена
     */
    public const CRON_STATUS_SUCCESS = 'success';

    /**
     * Статус записи, обработка cron завершена с ошибкой
     */
    public const CRON_STATUS_ERROR = 'error';

    /**
     * Статус, когда смс отправлено с ошибкой
     */
    public const CRON_STATUS_SMS_ERROR = 'sms-error';

    /**
     * Добавить новый телефон
     * @param array $data
     * @return int
     */
    public function addItem(array $data): int
    {
        $sql = $this->db->placehold('INSERT INTO s_phone_partner_api SET ?%', $data);
        $this->db->query($sql);

        return (int)$this->db->insert_id();
    }

    /**
     * Обновить запись с телефоном
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateItem(int $id, array $data)
    {
        $sql = $this->db->placehold('UPDATE s_phone_partner_api SET ?% WHERE id = ?', $data, $id);
        return $this->db->query($sql);
    }

    /**
     * Проверка телефона в базе данных
     * @param string $phone
     * @return bool
     */
    public function hasPhone(string $phone): bool
    {
        $sql = $this->db->placehold('SELECT EXISTS (SELECT * FROM s_phone_partner_api  WHERE phone = ?) as r', $phone);
        $this->db->query($sql);
        return (bool)$this->db->result('r');
    }

    /**
     * Выборка записей
     * @param int $limit
     * @param int $offset
     * @param bool $with_new_client Делать выборку с новыми клиентами
     * @return false|int
     */
    public function getCronWaitingPhone(int $limit = 500, int $offset = 500, bool $with_new_client = true)
    {
        $where = [];

        $sql = $this->db->placehold('SELECT id, phone, cron_status, client_type  FROM s_phone_partner_api 
                                            WHERE cron_status = ? 
                                            -- {{where}}
                                            ORDER BY id ASC LIMIT ?, ?', self::CRON_STATUS_NEW, $offset, $limit);

        if (!$with_new_client) {
            $where[] = $this->db->placehold("client_type <> ?", self::CLIENT_TYPE_NEW);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
    }
}
