<?php

namespace boostra\services;

use boostra\domains\Card;
use boostra\domains\extraServices\CreditDoctor;
use boostra\domains\extraServices\extraService;
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
        
        $this->credit_doctor = ( new Repository( CreditDoctor::class, Core::instance()->dbAccess ) )
            ->readBatch( $search_params, 'date_added' );
        $this->tv_medical = ( new Repository( TvMedical::class, Core::instance()->dbAccess ) )
            ->readBatch( $search_params, 'date_added' );
        $this->multipolis = ( new Repository( Multipolis::class, Core::instance()->dbAccess ) )
            ->readBatch( $search_params, 'date_added' );
        
        $this->all = array_merge(
            $this->credit_doctor,
            $this->tv_medical,
            $this->multipolis
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
     * @param int          $refund_percent
     * @param Card|null    $card
     *
     * @return void
     * @throws \Exception
     */
    public function refund( extraService $service, int $refund_percent = 100, Card $card = null ): void
    {
        if( $service->payment_method !== 'B2P' ){
            throw new \Exception( 'Возврат для Тинькофф банка не доступен' );
        }
        
        /** @var GatewayResponse $gateway_response */
        $refund_percent   = $service->discount_refunded ? 50 : $refund_percent;
        $amount           = round( $service->amount * $refund_percent / 100, 2 );
        $gateway_response = Core::instance()->best2pay->refundExtraService( $service, $amount, $card );
        $success          = $gateway_response->state === 'APPROVED';
        
        Core::instance()->changelogs->add_changelog( [
            'manager_id' => 0,
            'created'    => date( 'Y-m-d H:i:s' ),
            'type'       => $service->return_slug,
            'old_values' => serialize( $success ? $service : [] ),
            'new_values' => serialize( $success ? [ 'amount' => $amount ] : [ 'Не удалось выполнить операцию' ] ),
            'order_id'   => $service->order_id,
            'user_id'    => $service->user_id,
        ] );
        
        if( ! $success ){
            throw new \Exception( "Не удалось выполнить операцию возврата. Ошибка: {$gateway_response->description} Код: {$gateway_response->code}" );
        }
        
        switch( $service->slug ):
            case 'credit_doctor' : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR; break;
            case 'multipolis'    : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS;    break;
            case 'tv_medical'    : $refund_type = Core::instance()->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL;    break;
        endswitch;
    
        // добавим задание на отправку чека
        Core::instance()->receipts->addItem( [
            'user_id'         => $service->user_id,
            'order_id'        => $service->order_id,
            'transaction_id'  => $service->return_transaction_id,
            'amount'          => $amount,
            'payment_method'  => Core::instance()->orders::PAYMENT_METHOD_B2P,
            'payment_type'    => $refund_type,
            'organization_id' => $service->organization_id,
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
    
    public function isAlreadyRefunded( string $extra_service_name )
    {
        return ( $this->{$extra_service_name}->discount_refunded && $this->{$extra_service_name}->refund_amount === 50 ) ||
               ( $this->{$extra_service_name}->fully_refunded    && $this->{$extra_service_name}->refund_amount === 100 );
    }
}