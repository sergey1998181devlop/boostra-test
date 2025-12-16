<?php

//ini_set("soap.wsdl_cache_enabled", 0);

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

use api\helpers\UserHelper;
use boostra\services\UsersAddressService;

require_once('Simpla.php');

class Users extends Simpla {

    /**
     * Статус отклоненных фото у пользователя
     */
    public const PHOTO_STATUS_REJECT = 3;

    /**
     * Статус заблокированного пользователя в 1С
     */
    public const BLOCKED_USER_1C = 'Delete';

    /**
     * Флаги авторизации через кнопки сервисов ТИд, ГосУслуги, через 1С
     */
    public const AUTH_FROM_1C_TYPES = [
        TBankId::T_ID_AUTH_OLD_USER_TYPE => TBankId::T_ID_AUTH_FROM_1C,
        EsiaService::ESIA_AUTH_OLD_USER_TYPE => EsiaService::ESIA_AUTH_FROM_1C,
    ];

    public const USER_SKIP_CR = [
        'ids' => [6024, 318791],
        'phones_mobile' => [
            '79103972615',
            '79172382515',
            '79870495161',
            '79870495789',
            '79870495815',
            '79870495074',
            '79870498331',
            '79825066917',
            '79966309602',
            '79964167914',
            '79258277373',
            '79991585417',
            '79266533232',
            '79360034747',
        ]
    ];

    /**
     * Кол-во попыток для неверного ввода пароля
     */
    const MAX_INCORRECT_PASSWORD = 6;

    /**
     * Среднее образование
     */
    const EDUCATION_SECONDARY = 1;

    /**
     * Среднее специальное
     */
    const EDUCATION_SECONDARY_SPECIAL = 2;

    /**
     * Незаконченное высшее
     */
    const EDUCATION_INCOMPLETE_HIGHER = 3;

    /**
     * Высшее профессиональное
     */
    const EDUCATION_HIGHER = 4;

    /**
     * Другое
     */
    const EDUCATION_OTHER = 5;

    /**
     * Семейное положение - женат/замужем
    */
    const MARITAL_STATUS_MARRIED = 'married';

    /**
     * Семейное положение - разведен/разведена
     */
    const MARITAL_STATUS_DIVORCED = 'divorced';

    /**
     * Семейное положение - гражданский брак
     */
    const MARITAL_STATUS_CIVIL_MARRIAGE = 'civil_marriage';

    /**
     * Семейное положение - вдовец/вдова
     */
    const MARITAL_STATUS_WIDOWER = 'widower';

    /**
     * Семейное положение - не замужем/холост
     */
    const MARITAL_STATUS_SINGLE = 'single';

    /**
     * Users::isSafetyFlow()
     * Возвращает:
     * 1 - безопасное флоу
     * 0 - стандартное флоу
     *
     * @param mixed $user
     * @return int
     */
    public function isSafetyFlow($user): int
    {
        if (!$user) {
            return 0;
        }

        if ($this->settings->unsafe_flow == 1) {
            return 0;
        }

        $is_organic = $this->is_organic($user->utm_source);

        if (!$is_organic) {
            return 0;
        }

        $currentHour = (int)date('H');
        $dayOfWeek = (int)date('N');

        $isBusinessHours = ($currentHour >= 10 && $currentHour <= 16);
        $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5);

        if ($this->settings->safe_flow == 1) {
            return 1;
        }

