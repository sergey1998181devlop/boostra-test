<?php

require_once 'Simpla.php';

class Reasons extends Simpla
{
    /**
     * Причина отказа Недействительный паспорт
     */
    public const REASON_PASSPORT = 9;

    /**
     * Причина отказа Внесен в ЧС
     */
    public const REASON_BLACK_LIST = 2;

    public const REASON_LOCATION = 14;

    public const REASON_AGE = 23;

    /**
     * Отказ второго разделенного займа, если первый закрылся
     */
    public const REASON_CLOSED_ONE_DIVIDE_ORDER_REASON_ID = 39;

	public const REASON_EXPIRED_AUTO_APPROVE = 34;

    public const REASON_END_TIME = 36;

    /**
     * Клиент удален из черного списка
     */
    public const REASON_REMOVED_FROM_BLACKLIST = 43;

    /**
     * **НЕ ИСПОЛЬЗУЕТСЯ у новых заявок с 27.03.25**
     *
     * Карта отказного НК продана партнёрам
     * Заявка отклонена без рассылки СМС
     */
    public const REASON_CARD_SELLED_TO_BONON = 44;

    /**
     * Отказ скористы или акси
     */
    public const REASON_SCORISTA = 5;

    /**
     * АксиНБКИ не нашёл ИНН клиента
     */
    public const REASON_INN_NOT_FOUND = 45;

    /**
     * АксиНБКИ нашёл самозапрет на кредиты
     */
    public const REASON_SELF_DEC = 46;

    /**
     * АксиНБКИ - стоп-фактор IDX_SCOR
     */
    public const REASON_AXI_IDX = 47;

    /**
     * АксиНБКИ - стоп-фактор FSSP_SUM
     */
    public const REASON_AXI_FSSP = 48;

    /**
     * АксиНБКИ - стоп-фактор BAD_DEVICE
     */
    public const REASON_AXI_BAD_DEVICE = 57;

    /**
     * АксиНБКИ - стоп-фактор CNT_ACT_CH
     */
    public const REASON_AXI_CNT_ACT_CH = 58;

    /**
     * АксиНБКИ - стоп-фактор SCORE_CUTOFF
     */
    public const REASON_AXI_SCORE = 59;

    /**
     * АксиНБКИ - стоп-фактор YORISTO_BANKRUPCY
     */
    public const REASON_AXI_BANKRUPT = 60;

    /**
     * Отказ IL-займа пользователем в период охлаждения
     */
    public const REASON_COOLING_REJECT = 62;

    /** Отказ по АксиНБКИ - не удалось сопоставить полученный стоп-фактор с причиной отказа в СРМ */
    public const REASON_UNKNOWN_AXI = 63;

    /** АксиНБКИ - стоп-фактор ASOI_DEC */
    public const REASON_AXI_ASOI = 64;

	public function get_reason($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __reasons
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_reasons($filter = array())
	{
		$id_filter = '';
		$type_filter = '';
        $keyword_filter = '';
        $name_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", (string)$filter['type']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}

        if (isset($filter['name']) && !empty($filter['name']))
        {
            $filtering_names = is_array($filter['name']) ? $filter['name'] : [$filter['name']];
            $filtering_names = array_map(function ($name) {
                return '(admin_name = "' . $this->db->escape(trim($name)) . '")';
            }, $filtering_names);

            $name_filter .= $this->db->placehold("AND " . implode(' OR ', $filtering_names));
        }
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __reasons
            WHERE 1
                $id_filter
                $type_filter
				$keyword_filter
                $name_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_reasons($filter = array())
	{
        $id_filter = '';
        $type_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type = ?", (string)$filter['type']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __reasons
            WHERE 1
                $id_filter
                $type_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_reason($reason)
    {
		$query = $this->db->placehold("
            INSERT INTO __reasons SET ?%
        ", (array)$reason);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_reason($id, $reason)
    {
		$query = $this->db->placehold("
            UPDATE __reasons SET ?% WHERE id = ?
        ", (array)$reason, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_reason($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __reasons WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}