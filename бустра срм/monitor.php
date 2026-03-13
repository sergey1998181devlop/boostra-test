<?php
error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

define('ROOT', __DIR__);


$access_ip_list = [
    '193.176.87.139', // @rkopyl
    '176.222.54.6', // @azayanchkovskiy
    '51.250.100.251', // Zabbix
];

// Засекаем время.
$time_start = microtime(true);

session_start();

if (in_array($_SERVER["REMOTE_ADDR"], $access_ip_list)) {
    $_SESSION['manager_id'] = 4;
    $_SESSION['manager_ip'] = $_SERVER['REMOTE_ADDR'];    
} else {
    unset($_SESSION['manager_id'], $_SESSION['manager_ip']);
    exit;
}


try 
{
    
    require_once('view/IndexView.php');
    
    $view = new IndexView();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($view);echo '</pre><hr />';    
    // Если все хорошо
    if(($res = $view->fetch()) !== false)
    {
        if ($res == 403)
        {
            header("http/1.0 403 Forbidden");
        	$_GET['page_url'] = '403';
        	$_GET['module'] = 'PageView';
        	print $view->fetch();   
        }
        else
        {
        	// Выводим результат
        	header("Content-type: text/html; charset=UTF-8");	
        	print $res;
        
        	// Сохраняем последнюю просмотренную страницу в переменной $_SESSION['last_visited_page']
        	if(empty($_SESSION['last_visited_page']) || empty($_SESSION['current_page']) || $_SERVER['REQUEST_URI'] !== $_SESSION['current_page'])
        	{
        		if(!empty($_SESSION['current_page']) && !empty($_SESSION['last_visited_page']) && $_SESSION['last_visited_page'] !== $_SESSION['current_page'])
        			$_SESSION['last_visited_page'] = $_SESSION['current_page'];
        		$_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
        	}		
        }
    }
    else 
    { 
    	// Иначе страница об ошибке
    	header("http/1.0 404 not found");
    	
    	// Подменим переменную GET, чтобы вывести страницу 404
    	$_GET['page_url'] = '404';
    	$_GET['module'] = 'PageView';
    	print $view->fetch();   
    }
}
catch (Exception $e)
{
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($e);echo '</pre><hr />'; 
}

// Отладочная информация
if(1)
{
	print "<!--\r\n";
	$time_end = microtime(true);
	$exec_time = $time_end-$time_start;
  
  	if(function_exists('memory_get_peak_usage'))
		print "memory peak usage: ".memory_get_peak_usage()." bytes\r\n";  
	print "page generation time: ".$exec_time." seconds\r\n";  
	print "-->";
}
