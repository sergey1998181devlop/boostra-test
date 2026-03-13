<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

chdir('..');

require_once 'api/Simpla.php';
$simpla = new Simpla();

$response = array();

if (!empty($_SESSION['user_id']))
{
    $user = $simpla->users->get_user((int)$_SESSION['user_id']);
    if (empty($user))
    {
        $response['error'] = 'UNKNOWN_USER';
    }
    else
    {
        $card_type = $simpla->request->get('card_type', 'string');
        if ($card_type == 'b2p')
        {
            if ($card_detach = $simpla->request->get('card_detach'))
            {
                $simpla->best2pay->update_card($card_detach, [
                    'autodebit' => 0,
                    'autodebit_changed_at' => date('Y-m-d H:i:s')
                ]);
                $response['success'] = 'CARD DETACHED';
            }

            if ($card_attach = $simpla->request->get('card_attach'))
            {
                $simpla->best2pay->update_card($card_attach, [
                    'autodebit' => 1,
                    'autodebit_changed_at' => date('Y-m-d H:i:s')
                ]);
                $response['success'] = 'CARD ATTACHED';
            }
            
        }
        else
        {
            if ($card_detach = $simpla->request->get('card_detach'))
            {
                $simpla->soap->auto_debiting($user->uid, $card_detach, 0);
                $response['success'] = 'CARD DETACHED';
            }
            
            if ($card_attach = $simpla->request->get('card_attach'))
            {
                $simpla->soap->auto_debiting($user->uid, $card_attach, 1);
                $response['success'] = 'CARD ATTACHED';
            }
            
        }
        
        
        
    }
}
else
{
    $response['error'] = 'UNDEFINED_USER';
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($response);
