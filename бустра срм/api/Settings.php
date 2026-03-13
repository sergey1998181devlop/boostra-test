<?php

/**
 * Управление настройками, хранящимися в базе данных.
 * Класс позволяет работать с глобальными настройками, а также с настройками для конкретных сайтов (лендингов).
 *
 * @copyright 	2011 Denis Pikusov, 2025 Good Martian
 * @link 		https://itm.finfort.ru
 * @author 		Denis Pikusov, Good Martian
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
 * @property array $apikeys API ключи, токены и т.д. Специфичные для сайта хранятся в apikeys[$site_id], глобальные - просто в apikeys
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
 * @property array $sms_template_phone_partner
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
 * @property string $site_warning_banner_config JSON-конфигурация баннера предупреждений
 * @property string $addresses_is_dadata
 * @property string $dbrain_auto
 * @property string $autoapprove_plus_30
 * @property string $split_test_users
 * @property string $hui
 * @property string $installment_test_users
 * @property string $enabled_5days_maratorium
 * @property string $installments_enabled
 * @property array $sbp_enabled
 * @property bool $il_enabled
 * @property string $addcard_rejected_enabled
 * @property string $cross_orders_enabled
 * @property string $pdn_sync_day
 * @property string $new_flow_enabled Включено ли новое флоу с УПРИДом
 * @property string $leadgid_scorista_enabled Включена ли таблица с настройками минимального проходного балла для лидгенов
 * @property string $check_reports_for_loans_enable Включена ли проверка актуальности ССП и КИ отчетов при выдаче займов
 * @property string $approve_amount_settings_enabled Включена ли таблица с настройками надбавок к одобренной сумме
 * @property string $auto_confirm_for_auto_approve_orders_enable Включено ли авто-подтверждение (с отправкой смс с АСП-кодом) авто-одобренных заявок
 * @property string $vk_bot_enabled Включена ли рассылка сообщений ботом в ВК
 * @property array $prolongation_visible Настройки видимости баннера пролонгации для бакетов с -5 по 0
 * @property array $prolongation_text Переопределение текста баннера пролонгации для бакетов с -5 по 0
 * @property string $min_scorista_ball_for_autoretry Минимальный балл скористы для повышения суммы одобрения
 * @property string $increased_order_amount_for_autoretry Минимальная сумма одобрения, если балл скористы по заявке превышает $min_scorista_ball_for_autoretry
 * @property string $display_policy_days - Количество дней через которое отображаются полисы
 * @property string $bonon_enabled Включена ли продажа карт отказных НК клиентов
 * @property array $bonon_sources Продаваемые в Bonon источники
 * @property string $short_flow_enabled Короткое флоу регистрации включено
 * @property string $need_notify_user_when_scorista_success Нужно ли уведомлять пользовать об одобрении скористы
 * @property string $sbp_recurrents_enabled Признак разрешения списания рекуррентных платежей через СБП
 * @property string $auto_disable_additional_services Признак автоматического отключения всех активных допов при создании тикета
 * @property boolean $registration_disabled_captcha Выключена ли капча на странице входа и регистрации нового пользователя
 * @property array $flow_after_personal_data Настройки флоу телефон после ФИО + паспорт
 * @property string $axi_spr_enabled Часть потока ориентируется только на решение Акси, скориста не делает автоотказ и не ставит суммы в заявках
 * @property array $autoconfirm_flow_utm_sources Utm метки для потока трафика НК автовыдачи
 * @property array $autoconfirm_2_flow_utm_sources Utm метки для потока трафика НК автовыдачи 2
 * @property array $autoconfirm_2_flow_cross_utm_sources Utm метки cross_order для потока трафика НК автовыдачи 2
 * @property array $autoconfirm_crm_auto_approve_utm_sources Utm метки для потока трафика для заявок utm_source=crm_auto_approve
 * @property string $old_scorista_to_1c_date Дата последних обработанных данных для cron/send_old_scorista_to_1c.php
 * @property string $notice_contact_me_enabled Включён ли нотис "Свяжитесь со мной" в ЛК?
 * @property string $notice_contact_me_enabled_for Нотис "Свяжитесь со мной" в ЛК доступен для: 1 - ПК, 2 - НК, 3 - Всех?
 * @property string $cross_orders_nk_enabled Включены ли кросс-заявки для НК
 * @property int $t_bank_button_registration Кнопка T-Bank на регистрации
 * @property $esia_button_registration Кнопка гос услуг на регистрации
 * @property boolean $faq_highlight_enabled Флаг активации подсветки раздела "Вопросы и ответы" в ЛК
 * @property int $faq_highlight_delay Задержка в минутах до подсветки раздела "Вопросы и ответы" в ЛК
 * @property int $return_threshold_days_fd Настройки дней, в течении которых клиенту виден ФД
 * @property int $return_threshold_days_zo Настройки дней, в течении которых клиенту виден ЗО
 * @property boolean $sbp_issuance_enabled Включение выплат по СБП
 * @property array $auto_step_no_need_for_underwriter Автоматическое прохождение шагов, при скористе
 * @property int $scor_approve_counter
 * @property boolean $autoconfirm_enabled
 * @property bool $hide_order_information Скрытие информации(сумма займа) в ЛК
 * @property bool $whitelist_dop Отключение всех ДОПов для пользователей из белого списка
 * @property bool $safe_flow Безопасное флоу для органики
 * @property bool $unsafe_flow Опасное флоу
 * @property int $cooling_period_hours Количество часов в статусе "Охлаждение" перед переводом заявки в статус "Выдан"
 * @property bool $fake_dops Фейковые ДОПы
 * @property array $cessions_settings
 * @property array $show_sbp_banks_for_autoapprove_orders Показывать список банков для выплаты по СБП без привязки (не только для автозаявок)
 * @property array $disable_bank_selection_utm_sources Utm метки для отключения выбора банка при привязке карты (id банка получаем при привязке карты)
 * @property array $returning_users_flow_utm_sources Utm метки для клиентов, которые вернулись после длительного отсутствия для продолжения регистрации
 * @property array $mark_418_test_leadgids Utm метки для A/B теста MARK-418
 * @property array $ticket_sound_settings Настройки звукового уведомления о новых тикетах (JSON: check_interval_sec, remind_interval_min)
 * @property array $organization_switch Настройки переключения организации в заявке
 * @property array $allow_simplified_flow НЕ ИСПОЛЬЗУЕТСЯ - Флаг необходимости запрашивать КИ внутри Акси по МКК Фрида
 * @property array $il_nk_loan_edit_amount Настройки ИЛ займа, изменении суммы при подаче НК
 * @property string $base_organization_id Базовая организация
 * @property array $usedesk_settings Настройки usedesk
 * @property array $no_need_for_underwriter_card_step_disabled Настройки пропуска шага карты от скористы
 * @property array $cross_organization_id Id первой кросс-организации для выдачи
 * @property array $cross_organization_id2 Id второй кросс-организации для выдачи
 * @property string $disable_pdn_check Временно отключить проверку ПДН > 80 на проверку NBKI score
 * @property boolean $disable_loan_issuance Отключение выдачи займов (технические работы/нет средств на балансе)
 * @property boolean $cross2_enabled Включено ли создание второго кросс-ордера (для тестов, в будущем можно удалить настройку)
 * @property array $partner_api_repeat_client_utm_sources UTM метки для пк PING3
 */
