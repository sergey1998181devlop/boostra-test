<?php

namespace boostra\services;

use boostra\domains\Card;
use boostra\domains\extraServices\CreditDoctor;
use boostra\domains\extraServices\extraService;
use boostra\domains\extraServices\StarOracle;
use boostra\domains\extraServices\SafeDeal;
use boostra\domains\extraServices\TvMedical;
use boostra\domains\extraServices\Multipolis;
use boostra\domains\Transaction\GatewayResponse;
use boostra\repositories\Repository;

class extraServices extends BaseService{
    
    /**
     * @var CreditDoctor[]
     */
    public $credit_doctor;
    /**
     * @var TvMedical[]
     */
    public $tv_medical;
    /**
     * @var Multipolis[]
     */
    public $multipolis;
    /**
     * @var StarOracle[]
     */
    public $star_oracle;
    /**
     * @var SafeDeal[]
     */
    public $safe_deal;
    
    /**
     * @var extraService[]
     */
    public $all;
    
    /**
     * @throws \Exception
     */
    protected function init( $search_params = null )
    {
        if( ! $search_params ){
            return;
        }
        
        $this->credit_doctor = ( new Repository( CreditDoctor::class ) )
            ->readBatch( $search_params, 'date_added' );
        $this->tv_medical = ( new Repository( TvMedical::class ) )
            ->readBatch( $search_params, 'date_added' );
        $this->multipolis = ( new Repository( Multipolis::class ) )
            ->readBatch( $search_params, 'date_added' );
        $this->star_oracle = ( new Repository( StarOracle::class ) )
            ->readBatch( $search_params, 'date_added' );
        $this->safe_deal = ( new Repository( SafeDeal::class ) )
            ->readBatch( $search_params, 'date_added' );
        
        $this->all = array_merge(
            $this->credit_doctor,
            $this->star_oracle,
            $this->tv_medical,
            $this->multipolis,
            $this->safe_deal
        );
    }
    
    /**
     * @param string      $type
     * @param string|null $loan_number
     * @param int|null    $id
     *
     * @return extraService
     */
    public function searchExtraService( string $type, string $loan_number = null, int $id = null ): ?extraService
    {
        /** @var extraService[] $services */
        $services = $this->$type ?? $this->all;
        
        if( $loan_number ){
            $services = array_filter( $services, static function($service) use ( $loan_number ){
                return $service->loan->number === $loan_number;
            });
        }
        
        if( $id ){
            $services = array_filter( $services, static function($service) use ( $id ){
                return $service->id === $id;
            });
        }
        
        return $services ? current( $services ) : null;
    }
    
    /**
     * @param extraService[] $services
     *
     * @return void
     * @throws \Exception
     */
    public function groupServicesByLoan( array $services ): array
    {
        $tmp = [];
        foreach( $services as $service ){
            $tmp[ $service->loan->number ] = $service;
        }
        
        return $tmp;
    }

