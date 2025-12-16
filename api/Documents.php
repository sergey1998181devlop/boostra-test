<?php

require_once('Simpla.php');
require_once(dirname(dirname(__FILE__)).'/Smarty/libs/Smarty.class.php');
require_once __DIR__ . '/../vendor/autoload.php';


class Documents extends Simpla
{
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
    const UNACCEPTED_AGREEMENT = 'UNACCEPTED_AGREEMENT';
    const CREDIT_RATING_APPLICATION = 'CREDIT_RATING_APPLICATION';
    const SCORE_CREDIT_RATING = 'SCORE_CREDIT_RATING';
    const ZAYAVLENIE_NA_STRAHOVANIYE = 'ZAYAVLENIE_NA_STRAHOVANIYE';
    const CONTRACT_MULTIPOLIS = 'CONTRACT_MULTIPOLIS';
    const DOC_MULTIPOLIS = 'DOC_MULTIPOLIS';
    const PREVIEW_BEZACCEPT = 'PREVIEW_BEZACCEPT';
    const PDN_EXCESSED = 'PDN_EXCESSED';
    const MICRO_ZAIM = 'MICRO_ZAIM';
    const MICRO_ZAIM_FULL = 'MICRO_ZAIM_FULL';

    const PREVIEW_SOGLASIE_OBRABOTKA = 'PREVIEW_SOGLASIE_OBRABOTKA';
    const PREVIEW_IND_USLOVIYA = 'PREVIEW_IND_USLOVIYA';
    const PREVIEW_POLOZHENIE_OBRABOTKI = 'PREVIEW_POLOZHENIE_OBRABOTKI';
    public const PRICINA_OTKAZA_I_REKOMENDACII = 'PRICINA_OTKAZA_I_REKOMENDACII';
    public const ZAYAVLENIYE_OTKAZA_REKOMENDACII = 'ZAYAVLENIYE_OTKAZA_REKOMENDACII';
    public const OFFER_FAST_APPROVAL_SERVICE = 'OFFER_FAST_APPROVAL_SERVICE';
    public const ARBITRATION_AGREEMENT = 'ARBITRATION_AGREEMENT';
    public const ASP_AGREEMENT = 'ASP_AGREEMENT';
    public const PENALTY_CREDIT_DOCTOR = 'PENALTY_CREDIT_DOCTOR';

    public const OFFER_AGREEMENT = 'OFFER_AGREEMENT';
    
    //
    public const COOLDOWN_DAYS = 30;

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
    public const SPRAVKA_O_POGASHENII_ZAIMA = 'SPRAVKA_O_POGASHENII_ZAIMA';
    public const ZAYAVLENIE_NA_VOZVRAT_SREDSTV_ZA_DOP_USLUGI = 'ZAYAVLENIE_NA_VOZVRAT_SREDSTV_ZA_DOP_USLUGI';
    public const ZAYAVLENIE_NA_SKIDKU_NA_DOP_USLUGI = 'ZAYAVLENIE_NA_SKIDKU_NA_DOP_USLUGI';
    public const ZAYAVLENIE_NA_OTKAZ_OT_DOP_USLUGI = 'ZAYAVLENIE_NA_OTKAZ_OT_DOP_USLUGI';


    public const PAYMENT_DEFERMENT_REJECT = 'PAYMENT_DEFERMENT_REJECT';
    public const PAYMENT_DEFERMENT_APPROVE = 'PAYMENT_DEFERMENT_APPROVE';

    public const PREVIEW_CHDP = 'ZAYAVLENIE_CHDP';
    public const CHDP = 'ZAYAVLENIE_CHDP';
    public const PREVIEW_PDP = 'ZAYAVLENIE_PDP';
    public const PDP = 'ZAYAVLENIE_PDP';
    /**
     * Телемедицина Согласие на виды медицинских вмешательств
     */
    const CONSENT_TELEMEDICINE = 'CONSENT_TELEMEDICINE';

    /**
     * Телемедицина Заявление об акцепте-телемедицина
     */
    const ACCEPT_TELEMEDICINE = 'ACCEPT_TELEMEDICINE';

    const KEY_TEMPLATE = 'template';
    const KEY_NAME = 'name';
    const KEY_CLIENT_VISIBLE = 'client_visible';
    const ORGANIZATION_ID = 'organization_id';

