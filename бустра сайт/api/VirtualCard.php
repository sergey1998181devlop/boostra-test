<?php

require_once('Simpla.php');

class VirtualCard extends Simpla
{
    protected $client;
    protected static $ajaxFactory = null;

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
            if (!method_exists($this->client, $method)) {
                throw new \BadMethodCallException("Method {$method} does not exist");
            }

            return call_user_func_array([$this->client, $method], $arguments);

        } catch (\Throwable $throwable) {

            $this->logging(
                __METHOD__,
                '',
                'Virtual card error: ' . $throwable->getMessage(),
                ['method' => $method, 'params' => $arguments],
                'virt_card.txt'
            );

            return false;
        }
    }

    public function createAjaxFactory(int $userId, int $orderId = 0)
    {
        $request = new \VirtualCard\Ajax\Request($userId, $orderId);
        $response = new \VirtualCard\Ajax\Response();
        $vc = \VirtualCard\VirtualCard::forUser($userId, $orderId);

        return new \VirtualCard\Ajax\HandlerFactory($request, $response, $vc);
    }

    public function handleAjax(int $userId, int $orderId = 0): array
    {
        $factory = $this->createAjaxFactory($userId, $orderId);

        $factory
            ->register(\VirtualCard\Ajax\Handler\StatusHandler::class)
            ->register(\VirtualCard\Ajax\Handler\ManagementHandler::class)
            ->register(\VirtualCard\Ajax\Handler\TransactionHandler::class)
            ->register(\VirtualCard\Ajax\Handler\OnboardingHandler::class)
            ->register(\VirtualCard\Ajax\Handler\SurveyHandler::class);

        return $factory->handle();
    }
}
