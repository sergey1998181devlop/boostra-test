<?php

/**
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

require_once('Simpla.php');

/**
 * @property string $site_name
 * @property string $company_name
 * @property string $theme
 * @property string $products_num
 * @property string $products_num_admin
 * @property string $units
 * @property string $date_format
 * @property string $order_email
 * @property string $comment_email
 * @property string $notify_from_email
 * @property string $decimals_point
 * @property string $thousands_separator
 * @property string $last_1c_orders_export_date
 * @property string $license
 * @property string $max_order_amount
 * @property string $watermark_offset_x
 * @property string $watermark_offset_y
 * @property string $watermark_transparency
 * @property string $images_sharpen
 * @property string $admin_email
 * @property string $pz_server
 * @property string $pz_password
 * @property array $pz_phones
 * @property string $api_password
 * @property array $apikeys
 * @property array $scoring_settings
 * @property string $cdoctor_enabled
 * @property string $verificator_daily_plan_pk
 * @property string $verificator_daily_plan_nk
 * @property string $selenoid
 * @property array $individual_settings
 * @property string $last_update_border_date
 * @property string $recaptcha_key
 * @property string $recaptcha_secret
 * @property string $cc_pr_prolongation_plan
 * @property string $cc_pr_close_plan
 * @property string $next_mobile_version
 * @property array $additional_services_settings
 * @property string $is_CB
 * @property string $is_looker
 * @property array $insurance_threshold_settings
 * @property string $captcha_status
 * @property string $last_session_id
 * @property array $insurance_threshold_setting
 * @property array $notice_sms_approve
 * @property array $auto_approve
 * @property string $sms_approve_status
 * @property string $sms_template_motivation_close_status
 * @property string $percent_insurer_boostra
 * @property string $enable_loan_nk
 * @property array $enable_prolongation_checkbox
 * @property string $fake_try_prolongation_checkbox
 * @property string $gov_auth
 * @property string $enable_b2p_for_nk
 * @property string $b2p_dop_organization
 * @property string $tinkoff_dop_organization
 * @property string $repay_max_count
 * @property string $repay_timeout
 * @property string $ccprolongations1
 * @property string $ccprolongations2
 * @property string $ccprolongations3
 * @property string $ccprolongations4
 * @property string $ccprolongations5
 * @property string $delete_after_01072023
 * @property string $send_cd_date
 * @property array $sum_order_auto_approve
 * @property string $check_redirect_list
 * @property string $likezaim_enabled
 * @property string $site_warning_banner_config
 * @property string $addresses_is_dadata
 * @property string $dbrain_auto
 * @property string $autoapprove_plus_30
 * @property string $split_test_users
 * @property string $hui
 * @property string $installment_test_users
 * @property string $enabled_5days_maratorium
 * @property string $installments_enabled
 * @property string $addcard_rejected_enabled
 * @property string $cross_orders_enabled
 * @property string $pdn_sync_day
 * @property string $safe_flow Вкл/выкл безопасное флоу
 * @property string $unsafe_flow Вкл/выкл опасное флоу
 * @property string $new_flow_enabled Включено ли новое флоу с УПРИДом
 * @property string $leadgid_scorista_enabled Включена ли таблица с настройками минимального проходного балла для лидгенов
 * @property array $sbp_enabled
 * @property string $check_reports_for_loans_enable Включена ли проверка актуальности ССП и КИ отчетов при выдаче займов
 * @property string $auto_confirm_for_auto_approve_orders_enable Включено ли авто-подтверждение (с отправкой смс с АСП-кодом) авто-одобренных заявок
 * @property array $prolongation_visible Настройки видимости баннера пролонгации для бакетов с -5 по 0
 * @property array $prolongation_text Переопределение текста баннера пролонгации для бакетов с -5 по 0
 * @property string $display_policy_days - Количество дней через которое отображаются полисы
 * @property string $bonon_enabled Включена ли продажа карт отказных НК клиентов
 * @property array $bonon_sources Продаваемые в Bonon источники
 * @property string $short_flow_enabled Короткое флоу регистрации включено
 * @property string $need_notify_user_when_scorista_success Нужно ли уведомлять пользовать об одобрении скористы
 * @property boolean $registration_disabled_captcha Выключена ли капча на странице входа и регистрации нового пользователя
 * @property array $flow_after_personal_data Настройки флоу телефон после ФИО + паспорт
 * @property array $autoconfirm_flow_utm_sources Utm метки для потока трафика НК автовыдачи
 * @property string $axi_spr_enabled Часть потока ориентируется только на решение Акси, скориста не делает автоотказ и не ставит суммы в заявках
 * @property string $notice_contact_me_enabled Включён ли нотис "Свяжитесь со мной" в ЛК?
 * @property string $notice_contact_me_enabled_for Нотис "Свяжитесь со мной" в ЛК доступен для: 1 - ПК, 2 - НК, 3 - Всех?
 * @property array $t_bank_button_registration Кнопка T-Bank на регистрации
 * @property boolean $faq_highlight_enabled Флаг активации подсветки раздела "Вопросы и ответы" в ЛК
 * @property int $faq_highlight_delay Задержка в минутах до подсветки раздела "Вопросы и ответы" в ЛК
 * @property int $return_threshold_days_fd Настройки дней, в течении которых клиенту виден ФД
 * @property int $return_threshold_days_zo Настройки дней, в течении которых клиенту виден ЗО
 * @property boolean $sbp_issuance_enabled Включение выплат по СБП
 * @property array $autoconfirm_crm_auto_approve_utm_sources Utm метки для потока трафика для заявок utm_source=crm_auto_approve
 * @property bool $hide_order_information Скрытие информации(сумма займа) в ЛК
 * @property bool $whitelist_dop Отключение всех ДОПов для пользователей из белого списка
 * @property bool $fake_dops Фейковые ДОПы
 * @property array $non_organic_utm_sources Список неорганических источников трафика
 * @property array $show_sbp_banks_for_autoapprove_orders Показывать список банков для выплаты по СБП без привязки (не только для автозаявок)
 * @property bool $il_enabled
 * @property array $disable_bank_selection_utm_sources Utm метки для отключения выбора банка при привязке карты (id банка получаем при привязке карты)
 * @property array $sms_template_phone_partner
 * @property bool $returning_users_flow_utm_sources Utm метки для клиентов, которые вернулись после длительного отсутствия для продолжения регистрации
 * @property array $partner_api_repeat_client_utm_sources utm партнерского апи для повторников
 * @property array $il_nk_loan_edit_amount Настройки ИЛ займа, изменении суммы при подаче НК
 * @property array $organization_switch Настройки переключения организации в заявке
 * @property string $base_organization_id Базовая организация
 * @property array $usedesk_settings Настройки usedesk
 *
 */
