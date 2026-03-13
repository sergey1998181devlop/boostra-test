<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__ . '/../api/Simpla.php';

class SmsLikezaimCron extends Simpla
{
    // время задержки при отправке смс в секундах
    private $delay = 600;
    
    public function __construct()
    {
        parent::__construct();
    
        $this->run();
    }
    
    public function run()
    {
        $sms_template_likezaim = $this->sms->get_template($this->sms::SMS_TEMPLATE_LIKEZAIM);
        if ($items = $this->get_items_for_send()) {
            if (!empty($sms_template_likezaim->status)) {
                foreach ($items as $item) {
                    $message = $this->render_message($sms_template_likezaim->template, $item->link);
                    $resp = $this->smssender->send_sms($item->phone_mobile, $message, 'likezaim');
                    $sms_id = $this->sms->add_message(
                        [
                            'user_id' => $item->user_id,
                            'order_id' => $item->order_id,
                            'phone' => $item->phone_mobile,
                            'message' => $message,
                            'created' => date('Y-m-d H:i:s'),
                            'send_status' => $resp[1],
                            'delivery_status' => '',
                            'send_id' => $resp[0],
                            'type' => $this->smssender::TYPE_LIKEZAIM,
                        ]
                    );
                    
                    $this->update_likezaim_item($item->id, [
                        'sms_id' => $sms_id
                    ]);
                }
            } else {
                $this->update_likezaim_item($item->id, [
                    'sms_id' => 1
                ]);                
            }
        }        
    }
    
    private function render_message($message, $link)
    {
        return trim($message).' '.$link;
    }
    
    private function get_items_for_send()
    {
        $border_time = date('Y-m-d H:i:s', time() - $this->delay);
        
        $this->db->query("
            SELECT 
                l.id,
                l.link,
                l.user_id,
                l.order_id,
                u.phone_mobile
            FROM s_likezaim as l
            LEFT JOIN s_users AS u
            ON l.user_id = u.id
            WHERE l.created < ?
            AND l.sms_id = 0
            AND l.link IS NOT NULL
            AND l.link != ''
            AND l.has_contract = 0
            ORDER BY id ASC
            LIMIT 10
        ", $border_time);
        
        return $this->db->results();
    }
    
    private function update_likezaim_item($id, $item)
    {
        return $this->db->query("
            UPDATE s_likezaim
            SET ?%
            WHERE id = ?
        ", (array)$item, (int)$id);
    }
}

new SmsLikezaimCron();