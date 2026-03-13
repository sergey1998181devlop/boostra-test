<?php

use App\Enums\MindboxConstants;
use Mindbox\DTO\V3\OperationDTO;
use Mindbox\Mindbox;

class MindboxApi extends Simpla
{
    public function createMindbox(){
        $logger = new \Psr\Log\NullLogger();
        $mindbox = new Mindbox([
            'endpointId' => $this->config->mb_endpoint_id,
            'secretKey' => $this->config->mb_secret_key,
            'timeout' => MindboxConstants::MINDBOX_SDK_TIMEOUT,
            'domain' => $this->config->mb_domain,
            'domainZone' => 'ru',
        ], $logger);

        return $mindbox;
    }

    /**
     * Проверка существования клиента в Mindbox по номеру телефона
     * @param string $phone
     * @return bool|array Возвращает false если клиент не существует, или массив с данными если существует
     */
    public function isExistCustomer(string $phone)
    {
        $mindbox = $this->createMindbox();
        try {
            $data = [
                'customer' => [
                    'mobilePhone' => $phone,
                ],
            ];

            $body = new OperationDTO($data);
            $response = $mindbox->getClientV3()
                ->prepareRequest('POST', 'Website.CheckCustomer', $body, '', [], true, false)
                ->sendRequest();

            // Проверяем статус ответа правильным способом через методы класса MindboxResponse
            if ($response && $response->getMindboxStatus() === 'Success') {
                $customer = $response->getResult()->getCustomer();

                // Проверяем, что клиент найден
                if ($customer && !empty($customer->getField('processingStatus'))) {
                    $processingStatus = $customer->getField('processingStatus');
                    return $processingStatus === 'Found';
                }
            }

            return false;
        } catch (Throwable $e) {
            // Если клиент не найден или произошла ошибка, возвращаем false
            return false;
        }
    }
}