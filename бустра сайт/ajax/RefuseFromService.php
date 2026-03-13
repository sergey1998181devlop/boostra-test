<?php

use boostra\domains\extraServices\extraService;
use boostra\services\extraServices;

require_once './ajaxController.php';

/**
 * Contains validation rules and handlers
 */
class RefuseFromService extends ajaxController{
    
    /**
     * @var extraService
     */
    private $service;
    
    /**
     * @var extraServices
     */
    private $services;
    private $refund_amount;
    private $appeal_doc_type;
    private $extra_service_name;
    private $loan_number;
    private $asp_code;
    
    public function actions(): array
    {
        return [
            'prepare_docs' => [
                'amount'      => 'integer',
                'loan_number' => '@^\S{0,4}\d{1,2}-\d{5,10}$@',
                'service'     => [ 'multipolis', 'credit_doctor', 'tv_medical' ],
            ],
            'send_asp' => [
                'amount'      => 'integer',
                'loan_number' => '@^\S{0,4}\d{1,2}-\d{5,10}$@',
                'service'     => [ 'multipolis', 'credit_doctor', 'tv_medical' ],
            ],
            'confirm_asp' => [
                'asp_code'    => 'integer',
                'amount'      => 'integer',
                'loan_number' => '@^\S{0,4}\d{1,2}-\d{5,10}$@',
                'service'     => [ 'multipolis', 'credit_doctor', 'tv_medical' ],
            ],
        ];
    }
    
    /**
     * Init properties depends on input data
     *
     * @return void
     * @throws Exception
     */
    protected function init(): void
    {
        if( isset( $this->data['amount'] ) ){
            $this->refund_amount = $this->data['amount'];
            $this->appeal_doc_type = $this->data['amount'] === 100
                ? Documents::ZAYAVLENIE_NA_VOZVRAT_SREDSTV_ZA_DOP_USLUGI
                : Documents::ZAYAVLENIE_NA_SKIDKU_NA_DOP_USLUGI;
        }
        
        if( isset( $this->data['service'] ) ){
            $this->extra_service_name = $this->data['service'];
        }
        
        if( isset( $this->data['asp_code'] ) ){
            $this->asp_code = $this->data['asp_code'];
        }
        
        if( isset( $this->data['loan_number'] ) ){
            $this->loan_number = $this->data['loan_number'];
        }
        
        $this->services = new extraServices( [
            'user_id'                     => $this->user->id,
            'status'                      => 'SUCCESS',
            'datediff(NOW(), date_added)' => [ '<=', 16, 'function' ],
        ] );
        
        $this->service = $this->services
            ->searchExtraService(
                $this->extra_service_name,
                $this->loan_number
            );
        
        if( ! $this->service instanceof extraService ){
            throw new \Exception("Не удалось найти дополнительную услугу типа '{$this->extra_service_name}' для займа '$this->loan_number'");
        }
    }
    
    /**
     * Handler for action === 'prepare_docs'
     *
     * @return array
     * @throws Exception
     */
    public function actionPrepareDocs(): array
    {
        /** Create document if not exists **/
        if( $this->service->isAlreadyRefunded() ){
            throw new \Exception( 'Услуга уже возвращена.' );
        }
        
        /** Preparing data */
        $this->message = 'Документы подготовлены. Подпишите документы СМС-кодом.';
        
        /** Check for document existing  */
        $existing_document = $this->documents->isDocumentWithParametersExists(
            $this->user->id,
            $this->appeal_doc_type,
            $this->loan_number,
            [
                'refund_amount'   => $this->refund_amount,
                'service_name'    => $this->extra_service_name,
            ]
        );
        
        if( $existing_document ){
            return [
                'docs_already_exists' => true,
                'document_id'         => $existing_document->id
            ];
        }
        
        $document_id    = $this->documents->create_document( [
            'contract_number' => $this->loan_number,
            'type'            => $this->appeal_doc_type,
            'user_id'         => $this->user->id,
            'order_id'        => $this->service->order_id,
            'name_suffix'     => " ({$this->service->title}) ",
            'params'   => [
                'refund_amount' => $this->refund_amount,
                'service_name'  => $this->extra_service_name,
                'loan' => (object)[
                    'number' => $this->loan_number,
                ],
                'user'    => (object)[
                    'lastname'             => $this->user->lastname,
                    'firstname'            => $this->user->firstname,
                    'patronymic'           => $this->user->patronymic,
                    'birth'                => $this->user->birth,
                    'passport_serial'      => $this->user->passport_serial,
                    'passport_date'        => $this->user->passport_date,
                    'passport_issued'      => $this->user->passport_issued,
                    'registration_address' => $this->user->registration_address,
                    'phone_mobile'         => $this->user->phone_mobile,
                    'email'                => $this->user->email,
                ],
                'service' => $this->service->toObject(),
            ],
        ]);
        
        return ['document_id' => $document_id ];
    }
    
    /**
     * * Handler for action === 'send_asp'
     *
     * @return array
     * @throws Exception
     */
    public function actionSendAsp(): array
    {
        /** Init session */
        ! session_id() && @session_start();
        
        $asp_code = random_int( 1000, 9999 );
        $sms_text = "Ваш АСП код: " . $asp_code;
        
        $sms_id = $this->notify->send_sms(
            $this->user->phone_mobile,
            $sms_text
        );
        
        $_SESSION['asp_code'] = $asp_code;
        
        $this->sms->add_message( [
            'phone'   => $this->user->phone_mobile,
            'message' => $sms_text,
            'send_id' => $sms_id,
            'created' => date( 'Y-m-d H:i:s' ),
        ] );
        
        return [];
    }
    
    /**
     * * Handler for action === 'confirm_asp'
     *
     * @return array
     * @throws Exception
     */
    public function actionConfirmAsp(): array
    {
        /** Init session */
        ! session_id() && @session_start();
        
        if( $_SESSION['asp_code'] !== $this->asp_code ){
            throw new \Exception( 'Введён не верный код' );
        }
        
        if( $this->service->isAlreadyRefunded() ){
            throw new \Exception( 'Услуга уже возвращена.' );
        }
        
        $this->service->return_by_user = 1;
        
        $this->services->refund( $this->service, $this->refund_amount, $this->service->return_card );
        $this->services->addAspCodeToAppealDocument( $this->appeal_doc_type, $this->asp_code, $this->loan_number, $this->service );
        
        $this->message = 'Отказ оформлен, ожидайте возврат денежных средств в течение 3-х рабочих дней.';
        
        return [];
    }
}

new RefuseFromService;