<?php

namespace App\Modules\Referral\Services;

use App\Modules\ShortLink\Services\ShortLinkService;
use Best2pay;
use OrderData;
use stdClass;

class ReferralService
{
    private Best2pay $best2pay;
    private OrderData $orderData;
    private \UserData $userData;
    private ShortLinkService $shortLinkService;

    public function __construct(Best2pay $best2pay, OrderData $orderData, \UserData $userData, ShortLinkService $shortLinkService)
    {
        $this->best2pay = $best2pay;
        $this->orderData = $orderData;
        $this->userData = $userData;
        $this->shortLinkService = $shortLinkService;
    }

    /**
     * COLLECTION-1282
     * Вычисляем скидку для реферала
     */
    public function getReferralDiscountAmount(StdClass $userBalance, float $amount, int $prolongation): array
    {
        $restricted_mode = ($_SESSION['restricted_mode'] ?? 0) == 1;
        if ($restricted_mode && $this->best2pay->checkDebtAndPromo($userBalance, $userBalance->discount_amount, $amount, $prolongation)) {
            $discount = $userBalance->discount_amount;
        } else {
            $discount = 0;
        }

        $referralDiscount = $this->calculateReferralDiscount($userBalance, $amount);
        $left = max(0, $amount - $referralDiscount);

        // Скидка discount_amount - только на ОСТАТОК
        $discount = min($discount, $left);

        logger('referral_link')->info("discount: $discount | referralDiscount: $referralDiscount");

        return [$discount, $referralDiscount];
    }

    public function calculateReferralDiscount(?StdClass $userBalance, int $amount): int
    {
        if (!$userBalance) {
            return 0;
        }

        if (empty($userBalance->referral_discount_amount)) {
            return 0;
        }

        $debt = (int) $userBalance->ostatok_od + (int) $userBalance->ostatok_percents;
        $referralDiscountAmount = $userBalance->referral_discount_amount;
        $cap = (int) floor(($amount * 0.30) / 10) * 10;

        // Если оплата не всей суммы - можем предоставить скидку только 30% от суммы платежа
        if ($debt > $amount) {
            return min($referralDiscountAmount, $cap);
        }

        if ($referralDiscountAmount > $amount) {
            return $amount;
        }

        return $referralDiscountAmount;
    }

    public function checkIsUserReferral(\Request $request): void
    {
        // Проверяем перешёл ли человек по реферальной ссылке
        if (!empty($_COOKIE["referer_id"])) {
            return;
        }

        if (!$referrerId = $request->get('referer_id')) {
            return;
        }

        setcookie("referer_id", $referrerId, time() + (365 * 24 * 60 * 60), '/', config('app.main_domain'));
    }

    public function setRefererIdToOrderData(int $orderId, string $referralUID): void
    {
        logger('referral_link')->warning("start setting to order data $orderId $referralUID");
        $refererId = $_COOKIE["referer_id"];
        if (!$refererId) {
            logger('referral_link')->warning("referer_id not set $orderId $referralUID");
            return;
        }

        if ($referralUID === $refererId) {
            logger('referral_link')->warning('Referral and referrer ids are identical ' . $referralUID);
            return;
        }

        $this->orderData->set($orderId, 'referer_id', $refererId);
        logger('referral_link')->info("Order data was set $orderId $referralUID $refererId");

        // Удаляем куку
        setcookie('referer_id', '', time() - 3600, '/', config('app.main_domain'));
    }

    public function getRefererUrl(StdClass $user): ?string
    {
        $refererUrl = $this->userData->read($user->id, 'referer_url');
        if (!empty($refererUrl)) {
            return htmlspecialchars($refererUrl, ENT_QUOTES);
        }

        $refererUrl = $this->shortLinkService->getReferralShortLink($user->id, $user->uid);
        if ($refererUrl) {
            $this->userData->set($user->id, 'referer_url', $refererUrl);
            return htmlspecialchars($refererUrl, ENT_QUOTES);
        }

        logger('nc')->error("Пришла пустая реферальная ссылка с нц для клиента: $user->id $user->uid");

        return null;
    }

    public function canShowRefererBanner(int $userId): bool
    {
        return !empty($this->userData->read($userId, 'canShowRefererBanner'));
    }
}