    /**
     * Тип документа заявление КД
     */
    public const CONTRACT_USER_CREDIT_DOCTOR = 'CONTRACT_USER_CREDIT_DOCTOR';
    public const CREDIT_DOCTOR_POLICY = 'CREDIT_DOCTOR_POLICY';
    public const STAR_ORACLE_POLICY = 'STAR_ORACLE_POLICY';
    public const ORDER_FOR_EXECUTION_CREDIT_DOCTOR = 'ORDER_FOR_EXECUTION_CREDIT_DOCTOR';
    public const ORDER_FOR_EXECUTION_STAR_ORACLE = 'ORDER_FOR_EXECUTION_STAR_ORACLE';

    public const CONTRACT_DELETE_USER_CABINET = 'PREVIEW_CONTRACT_DELETE_USER_CABINET';

    public const AGREEMENT_DISAGREEMENT_TO_RECEIVE_KO = 'AGREEMENT_DISAGREEMENT_TO_RECEIVE_KO';

    public const CONTRACT_STAR_ORACLE = 'CONTRACT_STAR_ORACLE';

    /**
     * Поручение на перечисление микрозайма
     * Для заявок ИП и ООО
     */
    public const PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA = 'PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA';

    public const OFFER_ARBITRATION_CESSIONARY = 'OFFER_ARBITRATION_CESSIONARY';

    /**
     * @var array $documentParams
     */
    private $documentParams = [];

    public function create_document($data)
    {
        $documentType         = $data['type'];
        $filters              = [ 'type' => $documentType ];
        $this->documentParams = $this->getDocumentParamsByFilter( $filters );
        $organizationId = empty($data['organization_id'])
            ? $this->documentParams[$documentType][self::ORGANIZATION_ID]
            : $data['organization_id'];

        return $this->add_document( [
            'user_id'         => $data['user_id'] ?? 0,
            'order_id'        => $data['order_id'] ?? 0,
            'contract_number' => $data['contract_number'] ?? '',
            'type'            => $documentType,
            'name'            => $this->documentParams[ $documentType ][ self::KEY_NAME ] . ($data['name_suffix'] ?? ''),
            'template'        => $this->documentParams[ $documentType ][ self::KEY_TEMPLATE ],
            'client_visible'  => $this->documentParams[ $documentType ][ self::KEY_CLIENT_VISIBLE ],
            'params'          => $data['params'],
            'created'         => date( 'Y-m-d H:i:s' ),
            'organization_id' => $organizationId,
        ] );
    }
    public function get_document_params(): array
    {
        return $this->getDocumentParamsByFilter();
    }

    public function get_template(string $type): ?string
    {
        return $this->getDocumentParamsByFilter(['type' =>$type], false)[self::KEY_TEMPLATE] ?? null;
    }

