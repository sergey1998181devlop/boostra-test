<?php

error_reporting(1);
ini_set('display_errors', 'on');
ini_set('max_execution_time', '600');
require_once dirname(__FILE__).'/../api/Simpla.php';
class SendMissedCallsCron extends Simpla
{
    public function run()
    {
        $exists = $this->tasks->exists();
        if (empty($exists)) {
            exit();
        }
        if ($exists->attempts_count == $exists->attempts_made){
            exit();
        }
        $timeToSend = date('Y-m-d H:i:s', strtotime($exists->last_send) + $exists->interval_time * 60);
        if ($timeToSend !=  date('Y-m-d H:i:00')) {
            exit;
        }
        $filter = [
            'date' => date("Y-m-d"),
            'missed_calls' => true,
            'period' => 'zero'
        ];
        $users = $this->users->get_users_ccprolongations($filter);
        $data = [
            "campaign_id" => $exists->robo_number,
            'rows' => json_encode($users),
        ];
        $res = $this->voximplant->sendRobocompany($data);
        if (empty($res['success'])) {
            $this->updateMissedCall(date('Y-m-d'),
                [
                    'last_send' => date('Y-m-d H:i:00'),
                ]
            );
            exit();
        }
        $this->updateMissedCall(date('Y-m-d'),
            [
                'last_send' => date('Y-m-d H:i:00'),
                'attempts_made' => $exists->attempts_made + 1,
            ]
        );
    }
    private function updateMissedCall($date, $data) {
        $query = $this->db->placehold("
            UPDATE missed_calls SET ?% WHERE created = ?
        ", (array)$data, $date);
        $this->db->query($query);

    }
}

(new SendMissedCallsCron())->run();
