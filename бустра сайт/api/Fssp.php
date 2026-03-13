<?php

require_once 'Simpla.php';

class Fssp extends Simpla
{
    private $api_url = 'https://api-ip.fssp.gov.ru/api/v1.0/';
    private $api_key = '';
    
    private $error = null;
    
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->api_key = $this->settings->apikeys['fssp']['api_key'];
    }
    
    
    public function create_task($data)
    {
        $resp = $this->fssp->send('search/physical', $data);
        
        return ($resp);
    }
    
    public function check_task($task_id)
    {
        $resp = $this->fssp->send('status', array('task' => $task_id));
        
        return $resp;
    }
    
    public function get_task($task_id)
    {
        $resp = $this->fssp->send('result', array('task' => $task_id));
        
        return $resp;    	
    }
    
    public function get_error()
    {
    	return $this->error;
    }
    
    
    public function send($method, $data)
    {
    	$this->error = null;
        
        $data['token'] = $this->api_key;
        
        $url = $this->api_url. $method . '?' . http_build_query($data);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $json = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($json);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';
        if ($result->status != 'success')
        {
            $this->error = $result;
            return false;
        }
        
        return $result->response;
    }
    
    public function get_code($region_name)
    {
// Ненецкий
// 
        $codes = array(
            1 => "адыгея",
            2 => "башкортостан",
            3 => "бурятия",
            4 => "алтай",
            5 => "дагестан",
            6 => "ингушетия",
            7 => "кабардино-балкарская",
            8 => "калмыкия",
            9 => "карачаево-черкесская",
            10 => "карелия",
            11 => "коми",
            12 => "марий эл",
            13 => "мордовия",
            14 => "саха /якутия/",
            15 => "северная осетия - алания",
            16 => "татарстан",
            17 => "тыва",
            18 => "удмуртская",
            19 => "хакасия",
            20 => "чеченская",
            21 => "чувашская",
            22 => "алтайский",
            23 => "краснодарский",
            24 => "красноярский",
            25 => "приморский",
            26 => "ставропольский",
            27 => "хабаровский", 
            28 => "амурская",
            29 => "архангельская",
            30 => "астраханская",
            31 => "белгородская",
            32 => "брянская",
            33 => "владимирская",
            34 => "волгоградская",
            35 => "вологодская",
            36 => "воронежская",
            37 => "ивановская",
            38 => "иркутская",
            39 => "калининградская",
            40 => "калужская",
            41 => "камчатский",
            42 => "кемеровская",
            43 => "кировская",
            44 => "костромская",
            45 => "курганская",
            46 => "курская",
            47 => "ленинградская",
            48 => "липецкая",
            49 => "магаданская",
            50 => "московская ",
            51 => "мурманская",
            52 => "нижегородская",
            53 => "новгородская",
            54 => "новосибирская",
            55 => "омская",
            56 => "оренбургская",
            57 => "орловская",
            58 => "пензенская",
            59 => "пермский",
            60 => "псковская",
            61 => "ростовская",
            62 => "рязанская",
            63 => "самарская",
            64 => "саратовская",
            65 => "сахалинская",
            66 => "свердловская",
            67 => "смоленская",
            68 => "тамбовская",
            69 => "тверская",
            70 => "томская",
            71 => "тульская",
            72 => "тюменская",
            73 => "ульяновская",
            74 => "челябинская",
            75 => "забайкальский",
            76 => "ярославская",
            77 => "москва",
            78 => "санкт-петербург",
            82 => "крым",
            86 => "ханты-мансийский автономный округ - югра",
            87 => "чукотский",
            89 => "ямало-ненецкий",
            92 => "севастополь",
        );
        
        $index = array_search(mb_strtolower($region_name, 'utf8'), $codes);
        
        if (mb_strtolower($region_name, 'utf8') == 'еврейская')
            $index = 27;
        if (mb_strtolower($region_name, 'utf8') == 'ненецкий')
            $index = 29;
        
        return $index;            
    }
    
}