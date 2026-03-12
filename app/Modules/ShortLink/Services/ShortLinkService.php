<?php

namespace App\Modules\ShortLink\Services;

use App\Modules\NotificationCenter\Services\NotificationCenterService;

class ShortLinkService
{
    private const GENERATE_REFERRAL_LINK_API = 'api/short_links/generate_referral_link';
    private const GENERATE_FRIEND_PAYMENT_LINK_API = 'api/short_links/generate_friend_payment_link';
    private NotificationCenterService $ncService;

    public function __construct(NotificationCenterService $ncService)
    {
        $this->ncService = $ncService;
    }

    public function getReferralShortLink(int $userId, string $uid): string
    {
        try {
            $url = $this->ncService->getUrl() . static::GENERATE_REFERRAL_LINK_API;

            $response = $this->ncService->post([
                'external_id' => $userId,
                'uid' => $uid,
                'utm' => 'sms_1282_link_boostra_' . date('Ymd'),
            ], $url);

            return $response['short_link'] ?? '';
        } catch (\Throwable $e) {
            logger('referral_link')->error(
                __METHOD__ . PHP_EOL
                . $e->getFile() . PHP_EOL
                . $e->getLine() . PHP_EOL
                . $e->getMessage() . PHP_EOL
            );

            return '';
        }
    }

    public function getFriendPaymentLink(
        int $userId,
        string $uid,
        int $overdueDays,
        string $phone,
        int $order_id
    ): string {
        try {
            $url = $this->ncService->getUrl() . static::GENERATE_FRIEND_PAYMENT_LINK_API;

            $response = $this->ncService->post([
                'external_id'  => $userId,
                'uid'          => $uid,
                'order_id'     => $order_id,
                'phone'        => $phone,
                'overdue_days' => $overdueDays,
            ], $url);

            return $response['short_link'] ?? '';
        } catch (Exception $e) {
            logger('friend_payment_link')->error($e->getMessage());
            return '';
        }
    }
}


