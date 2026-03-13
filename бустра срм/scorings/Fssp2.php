<?php

class Fssp2 extends Simpla
{
    private $api_url = 'http://85.236.173.222:9010/api/fssp/';
    private $api_key = '';
    
    private $error = null;
    
    private $user_id;
    private $order_id;
    
        
    public function __construct()
    {
    	parent::__construct();
        
//        $this->api_key = $this->settings->apikeys['fssp2']['api_key'];
        $this->api_key = 'bd670bd16b7fa0dccfb113eb3108e923f5a8915c';
    }

    
    /**
     * Fssp2::create_task()
     * 
    "data": [                             # Список информации о людях для проверки
        {
           "last_name": "str",            # Фамилия
           "first_name": "str",           # Имя
           "patronymic": "str",           # Отчество
           "birthdate": "d.m.Y"           # Дата рождения
        },
        {
            "last_name": "str",
            "first_name": "str",
            "patronymic": "str",
            "birthdate": "d.m.Y"
        },
    ]                      
     * 
     * @param mixed $data
     * @return
     */
    public function create_task($data)
    {
        $url = $this->api_url.'createTask';
        
        $content = new StdClass();
        $content->api_key = $this->api_key;
        $content->data = $data;
        
        $json = json_encode($content);
        
            
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response = json_decode($json_response, true);

        return $response;
    }
    
    public function check_task($task_id)
    {
        $resp = $this->fssp->send('status', array('task' => $task_id));
        
        return $resp;
    }
    
    public function get_task($session_id, $token)
    {
        
        $args = array(
            'session_id' => $session_id,
            'token' => $token
        );

        $url = $this->api_url.'/getTaskResult?'. http_build_query($args);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $json = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($json);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($session_id, $token, $result);echo '</pre><hr />';

        return $result;    	
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $json = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($json);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($json, $result);echo '</pre><hr />';
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
            50 => "московская",
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