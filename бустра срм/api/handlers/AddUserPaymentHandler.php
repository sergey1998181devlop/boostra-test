<?php

namespace api\handlers;

use App\Core\Application\Application;
use App\Modules\Card\Services\CardService;
use App\Modules\SbpAccount\Services\SbpAccountService;
use Simpla;

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

require_once dirname(__DIR__) . '/Simpla.php';

class AddUserPaymentHandler extends Simpla
{
    private CardService $cardService;
    private SbpAccountService $sbpAccountService;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->cardService = $app->make(CardService::class);
        $this->sbpAccountService = $app->make(SbpAccountService::class);
    }

    public function handle($orderId, $userId, $paymentDate, $manager, $attachment = null): void
    {
        $confirmedAt = date('Y-m-d H:i:s');
        $paymentFormatted = date('d.m.Y', strtotime($paymentDate));

        $contractNumber = $this->contracts->get_contract_by_params(['order_id' => (int)$orderId])->number;

        if (!empty($attachment)) {
            $confirmedFormatted = date('d.m.Y H:i', strtotime($confirmedAt));
            $commentText = 'Чек об оплате по реквизитам по договору ' . $contractNumber
                . ' подтверждён ' . $confirmedFormatted . '.';

            $receiptLink = rtrim($this->config->back_url, '/') . '/ajax/view_payment_receipt.php?key=' . urlencode($attachment);
            $commentText .= "\nСсылка на чек: " . $receiptLink;
        } else {
            $commentText = $paymentFormatted . ' произведена оплата по реквизитам по договору ' . $contractNumber . '.';
        }

        $orderShort = $this->orders->get_order_short((int)$orderId);
        $order1cNumber = $orderShort->{"1c_id"} ?? '';

        // Отключаем рекуррентные списания по картам и СБП
        $cards = $this->best2pay->get_cards(['user_id' => $userId]);
        if (!empty($cards)) {
            $cardAutodebitParams = [];
            foreach ($cards as $card) {
                $cardAutodebitParams[$card->id] = 0;
            }
            $this->cardService->changeAutodebitParam(
                $cardAutodebitParams,
                (int)$userId,
                (int)$orderId,
                (int)$manager->id,
                (string)$this->users->getUserUidById($userId)
            );
        }

        $sbpAccounts = $this->sbpAccount->getSbpAccountsByUserId((int)$userId);
        if (!empty($sbpAccounts)) {
            $sbpAutodebitParams = [];
            foreach ($sbpAccounts as $sbpAccount) {
                $sbpAutodebitParams[$sbpAccount->id] = 0;
            }

            $this->sbpAccountService->changeAutodebitParam(
                $sbpAutodebitParams,
                (int)$userId,
                (int)$orderId,
                (int)$manager->id,
                (string)$this->users->getUserUidById($userId)
            );
        }

        $this->comments->add_comment([
            'manager_id' => $manager->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'block' => 'autodebit',
            'text' => $commentText,
            'created' => $confirmedAt,
        ]);

        $this->soap->send_comment([
            'manager' => $manager->name_1c,
            'text' => $commentText,
            'created' => $confirmedAt,
            'number' => $order1cNumber,
            'user_uid' => $this->users->getUserUidById($userId)
        ]);
    }
}