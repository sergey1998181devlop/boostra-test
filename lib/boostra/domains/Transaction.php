<?php

namespace boostra\domains;

use boostra\domains\Transaction\GatewayResponse;
use boostra\domains\Transaction\RequestBody;

/**
 * @property int             $id
 * @property int             $user_id
 * @property int             $order_id
 * @property int             $amount
 * @property int             $sector
 * @property int             $register_id
 * @property string          $contract_number
 * @property string          $reference
 * @property string          $description
 * @property string          $created
 * @property int             $operation
 * @property int             $reason_code
 * @property RequestBody     $body
 * @property GatewayResponse $callback_response
 * @property int             $sms
 * @property int             $prolongation
 * @property int             $insurance_id
 * @property float           $loan_body_summ
 * @property float           $loan_percents_summ
 * @property float           $loan_charge_summ
 * @property float           $loan_peni_summ
 * @property float           $commision_summ
 */
class Transaction extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 'b2p_transactions';
    }
    
    public function init()
    {
        if( strpos( $this->body, 'wakeup' ) !== false || strpos( $this->body, 'unserialize' ) !== false ){
            throw new \Exception( 'Unsafe object unserializing' );
        }
        
        $this->body              = new RequestBody( unserialize( $this->body ) );
        $this->callback_response = new GatewayResponse( simplexml_load_string( $this->callback_response ) );
    }
}