class Settings extends Simpla
{
	private $vars = array();
	private ?string $currentSiteId = null;

    const CONTACT_ME_NOTICE_ENABLED_FOR_REPEAT_CLIENTS = 1;
    const CONTACT_ME_NOTICE_ENABLED_FOR_NEW_CLIENTS = 2;
    const CONTACT_ME_NOTICE_ENABLED_FOR_ALL = 3;

	function __construct()
	{
		parent::__construct();

		$site_id = $this->config->site_id;
		if ($site_id !== null && $site_id !== '') {
			$this->currentSiteId = $site_id;
		}

		$this->loadSettings();

        if (!empty($_COOKIE['theme']))
        {
            $this->vars['theme'] = 'akticom';
        }
    }

	/**
	 * Загрузка настроек из БД с учетом site_id
	 * Приоритет: сначала глобальные (site_id IS NULL), затем site-specific
	 *
	 * @param bool $use_fallback Использовать fallback на глобальные настройки (по умолчанию true)
	 */
	private function loadSettings(bool $use_fallback = true)
	{
		if ($this->currentSiteId === null) {
			$this->db->query('SELECT name, value FROM __settings WHERE site_id IS NULL');
		} else {
			if ($use_fallback) {
				$this->db->query('SELECT name, value FROM __settings WHERE site_id IS NULL');
				foreach($this->db->results() as $result) {
					$unserialized = @unserialize($result->value);
					$this->vars[$result->name] = ($unserialized !== false || $result->value === 'b:0;')
						? $unserialized
						: $result->value;
				}
			}

            $this->db->query('SELECT name, value FROM __settings WHERE site_id = ?', $this->currentSiteId);
		}

		foreach($this->db->results() as $result) {
			$unserialized = @unserialize($result->value);
			$this->vars[$result->name] = ($unserialized !== false || $result->value === 'b:0;')
				? $unserialized
				: $result->value;
		}

        // API ключи загружаем с отдельной логикой
        if ($use_fallback) {
            $this->loadApiKeys();
        }
	}

