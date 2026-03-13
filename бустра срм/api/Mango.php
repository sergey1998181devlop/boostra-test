<?php

require_once 'Simpla.php';

class Mango extends Simpla {

    private $api_key = '';
    private $api_salt = '';
//    private $line_number = '74953748405';
    private $line_number = '78003333073';

    private const MANGO_OFFICE_BASE_URL = 'https://app.mango-office.ru/vpbx';

    public function __construct() {
        parent::__construct();

        $mangoApiKeys = $this->settings->getApiKeys('mango');
        $this->api_key = $mangoApiKeys['api_key'];
        $this->api_salt = $mangoApiKeys['api_salt'];
    }

    public function call($phone, $mango_number, $params = array()) {
        $url = self::MANGO_OFFICE_BASE_URL.'/commands/callback';

        $mangocall_id = $this->add_call(array(
            'manager_id' => empty($params['manager_id']) ? 0 : $params['manager_id'],
            'order_id' => empty($params['order_id']) ? 0 : $params['order_id'],
            'user_id' => empty($params['user_id']) ? 0 : $params['user_id'],
            'created' => date('Y-m-d H:i:s'),
        ));

        $data = array(
            "command_id" => "ID_" . $mangocall_id,
            "from" => array(
                "extension" => $mango_number, // внутренний номер, за счет которого производится звонок. (например 101)            
            //"number" => "user1@vpbx400215406.mangosip.ru" // <- кто звонит (можно SIP)
            //"number" => "74953748405" // <- (можно номер)            
            ),
            "to_number" => $phone, // <- кому звонит
            "line_number" => $this->line_number // <- какой АОН 
        );
        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );

        $response = $this->send($url, $postdata);
        return $response;
    }

    public function get_history($phone) {
        $now = time() - 3600;
        $from = $now - 86400 * 30;

        $data = array(
            "date_from" => $from,
            "date_to" => $now,
            "call_party" => array(
                "number" => $phone
            ),
            "fields" => "records"
        );

        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );
        $response = $this->send(self::MANGO_OFFICE_BASE_URL.'/stats/request', $postdata);
        sleep(1);
        $sign_result = hash('sha256', $this->api_key . $response . $this->api_salt);
        $post_data_result = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign_result,
            'json' => $response
        );
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($post_data_result, $response);echo '</pre><hr />';        
        $history_data = $this->send(self::MANGO_OFFICE_BASE_URL.'/stats/result', $post_data_result);

        return $history_data;
    }

    public function getOperatorByExtension(int $extension): array
    {
        $json = json_encode([
            "extension" => $extension
        ]);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );

        $response = $this->send(self::MANGO_OFFICE_BASE_URL.'/config/users/request', $postdata);

        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        $user = $response['users'][0] ?? [];

        return [
            'name' => $user['general']['name'] ?? '',
            'id' => $user['telephony']['extension'] ?? 0,
        ];
    }

    public function get_record_link($record_id) {
        $data = array(
            "recording_id" => $record_id, // <- идентификатор записи (можно взять из уведомления о записи или из статистики вызовов)
            "action" => "download" // <- скачать ("play" - проиграть)
        );
        $json = json_encode($data);
        $sign = hash('sha256', $this->api_key . $json . $this->api_salt);
        $postdata = array(
            'vpbx_api_key' => $this->api_key,
            'sign' => $sign,
            'json' => $json
        );

        $response = $this->send(self::MANGO_OFFICE_BASE_URL.'/queries/recording/post/', $postdata, 1);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($response);echo '</pre><hr />';
        $part_link = trim(substr($response, strripos($response, 'location') + 9));
        $expl = explode("\n", $part_link);
        $link = array_shift($expl);
        return trim($link); // вывести ссылку на mp3 
    }

    private function send($url, $postdata, $record = 0) {
        $post = http_build_query($postdata);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        if ($record) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        }
        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function get_call_id($entry_id) {
        $query = $this->db->placehold("
            SELECT id
            FROM __mangocalls
            WHERE entry_id = ?
        ", (string) $entry_id);
        $this->db->query($query);

        return $this->db->result('id');
    }

    public function get_call($id) {
        $query = $this->db->placehold("
            SELECT * 
            FROM __mangocalls
            WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_calls($filter = array()) {
        $id_filter = '';
        $phone_filter = '';
        $manager_id_filter = '';
        $user_id_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));

        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array) $filter['manager_id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', $filter['user_id']));

        if (!empty($filter['phone'])) {
            $phone = str_replace(array('-', ' ', '(', ')', '+'), '', $filter['phone']);
            $phone_filter = $this->db->placehold("AND (from_number = ? OR to_number = ?)", $phone, $phone);
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __mangocalls
            WHERE 1
                $id_filter
                $manager_id_filter
                $phone_filter
                $user_id_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_calls($filter = array()) {
        $id_filter = '';
        $phone_filter = '';
        $manager_id_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));

        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array) $filter['manager_id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array) $filter['user_id']));

        if (!empty($filter['phone'])) {
            $phone = str_replace(array('-', ' ', '(', ')', '+'), '', $filter['phone']);
            $phone_filter = $this->db->placehold("AND (from_number = ? OR to_number = ?)", $phone, $phone);
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __mangocalls
            WHERE 1
                $id_filter
                $phone_filter 
                $manager_id_filter
                $user_id_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_call($call) {
        $call = (array) $call;

        if (empty($call['created']))
            $call['created'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("
            INSERT INTO __mangocalls SET ?%
        ", $call);
        $this->db->query($query);
        $id = $this->db->insert_id();
        return $id;
    }

    public function update_call($id, $call) {
        $query = $this->db->placehold("
            UPDATE __mangocalls SET ?% WHERE id = ?
        ", (array) $call, (int) $id);
        $this->db->query($query);
        return $id;
    }

}
