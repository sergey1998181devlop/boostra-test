<?php
require_once('../api/Simpla.php');
$simpla = new Simpla();

$phone = $simpla->request->post('phone');
$sms = $simpla->request->post('sms');



$result = array();

if ( ! session_id() ) @ session_start();

//print_r($sms.' ');
//print_r($_SESSION['sms'].' ');
//print_r($phone.' ');
//print_r($_SESSION['phone'].' ');

if (($_SESSION['sms'] == $sms) && ($phone == $_SESSION['phone'])) 
{
        if (empty($_SERVER['HTTP_REFERER']))
        {
            $result['error'] = 'Обновите страницу и попробуйте еще раз';
            $result['user'] = false;
        }
        else
        {
            $soap = $simpla->soap->get_uid_by_phone($phone);
            if (!empty($soap->result) && !empty($soap->uid))
            {
                $query = $simpla->db->placehold("
                    SELECT id
                    FROM __users
                    WHERE UID = ?
                ", $soap->uid);
                $simpla->db->query($query);
                
                if (!($user_id = $simpla->db->result('id')))
                {
                    
                    $expl = explode(' ', $soap->client);
                    $lastname = isset($expl[0]) ? mb_convert_case($expl[0], MB_CASE_TITLE) : '';
                    $firstname = isset($expl[1]) ? mb_convert_case($expl[1], MB_CASE_TITLE) : '';
                    $patronymic = isset($expl[2]) ? mb_convert_case($expl[2], MB_CASE_TITLE) : '';
    
                    $user_id = $simpla->users->add_user(array(
                        'UID' => $soap->uid,
                        'UID_status' => "ok",
                        'phone_mobile' => $phone,
                        'lastname' => $lastname,
                        'firstname' => $firstname,
                        'patronymic' => $patronymic,
                        'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
        				'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                        'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                        'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                        'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                        'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                        'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
                        'enabled' => 1,
                        'sms' => $_SESSION['sms'],
                        'last_ip'=>$_SERVER['REMOTE_ADDR'],
                    ));
                }
                
                $_SESSION['user_id'] = $user_id;
                            
                $result['user'] = true;
                $result['user_id'] = $user_id;
            }
        }
        elseif ($soap->error == 'Множество совпадений')
        {
            $simpla->soap->send_doubling_phone($phone);
            
            $result['user'] = false;
            $result['error'] = 'blocked';
        }
        else
        {
            $result['user'] = false;
        }
        
//$result['soap'] = $soap;

	$result['sms'] = $_SESSION['sms'];
	//$_SESSION['phone']['approved'] = true;
}
else
{
//    $result['session_sms'] = $_SESSION['sms'];
    $result['sms'] = $sms;
//    $result['session_phone'] = $_SESSION['phone'];
    $result['phone'] = $phone;
    $result['error'] = 'different';
}
session_write_close();

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		
echo json_encode($result);