<?php

namespace boostra\domains;

/**
 * @property int    $id
 * @property int    $user_id
 * @property string $user_uid
 * @property int    $order_id
 * @property string $number
 * @property float  $amount
 * @property int    $period
 * @property string $payment_method
 * @property int    $card_id
 * @property int    $status
 * @property float  $base_percent
 * @property float  $charge_percent
 * @property float  $peni_percent
 * @property string $uid
 * @property float  $loan_body_summ
 * @property float  $loan_percents_summ
 * @property float  $loan_charge_summ
 * @property float  $loan_peni_summ
 * @property float  $loan_penalty_summ
 * @property float  $profit_border
 * @property string $create_date
 * @property string $confirm_date
 * @property string $issuance_date
 * @property string $grace_date
 * @property string $return_date
 * @property string $close_date
 * @property int    $prolongation_count
 * @property int    $stop_profit
 * @property int    $organization_id
 * @property string $asp
 * @property float  $psk
 * @property float  $pdn
 * @property int    $onec_sent
 * @property string $onec_sent_date
 * @property int    $is_true
 */
class Loan extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 's_contracts';
    }
    
    public static function _getColumns(): array
    {
        return [
            'id',
            'user_id',
            'user_uid',
            'order_id',
            'number',
            'amount',
            'period',
            'payment_method',
            'card_id',
            'status',
            'base_percent',
            'charge_percent',
            'peni_percent',
            'uid',
            'loan_body_summ',
            'loan_percents_summ',
            'loan_charge_summ',
            'loan_peni_summ',
            'loan_penalty_summ',
            'profit_border',
            'create_date',
            'confirm_date',
            'issuance_date',
            'grace_date',
            'return_date',
            'close_date',
            'prolongation_count',
            'stop_profit',
            'organization_id',
            'asp',
            'psk',
            'pdn',
            'onec_sent',
            'onec_sent_date',
            'is_true',
        ];
    }

}