<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");	

chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();

switch ($simpla->request->get('action', 'string')):
    
    case 'create':
        
        $order_id = $simpla->request->get('order_id', 'integer');
        $order = $simpla->orders->get_order((int)$order_id);
        
        $result = $simpla->scorista->create($order_id);
        
        if ($result->status == 'OK')
        {
            $scoring = array(
                'user_id' => $order->user_id,
                'type' => $simpla->scorings::TYPE_SCORISTA,
                'body' => '',
                'success' => 0,
                'scorista_id' => $result->requestid,
            );
            $simpla->scorings->log_add_scoring('ajax/scorista.php', $scoring);
            $simpla->scorings->add_scoring($scoring);
        }
        
    break;
    
    case 'result':
        
        $request_id = $simpla->request->get('request_id');
            
        $scorista = $simpla->scorings->get_scorista_organization($request_id);        
        $result = $simpla->scorista->get_result($request_id, $scorista->organization_id);
        
        if ($result->status == 'DONE')
        {
            $scoring = array(
                'body' => json_encode($result->data),
                'success' => $result->data->additional->summary->score > 650,
                'scorista_status' => $result->data->decision->decisionName,
                'scorista_ball' => $result->data->additional->summary->score,
            );
            $simpla->scorings->update_scoring($scorista->scoring_id, $scoring);
        }
        
    break;

    // получим тело скористы для заявки
    case 'get_body_order_view':
        $id = $simpla->request->post('id', 'integer');
        $table_name = $simpla->request->post('table_name', 'string');

        $scoring = $simpla->scorings->get_scoring($id, $table_name);
        $scoring->body = json_decode($scoring->body);

        $simpla->design->assign('scoring', $scoring);
        $response =  $simpla->design->fetch('html_blocks/scorista_body_order.tpl');

        $simpla->response->html_output($response);
        break;
    
    default:
        
        $result = new StdClass();
        $result->error = 'undefined_action';
    
endswitch;

echo json_encode($result);