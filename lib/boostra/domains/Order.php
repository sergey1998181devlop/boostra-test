<?php

namespace boostra\domains;

use boostra\domains\Transaction\GatewayResponse;
use boostra\domains\Transaction\RequestBody;

/**
 * @property int $id
 * @property int $contract_id
 * @property int $user_id
 * @property int $manager_id
 * @property int $cdoctor_id
 * @property string $accept_sms
 * @property string $accept_date
 * @property int $accept_try
 * @property string $manager_change_date
 * @property string $call_date
 * @property string $confirm_date
 * @property string $approve_date
 * @property string $reject_date
 * @property string $card_id
 * @property int $delivery_id
 * @property int $delivery_price
 * @property int $payment_method_id
 * @property int $paid
 * @property string $payment_date
 * @property int $closed
 * @property string $date
 * @property string $local_time
 * @property string $uid
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $comment
 * @property int $status
 * @property string $url
 * @property string $payment_details
 * @property string $ip
 * @property int $total_price
 * @property string $note
 * @property int $discount
 * @property int $coupon_discount
 * @property string $coupon_code
 * @property int $separate_delivery
 * @property int $modified
 * @property int $amount
 * @property int $approve_amount
 * @property int $period
 * @property int $selected_period
 * @property int $percent
 * @property int $first_loan
 * @property int $sent_1c
 * @property string $sms
 * @property string $1c_id
 * @property string $1c_status
 * @property string $official_response
 * @property int $reason_id
 * @property string $crm_response
 * @property string $utm_source
 * @property string $utm_medium
 * @property string $utm_campaign
 * @property string $utm_content
 * @property string $utm_term
 * @property string $webmaster_id
 * @property string $click_hash
 * @property string $juicescore_session_id
 * @property int $scorista_sms_sent
 * @property int $have_close_credits
 * @property string $pay_result
 * @property int $razgon
 * @property int $max_amount
 * @property int $stage1
 * @property string $stage1_date
 * @property int $stage2
 * @property string $stage2_date
 * @property int $stage3
 * @property string $stage3_date
 * @property int $stage4
 * @property string $stage4_date
 * @property int $stage5
 * @property string $stage5_date
 * @property string $call_variants
 * @property string $leadgid_postback_date
 * @property int $credit_getted
 * @property int $b2p
 * @property int $autoretry
 * @property int $number_of_signing_errors
 * @property string $insurer
 * @property int $insure_amount
 * @property int $insure_percent
 * @property int $scorista_ball
 * @property int $is_credit_doctor
 * @property int $is_default_way
 * @property int $is_discount_way
 * @property string $payout_grade
 * @property string $leadgen_postback
 * @property string $send_user_info_date
 * @property string $order_uid
 * @property int $complete
 * @property int $promocode
 * @property int $is_user_credit_doctor
 * @property int $not_received_loan_manager_id
 * @property string $not_received_loan_manager_update_date
 * @property int $will_client_receive_loan
 * @property int $pti_loan
 * @property int $pti_order
 * @property int $pdn_notification_shown
 * @property int $additional_service
 *
 *      Dynamic
 * @property Card            $card
 */
class Order extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 's_orders';
    }
    
    public function init()
    {
    
    }
    
    protected function relations(): array
    {
        return [
            'card' => [
                'classname' => Card::class,
                'condition' => [ 'id' => $this->card_id, ],
                'type'      => 'single',
            ],
        ];
    }
    
    public static function instantRelations(): array
    {
        return [
            'user' => [
                'classname' => User::class,
                'condition' => [ 'user_id' => 'id', ],
                'columns'   => [ 'inn' ],
            ],
            'contract' => [
                'classname' => Contract::class,
                'condition' => [ 'contract_id' => 'id', ],
                'columns'   => [ 'number' ],
            ],
            'scorings' => [
                'classname' => Scoring::class,
                'condition' => [ 'id' => 'order_id', ],
                'where'     => [ 'type' => 'efrsb' ],
            ]

        ];
    }
    
    public static function _getColumns(): array
    {
        return [
            'id',
            'user_id',
            'contract_id',
            'amount',
            'sector',
            'register_id',
            'contract_number',
            'reference',
            'description',
            'created',
            'operation',
            'reason_code',
            'body',
            'callback_response',
            'sms',
            'prolongation',
            'insurance_id',
            'loan_body_summ',
            'loan_percents_summ',
            'loan_charge_summ',
            'loan_peni_summ',
            'commision_summ',
        ];
    }
}