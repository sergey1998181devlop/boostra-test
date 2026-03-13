<?php

require_once('Simpla.php');

class Documents extends Simpla
{
    const CONTRACT_DELETE_USER_CABINET = 'PREVIEW_CONTRACT_DELETE_USER_CABINET';
    const ANKETA_PEP = 'ANKETA_PEP';
    const SOLGLASHENIE_PEP = 'SOLGLASHENIE_PEP';
    const SOGLASIE_VZAIMODEYSTVIE = 'SOGLASIE_VZAIMODEYSTVIE';
    const SOGLASIE_MEGAFON = 'SOGLASIE_MEGAFON';
    const SOGLASIE_SCORING = 'SOGLASIE_SCORING';
    const SOGLASIE_SPISANIE = 'SOGLASIE_SPISANIE';
    const PRICHINA_OTKAZA = 'PRICHINA_OTKAZA';
    const DOP_SOGLASHENIE_PROLONGATSIYA = 'DOP_SOGLASHENIE_PROLONGATSIYA';
    const IND_USLOVIYA_NL = 'IND_USLOVIYA_NL';
    const POLIS_STRAHOVANIYA = 'POLIS_STRAHOVANIYA';
    const DOP_USLUGI_VIDACHA = 'DOP_USLUGI_VIDACHA';
    const DOP_USLUGI_PROLONGATSIYA = 'DOP_USLUGI_PROLONGATSIYA';
    const CONTRACT_CREDIT_DOCTOR = 'CONTRACT_CREDIT_DOCTOR';
    const ZAYAVLENIE_NA_STRAHOVANIYE = 'ZAYAVLENIE_NA_STRAHOVANIYE';
    const DOC_MULTIPOLIS = 'DOC_MULTIPOLIS';
    const ARBITRATION_AGREEMENT = 'ARBITRATION_AGREEMENT';
    const PENALTY_CREDIT_DOCTOR = 'PENALTY_CREDIT_DOCTOR';
    const OFFER_AGREEMENT = 'OFFER_AGREEMENT';
    const ASP_AGREEMENT = 'ASP_AGREEMENT'; // Соглашение АСП (аналог собственноручной подписи)
    const OFFER_ARBITRATION = 'OFFER_ARBITRATION'; // соглашение о подписании молчанием

    /**
     * Crm templates
     */
    public const ANKETA_NA_POLUCHENIE_ZAIMA_NEW = 'ANKETA_NA_POLUCHENIE_ZAIMA_NEW';
    public const BANK_ORDER_NEW = 'BANK_ORDER_NEW';
    public const CONTRACT_CREDIT_DOCTOR_NEW = 'CONTRACT_CREDIT_DOCTOR_NEW';
    public const RESHENIE_NEW = 'RESHENIE_NEW';
    public const DOGOVOR_MIKROZAIMA_NEW = 'DOGOVOR_MIKROZAIMA_NEW';
    public const MULTIPOLIS_ZAYAVLENIE_NEW = 'MULTIPOLIS_ZAYAVLENIE_NEW';
    public const OPLATA_USLUGI_STRAHOVANIYA_NEW = 'OPLATA_USLUGI_STRAHOVANIYA_NEW';
    public const PROCHIE_SVEDENIYA_NEW = 'PROCHIE_SVEDENIYA_NEW';
    public const PLATEZH_PO_DOGOVORU_NEW = 'PLATEZH_PO_DOGOVORU_NEW';
    public const RASCHET_NACHISLENIY_NEW = 'RASCHET_NACHISLENIY_NEW';
    public const SOGLASIE_NA_RASPROSTRANENIE_NEW = 'SOGLASIE_NA_RASPROSTRANENIE_NEW';
    public const SOGLASIE_NA_OBRABOTKU_NEW = 'SOGLASIE_NA_OBRABOTKU_NEW';
    public const ZAYAVLENIE_NA_PREDOSTAVLENIE_NEW = 'ZAYAVLENIE_NA_PREDOSTAVLENIE_NEW';
    public const ZAYAVLENIE_NA_STRAHOVANIE_NEW = 'ZAYAVLENIE_NA_STRAHOVANIE_NEW';
    public const PDN_EXCESSED = 'PDN_EXCESSED';

    const KEY_TEMPLATE = 'template';
    const KEY_NAME = 'name';
    const KEY_CLIENT_VISIBLE = 'client_visible';
    const ORGANIZATION_ID = 'organization_id';

    /**
     * Тип документа заявление КД
     */
    public const CONTRACT_USER_CREDIT_DOCTOR = 'CONTRACT_USER_CREDIT_DOCTOR';
    public const ORDER_FOR_EXECUTION_CREDIT_DOCTOR = 'ORDER_FOR_EXECUTION_CREDIT_DOCTOR';
    public const CREDIT_DOCTOR_POLICY = 'CREDIT_DOCTOR_POLICY';
    public const STAR_ORACLE_POLICY = 'STAR_ORACLE_POLICY';