    private function loadApiKeys()
    {
        if (empty($this->currentSiteId)) {
            return;
        }

        $this->db->query("
            SELECT site_id, `value` 
            FROM __settings 
            WHERE 
                `name` = 'apikeys' AND 
                (
                    site_id IS NULL OR
                    site_id = ? 
                )
        ", $this->currentSiteId);

        $apikeys = $this->db->results();
        if (empty($apikeys)) {
            return;
        }

        $globalApikeys = [];
        $siteSpecificApikeys = [];
        foreach($apikeys as $apikeysRow) {
            if (!empty($apikeysRow->site_id)) {
                $siteSpecificApikeys = @unserialize($apikeysRow->value) ?? [];
            }
            else {
                $globalApikeys = @unserialize($apikeysRow->value) ?? [];
            }
        }

        $filteredSiteSpecificApikeys = [];
        foreach($siteSpecificApikeys as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $hasAnyValues = false;
                foreach($value as $subvalue) {
                    if (!empty($subvalue)) {
                        $hasAnyValues = true;
                        break;
                    }
                }

                if (!$hasAnyValues) {
                    continue;
                }
            }

            $filteredSiteSpecificApikeys[$key] = $value;
        }

        $this->vars['apikeys'] = array_replace_recursive($globalApikeys, $filteredSiteSpecificApikeys);
    }

	/**
	 * Установить контекст site_id и перезагрузить настройки
	 *
	 * @param string|null $site_id ID сайта или NULL для глобальных настроек
	 * @param bool $use_fallback Использовать fallback на глобальные настройки при загрузке (по умолчанию true)
	 */
	public function setSiteId(?string $site_id, bool $use_fallback = true)
	{
		$this->currentSiteId = $site_id;
		$this->vars = array();
		$this->loadSettings($use_fallback);
	}

    /**
     * Возвращает текущий id сайта.
     *
     * @return string|null Идентификатор сайта (site_id) или NULL для глобальных настроек
     */
    public function getSiteId(): ?string
    {
        return $this->currentSiteId;
    }

	public function __get($name)
	{
		if($res = parent::__get($name))
			return $res;
		
		if(isset($this->vars[$name]))
			return $this->vars[$name];
		else
			return null;
	}

    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
        $newValue = is_array($value) ? serialize($value) : (string)$value;

        $nameFilter = $this->db->placehold("AND name = ?", $name);

        if (!empty($this->currentSiteId)) {
            $siteIdFilter = $this->db->placehold("AND site_id =", $this->currentSiteId);
        } else {
            $siteIdFilter = $this->db->placehold("AND site_id IS NULL");
        }

        // Получение настроек
        $query = $this->db->placehold("
            SELECT * 
            FROM __settings
            WHERE 1
                $nameFilter
                $siteIdFilter
        ");
        $this->db->query($query);
        $settings = $this->db->results();

        // Если настройка по site_id не найдена, то пробуем найти глобальные
        if (empty($settings) && !empty($this->currentSiteId)) {
            $siteIdFilter = $this->db->placehold("AND site_id IS NULL");
            // Получение настроек
            $query = $this->db->placehold("
            SELECT * 
            FROM __settings
            WHERE 1
                $nameFilter
                $siteIdFilter
        ");
            $this->db->query($query);
            $settings = $this->db->results();
        }

        // Если настройка есть
        if (!empty($settings)) {
            foreach ($settings as $setting) {

                // Если значение изменилось, то обновляем и логируем
                if ($newValue !== $setting->value) {
                    $query = $this->db->placehold('UPDATE __settings SET value = ? WHERE setting_id = ?', $newValue, $setting->setting_id);
                    $this->db->query($query);

                    // Логирование
                    $query = $this->db->placehold('INSERT INTO __settings_log SET ?%', ['manager_id' => null, 'setting_id' => $setting->setting_id, 'old_value' => $setting->value, 'new_value' => $newValue]);
                    $this->db->query($query);
                }
            }
        } else {
            // Если настройки нет, то добавляем и логируем
            $query = $this->db->placehold('INSERT INTO __settings SET ?%', ['name' => $name, 'value' => $newValue, 'site_id' => $this->currentSiteId]);
            $this->db->query($query);
            $settingId = $this->db->insert_id();

            // Логирование
            $query = $this->db->placehold('INSERT INTO __settings_log SET ?%', ['manager_id' => null, 'setting_id' => $settingId, 'old_value' => null, 'new_value' => $newValue]);
            $this->db->query($query);
        }
    }
}
