<?php

require_once 'Simpla.php';

class Anticaptcha extends Simpla
{
    private $api_key = '';
    private $api_url = 'https://api.anti-captcha.com/';
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->api_key = $this->settings->apikeys['anticaptcha']['api_key'];
    }
    
    
    public function create_task($filename)
    {
        $task = new StdClass();
        $task->type = "ImageToTextTask";
        $task->body = base64_encode(file_get_contents($filename));
        $data = array(
            'task' => $task,
        );
    	$resp = $this->send('createTask', $data);
        if (empty($resp->errorId))
        {
            return $resp->taskId;
        }
        else
        {
            return false;
        }
        
    }
    
    public function get_task_result($task_id)
    {
    	$resp = $this->send('getTaskResult', array('taskId'=>$task_id));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';    
        return $resp;
    }
    
    
    public function send($method, $data)
    {
    	$this->error = null;
        
        $data['clientKey'] = $this->api_key;
        
        $url = $this->api_url. $method;
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
        $postDataEncoded = json_encode($data);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',     
            'Accept: application/json',     
            'Content-Length: ' . strlen($postDataEncoded) 
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,30);
        
        $json = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($json);        
        
        return $result;
    }
    
    
    
}