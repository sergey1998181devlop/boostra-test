<?php
/**
 * @author Jewish Programmer
 */

class Queue extends Simpla
{
    /**
     * @var resource $ch
     */
    private $ch;

    /**
     * @var bool|object
     */
    private $queue = true;

    /**
     * @var array RESOURCES
     */
    const RESOURCES = ['mobile'];

    /**
     * Add fail operations 1C & all future operations to queue table
     * @param string $method
     * @param string $url
     * @param string $call
     * @param array $data
     * @param string $tag
     * @param string $timer
     * @return void
     */
    public function add(
        string $method,
        string $url,
        string $call,
        array $data,
        string $tag = '',
        string $timer = ''
    ): void
    {
        if (!$timer) {
            $timer = date('Y-m-d H:i:s');
        }
        $userId = !empty($data['user_id']) ? (int) $data['user_id'] : 0;
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $query = $this->db->placehold("INSERT INTO __queue SET 
            `method` = ?, `url` = ?, `call` = ?, `data` = ?, `timer` = ?, `tag` = ?, user_id = ?",
            $method, $url, $call, $data, $timer, $tag, $userId);
        $this->db->query($query);
    }

    /**
     * Call fail 1C queries again via crontab
     * @return void
     */
    public function run(): void
    {
        $i = 100;
        while ($i > 0 && !empty($this->queue)) {
            if ($this->queue = $this->getQueue()) {
                try {
                    $this->queue->data = json_decode($this->queue->data, true);
                    $res = in_array($this->queue->call, self::RESOURCES) ? $this->noSoap() : $this->soap();
                    if ($res) {
                        $this->updateQueue();
                    }
                } catch (Exception $e) {
                    //
                }
            }
            $i--;
        }
    }

    /**
     * Call another methods
     * @return array
     */
    private function noSoap(): array
    {
        if (is_null($this->ch)) {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($this->queue->data, JSON_UNESCAPED_UNICODE));
        curl_setopt($this->ch, CURLOPT_URL, $this->queue->url . $this->queue->method);
        if (curl_errno($this->ch)) {
            return [];
        }
        $returnData = curl_exec($this->ch);
        return $returnData ? json_decode($returnData, true) : [];
    }

    /**
     * Call only soap methods
     * @return mixed
     * @throws SoapFault
     */
    private function soap()
    {
        $this->setLoggerState($this->queue->method . ' CRON', $this->queue->url . ' ' . $this->queue->call, $this->queue->data);
        $client = new SoapClient($this->queue->url);
        $returned = $client->__soapCall($this->queue->call, array($this->queue->data));
        $this->logging($this->queue->method . ' CRON', $this->queue->url . ' ' . $this->queue->call, $this->queue->data, (array) $returned);
        return $returned;
    }

    /**
     * Get last queue
     * @return ?object
     */
    private function getQueue()
    {
        $query = $this->db->placehold("SELECT id, `method`, `url`, `call`, `data` 
            FROM __queue WHERE sent = ? AND ? > timer
            ORDER BY created_date 
            LIMIT 1", 0, date('Y-m-d H:i:s'));
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Update queue
     * @return void
     */
    private function updateQueue(): void
    {
        $this->db->query("UPDATE __queue SET sent = 1 WHERE id = ?", (int) $this->queue->id);
    }
}