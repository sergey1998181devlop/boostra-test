<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
chdir('..');
require_once 'api/Simpla.php';

class ExitpoolAjax extends Simpla
{
    private $response = array();
    
    public function __construct()
    {
    	parent::__construct();
        
        $action = $this->request->post('action', 'string');
        switch ($action):
            
            case 'payment_exitpool':
                $this->run_payment_exitpool();
            break;
            
            default:
                $this->run();
            
        endswitch;

        $this->output();
    }
    
    private function run()
    {
//echo 11;
    	if ($this->request->method('post'))
        {
            $user_id = empty($_SESSION['user_id']) ? 0 : (int)$_SESSION['user_id'];
            if ($user_id && ($user = $this->users->get_user($user_id)))
            {
                $questions = $this->exitpools->get_questions();
                
                $results = $this->request->post('question');
                
                foreach ($results as $question_id => $response)
                {
                    $exitpool = array(
                        'user_id' => $user->id,
                        'question_id' => $question_id,
                        'question' => $questions[$question_id]->question,
                        'response' => $response,
                        'date' => date('Y-m-d H:i:s'),
                    );
///echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($exitpool);echo '</pre><hr />';
                    $this->response['success'] = $this->exitpools->add_exitpool($exitpool);
                    
                    $_SESSION['exitpool_completed'] = 1;
                }
            }
            else
            {
                $this->response['error'] = 'NOT AUTHORIZED';
            }
        }
        else
        {
            $this->response['error'] = 'UNDEFINED METHOD';
        }
    }
    
    public function run_payment_exitpool()
    {
//echo 11;
    	if ($this->request->method('post'))
        {
            $user_id = empty($_SESSION['user_id']) ? 0 : (int)$_SESSION['user_id'];
            if ($user_id && ($user = $this->users->get_user($user_id)))
            {
                $variant_id = $this->request->post('variant_id', 'integer');
                
                $variant = $this->payment_exitpools->get_variant($variant_id);
                
                $this->response['success'] = $this->payment_exitpools->add_exitpool(array(
                    'user_id' => $user_id,
                    'created' => date('Y-m-d H:i:s'),
                    'response' => $variant->variant
                ));
                
            }
            else
            {
                $this->response['error'] = 'NOT AUTHORIZED';
            }
        }
        else
        {
            $this->response['error'] = 'UNDEFINED METHOD';
        }
    }
    
    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");		
    
        echo json_encode($this->response);
    }
}
new ExitpoolAjax();