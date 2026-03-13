<?php

require_once 'View.php';

/**
 * Панель управления рассылкой, для одобренных заявок
 * Class SettingsSmsNoticeApproveView
 */
class SettingsSmsNoticeApproveView extends View
{
    /**
     * @var bool|int|mixed|string|null
     */
// Объявление свойства
    private ?string $active_site_id = null;
    /**
     * @var array|false
     */
    private $active_sites;

    public function __construct()
    {
        parent::__construct();


        // Всегда определяем site_id ДО обработки POST
        $siteId = $this->request->get('site_id');
        if (!$siteId) {
            $siteId = $this->request->post('site_id'); // если передаёшь hidden input
        }

        $activeSites = $this->sites->getActiveSites();

        // если site_id не задан/невалидный — берём первый активный
        if (empty($siteId) && !empty($activeSites)) {
            $siteId = $activeSites[0]->site_id;
        }

        $this->active_site_id = (string)$siteId;
        $this->settings->setSiteId($siteId, false);
        $this->active_sites   = $activeSites;

        if ($this->request->method('post')) {
            if ($settings = $this->request->post('notice_sms_approve')) {
                $this->settings->notice_sms_approve = $settings;
            }

            if ($sms_template_motivation_close = $this->request->post('sms_template_motivation_close')) {
                $this->settings->sms_template_motivation_close_status = (int)($sms_template_motivation_close['status'] ?? 0);
                $this->sms->update_template($this->sms::SMS_TEMPLATE_MOTIVATION_CLOSE, [
                    'template' => trim($sms_template_motivation_close['template']),
                ], $this->active_site_id);
            }

            if ($sms_template_phone_partner = $this->request->post('sms_template_phone_partner')) {
                $this->settings->sms_template_phone_partner = $sms_template_phone_partner;
            }

            if ($sms_template_approve = $this->request->post('sms_template_approve')) {
                $this->sms->update_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW, [
                    'template' => trim($sms_template_approve['template']),
                ], $this->active_site_id);

                $this->settings->setSiteId($this->active_site_id, false);
                $this->settings->sms_approve_status = (int)($sms_template_approve['status'] ?? 0);
            }

            if ($sms_template_reject = $this->request->post('sms_template_reject')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_NOW, [
                    'template' => trim($sms_template_reject['template']),
                    'status' => $sms_template_reject['status']
                ], $this->active_site_id);
            }

