<?php

use api\helpers\DocsHelper;
use api\helpers\UserHelper;
use Aws\S3\S3Client;

require_once 'View.php';
require_once dirname( __DIR__ ) . '/api/CreditRating.php';

class UserDocsView extends View {

    const SOGLASIE_NA_BKI_FINLAB_EXPORT_SALT = 'a1b2c3d4e5f6g7h8i9j0';

    const HIDDEN_1C_DOCS = [
        'Частота взаимодействия',
        'Заявление о предоставление микрозайма',
        'Анкета'
    ];

    public function fetch()
    {
        $this->redirectIfNotLoggedIn();
        
        // Загрузка документа с деталями займа
        if( $this->request->get( 'action' ) === 'details' ){
            $resp = $this->soap->get_loan_details( $this->request->get( 'number' ) );
            header( 'Content-type:application/pdf' );
            echo base64_decode( $resp );
        
            exit;
        }

        if ($this->request->get('action') === 's3_view') {
            $file_path = 'S3/DOC/' . $this->request->get('date') . '/' . $this->request->get('file_name');

            $s3Client = new S3Client(
                [
                    'version' => 'latest',
                    'region' => $this->config->s3['region'],
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key' => $this->config->s3['key'],
                        'secret' => $this->config->s3['secret'],
                    ],
                    'endpoint' => $this->config->s3['endpoint'],
                ]
            );

            try {
                $result = $s3Client->getObject(
                    [
                        'Bucket' => $this->config->s3['Bucket'],
                        'Key' => $file_path,
                    ]
                );

                if (!empty($result['Body'])) {
                    header( 'Content-type:application/pdf' );
                    echo $result['Body'];
                    exit;
                }
            } catch (Exception $exception) {
                header("http/1.0 404 not found");
                $this->logging('get s3 doc', $file_path, [], $exception->getMessage(), date('Y-m-d') . '_error_s3_docs.log');
                exit();
            }

            return false;
        }

        $this->show_unaccepted_agreement_modal();

        // Загрузка документа
        if( $this->request->get( 'action' ) === 'load' ){

            $uid = str_replace( '.pdf', '', $this->request->get( 'uid' ) );
        
            if( $uid === '19e7e23e-4ea3-426f-8f36-86deff750c38' ){
                return false;
            }
        
            if( $file = $this->filestorage->load_document( $uid ) ){
                header( 'Location: ' . $file );
                exit;
            }

            return false;
        }

        // Получение документа по микрозайму
        if( $this->request->get( 'action' ) === 'micro_zaim' ){
            $loan_amount = floatval($this->request->get('loan_amount') ?: 30000);
            $credit_doctor = (bool)$this->request->get('credit_doctor');
            $service = $this->docs->getMicroZaimParamsByUser($this->user, $loan_amount, $credit_doctor);
            $service['pdn'] = $this->users->getExcessedPdn($this->user->id);

            $this->design->assignBulk($service);

            $organization_id = $this->request->get('organization_id') ?: $this->organizations->get_base_organization_id();
            $this->design->assign('organization', $this->organizations->get_organization($organization_id));

            foreach ($service as $key => $value) {
                $this->design->assign($key, $value);
            }

            $tpl = "pdf/micro_zaim_akvarius.tpl";
            
            $this->pdf->create(
                $this->design->fetch($tpl),
                'Заявление о предоставлении микрозайма',
                'Заявление.pdf'
            );
            
            exit;
        }
        // Согласие БКИ
        if( $this->request->get('action') === 'soglasie_na_bki_finlab' ) {
            $user_id = $this->request->get('user_id', 'integer');
            $token = $this->request->get('token', 'string');
            $user = $this->user;

            if ($user_id && $token) {
                $passwd = hash_hmac(
                    'sha256',
                    $user_id,
                    self::SOGLASIE_NA_BKI_FINLAB_EXPORT_SALT
                );

                if ($token !== $passwd) {
                    http_response_code(403);
                    exit;
                }

                $user = $this->users->get_user_by_id($user_id);
            }

            if (empty($user)) {
                http_response_code(400);
                exit;
            }

            $service = $this->docs->getMicroZaimParamsByUser($user, 0);
            $service['user_inn'] = $user->inn;
            $service['subdivision_code'] = $user->subdivision_code;
            $service['birth_place'] = $user->birth_place;

            $this->design->assignBulk($service);
            $tpl = 'pdf/soglasie_na_bki_finlab.tpl';

            $this->pdf->create(
                $this->design->fetch($tpl),
                'Согласие на получение информации из бюро кредитных историй',
                'Согласие БКИ.pdf'
            );

            exit;
        }