    /**
     * @param extraService $service
     * @param int $refund_percent
     * @param Card|null $card
     * @param string $refund_method
     * @param int|null $sbp_account_id
     * @return void
     * @throws \Exception
     */
    public function refund( extraService $service, int $refund_percent = 100, Card $card = null, string $refund_method = 'card', ?int $sbp_account_id = null ): void
    {
        if( $service->isRefunded() ){
            throw new \Exception( 'Услуга уже возвращена.' );
        }
        
        if( $service->payment_method !== 'B2P' ){
            throw new \Exception( 'Возврат для Тинькофф банка не доступен' );
        }
        
        if( $this->hasErrorInRefundTransaction( $service ) ){
            throw new \Exception('Транзакция в работе. В данный момент возврат по этой услуге невозможен');
        }
        
        /** @var GatewayResponse $gateway_response */

        $amount = round( $service->amount_left * $refund_percent / 100);

        switch ($refund_method) {
            case 'sbp':
                $gateway_response = $this->executeSbpRefund($service, $amount, $sbp_account_id);
                break;
            case 'card':
                $gateway_response = $this->executeCardRefund($service, $amount, $card);
                break;
            default:
                throw new \Exception('Не указан способ возврата. Допустимые значения: card, sbp.');
        }
        $success          = $gateway_response->state === 'APPROVED';
        
        Core::instance()->changelogs->add_changelog( [
            'manager_id' => $service->return_by_manager_id,
            'created'    => date( 'Y-m-d H:i:s' ),
            'type'       => $service->return_slug,
            'old_values' => $service->id,
            'new_values' => serialize( $success ? [ 'amount' => $amount ] : [ 'Не удалось выполнить операцию' ] ),
            'order_id'   => $service->order_id,
            'user_id'    => $service->user_id,
            'file_id'    => $gateway_response->return_transaction_id,
        ] );
        
        if( ! $success ){
            throw new \Exception( "Не удалось выполнить операцию возврата. Ошибка: {$gateway_response->description} Код: {$gateway_response->code}" );
        }
        
        switch( $service->slug ):
            case 'credit_doctor' :
                $refund_type = $service->is_penalty ? Core::instance()->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR : Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR;
                break;
            case 'multipolis'    : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS;    break;
            case 'tv_medical'    : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL;    break;
            case 'star_oracle'   : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE;    break;
            case 'safe_deal'     : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL;    break;
        endswitch;

        $organization_id = $service->organization_id;

        if (in_array(
            $refund_type,
            [
                Core::instance()->receipts::PAYMENT_TYPE_CREDIT_DOCTOR,
                Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR,
                Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE,
            ],
            true
        )) {
            $organization_id = Core::instance()->receipts::ORGANIZATION_FINTEHMARKET;
        }
    
        // добавим задание на отправку чека
        Core::instance()->receipts->addItem( [
            'user_id'         => $service->user_id,
            'order_id'        => $service->order_id,
            'transaction_id'  => $service->return_transaction_id,
            'amount'          => $amount,
            'payment_method'  => Core::instance()->orders::PAYMENT_METHOD_B2P,
            'payment_type'    => $refund_type,
            'organization_id' => $organization_id,
            'description'     => Core::instance()->receipts::PAYMENT_DESCRIPTIONS[ $refund_type ],
        ] );
    }
    
    /**
     * @param string       $appeal_doc_type
     * @param string       $asp_code
     * @param string       $loan_number
     * @param extraService $service
     *
     * @return void
     * @throws \Exception
     */
    public function addAspCodeToAppealDocument( $appeal_doc_type, $asp_code, $loan_number, extraService $service )
    {
        $existing_document = Core::instance()->documents->get_documents( [
            'user_id'         => $service->user->id,
            'type'            => [ $appeal_doc_type ],
            'contract_number' => $loan_number,
        ] )[0];
        
        $existing_document->params['asp'] = (object)[
            'code'    => $asp_code,
            'created' => date('d.m.Y'),
        ];
        
        Core::instance()->documents->update_document( $existing_document->id, $existing_document);
    }
    
    public function isRefunded( string $extra_service_name ): bool
    {
        return ( $this->{$extra_service_name}->discount_refunded && $this->{$extra_service_name}->refund_amount === 50 ) ||
               ( $this->{$extra_service_name}->fully_refunded    && $this->{$extra_service_name}->refund_amount === 100 );
    }
    
    /**
     * @param extraService $service
     *
     * @return bool
     */
    private function hasErrorInRefundTransaction( extraService $service ): bool
    {
        /** @var \boostra\domains\Transaction $refund_transaction */
        $refund_transaction = ( new Repository( \boostra\domains\Transaction::class ) )
            ->read([
                'user_id'  => $service->user->id,
                'order_id' => $service->order->id,
                'type'     => $service->return_transaction_slug,
                'state'    => [ 'in', [ 'ERROR', 'TIMEOUT' ] ],
            ]);
        
        return ! empty( $refund_transaction->id ) ;
    }

    private function executeSbpRefund(extraService $service, float $amount, ?int $sbp_account_id): GatewayResponse
    {
        Core::instance()->best2pay->logging(
            __METHOD__,
            '',
            [
                'message' => 'Выбран СБП-возврат',
                'service_id' => $service->id,
                'order_id' => $service->order_id,
                'refund_method' => 'sbp',
                'amount' => $amount,
                'sbp_account_id' => $sbp_account_id,
            ],
            [],
            'extra_services_refund.txt'
        );

        return Core::instance()->best2pay->refundExtraServiceToSbp($service, $amount, $sbp_account_id);
    }

    private function executeCardRefund(extraService $service, float $amount, ?Card $card): GatewayResponse
    {
        if (!$card) {
            throw new \Exception('Для возврата на карту необходимо указать карту пользователя.');
        }
        return Core::instance()->best2pay->refundExtraService($service, $amount, $card);
    }
}
