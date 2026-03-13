<?php

namespace chats\mango;

use chats\mango\MangoSettings AS Settings;
use chats\mango\MangoCurl AS Curl;
use chats\mango\MangoAccount;
use Simpla;
use stdClass;

class Mango extends MangoAccount {

    const dir = uploadDir . 'mango' . DIRECTORY_SEPARATOR;
    const logsDir = self::dir . 'logs' . DIRECTORY_SEPARATOR;

    private $incomingCallFile = self::dir . 'incomingCall_';
    private $disconnectedCallFile = self::dir . 'disconnectedCall_';
    private $connectedCallFile = self::dir . 'connectedCall_';
    private $onHoldCallFile = self::dir . 'onHoldCall_';
    private $callDataFile = self::dir . 'callData_';

    public function acceptCall($data) {
        $url = 'vpbx/commands/calls_connect/';
        $obj = (object) [
                    'command_id' => rand(),
                    'holded_call_id' => $data['holded_call_id'],
                    'transfer_initiator_number' => $data['transfer_initiator_number'],
                    'transferred_call_id' => $data['transferred_call_id']
        ];
        $res = Curl::sendPost($url, $obj);
        echo $res;
        $this->mangoLog($obj, $res, 'accept');
        exit();
    }

    public function endCall($data) {
        $url = 'vpbx/commands/call/hangup';
        $obj = (object) [
                    'command_id' => rand(),
                    'call_id' => $data['callId']
        ];
        unlink($this->incomingCallFile . $data['managerMangoNumber'] . '.log');
        $incomingFile = $this->incomingCallFile . $data['managerMangoNumber'] . '.log';
        if(is_file($incomingFile)){
            
        }
        
        $fileConnected = $this->connectedCallFile . $data['managerMangoNumber'] . '.log';
        if(is_file($fileConnected)){
            unlink($fileConnected);
        }
        $callFile = $this->callDataFile . $data['managerMangoNumber'] . '.log';
        if(is_file($callFile)){
          unlink($callFile);
        }
        $res = Curl::sendPost($url, $obj);
        echo $res;
        $this->mangoLog($obj, $res, 'end');
        exit();
    }

    public function addComment($data) {
        $simplaObj = new Simpla();
        $comment = [
            'manager_id' => $data['managerId'],
            'user_id' => $data['userId'],
            'block' => 'incomingCall',
            'text' => 'Входящий звонок от : '
            . $data['userName'] . ' ' . date('Y-m-d H:i:s')
            . ' текст комментария : ' . $data['text'],
            'created' => date('Y-m-d H:i:s')
        ];
        $simplaObj->comments->add_comment($comment);
        echo json_encode((object) ['Data' => 'Ok']);
        exit();
    }

    /**
     * Логирование входящего вызова на номер сотрудника
     */
    public function incomingCall($data) {
        if (!is_dir(self::dir)) {
            mkdir(self::dir);
        }
        $json = json_decode($data['json']);
        file_put_contents($this->incomingCallFile . $json->to->extension . '.log', json_encode($json));
    }

    public function connectedCall($data) {
        if (!is_dir(self::dir)) {
            mkdir(self::dir);
        }
        $json = json_decode($data['json']);
        file_put_contents($this->connectedCallFile . $json->from->extension . '.log', json_encode($json));
    }

    public function onHoldCall($data) {
        if (!is_dir(self::dir)) {
            mkdir(self::dir);
        }
        $json = json_decode($data['json']);
        file_put_contents($this->onHoldCallFile . $json->to->extension . '.log', json_encode($json));
    }

    public function disconnectedCall($data) {
        if (!is_dir(self::dir)) {
            mkdir(self::dir);
        }
        $json = json_decode($data['json']);
        file_put_contents($this->disconnectedCallFile . $json->to->extension . '.log', json_encode($json));
    }

    /**
     * Статус входящего вызова на сотрудника
     */
    public function callStatus($data) {
        if (!is_dir(self::dir)) {
            mkdir(self::dir);
        }
        if (is_file($this->incomingCallFile . $data['managerMangoNumber'] . '.log')) {
            $connected = $this->connected($data['managerMangoNumber']);
            $incoming = json_decode(file_get_contents($this->incomingCallFile . $data['managerMangoNumber'] . '.log'));
            $callData = json_decode($this->getCallData($data['managerMangoNumber']));
            if ($connected) {
                $obj = (object) [
                            'connected' => $connected,
                            'incoming' => $incoming,
                            'callData' => $callData
                ];
            } else {
                $obj = (object) [
                            'connected' => $incoming,
                            'incoming' => $incoming,
                            'callData' => $callData
                ];
            }
            echo json_encode((object) ["Data" => $obj]);
            exit();
        }
        return false;
    }

    public function setCallData($data) {
        $obj = json_decode($_POST['data']);
        $file = $this->callDataFile . $data['managerMangoNumber'] . '.log';
        if (file_put_contents($file, json_encode($obj))) {
            return true;
        }
        return false;
    }

    private function getCallData($managerMangoNumber) {
        if (is_file($this->callDataFile . $managerMangoNumber . '.log')) {
            return file_get_contents($this->callDataFile . $managerMangoNumber . '.log');
        }
        return false;
    }

    public function mangoLog($obj, $res, $name) {
        if (!is_dir(self::logsDir)) {
            mkdir(self::logsDir);
        }
        if ($name === 'accept') {
            $fileName = $obj->transferred_call_id;
        } else {
            $fileName = $obj->call_id;
        }
        file_put_contents(
                self::logsDir . $name . '_' . $fileName . '.log',
                json_encode(
                        [
                            'body' => $obj,
                            'result' => $res
                        ]
                )
        );
    }

    public function connected($managerId) {
        if (is_file($this->connectedCallFile . $managerId . '.log')) {
            return json_decode($this->connectedCallFile . $managerId . '.log');
        } elseif (is_file($this->onHoldCallFile . $managerId . '.log')) {
            return json_decode($this->onHoldCallFile . $managerId . '.log');
        }
        return false;
    }

    public function questionTickets($data) {
        $answers = false;
        $question = $this->getQuestion($data['step'], $data['parent']);
        if ($question) {
            $answers = $this->getAnswers($data['step'], $question->id);
        } else {
            $question = false;
        }

        $data['step']++;
        echo json_encode((object) ["Data" => ['question' => $question, 'answers' => $answers, 'step' => $data['step']]]);
        exit();
    }

    private function getQuestion($step, $parent) {
        $obj = new Simpla();
        settype($step, 'integer');
        settype($parent, 'integer');
        $query = $obj->db->placehold("
            SELECT
                *
            FROM
                __mangoAnswersToTheQuestionsForTheQuestionnaire
            WHERE
                parent = " . $parent . "
        ");
        $obj->db->query($query);
        return $obj->db->result();
    }

    private function getAnswers($step, $questionId) {
        $obj = new Simpla();
        settype($step, 'integer');
        settype($questionId, 'integer');
        $query = $obj->db->placehold("
            SELECT
                *
            FROM
                __mangoQuestionsForTheQuestionnaire
            WHERE
                questionId = " . $questionId . "
        ");
        $obj->db->query($query);
        return $obj->db->results();
    }

}
