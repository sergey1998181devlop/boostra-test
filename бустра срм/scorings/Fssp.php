<?php

class Fssp extends Simpla
{
    private $api_url = 'https://api-ip.fssp.gov.ru/api/v1.0/';
    
    private $keys = array(
        array(
            'fssp_key' => 'BG5w2ztVLEfb',
            'proxy' => '185.148.27.145:8000',
            'auth' => 'KUoLnb:EXRvow',
        ),
        array(
            'fssp_key' => 'Kp896IDoNAsK',
            'proxy' => '194.67.209.63:8000',
            'auth' => 'KUoLnb:EXRvow',
        ),
        array(
            'fssp_key' => 'juDtGSK6XUfb',
            'proxy' => '193.124.187.3:8000',
            'auth' => 'KUoLnb:EXRvow',
        ),
        array(
            'fssp_key' => 'ZcJQIQtj4xpC',
            'proxy' => '193.124.183.98:8000',
            'auth' => 'KUoLnb:EXRvow',
        ),
        /*
        array(
            'fssp_key' => 'hitrWj8fLR1d',
            'proxy' => '45.10.82.72:8000',
            'auth' => 'KUoLnb:EXRvow',
        ),
*/
    );
    
    public function __construct()
    {
    	parent::__construct();        
    }

    public function run_scoring(int $scoring_id)
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_FSSP);
        if (empty($scoringType->active)) {
            return $this->scorings->update_scoring($scoring_id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Проверка на стороне СРМ отключена',
                'end_date' => date('Y-m-d H:i:s')
            ]);
        }

        $scoring = $this->scorings->get_scoring($scoring_id);

        if (empty($scoring)) {
            return;
        }

        $order = $this->orders->get_order((int)$scoring->order_id);

        if (empty($order)) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'не найдена заявка'
            ]);
            return;
        }

        $this->scorings->update_scoring($scoring->id, [
            'status' => $this->scorings::STATUS_WAIT,
            'string_result' => 'Идет проверка...'
        ]);

        if (
            empty($order->lastname) ||
            empty($order->firstname) ||
            empty($order->patronymic) ||
            empty($order->birth)
        ) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'в заявке не достаточно данных для проведения скоринга'
            ]);
            return;
        }

        $data = array(
            'middle' => $order->patronymic, // отчество
            'first' => $order->firstname, // имя
            'paternal' => $order->lastname, // фамилия
            'birthDt' => $order->birth,
        );

        $result = $this->infosphere->check_fssp($data);

        if (empty($result) || isset($result['Source']['Error'])) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'body' => serialize($result),
                'string_result' => $result['Source']['Error']
            ]);
            return;
        }

        $badArticle = [];
        $maxExp = $this->scorings->get_type($this->scorings::TYPE_FSSP);
        $maxExp = $maxExp->params;
        $maxExp = $maxExp['amount'];
        $expSum = [];

        $update = [
            'status' => $this->scorings::STATUS_COMPLETED,
            'body' => serialize($result),
            'success' => 1,
            'string_result' => 'Долгов нет',
            'end_date' => date('Y-m-d H:i:s'),
        ];

        $source = isset($result['Source']['@attributes']) ? $result['Source'] : reset($result['Source']);
        if (($source['ResultsCount'] ?? 0) > 0) {
            foreach ($source['Record'] as $key => $record) {
                $record_info = [];
                $record_source = ($key === 'Field' ? $record : $record['Field']);
                foreach ($record_source as $field) {
                    $record_info[$field['FieldName']] = $field['FieldValue'];
                }
                if (isset($record_info['CloseReason1']) && in_array($record_info['CloseReason1'], [46, 47])) {
                    $badArticle[$record_info['DocNumber']] = $record_info['CloseReason'];
                } else {
                    $expSum[$record_info['DocNumber']] = (float)$record_info['Total'];
                }
            }
        }

        $totalSum = array_sum($expSum);
        if ($totalSum > 0) {
            $update['string_result'] = 'Сумма долга: ' . $totalSum;
        }
        if ($totalSum > $maxExp || !empty($badArticle)) {
            $update['success'] = 1;
            if (!empty($badArticle)) {
                $articles = implode(',', array_unique($badArticle));
                $update['string_result'] .= "\n Обнаружены статьи: " . $articles;
            }
        }

        $this->scorings->update_scoring($scoring->id, $update);
    }

    /**
     * Получает кол-во исполнительных производств из ответа ФССП
     *
     * @param int $scoringId
     * @return int|null
     */
    public function getFsspDebtsRecordsAmount(int $scoringId): ?int
    {
        $scoring = $this->scorings->get_scoring($scoringId);

        if (empty($scoring) || empty($scoring->body)) {
            return null;
        }

        $body = unserialize($scoring->body);

        if (empty($body)) {
            return null;
        }

        if ($body['Source']['ResultsCount'] === '0') {
            return 0;
        }

        if (empty($body['Source']['Record'])) {
            return null;
        }

        $curDate = new DateTimeImmutable();
        $recordsAmount = 0;

        // Считаем кол-во активных (не завершенных) исполнительных производств
        foreach ($body['Source']['Record'] as $record) {

            if (empty($record['Field'])) {
                continue;
            }

            foreach ($record['Field'] as $row) {

                // Если есть дата завершения исполнительного производства
                if (!empty($row['FieldName']) && $row['FieldName'] === 'CloseDate') {
                    try {
                        $recordDate = new DateTimeImmutable($row['FieldValue']);
                    } catch (Throwable $e) {
                        continue;
                    }

                    // Если дата завершения исполнительного производства меньше текущей даты
                    if ($recordDate < $curDate) {
                        $recordsAmount++;
                    }
                }
            }
        }

        return $recordsAmount;
    }

    private function get_rand_index()
    {
        $index = mt_rand(0, count($this->keys) - 1);
        return $index;
    }
    
    public function create_task($data)
    {
        $index = $this->get_rand_index();
        $data_key = $this->keys[$index];

        $resp = $this->send('search/physical', $data, $data_key);
        if (!empty($resp->response->task))
            $resp->response->task = $resp->response->task.':'.$index;
        
        return ($resp);
    }
    
    public function check_task($task_id)
    {
        list($task, $index) = explode(':', $task_id);
        
        $data_key = $this->keys[$index];
        
        $resp = $this->send('status', array('task' => $task), $data_key);
        
        return $resp;
    }
    
    public function get_task($task_id)
    {
        list($task, $index) = explode(':', $task_id);
        
        $data_key = $this->keys[$index];
        
        $resp = $this->send('result', array('task' => $task), $data_key);
        
        return $resp;    	
    }
    
    public function send($method, $data, $data_key)
    {
    	$this->error = null;
        
        $data['token'] = $data_key['fssp_key'];
        
        $url = $this->api_url. $method . '?' . http_build_query($data);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

//        curl_setopt($ch, CURLOPT_PROXY, $data_key['proxy']);
//        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $data_key['auth']);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $json = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
                
        $result = json_decode($json);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data, $json, $result, $info);echo '</pre><hr />';
        
        return $result;
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