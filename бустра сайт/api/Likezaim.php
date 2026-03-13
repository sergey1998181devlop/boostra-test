<?php

require_once 'Simpla.php';

class Likezaim extends Simpla
{
    private $salt = 'mp63xXZkH8vW63LJ43js';
    private $url;
    private $lifetime_days = 20;

    public function __construct()
    {
        parent::__construct();
        $this->url = $this->config->like_zaim_crm_url . 'boostra/transfer';
    }

    public function send($request)
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    
    public function transfer($item)
    {
        if (!$this->settings->likezaim_enabled && $item->user_id != $this->settings->likezaim_test_user_id)
            return false;

        if ($order = $this->orders->get_order((int)$item->order_id)) {
                        
            $likezaim_id = $this->add_item([
                'user_id' => $item->user_id,
                'order_id' => $item->order_id,
                'created' => date('Y-m-d H:i:s'),
            ]);
            
            // грузим фотки из хранилища
            @file_get_contents($this->config->front_url . 'ajax/filestorage.php?user_id='.$item->user_id);
            
            $data = $this->get_data($order);
            $request = [
                'data' => base64_encode($data),
                'signature' => $this->get_signature($data),
            ];
            $json_response = $this->send($request);
            $response = json_decode($json_response);

            $this->update_item($likezaim_id, [
                'response' => $json_response,
                'link' => $response->data->link ?? NULL,
                'has_contract' => $response->data->has_contract ?? 0,
                'client_cloned' => $response->data->client_cloned ?? NULL,
            ]);
$this->logging('', '', json_decode($data), $response, 'likezaim.txt');
        }
        
    }

    public function check($order, $balances)
    {
        $order_open = 0;
        $likezaim_valid = 0;        

        foreach ($balances as $balance) {
            if ($order['1c_id'] == $balance['Заявка'])
                $order_open = 1;
        }
        
        if (!empty($order_open)) {
            if ($likezaim_item = $this->get_items(['order_id' => $order['id']])) {
                $border_time = strtotime(date('Y-m-d', time() - 86400 * $this->lifetime_days));
                $order_time = strtotime(date('Y-m-d', strtotime($likezaim_item->created)));
                
                if ($order_time >= $border_time) {
                    if (empty($likezaim_item->postback_getted)) {
                        if (!empty($likezaim_item->link) && stripos($likezaim_item->link, 'http') === false) {
                            $likezaim_item->link = '//'.$likezaim_item->link;
                        }
                        return $likezaim_item;
                    }
                }
            }
        }
        
        return NULL;
    }
    
    public function get_data($order)
    {
        $request = [];
        if ($user = $this->users->get_user((int)$order->user_id)) {
            $request['user'] = $user;
            
            $request['files'] = [];
            if ($files = $this->users->get_files(['user_id' => $order->user_id])) {
                foreach ($files as $file) {
                    if ($file->status != 3) {
                        $file->url = $this->config->front_url . '/' . $this->config->users_files_dir . $file->name;
                        $request['files'][] = $file;                        
                    }
                }
            }
            
            if ($card = $this->best2pay->get_card($order->card_id)) {
                $request['card'] = $card->pan;
            }
            
            $request['order_id'] = $order->id; 
        }
        return json_encode($request, JSON_UNESCAPED_UNICODE);
    }
    
    public function get_signature($data_string)
    {
        return md5($data_string.$this->salt);        
    }
    

    public function get_items_for_send()
    {
        $this->db->query("
            SELECT 
                b.user_id,
                b.order_id,
                b.complete_date
            FROM b2p_p2pcredits as b
            LEFT JOIN s_likezaim AS l
            ON b.order_id = l.order_id
            WHERE b.status = 'APPROVED'
            AND b.likezaim_enabled = 1
            AND l.order_id IS NULL
            ORDER BY b.id DESC
            LIMIT 10
        ");

        return $this->db->results();
    }
    
    public function get_items($params = [], $single = true)
    {
		$filter = [];        
        if (!empty($params))
            foreach ($params as $param_key => $param_value)
                $filter[] = $this->db->placehold('`'.$param_key.'` = ?', $param_value);
        $where = implode(' AND ', $filter);

        $this->db->query("
            SELECT * FROM s_likezaim
            WHERE $where
        ");
        return $single ? $this->db->result() : $this->db->results();
    }

    public function add_item($item)
    {
        $this->db->query("
            INSERT INTO s_likezaim
            SET ?%
        ", (array)$item);
        return $this->db->insert_id();
    }

    public function update_item($id, $item)
    {
        return $this->db->query("
            UPDATE s_likezaim
            SET ?%
            WHERE id = ?
        ", (array)$item, (int)$id);
    }

    public function delete_item($id)
    {
        return $this->db->query("
            DELETE FROM s_likezaim
            WHERE id = ?
        ", (int)$id);
    }
}