<?php

error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__) . '/api/Simpla.php';

class StageSmsCron extends Simpla
{
    // время через которое отправляется смс
    private $max_delay = 300; // 5 минут
    
    public function __construct()
    {
    	parent::__construct();
        
        // Отключил
        return false;
        
        $this->run();
    }
    
    private function run()
    {
    	$this->db->query("
            SELECT 
                id, 
                phone_mobile,
                created,
                personal_data_added,
                personal_data_added_date,
                address_data_added,
                address_data_added_date,
                accept_data_added,
                accept_data_added_date,
                files_added,
                files_added_date,
                card_added,
                card_added_date
                additional_data_added,
                additional_data_added_date
            FROM __users 
            WHERE stage_sms_sended = 0
            AND first_loan = 1
            AND (
                personal_data_added = 0
                OR address_data_added = 0
                OR accept_data_added = 0
                OR files_added = 0
                OR card_added = 0
                OR additional_data_added = 0
            )
        ");
        
        $results = $this->db->results();
        
        foreach ($results as $result)
        {
            if ($result->personal_data_added == 0)
            {
echo 'personal_data_not_added <br />';
                $delay = time() - strtotime($result->created);
echo 'delay: '.$delay.'<br />';
            }
            elseif ($result->address_data_added == 0)
            {
echo 'address_data_not_added <br />';
                $delay = time() - strtotime($result->personal_data_added_date);
echo 'delay: '.$delay.'<br />';                
            }
            elseif ($result->accept_data_added == 0)
            {
echo 'accept_data_not_added <br />';
                $delay = time() - strtotime($result->address_data_added_date);
echo 'delay: '.$delay.'<br />';                
            }
            elseif ($result->card_added == 0)
            {
echo 'card_not_added <br />';
                $delay = time() - strtotime($result->accept_added_date);
echo 'delay: '.$delay.'<br />';                
                
            }
            elseif ($result->files_added == 0)
            {
echo 'files_not_added<br />';
                $delay = time() - strtotime($result->card_data_added_date);
echo 'delay: '.$delay.'<br />';

            }
            elseif ($result->additional_data_added == 0)
            {
echo 'additional_data_not_added <br />';
                $delay = time() - strtotime($result->files_added_date);
echo 'delay: '.$delay.'<br />';                
            }
            
            if ($delay > $this->max_delay)
            {
                $this->send_sms($result->phone_mobile);
                
                $this->db->query("UPDATE __users SET stage_sms_sended = 1 WHERE id = ?", $result->id);
            }   

        }
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
    }

    private function send_sms($phone)
    {
//        $message = 'Деньги ждут! Завершите оформление заявки. boostra.ru/user';
        $message = 'Dengi zhdut! Zavershite ofomlenie zayavki. boostra.ru/user';


//echo $phone.' '.$message.'<br /><br />';
//return false;
        
        $result = $this->notify->send_sms($phone, $message);
        file_put_contents($this->config->root_dir.'logs/sms.txt', PHP_EOL.PHP_EOL.date('d-m-y H:i:s').PHP_EOL.'Отправляем смс на номер '.$phone.PHP_EOL.serialize($result).PHP_EOL, FILE_APPEND);

    }
    
}

new StageSmsCron();