<?php

require_once 'Simpla.php';

class Eventlogs extends Simpla
{
	private $events = array(
        1 => 'Переход в карточку',
        
        2 => 'Заявка принята',
        3 => 'Заявка одобрена',
        4 => 'Отказ по заявке',
        
        5 => 'Вкладка Заявка',
        6 => 'Вкладка Скоринги',
        7 => 'Вкладка Кредитная история',
        8 => 'Вкладка Комментарии',
        
        9 => 'Редактирование Сумма и срок заявки',
        10 => 'Редактирование Сервисные услуги',
        11 => 'Редактирование Персональная информация',
        12 => 'Редактирование Паспортные данные',
        13 => 'Редактирование Адрес прописки',
        14 => 'Редактирование Адрес проживания',
        15 => 'Редактирование Контактные лица',
        16 => 'Редактирование Данные о работе',
        17 => 'Редактирование Адрес Организации',
        18 => 'Редактирование Ссылки на профили в соц. сетях',
        
        19 => 'Открытие фото',
        20 => 'Фото принято',
        21 => 'Фото отклонено',
        
        22 => 'Звонок клиенту',
        23 => 'Звонок КЛ',
        24 => 'Звонок на работу',
        
        25 => 'Сохранение Сумма и срок заявки',
        26 => 'Сохранение Сервисные услуги',
        27 => 'Сохранение Персональная информация',
        28 => 'Сохранение Паспортные данные',
        29 => 'Сохранение Адрес прописки',
        30 => 'Сохранение Адрес проживания',
        31 => 'Сохранение Контактные лица',
        32 => 'Сохранение Данные о работе',
        33 => 'Сохранение Адрес Организации',
        34 => 'Сохранение Ссылки на профили в соц. сетях',
        
        35 => 'Вкладка Логирование',
        36 => '',
        37 => '',
        38 => '',
        39 => '',
        40 => '',
    );
    
    public function get_events()
    {
    	return $this->events;
    }
    
    public function get_log($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __eventlogs
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_logs($filter = array())
	{
		$id_filter = '';
        $order_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['order_id']))
            $order_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __eventlogs
            WHERE 1
                $id_filter
                $order_filter
				$keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_logs($filter = array())
	{
        $id_filter = '';
        $order_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['order_id']))
            $order_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __eventlogs
            WHERE 1
                $id_filter
                $order_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_log($eventlog)
    {
		$query = $this->db->placehold("
            INSERT INTO __eventlogs SET ?%
        ", (array)$eventlog);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_log($id, $eventlog)
    {
		$query = $this->db->placehold("
            UPDATE __eventlogs SET ?% WHERE id = ?
        ", (array)$eventlog, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_log($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __eventlogs WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}