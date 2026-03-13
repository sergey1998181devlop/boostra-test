<?php

require_once 'Simpla.php';


class CDoctor extends Simpla
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_SUCCESS = 'SUCCESS';

    private $enabled;    
    private $token;
    
    private $url = 'https://credit-rating24.ru';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->token = $this->settings->apikeys['cdoctor']['token'];
    	$this->enabled = $this->settings->apikeys['cdoctor']['enabled'] || $this->is_developer;
    }
    
    
    public function send_order($order_id)
    {
        if (empty($this->enabled))
            return false;
        
        $method = '/api/sales/credit-rating/add-lead';

        if ($order = $this->orders->get_order($order_id))
        {
            $passport = str_replace(array('-', ' '), '', $order->passport_serial);
            $passport_serial = substr($passport, 0, 4);
            $passport_number = substr($passport, 4, 6);
            
            $data = array(
                'firstName' => $order->firstname,
                'lastName' => $order->lastname,
                'patronymic' => $order->patronymic,
                'passport[seria]' => $passport_serial,
                'passport[no]' => $passport_number,
                'passport[issueDate]' => date('Y-m-d', strtotime($order->passport_date)),
                'birthDate' => date('Y-m-d', strtotime($order->birth)),
                'phone' => $order->phone_mobile,
                'loan[amount]' => $order->amount,
                'loan[term]' => $order->period,
                'clientId' => $order->user_id,
                'level' => 0
            );

            $response = $this->load($method, true, $data);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';            
            if ($response = json_decode($response))
            {
                if ($response->status == 'new')
                {
                    $this->cdoctor->add_item(array(
                        'user_id' => $order->user_id,
                        'order_id' => $order->order_id,
                        'cdoctor_id' => $response->id,
                        'cdoctor_status' => $response->status,
                        'url' => $response->url,
                        'created' => date('Y-m-d H:i:s'),
                    ));
                }
            }

        }
    }
    
    public function check_status($cdoctor_id)
    {
        if (empty($this->enabled))
            return false;
        
    	$method = '/api/sales/credit-rating/'.$cdoctor_id;

        $response = $this->load($method);

        return json_decode($response);
    }
    
    
    /**
     * CDoctor::load()
     * 
     * @param string $method
     * @param array $data
     * @param bool $post
     * @return
     */
    public function load($method, $post = false, $data = array())
    {
        $url = $this->url.$method;
        
        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer '.$this->token
        );
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($post))
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $res = curl_exec($ch);

        curl_close($ch);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump(curl_error($ch));echo '</pre><hr />';
        $this->logging(__METHOD__, $method, $data, $res, 'cdoctor.txt');
        
        return $res;
    }
    
    
	public function get_cdoctor_item($cdoctor_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __cdoctors
            WHERE cdoctor_id = ?
        ", (int)$cdoctor_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_order_item($order_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __cdoctors
            WHERE order_id = ?
        ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_item($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __cdoctors
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_items($filter = array())
	{
		$id_filter = '';
        $status_filter = '';
        $from_filter = '';
        $to_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND cdoctor_status = ?", (string)$filter['status']);
        
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND DATE(created) >= ?", $filter['from']);

        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND DATE(created) <= ?", $filter['to']);
        
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
            FROM __cdoctors
            WHERE 1
                $id_filter
				$status_filter
                $from_filter
                $to_filter
                $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_items($filter = array())
	{
        $id_filter = '';
        $status_filter = '';
        $from_filter = '';
        $to_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND cdoctor_status = ?", (string)$filter['status']);
        
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND DATE(created) >= ?", $filter['from']);

        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND DATE(created) <= ?", $filter['to']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __cdoctors
            WHERE 1
                $id_filter
                $status_filter
                $from_filter
                $to_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_item($item)
    {
		$query = $this->db->placehold("
            INSERT INTO __cdoctors SET ?%
        ", (array)$item);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_item($id, $item)
    {
		$query = $this->db->placehold("
            UPDATE __cdoctors SET ?% WHERE id = ?
        ", (array)$item, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_item($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __cdoctors WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
    
}