            if ($sms_template_reject_second = $this->request->post('sms_template_reject_second')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_SECOND, [
                    'template' => trim($sms_template_reject_second['template']),
                    'status' => $sms_template_reject_second['status']
                ], $this->active_site_id);
            }

            if ($sms_template_reject_third = $this->request->post('sms_template_reject_third')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_THIRD, [
                    'template' => trim($sms_template_reject_third['template']),
                    'status' => $sms_template_reject_third['status']
                ], $this->active_site_id);
            }

            if ($sms_template_reject_fourth = $this->request->post('sms_template_reject_fourth')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_FOURTH, [
                    'template' => trim($sms_template_reject_fourth['template']),
                    'status' => $sms_template_reject_fourth['status']
                ], $this->active_site_id);
            }

            if ($sms_template_reject_fifth = $this->request->post('sms_template_reject_fifth')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_FIFTH, [
                    'template' => trim($sms_template_reject_fifth['template']),
                    'status' => $sms_template_reject_fifth['status']
                ], $this->active_site_id);
            }

            if ($sms_template_include_emergency = $this->request->post('sms_template_emergency')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_EMERGENCY, [
                    'template' => trim($sms_template_include_emergency['template']),
                    'status' => $sms_template_include_emergency['status'],
                ], $this->active_site_id);
            }

            if ($sms_template_invalid_passport = $this->request->post('sms_template_invalid_passport')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_INVALID_PASSPORT, [
                    'template' => trim($sms_template_invalid_passport['template']),
                    'status' => $sms_template_invalid_passport['status'],
                ], $this->active_site_id);
            }

            if ($sms_template_expired = $this->request->post('sms_template_expired')) {
                $this->sms->update_template($this->sms::AUTO_REJECT_TEMPLATE_EXPIRED, [
                    'template' => trim($sms_template_expired['template']),
                    'status' => $sms_template_expired['status'],
                ], $this->active_site_id);
            }

            if ($this->manager->role == 'developer' || $this->manager->role == 'ts_operator') {
                if ($sms_template_likezaim = $this->request->post('sms_template_likezaim')) {
                    $this->sms->update_template($this->sms::SMS_TEMPLATE_LIKEZAIM, [
                        'template' => trim($sms_template_likezaim['template']),
                        'status' => $sms_template_likezaim['status']
                    ], $this->active_site_id);
                }

                $this->settings->setSiteId($this->active_site_id, false);
                $this->settings->likezaim_enabled = $this->request->post('likezaim_enabled', 'integer');
                $this->settings->temporary_sms_unsubscribe_days = $this->request->post('temporary_sms_unsubscribe_days', 'integer');
            }

            header("Location: /settings_sms_notice_approve?site_id=" . $this->active_site_id);
            exit();


        } else {

            // check if the siteId exists in the database (in the activeSites)
            $siteExists = array_search($this->active_site_id, array_column($this->active_sites, 'site_id'));

            if ($siteExists === false && !empty($activeSites)) {
                $siteId = $activeSites[0]->site_id; // set the first site id
                //perform your redirect here with the first siteId
                header("Location: /settings_sms_notice_approve?site_id=" . $siteId);
                exit();
            }

            $action = $this->request->get('action');
            if (method_exists(self::class, $action)) {
                $this->{$action}();
            }
        }
    }

    public function fetch()
    {
        $settings_notice_sms_approve = $this->settings->notice_sms_approve;

        $templates = $this->sms->get_templates(['site_id' => $this->active_site_id]);
        $templates_by_id = [];
        foreach ($templates as $t) {
            $templates_by_id[$t->id] = $t;
            $t->template = $t->{'template_'.$this->active_site_id};
        }

        $sms_template_approve          = $templates_by_id[$this->sms::AUTO_APPROVE_TEMPLATE_NOW] ?? null;
        $sms_template_motivation_close = $templates_by_id[$this->sms::SMS_TEMPLATE_MOTIVATION_CLOSE] ?? null;
        $sms_template_reject           = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_NOW] ?? null;
        $sms_template_reject_second    = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_SECOND] ?? null;
        $sms_template_reject_third     = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_THIRD] ?? null;
        $sms_template_reject_fourth    = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_FOURTH] ?? null;
        $sms_template_reject_fifth     = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_FIFTH] ?? null;
        $sms_template_likezaim         = $templates_by_id[$this->sms::SMS_TEMPLATE_LIKEZAIM] ?? null;
        $sms_template_emergency        = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_EMERGENCY] ?? null;
        $sms_template_invalid_passport = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_INVALID_PASSPORT] ?? null;
        $sms_template_expired          = $templates_by_id[$this->sms::AUTO_REJECT_TEMPLATE_EXPIRED] ?? null;


        $total_sms = $this->sms->getTotalSmsApprove();

        $settings = [];
        for ($i = 1; $i < 8; $i++) {

            $find_totals_array = array_filter($total_sms, function ($item) use ($i) {
                return $item->day_after_approve == $i;
            });
            $totals = array_shift($find_totals_array);
            $settings[$i] = $settings_notice_sms_approve['message_day_' . $i] ?? [
                'text' => '',
            ];

            $settings[$i]['total_sms'] = $totals->total ?? 0;
        }

        $this->design->assign('settings_approve', $settings);

        $this->design->assign('sms_approve_status', $this->settings->sms_approve_status);
        $this->design->assign('sms_template_approve', $sms_template_approve);

        $this->design->assign('sms_template_motivation_close', $sms_template_motivation_close);
        $this->design->assign('sms_template_motivation_close_status', $this->settings->sms_template_motivation_close_status);
        $this->design->assign('sms_template_reject', $sms_template_reject);
        $this->design->assign('sms_template_reject_second', $sms_template_reject_second);
        $this->design->assign('sms_template_reject_third', $sms_template_reject_third);
        $this->design->assign('sms_template_reject_fourth', $sms_template_reject_fourth);
        $this->design->assign('sms_template_reject_fifth', $sms_template_reject_fifth);
        $this->design->assign('sms_template_likezaim', $sms_template_likezaim);
        $this->design->assign('sms_template_emergency', $sms_template_emergency);
        $this->design->assign('sms_template_invalid_passport', $sms_template_invalid_passport);
        $this->design->assign('sms_template_expired', $sms_template_expired);

        $this->design->assign('temporary_sms_unsubscribe_days', $this->settings->temporary_sms_unsubscribe_days);
        $this->design->assign('active_sites', $this->active_sites);
        $this->design->assign('active_site_id', $this->active_site_id);

        return $this->design->fetch('settings_sms_notice_approve.tpl');
    }

    /**
     * Список смс
     * @return void
     */
    private function getSMSList()
    {
        $current_page = max(1, $this->request->get('page', 'integer'));
        $this->design->assign('current_page_num', $current_page);

        $filter_sms = [
            'type' => $this->smssender::TYPE_AUTO_APPROVE_ORDER,
            'page' => $current_page,
            'limit' => 50,
        ];

        $count_sms = $this->sms->count_messages($filter_sms);
        $pages_num = ceil($count_sms / $current_page);

        $sms_messages = $this->sms->get_messages($filter_sms);

        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_sms_count', $count_sms);
        $this->design->assign('sms_messages', $sms_messages);
        $this->design->assign('items', $sms_messages);

        $html = $this->design->fetch('sms_approve_list.tpl');
        $this->response->html_output($html);
    }
}