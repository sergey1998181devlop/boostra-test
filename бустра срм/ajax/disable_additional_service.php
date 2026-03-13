<?php

chdir('..');

require 'api/Simpla.php';

class MultipolisService extends Simpla
{    
    public function run(): void
    {        
        $this->logRequest();

        $phone = $this->request->get('phone');
        
        if (empty($phone)) {
            $this->response->json_output(['error' => 'Номер телефона обязателен'], 400);
            return;
        }

        $this->processMultipolisUpdate($phone);
        $this->response->json_output(['success' => true]);
    }

    private function logRequest(): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $getData = json_encode($_GET);
        $postData = json_encode($_POST);
        $headers = getallheaders();

        $logMessage = sprintf(
            "[%s] Request Details:\n" .
            "IP: %s\n" .
            "Method: %s\n" .
            "URL: %s\n" .
            "User-Agent: %s\n" .
            "Headers: %s\n" .
            "GET: %s\n" .
            "POST: %s\n" .
            "%s",
            $timestamp,
            $ip,
            $method,
            $url,
            $userAgent,
            json_encode($headers),
            $getData,
            $postData,
            str_repeat('-', 50) . "\n"
        );

        file_put_contents('logs/voximplant.txt', $logMessage, FILE_APPEND);
    }
    private function processMultipolisUpdate(string $phone): void
    {
        $orderId = $this->orders->get_user_last_issued_loan($phone);
        
        if (!$orderId) {
            return;
        }

        $order = $this->orders->get_order($orderId);
        if (!$order) {
            return;
        }

        $this->updateOrderMultipolis($orderId);
        $this->logMultipolisChange($order);
    }

    private function updateOrderMultipolis(int $orderId): void
    {
        $this->order_data->set(
            $orderId,
            $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS,
            1
        );
    }

    private function logMultipolisChange($order): void
    {
        $this->changelogs->add_changelog([
            'manager_id' => $order->manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $this->order_data::ADDITIONAL_SERVICE_MULTIPOLIS,
            'old_values' => 'Включение',
            'new_values' => 'Выключение',
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
        ]);
    }
}

$service = new MultipolisService();
$service->run();