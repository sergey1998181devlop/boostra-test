<?php
header('Content-Type: application/json');

chdir('..');
use api\helpers\NewYearHelper;

require 'api/Simpla.php';
require 'api/helpers/NewYearHelper.php';

class PromoBannersActions extends Simpla
{
    public function analyzeActions()
    {
        $action = $this->request->post('action', 'string');

        if ($action === 'get_promo_code') {
            $userId = $this->request->post('user_id', 'integer');

            $promoHelper = new NewYearHelper();
            $promoCodeMessage = $promoHelper->generateAndDisplayPromoCode($userId);


            echo json_encode([
                'promo_code_message' => $promoCodeMessage
            ]);
        }
    }
}

(new PromoBannersActions())->analyzeActions();
