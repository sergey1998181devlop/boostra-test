<?php
error_reporting(0);
ini_set('display_errors', 'Off');

chdir('..');
session_start();

require 'api/Simpla.php';

class RunScoringsApp extends Simpla
{
    private $response = array();
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');

        switch ($action):
            
            case 'create':
            
                $type = $this->request->get('type', 'string');
                $type_id = $this->scorings->get_type($type)->id;
                $order_id = $this->request->get('order_id', 'integer');

                if ((int)$type_id === $this->scorings::TYPE_SCORISTA) {
                    $this->checkCanAddScorista($order_id);
                }

                $scoring_types = $this->scorings->get_types();

                if ($order = $this->orders->get_order($order_id))
                {
                    switch ($type):
                        
                        case 'free':
                            
                            foreach ($scoring_types as $scoring_type)
                            {
                                if ($scoring_type->type == 'first' && $scoring_type->active)
                                {
                                    $add_scoring = array(
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->order_id,
                                        'type' => $scoring_type->id,
                                        'status' => $this->scorings::STATUS_NEW,
                                        'manual' => 1,
                                    );
                                    $this->scorings->add_scoring($add_scoring);
                                }
                            }
                            $this->response['success'] = 1;
                            
                        break;
                        
                        case 'all':
                        
                            foreach ($scoring_types as $scoring_type)
                            {
                                if ($scoring_type->active)
                                {
                                    $add_scoring = array(
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->order_id,
                                        'type' => $scoring_type->id,
                                        'status' => $this->scorings::STATUS_NEW,
                                        'manual' => 1,
                                    );
                                    $this->scorings->add_scoring($add_scoring);
                                }
                            }
                            $this->response['success'] = 1;
                            
                        break;
                        
                        case 'local_time':
                        case 'age':
                        case 'location':
//                        case 'fms':
                        case 'fns':
//                        case 'fssp':
//                        case 'fssp2':
//                        case 'juicescore':
                        case 'blacklist':
//                        case 'efrsb':
                        case 'axilink':
//                        case 'svo':
                        case 'finkarta':
                        case 'uprid':
                        case 'egrul':
                        case 'dbrain':
                        //case 'dbrain_passport':
                        case 'report':
                        case 'location_ip':
                        case 'hyper_c':
                        case 'terrorist':

                            $add_scoring = array(
                                'user_id' => $order->user_id,
                                'order_id' => $order->order_id,
                                'type' => $type_id,
                                'status' => $this->scorings::STATUS_NEW,
                                'manual' => 1,
                            );
                            $this->scorings->add_scoring($add_scoring);

                            $this->response['success'] = 1;
                            
                            
                        break;

                        case 'scorista':
                            
                            if (isset($_SESSION['manager_id'])) {
                                $manager = $this->managers->get_manager((int)$_SESSION['manager_id']);
                            } 
                            
                            if (empty($manager)){
                                header("Content-type: application/json; charset=UTF-8");
                                echo json_encode(['error' => 'не получилось']);
                                exit;
                            }
                            
                            $all_scorings = $this->scorings->get_scorings(['order_id'=>$order->order_id]);
                            
                            $this->response['all_scorings'] = $all_scorings;
                            
                            $have_fail_scoring = 0;
                            foreach ($all_scorings as $scor)
                            {
                                if (!($scor->status == $this->scorings::STATUS_ERROR || ($scor->status == $this->scorings::STATUS_COMPLETED && $scor->success == 1)))
                                {
                                    $have_fail_scoring = 1;
                                }
                                    
                            }
                            
                            $last_scoring = $this->scorings->get_last_type_scoring($this->scorings::TYPE_SCORISTA, $order->user_id);

                            if ($this->request->get('important') || (!empty($last_scoring) && $last_scoring->status == $this->scorings::STATUS_ERROR && $last_scoring->string_result == 'Ошибка скористы'))
                            {

                                    $add_scoring = array(
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->order_id,
                                        'type' => $type_id,
                                        'status' => $this->scorings::STATUS_NEW,
                                        'manual' => 2,
                                    );
                                    $scoring_id = $this->scorings->add_scoring($add_scoring);
                                    $this->add_scoring_manager($scoring_id, $manager->id);

                                    $this->response['success'] = 1;
                                    $this->response['block'] = 0;
                            }
                            elseif (!empty($have_fail_scoring))
                            {
                                $this->response['error'] = 'Не все скоринги были успешными!';
                                $this->response['block'] = 9;                                
                            }
                            elseif (!empty($last_scoring) && $last_scoring->status == $this->scorings::STATUS_ERROR && !empty($last_scoring->scorista_id))
                            {
                                $this->scorings->update_scoring($last_scoring->id, array('status' => $this->scorings::STATUS_IMPORT));
                                $this->response['success'] = 1;                                
                                $this->response['block'] = 1;
                            }
                            elseif (!empty($last_scoring) && (strtotime($last_scoring->created) > (time() - 86400 * 5)))
                            {
                                if (($last_scoring->status == $this->scorings::STATUS_COMPLETED && (empty($last_scoring->body) || $last_scoring->body == 'null') && !empty($last_scoring->scorista_id)))
                                {
                                    $this->scorings->update_scoring($last_scoring->id, array('status' => $this->scorings::STATUS_IMPORT));
                                    $this->response['success'] = 1;
                                    $this->response['block'] = 2;
                                }
                                elseif ($last_scoring->status == $this->scorings::STATUS_ERROR && $last_scoring->string_result == 'Истекло время ожидания')
                                {
                                    $this->scorings->update_scoring($last_scoring->id, array('status' => $this->scorings::STATUS_IMPORT));
                                    $this->response['success'] = 1;
                                    $this->response['block'] = 3;                                
                                }
                                elseif ($last_scoring->status == $this->scorings::STATUS_COMPLETED)
                                {
                                    $this->response['error'] = 'Скориста уже проводилась в этом месяце!';
                                    $this->response['block'] = 4;
                                }
                                elseif ($last_scoring->status == $this->scorings::STATUS_NEW || $last_scoring->status == $this->scorings::STATUS_PROCESS)
                                {
                                    $this->response['error'] = 'Скориста уже выполняется!';
                                    $this->response['block'] = 7;
                                }
                                else
                                {
                                    $add_scoring = array(
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->order_id,
                                        'type' => $type_id,
                                        'status' => $this->scorings::STATUS_NEW,
                                        'manual' => 1,
                                    );
                                    $scoring_id = $this->scorings->add_scoring($add_scoring);
                                    $this->add_scoring_manager($scoring_id, $manager->id);

                                    $add_scoring['type'] = $this->scorings::TYPE_AXILINK_2;
                                    $this->scorings->add_scoring($add_scoring);

                                    $this->response['success'] = 1;
                                    $this->response['block'] = 5;
                                    
                                }

                            }
                            else
                            {
                                $add_scoring = array(
                                    'user_id' => $order->user_id,
                                    'order_id' => $order->order_id,
                                    'type' => $type_id,
                                    'status' => $this->scorings::STATUS_NEW,
                                    'manual' => 1,
                                );
                                $scoring_id = $this->scorings->add_scoring($add_scoring);
                                $this->add_scoring_manager($scoring_id, $manager->id);

                                $add_scoring['type'] = $this->scorings::TYPE_AXILINK_2;
                                $this->scorings->add_scoring($add_scoring);

                                $this->response['success'] = 1;
                                $this->response['block'] = 6;
                            }
                            
                                                        
                        break;
                        
                    endswitch;
                }
                else
                {
                    $this->response['error'] = 'undefined_order';
                }
                
