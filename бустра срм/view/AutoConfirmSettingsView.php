<?php

require_once 'View.php';

/**
 * Автоодорения
 * Class AutoConfirmSettingsView
 */
class AutoConfirmSettingsView extends View {

    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if ($this->request->method('post') && $settings = $this->request->post('auto_approve')) {
            $this->settings->auto_approve = $settings;
            $this->design->assign('save_success', true);
        }

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch() {

        $settings_auto_approve = $this->settings->auto_approve;

        $this->design->assign('days_after_closed', $settings_auto_approve['days_after_closed'] ?? null);
        $this->design->assign('days_available', $settings_auto_approve['days_available'] ?? null);
        $this->design->assign('client_types', $settings_auto_approve['client_types'] ?? []);
        $this->design->assign('status_nk', $settings_auto_approve['status_nk'] ?? null);

        return $this->design->fetch('auto_confirm_settings.tpl');
    }
}