    public const CONTRACT_STAR_ORACLE = 'CONTRACT_STAR_ORACLE';
    public const ORDER_FOR_EXECUTION_STAR_ORACLE = 'ORDER_FOR_EXECUTION_STAR_ORACLE';
    public const ORDER_FOR_EXECUTION_TV_MEDICAL = 'ORDER_FOR_EXECUTION_TV_MEDICAL';
    public const ACCEPT_TELEMEDICINE = 'ACCEPT_TELEMEDICINE';
    public const CONTRACT_TV_MEDICAL = 'CONTRACT_TV_MEDICAL';
    
    public const PAYMENT_DEFERMENT_REJECT = 'PAYMENT_DEFERMENT_REJECT';
    public const PAYMENT_DEFERMENT_APPROVE = 'PAYMENT_DEFERMENT_APPROVE';

    public const OFFER_SAFE_DEAL = 'OFFER_SAFE_DEAL';
    public const ORDER_FOR_EXECUTION_SAFE_DEAL = 'ORDER_FOR_EXECUTION_SAFE_DEAL';
    public const REPORT_SAFE_DEAL = 'REPORT_SAFE_DEAL';
    public const NOTIFICATION_SAFE_DEAL = 'NOTIFICATION_SAFE_DEAL';
    public const CONTRACT_SAFE_DEAL = 'CONTRACT_SAFE_DEAL';


    /**
     * Поручение на перечисление микрозайма
     * Для заявок ИП и ООО
     */
    public const PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA = 'PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA';

    private array $documentParams = [];

    public function create_document($data)
    {
        $documentType = $data['type'];
        $filters = ['type' => $documentType];
        $this->documentParams = $this->getDocumentParamsByFilter($filters);
        $params = [
            'user_id' => $data['user_id'] ?? 0,
            'order_id' => $data['order_id'] ?? 0,
            'contract_number' => $data['contract_number'] ?? '',
            'type' => $documentType,
            'name' => $this->documentParams[$documentType][self::KEY_NAME],
            'template' => $this->documentParams[$documentType][self::KEY_TEMPLATE],
            'client_visible' => $this->documentParams[$documentType][self::KEY_CLIENT_VISIBLE],
            'params' => $data['params'],
            'created' => date('Y-m-d H:i:s'),
        ];
        $params['organization_id'] = $data['organization_id'] ?? $this->documentParams[$documentType][self::ORGANIZATION_ID];

        return $this->add_document($params);
    }

    public function get_document_param($type)
    {
        return $this->getDocumentParamsByFilter(['type' => $type], false) ?? null;
    }

    public function get_document_params(): array
    {
        return $this->getDocumentParamsByFilter();
    }

    public function get_template(string $type): ?string
    {
        return $this->getDocumentParamsByFilter(['type' =>$type], false)[self::KEY_TEMPLATE] ?? null;
    }

	public function get_document($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __documents
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
            $result->params = unserialize($result->params);

        return $result;
    }
    