        // Соглашение о рекуррентных платежах
        if ($this->request->get('action') === 'soglasie_recurrent') {
            // Получаем организацию пользователя
            $organization_id = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);
            $organization = $this->organizations->get_organization($organization_id);

            // Данные клиента
            $service = $this->docs->getMicroZaimParamsByUser($this->user, 0);

            // Добавляем данные организации
            $service['name'] = $organization->name;
            $service['short_name'] = $organization->name;
            $service['ogrn'] = $organization->ogrn;
            $service['inn'] = $organization->inn;
            $service['director'] = $organization->director;
            $service['site'] = $organization->site;
            $service['lastname'] = $this->user->lastname;
            $service['firstname'] = $this->user->firstname;
            $service['patronymic'] = $this->user->patronymic;
            $service['birthday'] = $this->user->birth;
            $service['passport_code'] = $this->user->subdivision_code;
            $service['registration_address'] = $this->user->registration_address;
            $service['date'] = date('d.m.Y');

            $last_order_obj = $this->orders->get_last_order($this->user->id);
            $last_order = json_decode(json_encode($last_order_obj), true);

            if (!empty($last_order['accept_sms'])) {
                $service['asp'] = $last_order['accept_sms'];
                $service['date'] = $last_order['accept_date'];
            }

            $this->design->assignBulk($service);

            $tpl = 'pdf/preview/soglasie-recurrent.tpl';

            $this->pdf->create(
                $this->design->fetch($tpl),
                'Соглашение о применении регулярных (рекуррентных) платежей',
                'Соглашение о рекуррентных платежах.pdf'
            );

