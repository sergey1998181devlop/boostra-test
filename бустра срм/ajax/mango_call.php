<?php

error_reporting(0);
ini_set('display_errors', 'Off');

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

session_start();
chdir('..');

require 'api/Simpla.php';

$simpla = new Simpla();

$response = array();

$phone = $simpla->request->get('phone');
//$phone = '79276053000';

if(isset($_SESSION['manager_id']))
{
	$manager = $simpla->managers->get_manager(intval($_SESSION['manager_id']));
}

if (empty($manager))
{
    $response['error'] = 'unknown_manager';
}
else
{
    if (empty($manager->mango_number))
    {
        $response['error'] = 'empty_mango';
    }
    else
    {
        if ($order_id = $simpla->request->get('order_id', 'integer'))
        {
            if ($order = $simpla->orders->get_order($order_id))
            {
                if (empty($order->call_date))
                {
                    $simpla->orders->update_order($order_id, array('call_date' => date('Y-m-d H:i:s')));
                }
            }
        }
        
        $user_id = $simpla->request->get('user_id', 'integer');
        
        $params = array(
            'manager_id' => $manager->id,
            'order_id' => empty($order_id) ? 0 : $order_id,
            'user_id' => empty($user_id) ? 0 : $user_id,
        );
        
        $response['success'] = json_decode($simpla->mango->call($phone, $manager->mango_number, $params));
    }
}

echo json_encode($response);


