<?php

require_once 'Simpla.php';

/**
 * Class LeadPrice
 * s_lead_price
 */
class LeadPrice extends Simpla
{
    /**
     * Получение всех записей
     * @return array|false
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __lead_price");
        return $this->db->results();
    }

    /**
     * Получение конкретной записи по её utm_source и, если указано, webmaster_id
     * @param string $utmSource
     * @param null|string $webmaster
     * @return false|int
     */
    public function getByUtm(string $utmSource, $webmaster = null)
    {
        if (!empty($webmaster))
            $query = $this->db->placehold('SELECT * FROM __lead_price WHERE `utm_source` = ? AND `webmaster_id` = ?', $utmSource, $webmaster);
        else
            $query = $this->db->placehold('SELECT * FROM __lead_price WHERE `utm_source` = ? AND (`webmaster_id` IS NULL OR `webmaster_id` = "")', $utmSource);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получение конкретной записи по её Id
     * @param int $id
     * @return false|ArrayObject
     */
    public function getById(int $id)
    {
        $query = $this->db->placehold('SELECT * FROM __lead_price WHERE `id` = ?', $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add(array $row)
    {
        $query = $this->db->placehold('INSERT INTO __lead_price SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $query = $this->db->placehold("UPDATE __lead_price SET ?% WHERE `id` = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __lead_price WHERE `id` = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Пытается найти цену заявки по её utm_source
     *
     * Возвращает false, если не удалось найти цену для этого utm_source.
     * @param int|object $order Заявка или её id
     * @return false|float
     */
    public function getPriceForOrder($order)
    {
        if (is_int($order))
            $order = $this->orders->get_order($order);

        if (empty($order->utm_source))
            return false;

        if ($order->webmaster_id)
            $row = $this->getByUtm($order->utm_source, $order->webmaster_id);

        if (empty($row) && !($row = $this->getByUtm($order->utm_source)))
            return false;

        return (float)$row->price;
    }
}