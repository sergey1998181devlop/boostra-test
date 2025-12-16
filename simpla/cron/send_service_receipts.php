<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();
date_default_timezone_set('Europe/Samara');

require_once(dirname(__DIR__, 2) . '/api/Simpla.php');

$simpla = new Simpla();

try
{
    $items = $simpla->soap->get_service_clients();
    
    if (!empty($items))
    {
        foreach ($items as $item)
            $simpla->cloudkassir->send_receipt($item);
    }
}
catch (Exception $e)
{
    $message = $e->getMessage();
    $trace = $e->getTraceAsString();
    
    
    
    $simpla->notify->email('alpex-s@rambler.ru', 'Ошибка в чеках', $message.'<br />'.$trace, $simpla->settings->notify_from_email);
}
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($items);echo '</pre><hr />';

try
{
    $items = $simpla->soap->get_service_return_clients();
    
    if (!empty($items))
    {
        foreach ($items as $item)
        {
            $res = $simpla->cloudkassir->send_receipt($item);
            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($res);echo '</pre><hr />';
        }
    }
}
catch (Exception $e)
{
    $message = $e->getMessage();
    $trace = $e->getTraceAsString();
    
    
    
    $simpla->notify->email('alpex-s@rambler.ru', 'Ошибка в чеках', $message.'<br />'.$trace, $simpla->settings->notify_from_email);
}

echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($items);echo '</pre><hr />';


$ip_list = [
    'IP', 'ST', 'PO', 'T', 'AL'
]; 

foreach ($ip_list as $ip)
{
    try
    {
        $items = $simpla->soap->get_service_clients($ip);
        
        if (!empty($items))
        {
            foreach ($items as $item)
            {
                $res = $simpla->cloudkassir->send_receipt_lagutkin($item, strtolower($ip));
            }
        }
    }
    catch (Exception $e)
    {
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();

        $simpla->notify->email('alpex-s@rambler.ru', 'Ошибка в чеках '.$ip, $message.'<br />'.$trace, $simpla->settings->notify_from_email);
    }
    
}