            break;
            
            case 'body':
                
                $id = $this->request->get('id', 'integer');
                $type = $this->request->get('type', 'string');
                
                $body = $this->scorings->get_scoring_body($id);
                
                switch ($type):
                    
                    case 'juicescore':
                        $body = unserialize($body);
                    break;
                    
                    case 'scorista':
                        $body = json_decode($body);
                        unset($body->equifaxCH);
                        
                        $html = '<tr class="collapse" id="scoring_'.$id.'">';
                        $html .= '';
                        $html .= '';
                        $html .= '';
                        $html .= '';
                        $html .= '';
                    break;
                    
                    case 'fssp':
                        $body = unserialize($body);
                    break;
                    
                    case 'blacklist':
                        $body = unserialize($body);

                        $html = '<tr class="collapse" id="scoring_'.$id.'">';
                        $html .= '<td colspan="6">';
                        if (empty($body))
                        {
                            $html .= 'Записей не найдено';
                        }
                        else
                        {
                            $html .= '<table class="table">';
                            foreach ($body as $key => $item)
                            {
                                $html .= '<tr>';
                                $html .= '<td>'.$item->created.'</td>';
                                $html .= '<td>'.$item->block.'</td>';
                                $html .= '<td>'.$item->text.'</td>';
                                $html .= '</tr>';
                            }
                            $html .= '</table>';
                        }
                        $html .= '</td>';
                        $html .= '</tr>';
                        
                        $this->html_output($html);
                        
                    break;
                    
                endswitch;
                
                
            break;
            
        endswitch;
    
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
    }
    
    private function html_output($html)
    {
        header('Contect-type: text/html');
        echo $html;
        exit;
    }
    
    private function add_scoring_manager($scoring_id, $manager_id)
    {
        $this->db->query('
            INSERT INTO s_scoring_manager
            SET scoring_id = ?, manager_id = ?
        ', $scoring_id, $manager_id);
    }

    /**
     * Если по заявке нельзя запустить скористу, так как по заявке нельзя запрашивать отчеты
     * (в результате смены организации), то запрещаем добавление скористы и просим добавить акси
     */
    private function checkCanAddScorista(int $orderId)
    {
        $axiWithoutCreditReports = $this->order_data->read($orderId, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);

        if (!empty($axiWithoutCreditReports)) {
            echo json_encode(['error' => 'По заявке нельзя запустить скоринг Скориста. Запустите скоринг акси']);
            die();
        }
    }
}

$app = new RunScoringsApp();
$app->run();