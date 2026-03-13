<?php

require_once('Simpla.php');

class VirtualCard extends Simpla
{
    protected $client;

    public function __construct()
    {
        parent::__construct();

        \VirtualCard\VirtualCard::configure(
            rtrim($this->config->virtual_card_base_api_url, '/'),
            trim($this->config->jwt_b2b_secret_key)
        );
    }

    public function forUser($userId, $orderId = null)
    {
        $this->client = \VirtualCard\VirtualCard::forUser(
            (int) $userId,
            $orderId ? (int) $orderId : 0
        );

        return $this;
    }

    public function __call($method, $arguments)
    {
        try {
            if (!$this->client) {
                throw new \LogicException('VirtualCard client not initialized');
            }

            if (!method_exists($this->client, $method)) {
                throw new \BadMethodCallException("Method {$method} does not exist");
            }

            return call_user_func_array([$this->client, $method], $arguments);

        } catch (\Throwable $throwable) {

            $this->logging(
                __METHOD__,
                '',
                'Virtual card error: ' . $throwable->getMessage(),
                [$arguments],
                'virt_card.txt'
            );

            return false;
        }
    }

    public function deposit($data)
    {
        try {
            $depositResponse = $this->client->deposit($data);
            if ($depositResponse['status'] !== 200 || !empty($depositResponse['error'])) {
                $status  = (string)$depositResponse['status'];
                $message = isset($depositResponse['error']) ? json_encode($depositResponse['error']) : 'Unknown error';
                $body    = (string)($depositResponse['body'] ?? '');

                $statusEsc  = htmlspecialchars($status, ENT_XML1 | ENT_COMPAT, 'UTF-8');
                $messageEsc = htmlspecialchars($message, ENT_XML1 | ENT_COMPAT, 'UTF-8');
                $bodyEsc    = htmlspecialchars($body, ENT_XML1 | ENT_COMPAT, 'UTF-8');

                return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<operation>
    <name>VCARD Transfer Error</name>
    <state>VIRTCARD_REJECTED</state>
    <type>VC_DEPOSIT</type>
    <message>{$messageEsc}</message>
    <body>{$bodyEsc}</body>
    <status>{$statusEsc}</status>
</operation>
XML;
            }

            return $depositResponse['body'];

        } catch (\Throwable $throwable) {
            return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<operation>
    <name>VCARD Transfer Error</name>
    <state>VIRTCARD_ERROR</state>
    <type>VC_DEPOSIT</type>
    <message>{$throwable->getMessage()}</message>
</operation>
XML;
        }
    }

    public function shouldWaitUntilVirtualCardReadyOrMoveToSBP($order)
    {
        if ($order->card_type !== $this->orders::CARD_TYPE_VIRT) {
            return false;
        }

        if (!$this->settings->vc_enabled) {
            $this->moveToSBP($order);
            return true;
        }

        $orderId = (int) $order->order_id;

        $cardData = $this->virtualCard->forUser($order->user_id, $orderId)->status();
        if (!is_array($cardData)) {
            $this->moveToSBP($order);
            return true;
        }

        $status = $cardData['status'] ?? null;
        if (!in_array($status, ['pending', 'active', 'deleted', 'blocked'])) {
            $this->moveToSBP($order);
            return true;
        }

        if ($status === 'active') {
            return false;
        }

        if ($status === 'pending') {
            // переводим ордер в ожидание карты
            $this->orders->update_order($order->order_id, array('status' => $this->orders::STATUS_WAIT_VIRTUAL_CARD));
            return true;
        }

        $this->moveToSBP($order);
        return true;
    }

    public function shouldRetryRejectedVirtualCardOrderWithSBP($res, $order)
    {
        if ($order->card_type !== $this->orders::CARD_TYPE_VIRT) {
            return false;
        }

        if (!$this->settings->vc_enabled) {
            return false;
        }

        if (!$res) {
            return false;
        }

        // XML содержит status
        if (isset($res->state) && (string)$res->state === 'REJECTED') {
            $this->moveToSBP($order, [
                'status' => $this->orders::ORDER_STATUS_SIGNED
            ]);
            return true;
        }

        return false;
    }

    private function moveToSBP($order, $updateData = [])
    {
        $this->orders->update_order($order->order_id, array_merge([
            'card_type' => $this->orders::CARD_TYPE_SBP
        ], $updateData));

        $this->changelogs->add_changelog([
            'manager_id' => $order->manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'order_retry_with_sbp',
            'old_values' => serialize(['card_type' => $this->orders::CARD_TYPE_VIRT]),
            'new_values' => serialize(['card_type' => $this->orders::CARD_TYPE_SBP]),
            'user_id' => $order->user_id,
            'order_id' => $order->order_id
        ]);

        $this->logging(__METHOD__, '', 'Повторная оплата через СБП:', ['order_id' => (string)$order->order_id], 'virt_card.txt');
    }
}
