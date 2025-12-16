<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
) {
    $fatal = [E_ERROR, E_PARSE];

    if (in_array($severity, $fatal, true)) {
        // НЕ трогаем фатальные — вернём FALSE,
        // чтобы PHP сам их показал согласно display_errors=1
        return false;
    }

    // Исключаем ошибки из Settings.php
    if (basename($file) === 'Settings.php') {
        return true;
    }

    $map = [
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];
    $level = $map[$severity] ?? 'UNKNOWN';

    (new Simpla())->logging(
        __METHOD__,
        '',
        'ERROR',
        [
            'severity' => $severity,
            'level' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ],
        'dop_api.txt'
    );

    return true;
});

session_start();

class GenerateDOPLicenseKey extends Simpla
{
    public function processRequest()
    {
        $licenseType = $this->request->post('license_type', 'string');
        $userId = $this->request->post('user_id', 'int');
        $orderId = $this->request->post('order_id', 'int');
        $serviceId = $this->request->post('service_id', 'int');
        $organizationId = $this->request->post('organization_id', 'int');
        $amount = $this->request->post('amount', 'int');

        if (!$orderId || !$userId || !$serviceId || !$organizationId || !$amount) {
            $this->jsonResponse(false, 'Не указан все данные');
            $this->logging(
                __METHOD__,
                '',
                $licenseType,
                [
                    'orderId' => $orderId,
                    'userId' => $userId,
                    'serviceId' => $serviceId,
                    'organizationId' => $organizationId,
                    'amount' => $amount
                ],
                'dop_api.txt'
            );
            return;
        }

        if (!$order = $this->orders->get_order($orderId)) {
            $this->jsonResponse(false, 'Заказ не найден');
            return;
        }

        $userId = $order->user_id;

        $filter_data = [
            'filter_service_type' => $licenseType,
            'filter_order_id' => $orderId,
            'filter_user_id' => $userId,
            'filter_service_id' => $serviceId,
            'filter_status' => "SUCCESS"
        ];

        $license = $this->dop_license->getAllLicenseKeys($filter_data,false);

        if ($license) {
            
            if ($license->license_key){
                $this->jsonResponse(true, 'Лицензия уже сгенерирована', ['license_key' => $license->license_key]);
                return;
            }

            $license_key = $this->dop_license->getLicenseKey($license);
        }else{
            $license_key = $this->dop_license->createLicenseWithKey(
                $licenseType,
                [
                    'user_id' => $userId,
                    'order_id' => $orderId,
                    'service_id' => $serviceId,
                    'organization_id' => $organizationId,
                    'amount' => $amount,
                ]
            );
        }

        
        $this->jsonResponse(true, 'Лицензия успешно сгенерирована', ['license_key' => $license_key]);
    }

    /**
     * @param bool $success
     * @param string $message
     * @param array $data
     * @return void
     */
    private function jsonResponse(bool $success, string $message, array $data = []): void
    {
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    }
}

(new GenerateDOPLicenseKey())->processRequest();
