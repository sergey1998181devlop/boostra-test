<?php

namespace boostra\domains\extraServices;

use boostra\domains\abstracts\EntityObject;
use boostra\domains\Card;
use boostra\domains\Loan;
use boostra\domains\Manager;
use boostra\domains\Order;
use boostra\domains\Payment;
use boostra\domains\Transaction;
use boostra\domains\User;

/**
 *      Common properties
 * @property int             id
 * @property int             user_id
 * @property int             organization_id
 * @property string          date_added
 * @property string          payment_method
 * @property int             order_id
 * @property string          status
 * @property int             amount
 *
 *      Return information
 * @property int             return_status
 * @property string          return_date
 * @property int             return_amount
 * @property int             return_transaction_id
 * @property int             return_sent
 * @property int             return_by_user
 * @property int             return_by_manager_id
 *
 *      Dynamic properties
 * @property string          $slug
 * @property string          $return_slug
 * @property string          $title
 * @property string          $description
 * @property false|float|int $discount
 * @property bool            $discount_refunded
 * @property bool            $fully_refunded
 * @property Card            $return_card
 *
 *      Entities
 * @property Loan                $loan
 * @property Order               $order
 * @property Transaction|Payment $transaction
 * @property Transaction         $return_transaction
 * @property User                $user
 * @property Manager             $return_manager
 */
abstract class extraService extends EntityObject{
    
    abstract public function isActive(): bool;
    
    public function isAlreadyRefunded()
    {
        return $this->discount_refunded && $this->fully_refunded;
    }
    
    public function init()
    {
        $this->discount          = ceil( $this->amount / 2 );
        $this->discount_refunded = $this->return_amount > 0;
        $this->fully_refunded    = (float)$this->amount <= (float)$this->return_amount;
        $this->return_card       = $card = $this->slug === 'credit_doctor'
            ? $this->order->card
            : new \boostra\domains\Card([
                'token' => $this->transaction->callback_response->token,
                'pan'   => $this->transaction->callback_response->pan,
            ]);
    }

    protected function relations(): array
    {
        return [
            'order' => [
                'classname' => Order::class,
                'condition' => [ 'id' => $this->order_id, ],
                'type'      => 'single',
            ],
            'loan' => [
                'classname' => Loan::class,
                'condition' => [ 'user_id' => $this->user_id, 'order_id' => $this->order_id, ],
                'type'      => 'single',
            ],
            'transaction' => [
                'classname' => isset( $this->transaction_id ) ? Transaction::class : Payment::class,
                'condition' => [ 'id' => $this->transaction_id ?? $this->payment_id ],
                'type'      => 'single',
            ],
            'return_transaction' => [
                'classname' => Transaction::class,
                'condition' => [ 'id' => $this->return_transaction_id ],
                'type'      => 'single',
            ],
            'user' => [
                'classname' => User::class,
                'condition' => [ 'id' => $this->user_id ],
                'type'      => 'single',
            ],
            'return_manager' => [
                'classname' => Manager::class,
                'condition' => [ 'id' => $this->return_by_manager_id ],
                'type'      => 'single',
            ],
        ];
    }
}