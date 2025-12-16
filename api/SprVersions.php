<?php

require_once 'Simpla.php';

/**
 * Версионирование Системы Принятия Решений
 * s_spr_versions
 */
class SprVersions extends Simpla
{
    /** @var string Ключ отметки в s_order_data с версией СПР на момент создания заявки */
    public const ORDER_DATA_SPR_VERSION = 'spr_version';

    /** @var string Ключ отметки в s_order_data с версией последнего акси проведённого по заявке */
    public const ORDER_DATA_AXI_VERSION = 'axi_version';

    /**
     * Получение всех последних записей
     * @param int $limit
     * @return array
     */
    public function getAll(int $limit = 100)
    {
        $this->db->query("SELECT * FROM __spr_versions ORDER BY id DESC LIMIT ?", $limit);
        return $this->db->results() ?: [];
    }

    /**
     * Получение конкретной записи по её Id
     * @param $id
     * @return false|ArrayObject
     */
    public function get($id)
    {
        $query = $this->db->placehold('SELECT * FROM __spr_versions WHERE id = ?', $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO __spr_versions SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $query = $this->db->placehold("UPDATE __spr_versions SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Получение актуальной (последней) версии
     * @return int
     */
    public function getCurrentVersion()
    {
        $this->db->query('SELECT MAX(id) AS version FROM __spr_versions');
        return $this->db->result('version') ?: 1;
    }

    /**
     * Получение версии СПР на момент создания переданной заявки
     * @param $order_id
     * @return int
     */
    public function getOrderVersion($order_id)
    {
        return (int)$this->order_data->read($order_id, self::ORDER_DATA_SPR_VERSION) ?? 1;
    }

    /**
     * Получение версии последнего акси проведённого у заявки
     * @param $order_id
     * @return string
     */
    public function getOrderAxiVersion($order_id)
    {
        return (int)$this->order_data->read($order_id, self::ORDER_DATA_AXI_VERSION) ?? '';
    }

    /**
     * Отмечаем заявку сохраняя в неё текущую версию СПР
     * @param $order_id
     * @return void
     */
    public function markOrderVersion($order_id)
    {
        $this->order_data->set($order_id, self::ORDER_DATA_SPR_VERSION, $this->getCurrentVersion());
    }

    /**
     * Отмечаем заявку сохраняя в неё текущую версию акси
     * @param $order_id
     * @param $axi_version
     * @return void
     */
    public function markOrderAxiVersion($order_id, $axi_version)
    {
        $this->order_data->set($order_id, self::ORDER_DATA_AXI_VERSION, $axi_version);
    }
}