class Settings extends Simpla
{
    private const CACHE_KEY_GLOBAL = 'GLOBAL';

	private ?string $currentSiteId = null;
    private bool $includeGlobalSettings = true;

    private array $activeSettings = [];
    private array $cachedSettings = [];

	public function __construct()
	{
		parent::__construct();

        // Заставляет загрузить все настройки из БД в кеш
        $this->clearSettingsCache();
	}

    /**
     * Загрузка всех настроек из БД в кеш для последующего чтения
     */
    private function loadAllSettings(): void
    {
        // Очищаем на случай, если кэш был не пустым
        $this->cachedSettings = [];

        $this->db->query('SELECT `name`, `value`, `site_id` FROM __settings') ?? [];
        foreach($this->db->results() as $setting) {
            $siteId = $setting->site_id ?? self::CACHE_KEY_GLOBAL;
            $unserialized = @unserialize($setting->value);
            $this->cachedSettings[$siteId][$setting->name] = ($unserialized !== false || $setting->value === 'b:0;')
                ? $unserialized
                : $setting->value;
        }
    }

    /**
     * Установка настроек для текущего сайта из загруженного ранее кэша
     * @return void
     */
    private function setUpSiteSettings(): void
    {
        if (
            $this->includeGlobalSettings
            && $this->currentSiteId !== null
            && isset($this->cachedSettings[self::CACHE_KEY_GLOBAL])
        ) {
            $this->activeSettings = $this->cachedSettings[self::CACHE_KEY_GLOBAL];
        }
        else {
            $this->activeSettings = [];
        }

        $this->activeSettings = array_merge(
            $this->activeSettings,
            $this->currentSiteId === null
                ? ($this->cachedSettings[self::CACHE_KEY_GLOBAL] ?? [])
                : ($this->cachedSettings[$this->currentSiteId] ?? [])
        );
    }