    public function get_document_param($type)
    {
    	return $this->getDocumentParamsByFilter(['type' => $type], false) ?? null;
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
        $contract_number_filter = '';
		$client_visible_filter = '';
        $doc_ready_filter = '';
        $doc_created_filter = '';
        $exclude_older_than_days = '';
        $type_filter = '';
        $keyword_filter = '';
        $not_types_filter = '';
        $filestorage_uid_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['contract_id']))
            $contract_id_filter = $this->db->placehold("AND contract_id IN (?@)", array_map('intval', (array)$filter['contract_id']));

        if (!empty($filter['contract_number'])){

            $contract_number = array_filter(
                (array)$filter['contract_number'],
                static function( $val ){
                    return (bool)preg_match( "@^\S{0,4}\d{1,2}-\d{5,10}$@", $val );
                }
            );

            if( empty( $contract_number ) ){
                throw new \Exception( 'Contract number filter did not pass validation' );
            }

            $contract_number_filter = $this->db->placehold( "AND contract_number IN (?@)", $contract_number );
        }

        if (!empty($filter['filter_not_ids'])) {
            $not_types_filter = $this->db->placehold("AND id NOT IN (?@)", $filter['filter_not_ids']);
        }

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", $filter['type']);

        if (!empty($filter['filter_not_types'])) {
            $not_types_filter = $this->db->placehold("AND type NOT IN (?@)", $filter['filter_not_types']);
        }
        
        if (isset($filter['client_visible']))
            $client_visible_filter = $this->db->placehold("AND client_visible = ?", (int)$filter['client_visible']);
        
        if (isset($filter['ready'])) {
            $doc_ready_filter = $this->db->placehold("AND ready = ?", (int)$filter['ready']);
        }
        if (isset($filter['created'])) {
            $doc_created_filter = $this->db->placehold("AND created > ?", $filter['created']);
        }

        // скрываем после n дней
        if (isset($filter['exclude_older_than_days'])) {
            $exclude_older_than_days = $this->db->placehold(
                "AND created >= DATE_SUB(NOW(), INTERVAL ? DAY)",
                (int)$filter['exclude_older_than_days']
            );

        }
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (!empty($filter['order'])) {
            $sql_order_by = $filter['order'] . ' ';
        } else {
            $sql_order_by = 'id ASC ';
        }

        if (array_key_exists('filestorage_uid', $filter)) {
            if (empty($filter['filestorage_uid'])) {
                $filestorage_uid_filter = $this->db->placehold("AND filestorage_uid = ''");
            } elseif (is_array($filter['filestorage_uid'])) {
                $filestorage_uid_filter = $this->db->placehold("AND filestorage_uid IN (?@)", $filter['filestorage_uid']);
            } else {
                $filestorage_uid_filter = $this->db->placehold("AND filestorage_uid = ?", $filter['filestorage_uid']);
            }
        }

        $query = $this->db->placehold("
            SELECT * 
            FROM __documents
            WHERE 1
                $id_filter
        		$user_id_filter
        		$order_id_filter
        		$contract_id_filter
                $contract_number_filter
                $client_visible_filter
                $doc_ready_filter
                $doc_created_filter
                $exclude_older_than_days
 	            $keyword_filter
                $type_filter
                $not_types_filter
                $filestorage_uid_filter
            ORDER BY $sql_order_by 
            $sql_limit
        ");
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

    /**
     * Возвращает документы по номеру контракта и (опционально) по типу
     * Так как в s_documents у многих документов пустое поле contract_number
     *
     * @param array $params {
     *     @type string|string[]      $contract_number  Номер контракта или массив
     *     @type string|string[]|null $type             Тип документа
     *     @type int|null             $limit
     *     @type int|null             $offset
     * }
     * @return array Массив документов из s_documents с подтянутым contract_number из s_contracts.
     *
     */
    public function get_documents_by_contract(array $params)
    {
        $contractNumbers = (array) $params['contract_number'];
        $contractFilter = $this->db->placehold(
            "AND COALESCE(NULLIF(d.contract_number, ''), c.number) IN (?@)",
            $contractNumbers
        );

        $typeFilter = '';
        if (!empty($params['type'])) {
            $types = (array) $params['type'];
            $typeFilter = $this->db->placehold(
                "AND d.type IN (?@)",
                $types
            );
        }

        $limit  = isset($params['limit'])  ? (int) $params['limit']  : 0;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;

        $limitOffsetSql = '';
        if ($limit > 0) {
            $limitOffsetSql = "LIMIT {$limit}";
            if ($offset > 0) {
                $limitOffsetSql .= " OFFSET {$offset}";
            }
        }

        $query = $this->db->placehold("
            SELECT
                d.id,
                d.user_id,
                d.order_id,
                COALESCE(NULLIF(d.contract_number, ''), c.number) AS contract_number,
                d.type,
                d.name,
                d.template,
                d.client_visible,
                d.params,
                d.created,
                d.sent_1c,
                d.sent_date,
                d.organization_id,
                d.filestorage_uid
            FROM __documents AS d
            LEFT JOIN __contracts AS c ON d.order_id = c.order_id
            WHERE 1
                {$contractFilter}
                {$typeFilter}
            ORDER BY d.id
            {$limitOffsetSql}
        ");

        $this->db->query($query);
        $results = $this->db->results();

        if ($results && $limit === 1) {
            return $results[0];
        }

        return $results;
    }


    /**
     * Получает просроченные договора на 119 дней для ШКД
     *  @param array $params {
     *      @type int|null $limit
     *      @type int|null $offset
     * }
     * @return array|false
     */
    public function get_overdue_penalty_credit_doctor_documents($params)
    {
        $limit  = isset($params['limit'])  ? (int) $params['limit']  : 100;
        $offset = isset($params['offset']) ? (int) $params['offset'] : 0;
        $days_overdue = isset($params['days_overdue']) ? (int) $params['days_overdue'] : 30;

        $this->db->query("                                                                                                                                                                                                                            
          SELECT ub.*                                                                                                                                                                                                                               
          FROM s_user_balance ub                                                                                                                                                                                                                    
          LEFT JOIN s_documents d ON ub.user_id = d.user_id                                                                                                                                                                                         
              AND d.type = 'arbitration_agreement'                                                                                                                                                                                                  
              AND d.contract_number = ub.zaim_number                                                                                                                                                                                                
          WHERE ub.payment_date < CURDATE()                                                                                                                                                                                                         
            AND (ub.ostatok_od > 0 OR ub.ostatok_percents > 0 OR ub.ostatok_peni > 0)                                                                                                                                                               
            AND d.id IS NULL                                                                                                                                                                                                                        
            AND DATEDIFF(CURDATE(), ub.payment_date) >= ?d                                                                                                                                                                                          
          ORDER BY ub.payment_date ASC                                                                                                                                                                                                              
          LIMIT ?d OFFSET ?d                                                                                                                                                                                                                        
      ", $days_overdue, $limit, $offset);

        return $this->db->results();
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
    
    public function add_document( $document )
    {
        $document = (array)$document;
        
        if( isset( $document['params'] ) ){
            $document['params'] = serialize( $document['params'] );
        }
        
        $this->db->query(
            $this->db->placehold( "INSERT INTO __documents SET ?%", $document )
        );

        return $this->db->insert_id();
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

    public function getLastScoreDocument($user_id)
    {
        $query = $this->db->placehold(
            "SELECT * FROM __documents WHERE user_id = ? AND `type` = '" . $this->db->escape(
                self::SCORE_CREDIT_RATING
            ) . "' ORDER BY id DESC LIMIT 1",
            (int)$user_id
        );
        $this->db->query($query);
        return $this->db->result();
    }

    public function update_personal_data($user)
    {
        $docs = $this->get_documents(['user_id' => [$user->id]]);
        foreach ($docs as $doc)
        {
            if ($doc->type == Documents::UNACCEPTED_AGREEMENT)
                continue;

            $doc->params = (array)$doc->params;
            if (!empty($doc->params['user'])) {
                $doc->params['user'] = $user;
            }
            else {
                foreach ($user as $key => $value) {
                    if (!empty($doc->params[$key]))
                        $doc->params[$key] = $value;
                }
            }

            foreach ($doc->params as $key => &$value) {
                if (substr($key, 0, strlen('fakt_')) == 'fakt_') {
                    $key = str_replace('fakt_', 'Fakt', $key);
                    if (!empty($user->$key))
                        $value = $user->$key;
                }
                elseif (substr($key, 0, strlen('reg_')) == 'reg_') {
                    $key = str_replace('reg_', 'Reg', $key);
                    if (!empty($user->$key))
                        $value = $user->$key;
                }
            }

            if (!empty($doc->params['passport_number'])) {
                $doc->params['passport_number'] = $this->helpers->splitPassportSerial($user->passport_serial)['number'];
                $doc->params['passport_serial'] = $this->helpers->splitPassportSerial($user->passport_serial)['serial'];
            }

            if (!empty($doc->params['gender'])) {
                $doc->params['gender'] = ($doc->params['gender'] == 'male') ? 'муж.' : 'жен.';
            }

            if (!empty($doc->params['full_name'])) {
                $doc->params['full_name'] = $user->lastname . ' ' . $user->firstname;
                if (!empty($user->patronymic))
                    $doc->params['full_name'] .= ' ' . $user->patronymic;
            }

            if (!empty($doc->params['short_name'])) {
                $doc->params['short_name'] = $user->lastname . ' ' . mb_substr($user->firstname, 0, 1) . '.';
                if (!empty($user->patronymic))
                    $doc->params['short_name'] .= ' ' . mb_substr($user->patronymic, 0, 1) . '.';
            }

            $this->update_document($doc->id, [
                'params' => $doc->params
            ]);

            if (!empty($doc->filestorage_uid)) {
                $file_url =  $this->config->root_url . '/document/' . $user->id . '/' . $doc->id;
                $storage_uid = $this->filestorage->upload_file($file_url);
                $this->update_document($doc->id, [
                   'filestorage_uid' => $storage_uid
                ]);
            }
        }
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

    public function download_zip($user_id){
        session_start();

        if (!$user_id || !is_int($user_id)) {
            throw new \RuntimeException('Invalid user ID provided');
        }

        $smarty = new Smarty();
        $smarty->setTemplateDir(dirname(__DIR__) .'/design/boostra_mini_norm/html/pdf/');
        $smarty->compile_check = false;
        $zip = new ZipArchive();
        $zip_name = 'documents_' . date('Ymd') . '.zip';
        if ($zip->open($zip_name, ZipArchive::CREATE) !== true) {
            die('Cannot create zip archive');
        }

        $pdf_dir = dirname(__DIR__) . '/files/document';
        if (!file_exists($pdf_dir)) {
            if (!mkdir($pdf_dir) && !is_dir($pdf_dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $pdf_dir));
            }
        }

        if ($this->documents->get_documents(['type' => [Documents::DOC_MULTIPOLIS], 'user_id' => $user_id])) {
            $offerta_file =  dirname(__DIR__) .'/files/docs/offerta_fin_tech.pdf';
            if (file_exists($offerta_file)) {
                $zip->addFile($offerta_file, 'offerta_fin_tech.pdf');
            }
        }

        $user_balance = $this->users->get_user_balance($user_id);
        $uid_docs = [];
        if ($docs = $this->soap->get_documents($user_balance->zaim_number))
        {
            foreach ($docs as $doc)
            {
                $uid_doc = new StdClass();
                $uid_doc->name = $doc->ТипДокумента;
                $uid_doc->uid = $doc->УИДХранилища;
                $uid_doc->hide = $doc->НеОтображать;

                if ($uid_doc->name == 'Частота взаимодействия')
                    $uid_doc->hide = 1;

                $uid_docs[] = $uid_doc;
            }
        }

        $documents_dir = dirname(__DIR__) . '/boostra/files/contracts/documents';
        if (!file_exists($documents_dir)) {
            if (!mkdir($documents_dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $documents_dir));
            }
        }
        $files = scandir($documents_dir);

        $added_files = [];

        foreach ($uid_docs as $uid_doc) {
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $filename = pathinfo($file, PATHINFO_FILENAME);
                if ($filename != $uid_doc->uid) {
                    continue;
                }
                $new_filename = $uid_doc->name .  '.pdf';
                if (in_array($new_filename, $added_files)) {
                    continue;
                }
                $zip->addFile($documents_dir . '/' . $file, $new_filename);
                $added_files[] = $new_filename;
            }
        }

        $documents = $this->documents->get_documents([
            'user_id' => $user_id
        ]);

        foreach ($documents as $document) {

            // исключаем док с ШКД из архива клиента
            if (stripos($document->name, 'ШКД') !== false) {
                continue;
            }

            $template_file = $document->template;
            $template_path = $smarty->getTemplateDir(0) . $template_file;
            if (!file_exists($template_path)) {
                echo 'Template file not found: ' . $template_file;
                continue;
            }
            $smarty->assign((array)$document->params);
            $template = $smarty->fetch($template_file);
            $dompdf = new Dompdf\Dompdf(['defaultFont' => 'DejaVu Serif']);
            $dompdf->loadHtml($template);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = $document->name.'_' .rand(100000, 999999) . '.pdf';
            $pdf_path = $pdf_dir . '/' . $filename;
            file_put_contents($pdf_path, $dompdf->output());
            $zip->addFile($pdf_path, $filename);
        }
        $zip->close();
        ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $zip_name);
        header('Content-Length: ' . filesize($zip_name));
        readfile($zip_name);
        unlink($zip_name);
        exit();
    }


    public function get_uploaded_documents(int $file_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __uploaded_documents
            WHERE id = ?    
        ", $file_id);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получает документы загруженные в CRM для конкретного пользователя
     * @param int $user_id
     * @return array|false
     */
    public function get_uploaded_documents_by_user(int $user_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __uploaded_documents
            WHERE `user_id` = ?    
        ", $user_id);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получает документы загруженные в CRM для конкретной заявки
     * @param int $order_id
     * @return array|false
     */
    public function get_uploaded_documents_by_order(int $order_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __uploaded_documents
            WHERE `order_id` = ?    
        ", $order_id);
        $this->db->query($query);
        return $this->db->results();
    }

    public function delete_file(int $file_id){

        $query = $this->db->placehold("
            DELETE FROM __files WHERE id = ?
        ", $file_id);
        $this->db->query($query);
    }

    /**
     * Saves document into the storage
     *  and updates its storage UID
     *
     * @param $document_id
     *
     * @return void
     */
    public function saveToStorage( $document_id, $user_id = null )
    {
        $file_url    = $this->config->root_url . '/document/' . ( $user_id ?? $this->user->id ) . '/' . $document_id;
        $storage_uid = $this->filestorage->upload_file( $file_url, 15 );

        // Update document storage UID
        $this->documents->update_document(
            $document_id,
            [ 'filestorage_uid' => $storage_uid, ]
        );
    }

    /**
     * Search for every user document of the searched type with searched params
     *      return the first matched document or false
     *
     * @param int    $user_id
     * @param string $type Contant defined in the Documents::class
     * @param array  $search_params
     *
     * @return object|false
     * @throws Exception
     */
    public function isDocumentWithParametersExists( int $user_id, string $type, string $contract_number, array $search_params )
    {
        $documents = $this->documents->get_documents([
            'user_id'         => $user_id,
            'type'            => [ $type ],
            'contract_number' => $contract_number,
        ]);

        foreach( $documents as $document ){
            // Intersection of search_params and document_params === search_params
            if( $search_params === array_intersect_assoc( (array) $document->params, $search_params ) ){
                return $document;
            }
        }

        return false;
    }

    /**
     * Генерация параметров для документа об удалении ЛК
     * @param $user
     * @return array
     */
    public static function getParamsForContractDeletedUser($user): array
    {
        list('serial' => $passport_serial, 'number' => $passport_number)  = Users::splitPassportSerial($user->passport_serial);
        return [
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'patronymic' => $user->patronymic,
            'birth' => $user->birth,
            'passport_serial' => $passport_serial,
            'passport_number' => $passport_number,
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
        ];
    }

    public function getDocument($type,$contractNumber) {
        $query = $this->db->placehold("
            SELECT * 
            FROM __documents
            WHERE type = ? 
            AND contract_number = ?
        ", $type,$contractNumber);
        $this->db->query($query);
        return $this->db->result();
    }

    public function convertAmountToString( $num, $units = false, $cents = false )
    {
        $morph = static function($n, $f1, $f2, $f5) {
            $n = abs( (int)$n ) % 100;
            if ($n>10 && $n<20) return $f5;
            $n %= 10;
            if ($n>1 && $n<5) return $f2;
            if ($n==1) return $f1;
            return $f5;
        };


        $nul='ноль';
        $ten=array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        );
        $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
        $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
        $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
        $unit=array( // Units
            array('копейка' ,'копейки' ,'копеек',	 1),
            array('рубль'   ,'рубля'   ,'рублей'    ,0),
            array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
            array('миллион' ,'миллиона','миллионов' ,0),
            array('миллиард','милиарда','миллиардов',0),
        );
        //
        list($rub,$kop) = explode('.',sprintf( "%015.2f", (float)$num ));
        $out = array();
        if ( (int)$rub > 0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (! (int)$v ) continue;
                $uk = count( $unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= $morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;

        // Customize output
        if( $units ){
            $out[] = $morph( (int)$rub, $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        }
        if( $cents ){
            $out[] = $kop.' '.$morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        }

        return trim(preg_replace( '/ {2,}/', ' ', implode( ' ', $out)));
    }

    public function get_last_document($type, $user_id)
    {
        $filter = [
            'type' => [$type],
            'user_id' => $user_id,
            'limit' => 1,
            'order' => 'id DESC',
        ];
        return $this->get_documents($filter);
    }

    public function save_pdf(string $fileBase64, string $baseFilename, string $type): string
    {
        if (file_exists($this->config->root_dir . "files/contracts/{$type}/{$baseFilename}.pdf")) {
            return "{$type}/{$baseFilename}.pdf";
        }

        $content = base64_decode($fileBase64);
        $path = $this->config->root_dir . "files/contracts/{$type}/{$baseFilename}.pdf";
        file_put_contents($path, $content);

        return "{$type}/{$baseFilename}.pdf";
    }
}
