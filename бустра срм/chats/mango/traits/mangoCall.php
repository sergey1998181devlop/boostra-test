<?php

namespace chats\mango\traits;

use chats\mango\MangoCurl AS Curl;
use chats\mango\traits\StandartMethods AS MainTrait;

trait mangoCall {

    use MainTrait;

    private $incomingCallFile = mangoDir . 'incomingCall_';
    private $disconnectedCallFile = mangoDir . 'disconnectedCall_';
    private $connectedCallFile = mangoDir . 'connectedCall_';
    private $onHoldCallFile = mangoDir . 'onHoldCall_';
    private $callDataFile = mangoDir . 'callData_';

    /**
     * Установка соединения
     */
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

    /**
     * разрыв соединения
     */
    public function endCall($data) {
        $url = 'vpbx/commands/call/hangup';
        $obj = (object) [
                    'command_id' => rand(),
                    'call_id' => $data['callId']
        ];
        $this->usetCallFiles($data);
        $res = Curl::sendPost($url, $obj);
        echo $res;
        $this->mangoLog($obj, $res, 'resultConversation_' . $data['managerMangoNumber'] . '.log');
        exit();
    }

    /**
     * Удаление файлов при завершении вызова
     */
    private function usetCallFiles($data) {
        $incomingFile = $this->incomingCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($incomingFile)) {
            unlink($incomingFile);
        }
        $fileConnected = $this->connectedCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($fileConnected)) {
            unlink($fileConnected);
        }
        $fileOnHold = $this->onHoldCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($fileOnHold)) {
            unlink($fileOnHold);
        }
        $callFile = $this->callDataFile . $data['managerMangoNumber'] . '.log';
        if (is_file($callFile)) {
            unlink($callFile);
        }
    }

    /**
     * логирование звонка
     */
    public function mangoLog($obj, $res, $name) {
        if (!is_dir(mangoLogsDir)) {
            mkdir(mangoLogsDir);
        }
        if ($name === 'accept') {
            $fileName = $obj->transferred_call_id;
        } else {
            $fileName = $obj->call_id;
        }
        file_put_contents(mangoLogsDir . $name . '_' . $fileName . '.log', json_encode(['body' => $obj, 'result' => $res]), FILE_APPEND | LOCK_EX);
    }

    /**
     * Создание дампа входящего вызова на номер сотрудника
     */
    public function incomingCall($data) {
        $file = false;
        if (!is_dir(mangoDir)) {
            mkdir(mangoDir);
        }
        $json = json_decode($data['json']);
        if (isset($json->to->extension)) {
            $file = $this->incomingCallFile . $json->to->extension . '.log';
        }
        if (!is_file($file)) {
            file_put_contents($file, json_encode($json));
        }
    }

    /**
     * создание дампа при соединении
     */
    public function connectedCall($data) {
        if (!is_dir(mangoDir)) {
            mkdir(mangoDir);
        }
        $json = json_decode($data['json']);
        if (!isset($json->to->extension)) {
            $json->to->extension = false;
        }
        $file = $this->connectedCallFile . $json->to->extension . '.log';
        if (!is_file($file)) {
            file_put_contents($file, json_encode($json));
        }
    }

    /**
     * создание дампа при удержании
     */
    public function onHoldCall($data) {
        if (!is_dir(mangoDir)) {
            mkdir(mangoDir);
        }
        $json = json_decode($data['json']);
        if (isset($json->to->extension)) {
            $file = $this->onHoldCallFile . $json->to->extension . '.log';
            if (!is_file($file)) {
                file_put_contents($file, json_encode($json));
            }
        }
    }

    /**
     * создание дампа при окончании разговора
     */
    public function disconnectedCall($data) {
        if (!is_dir(mangoDir)) {
            mkdir(mangoDir);
        }
        $json = json_decode($data['json']);
        if (isset($json->to->extension)) {
            file_put_contents($this->disconnectedCallFile . $json->to->extension . '.log', json_encode($json));
        }
    }

    /**
     * Статус входящего вызова на сотрудника
     */
    public function callStatus($data) {
        if (!is_dir(mangoDir)) {
            mkdir(mangoDir);
        }
        $obj = false;
        $connectFile = $this->connectedCallFile . $data['managerMangoNumber'] . '.log';
        $incomingFile = $this->incomingCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($connectFile) AND is_file($incomingFile)) {
            $connected = $this->connected($data['managerMangoNumber']);
            $incoming = json_decode(file_get_contents($incomingFile));
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
        }
        $this->returnJson($obj);
    }

    /**
     * проверка файлов дампа о входящем звонке
     */
    private function connected($managerId) {
        if (is_file($this->connectedCallFile . $managerId . '.log')) {
            return json_decode($this->connectedCallFile . $managerId . '.log');
        } elseif (is_file($this->onHoldCallFile . $managerId . '.log')) {
            return json_decode($this->onHoldCallFile . $managerId . '.log');
        }
        return false;
    }

    /**
     * Дамп данных о текущем звонке
     */
    public function setCallData($data) {
        $obj = json_decode($_POST['data']);
        $file = $this->callDataFile . $data['managerMangoNumber'] . '.log';
        if (file_put_contents($file, json_encode($obj))) {
            return $_POST['data'];
        }
        return false;
    }

    /**
     * проверка наличия дампа текущего звонка
     */
    private function getCallData($managerMangoNumber) {
        if (is_file($this->callDataFile . $managerMangoNumber . '.log')) {
            return file_get_contents($this->callDataFile . $managerMangoNumber . '.log');
        }
        return false;
    }

    public function setDisconnected($data) {
        $file = $this->disconnectedCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($file)) {
            unlink($file);
            $this->returnJson(false);
        }
        $this->returnJson(true);
    }

    public function getDisconnected($data) {
        $obj = false;
        $file = $this->disconnectedCallFile . $data['managerMangoNumber'] . '.log';
        if (is_file($file)) {
            $obj = true;
        }
        $this->returnJson($obj);
    }

}