    /**
     * Дополнительная обработка настроек после их установки
     * @return void
     */
    private function processSettingsAfterSetUp(): void
    {
        $this->activeSettings['theme'] = 'manager';

        if ($this->currentSiteId !== null) {
            $this->activeSettings['apikeys'] = $this->includeGlobalSettings
                ? $this->cachedSettings[self::CACHE_KEY_GLOBAL]['apikeys']
                : [];

            $this->activeSettings['apikeys'][$this->currentSiteId] = $this->cachedSettings[$this->currentSiteId]['apikeys'] ?? [];
        }
    }

    /**
     * Сохраняет запись в лог изменений настроек.
     *
     * @param int $settingId Id изменённой настройки
     * @param mixed $oldValue Старое значение настройки (или null)
     * @param mixed $newValue Новое значение настройки
     */
    private function saveLogEntry(int $settingId, $oldValue, $newValue): void
    {
        $this->db->query('INSERT INTO __settings_log SET ?%', [
            'manager_id' => $this->getManagerId(),
            'setting_id' => $settingId,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ]);
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

    /**
     * Возвращает флаг использования глобальных настроек при работе с настройками сайта.
     *
     * @return bool true, если site_id = null или глобальные настройки подтягиваются вместе с site-specific
     */
    public function isGlobalSettingsIncluded(): bool
    {
        return $this->includeGlobalSettings || $this->currentSiteId === null;
    }

	/**
	 * Устанавливает настройки для выбранного сайта.
	 *
	 * @param string|null $siteId Идентификатор сайта (site_id) (NULL для работы с глобальными настройками)
     * @param bool $includeGlobalSettings Использовать ли в том числе глобальные настройки, если site_id не NULL.
     * По-умолчанию глобальные настройки подтягиваются (true)
	 */
	public function setSiteId(?string $siteId, bool $includeGlobalSettings = true): void
    {
		$this->currentSiteId = $siteId;
        $this->includeGlobalSettings = $includeGlobalSettings;

		$this->setUpSiteSettings();
        $this->processSettingsAfterSetUp();
	}

    /**
     * Очищает кэш настроек и перезагружает их из базы данных.
     *
     * Обычно **не нужно вызывать это вручную**, только если надо подтянуть настройки которые поменялись "со стороны" - прямым
     * изменением в БД или другим PHP процессом.
     * @return void
     */
    public function clearSettingsCache(): void
    {
        $this->loadAllSettings();
        $this->setUpSiteSettings();
        $this->processSettingsAfterSetUp();
    }

    /**
     * Возвращает ключи API для текущего сайта или глобальные, если для сайта нет своих.
     *
     * ```
     * // Пример:
     * $this->token = $this->settings->getApiKeys('dadata')['api_key'];
     * ```
     *
     * @param string $apiName Имя API (например 'smsc', 'mango', 'dadata' и т.д.)
     * @return array|null Массив ключей API или null, если ключи не найдены
     */
    public function getApiKeys(string $apiName): ?array
    {
        $siteSpecificKeys = $this->activeSettings['apikeys'][$this->currentSiteId][$apiName] ?? [];
        if ($this->currentSiteId === null || empty($siteSpecificKeys)) {
            // Глобальные настройки, если нет ключей под сайт
            return $this->activeSettings['apikeys'][$apiName] ?? null;
        }

        // Дополнительная проверка - в словаре не должны быть только пустые ключи
        if (is_array($siteSpecificKeys)) {
            $hasAnyValues = false;
            foreach ($siteSpecificKeys as $value) {
                if (!empty($value)) {
                    $hasAnyValues = true;
                    break;
                }
            }

            if (!$hasAnyValues) {
                // Глобальные настройки, если в настройках сайта просто пустой словарь
                return $this->activeSettings['apikeys'][$apiName] ?? null;
            }
        }

        return $siteSpecificKeys;
    }

	/**
	 * Получить список имен настроек, видимых в текущем контексте (глобальном или сайта).
	 *
	 * @return array Список имен настроек
	 */
	public function getVisibleSettingNames(): array
    {
		return array_keys($this->activeSettings);
	}

	public function __get($name)
	{
		if($res = parent::__get($name)) {
            return $res;
        }

        return $this->activeSettings[$name] ?? null;
	}
	
	public function __set($name, $value)
	{
        $siteKey = $this->currentSiteId ?? self::CACHE_KEY_GLOBAL;
        if ($this->cachedSettings[$siteKey][$name] === $value) {
            // Значение не изменилось
            return;
        }

        $oldValue = $this->cachedSettings[$siteKey][$name] ?? null;
        $newValue = is_array($value) ? serialize($value) : (string)$value;

        if ($oldValue !== null) {
            // Настройка уже есть, находим её id
            $nameFilter = $this->db->placehold("AND name = ?", $name);
            if ($this->currentSiteId !== null) {
                $siteIdFilter = $this->db->placehold("AND site_id = ?", $this->currentSiteId);
            } else {
                $siteIdFilter = $this->db->placehold("AND site_id IS NULL");
            }

            $this->db->query("
                SELECT setting_id FROM __settings
                WHERE 1
                    $nameFilter
                    $siteIdFilter
                LIMIT 1
            ");
            $settingId = $this->db->result('setting_id');

            // Обновляем в БД
            $this->db->query('UPDATE __settings SET value = ? WHERE setting_id = ?', $newValue, $settingId);
            // И в кэше
            $this->cachedSettings[$siteKey][$name] = $value;
            $this->activeSettings[$name] = $value;
            $this->processSettingsAfterSetUp();
            // Логируем изменение
            $this->saveLogEntry($settingId, $oldValue, $newValue);

            return;
        }

        // Настройки ещё нет, добавляем новую
        $this->db->query('INSERT INTO __settings SET ?%', [
            'name' => $name,
            'value' => $newValue,
            'site_id' => $this->currentSiteId
        ]);
        $settingId = $this->db->insert_id();
        // Пишем в кэш
        $this->cachedSettings[$siteKey][$name] = $value;
        $this->activeSettings[$name] = $value;
        $this->processSettingsAfterSetUp();
        // Логируем изменение
        $this->saveLogEntry($settingId, null, $newValue);
	}
}