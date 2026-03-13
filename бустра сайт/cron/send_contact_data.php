<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
date_default_timezone_set('Europe/Samara');

require_once dirname(__DIR__) . '/api/Simpla.php';

$simpla = new Simpla();


$emails = array(
//    'alpex-s@rambler.ru',
    'userlog@boostra.ru',
    'kolgotin_vi@akticom.ru'
);




$docs = $simpla->docs->get_docs(array('in_register'=>1, 'visible'=>1));


$report_date = date('Y-m-d', time()-3*86400);

$query = $simpla->db->placehold("
    SELECT *
    FROM __users AS u
    WHERE DATE(u.created) = ?
", $report_date);
$simpla->db->query($query);

$results = $simpla->db->results();

$subject = 'Отчет по клиентам зарегистрированым '.$report_date;

$message = '<html>';
$message .= '<head>';
$message .= '<meta charaset="utf8">';
$message .= '</head>';
$message .= '<body>';
$message .= '<h1>Отчет по клиентам зарегистрированым '.$report_date.'</h1>';
$message .= '<table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">';
$message .= '<thead>';
$message .= '<tr>';
$message .= '<th width="15%">ФИО</th>';
$message .= '<th width="15%">Телефон</th>';
$message .= '<th width="30%">Паспортные данные</th>';
$message .= '<th width="10%">СМС код</th>';
$message .= '<th width="30%">Документы</th>';
$message .= '</tr>';
$message .= '</thead>';
$message .= '<tbody>';
foreach ($results as $item)
{
    $message .= '<tr>';

    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->lastname.' '.$item->firstname.' '.$item->patronymic.'</td>';
    
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->phone_mobile.'</td>';
    
    $message .= '<td valign="top" style="border: 1px solid #333;">';

    $message .= '<p><span>Серия и номер паспорта: </span> <strong>'.$item->passport_serial.'</strong></p>';
    $message .= '<p><span>Дата выдачи: </span> <strong>'.$item->passport_date.'</strong></p>';
    $message .= '<p><span>Код подразделения: </span> <strong>'.$item->subdivision_code.'</strong></p>';
    $message .= '<p><span>Кем выдан: </span> <strong>'.$item->passport_issued.'</strong></p>';

    $message .= '<p><span>Дата рождения: </span> <strong>'.$item->birth.'</strong></p>';
    $message .= '<p><span>Место рождения: </span> <strong>'.$item->birth_place.'</strong></p>';
    if (!empty($item->Regregion))
        $message .= '<p><span>Адрес прописки: </span> <strong>'.$item->Regregion.', '.$item->Regcity.', '.$item->Regstreet.', д.'.$item->Reghousing.(empty($item->Regbuilding) ? '' : '/'.$item->Regbuilding).' '.(empty($item->Regroom) ? '' : ' кв.'.$item->Regroom).'</strong></p>';
    if (!empty($item->Faktregion))
        $message .= '<p><span>Адрес Проживания: </span> <strong>'.$item->Faktregion.', '.$item->Faktcity.', '.$item->Faktstreet.', д.'.$item->Fakthousing.(empty($item->Faktbuilding) ? '' : '/'.$item->Faktbuilding).' '.(empty($item->Faktroom) ? '' : ' кв.'.$item->Faktroom).'</strong></p>';

    $message .= '</td>';
    
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->sms.'</td>';

    $message .= '<td valign="top" style="border: 1px solid #333;">';
    $message .= '<table  cellpadding="6" cellspacing="0" style="border-collapse: collapse;">';
    $message .= '<tbody>';
    foreach ($docs as $doc)
    {
        $message .= '<tr>';
        $message .= '<td style="border-bottom: 1px dotted #999;">'.$doc->name.'</td>';
        $message .= '<td style="border-bottom: 1px dotted #999;">'.date('d.m.y H:i:s', strtotime($item->created)).'</td>';
        $message .= '</tr>';
    }
    $message .= '</tbody>';
    $message .= '</table>';
    $message .= '</td>';

    $message .= '</tr>';
}
$message .= '</tbody>';
$message .= '</table>';
$message .= '</body>';

foreach ($emails as $email)
    $simpla->notify->email($email, $subject, $message, $simpla->settings->notify_from_email);

echo $message;






// Отправка клиентов не завершивших регистрацию
$simpla->db->query("
    SELECT 
    	id,
    	created,
    	lastname,
    	firstname,
    	patronymic,
    	birth,
    	phone_mobile,
    	email,
    	first_loan_amount AS summ,
    	first_loan_period AS period,
    	personal_data_added,
    	additional_data_added,
        files_added,
        card_added,
        passport_serial,
    	passport_date,
        passport_issued,
        subdivision_code,
    	Regindex,
    	Regregion,
    	Regregion_shorttype,
    	Regcity,
    	Regcity_shorttype,
    	Regstreet,
    	Regstreet_shorttype,
    	Reghousing,
    	Regbuilding,
    	Regroom
    FROM `s_users` 
    WHERE card_added != 1 
    AND DATE(created) = ?
    ORDER BY `s_users`.`created` ASC
", $report_date);
$results = $simpla->db->results();

$message = '<html>';
$message .= '<head>';
$message .= '<meta charaset="utf8">';
$message .= '</head>';
$message .= '<body>';
$message .= '<h1>Отчет по клиентам не завершившим регистрацию '.$report_date.'</h1>';

$message .= '<table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">';
$message .= '<thead>';
$message .= '<tr>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">ID <br />Дата</th>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">ДР <br />ФИО</th>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">Телефон <br />Email</th>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">Сумма <br />Срок</th>';
$message .= '<th width="20%" style="border: 1px solid #333;background:#333;color:#fff">Этапы</th>';
$message .= '<th width="20%" style="border: 1px solid #333;background:#333;color:#fff">Паспорт</th>';
$message .= '<th width="20%" style="border: 1px solid #333;background:#333;color:#fff">Адрес</th>';
$message .= '</tr>';
$message .= '</thead>';
$message .= '<tbody>';

foreach ($results as $item):
    $message .= '<tr>';
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->id.'<br />'.$item->created.'</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->birth.'<br />'.$item->lastname.' '.$item->firstname.' '.$item->patronymic.'</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->phone_mobile.'<br />'.$item->email.'</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">'.$item->summ.' руб<br />Дней: '.$item->period.'</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">';
    if (!empty($item->personal_data_added))     
        $message .= 'Персональные данные добавлены<br />';    
    if (!empty($item->additional_data_added))     
        $message .= 'Доп. информация добавлена<br />';    
    if (!empty($item->files_added))     
        $message .= 'Файлы загружены<br />';    
    if (!empty($item->card_added))     
        $message .= 'Карта привязана';    
    $message .= '</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">';
    if (!empty($item->passport_serial))
        $message .= $item->passport_serial.' от '.$item->passport_date.'<br />'.$item->passport_issued.'<br />'.$item->subdivision_code;
    $message .= '</td>';
    $message .= '<td valign="top" style="border: 1px solid #333;">';
    if (!empty($item->Regindex))     
        $message .= $item->Regindex.', ';
    if (!empty($item->Regregion))
        $message .= $item->Regregion.' '.$item->Regregion_shorttype.', ';
    if (!empty($item->Regcity))
        $message .= $item->Regcity_shorttype.' '.$item->Regcity.'., ';
    if (!empty($item->Regstreet))
        $message .= $item->Regstreet_shorttype.'. '.$item->Regstreet.', ';
    if (!empty($item->Reghousing))
        $message .= 'д. '.$item->Reghousing;
    if (!empty($item->Regbuilding)) 
        $message .= ', стр. '.$item->Regbuilding;
    if (!empty($item->Regroom)) 
        $message .= ', кв. '.$item->Regroom;
    $message .= '</td>';
    $message .= '</tr>';
endforeach;

$message .= '</tbody>';
$message .= '</table>';

$message .= '</body>';
$message .= '</html>';

$subject = 'Отчет по клиентам не завершившим регистрацию '.$report_date;

foreach ($emails as $email)
    $simpla->notify->email($email, $subject, $message, $simpla->settings->notify_from_email);

echo $message;



// разгоншики

$query = $simpla->db->placehold("
    SELECT 
        u.id,
        u.loan_history,
        u.lastname,
        u.firstname,
        u.patronymic,
        u.phone_mobile,
        u.birth,
        u.uid
    FROM __users AS u
    LEFT JOIN __orders AS o
    ON o.user_id = u.id
    WHERE o.razgon = 1
    AND DATE(o.date) = ?
", $report_date);
$simpla->db->query($query);

$results = $simpla->db->results();


$message = '<html>';
$message .= '<head>';
$message .= '<meta charaset="utf8">';
$message .= '</head>';
$message .= '<body>';
$message .= '<h1>Отчет по клиентам-разгонщикам за '.$report_date.'</h1>';

$message .= '<table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">';
$message .= '<thead>';
$message .= '<tr>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">ФИО</th>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">Телефон</th>';
$message .= '<th width="10%" style="border: 1px solid #333;background:#333;color:#fff">ДР</th>';
$message .= '</tr>';
$message .= '</thead>';
$message .= '<tbody>';

foreach ($results as $r):
    $credits_history = $simpla->soap->get_user_credits((string)$r->uid);
    $simpla->users->save_loan_history($r->id, $credits_history);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($credits_history);echo '</pre><hr />';
    $message .= '<tr>';
    $message .= '<td rowspan="2" style="border: 1px solid #333;">';
    $message .= '<a href="http://manager.boostra.ru/client/'.$r->id.'">'.$r->lastname.' '.$r->firstname.' '.$r->patronymic.'</a>';
    $message .= '</td>';
    $message .= '<td style="border: 1px solid #333;">'.$r->phone_mobile.'</td>';
    $message .= '<td style="border: 1px solid #333;">'.$r->birth.'</td>';
    $message .= '</tr>';
    $message .= '<tr>';
    $message .= '<td colspan="2">';
    if (is_object($credits_history)):
    $message .= '<table width="100%" cellpadding="3" cellspacing="0" style="border-collapse: collapse;">';
    $message .= '<tr>';
    $message .= '<th style="border: 1px solid #333;background:#efefef;">Номер</th>';
    $message .= '<th style="border: 1px solid #333;background:#efefef;">Открыт</th>';
    $message .= '<th style="border: 1px solid #333;background:#efefef;">Закрыт</th>';
    $message .= '<th style="border: 1px solid #333;background:#efefef;">Сумма</th>';
    $message .= '</tr>';
    
    foreach ($credits_history as $lh):
        $message .= '<tr>';
        $message .= '<td style="border: 1px solid #333;">'.$lh->number.'</td>';
        $message .= '<td style="border: 1px solid #333;">'.date('d.m.Y', strtotime($lh->date)).'</td>';
        $message .= '<td style="border: 1px solid #333;">'.date('d.m.Y', strtotime($lh->close_date)).'</td>';
        $message .= '<td style="border: 1px solid #333;">'.$lh->amount.'</td>';
        $message .= '</tr>';
    endforeach;
    
    $message .= '</table>';
    else:
    $message .= 'Не удалось получить историю';    
    endif;
    $message .= '</td>';
    $message .= '</tr>';
endforeach;

$message .= '</tbody>';
$message .= '</table>';

$message .= '</body>';
$message .= '</html>';

$subject = 'Отчет по клиентам-разгонщикам за '.$report_date;

foreach ($emails as $email)
    $simpla->notify->email($email, $subject, $message, $simpla->settings->notify_from_email);

echo $message;