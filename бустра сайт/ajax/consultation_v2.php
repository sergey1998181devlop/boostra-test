<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

chdir('..');

require_once 'api/Simpla.php';
$simpla = new Simpla();

$response = array();
if ($simpla->request->method('post'))
{
    $user_id = $simpla->request->post('user_id', 'integer');
    
    if (!($fio = $simpla->request->post('fio')))
    {
        $response['error'] = 'Укажите Ваше Имя';
    }
    elseif (!($phone = $simpla->request->post('phone')))
    {
        $response['error'] = 'Укажите контактный номер телефона';
    }
    elseif ($user = $simpla->users->get_user($user_id))
    {
        $message = '<html>';
        $message .= '<body>';
        $message .= '<h1>Заявка на финансовую консультацию с сайта boostra.ru</h1>';
        $message .= '<p style="margin:20px 0 10px 0;">Данные клиента</p>';
        $message .= '<table cellpadding="6" cellspacing="0" width="500" style="border-collapse: collapse;">';
        $message .= '<tr>';
        $message .= '<td style="padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">ФИО</td>';
        $message .= '<td style="padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">'.$fio.'</td>';
        $message .= '</tr>';
        $message .= '<tr>';
        $message .= '<td style="padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">Телефон</td>';
        $message .= '<td style="padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">'.$phone.'</td>';
        $message .= '</tr>';
        $message .= '</table>';
        
        $message .= '</body>';
        $message .= '</html>';
    
        $subject = 'Заявка на финансовую консультацию с сайта boostra.ru';
        
        
        $emails = array(
            'alpex-s@rambler.ru',
            'reports@boostra.ru', 
            'dstsurist@gmail.com',
            'managerdsts@gmail.com',
        );
        
        foreach ($emails as $email)
            $simpla->notify->email($email, $subject, $message);
//        $simpla->notify->email('alpex-s@rambler.ru', $subject, $message);

        setcookie('consultation_send', 1, time() + 86400*365, '/', 'boostra.ru');
        
        $response['success'] = 1;
    }
    else
    {
        $response['error'] = 'USER NOT FOUND';
    }
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");		

echo json_encode($response);