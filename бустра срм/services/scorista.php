<?php

require_once 'AService.php';

class ScoristaService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
//        $this->response['info'] = array(
//            
//        );
        
        $this->run();
    }
    
    private function run()
    {
        if ($uid = $this->request->get('uid'))
        {
            if ($agrid = $this->request->get('agrid'))
            {
                if (!($isset_scoring_id = $this->scorings->get_scorista_scoring_id($agrid)))
                {
                    if ($user_id = $this->users->get_uid_user_id($uid))
                    {
                        $this->scorings->log_add_scoring('services/scorista.php', ['user_id' => $user_id]);
                        $this->scorings->add_scoring(array(
                            'user_id' => $user_id,
                            'order_id' => 0,
                            'type' => $this->scorings::TYPE_SCORISTA,
                            'status' => $this->scorings::STATUS_IMPORT,
                            'body' => '',
                            'created' => date('Y-m-d H:i:s'),
                            'scorista_id' => $agrid,
                        ));
                        
                        $this->response['success'] = 1;
                    }
                    else
                    {
                        $this->response['error'] = 'USER_NOT_FOUND';
                    }
                }
                else
                {
                    $this->response['error'] = 'AGRID_ALLREADY_EXISTS';
                }
            }
            else
            {
                $this->response['error'] = 'AGRID_NOT_FOUND';
            }

        }
        else
        {
            $this->response['error'] = 'EMPTY_UID';
        }
        
        $this->json_output();
    }
}
new ScoristaService();