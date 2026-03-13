<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__) . '/api/Simpla.php';

class AuditCron extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
    	$audits = $this->scorings->get_audits(array('status'=>$this->scorings::STATUS_NEW));
        
        foreach ($audits as $audit)
            $this->scorings->update_audit($audit->id, array('status'=>$this->scorings::STATUS_PROCESS));
            
        foreach ($audits as $audit)
        {
//            $this->scorings->update_audit($audit->id, array('types'=>array('fms', 'fssp', 'location', 'local_time', 'scorista')));
            $this->run_audit($audit);
        }
    }
    
    public function run_audit($audit)
    {
    	foreach ($audit->types as $type)
        {
            $scoring_type = $this->scorings->get_type($type);
            
            $classname = $type;
//echo $classname;
            $scoring_type_result = $this->$classname->run($audit->id, $audit->user_id, $audit->order_id);
            
            if (!$scoring_type_result && $scoring_type->negative_action == 'stop')
            {
                $this->scorings->update_audit($audit->id, array('status'=>$this->scorings::STATUS_STOPPED));
                return false;
            }
            
        }
        
        $this->scorings->update_audit($audit->id, array('status'=>$this->scorings::STATUS_COMPLETED));
        return true;
    }
    
}

$cron = new AuditCron();
$cron->run();
