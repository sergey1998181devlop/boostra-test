<?php

namespace App\Modules\UserOrderGift\Services;

class UserOrderGiftService
{
    private \Database $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    public function updateUsersGiftStatus($balance, $payment, $orderId)
    {
        $debt = (int) $balance->ostatok_od + (int) $balance->ostatok_percents;

        if (
            $debt <= (
                $payment->amount
                + $payment->boostra_coins ?? 0
                + $payment->discount_amount
                + $payment->referral_discount_amount
            )
            && !$payment->prolongation
            && $orderId
        ) {
            return (bool) $this->db->query(
                $this->db->placehold(
                    "UPDATE user_order_gifts SET status = 1, activated_at = NOW() where user_id = ? AND status NOT IN (1,2) AND gift_expired_at >= NOW() AND order_id = ?",
                    $payment->user_id,
                    $orderId
                )
            );
        }
    }
}