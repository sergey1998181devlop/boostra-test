<?php

use App\Core\Application\Application;
use App\Modules\Referral\Services\ReferralService;

require_once 'Simpla.php';

class Contracts extends Simpla
{
    private ReferralService $referralService;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->referralService = $app->make(ReferralService::class);
        parent::__construct();
    }

    public function make_issuance($contract_id, $issuance_date)
    {
        if ($contract = $this->get_contract($contract_id))
        {
            $return_date = date('Y-m-d', strtotime($issuance_date) + $contract->period * 86400);

            $update_contract = [
                'status' => 2,
                'issuance_date' => date('Y-m-d H:i:s', strtotime($issuance_date)),
                'return_date' => $return_date,
                'loan_body_summ' => $contract->amount,
            ];
            if ($contract->base_percent == 0) {
                $update_contract['base_percent'] = 1;
                $update_contract['grace_date'] = $return_date;
            }

            $this->update_contract($contract->id, $update_contract);
        }
    }

    /**
     * Contracts::accept_credit()
     * подпись договора
     * @param object $order
     * @param array $params
     * @return void
     */
    public function accept_credit(object $order, array $params): void
    {
        $user = $this->users->get_user((int)$order->user_id);

        $additional_amount = $this->processAdditionalServices($order, $params);

        if(!empty($params['agree_claim_value']) && $order->id){
            $this->soap->sendCreditRegistrationFD(
                $order->id,
                $params['agree_claim_value']
            );
        }

        $this->setOrderMetadata($order, $user, $params);
    
        if ($order->loan_type == $this->orders::LOAN_TYPE_IL) {
            $this->orders->update_order($order->id, [
                'percent' => $this->orders::BASE_PERCENTS,
            ]);
        }

        if ($contract_id = $this->create_new_contract($order->id)) {
            $this->orders->update_order($order->id, ['contract_id' => $contract_id]);
        }

        $total_amount = $order->amount + $additional_amount;
        if ($order->loan_type == $this->orders::LOAN_TYPE_PDL && $additional_amount > 0) {
            $maxPdlAmount = $this->resolvePdlMaxAmount($order);

            if ($total_amount > $maxPdlAmount) {
                $total_amount = $maxPdlAmount;
                $this->orders->update_order($order->id, ['amount' => $total_amount - $additional_amount]);
            }
        }

        $this->update_contract($contract_id, ['amount' => $total_amount]);

        $update = [
            'confirm_date' => date('Y-m-d H:i:s'),
            'status' => $this->orders::STATUS_SIGNED,
        ];

        $this->orders->update_order($order->id, $update);

        /** Устанавливаем данные для реферальной скидки */
        $this->referralService->setRefererIdToOrderData((int) $order->id, $user->uid);
    }

    /**
     * @throws Exception
     */
    private function processAdditionalServices($order, array $params): float
    {
        $total_amount = 0;

        $total_amount += (int)$this->addCreditDoctorService($order, $params);
        $total_amount += $this->addTvMedicalService($order, $params);

        return $total_amount;
    }

    /**
     * Добавление услуги "Кредитный доктор"
     * @throws Exception
     */
    private function addCreditDoctorService($order, array $params): ?int
    {
        if (($params['is_user_credit_doctor'] ?? 0) != 1) {
            return 0;
        }

        $credit_doctor = $this->credit_doctor->getCreditDoctor(
            (int)$order->amount,
            $order->have_close_credits == 0,
            (int)$order->user_id
        );
        
        if (!$credit_doctor){
            return 0;
        }

        $this->credit_doctor->addUserCreditDoctorData([
            'status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_NEW,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'amount' => $credit_doctor->price,
            'credit_doctor_condition_id' => $credit_doctor->id,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'organization_id' => $this->organizations::FINTEHMARKET_ID,
        ]);

        return (int)$credit_doctor->price;
    }

    /**
     * Добавление услуги "Звездный оракул" (отключено на выдаче)
     */
    private function addStarOracleService($order, array $params): int
    {
        // star_oracle отключен на выдачах
        return 0;
    }

    /**
     * Добавление услуги "Телемедицина" при выдаче
     */
    private function addTvMedicalService($order, array $params): int
    {
        if (($params['is_tv_medical'] ?? 0) != 1) {
            return 0;
        }

        $this->tv_medical->addPayment([
            'tv_medical_id' => 1,
            'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_NEW,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'amount' => $this->tv_medical::ISSUANCE_AMOUNT,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'organization_id' => $this->organizations::FINTEHMARKET_ID,
            'action_type' => $this->star_oracle::ACTION_TYPE_ISSUANCE,
        ]);

        return $this->tv_medical::ISSUANCE_AMOUNT;
    }

    /**
     * Установка метаданных заказа
     */
    private function setOrderMetadata($order, $user, array $params): void
    {
        $safety_flow = $this->users->isSafetyFlow($user);
        $agree_claim_value = $params['agree_claim_value'] ?? 0;

        $this->order_data->set($order->id, $this->order_data::SAFETY_FLOW, $safety_flow);
        $this->order_data->set($order->id, $this->order_data::AGREE_CLAIM_VALUE, $agree_claim_value);
    }

    private function resolvePdlMaxAmount(object $order): float
    {
        $defaultMaxAmount = (float)$this->orders::PDL_MAX_AMOUNT;

        if (!$this->order_data->read((int)$order->id, $this->order_data::RCL_LOAN)) {
            return $defaultMaxAmount;
        }

        $tranche = $this->rcl->get_tranche(['order_id' => (int)$order->id]);
        if (!empty($tranche)) {
            $rclContract = $this->rcl->get_contract(['id' => (int)$tranche->rcl_contract_id]);
            if (!empty($rclContract) && (float)$rclContract->max_amount > 0) {
                return (float)$rclContract->max_amount;
            }
        }

        $firstRclMaxAmount = (float)$this->order_data->read((int)$order->id, $this->order_data::RCL_MAX_AMOUNT);
        return $firstRclMaxAmount > 0 ? $firstRclMaxAmount : $defaultMaxAmount;
    }
    
    public function create_new_contract($order_id)
    {
        if ($order = $this->orders->get_order($order_id))
        {
            $user = $this->users->get_user((int)$order->user_id);

            $pdn = $this->get_pdn($order->user_id);
            
            if ($isset_contract = $this->get_contract_by_params(['order_id' => $order_id]))
            {
                $update_contract = [
                    'amount' => $order->amount,
                    'period' => $order->period,
                    'card_id' => (int)$order->card_id,
                    'card_type' => $order->card_type,
                    'base_percent' => $order->percent,
                    'charge_percent' => 0,
                    'peni_percent' => 0,
                    'confirm_date' => $order->confirm_date,
                    'asp' => $order->accept_sms,
                    'psk' => $this->calc_psk($order),
                    'pdn' => $pdn,
                ];
                $contract_id = $this->update_contract($isset_contract->id, $update_contract);
            }
            else
            {
                $new_contract = [
                    'user_id' => $order->user_id,
                    'user_uid' => $user->UID ?? $user->uid,
                    'order_id' => $order->id,
                    'number' => $this->create_number($order),
                    'amount' => $order->amount,
                    'period' => $order->period,
                    'payment_method' => $order->b2p ? 'B2P' : 'TINKOFF',
                    'card_id' => (int)$order->card_id,
                    'status' => 1,
                    'base_percent' => $order->percent,
                    'charge_percent' => 0,
                    'peni_percent' => 0,
                    'uid' => exec($this->config->root_dir.'generic/uidgen'),
                    'create_date' => date('Y-m-d H:i:s'),
                    'confirm_date' => $order->confirm_date,
                    'organization_id' => $order->organization_id,
                    'asp' => $order->accept_sms,
                    'psk' => $this->calc_psk($order),
                    'pdn' => $pdn,
                ];
                $contract_id = $this->add_contract($new_contract);
                
            }
            
            return $contract_id;
        }

        return 0;
    }

    private function calc_psk($order)
    {
        if ($this->order_data->read($order->id, $this->order_data::RCL_LOAN)) {
            $contract = $this->rcl->get_contract(['user_id' => $order->user_id, 'organization_id' => $order->organization_id]);
            if (!empty($contract) && !empty($contract->max_amount)) {
                $amount = $contract->max_amount;
            }
            else {
                $amount = $this->order_data->read($order->id, $this->order_data::RCL_AMOUNT);
            }

            return $amount * $order->percent * 160; // Макс.лимит ВКЛ * Процент заявки * 160 дней
        }
        else {
            return $order->percent == 0 ? 365 : 365 * $order->percent;
        }
    }
    
    public function create_number($order)
    {
        $organization = $this->organizations->get_organization($order->organization_id);        
        $contract_number = $organization->contract_prefix.date('y', strtotime($order->date)).'-'.$order->id;
        
        return $contract_number;
    }
    
    public function get_contract_by_params($params)
    {
		$filter = [];
        $order_by = '';
        
        if (!empty($params['order_by'])) {
            $order_by = 'ORDER BY '.$params['order_by'];
            unset($params['order_by']);
        }
        
        if (!empty($params))
            foreach ($params as $param_key => $param_value)
                $filter[] = $this->db->placehold('`'.$param_key.'` = ?', $param_value);
        $where = implode(' AND ', $filter);
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE $where
            $order_by
        ");
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
        
    }
    
    public function get_contract($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_contracts($filter = array())
	{
		$id_filter = '';
		$user_id_filter = '';
		$user_uid_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['user_uid']))
            $user_uid_filter = $this->db->placehold("AND user_uid = ?", (string)$filter['user_uid']);
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE 1
                $id_filter
                $user_id_filter
                $user_uid_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_contracts($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $user_uid_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['user_uid']))
            $user_uid_filter = $this->db->placehold("AND user_uid = ?", (string)$filter['user_uid']);
        
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __contracts
            WHERE 1
                $id_filter
                $user_id_filter
                $user_uid_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_contract($contract)
    {
		$query = $this->db->placehold("
            INSERT INTO __contracts SET ?%
        ", (array)$contract);
        $this->db->query($query);
        $id = $this->db->insert_id();

        $this->logging(__METHOD__, '', '', ['contract_id' => $id, 'data' => $contract, 'debug' => debug_backtrace(0)], 'contracts.txt');

        return $id;
    }
    
    public function update_contract($id, $contract)
    {
		$query = $this->db->placehold("
            UPDATE __contracts SET ?% WHERE id = ?
        ", (array)$contract, (int)$id);
        $this->db->query($query);

        $this->logging(__METHOD__, '', '', ['contract_id' => $id, 'data' => $contract, 'debug' => debug_backtrace(0)], 'contracts.txt');

        return $id;
    }
    
    public function delete_contract($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __contracts WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function get_pdn(int $user_id)
    {
        $order_scorista = $this->scorings->getLastScoringOfUser($user_id);

        if ($order_scorista && property_exists($order_scorista, 'body')) {
            $scorista_body = json_decode($order_scorista->body);

            if (!empty($order_scorista->type) && $order_scorista->type == $this->scorings::TYPE_AXILINK) {
                $pdn = $scorista_body->pdn ?? 0;
            } else {
                $pdn = $scorista_body->additional->pti_RosStat->pti->result ?? 0;
            }

            return $pdn;
        }

        return 0;

    }

    /**
     * Метод для обновления даты закрытия займа
     * @param $number
     * @return void
     */
    public function updateCloseDateInContracts($number)
    {
        $currentDate = date('Y-m-d');

        $query = $this->db->placehold("
        UPDATE __contracts SET close_date = ? WHERE number = ?
    ", $currentDate, $number);

        $this->db->query($query);
    }

    /**
     * @param $userId
     * @return array|false
     */
    public function get_user_all_contracts_number($userId)
    {
        $query = $this->db->placehold("
            SELECT number 
            FROM __contracts
            WHERE
                user_id = ? 
            ORDER BY id DESC 
        ",(int)$userId);
        $this->db->query($query);
        

        return $this->db->results('number');
    }

    /** Получаем сумму выдачи в определенной МКК за определенный период (s_contracts.status = 2 - выдача) */
    public function getIssuanceAmountForPeriod(string $dateStart, string $dateEnd, int $organizationId): int
    {
        $query = $this->db->placehold("
            SELECT SUM(amount) as issuance_amount
            FROM s_contracts
            WHERE organization_id = ?
                AND status = 2
                AND issuance_date >= ?
                AND issuance_date < ?
            ;
        ", $organizationId, $dateStart, $dateEnd);

        $this->db->query($query);
        return (int)$this->db->result('issuance_amount');
    }
}
