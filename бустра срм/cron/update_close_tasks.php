<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';

class UpdateCloseTasksCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    
        $this->run();
    }
    
    public function run()
    {
//$this->truncate();
        
    	if ($loans = $this->soap->get_close_loans(1))
        {
            foreach ($loans as $loan)
            {
                if ($task = $this->tasks->get_number_close_task($loan->НомерДоговора))
                {
                    $this->tasks->update_close_task($task->id, array(
                        'last_update' => date('Y-m-d H:i:s'),
                    ));
                }
                else
                {
                    $close_task = new StdClass();

                    $close_task->number = $loan->НомерДоговора;
                    $close_task->amount = $loan->СуммаДоговора;
                    $close_task->open_date = date('Y-m-d', strtotime($loan->ДатаДоговора));
                    $close_task->close_date = date('Y-m-d', strtotime($loan->ДатаЗакрытия));
                    $close_task->year_closed = $loan->КоличествоЗаГод;
                    $close_task->uid = $loan->УИД;
                    $close_task->client = $loan->Клиент;
                    $close_task->pk = $loan->ПК;
                    $close_task->last_update = date('Y-m-d H:i:s');
    
                    if ($close_task->user_id = $this->users->get_uid_user_id($close_task->uid))
                    {
                        $user = $this->users->get_user((int)$close_task->user_id);
                        if ($user->id == 170906)
                            $close_task->timezone = 4;
                        else
                            $close_task->timezone = $this->users->get_timezone($user->Regregion);
                    }
                    
                    if (!empty($loan->НомерЗаявки))
                    {
                        $close_task->zayavka = $loan->НомерЗаявки;
                        $close_task->order_id = $this->orders->get_order_1cid($close_task->zayavka);
                    }
                    
                    
                    if (!empty($close_task->user_id))
                    {
                        $task_id = $this->tasks->add_close_task($close_task);
                    }
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($close_task, $task_id);echo '</pre><hr />';
                }
                
            }
        }
        
        

    }
    
    private function truncate()
    {
        $this->db->query("TRUNCATE TABLE s_close_tasks");
    }    
}
new UpdateCloseTasksCron();