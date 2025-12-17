<?php

/**
 * Simpla CMS
 *
 * @copyright	2014 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

require_once(__DIR__ . '/Simpla.php');
require_once(__DIR__ . '/CreditDoctor.php');
require_once(__DIR__ . '/Helpers.php');

require_once dirname(__DIR__) . '/app/Modules/Referral/Services/ReferralService.php';
require_once dirname(__DIR__) . '/app/Modules/User/Repositories/UserRepository.php';

class Orders extends Simpla
{
    const STATUS_FILLING = 0;
    const STATUS_NEW = 1;

    public const PDL_MAX_AMOUNT = 30_000;
    
    /**
     * Типы займов
     */
    public const LOAN_TYPE_PDL = 'PDL';
    public const LOAN_TYPE_IL = 'IL';
    
    /**
     * Базовая процентная ставка
     */
    const BASE_PERCENTS = 0.8;

    /**
     * Статус разделенной заявки "Новая"
     */
    const DIVIDE_ORDER_STATUS_NEW = 'NEW';

    /**
     * Статус добавления разделенной заявки к главной
     */
    const DIVIDE_ORDER_STATUS_ADD_NEW_ORDER = 'ADD_NEW_ORDER';

    /**
     * Статус разделенной заявки "Одобрено"
     */
    const DIVIDE_ORDER_STATUS_APPROVED = 'APPROVED';

    /**
     * Статус разделенной заявки "Выдана"
     */
    const DIVIDE_ORDER_STATUS_ISSUED = 'ISSUED';

    /**
     * Статус разделенной заявки "Закрыта"
     */
    public const DIVIDE_ORDER_STATUS_CLOSED = 'CLOSED';

    /**
     * Статус закрытия разделенной заявки, если закрылся только первый займ
     */
    public const DIVIDE_ORDER_STATUS_CLOSED_BY_ONE = 'CLOSED_BY_ONE';

    /**
     * Статус разделенной заявки "Ошибки"
     */
    public const DIVIDE_ORDER_STATUS_ERROR = 'ERROR';

    /**
     * Статусы до того как разделенная заявка одобрена, отказана, или выдана
     */
    const DIVIDE_ORDER_STATUSES_IS_NEW = [
        self::DIVIDE_ORDER_STATUS_NEW,
        self::DIVIDE_ORDER_STATUS_ADD_NEW_ORDER,
    ];

    /**
     * Платежная система Тинькофф
     */
    const PAYMENT_METHOD_TINKOFF = 'TINKOFF';

    /**
     * Метка для кросс-заявок 
     */
    public const UTM_SOURCE_CROSS_ORDER = 'cross_order';

    /**
     * Платежная система б2п
     */
    const PAYMENT_METHOD_B2P = 'B2P';

    /**
     * ИП Алфавит
     */
    const INSURER_AL = 'AL';

    /**
     *  Статус одобренно из CRM
     */
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_SIGNED = 8;
    public const STATUS_PROCESS = 9;
    public const STATUS_CONFIRMED = 10;
    public const STATUS_NOT_ISSUED = 11;
    public const STATUS_CLOSED = 12;
    public const STATUS_WAIT = 13;

    /**
     * Статус для ожидания привязки карты на сектор кросс-ордера.
     * По истечении установленного времени переходит в статус STATUS_SIGNED (см. в crm cron/b2p_issuance.php:check_wait_for_card_orders())
     */
    public const STATUS_WAIT_CARD = 14;
    public const ORDER_STATUS_CRM_AUTOCONFIRM = 15;
	public const STATUS_WAIT_VIRTUAL_CARD = 16;
    public const STATUS_COOLING = 17;

    /**
     * Статус из 1С 
     */
    public const ORDER_1C_STATUS_CONFIRMED = '5.Выдан';
    public const ORDER_1C_STATUS_WAIT = '1.Рассматривается';
    public const ORDER_1C_STATUS_REJECTED = '2.Отказано';
    public const ORDER_1C_STATUS_APPROVED = '3.Одобрено';
    public const ORDER_1C_STATUS_CLOSED = '6.Закрыт';
    public const ORDER_1C_STATUS_REJECTED_TECH = '7.Технический отказ';
    public const ORDER_1C_STATUS_UNDEFINED = 'Не определено';
    public const ORDER_1C_STATUS_NEW = 'Новая';

    /**
     * Статус для обмена с 1С 
     */
    public const ORDER_1C_STATUS_REJECTED_FOR_SEND = 'Отказано';

    /**
     * Статус CRM заявки при отказе, тех-отказе
     */
    public const ORDER_STATUS_CRM_REJECT = 3;

    /**
     * Статус когда Выдан займ, используется при Б2П
     */
    public const ORDER_STATUS_CRM_ISSUED = 10;

    /**
     * Статус когда не удалось выдать заём
     */
    public const ORDER_STATUS_CRM_NOT_ISSUED = 11;

    /**
     * Статус новой заявки
     */
    public const ORDER_STATUS_CRM_NEW = 1;

    /**
     * Статус CRM на исправлении
     */
    public const ORDER_STATUS_CRM_CORRECTION = 5;

    /**
     * Статус CRM исправлена
     */
    public const ORDER_STATUS_CRM_CORRECTED = 6;

    /**
     * Статус CRM ожидание
     */
    public const ORDER_STATUS_CRM_WAITING = 7;

    /**
     * Статус одобренной заявки
     */
    public const ORDER_STATUS_CRM_APPROVED = 2;
    
    /**
     * Статус заявки "Рассматривается" в 1С, используется для обновления
     */
    public const ORDER_UPDATE_1C_STATUS_CONSIDERED = 'Рассматривается';
    
    /**
     * Utm метка автоодобрения
     */
    public const UTM_RESOURCE_AUTO_APPROVE = 'crm_auto_approve';
    
    // максимальное количество попыток ввода АСП
    public const MAX_ACCEPT_TRY = 5;
    /**
     * Период для акции НК
     */
    public const MAX_PERIOD = 5;

    public const MAX_PERIOD_FIRST_LOAN = 16;
    public const MAX_AMOUNT_FIRST_LOAN = 16;
    public const IN_PROGRESS_STATUSES = [
        'Новая',
        'Не определено',
        '1.Рассматривается',
        '3.Одобрено',
        '4.Готов к выдаче',
    ];

    /**
     * Заявки ООО и ИП
     */
    public const UTM_SOURCE_COMPANY_FORM = 'company_form';

    /** @var string Выплата на карту (b2p_cards) */
    public const CARD_TYPE_CARD = 'card';

    /** @var string Выплата на СБП счет (b2p_sbp_accounts) */
    public const CARD_TYPE_SBP = 'sbp';
	public const CARD_TYPE_VIRT = 'virt';

    // определяем на какое ип страховка
    public function get_insure_ip()
    {
        foreach ($this->settings->insurance_threshold_settings as $ip => $summ)
        {
            if (!empty($result_ip)) {
                continue;
            }

            if ($summ > 0)
            {
                $total_strah_summ = $this->soap->get_strah_summ($ip);
                if ($total_strah_summ < $summ)
                    return $ip;
            }
        }

        return 'Boostra';
    }

	//TODO определяем на какое ип страховка
	public function get_insure_ip_by_crm()
	{
        return 'Boostra';
	}

    public function get_strah_summ($insurer) {
        $this->db->query("
            SELECT SUM(amount) as total_strah_summ FROM `tinkoff_insures` WHERE DATE(date) = CURDATE() AND insurer = ? LIMIT 1
        ", $insurer); 	
        return $this->db->result();
        //return '230.60';
    }

	public function get_crm_order($id)
	{
		$query = $this->db->placehold("
            SELECT 
                o.id AS order_id,
                o.manager_id,
                o.status,
                o.date,
                o.accept_date,
                o.accept_sms,
                o.accept_try,
                o.call_date,
                o.confirm_date,
                o.approve_date,
                o.reject_date,
                o.credit_getted,
                o.user_id,
                o.ip,
                o.amount,
                o.period,
                o.percent,
                o.first_loan,
                o.1c_id AS id_1c,
                o.1c_id,
                o.1c_status AS status_1c,
                o.utm_source,
                o.utm_medium,
                o.utm_campaign,
                o.utm_content,
                o.utm_term,
                o.scorista_ball,
                o.webmaster_id,
                o.click_hash,
                o.local_time,
                o.juicescore_session_id,
                o.have_close_credits,
                o.card_id,
                o.card_type,
                o.reason_id,
                o.pay_result,
                o.razgon,
                o.max_amount,
                o.min_period,
                o.max_period,
                o.loan_type,
                o.payment_period,
                o.cdoctor_id,
                o.stage1,
                o.stage1_date,
                o.stage2,
                o.stage2_date,
                o.stage3,
                o.stage3_date,
                o.stage4,
                o.stage4_date,
                o.stage5,
                o.stage5_date,
                o.call_variants,
                o.b2p,
                o.organization_id,
                o.autoretry,
                o.insurer,
                o.insure_amount,
                o.insure_percent,
                o.is_credit_doctor,
                o.is_user_credit_doctor,
                o.selected_period,
                o.is_discount_way,
                o.is_default_way,
                o.order_uid,
                u.personal_data_added,
                u.additional_data_added,
                u.card_added,
                u.files_added,
                u.UID AS user_uid,
                u.maratorium_id, 
                u.maratorium_date,
                u.tinkoff_id,
                u.created,
                u.service_sms,
                u.service_insurance,
                u.service_reason,
                u.phone_mobile,
                u.email,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.gender,
                u.birth,
                u.birth_place,
                u.inn,
                u.passport_serial,
                u.subdivision_code,
                u.passport_date,
                u.passport_issued,
                u.Regindex,
                u.Regregion,
                u.Regregion_shorttype,
                u.Regdistrict,
                u.Regcity,
                u.Regcity_shorttype,
                u.Regstreet,
                u.Regstreet_shorttype,
                u.Reghousing,
                u.Regbuilding,
                u.Regroom,
                u.Faktindex,
                u.Faktregion,
                u.Faktregion_shorttype,
                u.Faktdistrict,
                u.Faktcity,
                u.Faktcity_shorttype,
                u.Faktstreet,
                u.Faktstreet_shorttype,
                u.Fakthousing,
                u.Faktbuilding,
                u.Faktroom,
                u.contact_person_name,
                u.contact_person_phone,
                u.contact_person_relation,
                u.contact_person2_name,
                u.contact_person2_phone,
                u.contact_person2_relation,
                u.contact_person3_name,
                u.contact_person3_phone,
                u.contact_person3_relation,
                u.workplace,
                u.profession,
                u.work_scope,
                u.work_phone,
                u.has_estate,
                u.identified_phone,
                u.workdirector_name,
                u.work_address,
                u.Workindex,
                u.Workregion,
                u.Workcity,
                u.Workstreet,
                u.Workhousing,
                u.Workbuilding,
                u.Workroom,
                u.marital_status,
                u.income_base,
                u.social_inst,
                u.social_fb,
                u.social_vk,
                u.social_ok,
                u.loan_history,
                u.choose_insure,
                u.cdoctor_level,
                crm_response
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE o.id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
        {
            $this->addAdditionalServicesFields($result, $result->order_id);
            $result->loan_history = empty($result->loan_history) ? array() : json_decode($result->loan_history);
        }

        return $result;
    }
	
    public function get_order_by_1c($loan_number)
    {
        $query = $this->db->placehold("
            SELECT * FROM __orders WHERE 1c_id = ? ORDER BY id DESC
        ", $loan_number);
        $this->db->query($query);

        $result = $this->db->result();

        if ($result && isset($result->id)) {
            $this->addAdditionalServicesFields($result, $result->id);
        } else {
            return null;
        }

        return $result;
    }
 
    
    public function get_order($id)
	{
        $where = $this->db->placehold(' WHERE o.id=? ', intval($id));
		
		$query = $this->db->placehold("SELECT  
										o.id, 
										o.contract_id,
										o.is_user_credit_doctor,
                                        o.accept_sms,
                                        o.accept_date,
                                        o.accept_try,
                                        o.card_id,
                                        o.card_type,
										o.payment_method_id, 
										o.paid, 
										o.payment_date, 
										o.closed, 
										o.discount, 
										o.coupon_code, 
										o.coupon_discount,
										o.date, 
										o.user_id, 
										o.name, 
										o.scorista_ball, 
										o.address, 
										o.phone, 
										o.email, 
										o.comment, 
										o.status,
										o.url, 
										o.note, 
										o.ip,
										o.manager_id,
                                        o.amount,
										o.approve_amount,
                                        o.approve_date,
										o.period,
                                        o.percent,
                                        o.first_loan,
										o.sent_1c,
                                        o.official_response,
                                        o.reason_id,
										o.sms,
                                        o.razgon,
                                        o.max_amount,
                                        o.min_period,
                                        o.max_period,
                                        o.loan_type,
                                        o.payment_period,
										o.utm_source,
										o.utm_medium,
										o.utm_campaign,
										o.utm_content,
										o.utm_term,
										o.webmaster_id,
										o.click_hash,
										o.1c_id as id_1c,
										o.1c_status as status_1c,
                                        o.b2p,
                                        o.organization_id,
                                        o.insurer,
                                        o.insure_amount,
                                        o.insure_percent,
                                        o.order_uid,
                                        o.is_credit_doctor,
                                        o.promocode,
                                        o.confirm_date,
                                        o.number_of_signing_errors,
                                        o.have_close_credits
										FROM __orders o $where LIMIT 1");

        $this->db->query($query);
        if ($result = $this->db->result()) {
            $result->{'1c_id'} = $result->id_1c;
            $this->addAdditionalServicesFields($result, $result->id);
            return $result;
        }

        return false;
	}

    public function get_order_by_params(array $params)
    {

        if (empty($params)) return false;

        $filter = [];
        foreach ($params as $param_key => $param_value) {
            $filter[] = $this->db->placehold('`'.$param_key.'` = ?', $param_value);
        }

        $where = implode(' AND ', $filter);

        $query = $this->db->placehold("SELECT  
										o.id, 
										o.contract_id,
										o.is_user_credit_doctor,
                                        o.accept_sms,
                                        o.accept_date,
                                        o.accept_try,
                                        o.card_id,
										o.payment_method_id, 
										o.paid, 
										o.payment_date, 
										o.closed, 
										o.discount, 
										o.coupon_code, 
										o.coupon_discount,
										o.date, 
										o.user_id, 
										o.name, 
										o.address, 
										o.phone, 
										o.email, 
										o.comment, 
										o.status,
										o.url, 
										o.note, 
										o.ip,
										o.manager_id,
                                        o.amount,
										o.approve_amount,
										o.period,
                                        o.percent,
                                        o.first_loan,
										o.sent_1c,
                                        o.official_response,
                                        o.reason_id,
										o.sms,
                                        o.razgon,
                                        o.max_amount,
                                        o.min_period,
                                        o.max_period,
                                        o.loan_type,
                                        o.payment_period,
										o.utm_source,
										o.utm_medium,
										o.utm_campaign,
										o.utm_content,
										o.utm_term,
										o.webmaster_id,
										o.click_hash,
										o.1c_id as id_1c,
										o.1c_status as status_1c,
                                        o.b2p,
                                        o.organization_id,
                                        o.insurer,
                                        o.insure_amount,
                                        o.insure_percent,
                                        o.order_uid,
                                        o.is_credit_doctor,
                                        o.promocode,
                                        o.confirm_date,
                                        o.number_of_signing_errors,
                                        o.have_close_credits
										FROM __orders o $where LIMIT 1");

        $this->db->query($query);
        if ($result = $this->db->result()) {
            $this->addAdditionalServicesFields($result, $result->id);
            return $result;
        }

        return false;
    }

    public function get_order_by_params_and_order_data(array $params, array $order_data)
    {

        if (empty($params) || empty($order_data)) return false;

        $filter = [];
        foreach ($params as $param_key => $param_value) {
            $filter[] = $this->db->placehold('`o`.`'.$param_key.'` = ?', $param_value);
        }

        $where = implode(' AND ', $filter);

        $orderDataFilter = [];
        foreach ($order_data as $data) {
            foreach ($data as $key => $value) {
                $orderDataFilter[] = $this->db->placehold('`od`.`'.$key.'` = ?', $value);
            }
        }
        $whereOrderData = $orderDataFilter ? 'AND ' . implode(' AND ', $orderDataFilter) : '';

        $query = $this->db->placehold("SELECT  
										o.id, 
										o.contract_id,
										o.is_user_credit_doctor,
                                        o.accept_sms,
                                        o.accept_date,
                                        o.accept_try,
                                        o.card_id,
										o.payment_method_id, 
										o.paid, 
										o.payment_date, 
										o.closed, 
										o.discount, 
										o.coupon_code, 
										o.coupon_discount,
										o.date, 
										o.user_id, 
										o.name, 
										o.address, 
										o.phone, 
										o.email, 
										o.comment, 
										o.status,
										o.url, 
										o.note, 
										o.ip,
										o.manager_id,
                                        o.amount,
										o.approve_amount,
										o.period,
                                        o.percent,
                                        o.first_loan,
										o.sent_1c,
                                        o.official_response,
                                        o.reason_id,
										o.sms,
                                        o.razgon,
                                        o.max_amount,
                                        o.min_period,
                                        o.max_period,
                                        o.loan_type,
                                        o.payment_period,
										o.utm_source,
										o.utm_medium,
										o.utm_campaign,
										o.utm_content,
										o.utm_term,
										o.webmaster_id,
										o.click_hash,
										o.1c_id as id_1c,
										o.1c_status as status_1c,
                                        o.b2p,
                                        o.organization_id,
                                        o.insurer,
                                        o.insure_amount,
                                        o.insure_percent,
                                        o.order_uid,
                                        o.is_credit_doctor,
                                        o.promocode,
                                        o.confirm_date,
                                        o.number_of_signing_errors,
                                        o.have_close_credits
										FROM __orders o 
                                        JOIN __order_data od ON od.order_id = o.id
                                        WHERE $where $whereOrderData ORDER BY o.id DESC LIMIT 1");

        $this->db->query($query);
        if ($result = $this->db->result()) {
            $this->addAdditionalServicesFields($result, $result->id);
            $result->refinance = 1;
            return $result;
        }

        return false;
    }

    public function check_sms($order_id, $code)
    {
        $query = $this->db->placehold("SELECT COUNT(*) as count FROM __orders o where o.id = ? AND o.accept_sms = ?", $order_id, $code);
        $this->db->query($query);

        return (bool) $this->db->result('count');
    }
	
    public function get_cross_orders($base_order_id)
    {
        $this->db->query("
            SELECT *
            FROM s_orders
            WHERE utm_source = ?
            AND utm_medium = ?
        ", self::UTM_SOURCE_CROSS_ORDER, (string)$base_order_id);
        return $this->db->results();
    }
    
	function get_orders($filter = array())
	{
		// По умолчанию
		$limit = 100;
		$page = 1;
		$keyword_filter = '';	
		$label_filter = '';	
		$status_filter = '';
        $user_filter = '';
        $loan_filter = '';	
		$modified_since_filter = '';
        $modified_to_filter = '';
		$id_filter = '';
        $sort = 'status, id';
		
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));

		$sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);
		
			
		if(isset($filter['status']))
			$status_filter = $this->db->placehold('AND o.status = ?', intval($filter['status']));
			
		if(isset($filter['status_1c'])) {
			$status_filter = $this->db->placehold('AND o.1c_status = ?', $filter['status_1c']);
        }
		
		if(isset($filter['id']))
			$id_filter = $this->db->placehold('AND o.id in(?@)', (array)$filter['id']);
		
		if(isset($filter['user_id']))
			$user_filter = $this->db->placehold('AND o.user_id = ?', intval($filter['user_id']));

        if (isset($filter['loan_type'])) {
            $loan_filter = $this->db->placehold('AND o.loan_type = ?', (string)($filter['loan_type']));
        }
		
		if(isset($filter['modified_since']))
			$modified_since_filter = $this->db->placehold('AND o.modified > ?', $filter['modified_since']);

        if(isset($filter['modified_to']))
            $modified_to_filter = $this->db->placehold('AND o.modified <= ?', $filter['modified_to']);
		
		if(isset($filter['label']))
			$label_filter = $this->db->placehold('AND ol.label_id = ?', $filter['label']);
		
		if(!empty($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (o.id = "'.$this->db->escape(trim($keyword)).'" OR o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" OR REPLACE(o.phone, "-", "")  LIKE "%'.$this->db->escape(str_replace('-', '', trim($keyword))).'%" OR o.address LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
		
		// Выбираем заказы
		$query = $this->db->placehold("
            SELECT 
				o.id, 
                o.accept_sms,
                o.accept_try,
                o.card_id,
                o.card_type,
				o.closed, 
				o.date, 
				o.user_id, 
				o.name, 
				o.address, 
				o.phone, 
				o.email, 
				o.comment, 
				o.status,
				o.url, 
				o.note,
				o.ip,
				o.amount,
				o.period,
                o.percent,
                o.first_loan,
				o.sent_1c,
                o.confirm_date,
                o.official_response,
                o.reason_id,
				o.sms,
                o.razgon,
                o.max_amount,
                o.min_period,
                o.max_period,
                o.loan_type,
                o.payment_period,
				o.utm_source,
				o.utm_medium,
				o.utm_campaign,
				o.utm_content,
				o.utm_term,
				o.webmaster_id,
				o.click_hash,
				o.1c_id as id_1c,
				o.1c_status as status_1c,
                o.b2p,
                o.organization_id,                
                o.insurer,
                o.insure_amount,
                o.insure_percent,
                o.order_uid,
                o.accept_date,
                o.approve_amount,
                o.have_close_credits
			FROM __orders AS o 
			WHERE 1
                $id_filter 
                $status_filter 
                $user_filter 
			    $loan_filter
                $keyword_filter 
                $label_filter 
                $modified_since_filter 
                $modified_to_filter 
            ORDER BY $sort
            DESC $sql_limit
        ", "%Y-%m-%d");

		$this->db->query($query);
		$orders = array();
		foreach($this->db->results() as $order)
			$orders[$order->id] = $order;

		return $orders;
	}

	function count_orders($filter = array())
	{
		$keyword_filter = '';	
		$label_filter = '';	
		$status_filter = '';
		$user_filter = '';	
		
		if(isset($filter['status']))
			$status_filter = $this->db->placehold('AND o.status = ?', intval($filter['status']));
		
		if(isset($filter['user_id']))
			$user_filter = $this->db->placehold('AND o.user_id = ?', intval($filter['user_id']));

		if(isset($filter['label']))
			$label_filter = $this->db->placehold('AND ol.label_id = ?', $filter['label']);
		
		if(!empty($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" OR REPLACE(o.phone, "-", "")  LIKE "%'.$this->db->escape(str_replace('-', '', trim($keyword))).'%" OR o.address LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
		
		// Выбираем заказы
		$query = $this->db->placehold("SELECT COUNT(DISTINCT id) as count
									FROM __orders AS o 
									LEFT JOIN __orders_labels AS ol ON o.id=ol.order_id 
									WHERE 1
									$status_filter $user_filter $label_filter $keyword_filter");
		$this->db->query($query);
		return $this->db->result('count');
	}

    public function update_order($id, $order)
    {
        if (empty($id)) {
            return false;
        }
    
        // добавим отправку постбека если статус изменился на "Отказ"
        $order_old = $this->get_order((int)$id);
    
        // Логируем только если изменилось поле amount
        if (
            isset($order['amount']) &&
            (int)$order_old->amount !== (int)$order['amount']
        ) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $caller_trace = [];
    
            foreach ($bt as $trace) {
                if (!empty($trace['file']) && !empty($trace['line'])) {
                    $caller_trace[] = "{$trace['file']}:{$trace['line']}" .
                        (isset($trace['function']) ? " in {$trace['function']}()" : '');
                }
            }
    
            $this->logging(
                __METHOD__,
                'amount changed',
                [
                    'order_id'   => $id,
                    'old_amount' => $order_old->amount,
                    'new_amount' => $order['amount'],
                    'caller'     => $caller_trace,
                ],
                'Изменение суммы заявки',
                'update_order_amount.txt'
            );
        }
    
        $old_status = (int)$order_old->status;
        if ($old_status !== 3 && ((int)($order['status'] ?? 0)) === 3) {
            $this->post_back->sendReject($order_old);
        }
    
        if (
            (!empty($order['status']) && $order_old->status != $order['status']) ||
            (!empty($order['1c_status']) && $order_old->status_1c != $order['1c_status'])
        ) {
            $this->addStatusLog((int)$id, (array)$order, (array)$order_old);
    
            // При продаже карты отказного НК не отправляем никакие смс и не делаем переход на отказные витрины
            $is_bonon = (bool)$this->order_data->read($id, 'is_sold_to_bonon');
            // Самоотказ, клиенту бесполезно предлагать кредиты
            $is_self_dec = $order_old->reason_id == $this->reasons::REASON_SELF_DEC;
            if (!($is_bonon || $is_self_dec) && is_object($order_old))
                $this->push_token->addTasks($order_old, isset($order['1c_status']) ? $order['1c_status'] : null);
        }
    
        $query = $this->db->placehold("UPDATE __orders SET ?%, modified=now() WHERE id=? LIMIT 1", $order, intval($id));
        $this->db->query($query);
        $this->update_total_price(intval($id));
        $this->onOrderUpdated($id, (array)$order);
        return $id;
    }    

    private function onOrderUpdated($id, $order)
    {
        if(!empty($order['reject_date'])) {
            $query = $this->db->placehold("INSERT INTO __external_api_queue SET ?%", ['order_id' => $id, 'api' => 'bonon_short_api']);
            $this->db->query($query);
        }

    }
    
	public function delete_order($id)
	{
		if(!empty($id))
		{
			$query = $this->db->placehold("DELETE FROM __purchases WHERE order_id=?", $id);
			$this->db->query($query);

			$query = $this->db->placehold("DELETE FROM __orders_labels WHERE order_id=?", $id);
			$this->db->query($query);
 			
			$query = $this->db->placehold("DELETE FROM __orders WHERE id=? LIMIT 1", $id);
			$this->db->query($query);
		}
	}
	
	public function add_order($order)
	{
		$order = (object)$order;
        
        if (empty($order->order_uid))
            $order->order_uid = exec($this->config->root_dir.'generic/uidgen');

		$order->url = md5(uniqid($this->config->salt, true));
		$set_curr_date = '';
		if(empty($order->date))
			$set_curr_date = ', date=now()';

		$query = $this->db->placehold("INSERT INTO __orders SET ?%$set_curr_date", $order);
		$this->db->query($query);

		$id = $this->db->insert_id();

        if ($id && !empty($_SESSION['vid'])) {
            $this->order_data->set($id, 'vid', $_SESSION['vid']);
        }

        try {
            if ($order_price = $this->leadPrice->getPriceForOrder($order))
                $this->leadPriceLogs->add([
                    'order_id' => $id,
                    'utm_source' => $order->utm_source,
                    'webmaster_id' => $order->webmaster_id,
                    'price' => $order_price,
                ]);
        }
        catch (Exception $e) {
            $this->logging(__METHOD__, '', (array)$order, $e->getMessage(), 'leadPriceLogs.txt');
        }

        $this->spr_versions->markOrderVersion($id);

		return $id;
	}
    

    public function get_last_order($user_id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __orders
            WHERE user_id = ?
            AND DATE(date) > '2020-01-01'
            AND is_credit_doctor = 0
            ORDER BY id DESC
            LIMIT 1
        ", (int)$user_id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получение последней заявки с определённым статусом (Например, последней закрытой заявки).
     * Можно передать несколько статусов в массиве.
     * @param int $user_id
     * @param array|int $status
     */
    public function get_last_order_by_status($user_id, $status)
    {
        if (!is_array($status))
            $status = [$status];

        $query = $this->db->placehold("
            SELECT *
            FROM __orders
            WHERE user_id = ? AND status IN (?@)
            AND DATE(date) > '2020-01-01'
            AND is_credit_doctor = 0
            ORDER BY id DESC
            LIMIT 1
        ", (int)$user_id, $status);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Получение предыдущей заявки.
     * @param int $user_id
     * @param int $offset
     */
    public function get_previous_order($user_id, $offset = 1)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __orders
            WHERE user_id = ?
            AND DATE(date) > '2020-01-01'
            AND is_credit_doctor = 0
            ORDER BY id DESC
            LIMIT 1 OFFSET ?
        ", (int)$user_id, $offset);
        $this->db->query($query);

        return $this->db->result();
    }
    
	public function check_order_1c($id_1c)
	{

		$log = false;
        $inn_arr =  $this->organizations->get_site_inns();
        if (count($inn_arr) < 1){
            return false;
        }

		$z = new stdClass();
		$z->НомерЗаявки = $id_1c;
		$z->Partner = 'Boostra';
        $z->ArrayINN = json_encode($inn_arr, false);

        $this->setLoggerState(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl GetOtvetZayavkiINN', (array)$z, 'statuses.txt');
        
        $stat_z_client = new SoapClient($this->config->url_1c . $this->config->work_1c_db . "/ws/WebOtvetZayavki.1cws?wsdl");
        $returnnnn = $stat_z_client->__soapCall('GetOtvetZayavkiINN',array($z));
		
        if (!empty($log))
            $this->logging(__METHOD__, $this->config->url_1c . $this->config->work_1c_db . '/ws/WebOtvetZayavki.1cws?wsdl GetOtvetZayavki', (array)$z, (array)$returnnnn, 'statuses.txt');

        return $returnnnn;
	}    
    
    public function update_1c_status($last_order, $resp = null)
    {
        $last_order = (array)$last_order;
        if (empty($last_order['1c_id']))
            $last_order['1c_id'] = $last_order['id_1c'];

        if (!empty($last_order) && !empty($last_order['1c_id']))
        {
            if ($last_order['status'] != 3)
            {
                if(!$resp) {
                    $resp = $this->check_order_1c($last_order['1c_id']);
                }
                
          		$stat = $resp->return->Статус;
            	$comment = $resp->return->Комментарий;
                $official_response = $resp->return->ОфициальныйОтвет;
//            echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($stat);echo '</pre><hr />';
            	switch ($stat):
                    
                    case 'Новая':
                    case '1.Рассматривается':
                		$update = array(
                            '1c_status' => $stat,
                            'comment' => $comment,
                        );
                        $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                        $last_order['comment'] = $comment;                    
                    break;
                    
                    case '2.Отказано':
                    case '7.Технический отказ':
//                    case 'Не определено':
                		$update = array(
                            '1c_status' => $stat,
                            'comment' => $comment,
                            'official_response' => $official_response,
                        );
                        $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                        $last_order['comment'] = $comment;                    
                    break;
                    
                    case '3.Одобрено':
                    case '4.Готов к выдаче':
                		$update = array(
                            '1c_status' => $stat,
                            'comment' => $comment,
                        );                
                        $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                        $last_order['comment'] = $comment;                    
                    break;
                    
                    case '5.Выдан':
                		$update = array(
                            '1c_status' => $stat,
                            'comment' => $comment,
                        );            
                        $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                        $last_order['comment'] = $comment;                    
                    break;
                    
                    case '6.Закрыт':            
                		$update = array(
                            '1c_status' => $stat,
                            'comment' => $comment,
                        );
                        $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                        $last_order['comment'] = $comment;                    
                    break;
                    
                endswitch;
                
                if (!empty($update))
                    $this->update_order($last_order['id'], $update);
                
            }
                
        }
        return $last_order;
    }
    
    

	public function get_label($id)
	{
		$query = $this->db->placehold("SELECT * FROM __labels WHERE id=? LIMIT 1", intval($id));
		$this->db->query($query);
		return $this->db->result();
	}

	public function get_labels()
	{
		$query = $this->db->placehold("SELECT * FROM __labels ORDER BY position");
		$this->db->query($query);
		return $this->db->results();
	}

	/*
	*
	* Создание метки заказов
	* @param $label
	*
	*/	
	public function add_label($label)
	{	
		$query = $this->db->placehold('INSERT INTO __labels SET ?%', $label);
		if(!$this->db->query($query))
			return false;

		$id = $this->db->insert_id();
		$this->db->query("UPDATE __labels SET position=id WHERE id=?", $id);	
		return $id;
	}
	
	
	/*
	*
	* Обновить метку
	* @param $id, $label
	*
	*/	
	public function update_label($id, $label)
	{
		$query = $this->db->placehold("UPDATE __labels SET ?% WHERE id in(?@) LIMIT ?", $label, (array)$id, count((array)$id));
		$this->db->query($query);
		return $id;
	}

	public function update_orders_waiting_virtual_cards_by_user($user_id)
	{
		if (empty($user_id)) {
			return false;
		}

		$query = $this->db->placehold(
			"UPDATE __orders
                SET status = ?
            WHERE user_id = ?
            AND status = ?
            AND card_type = ?",
			self::STATUS_SIGNED,
			$user_id,
			self::STATUS_WAIT_VIRTUAL_CARD,
			self::CARD_TYPE_VIRT
		);

		$this->db->query($query);

		return $user_id;
	}

	/*
	*
	* Удалить метку
	* @param $id
	*
	*/	
	public function delete_label($id)
	{
		if(!empty($id))
		{
			$query = $this->db->placehold("DELETE FROM __orders_labels WHERE label_id=?", intval($id));
			if($this->db->query($query))
			{
				$query = $this->db->placehold("DELETE FROM __labels WHERE id=? LIMIT 1", intval($id));
				return $this->db->query($query);
			}
			else
			{
				return false;
			}
		}
	}	
	
	function get_order_labels($order_id = array())
	{
		if(empty($order_id))
			return array();

		$label_id_filter = $this->db->placehold('AND order_id in(?@)', (array)$order_id);
				
		$query = $this->db->placehold("SELECT ol.order_id, l.id, l.name, l.color, l.position
					FROM __labels l LEFT JOIN __orders_labels ol ON ol.label_id = l.id
					WHERE 
					1
					$label_id_filter   
					ORDER BY position       
					");
		
		$this->db->query($query);
		return $this->db->results();
	}
	
	public function update_order_labels($id, $labels_ids)
	{
		$labels_ids = (array)$labels_ids;
		$query = $this->db->placehold("DELETE FROM __orders_labels WHERE order_id=?", intval($id));
		$this->db->query($query);
		if(is_array($labels_ids))
		foreach($labels_ids as $l_id)
			$this->db->query("INSERT INTO __orders_labels SET order_id=?, label_id=?", $id, $l_id);
	}

	public function add_order_labels($id, $labels_ids)
	{
		$labels_ids = (array)$labels_ids;
		if(is_array($labels_ids))
		foreach($labels_ids as $l_id)
		{
			$this->db->query("INSERT IGNORE INTO __orders_labels SET order_id=?, label_id=?", $id, $l_id);
		}
	}

	public function delete_order_labels($id, $labels_ids)
	{
		$labels_ids = (array)$labels_ids;
		if(is_array($labels_ids))
		foreach($labels_ids as $l_id)
			$this->db->query("DELETE FROM __orders_labels WHERE order_id=? AND label_id=?", $id, $l_id);
	}


	public function get_purchase($id)
	{
		$query = $this->db->placehold("SELECT * FROM __purchases WHERE id=? LIMIT 1", intval($id));
		$this->db->query($query);
		return $this->db->result();
	}

	public function get_purchases($filter = array())
	{
		$order_id_filter = '';
		if(!empty($filter['order_id']))
			$order_id_filter = $this->db->placehold('AND order_id in(?@)', (array)$filter['order_id']);

		$query = $this->db->placehold("SELECT * FROM __purchases WHERE 1 $order_id_filter ORDER BY id");
		$this->db->query($query);
		return $this->db->results();
	}
	
	public function update_purchase($id, $purchase)
	{	
		$purchase = (object)$purchase;
		$old_purchase = $this->get_purchase($id);
		if(!$old_purchase)
			return false;
			
		$order = $this->get_order(intval($old_purchase->order_id));
		if(!$order)
			return false;
		
		// Не допустить нехватки на складе
		$variant = $this->variants->get_variant($purchase->variant_id);
		if($order->closed && !empty($purchase->amount) && !empty($variant) && !$variant->infinity && $variant->stock<($purchase->amount-$old_purchase->amount))
			return false;
		
		// Если заказ закрыт, нужно обновить склад при изменении покупки
		if($order->closed && !empty($purchase->amount))
		{
			if($old_purchase->variant_id != $purchase->variant_id)
			{
				if(!empty($old_purchase->variant_id))
				{
					$query = $this->db->placehold("UPDATE __variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount, $old_purchase->variant_id);
					$this->db->query($query);
				}
				if(!empty($purchase->variant_id))
				{
					$query = $this->db->placehold("UPDATE __variants SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $purchase->amount, $purchase->variant_id);
					$this->db->query($query);
				}
			}
			elseif(!empty($purchase->variant_id))
			{
				$query = $this->db->placehold("UPDATE __variants SET stock=stock+(?) WHERE id=? AND stock IS NOT NULL LIMIT 1", $old_purchase->amount - $purchase->amount, $purchase->variant_id);
				$this->db->query($query);
			}
		}
		
		$query = $this->db->placehold("UPDATE __purchases SET ?% WHERE id=? LIMIT 1", $purchase, intval($id));
		$this->db->query($query);
		$this->update_total_price($order->id);		
		return $id;
	}
	
	public function add_purchase($purchase)
	{
		$purchase = (object)$purchase;
		if(!empty($purchase->variant_id))
		{
			$variant = $this->variants->get_variant($purchase->variant_id);
			if(empty($variant))
				return false;
			$product = $this->products->get_product(intval($variant->product_id));
			if(empty($product))
				return false;
		}			

		$order = $this->get_order(intval($purchase->order_id));
		if(empty($order))
			return false;				
	
		// Не допустить нехватки на складе
		if($order->closed && !empty($purchase->amount) && !$variant->infinity && $variant->stock<$purchase->amount)
			return false;
		
		if(!isset($purchase->product_id) && isset($variant))
			$purchase->product_id = $variant->product_id;
				
		if(!isset($purchase->product_name)  && !empty($product))
			$purchase->product_name = $product->name;
			
		if(!isset($purchase->sku) && !empty($variant))
			$purchase->sku = $variant->sku;
			
		if(!isset($purchase->variant_name) && !empty($variant))
			$purchase->variant_name = $variant->name;
			
		if(!isset($purchase->price) && !empty($variant))
			$purchase->price = $variant->price;
			
		if(!isset($purchase->amount))
			$purchase->amount = 1;

		// Если заказ закрыт, нужно обновить склад при добавлении покупки
		if($order->closed && !empty($purchase->amount) && !empty($variant->id))
		{
			$stock_diff = $purchase->amount;
			$query = $this->db->placehold("UPDATE __variants SET stock=stock-? WHERE id=? AND stock IS NOT NULL LIMIT 1", $stock_diff, $variant->id);
			$this->db->query($query);
		}

		$query = $this->db->placehold("INSERT INTO __purchases SET ?%", $purchase);
		$this->db->query($query);
		$purchase_id = $this->db->insert_id();
		
		$this->update_total_price($order->id);		
		return $purchase_id;
	}

	public function delete_purchase($id)
	{
		$purchase = $this->get_purchase($id);
		if(!$purchase)
			return false;
			
		$order = $this->get_order(intval($purchase->order_id));
		if(!$order)
			return false;

		// Если заказ закрыт, нужно обновить склад при изменении покупки
		if($order->closed && !empty($purchase->amount))
		{
			$stock_diff = $purchase->amount;
			$query = $this->db->placehold("UPDATE __variants SET stock=stock+? WHERE id=? AND stock IS NOT NULL LIMIT 1", $stock_diff, $purchase->variant_id);
			$this->db->query($query);
		}
		
		$query = $this->db->placehold("DELETE FROM __purchases WHERE id=? LIMIT 1", intval($id));
		$this->db->query($query);
		$this->update_total_price($order->id);				
		return true;
	}

	
	public function close($order_id)
	{
		$order = $this->get_order(intval($order_id));
		if(empty($order))
			return false;
		
		if(!$order->closed)
		{
			$variants_amounts = array();
			$purchases = $this->get_purchases(array('order_id'=>$order->id));
			foreach($purchases as $purchase)
			{
				if(isset($variants_amounts[$purchase->variant_id]))
					$variants_amounts[$purchase->variant_id] += $purchase->amount;
				else
					$variants_amounts[$purchase->variant_id] = $purchase->amount;
			}

			foreach($variants_amounts as $id=>$amount)
			{
				$variant = $this->variants->get_variant($id);
				if(empty($variant) || ($variant->stock<$amount))
					return false;
			}
			foreach($purchases as $purchase)
			{	
				$variant = $this->variants->get_variant($purchase->variant_id);
				if(!$variant->infinity)
				{
					$new_stock = $variant->stock-$purchase->amount;
					$this->variants->update_variant($variant->id, array('stock'=>$new_stock));
				}
			}				
			$query = $this->db->placehold("UPDATE __orders SET closed=1, modified=NOW() WHERE id=? LIMIT 1", $order->id);
			$this->db->query($query);
		}
		return $order->id;
	}

	public function open($order_id)
	{
		$order = $this->get_order(intval($order_id));
		if(empty($order))
			return false;
		
		if($order->closed)
		{
			$purchases = $this->get_purchases(array('order_id'=>$order->id));
			foreach($purchases as $purchase)
			{
				$variant = $this->variants->get_variant($purchase->variant_id);				
				if($variant && !$variant->infinity)
				{
					$new_stock = $variant->stock+$purchase->amount;
					$this->variants->update_variant($variant->id, array('stock'=>$new_stock));
				}
			}				
			$query = $this->db->placehold("UPDATE __orders SET closed=0, modified=NOW() WHERE id=? LIMIT 1", $order->id);
			$this->db->query($query);
		}
		return $order->id;
	}
	
	public function pay($order_id)
	{
		$order = $this->get_order(intval($order_id));
		if(empty($order))
			return false;
		
		if(!$this->close($order->id))
		{
			return false;
		}
		$query = $this->db->placehold("UPDATE __orders SET payment_status=1, payment_date=NOW(), modified=NOW() WHERE id=? LIMIT 1", $order->id);
		$this->db->query($query);
		return $order->id;
	}
	
	private function update_total_price($order_id)
	{
//		$order = $this->get_order(intval($order_id));
//		if(empty($order))
			return false;
		
		$query = $this->db->placehold("UPDATE __orders o SET o.total_price=IFNULL((SELECT SUM(p.price*p.amount)*(100-o.discount)/100 FROM __purchases p WHERE p.order_id=o.id), 0)+o.delivery_price*(1-o.separate_delivery)-o.coupon_discount, modified=NOW() WHERE o.id=? LIMIT 1", $order->id);
		$this->db->query($query);
		return $order->id;
	}
	

	public function get_next_order($id, $status = null)
	{
		$f = '';
		if($status!==null)
			$f = $this->db->placehold('AND status=?', $status);
		$this->db->query("SELECT MIN(id) as id FROM __orders WHERE id>? $f LIMIT 1", $id);
		$next_id = $this->db->result('id');
		if($next_id)
			return $this->get_order(intval($next_id));
		else
			return false; 
	}
	
	public function get_prev_order($id, $status = null)
	{
		$f = '';
		if($status !== null)
			$f = $this->db->placehold('AND status=?', $status);
		$this->db->query("SELECT MAX(id) as id FROM __orders WHERE id<? $f LIMIT 1", $id);
		$prev_id = $this->db->result('id');
		if($prev_id)
			return $this->get_order(intval($prev_id));
		else
			return false; 
	}

	public function get_crm_orders($filter = array())
	{
		$id_filter = '';
		$manager_id_filter = '';
		$not_manager_id_filter = '';
		$user_id_filter = '';
        $b2p_filter = '';
        $current_filter = '';
        $status_filter = '';
        $notbusy_filter = '';
        $inwork_filter = '';
        $notreceived_filter = '';
        $approve_filter = '';
        $issued_filter = '';
        $stage_completed_filter = '';
        $not_status_1c_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $search_filter = '';
        $keyword_filter = '';
        $id_1c_filter = '';
        $dops_filter = '';
        $cdoctor_filter = '';
        $close_credits_filter = '';
        $credit_getted_filter = '';
        $autoretry_filter = '';
        $credit_doctor_filter = '';
        $limit = 10000;
		$page = 1;
        $sort = 'o.1c_status DESC';
        
        if (isset($filter['close_credits']))
            $close_credits_filter = $this->db->placehold("AND o.have_close_credits = ?", (int)$filter['close_credits']);
        
        if (isset($filter['credit_getted']))
            $credit_getted_filter = $this->db->placehold("AND o.credit_getted = ?", (int)$filter['credit_getted']);
        
        if (isset($filter['cdoctor']))
        {
            if (empty($filter['cdoctor']))
                $cdoctor_filter = $this->db->placehold("AND (o.cdoctor_id = 0 OR o.cdoctor_id IS NULL)");
            else
                $cdoctor_filter = $this->db->placehold("AND o.cdoctor_id > 0");
        }
        
        if (!empty($filter['dops']))
            $dops_filter = $this->db->placehold("AND (u.service_insurance = 1 OR (u.service_insurance = 0 AND o.have_close_credits = 1) OR o.is_credit_doctor = 1)");

        if (isset($filter['credit_doctor']) && $filter['credit_doctor'])
        {
            $credit_doctor_filter = $this->db->placehold("AND (o.is_credit_doctor = 1 OR o.is_credit_doctor = 0)");
        }
        else
        {
            $credit_doctor_filter = $this->db->placehold("AND o.is_credit_doctor = 0");
        }
                        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND o.id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['not_status_1c']))
            $not_status_1c_filter = $this->db->placehold("AND (o.1c_status NOT IN (?@) OR o.1c_status IS NULL)", (array)$filter['not_status_1c']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND o.status IN (?@)", (array)$filter['status']);
        
        if (!empty($filter['notreceived']))
            $notreceived_filter = $this->db->placehold("AND o.status = 2 AND o.confirm_date IS NULL AND o.1c_status != '5.Выдан' AND o.1c_status != '6.Закрыт'");        
        
        if (!empty($filter['approve']))
            $approve_filter = $this->db->placehold("AND o.status = 2 AND o.confirm_date IS NULL AND o.1c_status != '5.Выдан' AND o.1c_status != '6.Закрыт'");        
        
        if (!empty($filter['issued']))
            $issued_filter = $this->db->placehold("AND o.status = 2 AND (o.confirm_date IS NOT NULL OR o.1c_status = '5.Выдан')");        
        
        if (!empty($filter['notbusy']))
            $notbusy_filter = $this->db->placehold("AND (o.manager_id = 0 OR o.manager_id IS NULL) AND status = 1");
        
        if (!empty($filter['inwork']))
            $inwork_filter = $this->db->placehold("AND o.manager_id != 0 AND o.manager_id IS NOT NULL AND status = 1");
        
        if (!empty($filter['stage_completed']))
            $stage_completed_filter = $this->db->placehold("AND additional_data_added = 1");
        
        if (!empty($filter['id_1c']))
            $id_1c_filter = $this->db->placehold("AND o.1c_id IN (?@)", (array)$filter['id_1c']);
        
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND o.manager_id = ?", (int)$filter['manager_id']);
            
        if (!empty($filter['not_manager_id']))
            $not_manager_id_filter = $this->db->placehold("AND (o.manager_id NOT IN (?@) OR o.manager_id IS NULL)", array_map('intval', (array)$filter['not_manager_id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND o.user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['b2p']))
            $b2p_filter = $this->db->placehold("AND o.b2p IN (?@)", array_map('intval', (array)$filter['b2p']));
        
        if (!empty($filter['current']))
            $current_filter = $this->db->placehold("AND (o.manager_id = ? OR (o.manager_id IS NULL AND o.status = 1))", (int)$filter['current']);
		
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE(o.date) >= ?", $filter['date_from']);
            
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE(o.date) <= ?", $filter['date_to']);

        if (isset($filter['autoretry']))
        {
            if (empty($filter['autoretry']))
                $autoretry_filter = $this->db->placehold("AND (o.autoretry = ? OR o.autoretry IS NULL)", (int)$filter['autoretry']);
            else
                $autoretry_filter = $this->db->placehold("AND o.autoretry = ?", (int)$filter['autoretry']);
        }
        
        
        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['order_id']))
                $search_filter .= $this->db->placehold(' AND o.id = ?', (int)$filter['search']['order_id']);
            if (!empty($filter['search']['date']))
                $search_filter .= $this->db->placehold(' AND DATE(o.date) = ?', date('Y-m-d', strtotime($filter['search']['date'])));
            if (!empty($filter['search']['amount']))
                $search_filter .= $this->db->placehold(' AND o.amount = ?', (int)$filter['search']['amount']);
            if (!empty($filter['search']['period']))
                $search_filter .= $this->db->placehold(' AND o.period = ?', (int)$filter['search']['period']);
            if (!empty($filter['search']['fio']))
            {
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl)
                {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(u.lastname LIKE '%".$expl."%' OR u.firstname LIKE '%".$expl."%' OR u.patronymic LIKE '%".$expl."%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }
            if (!empty($filter['search']['birth']))
                $search_filter .= $this->db->placehold(' AND DATE(u.birth) = ?', date('Y-m-d', strtotime($filter['search']['birth'])));
            if (!empty($filter['search']['phone']))
                $search_filter .= $this->db->placehold(" AND u.phone_mobile LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'");
            if (!empty($filter['search']['region']))
                $search_filter .= $this->db->placehold(" AND u.Regregion LIKE '%".$this->db->escape($filter['search']['region'])."%'");
            if (!empty($filter['search']['manager_id']))
                $search_filter .= $this->db->placehold(" AND o.manager_id = ?", (int)$filter['search']['manager_id']);
            if (!empty($filter['search']['status']))
                $search_filter .= $this->db->placehold(" AND o.1c_status LIKE '%".$this->db->escape($filter['search']['status'])."%'");
            if (!empty($filter['search']['utm_source']))
                $search_filter .= $this->db->placehold(" AND o.utm_source = ?", (int)$filter['search']['utm_source']);
            if (!empty($filter['search']['utm']))
                $search_filter .= $this->db->placehold(" AND (o.utm_source LIKE '%".$this->db->escape($filter['search']['utm'])."%' OR o.webmaster_id LIKE '%".$this->db->escape($filter['search']['utm'])."%')");
            if (!empty($filter['search']['reason']))
                $search_filter .= $this->db->placehold(" AND o.reason_id = ?", (int)$filter['search']['reason']);
        }
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);
        
        if (!empty($filter['sort']))
        {
            switch ($filter['sort']):
                
                case 'order_id_asc':
                    $sort = 'order_id ASC';
                break;
                
                case 'order_id_desc':
                    $sort = '(status = 6) DESC, order_id DESC';
                break;
                
                case 'date_asc':
                    $sort = 'o.date ASC';
                break;
                
                case 'date_desc':
                    $sort = 'o.date DESC';
                break;
                
                case 'amount_desc':
                    $sort = 'o.amount DESC';
                break;
                
                case 'amount_asc':
                    $sort = 'o.amount ASC';
                break;
                
                case 'period_asc':
                    $sort = 'o.period ASC';
                break;
                
                case 'period_desc':
                    $sort = 'o.period DESC';
                break;
                
                case 'fio_asc':
                    $sort = 'u.lastname ASC';
                break;
                
                case 'fio_desc':
                    $sort = 'u.lastname DESC';
                break;
                                
                case 'birth_asc':
                    $sort = 'u.birth ASC';
                break;
                
                case 'birth_desc':
                    $sort = 'u.birth DESC';
                break;

                case 'phone_asc':
                    $sort = 'u.phone_mobile ASC';
                break;
                
                case 'phone_desc':
                    $sort = 'u.phone_mobile DESC';
                break;
                                
                case 'region_asc':
                    $sort = 'u.Regregion ASC';
                break;
                
                case 'region_desc':
                    $sort = 'u.Regregion DESC';
                break;
                                
                case 'status_asc':
                    $sort = 'o.1c_status DESC, o.status ASC';
                break;
                
                case 'status_desc':
                    $sort = 'o.1c_status ASC, o.status DESC';
                break;
                                
                case 'utm_asc':
                    $sort = 'o.utm_source ASC, o.webmaster_id ASC';
                break;
                
                case 'utm_desc':
                    $sort = 'o.utm_source DESC, o.webmaster_id DESC';
                break;
                                
                                
            endswitch;
        }

        $query = $this->db->placehold("
            SELECT 
                o.id AS order_id,
                o.manager_id,
                o.status,
                o.date,
                o.accept_date,
                o.accept_sms,
                o.call_date,
                o.confirm_date,
                o.approve_date,
                o.reject_date,
                o.user_id,
                o.ip,
                o.amount,
                o.period,
                o.percent,
                o.first_loan,
                o.1c_id AS id_1c,
                o.1c_status AS status_1c,
                o.utm_source,
                o.utm_medium,
                o.utm_campaign,
                o.utm_content,
                o.utm_term,
                o.webmaster_id,
                o.click_hash,
                o.local_time,
                o.card_id,
                o.card_type,
                o.juicescore_session_id,
                o.have_close_credits,
                o.reason_id,
                o.pay_result,
                o.razgon,
                o.max_amount,
                o.min_period,
                o.max_period,
                o.loan_type,
                o.payment_period,
                o.cdoctor_id,
                o.stage1,
                o.stage1_date,
                o.stage2,
                o.stage2_date,
                o.stage3,
                o.stage3_date,
                o.stage4,
                o.stage4_date,
                o.stage5,
                o.stage5_date,
                o.call_variants,
                o.b2p,
                o.organization_id,                
                o.insurer,
                o.insure_amount,
                o.insure_percent,
                o.autoretry,
                o.note,
                o.is_credit_doctor,                   
                o.order_uid,
                u.personal_data_added,
                u.additional_data_added,
                u.card_added,
                u.files_added,
                u.maratorium_id, 
                u.maratorium_date,
                u.UID AS user_uid,
                u.tinkoff_id,
                u.service_sms,
                u.service_insurance,
                u.service_reason,
                u.phone_mobile,
                u.email,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.gender,
                u.birth,
                u.birth_place,
                u.inn,
                u.passport_serial,
                u.subdivision_code,
                u.passport_date,
                u.passport_issued,
                u.Regindex,
                u.Regregion,
                u.Regregion_shorttype,
                u.Regcity,
                u.Regcity_shorttype,
                u.Regstreet,
                u.Regstreet_shorttype,
                u.Reghousing,
                u.Regbuilding,
                u.Regroom,
                u.Faktindex,
                u.Faktregion,
                u.Faktregion_shorttype,
                u.Faktcity,
                u.Faktcity_shorttype,
                u.Faktstreet,
                u.Faktstreet_shorttype,
                u.Fakthousing,
                u.Faktbuilding,
                u.Faktroom,
                u.contact_person_name,
                u.contact_person_phone,
                u.contact_person_relation,
                u.contact_person2_name,
                u.contact_person2_phone,
                u.contact_person2_relation,
                u.contact_person3_name,
                u.contact_person3_phone,
                u.contact_person3_relation,
                u.workplace,
                u.profession,
                u.work_scope,
                u.work_phone,
                u.workdirector_name,
                u.Workindex,
                u.Workregion,
                u.Workcity,
                u.Workstreet,
                u.Workhousing,
                u.Workbuilding,
                u.Workroom,
                u.marital_status,
                u.income_base,
                u.social_inst,
                u.social_fb,
                u.social_vk,
                u.social_ok,
                u.loan_history,
                u.choose_insure,
                u.cdoctor_level,
                u.has_estate,
                crm_response
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE 1
                $id_filter
                $stage_completed_filter
                $not_status_1c_filter
                $manager_id_filter
                $not_manager_id_filter
                $user_id_filter
                $b2p_filter
                $current_filter
                $status_filter
                $notbusy_filter
                $inwork_filter
                $notreceived_filter
                $approve_filter
                $issued_filter
                $id_1c_filter
                $date_from_filter
                $date_to_filter
                $search_filter
                $dops_filter
                $cdoctor_filter
                $autoretry_filter
                $credit_doctor_filter
                $close_credits_filter
                $credit_getted_filter
                $keyword_filter
            ORDER BY $sort 
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
            {
                $result->loan_history = empty($result->loan_history) ? array() : json_decode($result->loan_history);
            }
        }
        if ($this->is_developer)
        {
        //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        }
        return $results;
	}

    /**
     * Проверка наличия заявок
     * @param int $user_id
     * @return bool
     */
    public function hasOrdersByUserId(int $user_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM __orders WHERE user_id = ?) as r", $user_id);
        $this->db->query($query);

        return (bool)$this->db->result('r');
    }

    /**
     * Сохраняет лог статусов при изменении
     * @param int $order_id
     * @param array $new_data
     * @param array $old_data
     * @return void
     */
    public function addStatusLog(int $order_id, array $new_data, array $old_data)
    {
        $data = [
            'order_id' => $order_id,
            'front' => 1,
        ];

        if (isset($new_data['1c_status']) && isset($old_data['status_1c']) && $new_data['1c_status'] != $old_data['status_1c'] && !empty($new_data['1c_status'])) {
            $data['old_status_1c'] = $old_data['status_1c'];
            $data['status_1c'] = $new_data['1c_status'];
        }

        if ($new_data['status'] != $old_data['status'] && !empty($new_data['status'])) {
            $data['old_status'] = $old_data['status'];
            $data['status'] = $new_data['status'];
        }

        // добавим в крон НК при закрытии займа
        if (($data['status_1c'] ?? '') === '6.Закрыт') {
            // проверяем займ на разделение
            $divide_order = $this->getDivideOrderByOrderId($order_id);
            if (!empty($divide_order)) {
                $this->updateStatusDivideOrder($divide_order, $order_id);
            } else {
                $this->addTaskAutoApproveOrder($order_id);
            }
        }

        $query = $this->db->placehold("INSERT INTO s_order_status_logs SET ?%", $data);
        $this->db->query($query);
    }

    /**
     * Проверяет деленные части, если одна из них выданная ещё, то не позволяем закрыть деленный займ
     *
     * @param $divide_order
     * @param int $order_id
     * @return void
     */
    public function updateStatusDivideOrder($divide_order, int $order_id)
    {
        $additional_order_id = (int)($order_id == $divide_order->main_order_id ? $divide_order->divide_order_id : $divide_order->main_order_id);
        $additional_order = $this->get_order($additional_order_id);

        // если вторая половина не выдана, то выполним действия
        if ($order_id == $divide_order->main_order_id) {
            if (!empty($divide_order->divide_order_id)) {
                if (!empty($additional_order) && $additional_order->status_1c != self::ORDER_1C_STATUS_CONFIRMED) {
                    if ($additional_order->status_1c != self::ORDER_1C_STATUS_CLOSED) {
                        $res = $this->soap->set_tehokaz($additional_order->id_1c);
                        if ($res === 'OK') {
                            $this->orders->update_order(
                                $additional_order_id,
                                [
                                    'status' => 3,
                                    'reason_id' => $this->reasons::REASON_CLOSED_ONE_DIVIDE_ORDER_REASON_ID,
                                    '1c_status' => self::ORDER_1C_STATUS_REJECTED_TECH,
                                    'reject_date' => date('Y-m-d H:i:s'),
                                ]
                            );
                            $this->addTaskAutoApproveOrder($order_id);
                        }
                    }

                    $this->orders->updateDivideOrder(
                        $divide_order->id,
                        ['status' => $this->orders::DIVIDE_ORDER_STATUS_CLOSED_BY_ONE]
                    );
                }
            } else {
                $this->orders->updateDivideOrder(
                    $divide_order->id,
                    ['status' => $this->orders::DIVIDE_ORDER_STATUS_CLOSED_BY_ONE]
                );
                $this->addTaskAutoApproveOrder($order_id);
            }
        } elseif (!empty($additional_order) && $additional_order->status_1c != self::ORDER_1C_STATUS_CONFIRMED) {
            $this->orders->updateDivideOrder(
                $divide_order->id,
                ['status' => $this->orders::DIVIDE_ORDER_STATUS_CLOSED]
            );
            $this->addTaskAutoApproveOrder($order_id);
        }
    }

    /**
     * Генерирует задание cron для автоодобрения
     * @param int $order_id
     * @return void
     */
    public function addTaskAutoApproveOrder(int $order_id)
    {
        $order = $this->get_order($order_id);
        $data_auto_approve = [
            'parent_order_id' => $order_id,
            'user_id' => $order->user_id,
            'status' => $this->orders_auto_approve::STATUS_CRON_NEW,
            'date_cron' => (new DateTime())->format('Y-m-d H:i:s'),
            'validate_scoring' => 1,
        ];
        $this->orders_auto_approve->addAutoApproveNK($data_auto_approve);
    }

    /**
     * Обновляет сумму заявки
     * @param string $uid
     * @param int $order_id
     * @param int $amount
     * @return bool
     */
    public function editAmount(string $uid, int $order_id, int $amount, int $period = 0): bool
    {
        $data = [
            'НомерЗаявки' => $uid,
            'Сумма' => $amount,
        ];

        $object = $this->soap->generateObject($data);
        $response_soap = $this->soap->requestSoap($object, 'WebOtvetZayavki', 'ApprovalSumm', 'editAmount.txt');
        $result = $response_soap['response'] === 'OK';

        if ($result) {
            $order = $this->get_order($order_id);

            $edit_period = empty($period) ? $order->period : $period;
            
            $update_data = [
                'amount' => $amount,
                'period' => $edit_period,
            ];

            //сохраним старую сумму для возможности ее применить потом после изменения суммы в ЛК
            if (empty($order->approve_amount)) {
                $update_data['approve_amount'] = $order->amount;
            }
            
            $manager = $this->managers->get_crm_manager($order->manager_id); 
            $this->soap->update_status_1c($order->id_1c, 'Одобрено', $manager->name_1c, $amount, $order->percent, '', 0, $edit_period);
            
            $this->update_order($order_id, $update_data);
        }

        return $result;
    }
    

    /**
     * Получает информацию об автоодобрении
     * @param int $order_id
     * @return false|int
     */
    public function getAutoApproveOrderByOrderId(int $order_id)
    {
        $sql = "SELECT * FROM s_orders_auto_approve WHERE order_id = ?";
        $query = $this->db->placehold($sql, $order_id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Генерируем данные для мотивационного баннера в ЛК
     * @param array $last_order
     * @param $user
     * @return array
     * @throws Exception
     */
    public function getMotivationBannerData(array $last_order, $user): array
    {
        $motivation_banner = [];
        $order_no_sale = $user->balance->sale_info != 'Договор продан';

        if ($order_no_sale) {
            $getDayBanner = function ($last_order) {
                $motivation_banner = [];

                $confirm_date = new DateTime($last_order['confirm_date']);
                $confirm_date->setTime(0, 0); // ставим время для корректной проверки разницы в днях
                $interval_view_banner = $confirm_date->diff((new DateTime()));
                $order_zero_percent = (int)$last_order['percent'] === 0; // беспроцентный ли займ

                if ($order_zero_percent) {
                    $motivation_banner['text'] = 'Закройте действующий договор и получите <b>25 000 рублей</b><small>*</small>';
                    $motivation_banner['level'] = $motivation_banner['level_img'] = 'Silver';
                    $motivation_banner['show'] = $interval_view_banner->days > 0; // показ баннера со второго дня
                } else {
                    $motivation_banner['text'] = 'Закройте действующий договор и получите на <b>8000 рублей</b> больше<small>*</small>';
                    $motivation_banner['level'] = $motivation_banner['level_img'] = 'Gold';
                    $motivation_banner['show'] = $interval_view_banner->days > 8; // показ баннера с 10 дня
                }
                return $motivation_banner;
            };

            $is_new_user = empty($last_order['have_close_credits']); //проверка на НК
            $order_is_worked = $last_order['1c_status'] === self::ORDER_1C_STATUS_CONFIRMED && $last_order['status'] == self::STATUS_APPROVED; //проверка на действующий займ
            $order_is_approved = Helpers::isApproved($last_order); // одобрен ли займ
            $order_is_auto_approve = $last_order['utm_source'] === self::UTM_RESOURCE_AUTO_APPROVE; // автоодобрение

            if ($is_new_user) {
                if ($order_is_worked) {
                    $motivation_banner = $getDayBanner($last_order);
                } else if ($order_is_approved) {
                    $motivation_banner['text'] = 'Для перехода на уровень <b class="anime_text">Platinum</b><br/> осталось оформить 2 договора займа';
                    $motivation_banner['level'] = 'Gold';
                    $motivation_banner['level_img'] = 'Platinum';
                    $motivation_banner['show'] = true;
                }
            } else {
                // проверим мораторий у пользователя
                if(strtotime($user->maratorium_date) > time())
                {
                    $moratorium_date = new DateTime($user->maratorium_date);
                } else if($last_order['status'] === self::STATUS_REJECTED) {
                    // если нет моратория у пользователя, проверим мораторий по заявке
                    $reason = $this->reasons->get_reason($last_order['reason_id']);
                    $date = new DateTime($last_order['date']);
                    $date->modify('+ ' . ((int)($reason->maratory ?? 0)) . ' day');

                    if($date->getTimestamp() > time())
                    {
                        $date = new DateTime($last_order['date']);
                        $moratorium_date = $date->modify('+ ' . $reason->maratory . ' day');
                    }
                }

                $motivation_banner['level'] = $motivation_banner['level_img'] = 'Gold';

                if ($order_is_worked) {
                    $motivation_banner = $getDayBanner($last_order);
                } else {
                    if ($order_is_approved) {
                        if ($order_is_auto_approve) {
                            $motivation_banner['text'] = 'Наша система<br/> одобрила Вам автоматически.<br/> <b>Осталось только забрать деньги!</b>';
                        } else {
                            $motivation_banner['text'] = 'Для перехода на уровень <b class="anime_text">Platinum</b><br/> осталось оформить 2 договора займа';
                            $motivation_banner['level_img'] = 'Platinum';
                        }
                        $motivation_banner['show'] = true;
                    } else if(!isset($moratorium_date)){
                        $motivation_banner['text'] = 'Вам предодобрено <b>' . (number_format(min($last_order['amount'] + 8000, 30000), 0, '.',' ')) . ' рублей.</b>';
                        $motivation_banner['show'] = true;
                    }
                }
            }

            if ($order_is_worked) {
                $motivation_banner['description'] = '<small>*</small> окончательное решение о выдаче займа принимает компания на основании оценки факторов кредитного риска';
            }
        }

        return $motivation_banner;
    }

    /**
     * check_use_b2p()
     * Проверяет можно ли перевести клиента на б2п
     * Возвращает 1 - переводим на б2п, 0 - переводим на тиньков
     *
     * @param int $user_id
     * @return int
     */
    public function check_use_b2p($user_id)
    {
        // для всех возвращаем б2п
        return 1;
        
        $minus7 = date('Y-m-d', time() - 7*86400);

        $query = $this->db->placehold("
            SELECT id FROM s_orders
            WHERE user_id = ?
            AND status = 2
            AND b2p = 0
            AND credit_getted = 0
            AND DATE(date) >= ?
        ", (int)$user_id, $minus7);
        $this->db->query($query);
        $res = $this->db->result('id');

        return (int)empty($res);
    }

    /**
     * Получает данные если есть данные для будущей заявки при разделении
     * @param int $order_id
     * @return false|int
     */
    public function getDividePreOrder(int $order_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_divide_pre_orders WHERE order_id = ?", $order_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Возвращает информацию из логов изменения статусов заявок
     * @param array $data_filter
     * @param bool $return_all
     * @return array|false|int
     */
    public function getOrderStatusLogs(array $data_filter = [], bool $return_all = true)
    {
        $where = [];

        $sql = "SELECT * FROM s_order_status_logs WHERE 1
                    -- {{where}}";

        if (!empty($data_filter['filter_order_id'])) {
            $where[] = $this->db->placehold("order_id = ?", intval($data_filter['filter_order_id']));
        }

        if (!empty($data_filter['filter_new_status'])) {
            $where[] = $this->db->placehold("status = ?", intval($data_filter['filter_new_status']));
        }

        if (!empty($data_filter['filter_new_status_1c'])) {
            $where[] = $this->db->placehold("status_1c = ?", trim($data_filter['filter_new_status_1c']));
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $return_all ? $this->db->results() : $this->db->result();
    }

    /**
     * Получает разделенную заявку по главной или разделенной заявке
     * @param int $order_id
     * @return false|int
     */
    public function getDivideOrderByOrderId(int $order_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_divide_order WHERE main_order_id = ? OR divide_order_id = ?", $order_id, $order_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Возвращает разделенный займ по id
     * @param int $id
     * @return false|int
     */
    public function getDivideOrderById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_divide_order WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Обновляет разделенный займ
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateDivideOrder(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE s_divide_order SET ?% WHERE id = ?", $data, $id);

        if (!empty($data['status'])) {
            $divide_order_old = $this->getDivideOrderById($id);
            if ($divide_order_old->status !== $data['status']) {
                $this->addDivideOrderStatusLog(
                    [
                        'divide_order_id' => $id,
                        'status' => $data['status'],
                    ]
                );
            }
        }

        $result = $this->db->query($query);
        return $result !== false ? $result : new stdClass();
    }

    /**
     * Находит данные о будущих разделенных заявках
     * @param array $data_filter
     * @param bool $return_all
     * @return array|false|int
     */
    public function getDivideOrders(array $data_filter = [], bool $return_all = true)
    {
        $where = [];
        $limit = '';

        $sql = "SELECT * FROM s_divide_order WHERE 1
                    -- {{where}}
                    -- {{limit}}";

        if (!empty($data_filter['filter_status'])) {
            $where[] = $this->db->placehold("status = ?", trim($data_filter['filter_status']));
        }

        if (!empty($data_filter['filter_not_statuses'])) {
            $where[] = $this->db->placehold("status NOT IN (?@)", $data_filter['filter_not_statuses']);
        }

        if (!empty($data_filter['filter_user_id'])) {
            $where[] = $this->db->placehold("user_id = ?", intval($data_filter['filter_user_id']));
        }

        if (!empty($data_filter['filter_date_added'])) {
            $where[] = $this->db->placehold("date_added BETWEEN ? AND ?", $data_filter['filter_date_added']['filter_date_start'] . ' 00:00:00', $data_filter['filter_date_added']['filter_date_end'] . ' 23:59:59');
        }

        if (!empty($data_filter['filter_limit'])) {
            $limit = $this->db->placehold("LIMIT ?, ?", $data_filter['filter_limit']['offset'] ?? 0, intval($data_filter['filter_limit']['limit']));
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{limit}}' => $limit,
        ]);

        $this->db->query($query);
        return $return_all ? $this->db->results() : $this->db->result();
    }

    /**
     * Получает дату выдачи займа
     * @param int $order_id
     * @return string
     */
    public function getConfirmeDate(int $order_id): string
    {
        $confirm_date = '';
        $order = $this->get_order($order_id);

        if (!empty($order->confirm_date)) {
            $confirm_date = $order->confirm_date;
        } else if($order->b2p == 1) { // если Б2П, то надо смотреть на статус CRM
            $filter_data = [
                'order_id' => $order_id,
                'filter_new_status' => self::STATUS_CONFIRMED,
            ];
        } else { // если не Б2П, то надо смотреть на статус 1C
            $filter_data = [
                'order_id' => $order_id,
                'filter_new_status_1c' => self::ORDER_1C_STATUS_CONFIRMED,
            ];
        }

        if (empty($confirm_date) && !empty($filter_data)) {
            $status_log = $this->getOrderStatusLogs($filter_data, false);
            $confirm_date = $status_log['date_added'];
        }

        return $confirm_date;
    }

    /**
     * Добавляет информацию об изменении статусов для разделенных займов
     * @param array $data
     * @return mixed
     */
    public function addDivideOrderStatusLog(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_divide_order_status_log SET ?%", $data);
        $this->db->query($query);

        return $this->db->insert_id();
    }

    /**
     * @param string $code
     * @return false|stdClass
     */
    public function getLinkData(string $code){
        $this->db->query("
            SELECT * FROM __short_link WHERE link = ?" ,$code);
        return $this->db->result();
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function isFirstOrder(int $user_id): bool
    {
        $query = $this->db->placehold("SELECT COUNT(*) FROM __orders WHERE user_id=? AND status = 10", $user_id);
        $this->db->query($query);
        $result = $this->db->result();

        $count = 0;
        if (is_object($result)) {
            $count = isset($result->count) ? $result->count : 0;
        } elseif (is_array($result)) {
            $count = isset($result['count']) ? $result['count'] : 0;
        }

        return ($count == 1);
    }

    /**
     * @param int $order_id
     * @param array $loan_history
     * @return string|null
     */
    public function get_loan_id(int $order_id, array $loan_history): ?string
    {
        foreach ($loan_history as $loan)
        {
            $parts = explode('-', $loan->number);
            $number = end($parts);
            if ((int)$number == (int)$order_id)
                return $loan->number;
        }
        return null;
    }

    public function get_loan_date(int $order_id, array $loan_history)
    {
        foreach ($loan_history as $loan)
        {
            $parts = explode('-', $loan->number);
            $number = end($parts);
            if ((int)$number == (int)$order_id)
                return $loan->date;
        }
        return null;
    }

    /**
     * Получение всех order_id из $this->user->loan_history
     * @param array $loan_history
     * @return array
     */
    public function get_order_ids(array $loan_history): array
    {
        $loans = [];
        foreach ($loan_history as $loan)
        {
            $parts = explode('-', $loan->number);
            $number = end($parts);
            $loans[] = $number;
        }
        return $loans;
    }

    /**
     * Получить отпечаток устройства пользователя для финкарты
     * @param string|int $orderId
     * @return string
     */
    public function getFinkartaFp($orderId)
    {
        return $this->order_data->read($orderId, 'finkarta_fp') ?? '';
    }

    /**
     * Сохранить отпечаток устройства пользователя для финкарты
     * @param string|int $orderId
     * @param string $fingerprint
     * @return void
     */
    public function saveFinkartaFp($orderId, $fingerprint)
    {
        if (!empty($fingerprint))
            $this->order_data->set($orderId, 'finkarta_fp', $fingerprint);
    }

    /**
     * Метод обработки дополнительных услуг с учётом логов изменений
     */
    public function enableAdditionalServicesWithLogs($startDate, $endDate)
    {
        $sql = "
        UPDATE s_order_data sod
        SET sod.value = 0
        WHERE sod.key IN ('additional_service_tv_med', 'additional_service_multipolis')
          AND sod.value = 1
          AND NOT EXISTS (
              SELECT 1
              FROM s_changelogs cl
              WHERE cl.type = sod.key
                AND cl.new_values = 'Выключение'
                AND cl.created BETWEEN ? AND ?
                AND cl.order_id = sod.order_id
          );
    ";

        $query = $this->db->placehold($sql, $startDate . ' 00:00:00', $endDate . ' 23:59:59');
        $this->db->query($query);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Добавляем переменные ДОП`ов - self::ADDITIONAL_SERVICES
     * @param array|stdClass|object $result
     * @param int|null $id
     * @return void
     */
    public function addAdditionalServicesFields(&$result, ?int $id): void
    {
        if ($id) {
            $orderData = $this->order_data->getAdditionalServices($id);
        }
        foreach ($this->order_data::ADDITIONAL_SERVICES as $service) {
            $result->$service = ($orderData[$service] ?? 0) ? 0 : $this->order_data::ADDITIONAL_SERVICE_DEFAULT_VALUE;
        }
    }

    public function getListOfOrdersWithMoratoriumFlag(int $orderId = null): array
    {
        $where = null;
        if ($orderId != null) {
            $where = "AND s_orders.id = " . $orderId;
        }

        $sql = "
          SELECT 
           s_orders.id AS 'id', 
           s_orders.amount AS 'amount',
           user.firstname AS 'firstname', 
           user.phone_mobile AS 'phone', 
           user.Faktcity AS 'f_city', 
           user.Regcity AS 'r_city',
           user.Regcity_shorttype AS 'short', 
           user.Faktindex AS 'i'
          FROM s_orders
          LEFT JOIN s_reasons AS reason ON s_orders.reason_id = reason.id
          LEFT JOIN s_users AS user ON s_orders.user_id = user.id
          LEFT JOIN s_contracts AS contract ON s_orders.contract_id = contract.id
          WHERE s_orders.status = 3
          AND reason.type = 'reject' 
          AND reason.maratory > 1
          AND NOT EXISTS (SELECT * FROM s_order_data WHERE order_id = s_orders.id AND `key` = 'sent_to_banki_ru')
          AND s_orders.date >= DATE_SUB(NOW(), INTERVAL 1 DAY) {$where}
        LIMIT 300";

        $this->db->query($sql);

        $result =  $this->db->results();

        return $result !== false ? $result : [];
    }

    /**
     * Проверяет, что включено опасное флоу и тип займа PDL
     * @param array $order
     * @param object $user
     * @return bool
     */
    public function isPdlOnDangerousFlow(array $order, object $user): bool
    {
        return !$this->users->isSafetyFlow($user) && $order['loan_type'] == 'PDL';
    }

    /**
     * Формирует прайс доп услуг
     * @param array $order
     * @param object $user
     * @return int
     */
    public function getAdditionalServicesPrice(array $order, object $user): int
    {
        $credit_doctor = $this->credit_doctor->getCreditDoctor(
            (int)$order['amount'],
            empty($user->loan_history)
        );

        return $credit_doctor->price + $this->star_oracle::AMOUNT;
    }

    /**
     * Проверка на лимит PDL займа + допов
     * @param array $order
     * @param object $user
     * @return bool
     */
    public  function isExceedingMaxLimit(array $order, object $user): bool
    {
        $orderAmount = (int)$order['amount'];
        $additionalServicesPrice = $this->getAdditionalServicesPrice($order, $user);

        return $orderAmount + $additionalServicesPrice >= self::PDL_MAX_AMOUNT;
    }

    /**
     * Обновляет прайс с учетом цены допов на опасном флоу и для PDL
     * @param array $order
     * @param object $user
     * @param int $price
     * @return int
     */
    public function editPdlAmountOnDangerousFlow(array $order, object $user, int $price): int
    {
        if (!$this->isPdlOnDangerousFlow($order, $user)) {
            return $price;
        }

        if (!$this->isExceedingMaxLimit($order, $user)) {
            return $price;
        }

        return $price + $this->getAdditionalServicesPrice($order, $user);
    }

    /**
     * Рассчитывает сумму, которая будет отображаться пользователю в зависимости от флоу
     * @param array $order
     * @param object $user
     * @return array
     */
    public function calculatePdlPriceOnDangerousFlow(array $order, object $user): array
    {
        if (!$this->isPdlOnDangerousFlow($order, $user)) {
            return $order;
        }

        if (!$this->isExceedingMaxLimit($order, $user)) {
            return $order;
        }

        $additionalServicesPrice = $this->getAdditionalServicesPrice($order, $user);

        if ($order['approve_amount']) {
            $order['approve_amount'] = self::PDL_MAX_AMOUNT - $additionalServicesPrice;
        }

        $order['amount'] = self::PDL_MAX_AMOUNT - $additionalServicesPrice;
        $order['user_amount'] -= $additionalServicesPrice;
        $order['approved_amount'] = self::PDL_MAX_AMOUNT - $additionalServicesPrice;
        $order['approve_max_amount'] = self::PDL_MAX_AMOUNT - $additionalServicesPrice;

        return $order;

    }

    /**
     * Получение минимального и максимального значения калькулятора для выбора получаемой суммы по одобренной заявке.
     *
     * Дублирует логику из `accept_credit.tpl` и `installment/edit_amount.tpl` для проверки полученных данных на бэке.
     * @param stdClass $order
     * @return array|null `null` если нельзя менять сумму.
     *
     * Если сумму менять можно - возвращает массив с её границами:
     * ```
     * [ // Структура массива, цифры для примера
     *  'min' => 1000,
     *  'max' => 5000,
     * ]
     * ```
     */
    public function getAmountEditRange($order)
    {
        // Фиксим неоднозначность нашего кода
        if (empty($order->id))
            $order->id = $order->order_id;
        if (empty($order->id_1c))
            $order->id_1c = $order->{'1c_id'};

        $approve_max_amount = $order->max_amount ?: $order->approve_amount;
        if (empty($approve_max_amount)) {
            $order_1c = $this->orders->check_order_1c($order->id_1c);
            $approve_max_amount = preg_replace("/[^0-9]/", '', $order_1c->return->Сумма);
        }

        $user_amount = (int)$this->order_data->read((int)$order->id, $this->order_data::USER_AMOUNT);
        $user = $this->users->get_user_by_id($order->user_id);
        $divide_pre_order = $this->getDividePreOrder($order->id);

        // Копия условия из accept_credit.tpl
        if (
            $approve_max_amount > $user_amount
            && (
                ($approve_max_amount != 1000 && $order->have_close_credits == 1)
                ||
                (
                    $approve_max_amount > $user->first_loan_amount
                    && !$divide_pre_order
                )
            )
        ) {
            $min_amount = $user_amount ?? $approve_max_amount - 1000;
            // Условие выполнилось - менять сумму в заявке можно
            if ($order->max_period > 0 && $order->max_amount > 30000) {
                // Installment
                return [
                    'min' => $min_amount,
                    'max' => $approve_max_amount
                ];
            }
            else {
                // PDL
                return [
                    'min' => $min_amount,
                    'max' => $approve_max_amount
                ];
            }
        }

        // Условие из accept_credit.tpl не выполнилось - в заявке нельзя менять сумму
        return null;
    }

    /**
     * Определяет, нужно ли отключать VitaMed для займа
     * @param string|null $zaimNumber
     * @return bool
     */
    public function shouldDisableVitamed(?string $zaimNumber): bool
    {
        if (empty($zaimNumber)) {
            return false;
        }

        $isBankPayment = $this->soap->checkIfPaidViaSettlementAccount($zaimNumber);

        return $this->settings->vitamed_bankpay_disable && $isBankPayment;
    }

    public function isCrossOrder(stdClass $order): bool
    {
        return $order->utm_source === self::UTM_SOURCE_CROSS_ORDER;
    }
}
