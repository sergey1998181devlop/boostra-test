<?php

use App\Core\Application\Application;
use App\Service\SystemNoticeSettingsService;

class SiteSettingsView extends View
{

    private SystemNoticeSettingsService $systemNoticeService;

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->systemNoticeService = $app->make(SystemNoticeSettingsService::class);
    }

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');

        $organizationsForSBP = $this->organizations->get_organizations_by_ids([
            Organizations::AKVARIUS_ID,
            Organizations::FINLAB_ID,
            Organizations::RZS_ID,
            Organizations::LORD_ID,
            Organizations::MOREDENEG_ID,
        ]);

        $currentSiteId = $this->request->method('post')
            ? $this->request->post('site_id', 'string')
            : $this->request->get('site_id', 'string');

        if (empty($currentSiteId)) {
            $currentSiteId = null;
        }

        $this->settings->setSiteId($currentSiteId);

        $visibleSettingNames = $this->settings->getVisibleSettingNames();

        // Сохранение настроек
        if ($this->request->method('post'))
        {
            $customProcessedSettings = [
                'sbp_enabled',
                'sbp_recurrents_enabled',
                'refinance_settings',
                'auto_step_no_need_for_underwriter',
                'flow_after_personal_data',
                't_bank_button_registration',
                'esia_button_registration',
                'autoconfirm_flow_utm_sources',
                'autoconfirm_2_flow_utm_sources',
                'autoconfirm_2_flow_cross_utm_sources',
                'autoconfirm_crm_auto_approve_utm_sources',
                'non_organic_utm_sources',
                'disable_bank_selection_utm_sources',
                'returning_users_flow_utm_sources',
                'mark_418_test_leadgids',
                'site_warning_banner_config',
                'no_need_for_underwriter_card_step_disabled',
                'organization_switch'
            ];

            $input = $this->request->input();

            foreach ($input as $key => $value) {
                if ($key === 'site_id' || in_array($key, $customProcessedSettings)) {
                    continue;
                }

                if ($currentSiteId === null || in_array($key, $visibleSettingNames)) {
                    $this->settings->$key = $value;
                }
            }

            $this->processCustomSettings($organizationsForSBP, $visibleSettingNames);
        }

        $this->design->assign_array([
            'all_sites' => $this->sites->getActiveSites(),
            'organizations_for_issuance' => $this->organizations->get_organizations_for_issuance(),
            'organizations' => $this->organizations->getList(),
            'organizations_for_sbp' => $organizationsForSBP,
            'current_site_id' => $currentSiteId,
            'site_setting_names' => $visibleSettingNames
        ]);

        return $this->design->fetch('site_settings.tpl');
    }

    private function setNameToKey($names, $keys): array
    {
        $return = [];
        foreach ($names as $obj) {
            if (isset($keys[$obj->id])) {
                $return[$obj->short_name] = $keys[$obj->id];
            }
        }

        return $return;
    }

    private function logChange($type, $newValue, $oldValue): void
    {
        if ($newValue !== null && $oldValue != $newValue ) {
            if (is_array($oldValue)) {
                $oldValue = serialize($oldValue);
            }
            if (is_array($newValue)) {
                $newValue = serialize($newValue);
            }

            $this->changelogs->add_changelog([
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => $type,
                'old_values' => $oldValue,
                'new_values' => $newValue,
            ]);
        }
    }

    /**
     * Кастомная обработка специальных настроек
     * Используется для настроек, требующих дополнительной обработки перед сохранением
     */
    private function processCustomSettings($organizationsForSBP, array $visibleSettings): void
    {
        $this->updateSystemNoticeConfig();

        if ($this->request->post('sbp_enabled') !== null) {
            $sbpOldLog = $this->setNameToKey($organizationsForSBP, $this->settings->sbp_enabled);
            $sbpLog = $this->setNameToKey($organizationsForSBP, $this->request->post('sbp_enabled'));
            $this->logChange('SBP_enabled', $sbpLog, $sbpOldLog);
            $this->settings->sbp_enabled = $this->request->post('sbp_enabled');
        }

        if ($this->request->post('refinance_settings') !== null) {
            $this->logChange('refinance_settings', 'Refinance_settings', $this->settings->refinance_settings);
            $this->settings->refinance_settings = $this->request->post('refinance_settings');
        }

        $utmSourcesSettings = [
            'auto_step_no_need_for_underwriter',
            'flow_after_personal_data',
            't_bank_button_registration',
            'esia_button_registration',
            'il_nk_loan_edit_amount',
            'no_need_for_underwriter_card_step_disabled',
            'organization_switch'
        ];

        foreach ($utmSourcesSettings as $settingName) {
            if ($setting = $this->request->post($settingName)) {
                if (isset($setting['utm_sources']) && is_string($setting['utm_sources'])) {
                    $setting['utm_sources'] = array_map('trim', explode(",", $setting['utm_sources']));
                }
                $this->settings->$settingName = $setting;
            }
        }

        $simpleUtmSettings = [
            'autoconfirm_flow_utm_sources',
            'autoconfirm_2_flow_utm_sources',
            'autoconfirm_2_flow_cross_utm_sources',
            'autoconfirm_crm_auto_approve_utm_sources',
            'non_organic_utm_sources',
            'disable_bank_selection_utm_sources',
            'returning_users_flow_utm_sources',
            'mark_418_test_leadgids',
            'partner_api_repeat_client_utm_sources'
        ];

        foreach ($simpleUtmSettings as $settingName) {
            if ($utmSources = $this->request->post($settingName)) {
                if (is_string($utmSources)) {
                    $utmSources = array_map('trim', explode(",", $utmSources));
                }
                $this->settings->$settingName = $utmSources;
            }
        }

        $customCheckboxes = [
            'sbp_recurrents_enabled' => [$this, 'updateSbpRecurrentsSetting'],
        ];

        foreach ($customCheckboxes as $settingName => $handler) {
            if (in_array($settingName, $visibleSettings)) {
                $handler();
            }
        }
    }

    /**
     * Обработка и сохранение настроек баннера предупреждений
     * @return void
     */
    private function updateSystemNoticeConfig(): void
    {
        $bannerConfig = $this->request->post('banner_config');
        if (!$bannerConfig) {
            return;
        }

        $currentSiteId = $this->request->post('site_id', 'string') ?: 'boostra';
        $this->settings->setSiteId($currentSiteId);

        $oldConfig = $this->settings->site_warning_banner_config;

        $newConfig = $this->systemNoticeService->process($bannerConfig);

        if ($newConfig === null) {
            return;
        }

        $this->logChange('site_warning_banner_config', $newConfig, $oldConfig);

        $this->settings->site_warning_banner_config = $newConfig;
    }

    /** Включение/отключение рекуррентов по СБП */
    public function updateSbpRecurrentsSetting(): void
    {
        $sbpRecurrentsNewValue = $this->request->post('sbp_recurrents_enabled');
        if ($sbpRecurrentsNewValue !== $this->settings->sbp_recurrents_enabled) {
            $curl = curl_init();
            $data = [
                'name' => 'sbp_recurrents_enabled_boostra',
                'value' => $sbpRecurrentsNewValue === '1' ? 'enabled' : 'disabled'
            ];

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://rc.mkkcollection.ru/api/settings/update',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            curl_exec($curl);
        }

        $this->settings->sbp_recurrents_enabled = $sbpRecurrentsNewValue;
    }
}
