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
 * @property int             amount_total_returned
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
 * @property bool            $amount_left
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

    public function init()
    {
        $this->discount          = ceil( $this->amount / 2 );
        $this->discount_refunded = $this->return_amount > 0;
        $this->amount_left = $this->amount - $this->amount_total_returned;
        $this->fully_refunded    = (float)$this->amount <= (float)$this->return_amount;
    }

    protected function relations(): array
    {
        // Определяем класс и ID по action_type: issuance -> Transaction, остальное -> Payment
        switch ($this::table()) {
            case 's_credit_doctor_to_user':
                $classname = ($this->is_penalty ?? false) ? Payment::class : Transaction::class;
                $transaction_id = $this->transaction_id ?? $this->payment_id;
                break;

            case 's_star_oracle':
                $classname = ($this->action_type === 'issuance' || empty($this->action_type)) ? Transaction::class : Payment::class;
                $transaction_id = ($this->action_type === 'issuance' || empty($this->action_type)) ? $this->transaction_id : $this->payment_id;
                break;

            case 's_tv_medical_payments':
            case 's_multipolis':
                $classname = ($this->action_type === 'issuance') ? Transaction::class : Payment::class;
                // Для выдач используем payment_id как ID транзакции
                $transaction_id = $this->payment_id ?? $this->transaction_id;
                break;

            case 's_safe_deal':
                $classname = Transaction::class;
                break;

            default:
                $classname = Payment::class;
                $transaction_id = $this->payment_id ?? $this->transaction_id;
                break;
        }

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
                'classname' => $classname,
                'condition' => ['id' => $transaction_id],
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

    public function isRefunded()
    {
        return $this->discount_refunded && $this->fully_refunded;
    }

    public function markAsRefunded( $return_transaction_id, $amount )
    {

        $this->return_status         = 2;
        $this->return_date           = date( 'Y-m-d H:i:s' );
        $this->return_amount         = $amount;
        $this->amount_total_returned += $amount;
        $this->return_transaction_id = $return_transaction_id;
        $this->return_sent           = 0;

        $this->save();
    }
}
