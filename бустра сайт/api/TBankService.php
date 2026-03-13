<?php

require_once 'Simpla.php';

class TBankService extends Simpla
{
    const T_BANK_SBP_SECTOR = 22;

    private function createToken(array $requestInfo): string
    {
        $data = [];
        foreach ($requestInfo as $key => $value) {
            if (!is_array($value)) { // Вложенные объекты и массивы не учитываются согласно условию задачи
                $data[$key] = $value;
            }
        }
        $data['Password'] = $this->config->tbank_terminal_password;

        // Сортируем массив по алфавиту по ключу
        ksort($data);

        // Конкатенируем только значения параметров в одну строку
        $concatenatedString = implode('', array_map(function ($value) {
            return mb_convert_encoding($value, 'UTF-8');
        }, array_values($data)));

        return hash('sha256', $concatenatedString);
    }

    public function init(array $params): array
    {
        $this->TBankValidateService->validateInit($params);

        $request = [
            'TerminalKey' => $this->config->tbank_terminal_key,
            'Amount' => $params['amount'] * 100, // Сумма в копейках
            'OrderId' => $params['payment_id'],
            'Description' => isset($params['description']) ? $params['description'] : '',
        ];

        $request['Token'] = $this->createToken($request);

        if (isset($params['is_attach_account']) && $params['is_attach_account']) {
            $request['Recurrent'] = 'Y';
            $request['DATA.QR'] = 'true';
        }

        $response = $this->TBankApiService->initRequest($request);

        if (!$response) throw new \Exception('Ошибка получения ответа от API Т-Банка');

        if (isset($response['Success']) && $response['Success']) {
            $paymentData = [
                'reason_code' => 0,
                'payment_id' => $response['OrderId'],
                'register_id' => 0,
                'body' => json_encode($request),
                'callback_response' => json_encode($response),
                'sector' => self::T_BANK_SBP_SECTOR,
            ];

            $this->best2pay->update_payment($params['payment_id'], $paymentData);
        } else {
            $paymentData = [
                'reason_code' => 3,
                'payment_id' => isset($response['OrderId']) ? $response['OrderId'] : null,
                'register_id' => 0,
                'body' => json_encode($request),
                'callback_response' => json_encode($response),
                'sector' => self::T_BANK_SBP_SECTOR,
            ];

            $this->best2pay->update_payment($params['payment_id'], $paymentData);
        }

        return [
            'amount' => $response['Amount'] / 100,
            'payment_id' => $response['OrderId'],
            'success' => $response['Success'],
            'status' => isset($response['Status']) ? $response['Status'] : null,
            'operation_id' => isset($response['PaymentId']) ? $response['PaymentId'] : null,
            'error_code' => isset($response['ErrorCode']) ? $response['ErrorCode'] : null,
            'payment_url' => isset($response['PaymentURL']) ? $response['PaymentURL'] : null,
            'message' => isset($response['Message']) ? $response['Message'] : null,
            'details' => isset($response['Details']) ? $response['Details'] : null,
        ];
    }

    public function getQr($operationId): string
    {
        if (!$operationId || !is_numeric($operationId)) {
            throw new \Exception('Не передан идентификатор платежа');
        }

        $request = [
            'TerminalKey' => $this->config->tbank_terminal_key,
            'PaymentId' => $operationId,
        ];

        $request['Token'] = $this->createToken($request);

        $response = $this->TBankApiService->getQrRequest($request);

        if (!$response) throw new \Exception('Ошибка получения ответа от API Т-Банка');
        $requestKey = isset($response['RequestKey']) ? $response['RequestKey'] : '';

        if ($response['Success']) {
            $paymentData = [
                'payment_link' => $response['Data'],
                'body' => json_encode($request),
                'callback_response' => json_encode($response),
            ];

            $this->best2pay->update_payment($operationId, $paymentData);
        } else {
            $paymentData = [
                'reason_code' => 3,
                'body' => json_encode($request),
                'callback_response' => json_encode($response),
            ];

            $this->best2pay->update_payment($operationId, $paymentData);
        }

        return $requestKey;
    }

    public function addAccountQr(array $params): string
    {
        $this->TBankValidateService->validateAddAccountQr($params);

        $request = [
            'TerminalKey' => $this->config->tbank_terminal_key,
            'Description' => $params['description'],
        ];

        $request['Token'] = $this->createToken($request);

        $response = $this->TBankApiService->addAccountQrRequest($request);

        if (!$response) throw new \Exception('Ошибка получения ответа от API Т-Банка');
        $link = '';

        if (isset($response['Success']) && $response['Success']) {
            $link = $response['Data'];

            $this->TBankDatabaseService->addAccount([
                'user_id' => $params['user_id'],
                'request_key' => $response['RequestKey'],
                'status' => isset($response['Status']) ? $response['Status'] : null,
                'error_code' => isset($response['ErrorCode']) ? $response['ErrorCode'] : null,
                'message' => isset($response['Message']) ? $response['Message'] : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            throw new \Exception('Ошибка получения ответа от API Т-Банка: ' . (isset($response['Message']) ? $response['Message'] : 'Неизвестная ошибка'));
        }

        return $link;
    }
}