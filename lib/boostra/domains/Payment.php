<?php

namespace boostra\domains;

use boostra\domains\Transaction\GatewayResponse;
use boostra\domains\Transaction\RequestBody;

/**
 * @property  int             $id
 * @property  int             $user_id
 * @property  int             $order_id
 * @property  string          $contract_number
 * @property  int             $card_id
 * @property  int             $amount
 * @property  int             $insure
 * @property  int             $fee
 * @property  int             $body_summ
 * @property  int             $percents_summ
 * @property  int             $prolongation
 * @property  int             $calc_percents
 * @property  string          $asp
 * @property  string          $created
 * @property  string          $payment_type
 * @property  int             $sector
 * @property  int             $register_id
 * @property  int             $operation_id
 * @property  string          $reference
 * @property  string          $description
 * @property  string          $payment_link
 * @property  int             $reason_code
 * @property  RequestBody     $body
 * @property  GatewayResponse $callback_response
 * @property  int             $sent
 * @property  string          $send_date
 * @property  int             $recurrent_id
 */
class Payment extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 'b2p_payments';
    }
    
    public function init()
    {
        if( strpos( $this->body, 'wakeup' ) !== false || strpos( $this->body, 'unserialize' ) !== false ){
            throw new \Exception( 'Unsafe object unserializing' );
        }
        
        $this->body              = new RequestBody( unserialize( $this->body ) );
        $this->callback_response = new GatewayResponse( simplexml_load_string( unserialize( $this->callback_response ) ) );
    }
}