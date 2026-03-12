<?php

require_once 'View.php';

class AutoConfirmAspView extends View
{
    use \api\traits\JWTAuthTrait;

    const DOCUMENTS = [
        ['type' => 'ind_usloviya',          'title' => 'Индивидуальные условия договора займа'],
        ['type' => 'asp_usage_policy',      'title' => 'Положение об использовании АСП'],
        ['type' => 'personal_data_consent', 'title' => 'Согласие на хранение и обработку персональных данных'],
        ['type' => 'marketing_consent',     'title' => 'Согласие на получение маркетинговых коммуникаций'],
    ];

    public function fetch()
    {
        $this->jwtAuthValidate();

        if (!$this->user_data->read($this->user->id, $this->user_data::AUTOCONFIRM_FLOW)) {
            $this->request->redirect($this->config->root_url . '/account');
        }

        $last_order = $this->orders->get_last_order($this->user->id);
        if ($this->orders->isCrossOrder($last_order)) {
            $last_order = $this->orders->get_order($last_order->utm_medium);
        }

        $decisionSum = $this->autoconfirm->getAutoConfirmAmount($this->user->id, $last_order);

        $get_params = [
            'params' => [
                'percent' => $last_order->percent,
                'period' => $last_order->period,
                'amount' => $decisionSum ?: $last_order->amount,
            ],
            'user_id' => $this->user->id,
            'organization_id' => $last_order->organization_id,
        ];

        if (empty($decisionSum)) {
            $get_params['params']['hide_user_data'] = 1;
        }

        $docs_list = self::DOCUMENTS;
        $is_rcl_loan = !empty($this->order_data->read($last_order->id, $this->order_data::RCL_LOAN));
        if ($is_rcl_loan) {
            $docs_list[] = [
              'type' => 'rcl_limit',
              'title' => 'Заявление на установку расходного кредитного лимита',
            ];
            $docs_list[] = [
                'type' => 'rcl_transh',
                'title' => 'Заявление на получение транша',
            ];

            $get_params['params']['rcl_amount'] =
                $this->order_data->read($last_order->id, $this->order_data::RCL_AMOUNT)
                ?: $decisionSum
                ?: $last_order->amount;
            $get_params['params']['amount_string'] = $this->documents->convertAmountToString($get_params['params']['amount']);
            $get_params['params']['rcl_amount_string'] = $this->documents->convertAmountToString($get_params['params']['rcl_amount']);
            $get_params['params']['rcl_max_amount'] = $this->order_data->read($last_order->id, $this->order_data::RCL_MAX_AMOUNT);
            $get_params['params']['rcl_max_amount_string'] = $this->documents->convertAmountToString($get_params['params']['rcl_max_amount']);

            $this->design->assign('rcl_loan', $last_order->id);
            $this->design->assign('rcl_amount', $get_params['params']['rcl_amount']);
        }
        foreach ($docs_list as &$doc) {
            if ($is_rcl_loan && $doc['type'] == 'ind_usloviya') {
                $doc['type'] = 'rcl_ind_usloviya';
            }

            $doc['url'] = $this->config->root_url . '/preview/' . $doc['type'] . '?' . http_build_query($get_params);
        }
        $this->design->assign('docs_list', $docs_list);

        $promo_block = $this->promocodes->promoCodeModeAutoConfirmNewUser($last_order);
        $this->design->assign('promo_block', $promo_block);

        if (!empty($last_order->promocode)) {
            $promocode = $this->promocodes->getInfoById($last_order->promocode);
            $this->design->assign('promo_code', $promocode->promocode);
        }

        $user_from_partner_api = $this->user_data->read($this->user->id, $this->user_data::PARTNER_USER_RESPONSE);
        $this->design->assign('user_from_partner_api', $user_from_partner_api);
        $this->design->assign('order', $last_order);

        return $this->design->fetch('auto_confirm_asp.tpl');
    }
}
