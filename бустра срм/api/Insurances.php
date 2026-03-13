<?php

class Insurances extends Simpla
{
    /**
     * Сумма штрафного КД для просрочки +9 дней
     */
    public const PENALTY_9_DAYS_AMOUNT = 2790;

    /**
     * Сумма штрафного КД для просрочки +30 дней
     */
    public const PENALTY_30_DAYS_AMOUNT = 3790;

    /**
     * Сумма штрафного КД для просрочки +90 дней
     */
    public const PENALTY_90_DAYS_AMOUNT = 4790;

    /**
     * ИП Алфавит
     */
    public const INSURER_AL = 'AL';

    public $insurers = array(
        'IP' => array(
            'code' => 'IP',
            'name' => 'Индивидуальный предприниматель Лагуткин Максим Дмитриевич',
            'title' => 'Лагуткин',
            'address' => '443099 Самарская область, г.Новокуйбешевск, ул. Дзержинского, д. 4, корп. А, кв.21',
            'mask' => '220H3NSB256',
        ),
        'ST' => array(
            'code' => 'ST',
            'name' => 'Индивидуальный предприниматель Стецура Александр Владимирович',
            'title' => 'Стецура',
            'address' => '461040, Оренбургская обл., Бузулук г., 1-й мкр, д.21, кв 26',
            'mask' => '220H3NSB272',
        ),
        'PO' => array(
            'code' => 'PO',
            'name' => 'Индивидуальный предприниматель Полякова Юлия Васильевна',
            'title' => 'Полякова',
            'address' => '446206, Самарская обл, Новокуйбышевск г, Нефтепроводчиков ул, дом 10, кв. 13',
            'mask' => '220H3NSB273',
        ),
        'T' => array(
            'code' => 'T',
            'name' => 'Индивидуальный предприниматель Терляхина Елизавета Степановна',
            'title' => 'Терляхина',
            'address' => 'Обл. Оренбургская, г. Бузулук 2-й мкр, д.2-3 литер АА1ЕЕ1 кв. 139А',
            'mask' => '220H3NSB286',
        ),
        'AL' => array(
            'code' => 'AL',
            'name' => 'Общество с ограниченной ответственностью  "Алфавит"',
            'title' => 'Алфавит',
            'address' => '443045 г.Самара ул.Революционная д.70 оф.411',
            'mask' => '220H3NSB600',
        ),
        'Boostra' => array(
            'code' => 'Boostra',
            'name' => 'МКК ООО «Бустра»',
            'title' => 'Бустра',
            'address' => '443099 Самарская область, г. Самара, ул. Максима Горького, д. 119, к. 15',
            'mask' => '220H3NSB137',
        ),
    );
    
    public function get_insurance_period()
    {
        return 14;
    }
    
    public function get_insurance_cost($amount) {}
    
    public function get_insurance_percent($order)
    {
        if ($order->percent == 0)
            $insure = 0.33;
        if ($order->have_close_credits == 0)
            $insure = 0.33;
        elseif ($order->amount <= 2000)
            $insure = 0.23;
        elseif ($order->amount <= 4000)
            $insure = 0.18;
        elseif ($order->amount <= 7000)
            $insure = 0.15;
        elseif ($order->amount <= 10000)
            $insure = 0.14;
        else
            $insure = 0.13;
        
        return $insure;
    }
    
    public function create_insurance_documents($insurance_id)
    {
    	if ($insurance = $this->get_insurance($insurance_id))
        {
            $user = $this->users->get_user($insurance->user_id);
            $user->insurance = $insurance;
            $user->order = $this->orders->get_order($insurance->order_id);
            $user->insurer_info = $this->insurers[$this->get_insurer($insurance->number)];
            
            if (!empty($insurance->transaction_id))
                $user->transaction = $this->transactions->get_transaction($insurance->transaction_id);
            
            $this->documents->create_document(array(
                'type' => 'POLIS_STRAHOVANIYA',
                'user_id' => $user->id,
                'order_id' => $insurance->order_id,
                'contract_number' => $insurance->contract_number,
                'params' => $user
            ));
            $this->documents->create_document(array(
                'type' => 'ZAYAVLENIE_NA_STRAHOVANIYE',
                'user_id' => $user->id,
                'order_id' => $insurance->order_id,
                'contract_number' => $insurance->contract_number,
                'params' => $user
            ));
        }
    }
    
