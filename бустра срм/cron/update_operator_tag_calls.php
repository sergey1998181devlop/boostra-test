<?php

ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';
require_once dirname(__FILE__) . '/../api/Voximplant.php';

class UpdateOperatorTagCalls extends Simpla
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run()
    {
        $limit = 30;
        $offset = 0;
        $startDate = date('Y-m-d H:i:s', strtotime('-1 days'));
        $endDate = date('Y-m-d H:i:s');

        $voximplant = new Voximplant();

        do {
            $calls = $this->getCalls($startDate, $endDate, $limit, $offset);
            if (empty($calls)) {
                break;
            }

            foreach ($calls as $call) {
                $data = json_decode($call->text, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                if (!isset($data['operator_tag']) && isset($data['call_id'])) {
                    $callId = $data['call_id'];
                    $tags = $voximplant->searchCalls($callId)['result'][0]['tags'];

                    if (!empty($tags)) {
                        $data['operator_tag'] = $tags[0]['tag_name'];
                        $jsonText = json_encode($data, JSON_UNESCAPED_UNICODE);
                        $sql = "UPDATE s_comments SET text = ? WHERE id = ?";
                        $this->db->query($sql, $jsonText, $call->id);
                    }
                }
            }

            $offset += $limit;
        } while (count($calls) == $limit);
    }

    private function getCalls(string $startDate, string $endDate, int $limit, int $offset)
    {
        $sql = "SELECT id, text 
                FROM s_comments 
                WHERE block = 'incomingCall'
                  AND created BETWEEN ? AND ?
                  AND text NOT LIKE '%\"operator_name\":\"\"%'
                  AND text NOT LIKE '%operator_tag%'
                  AND text LIKE '%call_id%'
                ORDER BY created ASC
                LIMIT ? OFFSET ?";
        $this->db->query($sql, $startDate, $endDate, $limit, $offset);
        return $this->db->results();
    }
}

$cron = new UpdateOperatorTagCalls();
$cron->run();