            exit;
        }

        // Получение документа по кред. доктору, рейтингу
        if ($this->request->get('action') == 'additional_service') {

            if((!$user_id = $this->request->get( 'user_id', 'integer' ))){
                $user_id = $this->user->id;
            }

            $last_order_obj = $this->orders->get_last_order($user_id);
            $last_order = json_decode(json_encode($last_order_obj), true);

            $user_uid = $this->users->get_user_uid($last_order['user_id']);
            $credits_history = $this->soap->get_user_credits($user_uid->uid);

            $credit_doctor = $this->credit_doctor->getCreditDoctor((int)$last_order['amount'], empty($credits_history));
            $this->design->assign('credit_doctor_amount', $this->credit_doctor->numberToWords($credit_doctor->price));

            $loan_amount = floatval($this->request->get('loan_amount'));
            $credit_doctor = (bool)$this->request->get('credit_doctor');
            $service = $this->docs->getMicroZaimParamsByUser($this->user, $loan_amount, $credit_doctor);
            $this->design->assignBulk($service);

            $this->pdf->create(
                $this->design->fetch('pdf/application_for_additional_services.tpl'),
                'Заявление о предоставлении дополнительных услуг (работ, товаров)',
                'Заявление о доп услугах.pdf'
            );

            exit;
        }

        // Получение документа по Звездный оракул
        if ($this->request->get('action') == 'additional_service_star_oracle') {
            if ((!$user_id = $this->request->get('user_id', 'integer'))) {
                $user_id = $this->user->id;
            }


            $this->design->assign('star_oracle_amount', 'триста пятьдесят');

            $service = $this->docs->getMicroZaimParamsByUser($this->user, 0);
            $this->design->assignBulk($service);

            $this->pdf->create(
                $this->design->fetch('pdf/application_for_additional_services_star_oracle.tpl'),
                'Заявление о предоставлении дополнительных услуг (работ, товаров)',
                'Заявление о доп услуге ЗО.pdf'
            );
            exit;
        }

        // Получение документа по вита-мед
        if ($this->request->get('action') == 'additional_service_vita-med') {

            $vita_med = $this->tv_medical->getVitaMedById(2);
            $this->design->assign('vita_med_amount', $vita_med->price);

            $loan_amount = floatval($this->request->get('loan_amount'));

            $service = $this->docs->getMicroZaimParamsByUser($this->user, $loan_amount);
            $this->design->assignBulk($service);

            $this->pdf->create(
                $this->design->fetch('pdf/additional_service_vita-med.tpl'),
                'Заявление о предоставлении дополнительных услуг (работ, товаров)',
                'Заявление о доп услугах.pdf'
            );

            exit;
        }

        // Получение документа по кред. доктору, рейтингу, но немного другие(юристы?!)
        if ($this->request->get('action') === 'additional_service_2') {

            $last_order_obj = $this->orders->get_last_order($this->user->id);
            $last_order = json_decode(json_encode($last_order_obj), true);

            $user_uid = $this->users->get_user_uid($last_order['user_id']);
            $credits_history = $this->soap->get_user_credits($user_uid->uid);

            $credit_doctor = $this->credit_doctor->getCreditDoctor((int)$last_order['amount'], empty($credits_history));
            $this->design->assign('credit_doctor_amount', $this->credit_doctor->numberToWords($credit_doctor->price));

            $loan_amount = floatval($this->request->get('loan_amount'));
            $credit_doctor = (bool) $this->request->get('credit_doctor');
            $service = $this->docs->getMicroZaimParamsByUser($this->user, $loan_amount, $credit_doctor);

            $futureDate = strtotime('+30 days');
            $service['date_plus_30_days'] = date('d.m.Y', $futureDate);

            $this->design->assignBulk($service);

            $this->pdf->create(
                $this->design->fetch('pdf/application_for_additional_services_2.tpl'),
                'Заявление о предоставлении дополнительных услуг (работ, товаров)',
                'Заявление о доп услугах.pdf'
            );

            exit;
        }

        // Арбитражное соглашение
        if ($this->request->get('action') === 'arbitration_agreement') {
            $this->docs->getArbitrationAgreementPdf($this->user, $this->request->get('order_id'));
            

            exit;
        }

        // АСП соглашение
        if ($this->request->get('action') === 'asp_agreement') {
            $this->docs->getAspAgreementPdf($this->user, $this->request->get('order_id'));
            
            exit;
        }

        // Соглашение об акцепте оферты
        if ($this->request->get('action') === 'offer_agreement') {
            $this->docs->getOfferAgreementPdf($this->user, $this->request->get('order_id'));
            
            exit;
        }

        // (ПДН) Уведомление о повышенном риске невыполнения кредитных обязательств
        if( $this->request->get( 'action' ) === 'pdn_excessed' ){

            $service = $this->users->getExcessedPdnDocumentParams($this->user, 0);
            $this->design->assignBulk( $service );

            $this->pdf->create(
                $this->design->fetch( 'pdf/pdn_excessed.tpl' ),
                'Уведомление о повышенном риске невыполнения кредитных обязательств',
                'Уведомление.pdf'
            );

            exit;
        }

        // Оферта о заключении арбитражного соглашения от цессионария
        if ($this->request->get('action') === 'offer_arbitration_cessionary') {
            $this->docs->getOfferArbitrationCessionaryPdf($this->user, $this->request->get('order_id'));
            
            exit;
        }
        
        // Paid loans
        $paid_loan_references = [];

        //  Получение "Общих" документов не относящихся в дизайне к конкретному займу

        $crm_docs = $this->documents->get_documents(
            [
                'user_id' => $this->user->id,
                'type' => [
//                    Documents::CONTRACT_DELETE_USER_CABINET,
//                    Documents::ZAYAVLENIE_NA_VOZVRAT_SREDSTV_ZA_DOP_USLUGI,
                    Documents::PRICINA_OTKAZA_I_REKOMENDACII,
                    Documents::ZAYAVLENIYE_OTKAZA_REKOMENDACII,
//                    Documents::PDN_EXCESSED,
//                    Documents::ZAYAVLENIE_NA_SKIDKU_NA_DOP_USLUGI,
                    Documents::PREVIEW_PORUCHENIE_NA_PERECHISLENIE_MIKROZAJMA,
                    Documents::PENALTY_CREDIT_DOCTOR,
                    Documents::ARBITRATION_AGREEMENT,
                    Documents::ASP_AGREEMENT,
                ],
            ]
        ) ?: [];
        // Дополнительное соглашение о смене персональных данных
        $unacceptedAgreement = $this->documents->get_last_document(Documents::UNACCEPTED_AGREEMENT, $this->user->id);
        if (!empty($unacceptedAgreement)) {
            $crm_docs[] = $unacceptedAgreement;
        }

        $additional_docs = [];
        foreach ($crm_docs as $doc) {
            if (!empty($doc) && $doc->type == Documents::DOC_MULTIPOLIS) {
                $additional_docs[] = (object)[
                    'url'  => $this->config->root_url . '/files/docs/offerta_fin_tech.pdf',
                    'name' => 'Оферта Консьерж сервис',
                ];
                break;
            }
        }

        // Получение конкретных документов относящихся к займам

        $order_docs = [];
        $order_ids = $this->orders->get_order_ids($this->user->loan_history);
        foreach ($order_ids as $order_id)
        {
            $loan_id = $this->orders->get_loan_id($order_id, $this->user->loan_history);
            $order = $this->orders->get_order($order_id);
            $order_docs[$loan_id]['is_closed'] = ($order->status_1c === Orders::ORDER_1C_STATUS_CLOSED);
            $order_docs[$loan_id]['date'] = date('d.m.Y', strtotime($this->orders->get_loan_date($order_id, $this->user->loan_history)));

            $filter_not_types = [
                Documents::CONSENT_TELEMEDICINE,
                Documents::MICRO_ZAIM,
                Documents::MICRO_ZAIM_FULL,
                Documents::ZAYAVLENIE_NA_SKIDKU_NA_DOP_USLUGI,
                Documents::ZAYAVLENIE_NA_OTKAZ_OT_DOP_USLUGI,
                Documents::CREDIT_DOCTOR_POLICY,
                Documents::ORDER_FOR_EXECUTION_CREDIT_DOCTOR,
                Documents::ORDER_FOR_EXECUTION_STAR_ORACLE,
                Documents::CONTRACT_USER_CREDIT_DOCTOR,
                Documents::CONTRACT_STAR_ORACLE,
                Documents::STAR_ORACLE_POLICY,
                Documents::DOC_MULTIPOLIS,
                Documents::ACCEPT_TELEMEDICINE,
                Documents::PENALTY_CREDIT_DOCTOR,
            ];

            $order_docs[$loan_id]['crm'] = $this->documents->get_documents([
                'order_id' => $order_id,
                'filter_not_types' => $filter_not_types,
            ]);
            $order_docs[$loan_id]['crm'] = $this->filterNotViewablePolices($order_docs[$loan_id]['crm']);

            $order_docs_1c = $this->soap->get_documents($loan_id);
            foreach ($order_docs_1c as $doc) {
                if ($doc->НеОтображать || in_array($doc->ТипДокумента, self::HIDDEN_1C_DOCS, true))
                    continue;
                $order_docs[$loan_id]['1c'][] = (object)[
                    'name' => $doc->ТипДокумента,
                    'uid' => $doc->УИДХранилища,
                ];
            }

            $uploaded_docs = $this->documents->get_uploaded_documents_by_order($order_id);
            foreach ($uploaded_docs as $doc) {
                $order_docs[$loan_id]['uploaded'][] = $doc;
            }
        }

        $asp_zaim_list = $this->users->getZaimListAsp((int)$this->user->id);
        foreach ($asp_zaim_list as $asp_doc)
        {
            $order_docs[$asp_doc->zaim_number]['asp'][] = $asp_doc;
        }

        $user_balances = $this->soap->get_user_balances_array_1c($this->user->uid);
        foreach ($user_balances as $balance_doc)
        {
            if ($balance_doc['НомерЗайма'] == 'Нет открытых договоров')
                continue;
            $order_docs[$balance_doc['НомерЗайма']]['balance'][] = $balance_doc;

            if ($this->orders->get_order_by_1c($balance_doc['Заявка'])->is_user_credit_doctor){
                $this->design->assign(
                    'additional_action_2',
                    true
                );

            }
        }

        // Сортировка документов по дате займа. Сначала показываем свежие
        uasort($order_docs, function ($first, $second) {
            $first_timestamp = strtotime($first['date']);
            $second_timestamp = strtotime($second['date']);
            if ($first_timestamp == $second_timestamp)
                return 0;
            return ($first_timestamp < $second_timestamp) ? 1 : -1;
        });

        if($this->user->uid && empty($order_docs)) {
            $get_contract_bki = $this->soap->get_contract_bki_base64($this->user->uid, $this->user->sms);
            $doc_bki = $this->config->root_url.'/files/contracts/'.$this->documents->save_pdf($get_contract_bki[0]->{'ФайлBase64'}, $this->user->uid, 'BKI');
            
            $this->design->assign('doc_bki', $doc_bki);
        }

        if (!empty($order_docs)) {
            $order_docs = DocsHelper::filterByPattern($order_docs, true);

            foreach ($order_docs as &$order) {
                foreach ($order as $docs) {
                    $order['hidden'] = true;
                    if (is_array($docs) && !empty($docs)) {
                        $order['hidden'] = false;
                        break;
                    }
                }
            }
        }

        $user_balance = $this->users->get_user_balance($this->user->id);
        if (!empty($user_balance->zaim_number) && $user_balance->zaim_number != 'Нет открытых договоров')
            $this->design->assign('current_loan', $user_balance->zaim_number);

        /* Если просрочка по последнему займу более 11 дней показываем документы Арбитражное соглашение и Оферту */
        $has11daysOverdue = UserHelper::userHasOverduedDays($this, $this->user->id, 11);

        if ($has11daysOverdue) {
            $last_order_obj = $this->orders->get_last_order($this->user->id);
            $last_order = json_decode(json_encode($last_order_obj), true);

            $docArbitrationAgreement = $this->config->root_url.'/user/docs?action=arbitration_agreement&order_id='. $last_order['id'];
            $this->design->assign('docArbitrationAgreement', $docArbitrationAgreement);

            $docArbitrationCessionary = $this->config->root_url.'/user/docs?action=offer_arbitration_cessionary&order_id='. $last_order['id'];
            $this->design->assign('docArbitrationCessionary', $docArbitrationCessionary);
        }


        // ШКД
        $is_pcd_enabled = (int)$this->user_data->read($this->user->id, 'IS_ENABLED_PENALTY_CD') ?? 0;

        if (array_filter($crm_docs, fn($doc) => $doc->type === Documents::PENALTY_CREDIT_DOCTOR) || ($is_pcd_enabled == 1)){
            $penaltyCreditDoctor = $this->settings->url_findzen.'/auth.php?fio=Иванов_Иван&app_source=like&external_id='. $this->user->uid;
            $this->design->assign('penaltyCreditDoctorLink', $penaltyCreditDoctor);
        }

        if (
            ($row = $user_balances[0] ?? null) && is_array($row)
            && !empty($row['ПланДата'])
            && ($ts = strtotime($row['ПланДата'])) !== false
            && (float)($row['ОстатокОД'] ?? 0) > 0
            && (int)$is_pcd_enabled !== 1
            && floor((strtotime('today') - $ts) / 86400) > 8
        ) {
            $this->design->assign('penaltyCreditDoctorActivate', true);
            $this->design->assign('loan_number', $row['Заявка'] ?? null);
        }

        $loan_history = DocsHelper::filterByPattern($this->user->loan_history);
        if ($loan_history) {
            $this->design->assign('loan_history', $loan_history);
        }
        $this->design->assign('additional_docs', $additional_docs);
        $this->design->assign('meta_title', 'Документы');

        $this->design->assign('crm_docs', $crm_docs);
        $this->design->assign('order_docs', $order_docs);
        $this->design->assign('paid_loan_references', $paid_loan_references);

        $userHasDocuments = !empty(array_filter($crm_docs)) || !empty($order_docs) || !empty($docs_bki) || !empty($paid_loan_references);

        $this->design->assign('userHasDocuments', $userHasDocuments);
        $this->design->assign('user_id', $this->user->id);

        $this->design->assign('extra_services_to_refuse', $this->getBoughtExtraServices() );

        // Проверяем есть ли у пользователя согласие на рекуррентные платежи
        $has_recurring_consent = false;

        // Проверяем карты
        $cards = $this->best2pay->get_cards(['user_id' => $this->user->id]);
        if (!empty($cards)) {
            foreach ($cards as $card) {
                if (!empty($card->autodebit) && $card->autodebit == 1) {
                    $has_recurring_consent = true;
                    break;
                }
            }
        }

        // Если не нашли в картах, проверяем СБП
        if (!$has_recurring_consent) {
            $sbp_accounts = $this->users->getSbpAccounts($this->user->id);
            if (!empty($sbp_accounts)) {
                foreach ($sbp_accounts as $sbp) {
                    if (!empty($sbp->autodebit) && $sbp->autodebit == 1) {
                        $has_recurring_consent = true;
                        break;
                    }
                }
            }
        }

        $this->design->assign('has_recurring_consent', $has_recurring_consent);

        if (!empty($last_order) && !empty($last_order['1c_status'])
            && $last_order['1c_status'] == $this->orders::ORDER_1C_STATUS_CONFIRMED)
        {
            $this->design->assign('last_order_id', $last_order['id']);
        } else {
            $last_order_obj = $this->orders->get_last_order($user_id);
            $last_order = json_decode(json_encode($last_order_obj), true);

            if (!empty($last_order) && !empty($last_order['1c_status'])
                && $last_order['1c_status'] == $this->orders::ORDER_1C_STATUS_CONFIRMED)
            {
                $this->design->assign('last_order_id', $last_order['id']);
            }
        }

    	return $this->design->fetch('user_docs.tpl');
    }
    
    private function getBoughtExtraServices()
    {
        $output = [];
        $extra_services = new \boostra\services\extraServices( [
            'user_id'                     => $this->user->id,
            'status'                      => 'SUCCESS',
            'datediff(NOW(), date_added)' => [ '<=', 16, 'function' ],
        ] );
        
        $extra_services->credit_doctor = $extra_services->groupServicesByLoan( is_array( $extra_services->credit_doctor ) ? $extra_services->credit_doctor : [ $extra_services->credit_doctor ] );
        $extra_services->multipolis    = $extra_services->groupServicesByLoan( is_array( $extra_services->multipolis ) ?    $extra_services->multipolis : [ $extra_services->multipolis ] );
        $extra_services->tv_medical    = $extra_services->groupServicesByLoan( is_array( $extra_services->tv_medical ) ?    $extra_services->tv_medical : [ $extra_services->tv_medical ] );
        
        $extra_services->all = [
            'credit_doctor' => $extra_services->credit_doctor,
            'tv_medical'    => $extra_services->tv_medical,
            'multipolis'    => $extra_services->multipolis,
        ];
        
        foreach( $extra_services->all as $service_type => &$extra_service ){
            if( ! $extra_service ){
                continue;
            }
            $extra_service['slug']        = current( $extra_service )->slug;
            $extra_service['title']       = current( $extra_service )->title;
            $extra_service['description'] = current( $extra_service )->description;
            $extra_service['checked'] = 'checked';
            $output[ $service_type ]      = $extra_service;
        }
        
        // Mark first one as active using 'checked' attribute
        if( $output ){
            current( $output )['checked'] = 'checked';
        }
        
        return $output;
    }
    
    private function getPaidLoanReference()
    {
        $loan_number = $this->request->get( 'loan_number', 'string' );

        if( ! preg_match( "@^\S{0,4}\d{1,2}-\d{5,10}$@", $this->request->get( 'loan_number', 'string' ) ) ){
            throw new \Exception( 'Loan number did not pass validation' );
        }

        $notification_title = "Уведомление о выплате займа № $loan_number";
        $stored_document    = $this->documents->get_documents( [
            'contract_number' => $loan_number,
            'user_id'         => $this->user->id,
        ] )[0];

        $this->design->assignBulk( $stored_document->params );

        $this->pdf->create(
            $this->design->fetch( 'pdf/crm/loan_paid_reference.tpl' ),
            $notification_title,
            $notification_title . ".pdf",
        );
    }

    /**
     * Gets only paid loans
     *
     * @param array $loans if empty getting it from $this->user->loan_history
     *
     * @return array|mixed
     */
    private function getPaidLoans( $loans = [] )
    {
        $loans = $loans ?: json_decode( $this->user->loan_history );

        return array_filter( $loans, static function( $loan ){
            return ! empty( $loan->close_date );
        } );
    }

    /**
     * Filters loans by given date returns the newest
     *
     * @param string $date
     * @param string $filter_style
     * @param array  $loans if empty getting it from $this->user->loan_history
     *
     * @return array|mixed
     */
    private function filterLoansByCloseDate( $date, $filter_style = 'newer', $loans = [] )
    {
        $loans = $loans ?: json_decode( $this->user->loan_history );

        return array_filter( $loans, static function( $loan ) use ( $date, $filter_style ){
            if( ! isset( $loan->close_date ) ){
                return false;
            }

            if( $filter_style === 'newer' ){
                return strtotime( $loan->close_date ) > strtotime( $date );
            }

            if( $filter_style === 'older' ){
                return strtotime( $loan->close_date ) < strtotime( $date );
            }

            if( $filter_style === 'match' ){
                return strtotime( $loan->close_date ) === strtotime( $date );
            }

            return false;
        } );
    }

    /**
     * Gets a loan from $this->user->loan_history by number like 'Б23-1458224'
     *
     * @param string $loan_number
     * @param array  $loans if empty getting it from $this->user->loan_history
     *
     * @return false|mixed
     */
    private function getLoanByNumber( $loan_number, $loans = [] )
    {
        $loans    = $loans ?: json_decode( $this->user->loan_history, true );
        $loans    = array_values( $loans );
        $loan_key = array_search(
            $loan_number,
            array_column( $loans, 'number' ),
            true
        );

        return $loans[ $loan_key ] ?: false;
    }

    private function saveLoanToDocumentStorage( $loan ){

        $document_id = $this->documents->create_document( [
            'type'               => $this->documents::SPRAVKA_O_POGASHENII_ZAIMA,
            'notification_title' => "Уведомление о выплате займа № $loan->number",
            'user_id'            => $this->user->id,
            'contract_number'    => $loan->number,
            'params'             => array_merge(
                (array) $loan,
                [
                    'accept_data_added_date' => $this->user->accept_data_added_date,
                    'lastname'               => $this->user->lastname,
                    'firstname'              => $this->user->firstname,
                    'patronymic'             => $this->user->patronymic,
                    'birth'                  => $this->user->birth,
                    'registration_address'   => $this->user->registration_address,
                    'gender'                 => $this->user->gender,
                    'root_url'               => $this->user->root_url,
                    'amount_in_string'       => $this->documents->convertAmountToString( $loan->amount ),
                ]
            )
        ] );

        $file_url    = $this->config->root_url . '/document/' . $this->user->id . '/' . $document_id;
        $storage_uid = $this->filestorage->upload_file( $file_url, 15 );

        $this->documents->update_document($document_id, [
            'filestorage_uid' => $storage_uid,
        ]);

        return $document_id;
    }

    private function filterNotViewablePolices($docs): array
    {
        return array_filter($docs, function ($doc) {
            $isPolicy = in_array($doc->type, [
                Documents::CREDIT_DOCTOR_POLICY,
                Documents::STAR_ORACLE_POLICY,
                Documents::DOC_MULTIPOLIS,
                Documents::ACCEPT_TELEMEDICINE,
                Documents::CONTRACT_USER_CREDIT_DOCTOR,
                Documents::CONTRACT_STAR_ORACLE,
            ]);

            if (! $isPolicy) {
                return true;

            }

            $createdTime = strtotime($doc->created);
            $daysDiff = (time() - $createdTime) / 86400;
            return $this->settings->display_policy_days < $daysDiff;
        });
    }
}
