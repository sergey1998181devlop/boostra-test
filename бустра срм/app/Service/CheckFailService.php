<?php

namespace App\Service;

use App\Dto\CheckFailServiceResultDTO;
use App\Models\AutomationFail;
use App\Models\B2PP2PCredit;
use App\Models\B2PPayments;

class CheckFailService
{
    private const TimeIssuanceError = 'time_issuance_error';
    private const IssuanceError = 'issuance_error';
    private const PaymentError = 'payment_error';
    private const SiteError = 'site_error';
    private const SoapError = 'soap_error';

    public function check(): CheckFailServiceResultDTO
    {
        $checkList = $this->getCheckList();

        $checkResult = new CheckFailServiceResultDTO(false);

        foreach ($checkList as $checkType => $checkData) {
            $automationFail = $this->getAutomationFail(['type' => $checkType]);
            $automationFail = $this->updateAutomationFail($automationFail['id'], ['is_active' => $checkData['error']]);

            if ($checkData['error']) {
                error_log($checkData['message']);

                $isNeedSentNotification = $this->checkIsNeedSentNotification($automationFail);
                if ($isNeedSentNotification) {
                    $this->sendTelegramNotification($automationFail);

                    $automationFail = $this->updateAutomationFail(
                        $automationFail['id'],
                        ['last_notification_at' => date('Y-m-d H:i:s')]
                    );
                }

                $checkResult = new CheckFailServiceResultDTO(
                    true,
                    $automationFail['is_active'],
                    $automationFail['text'],
                    $isNeedSentNotification,
                    $automationFail['show_at'],
                );
            }
        }

        return $checkResult;
    }

    private function getCheckList(): array
    {
        return [
            self::IssuanceError => [
                'message' => 'Check tech health error: last 20 order rejected',
                'error' => $this->issetIssuanceError()
            ],
            self::PaymentError => [
                'message' => 'Check tech health error: last 20 payments not success',
                'error' => $this->issetPaymentsError()
            ],
            self::TimeIssuanceError => [
                'message' => 'Check tech health error: problem with issuing order',
                'error' => $this->isLongIssuanceOrders()
            ],
            self::SiteError => [
                'message' => 'Check tech health error: site not load',
                'error' => $this->hasSiteError()
            ],
            self::SoapError => [
                'message' => 'Check tech health error: soap error',
                'error' => $this->hasSoapError()
            ]
        ];
    }

    private function getAutomationFail(array $where): array
    {
        return (new AutomationFail())->get(
            ['id', 'last_notification_at', 'text', 'show_at', 'is_active'],
            $where,
        )->getData();
    }

    private function updateAutomationFail(int $id, array $data): array
    {
        (new AutomationFail())->update($data, ['id' => $id]);

        return $this->getAutomationFail(['id' => $id]);
    }

    private function checkIsNeedSentNotification(array $automationFail): bool
    {
        if ($automationFail['last_notification_at']) {
            $fromTime = strtotime($automationFail['last_notification_at']);
            $toTime = time();

            if ($toTime - $fromTime < 300) {
                return false;
            }
        }

        return true;
    }

    private function hasSiteError(): bool
    {
        $ch = curl_init('https://boostra.ru/');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $statusCode !== 200;
    }

    private function hasSoapError(): bool
    {
        $soapError = (new AutomationFail())->get(
            ['id'],
            [
                'type' => self::SoapError,
                'is_active' => true
            ],
        )->getData();

        return (bool)$soapError;
    }

    private function isLongIssuanceOrders(): bool
    {
        $timeIssuanceError = (new AutomationFail())->get(
            ['id'],
            [
                'type' => self::TimeIssuanceError,
                'is_active' => true
            ],
        )->getData();

        return (bool)$timeIssuanceError;
        // По верификации пока что будет включаться самостоятельно, без расчетов, расчеты не актуальны но пригодятся в будущем

//        $currentTime = date('Y-m-d H:i:s');
//        $startTime = date('Y-m-d H:i:s', strtotime('-70 minutes', strtotime($currentTime)));
//        $endTime = date('Y-m-d H:i:s', strtotime('-60 minutes', strtotime($currentTime)));
//
//        $lastTwentyOrders = (new Order())->select(
//            ['id', 'accept_date', 'approve_date', 'reject_date'],
//            [
//                'accept_date[<>]' => [$startTime, $endTime],
//                'complete' => 1,
//            ],
//        )->getData();
//
//        /** @var array{
//         *     id: integer,
//         *     accept_date: string,
//         *     approve_date: string,
//         *     reject_date: string
//         * } $payment
//         */
//        foreach ($lastTwentyOrders as $order) {
//
//            $acceptDateTimestamp = strtotime($order['accept_date']);
//            $approveDateTimestamp = strtotime($order['approve_date']);
//            $rejectDateTimestamp = strtotime($order['reject_date']);
//
//            $diffBetweenAcceptAndApprove = ($approveDateTimestamp - $acceptDateTimestamp) / 60;
//            $diffBetweenAcceptAndReject = ($approveDateTimestamp - $rejectDateTimestamp) / 60;
//
//            if (
//                ($order['approve_date'] && $diffBetweenAcceptAndApprove < 60) ||
//                ($order['reject_date'] && $diffBetweenAcceptAndReject < 60)
//            ) {
//                return false;
//            }
//        }
//
//        return true;
    }

    /**
     * Check the last twenty payments only with error or no
     *
     * @return bool
     */
    private function issetPaymentsError(): bool
    {
        $lastTwentyPayments = (new B2PPayments())->select(
            ['id', 'reason_code'],
            [
                'LIMIT' => 20,
                'ORDER' => [
                    'id' => 'DESC'
                ],
            ],
        )->getData();

        /** @var array{id: integer, reason_code: integer} $payment */
        foreach ($lastTwentyPayments as $payment) {
            if ($payment['reason_code'] == 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check the last twenty issuance only rejected or no
     *
     * @return bool
     */
    private function issetIssuanceError(): bool
    {
        $lastTwentyCredit = (new B2PP2PCredit())->select(
            ['id', 'status'],
            [
                'LIMIT' => 20,
                'ORDER' => [
                    'id' => 'DESC'
                ],
            ],
        )->getData();

        /** @var array{id: integer, status: string} $credit */
        foreach ($lastTwentyCredit as $credit) {
            if ($credit['status'] !== 'REJECTED') {
                return false;
            }
        }

        return true;
    }

    private function sendTelegramNotification(array $automationFail): void
    {
        $chatId = config('services.telegram.notifications_chat_id');
        $messageThreadId = config('services.telegram.status_site_work_thread_id');

        (new TelegramService())->sendMessage($chatId,
            $automationFail['text'] ?? 'Проблема с техническим здоровьем сайта', [
                'parse_mode' => 'HTML',
                'message_thread_id' => $messageThreadId
            ]
        );
    }
}