    public function get_insurer($number)
    {
        foreach ($this->insurers as $insure)
        {
            if (stripos($number, $insure['mask']) !== false)
                return $insure['code'];
        }
        
        return 'Boostra';        
    }
    /**
     * Insurances::create_number()
     * 

Нумерация по каждому Юр.лицу(Ип) будет своя - в части кода партнера, чтобы легко было производить сверку). Код партнера остаётся действующий, в итоге будет следующая нумерация (18-значная(sad)
МКК Бустра - 220H3NSB1370000000

ИП Лагуткин - 220H3NSB2560000000

ИП Стецура - 220H3NSB2720000000

ИП Полякова - 220H3NSB2730000000 

ИП Терляхина - 
220H3NSB2860000000

     * 18-значная нумерация 200H3NZI163ХХХХХХХ
        Где,
        20 – год выпуска полиса
        0H3 – код подразделения выпустившего полис (не меняется)
        NZI – код продукта (не меняется)
        163 – код партнера (не меняется)
        ХХХХХХХ – номер полиса страхования 
     * 
     * @param mixed $id
     * @return string
     */
    public function create_number($id)
    {
        $number = '';
        $number .= date('y'); // год выпуска полиса
        $number .= '0H3'; // код подразделения выпустившего полис (не меняется)
        $number .= 'NZI'; // код продукта (не меняется)
        $number .= '165'; // код партнера (не меняется)
        $number .= 9; // 9ХХХХХХ – номер полиса страхования (первая всегда 9 для экозайма)
    	
        $id_number = $id;
        while (strlen($id_number) < 6)
        {
            $id_number = '0'.$id_number;
        }
        $number .= $id_number;
        
        return $number;
    }
    
    
	public function get_operation_insurance($operation_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __insurances
            WHERE operation_id = ?
        ", (int)$operation_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_insurance($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __insurances
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_order_insurance($order_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __insurances
            WHERE order_id = ?
        ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_insurances($filter = array())
	{
        $where = [];
        $join = [];
        $select = [];

		$id_filter = '';
		$user_id_filter = '';
		$order_id_filter = '';
        $sent_status_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND i.id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND i.user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND i.order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (isset($filter['sent']))
            $sent_status_filter = $this->db->placehold("AND i.sent_status = ?", (int)$filter['sent']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (i.name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}

        if (!empty($filter['filter_has_pay'])) {
            $select[] = ",t.insure_amount";
            $join[] = "LEFT JOIN s_transactions t ON t.id = i.transaction_id";
            $where[] = $this->db->placehold("t.status IN (?@)", $this->transactions::STATUSES_SUCCESS);
        }
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT 
                i.*
            -- {{select}}
            FROM __insurances i
            -- {{join}}
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
				$sent_status_filter
                $keyword_filter
            -- {{where}}
            ORDER BY i.id ASC 
            $sql_limit
        ");

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode(PHP_EOL, $select) : '',
            '-- {{join}}' => !empty($join) ? implode(PHP_EOL, $join) : '',
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
	}
    
	public function count_insurances($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $sent_status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (isset($filter['sent']))
            $sent_status_filter = $this->db->placehold("AND sent_status = ?", (int)$filter['sent']);
            
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __insurances
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $sent_status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_insurance($insurance)
    {
		$insurance = (array)$insurance;
        
        $query = $this->db->placehold("
            INSERT INTO __insurances SET ?%
        ", $insurance);
        $this->db->query($query);
        $id = $this->db->insert_id();

        if (empty($insurance['number']))
        {
            $insurance_number = $this->create_number($id);    
            $this->update_insurance($id, array('number' => $insurance_number));
        }
        
        return $id;
    }
    
    public function update_insurance($id, $insurance)
    {
		$query = $this->db->placehold("
            UPDATE __insurances SET ?% WHERE id = ?
        ", (array)$insurance, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_insurance($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __insurances WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * Получает штрафные КД
     * @param array $filter_data
     * @return array|false
     */
    public function getPenaltyCreditDoctor(array $filter_data)
    {
        $where = [];

        $sql = "
            SELECT
                    DATE(created) as date,
                    COUNT(created) as periodName,
                    COUNT(DISTINCT user_id) as total_users,
                    COUNT(insure) as insure,
                    COUNT(id) as total_count,
                    ROUND(SUM(insure)) as total_pays,
                    SUM(nine_plus) as nine_plus_count,
                    SUM(thirty_plus) as thirty_plus_count,
                    SUM(eighty_five_plus) as eighty_five_plus_count
            FROM (
                    SELECT
                        bpp.id,
                        bpp.user_id,
                        bpp.insure,
                        bpp.created,
                        bpp.insure = ? as nine_plus,
                        bpp.insure = ? as thirty_plus,
                        bpp.insure = ? as eighty_five_plus
                    FROM b2p_payments bpp
                            LEFT JOIN s_contracts c ON bpp.contract_number = c.number
                        WHERE bpp.insure > 0
                        AND bpp.reason_code = " . $this->best2pay::REASON_CODE_SUCCESS . "
                        -- {{where}}
                 ) a
        ";

        if (!empty($filter_data['filter_date_created'])) {
            $where[] = $this->db->placehold(
                'bpp.created BETWEEN ? AND ?',
                $filter_data['filter_date_created']['filter_date_start'],
                $filter_data['filter_date_created']['filter_date_end'] . ' 23:59:59'
            );
        }

        if (!empty($filter_data['group_by'])) {
            $sql .= PHP_EOL . "GROUP BY " . $filter_data['group_by'];
        }

        $sql = $this->db->placehold(
            $sql,
            self::PENALTY_9_DAYS_AMOUNT,
            self::PENALTY_30_DAYS_AMOUNT,
            self::PENALTY_90_DAYS_AMOUNT
        );

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        return $this->db->results();
    }
}