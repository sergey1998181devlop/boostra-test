<?php

require_once 'View.php';

class AutoConfirmAspView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        $this->jwtAuthValidate();

        if (!$this->user_data->read($this->user->id, $this->user_data::AUTOCONFIRM_FLOW)) {
            $this->request->redirect($this->config->root_url . '/account');
        }

        $last_order = $this->orders->get_last_order($this->user->id);
        $decisionSum = $this->autoconfirm->getAutoConfirmAmount($this->user->id, $last_order);

        $get_params = [
            'params' => [
                'percent' => $last_order->percent,
                'period' => $last_order->period,
                'amount' => $decisionSum ?: $last_order->amount,
            ],
            'user_id' => $this->user->id,
        ];

        if (empty($decisionSum)) {
            $get_params['params']['hide_user_data'] = 1;
        }

        $this->design->assign('individual_url', $this->config->root_url . '/preview/IND_USLOVIYA?' . http_build_query($get_params));

        $promo_block = $this->promocodes->promoCodeModeAutoConfirmNewUser($last_order);
        $this->design->assign('promo_block', $promo_block);

        if (!empty($last_order->promocode)) {
            $promocode = $this->promocodes->getInfoById($last_order->promocode);
        }

        $this->design->assign('promo_code', $promocode->promocode);

        $user_from_partner_api = $this->user_data->read($this->user->id, $this->user_data::PARTNER_USER_RESPONSE);
        $this->design->assign('user_from_partner_api', $user_from_partner_api);
        $this->design->assign('order', $last_order);

        return $this->design->fetch('auto_confirm_asp.tpl');
    }
}
