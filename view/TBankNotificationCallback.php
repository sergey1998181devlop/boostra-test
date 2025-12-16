<?php

require_once 'View.php';

class TBankNotificationCallback extends View
{
    public function fetch()
    {
        if ($this->request->method() !== 'POST') {
            $this->logging(__METHOD__, 'TBankNotificationCallback', [], ['error' => 'Invalid request method'], date('d-m-Y').'-t-bank-error.txt');
            return 'OK';
        }

        $notification = $this->request->post();

        try {
            $this->TBankValidateService->validateNotificationCallback($notification);
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'TBankNotificationCallback', ['notification' => $notification], ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');

            return 'OK';
        }

        try {
            if ($notification['NotificationType'] === 'LINKACCOUNT') {
                if ($notification['Success']) {
                    $this->TBankDatabaseService->updateAccount([
                        'status' => $notification['Status'],
                        'account_token' => $notification['AccountToken'],
                        'error_code' => isset($notification['ErrorCode']) ? $notification['ErrorCode'] : '0',
                    ], [
                        'request_key' => $notification['RequestKey'],
                    ]);
                } else {
                    $this->TBankDatabaseService->updateAccount([
                        'status' => $notification['Status'],
                        'error_code' => $notification['ErrorCode'],
                        'message' => $notification['Message'],
                        'deleted' => 1,
                        'deleted_at' => date('Y-m-d H:i:s'),
                    ], [
                        'request_key' => $notification['RequestKey'],
                    ]);
                }
            } else {
                $this->best2pay->update_payment_where([
                    'reason_code' => $notification['Status'] === 'CONFIRMED' ? 1 : 3,
                    'callback_response' => json_encode($notification),
                ], [
                    'operation_id' => $notification['PaymentId'],
                ]);
            }

            return 'OK';
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'TBankNotificationCallback', ['notification' => $notification], ['error' => $e->getMessage()], date('d-m-Y').'-t-bank-error.txt');
            return 'OK';
        }
    }
}