	public function get_documents($filter = array())
	{
		$id_filter = '';
		$user_id_filter = '';
		$order_id_filter = '';
		$contract_id_filter = '';
		$client_visible_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        $where = [];

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
            } else {
                $user_id_filter = $this->db->placehold("AND user_id = ?", intval($filter['user_id']));
            }
        }

        if (!empty($filter['order_id'])) {
            if (is_array($filter['order_id'])) {
                $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
            } else {
                $order_id_filter = $this->db->placehold("AND order_id = ?", intval($filter['order_id']));
            }
        }

        if (!empty($filter['contract_id']))
            $contract_id_filter = $this->db->placehold("AND contract_id IN (?@)", array_map('intval', (array)$filter['contract_id']));
        
        if (isset($filter['client_visible']))
            $client_visible_filter = $this->db->placehold("AND client_visible = ?", (int)$filter['client_visible']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}

        if(!empty($filter['contract_number'])) {
            $where[] = $this->db->placehold("contract_number IN (?@)", (array)$filter['contract_number']);
        }

        if (!empty($filter['type']))
        {
            $where[] = $this->db->placehold("type = ?", $filter['type']);
        }

        if (!empty($filter['not_types']))
        {
            $where[] = $this->db->placehold("type NOT IN (?@)", $filter['not_types']);
        }
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __documents
            WHERE 1
                $id_filter
        		$user_id_filter
        		$order_id_filter
        		$contract_id_filter
                $client_visible_filter
 	            $keyword_filter
            -- {{where}}
            ORDER BY id ASC 
            $sql_limit
        ");

        $query = strtr($query, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
            {
                $result->params = unserialize($result->params);
            }
        }
        return $results;
	}

	public function count_documents($filter = array())
	{
        $id_filter = '';
		$user_id_filter = '';
		$order_id_filter = '';
		$contract_id_filter = '';
        $client_visible_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['contract_id']))
            $contract_id_filter = $this->db->placehold("AND contract_id IN (?@)", array_map('intval', (array)$filter['contract_id']));
        
        if (isset($filter['client_visible']))
            $client_visible_filter = $this->db->placehold("AND client_visible = ?", (int)$filter['client_visible']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __documents
            WHERE 1
                $id_filter
        		$user_id_filter
        		$order_id_filter
        		$contract_id_filter
                $client_visible_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_document($document)
    {
        $document = (array)$document;
        
        if (isset($document['params']))
            $document['params'] = serialize($document['params']);
        
		$query = $this->db->placehold("
            INSERT INTO __documents SET ?%
        ", $document);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }
    
    public function update_document($id, $document)
    {
        $document = (array)$document;
        
        if (isset($document['params']))
            $document['params'] = serialize($document['params']);
        
		$query = $this->db->placehold("
            UPDATE __documents SET ?% WHERE id = ?
        ", $document, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_document($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __documents WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * Получает последний документ с Кредитным рейтингом
     * @param int $user_id
     */
    public function getLastDocumentCreditRating(int $user_id)
    {
        $query = $this->db->placehold("
            SELECT * 
                FROM __documents
            WHERE 
                user_id = ? 
            AND
                `type` = 'SCORE_CREDIT_RATING'
            ORDER BY id DESC LIMIT 1
        ", $user_id);

        $this->db->query($query);

        if ($result = $this->db->result())
        {
            $result->params = unserialize($result->params);
        }

        return $result;
    }

    /**
     * @param array $filters
     * @param bool $getAll
     * @return array
     */
    private function getDocumentParamsByFilter(array $filters = [], bool $getAll = true): array
    {
        $idCond = '';
        $typeCond = '';
        $templateCond = '';
        $nameCond = '';
        $clientVisibleCond = '';

        if (!empty($filters['id'])) {
            $idCond = $this->db->placehold('AND id IN (?@)', (array) $filters['id']);
        }

        if (!empty($filters['type'])) {
            $typeCond = $this->db->placehold('AND type IN (?@)', (array) $filters['type']);
        }

        if (!empty($filters['template'])) {
            $templateCond = $this->db->placehold('AND template IN (?@)', (array) $filters['template']);
        }

        if (!empty($filters['name'])) {
            $nameCond = $this->db->placehold('AND (name LIKE "%?%"', $filters['name']);
        }

        if (!empty($filters['client_visible'])) {
            $clientVisibleCond = $this->db->placehold('AND client_visible = ?', $filters['client_visible']);
        }

        $query = $this->db->placehold(
            "SELECT * FROM s_document_types 
                WHERE 1
                $idCond
                $typeCond
                $templateCond
                $nameCond
                $clientVisibleCond"
        );
        $this->db->query($query);

        $result = [];
        if ($getAll === false) {
            $result = (array) $this->db->result() ?? [];
        } else {
            $queryResult = $this->db->results() ?? [];
            foreach ($queryResult as $item) {
                $result[$item->type] = (array) $item;
            }
        }

        return $result;
    }

    public function deleteDocument($id, $table)
    {
        $query = $this->db->placehold("
            DELETE FROM $table WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function upload_document(array $data = [])
    {
        $query = $this->db->placehold("
            INSERT INTO __uploaded_documents(`name`,user_id,order_id) values(?,?,?)
        ", $data['name'], (int)$data['user_id'], (int)$data['order_id']);

        $this->db->query($query);
    }

    public function get_uploaded_documents(int $user_id, int $order_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __uploaded_documents
            WHERE user_id = ?
            and order_id = ?
                
        ", $user_id, $order_id);
        $this->db->query($query);
        return $this->db->results();
    }

    public function get_uploaded_document_by_id(int $doc_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __uploaded_documents
            WHERE id = ?
            LIMIT 1    
        ", $doc_id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function update_uploaded_document(string $name,int $user, int $order,string $tmpName){
        $query = $this->db->placehold("
            UPDATE __uploaded_documents SET name = ? WHERE user_id = ? and order_id = ? and name = ?
        ", $name, $user,$order,$tmpName);

        $this->db->query($query);

    }

    /**
     * Параметры для формирования поручения на перечисление микрозайма
     * Для займов ИП и ООО
     * @param int $company_order_id
     * @return array
     */
    public function getCompanyOrderAssignmentParams(int $company_order_id): array
    {
        $company_order = $this->company_orders->getItem($company_order_id);
        $user_id = (int)$company_order->user_id;
        $user = $this->users->get_user($user_id);
        $ogrnip = $this->user_data->read($user_id, 'ogrnip');
        $passport = Helpers::splitPassportSerial((string) $user->passport_serial);

        return [
            'lastname' => $user->lastname,
            'patronymic' => $user->patronymic,
            'firstname' => $user->firstname,
            'birth' => $user->birth,
            'passport_serial' => $passport['serial'],
            'passport_number' => $passport['number'],
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'reg_address' => implode(', ', array_filter([
                $user->Regindex,
                $user->Regregion,
                $user->Regcity,
                $user->Regstreet,
                'д.' . $user->Reghousing . ($user->Regbuilding ? ', стр.' . $user->Regbuilding : '') . ($user->Regroom ? ', кв.' . $user->Regroom : '')
            ], function ($item) {
                return !empty($item);
            })),
            'phone_mobile' => $user->phone_mobile,
            'ogrnip' => $ogrnip,
            'inn' => $user->inn,
            'bank_name' => $company_order->bank_name,
            'bank_place' => $company_order->bank_place,
            'bank_cor_wallet' => $company_order->bank_cor_wallet,
            'bank_bik' => $company_order->bank_bik,
            'bank_user_wallet' => $company_order->bank_user_wallet,
        ];
    }

    /**
     * Возвращает данные для арбитражного соглашения
     * @param Object $user
     * @param int $order_id
     * @param string|null $sms
     * @param string|null $sign_date
     * @return array
     * @throws Exception
     */
    public function getArbitrationAgreementParams(Object $user, int $order_id, ?string $sms = null, ?string $sign_date = null): array
    {
        $order = $this->orders->get_order($order_id);
        $organization = $this->organizations->get_organization($order->organization_id);

        $loan_type = '';
        if ($order->loan_type === 'PDL') {
            $loan_type = 'микрозайма';
        } elseif ($order->loan_type === 'IL') {
            $loan_type = 'потребительского займа';
        }

        $user_fakt_address = "{$user->Faktindex} {$user->Faktregion}, {$user->Faktcity}, ".
            "{$user->Faktstreet} ул, д. {$user->Fakthousing}, кв. {$user->Faktroom}";

        $contract = $this->contracts->get_contract_by_params(['order_id' => $order_id]);
        if (empty($contract) || empty($contract->number)) {
            throw new Exception('Не найден номер договора (zaim_number) для заявки');
        }
        $user_balance = $this->users->get_user_balance_for_order($user->id, $contract->number);

        // Даты просрочки
        $paymentDate = new DateTime($user_balance->payment_date);
        $firstOverdueDate = (clone $paymentDate)->modify('+1 day');

        $passportSplit = Helpers::splitPassportSerial((string) $user->passport_serial);

        return [
            // Данные пользователя
            'full_name' => $this->helpers::getFIO($user),
            'short_name' => $this->helpers::getShortFIO($user),
            'fakt_address' => $user_fakt_address,
            'birth_place' => $user->birth_place,
            'birth_date' => $user->birth,
            'phone_mobile' => $user->phone_mobile,
            'phone' => $user->phone_mobile,
            'email' => $user->email,
            'registration_address' => $user->registration_address,
            'passport_serial' => $passportSplit['serial'],
            'passport_number' => $passportSplit['number'],
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'subdivision_code' => $user->subdivision_code,

            // Данные организации
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'organization_short_name' => $organization->short_name ?? $organization->name ?? null,
            'organization_registry_number' => $organization->registry_number,
            'organization_inn' => $organization->inn,
            'organization_kpp' => $organization->kpp ?? 771401001,
            'organization_ogrn' => $organization->ogrn,
            'organization_address' => $organization->address,
            'organization_director' => $organization->director ?? 'Поздняковa С.В.',
            'organization_email' => $organization->email,
            'organization_phone' => $organization->phone,
            'organization_site' => $organization->site,
            'organization_address_post' => $organization->address,
            'organization_address_req' => $organization->address,
            'plaintiff_name' => 'ООО ПКО "ПРАВОВАЯ ЗАЩИТА"',
            'plaintiff_site' => 'https://pravza.com/',

            // Данные займа/соглашения
            'zaim_date' => $user_balance->zaim_date,
            'sign_date' => $sign_date ?? date('Y-m-d H:i:s'),
            'payment_date' => $user_balance->payment_date,
            // первый день просрочки
            'overdue_date' => $firstOverdueDate,
            // 1-й день просрочки для оферты арбитражного соглашения
            'first_overdue_date' => $firstOverdueDate->format('Y-m-d'),
            // Новый: 11-й день просрочки для арбитражного соглашения
            'eleventh_overdue_date' => (clone $paymentDate)->modify('+11 days')->format('Y-m-d'),
            'zaim_number' => $user_balance->zaim_number,
            'loan_type' => $loan_type,
            'accept_sms' => $sms ?? $order->accept_sms,
        ];
    }
}
