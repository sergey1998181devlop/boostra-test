<?php

trait MonitoringIntervalTrait
{
    /**
     * Определяет интервал в зависимости от времени суток
     * 7-20 часов = 15 минут, иначе 30 минут
     *
     * @return string
     */
    protected function getInterval(): string
    {
        $currentHour = (int)date('G');
        
        if ($currentHour >= 7 && $currentHour < 20) {
            return '15 MINUTE';
        }
        
        return '30 MINUTE';
    }

    /**
     * Проверяет API ключ из заголовка X-Api-Key
     * 
     * @return void
     */
    protected function checkApiKey(): void
    {
        // Получаем заголовки
        $headers = getallheaders();
        
        $token = $headers['X-Api-Key'] ?? null;

        $expected = $this->config->api_transactions_token_1c ?? null;

        if (!$expected) {
            http_response_code(500);
            $this->response->json_output(['error' => 'Server token not configured']);
            exit;
        }

        if ($token !== $expected) {
            http_response_code(401);
            $this->response->json_output(['error' => 'Unauthorized: invalid API token']);
            exit;
        }
    }
}

