<?php

require_once 'View.php';

class SettingsView extends View
{
    /**
     * Чекбоксы настроек на странице.
     *
     * Будут подтянуты текущие значения из `$this->settings` в `$this->design->assign(...)`.
     */
    const SETTINGS_ON_PAGE = [
        'captcha_status',
        'new_flow_enabled',
        'check_reports_for_loans_enable',
        'auto_confirm_for_auto_approve_orders_enable',
        'need_notify_user_when_scorista_success',
        'bonon_enabled',
        'short_flow_enabled',
        'pdn_organic_enabled',
        'self_dec_before_loan_issuance_enabled',
        'terrorist_check_before_loan_issuance_enabled',
        'axi_spr_enabled',
        'cross_orders_nk_enabled',
        'check_uprid_enabled',
    ];

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');
        
        if ($this->request->method('post'))
        {
            $scoring_settings = $this->request->post('settings');


            foreach ($scoring_settings as $scoring_type)
            {
                $update_item = new StdClass();
//                $update_item->type = (string)$scoring_type['type'];
                $update_item->negative_action = (string)$scoring_type['negative_action'];
                $update_item->active = isset($scoring_type['active']) ? (int)$scoring_type['active'] : 0;

                if (isset($scoring_type['params'])) {
                    $update_item->params = (array)$scoring_type['params'];
                }

                $this->scorings->update_type($scoring_type['id'], $update_item);
            }

            if ($positions = $this->request->post('position'))
            {
                $i = 1;
                foreach ($positions as $pos => $id)
                    $this->scorings->update_type($id, array('position' => $i++));
            }
        }
        else
        {
            $scoring_settings = $this->settings->scoring_settings;
        }
        
        if (!empty($scoring_settings))
            $this->design->assign('scoring_settings', $scoring_settings);
        
        $scoring_types = $this->scorings->get_types();
        $this->design->assign('scoring_types', $scoring_types);

        foreach (self::SETTINGS_ON_PAGE as $setting_name) {
            $setting_value = $this->settings->$setting_name;
            $this->design->assign($setting_name, $setting_value);
        }

        $show_sbp_banks_for_autoapprove_orders = $this->settings->show_sbp_banks_for_autoapprove_orders;
        $this->design->assign('show_sbp_banks_for_autoapprove_orders', $show_sbp_banks_for_autoapprove_orders);

        $this->design->assign('organizations', $this->organizations->getList());

        return $this->design->fetch('settings.tpl');
    }
}