        return ($isBusinessHours && $isWeekday) ? 1 : 0;
    }

    /**
     * Является ли трафик органикой
     *
     * @param string $utm_source
     * @return bool
     */
    public function is_organic(string $utm_source): bool
    {
        $non_organic_sources = $this->settings->non_organic_utm_sources ?? [];
        return empty($utm_source) || !in_array(strtolower($utm_source), array_map('strtolower', $non_organic_sources));
    }

    /**
     * Проверка пользователей на демо страницы покупки Кредитного рейтинга и установки стоимости покупки в 1 руб.
     * @param int $user_id
     * @param string $user_phone_mobile
     * @return bool
     */
    public static function validateNoSkipUser(int $user_id, string $user_phone_mobile): bool
    {
        return in_array($user_id, self::USER_SKIP_CR['ids']) || in_array(
                $user_phone_mobile,
                self::USER_SKIP_CR['phones_mobile']
            );
    }

    const USER_STATUS_CANCEL_ORDER = 3;

    // осторожно, при изменении соли испортятся текущие пароли пользователей
    private $salt = "8e86a279d6e182b3c811c559e6b15484";

    public function get_recomendation_amount($user) {
        $amounts = array(
            'male' => array(
                0 => array(3000, 3000, 3000, 3000),
                21 => array(4000, 4000, 3000, 4000),
                25 => array(6000, 6000, 5000, 6000),
                30 => array(9000, 8000, 6000, 8000),
                35 => array(8000, 7000, 6000, 7000),
                40 => array(11000, 10000, 8000, 9000),
                45 => array(10000, 9000, 7000, 8000),
                50 => array(4000, 4000, 3000, 4000),
                55 => array(6000, 6000, 5000, 6000),
                60 => array(2000, 2000, 2000, 2000),
            ),
            'female' => array(
                0 => array(3000, 3000, 3000, 3000),
                21 => array(6000, 6000, 5000, 6000),
                25 => array(9000, 8000, 6000, 8000),
                30 => array(11000, 10000, 8000, 9000),
                35 => array(10000, 9000, 7000, 8000),
                40 => array(12000, 12000, 10000, 11000),
                45 => array(12000, 12000, 10000, 11000),
                50 => array(6000, 6000, 5000, 6000),
                55 => array(9000, 8000, 6000, 8000),
                60 => array(4000, 4000, 3000, 4000),
            ),
        );

        $user_age = date_diff(date_create(date('Y-m-d', strtotime($user->birth))), date_create(date('Y-m-d')));
        $user_age_year = $user_age->y;

        $recommendation_amount = 0;
        if (empty($user->gender))
            $user->gender = 'male';

        if (!empty($user->gender) && isset($amounts[$user->gender])) {
            $current = NULL;
            foreach ($amounts[$user->gender] as $age => $values) {
                if ($user_age_year >= $age)
                    $current = $values;
            }
        }
        if ($this->is_developer) {
//    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($current);echo '</pre><hr />';
        }

        switch ($user->marital_status):

            case 'женат/замужем':
            case 'разведен/разведена':
                $recommendation_amount = $current[0];
                break;

            case 'не замужем/холост':
            case 'вдовец/вдова':
                $recommendation_amount = $current[1];
                break;

            case 'гражданский брак':
                $recommendation_amount = $current[2];
                break;

            default:

                $recommendation_amount = $current[3];

        endswitch;

        if (!empty($user->first_loan_amount))
            return min($user->first_loan_amount, $recommendation_amount);
        else
            return $recommendation_amount;
    }

    // автоповтор
    /*
      1. Не менее 2х закрытых займов (3й уже может быть выдан автоматически)
      2. Скориста одобрила заявку (не автовыдачу, а просто одобрение)
      3. В анкете нет изменений с момента последнего займа, карта та же
      4. Сумма выдачи берется из 1с
      5. Три крайних займа (включая тот, на который подается заявка) не находятся в интервале 30 дней с момента выдачи.
     */
    public function check_autoretry($user_id, $order_id) {
        
        // по источнику sms_23 необходимо сделать так, чтобы они падали ТОЛЬКО на ручную верификацию 
        if ($current_order = $this->orders->get_order((int)$order_id)) {
            if ($current_order->utm_source == 'sms_23') {
                return 0;
            }
        }
        
        if ($user = $this->get_user((int) $user_id)) {
            if (!empty($user->loan_history)) {
                $loan_history = $user->loan_history;
                if (count($loan_history) > 0) { // Не менее 1 закрытого займа, 2й выдаёт автоматически
                    // ОТКЛЮЧЕНО https://tracker.yandex.ru/BOOSTRARU-2635
                    // Три крайних займа (включая тот, на который подается заявка)
                    // не находятся в интервале 30 дней с момента выдачи.
                    /*
                    $minus30_date = date('Y-m-d 00:00:00', time() - 86400 * 30);
                    $minus30_loans = array();
                    foreach ($loan_history as $item) {
                        if (strtotime($item->date) > strtotime($minus30_date))
                            $minus30_loans[] = $item;
                    }

                    if (count($minus30_loans) < 2) {
                    */
                    if (true) {
                        $current_order = NULL;
                        $last_order = NULL;
                        $orders = $this->orders->get_orders(array('user_id' => $user_id));
                        foreach ($orders as $order) {
                            if ($order->id == $order_id) {
                                $current_order = $order;
                            } elseif (($order->status == 2 || $order->status == 10) && (empty($last_order) || (strtotime($last_order->date) < strtotime($order->date)))) {
                                $last_order = $order;
                            }
                        }
                        if (!empty($current_order) && !empty($last_order)) {
                            // карта та же
                            if ($current_order->card_id == $last_order->card_id) {
                                return 1;
                            } else {
                                if ($user_cards = $this->best2pay->get_cards(['user_id' => $current_order->user_id])) {
                                    foreach ($user_cards as $user_card) {
                                        if ($user_card->id == $current_order->card_id) {
                                            $current_order_card = $user_card;
                                        }
                                        if ($user_card->id == $last_order->card_id) {
                                            $last_order_card = $user_card;
                                        }
                                    }
                                    if ($current_order_card->pan == $last_order_card->pan) {
                                        return 1;
                                    }
                                }
                                
                                
                            }
                        }
                    }
                }
            } else {
                if ($this->settings->dbrain_auto == 0) {
                    $this->settings->dbrain_auto = $order_id;
                    return 2;
                }
            }
        }

        return 0;
    }

    /**
     * Users::save_loan_history()
     * Сохраняем кредитную историю полученную из 1с
     *
     * @param integer $user_id
     * @param array $credits_history
     * @return void
     */
    public function save_loan_history($user_id, $credits_history) {
        $loan_history = array();
        if (!empty($credits_history)) {
            foreach ($credits_history as $credits_history_item) {
                $loan_history_item = new StdClass();

                $loan_history_item->date = $credits_history_item->ДатаЗайма;
                $loan_history_item->number = $credits_history_item->НомерЗайма;
                $loan_history_item->amount = $credits_history_item->СуммаЗайма;
                $loan_history_item->loan_body_summ = $credits_history_item->ОстатокОД;
                $loan_history_item->loan_percents_summ = $credits_history_item->ОстатокПроцентов;
                $loan_history_item->close_date = $credits_history_item->ДатаЗакрытия;
                $loan_history_item->paid_percents = $credits_history_item->ОплатаПроцентов;
                $loan_history_item->prolongation_count = isset($credits_history_item->КоличествоПролонгаций) ? $credits_history_item->КоличествоПролонгаций : 0;
                $loan_history_item->plan_close_date = !empty($credits_history_item->ПланДатаВозврата) ? date('Y-m-d H:i:s', strtotime($credits_history_item->ПланДатаВозврата)) : NULL;

                $loan_history[] = $loan_history_item;
            }
        }
        $this->users->update_user($user_id, array('loan_history' => json_encode($loan_history, JSON_UNESCAPED_UNICODE)));

        return $loan_history;
    }

    /**
     * @param string $passport
     * @param int $userId
     * @return false|int
     */
    public function get_passport_user(string $passport, int $userId = 0)
    {
        $checkExists = $userId ? $this->db->placehold( 'AND id <> ?', $userId ) : '';
        $passport_clear = $this->getClearPassport($passport);

        $this->db->query(
            $this->db->placehold(
                "SELECT id
                    FROM __users
                    WHERE passport_serial = ? {$checkExists}",
                $passport_clear
            )
        );

        return $this->db->result('id');
    }

    /**
     * Get user by passport & birth date
     * @param array $data
     * @return object|false
     */
    public function getUserByContract(array $data = [])
    {
        if (!($data && !empty($data['DR']) && !empty($data['Passport']))) {
            return false;
        }

        $passport_clear = $this->getClearPassport($data['Passport']);

        $query = $this->db->placehold("SELECT id, uid FROM __users  WHERE passport_serial = ? AND birth = ? LIMIT 1", $passport_clear, $data['DR']);
        $this->db->query($query);
        return $this->db->result();
    }

    public function getUserIdByEmail($email) {
        $query = $this->db->placehold("
            SELECT id FROM __users WHERE email = '?'
        ", (string) $email);
        $this->db->query($query);
        return $this->db->result('id');
    }

    public function getUserInfoById($id) {
        $query = $this->db->placehold("
            SELECT * FROM __users WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function get_phone_user($phone) {
        $query = $this->db->placehold("
            SELECT id FROM __users WHERE phone_mobile = ?
        ", (string) $this->clear_phone($phone));
        $this->db->query($query);

        return $this->db->result('id');
    }

    function get_user_uid($id) {
        $query = $query = $this->db->placehold("
            SELECT
    			u.id,
    			u.uid,
    			u.uid_status
            FROM __users AS u
            WHERE u.id = ?
        ", (int) $id);

        $this->db->query($query);
        return $this->db->result();
    }

    function get_user_by_uid($uid) {
        $query = $this->db->placehold("
            SELECT * FROM __users WHERE uid = ?
            ORDER BY blocked ASC, id DESC
        ", (string) $uid);
        $this->db->query($query);

        $result = $this->db->result();

        return $result;
    }

    function get_users($filter = array()) {
        $limit = 1000;
        $page = 1;
        $group_id_filter = '';
        $keyword_filter = '';
        $no_uid_filter = '';
        $bad_uid_filter = '';
        $error_uid_filter = '';
        $ok_uid = '';

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        if (isset($filter['group_id']))
            $group_id_filter = $this->db->placehold('AND u.group_id in(?@)', (array) $filter['group_id']);

        // Новый фильтр для отбора по пустому UID
        // Позволяет проверить есть ли этот человек с этого сайта в 1с

        if (isset($filter['no_uid']))
            $no_uid_filter = $this->db->placehold('AND u.UID = ""');

        if (isset($filter['bad_uid']))
            $bad_uid_filter = $this->db->placehold('AND u.UID_status <> "ok"');

        if (isset($filter['error_uid']))
            $error_uid_filter = $this->db->placehold('AND u.UID LIKE "error"');

        if (isset($filter['ok_uid']))
            $ok_uid = $this->db->placehold('AND u.UID_status = "ok" AND u.UID NOT LIKE "error"');

        // Новый поиск
        // По телефону
        if (isset($filter['keyword_phone'])) {

            $keyword_filter .= $this->db->placehold('AND (replace(replace(replace(replace(replace(u.phone_mobile," ",""),"-",""),"(",""),")",""),"+","") LIKE "%' . (trim($filter['keyword_phone'])) . '%")');
        }
        if (isset($filter['keyword_surname'])) {

            $keyword_filter .= $this->db->placehold('AND (u.lastname LIKE "%' . (trim($filter['keyword_surname'])) . '%")');
        }
        if (isset($filter['keyword_name'])) {

            $keyword_filter .= $this->db->placehold('AND (u.firstname LIKE "%' . (trim($filter['keyword_name'])) . '%")');
        }
        if (isset($filter['keyword_patronimic'])) {

            $keyword_filter .= $this->db->placehold('AND (u.patronymic LIKE "%' . (trim($filter['keyword_patronimic'])) . '%")');
        }

        $order = 'u.name';
        if (!empty($filter['sort']))
            switch ($filter['sort']) {
                case 'date':
                    $order = 'u.created DESC';
                    break;
                case 'name':
                    $order = 'u.lastname';
                    break;
            }


        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        // Выбираем пользователей
        $query = $this->db->placehold("
            SELECT
                u.id,

                u.maratorium_id,
                u.maratorium_date,

                u.first_loan,
                u.first_loan_amount,
                u.first_loan_period,

    			u.service_sms,
    			u.service_insurance,
    			u.service_reason,
    			u.service_recurent,

                u.email,
				u.password,
				u.name,
				u.group_id,
				u.enabled,
				u.last_ip,
				u.reg_ip,
				u.created,

                u.personal_data_added,
                u.additional_data_added,
                u.files_added,
                u.card_added,
                u.personal_data_added_date,
                u.additional_data_added_date,
                u.files_added_date,
                u.card_added_date,
                u.address_data_added,
                u.address_data_added_date,
                u.accept_data_added,
                u.accept_data_added_date,

				u.lastname,
				u.firstname,
				u.patronymic,
                u.gender,
				u.birth,
				u.birth_place,
				u.phone_mobile,
                u.landline_phone,
				u.passport_serial,
				u.subdivision_code,
				u.passport_date,
				u.passport_issued,
				u.Regindex,
				u.Regregion,
				u.Regdistrict,
				u.Regcity,
				u.Reglocality,
				u.Regstreet,
				u.Regbuilding,
				u.Reghousing,
				u.Regroom,
				u.Regregion_shorttype,
				u.Regcity_shorttype,
				u.Regstreet_shorttype,
				u.Faktindex,
				u.Faktregion,
				u.Faktdistrict,
				u.Faktcity,
				u.Faktlocality,
				u.Faktstreet,
				u.Faktbuilding,
				u.Fakthousing,
				u.Faktroom,
				u.Faktregion_shorttype,
				u.Faktcity_shorttype,
				u.Faktstreet_shorttype,
                u.contact_person_name,
                u.contact_person_phone,
                u.contact_person_relation,
                u.contact_person2_name,
                u.contact_person2_phone,
                u.contact_person2_relation,
                u.contact_person3_name,
                u.contact_person3_phone,
                u.contact_person3_relation,
                u.employment,
                u.profession,
                u.workplace,
                u.experience,

                u.work_address,
                u.work_scope,
                u.work_staff,
                u.work_phone,
                u.workdirector_name,
				u.Workindex,
				u.Workregion,
				u.Workcity,
				u.Workstreet,
				u.Workbuilding,
				u.Workhousing,
				u.Workroom,
				u.Workregion_shorttype,
				u.Workcity_shorttype,
				u.Workstreet_shorttype,
                u.income_base,
                u.income_additional,
                u.income_family,

                u.obligation,
                u.other_loan_month,
                u.other_loan_count,
                u.credit_history,
                u.other_max_amount,
                u.other_last_amount,
                u.bankrupt,

                u.education,
                u.marital_status,
                u.childs_count,
                u.have_car,
                u.has_estate,
                u.social_inst,
                u.social_fb,
                u.social_vk,
                u.social_ok,

				u.site_id,
				u.partner_id,
				u.partner_name,
				u.utm_source,
				u.utm_medium,
				u.utm_campaign,
				u.utm_content,
				u.utm_term,
				u.webmaster_id,
				u.click_hash,
				u.sms,

				u.uid,
				u.uid_status,
				u.rebillId,
                u.file_uploaded,

                u.fake_order_error,
                u.choose_insure,
                
                u.cdoctor_pdf,
                u.cdoctor_level,
                u.use_b2p,
                u.quantity_loans

                FROM __users u
				WHERE 1
                    $keyword_filter
                    $no_uid_filter
                    $bad_uid_filter
                    $error_uid_filter
                    $ok_uid
            ORDER BY $order
            $sql_limit
        ");

        //print_r('<br/><br/>');
        //print_r($query);

        $this->db->query($query);
        return $this->db->results();
    }

    function count_users($filter = array()) {
        $group_id_filter = '';
        $keyword_filter = '';

        if (isset($filter['group_id']))
            $group_id_filter = $this->db->placehold('AND u.group_id in(?@)', (array) $filter['group_id']);

        /* Старый поиск
          if(isset($filter['keyword']))
          {
          $keywords = explode(' ', $filter['keyword']);
          foreach($keywords as $keyword)
          $keyword_filter .= $this->db->placehold('AND u.name LIKE "%'.$this->db->escape(trim($keyword)).'%" OR u.email LIKE "%'.$this->db->escape(trim($keyword)).'%"');
          }
         */
        // Новый поиск
        // По телефону
        if (isset($filter['keyword_phone'])) {

            $keyword_filter .= $this->db->placehold('AND (replace(replace(replace(replace(replace(u.phone_mobile," ",""),"-",""),"(",""),")",""),"+","") LIKE "%' . (trim($filter['keyword_phone'])) . '%")');
            //print_r($keyword_filter);
        }
        if (isset($filter['keyword_surname'])) {

            $keyword_filter .= $this->db->placehold('AND (u.lastname LIKE "%' . (trim($filter['keyword_surname'])) . '%")');
            //print_r($keyword_filter);
        }
        if (isset($filter['keyword_name'])) {

            $keyword_filter .= $this->db->placehold('AND (u.firstname LIKE "%' . (trim($filter['keyword_name'])) . '%")');
            //print_r($keyword_filter);
        }
        if (isset($filter['keyword_patronimic'])) {

            $keyword_filter .= $this->db->placehold('AND (u.patronymic LIKE "%' . (trim($filter['keyword_patronimic'])) . '%")');
            //print_r($keyword_filter);
        }


        // Выбираем пользователей
        $query = $this->db->placehold("SELECT count(*) as count FROM __users u
		                                LEFT JOIN __groups g ON u.group_id=g.id
										WHERE 1 $group_id_filter $keyword_filter");
        $this->db->query($query);
        return $this->db->result('count');
    }

    function get_user_by_id($id) {
        $query = $this->db->placehold("
            SELECT * FROM __users u WHERE u.id = ? LIMIT 1
        ", $id);

        $this->db->query($query);
        return $this->db->result();
    }

    function get_user($id, $for_zayavka = false) {
        if (gettype($id) == 'string')
            $where = $this->db->placehold(' WHERE u.phone_mobile=? ', $this->clear_phone($id));
        else
            $where = $this->db->placehold(' WHERE u.id=? ', intval($id));

        // Выбираем пользователя
        $query = $this->db->placehold("
            SELECT
    			u.id,

                u.maratorium_id,
                u.maratorium_date,

                u.first_loan,
                u.first_loan_amount,
                u.first_loan_period,

    			u.service_sms,
    			u.service_insurance,
    			u.service_reason,
    			u.service_recurent,

                u.email,
    			u.password,
    			u.name,
    			u.group_id,
    			u.enabled,
                u.blocked,
    			u.last_ip,
    			u.reg_ip,
    			u.created,
                u.inn,
                u.personal_data_added,
                u.additional_data_added,
                u.files_added,
                u.card_added,
                u.personal_data_added_date,
                u.card_req_data_added,
                u.card_req_data_added_date,
                u.additional_data_added_date,
                u.files_added_date,
                u.card_added_date,
                u.address_data_added,
                u.address_data_added_date,
                u.accept_data_added,
                u.accept_data_added_date,

    			u.lastname,
    			u.firstname,
    			u.patronymic,
                u.gender,
    			u.birth,
    			u.birth_place,
    			u.phone_mobile,
                u.landline_phone,
    			u.passport_serial,
    			u.subdivision_code,
    			u.passport_date,
    			u.passport_issued,
    			u.Regindex,
    			u.Regregion,
    			u.Regdistrict,
    			u.Regcity,
    			u.Reglocality,
    			u.Regstreet,
    			u.Regbuilding,
    			u.Reghousing,
    			u.Regroom,
				u.Regregion_shorttype,
				u.Regcity_shorttype,
				u.Regstreet_shorttype,
    			u.Faktindex,
    			u.Faktregion,
    			u.Faktdistrict,
    			u.Faktcity,
    			u.Faktlocality,
    			u.Faktstreet,
    			u.Faktbuilding,
    			u.Fakthousing,
    			u.Faktroom,
				u.Faktregion_shorttype,
				u.Faktcity_shorttype,
				u.Faktstreet_shorttype,
                u.contact_person_name,
                u.contact_person_phone,
                u.contact_person_relation,
                u.contact_person2_name,
                u.contact_person2_phone,
                u.contact_person2_relation,
                u.contact_person3_name,
                u.contact_person3_phone,
                u.contact_person3_relation,
                u.employment,
                u.profession,
                u.workplace,
                u.experience,

                u.work_address,
                u.work_scope,
                u.work_staff,
                u.work_phone,
                u.workdirector_name,
				u.Workindex,
				u.Workregion,
				u.Workcity,
				u.Workstreet,
				u.Workbuilding,
				u.Workhousing,
				u.Workroom,
				u.Workregion_shorttype,
				u.Workcity_shorttype,
				u.Workstreet_shorttype,
                u.income_base,
                u.income_additional,
                u.income_family,

                u.obligation,
                u.other_loan_month,
                u.other_loan_count,
                u.credit_history,
                u.other_max_amount,
                u.other_last_amount,
                u.bankrupt,

                u.education,
                u.marital_status,
                u.childs_count,
                u.have_car,
                u.has_estate,
                u.social_inst,
                u.social_fb,
                u.social_vk,
                u.social_ok,


    			u.site_id,
    			u.partner_id,
    			u.partner_name,
    			u.utm_source,
    			u.utm_medium,
    			u.utm_campaign,
    			u.utm_content,
    			u.utm_term,
    			u.webmaster_id,
    			u.click_hash,
    			u.sms,

    			u.uid,
    			u.uid_status,
    			u.rebillId,
                u.file_uploaded,

                u.fake_order_error,
                u.choose_insure,

                u.cdoctor_pdf,
                u.cdoctor_level,
                u.loan_history,
                u.use_b2p,
                u.skip_credit_rating,
                u.date_skip_cr_visit,
                u.quantity_loans,
                u.cdoctor_last_graph_update_date,
                u.registration_address_id,
                u.factual_address_id,
                u.Snils,
                u.missing_real_date
                
            FROM __users u
            $where
            LIMIT 1
        ", $id);
        $this->db->query($query);
        $user = $this->db->result();

        if (empty($user)) {
            return false;
        }

        if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
            require_once __DIR__ . '/../lib/autoloader.php';
            (new UsersAddressService())->addUserAddressesToUser($user);
        }

        $user->registration_address = $this->get_registration_address($user);

        $user->loan_history = empty($user->loan_history) ? [] : json_decode($user->loan_history);

        return $user;
    }

    public function user_uses_sbp_tbank(int $userId): bool
    {
        $this->db->query("SELECT `value` from `s_user_data` where `user_id` = ? AND `key`='uses_t_sbp'", $userId);
        return (bool) $this->db->result('value');
    }

    /**
     * Поиск клиента по IP-адресу.
     *
     * @param string $userIp
     * @return object|null
     */
    function get_user_by_ip(string $userIp): ?object
    {
        $this->db->query("
            SELECT
                u.id,
                u.lastname,
    			u.firstname,
    			u.patronymic,
                u.phone_mobile,
                u.birth,
                u.last_ip,
                u.reg_ip
            FROM __users u
            WHERE u.reg_ip = ? OR u.last_ip = ?
            LIMIT 1;
        ", $userIp, $userIp);

        return $this->db->result();
    }

    /**
     * @param $userId
     * @return bool
     */
    public function checkUtmSource($userId): bool
    {
        $query = $this->db->placehold('SELECT utm_source FROM s_users WHERE id = ?', $userId);
        $this->db->query($query);

        $result = $this->db->result('utm_source');

        return $result === '' || $result === 'Boostra';
    }


    public function add_user($user) {

        $user = (array) $user;

        if (isset($user['password']))
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));

        if (isset($user['phone_mobile']))
            $user['phone_mobile'] = $this->clear_phone($user['phone_mobile']);

        if (isset($user['passport_serial']))
            $user['passport_serial'] = $this->tryFormatPassportSerial($user['passport_serial']);

        if (isset($user['passport_date']))
            $user['passport_date'] = $this->tryFormatDate($user['passport_date']);

        //$query = $this->db->placehold("SELECT count(*) as count FROM __users WHERE email=?", $user['email']);
        $query = $this->db->placehold("SELECT count(*) as count FROM __users WHERE phone_mobile=?", $user['phone_mobile']);
        $this->db->query($query);

        if ($this->db->result('count') > 0)
            return false;

        $query = $this->db->placehold("INSERT INTO __users SET ?%", $user);

        $this->db->query($query);
        return $this->db->insert_id();
    }

    public function update_user($id, $user) {

        $user = (array) $user;

        if (isset($user['password'])){
            $user['password'] = md5( $this->salt . $user['password'] . md5( $user['password'] ) );
        }
        if (!empty($user['Faktregion'])){

            $this->db->query(
                $this->db->placehold(
                    "SELECT time_zone_id
                        FROM s_time_zones
                        WHERE REPLACE(LOWER('" . $user['Faktregion'] . "'), ' ', '') LIKE CONCAT('%', REPLACE(LOWER(name_zone), ' ', ''), '%')"
                )
            );
            $user['timezone_id'] =  $this->db->results()[0]->time_zone_id;
        }

        if (isset($user['passport_serial']))
            $user['passport_serial'] = $this->tryFormatPassportSerial($user['passport_serial']);

        if (isset($user['passport_date']))
            $user['passport_date'] = $this->tryFormatDate($user['passport_date']);

        $this->db->query(
            $this->db->placehold(
                "UPDATE __users SET ?% WHERE id=? LIMIT 1",
                $user,
                (int) $id
            )
        );

        return $id;
    }

    /*
     *
     * Удалить пользователя
     * @param $post
     *
     */

    public function delete_user($id) {
        if (!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __users WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query))
                return true;
        }
        return false;
    }

    /* банковские карты пользователя, получить, удалить, прочее */

    function get_user_cards($user_id) {

        require_once('/home/p/pravza/simpla/public_html/api/addons/TinkoffMerchantAPI.php');

        $api = new TinkoffMerchantAPI(
                '1556097708543AFT',
                'a56zc57338umq6f1',
                'https://securepay.tinkoff.ru/v2'
        );

        $params = array(
            'CustomerKey' => $user_id,
        );

        $api->getCardList($params);

        return json_decode(htmlspecialchars_decode($api->response), true);
    }

    function set_payment_url($order_id, $amount, $customer) {
        require_once('/home/p/pravza/simpla/public_html/api/addons/TinkoffMerchantAPI.php');

        $api = new TinkoffMerchantAPI(
                '1556097708543AFT',
                'a56zc57338umq6f1',
                'https://securepay.tinkoff.ru/v2'
        );

        $params = array(
            'OrderId' => $order_id . rand(0, 53353232212275),
            'Amount' => $amount * 100,
            'CustomerKey' => $customer
        );

        $api->init($params);
        $response = json_decode(htmlspecialchars_decode($api->response), true);

        return $response;
    }

    function set_charge($PaymentId, $RebillId) {
        require_once('/home/p/pravza/simpla/public_html/api/addons/TinkoffMerchantAPI.php');

        $api = new TinkoffMerchantAPI(
                '1556097708543AFT',
                'a56zc57338umq6f1',
                'https://securepay.tinkoff.ru/v2'
        );

        $params = array(
            'PaymentId' => $PaymentId,
            'RebillId' => $RebillId
        );

        $api->charge($params);
        $response = json_decode(htmlspecialchars_decode($api->response), true);

        return $response;
    }

    function add_payment($customer, $amount, $order_id) {
        $payment = array('user_id' => $customer, 'summ' => $amount, 'payment_date' => date("Y-m-d H:i:s"), 'payment_method_id' => 14, 'order_id' => $order_id);

        $query = $this->db->placehold("INSERT INTO __payments SET ?%", $payment);

        $this->db->query($query);
        $orderId = $this->db->insert_id();
    }

    /* банковские карты пользователя, получить, удалить, прочее */

    /**
     * Получить баланс пользователя по фильтрам (первый попавшийся)
     *
     * @param int   $user_id  Идентификатор пользователя
     * @param array $filters  массив фильтров (например, ['zayavka' => 'ID_1C'])
     * @return false|object  Объект строки из s_user_balance либо false, если не найдено
     */
    function get_user_balance($user_id, array $filters = [])
    {
        $user_id = intval($user_id);

        $filtersQuery = '';
        foreach ($filters as $column => $value) {
            $filtersQuery .= $this->db->placehold(" AND {$column} = ?", $value);
        }

        $query = $this->db->placehold("
            SELECT
                ub.id,
                ub.user_id,
                ub.zaim_number,
                ub.zaim_summ,
                ub.percent,
                ub.ostatok_od,
                ub.ostatok_percents,
                ub.ostatok_peni,
                ub.client,
                ub.zaim_date,
                ub.zayavka,
                ub.sale_info,
                ub.payment_date,
                ub.prolongation_amount,
                ub.prolongation_summ_percents,
                ub.prolongation_summ_insurance,
                ub.prolongation_summ_sms,
                ub.prolongation_summ_cost,
                ub.prolongation_count,
                ub.expired_days,
                ub.allready_added,
                ub.last_prolongation,
                ub.last_update,
                ub.buyer,
                ub.penalty,
                ub.is_cession_shown,
                ub.inn,
                ub.current_inn,
                ub.discount_amount,
                ub.discount_date,
                sum_with_grace,
                sum_od_with_grace,
                sum_percent_with_grace 
            FROM __user_balance ub
            WHERE ub.user_id=?
            $filtersQuery
            LIMIT 1
            ", $user_id);

        $this->db->query($query);
        $user_balance = $this->db->result();
        if (empty($user_balance))
            return false;

        return $user_balance;
    }

    public function getUserActiveZaimNumber(int $user_id)
    {
        $this->db->query("
            SELECT
                ub.zaim_number
            FROM s_user_balance ub 
            WHERE ub.user_id=? 
            LIMIT 1",
            $user_id
        );
        return $this->db->result('zaim_number') ?? false;
    }

    // Добавление баланса пользователя, актуально для первого запроса
    function add_user_balance($user_balance) {
        $user_balance = (array) $user_balance;

        // Баланс у пользователя только один, проверяем, если есть, не добавим
        $query = $this->db->placehold("SELECT count(*) as count FROM __user_balance WHERE user_id=?", $user_balance['user_id']);

        $this->db->query($query);

        if ($this->db->result('count') > 0)
            return false;

        $query = $this->db->placehold("INSERT INTO __user_balance SET ?%", $user_balance);

        $this->db->query($query);
        return $this->db->insert_id();
    }

    public function get_file($id) {
        $query = $this->db->placehold("
            SELECT *
            FROM __files
            WHERE id = ?
        ", (int) $id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_files($filter = array(), $visible = true) {
        $id_filter = '';
        $user_id_filter = '';
        $status_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array) $filter['id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int) $filter['user_id']);

        if (isset($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (int) $filter['status']);


        if ($visible) {
            $visible_filter = $this->db->placehold("AND visible = ?", 1);
        } else {
            $visible_filter = $this->db->placehold("AND visible = ?", 0);
        }

        $query = $this->db->placehold("
            SELECT *
            FROM __files
            WHERE 1
                $id_filter
                $user_id_filter
                $status_filter
				$visible_filter
            ORDER BY id ASC
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function add_file($file) {
        $query = $this->db->placehold("
            INSERT INTO __files SET ?%, created = NOW()
        ", (array) $file);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_file($id, $file) {
        $query = $this->db->placehold("
            UPDATE __files SET ?% WHERE id = ?
        ", (array) $file, (int) $id);
        $this->db->query($query);

        return $id;
    }

    public function delete_file($id) {
        if ($file = $this->get_file($id)) {
            if (file_exists($this->config->root_dir . $this->config->users_files_dir . $file->name))
                unlink($this->config->root_dir . $this->config->users_files_dir . $file->name);

            if (file_exists($this->config->root_dir . $this->config->original_images_dir . $file->name))
                unlink($this->config->root_dir . $this->config->original_images_dir . $file->name);

            // Удалить все ресайзы
            $filename = pathinfo($file->name, PATHINFO_FILENAME);
            $ext = pathinfo($file->name, PATHINFO_EXTENSION);

            $rezised_images = glob($this->config->root_dir . $this->config->resized_images_dir . $filename . ".*x*." . $ext);
            if (is_array($rezised_images)) {
                foreach (glob($this->config->root_dir . $this->config->resized_images_dir . $filename . ".*x*." . $ext) as $f)
                    @unlink($f);
            }

            $query = $this->db->placehold("
                DELETE FROM __files WHERE id = ?
            ", (int) $id);
            $this->db->query($query);
        }
    }

    public function check_filename($filename) {
        $this->db->query("SELECT id FROM __files WHERE name = ?");
        return $this->db->result('id');
    }

    /* файлы */

    function add_user_files($user_files) {

        $query = $this->db->placehold("INSERT INTO __files SET ?%", $user_files);

        $this->db->query($query);
        return $this->db->insert_id();
    }

    function get_user_files($user_id) {
        $user_id = intval($user_id);

        $query = $this->db->placehold("SELECT
										*
										FROM __files WHERE user_id=?", $user_id);

        $this->db->query($query);
        $user_files = $this->db->results();

        if (empty($user_files))
            return false;

        return $user_files;
    }

    function update_user_file($id) {
        $query = $this->db->placehold("UPDATE __user_files SET status = '1' WHERE id=? LIMIT 1", intval($id));
        $this->db->query($query);
        return $id;
    }

    /* файлы */

    function delete_user_balance_for_user($user_id) {
        if (!empty($user_id)) {
            $query = $this->db->placehold("DELETE FROM __user_balance WHERE user_id=? LIMIT 1", intval($user_id));
            if ($this->db->query($query))
                return true;
        } else
            return false;
    }

    function delete_user_balance($id) {
        if (!empty($user_id)) {
            $query = $this->db->placehold("DELETE FROM __user_balance WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query))
                return true;
        } else
            return false;
    }

    function update_user_balance($id, $user_balance) {
        $user_balance = (array) $user_balance;

        if (!isset($user_balance['last_update']))
            $user_balance['last_update'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("UPDATE __user_balance SET ?% WHERE id=? LIMIT 1", $user_balance, intval($id));

        $this->db->query($query);

        return $id;
    }

    function make_up_user_balance($user_id, $balance_1c) {
        $balance_1c_norm = new stdClass();
        $balance_1c_norm->user_id = $user_id;
        $balance_1c_norm->zaim_number = isset($balance_1c->НомерЗайма) ? $balance_1c->НомерЗайма : null;
        $balance_1c_norm->zaim_summ = isset($balance_1c->СуммаЗайма) ? $balance_1c->СуммаЗайма : null;
        $balance_1c_norm->percent = isset($balance_1c->ПроцентнаяСтавка) ? $balance_1c->ПроцентнаяСтавка : null;
        $balance_1c_norm->ostatok_od = isset($balance_1c->ОстатокОД) ? $balance_1c->ОстатокОД : null;
        $balance_1c_norm->ostatok_percents = isset($balance_1c->ОстатокПроцентов) ? $balance_1c->ОстатокПроцентов : null;
        $balance_1c_norm->ostatok_peni = isset($balance_1c->ОстатокПени) ? $balance_1c->ОстатокПени : null;
        $balance_1c_norm->client = isset($balance_1c->Клиент) ? $balance_1c->Клиент : null;
        $balance_1c_norm->zaim_date = isset($balance_1c->ДатаЗайма) ? $balance_1c->ДатаЗайма : null;
        $balance_1c_norm->zayavka = isset($balance_1c->Заявка) ? $balance_1c->Заявка : null;
        $balance_1c_norm->sale_info = isset($balance_1c->ИнформацияОПродаже) ? $balance_1c->ИнформацияОПродаже : null;
        $balance_1c_norm->payment_date = isset($balance_1c->ПланДата) ? $balance_1c->ПланДата : null;
        $balance_1c_norm->prolongation_amount = isset($balance_1c->СуммаДляПролонгации) ? $balance_1c->СуммаДляПролонгации : null;
        $balance_1c_norm->last_prolongation = isset($balance_1c->ПоследняяПролонгация) ? $balance_1c->ПоследняяПролонгация : null;

        $balance_1c_norm->prolongation_summ_percents = isset($balance_1c->СуммаДляПролонгации_Проценты) ? $balance_1c->СуммаДляПролонгации_Проценты : null;
        $balance_1c_norm->prolongation_summ_insurance = isset($balance_1c->СуммаДляПролонгации_Страховка) ? $balance_1c->СуммаДляПролонгации_Страховка : null;
        $balance_1c_norm->prolongation_summ_sms = isset($balance_1c->СуммаДляПролонгации_СМС) ? $balance_1c->СуммаДляПролонгации_СМС : null;
        $balance_1c_norm->prolongation_summ_cost = isset($balance_1c->СуммаДляПролонгации_Стоимость) ? $balance_1c->СуммаДляПролонгации_Стоимость : null;
        $balance_1c_norm->prolongation_count = isset($balance_1c->КоличествоПролонгаций) ? $balance_1c->КоличествоПролонгаций : null;
        $balance_1c_norm->allready_added = isset($balance_1c->УжеНачислено) ? $balance_1c->УжеНачислено : null;
        $balance_1c_norm->penalty = isset($balance_1c->ШтрафнойКД) ? $balance_1c->ШтрафнойКД : 0;

        $balance_1c_norm->buyer = isset($balance_1c->Покупатель) ? $balance_1c->Покупатель : null;
        $balance_1c_norm->buyer_phone = isset($balance_1c->ПокупательТелефон) ? $balance_1c->ПокупательТелефон : null;
        $balance_1c_norm->sum_with_grace = isset($balance_1c->СуммаСоСкидкой) ? $balance_1c->СуммаСоСкидкой : null;
        $balance_1c_norm->sum_od_with_grace = isset($balance_1c->СуммаСоСкидкойОД) ? $balance_1c->СуммаСоСкидкойОД : null;
        $balance_1c_norm->sum_percent_with_grace = isset($balance_1c->СуммаСоСкидкойПроцент) ? $balance_1c->СуммаСоСкидкойПроцент : null;

        $balance_1c_norm->inn = isset($balance_1c->ИНН) ? $balance_1c->ИНН : null;
        $balance_1c_norm->current_inn = isset($balance_1c->ИННТекущейОрганизации) ? $balance_1c->ИННТекущейОрганизации : null;
        $balance_1c_norm->loan_type = isset($balance_1c->IL) && $balance_1c->IL == 1 ? 'IL' : 'PDL';

        /** Скидка */
        $balance_1c_norm->discount_amount = isset($balance_1c->СуммаСкидки) ? $balance_1c->СуммаСкидки : null;
        $discount_date = $balance_1c->ДатаСкидки;
        if (in_array($discount_date, ['0001-01-01 00:00:00', '0001-01-01T00:00:00'])) {
            $discount_date = null;
        }
        $balance_1c_norm->discount_date = $discount_date;

        $balance_1c_norm->referer_id = $balance_1c->РеферерID ?? null;
        $balance_1c_norm->referral_discount_amount = $balance_1c->РеферальнаяСкидка ?? 0;

        return $balance_1c_norm;
    }

    public function get_user_balance_1c($uid_1c, $log = true) {

        if (!empty($uid_1c)) {
            $inn_arr =  $this->organizations->get_site_inns();
            if (count($inn_arr) < 1){
                return false;
            }

            $z = new stdClass();
            $z->UID = $uid_1c;
            $z->Пароль = $this->settings->api_password;
            $z->Partner = 'Boostra';
            $z->ArrayINN = json_encode($inn_arr, false);

//    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($z);echo '</pre><hr />';
            $uid_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebLK.1cws?wsdl");
            $returned = $uid_client->__soapCall('GetLKINN', array($z));
            if (!empty($log))
                $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebLK.1cws?wsdl GetLK', (array) $z, (array) $returned);

            return $returned;
        } else
            return false;
    }

    /**
     * @param int $user_id
     * @param callable|null $filterCallback
     * @return object|stdClass|null
     */
    public function get_user_balance_1c_normalized(int $user_id, ?callable $filterCallback = null): ?object
    {
        $userUid = $this->get_user_uid($user_id);

        if (empty($userUid->uid)) {
            return null;
        }

        $balances = $this->soap->get_user_balances_array_1c($userUid->uid);

        if ($filterCallback === null) {
            $firstBalance = (object)current($balances);

            if (empty($firstBalance)) {
                return null;
            }

            return $this->make_up_user_balance($user_id, $firstBalance);
        }

        $filteredBalances = array_filter($balances, $filterCallback);

        if (empty($filteredBalances)) {
            return null;
        }

        $balance = (object)array_shift($filteredBalances);

        return $this->make_up_user_balance($user_id, $balance);
    }

    /**
     * Унифицированный способ получить баланс пользователя по конкретному займу (заявке 1С).
     * Источник - 1С. Если в 1С не нашли, fallback на s_user_balance по полю zayavka.
     *
     * @param int $user_id
     * @param string|int $oneCid Идентификатор заявки/договора в 1С (order->1c_id)
     * @return object Нормализованный объект баланса из 1С или объект строки s_user_balance
     * @throws Exception Если $oneCid пустой или баланс не найден в обоих источниках
     */
    public function get_user_balance_for_order(int $user_id, $oneCid): object
    {
        if ($oneCid === null || $oneCid === '') {
            throw new Exception('Не передан идентификатор заявки 1С (oneCid)');
        }

        // Пытаемся получить баланс из 1С, строго по заявке (Заявка == 1C_ID)
        $balanceFrom1C = $this->get_user_balance_1c_normalized($user_id, function ($balance) use ($oneCid) {
            $zayavka = $balance->Заявка ?? null;
            return (string)$zayavka === (string)$oneCid;
        });
        if ($balanceFrom1C) {
            return $balanceFrom1C;
        }

        // Fallback: локальная таблица s_user_balance по заявке
        $user_balance = $this->get_user_balance($user_id, ['zayavka' => (string)$oneCid]);
        if ($user_balance) {
            return $user_balance;
        }

        throw new Exception('Баланс пользователя для указанного займа не найден');
    }

    function get_groups() {
        // Выбираем группы
        $query = $this->db->placehold("SELECT g.id, g.name, g.discount FROM __groups AS g ORDER BY g.discount");
        $this->db->query($query);
        return $this->db->results();
    }

    function get_group($id) {
        // Выбираем группу
        $query = $this->db->placehold("SELECT * FROM __groups WHERE id=? LIMIT 1", $id);
        $this->db->query($query);
        $group = $this->db->result();

        return $group;
    }

    public function add_group($group) {
        $query = $this->db->placehold("INSERT INTO __groups SET ?%", $group);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    public function update_group($id, $group) {
        $query = $this->db->placehold("UPDATE __groups SET ?% WHERE id=? LIMIT 1", $group, intval($id));
        $this->db->query($query);
        return $id;
    }

    public function set_user_last_lk_visit_time($user_id, DateTime $time = null)
    {
        if ($time === null) {
            $time = new DateTime();
        }

        return $this->db->query($this->db->placehold(
            'UPDATE __users SET last_lk_visit_time = ? WHERE id = ?',
            $time->format('Y.m.d H:i:s'),
            $user_id
        ));
    }

    public function get_user_last_lk_visit_time($user_id)
    {
        $this->db->query($this->db->placehold(
            'SELECT last_lk_visit_time from __users WHERE id = ?', $user_id
        ));

        return $this->db->result('last_lk_visit_time');
    }

    public function delete_group($id) {
        if (!empty($id)) {
            $query = $this->db->placehold("UPDATE __users SET group_id=NULL WHERE group_id=? LIMIT 1", intval($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __groups WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query))
                return true;
        }
        return false;
    }

    public function check_password($email, $password) {
        $encpassword = md5($this->salt . $password . md5($password));
        $query = $this->db->placehold("SELECT id FROM __users WHERE email=? AND password=? LIMIT 1", $email, $encpassword);
        $this->db->query($query);
        if ($id = $this->db->result('id'))
            return $id;
        return false;
    }

    public function clear_phone($phone, $first_number = null)
    {
        // уберем все кроме цифр
        $phone_regex = preg_replace('/[^\d]/', '', $phone);

        // добавим первую цифру
        if (!empty($first_number)) {
            $phone_formatted = preg_replace('/^(\d)?/i', $first_number, $phone_regex);
        } else {
            $phone_formatted = $phone_regex;
        }

        return $phone_formatted;
    }

    public function get_timezone($region) {
        $region_times = array(
            "адыгея" => 0,
            "башкортостан" => 2,
            "бурятия" => 5,
            "алтай" => 4,
            "дагестан" => 0,
            "ингушетия" => 0,
            "кабардино-балкарская" => 0,
            "калмыкия" => 0,
            "карачаево-черкесская" => 0,
            "карелия" => 0,
            "коми" => 0,
            "марий эл" => 0,
            "мордовия" => 0,
            "саха /якутия/" => 6,
            "северная осетия - алания" => 0,
            "татарстан" => 0,
            "тыва" => 4,
            "удмуртская" => 1,
            "хакасия" => 4,
            "чеченская" => 0,
            "чувашская" => 0,
            "алтайский" => 4,
            "краснодарский" => 0,
            "красноярский" => 4,
            "приморский" => 7,
            "ставропольский" => 0,
            "хабаровский" => 7,
            "амурская" => 6,
            "архангельская" => 0,
            "астраханская" => 1,
            "белгородская" => 0,
            "брянская" => 0,
            "владимирская" => 0,
            "волгоградская" => 0,
            "вологодская" => 0,
            "воронежская" => 0,
            "ивановская" => 0,
            "иркутская" => 5,
            "калининградская" => -1,
            "калужская" => 0,
            "камчатский" => 9,
            "кемеровская" => 4,
            "кировская" => 0,
            "костромская" => 0,
            "курганская" => 2,
            "курская" => 0,
            "ленинградская" => 0,
            "липецкая" => 0,
            "магаданская" => 8,
            "московская" => 0,
            "мурманская" => 0,
            "нижегородская" => 0,
            "новгородская" => 0,
            "новосибирская" => 4,
            "омская" => 3,
            "оренбургская" => 2,
            "орловская" => 0,
            "пензенская" => 0,
            "пермский" => 2,
            "псковская" => 0,
            "ростовская" => 0,
            "рязанская" => 0,
            "самарская" => 1,
            "саратовская" => 1,
            "сахалинская" => 8,
            "свердловская" => 2,
            "смоленская" => 0,
            "тамбовская" => 0,
            "тверская" => 0,
            "томская" => 4,
            "тульская" => 0,
            "тюменская" => 2,
            "ульяновская" => 1,
            "челябинская" => 2,
            "забайкальский" => 6,
            "ярославская" => 0,
            "москва" => 0,
            "санкт-петербург" => 0,
            "крым" => 0,
            "ханты-мансийский автономный округ - югра" => 2,
            "чукотский" => 9,
            "ямало-ненецкий" => 2,
            "севастополь" => 0,
        );

        $zonesName = array_keys($region_times);
        $string = mb_strtolower(trim($region));
        for ($i = 0; $i < count($zonesName); $i++) {
            $zone = mb_strtolower(trim($zonesName[$i]));
            if (mb_strpos($string, $zone) !== false) {
                return $region_times[$zonesName[$i]];
            }
        }
        return 0;
    }

    public function get_registration_address($user)
    {
        $address = '';

        if ($user->Regindex)
        {
            $address .= "$user->Regindex ";
        }
        if ($user->Regregion)
        {
            $region = trim(str_replace($user->Regregion_shorttype, '', $user->Regregion));
            $address .= "$user->Regregion_shorttype. $region ";
        }
        if ($user->Regcity)
        {
            $city = trim(str_replace($user->Regcity_shorttype, '', $user->Regcity));
            $address .= "$user->Regcity_shorttype. $city ";
        }
        if ($user->Regstreet)
        {
            $street = trim(str_replace($user->Regcity_shorttype, '', $user->Regcity));
            $address .= "$user->Regstreet_shorttype. $street ";
        }
        if ($user->Reghousing)
        {
            $address .= "д. $user->Reghousing ";
        }
        if ($user->Regbuilding)
        {
            $address .= "стр. $user->Regbuilding ";
        }
        if ($user->Regroom)
        {
            $address .= "кв. $user->Regroom ";
        }

        return $address;
    }

    /**
     * Проверка роли пользователя ("Отказник", "Апрувник")
     * @param $user_id
     * @return bool
     */
    public function getUserApprove($user_id)
    {
        $query = $this->db->placehold("SELECT `status` FROM __orders WHERE user_id = ? ORDER BY id DESC LIMIT 1", $user_id);
        $this->db->query($query);
        return ((int)$this->db->result('status') !== self::USER_STATUS_CANCEL_ORDER);
    }

    /**
     * Проверка возвращал ли раньше пользователь экстра сервисы
     * @param $user_id
     * @param string $extraServiceType

     * @return int
     */
    public function getUserReturnExtraService($user_id, string $extraServiceType="")
    {
        switch ($extraServiceType) {
            case 'credit_doctor':
                $table_extra_service = boostra\domains\extraServices\CreditDoctor::table();
                break;
            case 'tv_medical':
                $table_extra_service = boostra\domains\extraServices\TVMedical::table();
                break;
            case 'multipolis':
                $table_extra_service = boostra\domains\extraServices\Multipolis::table();
                break;
            default:
                return false;
        }

        $query = $this->db->placehold("SELECT COUNT(`id`) as `count` 
                                        FROM ".$table_extra_service." 
                                        WHERE NOT(`return_date` IS NULL) AND `user_id` = ? ", $user_id);

        $this->db->query($query);
        return ( $this->db->result('count') );
    }

    /**
     * Проверяет возвраты доп. услуг (ФД, ЗО)
     * @param int $user
     * @return array
     */
    public function getUserReturnExtraServicesInfo($user)
    {
        $services = [
            'credit_doctor' => 's_credit_doctor_to_user',
            'star_oracle' => 's_star_oracle'
        ];

        $result = [];
        $user_id = $user->id;
        $currentDate = time();

        $isSafeFlow = $this->isSafetyFlow($user);  // стандартная логика если не было возвратов

        $hasRecentReturn = false;
        $returnData = [];

        foreach ($services as $service => $table) {
            $query = $this->db->placehold("
            SELECT COUNT(`id`) as `count`, 
                   MAX(`return_date`) as `last_return_date` 
            FROM {$table}
            WHERE `return_date` IS NOT NULL
              AND `user_id` = ?
        ", $user_id);

            $this->db->query($query);
            $data = $this->db->result();

            $returnCount = (int) ($data->count ?? 0);
            $lastReturnDate = $data->last_return_date ? strtotime($data->last_return_date) : null;

            $hasReturnInLast30 = $lastReturnDate && ($currentDate - $lastReturnDate <= 30 * 86400);
            $hasReturnInLast90 = $lastReturnDate && ($currentDate - $lastReturnDate <= 90 * 86400);
            $alwaysVisible = ($returnCount > 2);

            if ($hasReturnInLast30) {
                $hasRecentReturn = true;
            }

            // сохраняем данные для принятия решения
            $returnData[$service] = [
                'hasReturnInLast90' => $hasReturnInLast90,
                'alwaysVisible' => $alwaysVisible
            ];
        }

        foreach ($services as $service => $table) {
            $finalSafeFlow = $isSafeFlow;

            if ($hasRecentReturn || $returnData[$service]['alwaysVisible']) {
                $finalSafeFlow = 1;
            } elseif ($returnData[$service]['hasReturnInLast90']) {
                // если доп был возвращен 31–90 дней назад, делаем его видимым
                $finalSafeFlow = 1;
            }

            $result["final_safe_flow_{$service}"] = $finalSafeFlow;
        }

        return $result;
    }


    /**
     * Проверяет снят ли мораторий у отказника
     * @param $user_id
     * @return bool
     */
    public function getNoApprovedUserNotMoratorium($user_id): bool
    {
        $sql = "SELECT EXISTS (SELECT 
                    *
                FROM 
                    __transactions t
                LEFT JOIN __moratorium_rating m ON (m.user_id = t.user_id)
                WHERE 
                    t.user_id = ? 
                    AND t.payment_type = 'credit_rating'
                    AND `status` IN('CONFIRMED', 'AUTHORIZED')
                    AND m.date_order_added < t.created
                    ORDER BY t.id DESC LIMIT 1) as result";

        $query = $this->db->placehold($sql, $user_id);
        $this->db->query($query);
        return (bool)$this->db->result('result');
    }

    /**
     * Обновляет время последней поданной заявки
     * @param $user_id
     * @return void
     */
    public function updateNoApprovedUserMoratorium($user_id)
    {
        $query = $this->db->placehold(
            "REPLACE INTO __moratorium_rating SET ?%",
            [
                'date_order_added' => date('Y-m-d H:i:s'),
                'user_id' => intval($user_id)
            ]
        );
        $this->db->query($query);
    }

    /**
     * Проверяет наличие подписи у просроченного займа
     * @param string $zaim_number
     * @return bool
     */
    public function getZaimAspStatus(string $zaim_number): bool
    {
        $query = $this->db->placehold(
            "SELECT EXISTS (SELECT * FROM __asp_to_zaim WHERE zaim_number = ?) as r",
            $zaim_number
        );
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Добавляет запись о подписании АСП просроченного договора
     * @param string $zaim_number
     * @param int $sms_code
     * @param int $user_id
     * @return bool
     */
    public function addZaimToAsp(string $zaim_number, int $sms_code, int $user_id,string $file_name): bool
    {
        $data_insert = compact('zaim_number', 'sms_code', 'user_id','file_name');
        $query = $this->db->placehold("INSERT INTO __asp_to_zaim SET ?%", $data_insert);

        return $this->db->query($query);
    }

    /**
     * Получает данные подписи о просроченном займе по номеру займа
     * @param string $zaim_number
     * @return Object|null
     */
    public function getZaimAsp(string $zaim_number) :? Object
    {
        $query = $this->db->placehold("SELECT * FROM __asp_to_zaim WHERE zaim_number = ?", $zaim_number);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает записи о подписанных АСП просроченных договорах
     * @param int $user_id
     * @return array|null
     */
    public function getZaimListAsp(int $user_id) : array
    {
        $query = $this->db->placehold("SELECT * FROM __asp_to_zaim WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Добавляет запись о покупке или пропуске Кредитного рейтинга при регистрации нового пользователя
     * @param int $user_id
     * @param string $skip_credit_rating
     * @return bool
     */
    public function addSkipCreditRating(int $user_id, string $skip_credit_rating): bool
    {
        $data_insert = compact('skip_credit_rating');
        $query = $this->db->placehold("UPDATE __users SET ?% WHERE id = ?", $data_insert, $user_id);
        return $this->db->query($query);
    }

    /**
     * Обновляет время посещения страницы КР при регистрации
     * @param $user_id
     * @param $date_time
     * @return mixed
     */
    public function updateSkipUserTime($user_id, $date_time)
    {
        $query = $this->db->placehold("UPDATE __users SET date_skip_cr_visit = ? WHERE id = ?", $date_time, $user_id);
        return $this->db->query($query);
    }

    /**
     * Добавляет пароль к пользователю
     * @param array $data
     * @return mixed
     */
    public function addPassword(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_password SET ?%", $data);
        return $this->db->query($query);
    }

    /**
     * Обновляет пароль пользователя
     * @param array $data
     * @param int $user_id
     * @return mixed
     */
    public function editPassword(array $data, int $user_id)
    {
        $query = $this->db->placehold("UPDATE s_password SET ?% WHERE user_id = ?", $data, $user_id);
        return $this->db->query($query);
    }

    /**
     * Получает данные о пароле пользователя
     * @param int $user_id
     * @return false|int
     */
    public function getUserPassword(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_password WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Проверяет наличие пароля пользователя в БД
     * @param int $user_id
     * @return bool
     */
    public function hasUserPassword(int $user_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_password WHERE user_id = ?) as r", $user_id);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Проверяет наличие непринятого соглашения о изменении персональных данных
     * @param int $user_id
     * @return bool
     */
    public function hasUnacceptedAgreement(int $user_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_user_agreement WHERE user_id = ?) as r", $user_id);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Получает изменяемые соглашением персональные данные
     * @param int $user_id
     * @return false|int
     */
    public function getUnacceptedAgreement(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_user_agreement WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Удаляет изменяемые соглашением персональные данные из промежуточной таблицы
     * @param int $user_id
     */
    public function removeUnacceptedAgreement(int $user_id)
    {
        $query = $this->db->placehold("DELETE FROM s_user_agreement WHERE user_id = ?", $user_id);
        $this->db->query($query);
    }

    /**
     * Применяет изменяемые соглашением персональные данные и удаляет их из промежуточной таблицы
     * @param int $user_id
     * @return bool
     */
    public function applyUnnaceptedAgreement(int $user_id)
    {
        if (!$this->hasUnacceptedAgreement($user_id))
            return false;

        $new_data = $this->getUnacceptedAgreement($user_id);
        $update = [];
        foreach ($new_data as $key => $val) {
            if ($key == "user_id")
                continue;

            if (!empty($val))
                $update[$key] = $val;
        }

        $user = $this->get_user($user_id);
        $this->saveUnacceptedAgreementDocument($user, $new_data);

        $this->update_user($user_id, $update);
        $this->removeUnacceptedAgreement($user_id);

        $this->soap->update_fields($user->uid, $update);
        $this->documents->update_personal_data($user);

        return true;
    }

    private function saveUnacceptedAgreementDocument($user, $unaccepted_agreement)
    {
        foreach ($unaccepted_agreement as $key => &$value) {
            if (empty($value))
                $value = $user->$key;
        }

        $document_id = $this->documents->create_document([
            'user_id' => $user->id,
            'type' => Documents::UNACCEPTED_AGREEMENT,
            'params' => $this->getUnacceptedAgreementDocumentParams($user, $unaccepted_agreement)
        ]);

        $file_url =  $this->config->root_url . '/document/' . $user->id . '/' . $document_id;
        $storage_uid = $this->filestorage->upload_file($file_url);
        $this->db->placehold("UPDATE s_documents SET filestorage_uid = ? WHERE id = ?", $storage_uid, $document_id);
    }

    private function getUnacceptedAgreementDocumentParams($user, $unaccepted_agreement)
    {
        $numbersArray = explode(' ', $user->passport_serial); // 12 34 567890
        $old_passport_serial = $numbersArray[2]; // 567890
        $old_passport_number = $numbersArray[0] . $numbersArray[1]; // 1234

        $numbersArray = explode(' ', $unaccepted_agreement->passport_serial); // 12 34 567890
        $new_passport_serial = $numbersArray[2]; // 567890
        $new_passport_number = $numbersArray[0] . $numbersArray[1]; // 1234

        return [
            'full_name' => "{$user->lastname} {$user->firstname} {$user->patronymic}",
            'regindex' => $user->Regindex,
            'regcity_shorttype' => $user->Regcity_shorttype,
            'regcity' => $user->Regcity,
            'regstreet_shorttype' => $user->Regstreet_shorttype,
            'regstreet' => $user->Regstreet,
            'regroom' => $user->Regroom,
            'birth' => $user->birth,
            'old_passport_serial' => $old_passport_serial,
            'old_passport_number' => $old_passport_number,
            'passport_date' => $user->passport_date,
            'subdivision_code' => $user->subdivision_code,
            'passport_issued' => $user->passport_issued,
            'birth_place' => $user->birth_place,
            'phone_mobile' => $user->phone_mobile,
            'new_full_name' => "{$unaccepted_agreement->lastname} {$unaccepted_agreement->firstname} {$unaccepted_agreement->patronymic}",
            'new_birth' => $unaccepted_agreement->birth,
            'new_passport_serial' => $new_passport_serial,
            'new_passport_number' => $new_passport_number,
            'new_passport_date' => $unaccepted_agreement->passport_date,
            'new_subdivision_code' => $unaccepted_agreement->subdivision_code,
            'new_passport_issued' => $unaccepted_agreement->passport_issued,
            'new_birth_place' => $unaccepted_agreement->birth_place,
            'new_phone_mobile' => $unaccepted_agreement->phone_mobile,
        ];
    }

    /**
     * Получает список карт пользователя
     * @param int $user_id
     * @return array
     */
    public function getUserCardsByUserId(int $user_id): array
    {
        $user = $this->get_user($user_id);
        $cards = [];

        if ($user->uid != "Error")
        {
            $b2p_enabled = $this->settings->b2p_enabled || $user->use_b2p;
            if (!empty($b2p_enabled))
            {
                $b2p_cards = $this->best2pay->get_cards(compact('user_id'));

                $cards = array_map(function ($card) {
                    $card->default = ($_COOKIE['card_pay_id'] ?? null) == $card->id;
                    $card->autodebiting = false;
                    $card->rebill_id = false;

                    return $card;
                }, $b2p_cards);
            } else {
                $soap_cards = $this->notify->soap_get_card_list($user->uid);

                if ($soap_cards)
                {
                    foreach ($soap_cards as $card)
                    {
                        if ($card->Status == 'A')
                        {
                            $new_card = new stdClass();
                            $new_card->id = $card->CardId;
                            $new_card->pan = $card->Pan;
                            $new_card->autodebiting = $card->AutoDebiting ?? 0; // @todo этого признака нет в АПИ Тинька https://acdn.tinkoff.ru/static/documents/merchant_api_protocoI_e2c_v2.pdf стр. 30
                            $new_card->rebill_id = $card->RebillId;
                            $new_card->default = ($_COOKIE['card_pay_id'] ?? null) == $card->id;

                            $cards[] = $new_card;
                        }
                    }
                }
            }
        }

        return $cards;
    }

    /**
     * Получает ссылку на привязку карты Тинькофф
     * @param string $uid
     * @return mixed|string
     */
    public function getCardAddTinkoff(string $uid)
    {
        $add_card = $this->tinkoff->add_card($uid);
        if (isset($add_card['error']) && $add_card['error'] == 'Найдено больше одного CustomerKey')
        {
            $this->tinkoff->remove_customer($uid);
            $add_card = $this->tinkoff->add_card($uid);
        }
        return $add_card['PaymentURL'] ?? '';
    }

    public function getTicketId($email){
        $query = $this->db->placehold("Select ticket_id from vox_tickets where email = ?", (string)$email);
        $this->db->query($query);
        $result = $this->db->result();

        return $result->ticket_id;
    }

    public function setTicketId($data)
    {
        $query = $this->db->placehold("
            INSERT INTO vox_tickets SET ?%
        ", (array) $data);
        $this->db->query($query);

    }

    /**
     * Возвращает ПДН, если уведомление о его превышении ещё не подписано.
     *
     * Если ПДН не превышен или уведомление уже подписано - возвращает false.
     * @param int $user_id
     * @return false|float
     */
    public function getExcessedPdn(int $user_id)
    {
        //  Поиск одобренной заявки
        $query = $this->db->placehold("SELECT status, pdn_notification_shown FROM s_orders WHERE user_id = ? ORDER BY id DESC LIMIT 1", $user_id);
        $this->db->query($query);
        $result = $this->db->result();
        if ($result->status == 2 && $result->pdn_notification_shown == 0)
        {
            $pdn = $this->contracts->get_pdn($user_id) * 100;
            return $pdn >= 50 ? round($pdn, 1) : false;
        }
        return false;
    }

    public function applyExcessedPdnNotification(int $user_id, int $sms_code)
    {
        $this->saveExcessedPdnDocument($user_id, $sms_code);
        $query = $this->db->placehold("UPDATE s_orders SET pdn_notification_shown = 1 WHERE user_id = ? AND status = 2 ORDER BY id DESC LIMIT 1", $user_id);
        $this->db->query($query);
    }

    private function saveExcessedPdnDocument(int $user_id, int $sms_code)
    {
        $last_order = $this->orders->get_last_order($user_id);
        $order_id = empty($last_order) ? 0 : $last_order->id;
        $user = $this->get_user($user_id);
        $document_id = $this->documents->create_document([
            'user_id' => $user_id,
            'order_id' => $order_id,
            'type' => Documents::PDN_EXCESSED,
            'params' => $this->getExcessedPdnDocumentParams($user, $sms_code)
        ]);

        $file_url =  $this->config->root_url . '/document/' . $user->id . '/' . $document_id;
        $storage_uid = $this->filestorage->upload_file($file_url);
        $this->documents->update_document($document_id, [
            'filestorage_uid' => $storage_uid,
        ]);
    }

    public function getExcessedPdnDocumentParams($user, int $sms_code)
    {
        $passport = $this::splitPassportSerial($user->passport_serial);
        $passport_serial = $passport['serial'];
        $passport_number = $passport['number'];

        return [
            'full_name' => "{$user->lastname} {$user->firstname} {$user->patronymic}",
            'birth' => $user->birth,
            'passport_serial' => $passport_serial,
            'passport_number' => $passport_number,
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'regregion' => $user->Regregion,
            'regcity' => $user->Regcity,
            'regstreet' => $user->Regstreet,
            'reghousing' => $user->Reghousing,
            'regroom' => $user->Regroom,
            'sms' => $sms_code,
            'receiving_date' => date('d.m.Y'),
            'pdn' => $this->users->getExcessedPdn($user->id)
        ];
    }

    /**
     * Разделение строки серии паспорта из БД на номер и серию
     *
     * (12 34 567890 -> serial: 1234, number: 567890)
     * @param string $serial
     * @return array
     */
    public static function splitPassportSerial(string $serial)
    {
        $clear_passport_serial = preg_replace('/[^0-9]/', '', $serial);
        $passport_serial = substr($clear_passport_serial, 0, 4);
        $passport_number = substr($clear_passport_serial, 4);
        return [
            'number' => $passport_number,
            'serial' => $passport_serial
        ];
    }

    /**
     * Users::calc_percents()
     *
     * @param object $balance
     * @return float $calc_percents
     */
    public function calc_percents($balance)
    {
        // 1% От основного долга
        $base_calc_percents = round($balance->ostatok_od * 0.01);

        $calc_percents = 0;

        $issuance_datetime = new DateTime(date('Y-m-d', strtotime($balance->zaim_date)));
        $payment_datetime = new DateTime(date('Y-m-d', strtotime($balance->payment_date)));
        $today_datetime = new DateTime(date('Y-m-d'));

        if ($issuance_datetime == $today_datetime) {
            // Когда списывается 100 рублей:
            // - Если пролонгация в день выдачи займа. (Любого, хоть безпроцентного, хоть обычного))

            $calc_percents = $base_calc_percents;

        } elseif ($balance->percent == 0 && $balance->prolongation_count == 0 && $payment_datetime >= $today_datetime) {

            // - При заключении договора под 0% , в случае пролонгации ДО ПРОСРОЧКИ идет перерасчет суммы
            // минимального платежа, как при взятии займа при 0,8% (или любой другой процент, так как бывают
            // и акционные проценты) , на текущий день займа.
            $diff_days = $today_datetime->diff($issuance_datetime)->days;

            if ($diff_days > 0) {
                $calc_percents = round($diff_days * $this->orders::BASE_PERCENTS * $balance->ostatok_od / 100, 2);
            }

        } elseif ($balance->prolongation_amount > 0) {
            // не более 31 дня просрочки
            $prolongation_datetime = (new DateTime(date('Y-m-d')))->add(new DateInterval('P16D'));
            $border_datetime = (new DateTime(date('Y-m-d')))->sub(new DateInterval('P31D'));

            if ($border_datetime < $payment_datetime) {
                if ($prolongation_datetime > $payment_datetime && $balance->prolongation_count < 5) {
                    $calc_percents = $base_calc_percents;
                }
            }
        } 

        return $calc_percents;
    }

    /**
     * @param int $user_id
     * @return false|int
     */
    public function getBasicCard(int $user_id)
    {
        $query = $this->db->placehold("
            SELECT card_id FROM __user_basic_cards WHERE user_id = '?'
        ", $user_id);
        $this->db->query($query);
        return $this->db->result('card_id');
    }

    /**
     * @param int $user_id
     * @param int $card_id
     * @return void
     */
    public function setBasicCard(int $user_id, int $card_id)
    {
        $query = $this->db->placehold('INSERT INTO __user_basic_cards(user_id,card_id) VALUES (?,?)', $user_id,$card_id);

        $this->db->query($query);
    }

    /**
     * @param int $user_id
     * @param int $card_id
     * @return void
     */
    public function updateBasicCard(int $user_id, int $card_id){
        $query = $this->db->placehold("UPDATE __user_basic_cards SET card_id=? WHERE user_id=?", $card_id,$user_id);

        $this->db->query($query);
    }

    /**
     * @param $time
     * @param $ip
     * @param $date
     * @param $webmaster_id
     * @return void
     */
    public function add_loan_funnel_report( $ip, $webmaster_id, $time = null, $date = null ): void
    {
        $time = $time ?? time();
        $date = $date ?? date( 'Y-m-d' );
        
        $query = $this->db->placehold(
            'INSERT INTO __loan_funnel_report (time,user_ip,link_date,webmaster_id) VALUES (?,?,?,?)',
            $time,
            $ip,
            $date,
            $webmaster_id
        );
        
        $this->db->query($query);
    }

    /**
     * @param string $time
     * @param string $ip
     * @param array $data
     * @return void
     */
    public function update_loan_funnel_report(string $time, string $ip, bool $bool, array $data)
    {
        if (!$bool) {
            $query = $this->db->placehold("
                UPDATE s_loan_funnel_report SET ?% 
                                            WHERE  time = ? AND 
                                            (user_id IS NULL OR user_id = '') AND 
                                            user_ip = ?",
                $data, $time, $ip);
        } else {
            $query = $this->db->placehold("
                UPDATE s_loan_funnel_report SET ?% 
                                            WHERE  time = ? AND 
                                            user_ip = ?",
                $data, $time, $ip);
            if(isset($_SESSION['web']) && $_SESSION['web'] == '5555') {
                file_put_contents('voximplant.txt', "query: $query \n", FILE_APPEND);
            }
        }
        $this->db->query($query);
    }

    public function update_loan_funnel_report_issued($order_id, $user_id, $data)
    {
        $query = $this->db->placehold("
                UPDATE s_loan_funnel_report SET ?% 
                                            WHERE  order_id = ? AND 
                                            user_id = ?",
            $data, $order_id, $user_id);
        $this->db->query($query);
    }


    public function updateSessionData($oldTime,$newTime, $ip) {
        $query = $this->db->placehold("
                UPDATE s_loan_funnel_report SET time = ? 
                                            WHERE  time = ? AND 
                                            user_ip = ?",
            $newTime, $oldTime, $ip);
        $this->db->query($query);
    }

    public function blockedUserCabinet(int $user_id)
    {
        $user = $this->get_user($user_id);
        $this->update_user($user_id, ['blocked' => 1]);
        $this->changelogs->add_changelog(
            [
                'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'blocked',
                'old_values' => $user->blocked,
                'new_values' => 1,
                'user_id' => $user_id,
            ]
        );
    }

    public function get_organization_id($balances, $user_balance = null)
    { 
        $organization_id = $this->organizations->get_base_organization_id();

        if (!empty($balances)) {
            foreach ($balances as $balance) {
                if (empty($user_balance) || $user_balance->zaim_number == $balance['НомерЗайма']) {
                    if (!empty($balance['ИННТекущейОрганизации'])) {
                        $organization_id = $this->organizations->get_organization_id_by_inn($balance['ИННТекущейОрганизации']);
                    } elseif (!empty($balance['ИНН'])) {
                        $organization_id = $this->organizations->get_organization_id_by_inn($balance['ИНН']);
                    }
                }
            }
        }
        
        return $organization_id;
    }

    public function getOrganizationIdByOrderId($orderId)
    {
        $query = $this->db->placehold("
        SELECT organization_id FROM __orders WHERE id = ?
    ", (int)$orderId);
        $this->db->query($query);
        return $this->db->result('organization_id');
    }

    /**
     * Users::check_5days_maratorium()
     * Проверяет подпадает ли пользователь под мораторий 5дней
     * Вовращает либо NULL либо дату окончания моратория
     * @param mixed $user_id
     * @return mixed
     */
    public function check_5days_maratorium($user_id)
    {
        $enabled_5days_maratorium = $this->settings->enabled_5days_maratorium;
        if (empty($enabled_5days_maratorium)) {
            return NULL;
        }
        
        if ($last_contract = $this->contracts->get_contract_by_params(['user_id' => $user_id, 'order_by' => 'id DESC'])) {
            // если выдача была больше 10 дней назад то смысла проверять дальше нет
            if (!empty($last_contract->issuance_date) && strtotime($last_contract->issuance_date) > strtotime('-10 day')) {

                $user = $this->users->get_user((int)$user_id);
                $credits_history = $this->soap->get_user_credits($user->uid);
                $loan_history = $this->users->save_loan_history($user_id, $credits_history);

                foreach ($loan_history as $loan) {
                    if (empty($last_loan) || strtotime($loan->date) > strtotime($last_loan->date)) {
                        $last_loan = $loan;
                    }
                }
                if (!empty($last_loan->close_date)) {                    
                    $dt_date = date_create(date('Y-m-d', strtotime($last_loan->date)));
                    $dt_close_date = date_create(date('Y-m-d', strtotime($last_loan->close_date)));
                    $last_loan_period = date_diff($dt_date, $dt_close_date, true)->format('%a');
                    if ($last_loan_period < 5) {
                        $maratorium_time = strtotime('+5 day', strtotime($last_loan->close_date));
                        if (time() < $maratorium_time) {
                            return date('Y-m-d H:i:s', $maratorium_time);
                        }
                    }
                }
            }
        }
        return NULL;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isProlongationZero($user_id): bool
    {
        $query = $this->db->placehold("SELECT prolongation_count FROM __user_balance WHERE user_id=? LIMIT 1", intval($user_id));
        $this->db->query($query);
        $prolongation = $this->db->result();

        return ($prolongation->prolongation_count == 0);
    }

    /**
     * Пробует сконвертировать дату в формат дд.мм.гггг
     *
     * Если не получилось - возвращает старую дату
     * @param string|null $date
     * @return string
     */
    public function tryFormatDate($date): string
    {
        if (empty($date))
            return $date;

        // Удаляем всё кроме цифр
        $date = preg_replace('/[^0-9]/', '', $date);
        if (strlen($date) == 8 &&
            preg_match('/^(\d{2})(\d{2})(\d{4})$/', $date, $matches)) {
            $dateTime = DateTime::createFromFormat('d-m-Y', "$matches[1]-$matches[2]-$matches[3]");
            return $dateTime->format('d.m.Y');
        }
        return $date;
    }

    /**
     * Пробует сконвертировать серию паспорта вида "2409454626" в формат "24 09 454626"
     *
     * Если не получилось (или уже в этом формате) - возвращает старую серию
     * @param string|null $serial
     * @return string
     */
    public function tryFormatPassportSerial($serial): string
    {
        if (empty($serial) || preg_match('/^\d{2} \d{2} \d{6}$/', $serial))
            return $serial;

        // Удаляем всё кроме цифр
        $formattedSerial = preg_replace('/\D/', '', $serial);
        if (strlen($formattedSerial) < 10)
            return $serial;

        return substr($formattedSerial, 0, 2) . ' ' . substr($formattedSerial, 2, 2) . ' ' . substr($formattedSerial, 4);
    }

    /**
     * Добавляет номер телефона для инициализации
     * @param array $data
     * @return mixed
     */
    public function addInitUserPhone(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_init_user_phones SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param string $phone
     * @return false|int
     */
    public function deleteInitUserPhone(string $phone)
    {
        $query = $this->db->placehold("DELETE FROM s_init_user_phones WHERE phone = ?", $phone);
        return $this->db->query($query);
    }

    /**
     * Поиск номера телефона для инициализации
     * @param string $phone
     * @return mixed
     */
    public function getInitUserPhone(string $phone)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_init_user_phones 
            WHERE phone = ?", $phone);
        $this->db->query($query);
        $result = $this->db->results();

        return $result;
    }

     /** Получение СБП счетов клиента
     * @param $id
     * @return false|array|null
     */
    public function getSbpAccounts($id) {
        $query = $this->db->placehold("
            SELECT * 
            FROM b2p_sbp_accounts
            WHERE user_id = ? AND deleted = 0
        ", $id);
        $this->db->query($query);
        $result = $this->db->results();

        return $result;
    }

    /**
     * Проверяет пользователя в списках кому надо заблокировать допы, если нет просрочки
     * @param string $phone
     * @return false|int
     */
    public function hasOverdueHideUserService(string $phone): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_overdue_hide_service WHERE phone = ?) as r", $phone);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Проверка, подписан ли на TG бота и включен ли баннер
     * @param string $phone
     * @return bool
     */
    public function checkTgNickname(string $phone): bool
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM tg_nicknames
            WHERE phone_number = ?
        ", $phone);
        $this->db->query($query);
        if (!$this->db->result() && $this->settings->telegram_banner) {
            return false;
        }
        return true;
    }

    public function encodingString($string, $key = 'boostra_key-2024')
    {
        $string=base64_encode($string);//Переводим в base64

        $arr=array();//Это массив
        $x=0;
        while ($x++< strlen($string)) {//Цикл
            $arr[$x-1] = md5(md5($key.$string[$x-1]).$key);//Почти чистый md5
            $newstr = $newstr.$arr[$x-1][3].$arr[$x-1][6].$arr[$x-1][1].$arr[$x-1][2];//Склеиваем символы
        }
        return $newstr;
    }

    /**
     * @param $hash
     * @return false|int
     */
    public function getTelegramHash($hash)
    {

        $query = $this->db->placehold("
            SELECT * 
            FROM tg_auth_hash
            WHERE hash = ?
        ", $hash);
        $this->db->query($query);
        $result = $this->db->result();

        if ($result) {
            $deleteQuery = $this->db->placehold("
            DELETE FROM tg_auth_hash
            WHERE hash = ?
        ", $hash);
            $this->db->query($deleteQuery);
        }

        return $result;
    }

    public function getGifts($user_id, bool $promo = false)
    {
        // status = 2 - просрочен
        $query = $this->db->placehold("
            SELECT * 
            FROM user_order_gifts
            WHERE user_id = ?
            ".($promo ? "AND promocode IS NOT NULL AND status != 2" : "")."
            ORDER BY id DESC LIMIT 1
        ", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function updateGift($user_id, $data)
    {
        $this->db->query(
            $this->db->placehold(
                "UPDATE user_order_gifts SET ?% WHERE user_id = ? ORDER BY id DESC LIMIT 1",
                $data,
                (int) $user_id
            )
        );
        $this->logging(__METHOD__, 'Best2payCallback', [], [$this->db->placehold(
            "UPDATE user_order_gifts SET ?% WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            $data,
            (int) $user_id
        )], 'aaaaa.txt');
    }

    public function check_contact_me_notice_availability(int $userId): bool
    {
        if (!$this->settings->notice_contact_me_enabled) return false;

        $user = $this->get_user($userId);
        $newClientsOrAll = [
            $this->settings::CONTACT_ME_NOTICE_ENABLED_FOR_NEW_CLIENTS,
            $this->settings::CONTACT_ME_NOTICE_ENABLED_FOR_ALL
        ];
        $repeatClientsOrAll = [
            $this->settings::CONTACT_ME_NOTICE_ENABLED_FOR_REPEAT_CLIENTS,
            $this->settings::CONTACT_ME_NOTICE_ENABLED_FOR_ALL
        ];

        if( empty($user->loan_history )) {
            // Доступен ли нотис для НК (2) или Всех (3)?
            return in_array($this->settings->notice_contact_me_enabled_for, $newClientsOrAll);
        }

        $loans =  array_filter( $user->loan_history, static function( $loan ){
            return ! empty( $loan->close_date );
        } );

        if (empty($loans)) {
            // Доступен ли нотис для НК (2) или Всех (3)?
            return in_array($this->settings->notice_contact_me_enabled_for, $newClientsOrAll);
        }

        // Доступен ли нотис для ПК (1) или всех (3)?
        return in_array($this->settings->notice_contact_me_enabled_for, $repeatClientsOrAll);
    }

    /**
     * Проверяет корректность номера при регистрации
     *
     * @param $phone
     * @return string[]
     * @throws SoapFault
     */
    public function validateUserByPhone($phone): array
    {
        $existUser = $this->users->get_user($phone);
        if ($existUser) {
            return [
                'type' => 'user_db_exists',
                'message' => 'Пользователь с таким телефоном уже существует.',
            ];
        }

        $isBlocked = Helpers::isBlockedUserBy1C($this, $phone);
        if ($isBlocked) {
            return [
                'type' => 'user_blocked',
                'message' => 'Пользователь заблокирован.',
            ];
        }

        $soap = $this->soap->get_uid_by_phone($phone);
        if (!empty($soap->uid)) {
            return [
                'type' => 'user_exists_1c',
                'message' => 'Пользователь с таким телефоном уже существует.',
            ];
        }

        return [
            'result' => true,
        ];
    }

    /**
     * Записывает признак того, что нового пользователя нужно будет вести по автовыдачи
     * @param int $user_id
     * @param $user
     * @return void
     */
    public function initAutoConfirmNewUser(int $user_id, $user)
    {
        if (is_array($user)) {
            $user = (object)$user;
        }

        $utm_source = trim($user->utm_source ?? '');
        if (!in_array($utm_source, array_map('trim', $this->settings->autoconfirm_flow_utm_sources ?? []))) {
            return;
        }

        if ($this->autoconfirm->is_enabled($user)) {
            $this->user_data->set($user_id, $this->user_data::AUTOCONFIRM_FLOW, 1);
            $this->user_data->set($user_id, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW_VALUE);
        }
    }


    /**
     * Записывает признак того, что нового пользователя нужно будет вести по автовыдачи 2.0
     * @param int $user_id
     * @param $user
     * @return void
     */
    public function initAutoConfirm2NewUser(int $user_id, $user)
    {
        if (is_array($user)) {
            $user = (object)$user;
        }

        $utm_source = trim($user->utm_source ?? '');
        if (!in_array($utm_source, array_map('trim', $this->settings->autoconfirm_2_flow_utm_sources ?? []))) {
            return;
        }

        if ($this->autoconfirm->is_enabled_without_safety_flow($user)) {
            $this->user_data->set($user_id, $this->user_data::AUTOCONFIRM_2_FLOW, 1);
            $this->user_data->set($user_id, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW, $this->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE);
        }
    }

    /**
     * Проверяет, включена ли рекуррентная оплата ЗО для текущего пользователя
     *
     * @param int $userId
     * @return int 1 если рекуррентная оплата разрешена, 0 если запрещена
     */
    public function isRecurringPaymentSoEnabled(int $userId): int
    {
        // Проверяем глобальную настройку
        if (!filter_var($this->settings->recurring_payment_so, FILTER_VALIDATE_BOOL)) {
            return 0;
        }

        // Получаем значение для клиента
        $clientValue = $this->user_data->read($userId, 'client_recurring_payment_so');

        // Если записи нет или значение не равно 0 - разрешаем
        if ($clientValue === null || filter_var($clientValue, FILTER_VALIDATE_BOOL)) {
            return 1;
        }

        return 0;
    }

    /**
     * Авторизуем пользователя по Id
     *
     * @param int $user_id
     * @return void
     */
    public function authUserById(int $user_id): void
    {
        $_SESSION['user_id'] = $user_id;
        setcookie('user_id', $user_id, time() + 86400 * 365, '/');
        UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

        $this->request->redirect($this->config->root_url . '/user');
    }

    /**
     * Добавляет историю входа из сервисов ГосУслуги, ТИд ...
     *
     * @param int $user_id
     * @param string $type
     * @return mixed
     */
    public function addAuthUser(int $user_id, string $type)
    {
        $query = $this->db->placehold("INSERT INTO __auth_users SET user_id = ? , type = ?", $user_id, $type);

        $this->db->query($query);
        return $this->db->insert_id();
    }


    /**
     * Проверка заполненности анкеты
     * Если есть активный займ, исключаем проверку card_added
     *
     * @param object $user
     * @return bool
     */
    public function validateUserFields(object $user): bool
    {
        $user = (array)$user;

        $hasActiveLoan = $this->hasActiveLoanViaGetLK($user['uid'] ?? '');

        $requiredFields = UserHelper::REQUIRE_FIELDS;

        if ($hasActiveLoan) {
            unset($requiredFields['card_added']);
        }

        // Сначала проверим шаги
        $find_bad_element = array_filter($user, function ($value, $key) use ($requiredFields) {
            return in_array($key, array_keys($requiredFields)) && empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        // Если шаги заполнены, проверим обязательное поле в каждом шаге
        if (empty($find_bad_element)) {
            foreach ($requiredFields as $key => $fields) {
                if (empty($fields)) {
                    continue;
                }

                $find_element = array_filter($user, function ($value, $key_field) use ($fields) {
                    return in_array($key_field, $fields) && empty($value);
                }, ARRAY_FILTER_USE_BOTH);

                if (!empty($find_element)) {
                    $this->users->update_user($user['id'], [$key => 0]);
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Чистим данные сессии, для флоу регистрация после персональных данных
     */
    public function clearSessionDataFlowAfterPersonalData()
    {
        unset($_SESSION['user_info'], $_SESSION['flow_after_personal_data_step_register']);
    }

    /**
     * Показывает можно ли показывать окно подписи АСП автоподпсиания для будущей заявки utm_source=crm_auto_approve
     *
     * Проверка идет по меткам пользователя и включенной настройки автоподписи
     *
     * @param $user
     * @return bool
     */
    public function isAutoConfirmCrmAutoApproveOrder($user): bool
    {
        if (is_array($user)) {
            $user = (object)$user;
        }

        $utm_source = trim($user->utm_source ?? '');
        $utm_enabled =  in_array($utm_source, array_map('trim', $this->settings->autoconfirm_crm_auto_approve_utm_sources ?? []));

        return $utm_enabled && $this->autoconfirm->is_enabled($user);
    }

    /**
     * Проверяем находится ли пользователь в whitelist и включена ли эта функция
     */
    public function allowedByWhitelist(int $userId): bool
    {
        return $this->settings->whitelist_dop && $this->user_data->read($userId, $this->user_data::WHITELIST_DOP);
    }

    public function isRegistrationCompleted(stdClass $user): bool
    {
        return $user->personal_data_added &&
            $user->address_data_added &&
            $user->accept_data_added &&
            $user->card_added &&
            $user->files_added &&
            $user->additional_data_added;
    }

    /**
     * Проверяет наличие активного займа у пользователя через метод GetLK из 1С
     *
     * @param string $uid_1c UID пользователя в 1С
     * @return bool true если есть активный займ (ОстатокОД > 0), false если нет
     */
    public function hasActiveLoanViaGetLK(string $uid_1c): bool
    {
        try {
            if (empty($uid_1c) || $uid_1c === 'Error') {
                return false;
            }

            $lk_data = $this->get_user_balance_1c($uid_1c, false);
            
            if (empty($lk_data) || empty($lk_data->return)) {
                return false;
            }

            $balance_data = $lk_data->return;

            if (is_object($balance_data)) {
                error_log('is_object($balance_data)');
                $balance_data = (array)$balance_data;
            }
            
            if (empty($balance_data)) {
                return false;
            }

            // Проверяем есть ли активный займ (остаток основного долга больше 0)
            return isset($balance_data['ОстатокОД']) && (float)$balance_data['ОстатокОД'] > 0;

        } catch (Exception $e) {;
            return false;
        }
    }

    /**
     * Клиент ПК или Старорег
     *
     * @param object $user
     * @return bool
     */
    public function isUserOldClientOrOldRegister(object $user): bool
    {
        if (!empty($user->loan_history)) {
            return true;
        } else {
            $lastOrder = $this->orders->get_last_order((int)$user->id);
            return !empty($lastOrder->complete);
        }
    }

    /**
     * Количество пользователей по телефону
     *
     * @param string $phone_mobile
     * @return int
     */
    public function getUsersCountByPhone(string $phone_mobile): int
    {
        $phone_mobile = $this->clear_phone($phone_mobile, '7');

        $query = $this->db->placehold("
                SELECT 
                    COUNT(*) as total
                FROM __users
                WHERE phone_mobile = ?
            ", $phone_mobile
        );

        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    /**
     * Преобразовывает паспорт в новый формат
     * @param string $passport_serial
     * @return array|string|string[]|null
     */
    public static function getClearPassport(string $passport_serial)
    {
        return preg_replace('/\D/', '', $passport_serial);
    }

    /**
     * Количество пользователей по паспорту
     * @param string $passport_serial
     * @return int
     */
    public function getUsersCountByPassport(string $passport_serial): int
    {
        $passport_clear = $this->getClearPassport($passport_serial);
        $query = $this->db->placehold("
                SELECT 
                    COUNT(*) as total
                FROM __users
                WHERE passport_serial = ?
            ", $passport_clear
        );

        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    /**
     * Получить пользователя по паспорту
     *
     * @param string $passport_serial
     * @return false|stdClass
     */
    public function getUserByPassport(string $passport_serial)
    {
        $user_id = $this->get_passport_user($passport_serial);

        if (empty($user_id)) {
            return null;
        }

        return $this->get_user((int)$user_id);
    }

    /**
     * Проверяет наличие активного моратория для пользователя и заказа
     */
    public function hasActiveMoratorium(object $user, object $order): bool
    {
        // Проверяем глобальный мораторий пользователя
        if ($this->hasUserMoratorium($user)) {
            return true;
        }

        // Проверяем мораторий на основе причины отказа
        return $this->hasReasonBasedMoratorium($order);
    }

    /**
     * Проверяет глобальный мораторий пользователя
     */
    public function hasUserMoratorium(object $user): bool
    {
        if (empty($user->maratorium_date)) {
            return false;
        }

        return strtotime($user->maratorium_date) > time();
    }

    /**
     * Проверяет мораторий на основе причины отказа заказа
     */
    public function hasReasonBasedMoratorium(object $order): bool
    {
        $reason = $this->reasons->get_reason($order->reason_id);

        if (empty($reason->maratory)) {
            return false;
        }

        $moratoriumDays = (int) $reason->maratory;
        if ($moratoriumDays <= 0) {
            return false;
        }

        $moratoriumEndDate = (new DateTime($order->date))->modify("+{$moratoriumDays} days");

        return $moratoriumEndDate->getTimestamp() > time();
    }

    /**
     * Проверяем были ли у пользователя заявки
     *
     * @param int $user_id
     * @return true
     */
    public function userHasOrders(int $user_id): bool
    {
        // На всякий случай проверим последнюю заявку, чтобы она была не пинг 3
        $sql = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_orders WHERE user_id = ?) as r", $user_id);
        $this->db->query($sql);
        return (bool)$this->db->result('r');
    }

    /**
     * Проверка пользователь в ЧС или нет
     * @param StdClass $user
     * @return bool
     */
    public function checkUserBlackList(StdClass $user): bool
    {
        $blackListBD = $this->checkIsUserInBDBlackList($user->id);

        if ($blackListBD) {
            return true;
        }

        if (!empty($user->uid)) {
            return $this->checkIsUserIn1cBlacklist($user->uid);
        }

        return false;
    }

    /**
     * Проверка пользователя на ЧС в базе данных
     * @param int $userId
     * @return false|int
     */
    public function checkIsUserInBDBlackList(int $userId)
    {
        $this->db->query($this->db->placehold("SELECT id FROM __blacklist WHERE user_id = ?", $userId));
        return $this->db->result('id');
    }

    /**
     * Проверка ЧС в 1С
     * @param string $uid
     * @return bool
     */
    public function checkIsUserIn1cBlacklist(string $uid): bool
    {
        $data = $this->soap->generateObject(['ContragentUID' => $uid]);

        $responseData = $this->soap->requestSoap($data, 'WebSignal', 'isContragentInBlacklist');
        if (isset($responseData['response'])) {
            return (bool) $responseData['response'];
        }

        return false;
    }

    /**
     * @param $email
     * @return false|int
     */
    public function get_user_by_email($email)
    {
        $query = $this->db->placehold("SELECT * FROM __users WHERE email = ? LIMIT 1", (string) $email);
        $this->db->query($query);

        return $this->db->result();
    }
}
