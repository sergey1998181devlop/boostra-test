<?php
error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';

class ExpiredLoansCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
        $this->db->query("UPDATE __user_balance SET expired_days = 0");
    
        if ($opens = $this->soap->get_open_zaims())
        {
            foreach ($opens as $loan)
            {
                if ($loan->ДниПросрочки > 0)
                {
                    $this->db->query("
                        UPDATE __user_balance 
                        SET expired_days = ? 
                        WHERE zaim_number = ?
                    ", $loan->ДниПросрочки, $loan->Номер);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($loan);echo '</pre><hr />';                    
                }
            }
        }
    }
    
    
}

$cron = new ExpiredLoansCron();
$cron->run();
