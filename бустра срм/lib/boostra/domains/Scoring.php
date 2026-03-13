<?php

namespace boostra\domains;


use boostra\domains\abstracts\EntityObject;
use boostra\domains\Scoring\ResponseBody;
use boostra\helpers\Converter;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $order_id
 * @property int    $audit_id
 * @property string $type
 * @property string $status
 * @property int    $success
 * @property int    $created
 * @property string $scorista_id
 * @property string $scorista_status
 * @property string $scorista_ball
 * @property string $string_result
 * @property string $start_date
 * @property string $end_date
 * @property int    $manual
 *
 *      Entities
 * @property User        $user
 * @property Order       $order
 * @property ScoringBody $body
 */
class Scoring extends EntityObject{
    
    public static function table(): string
    {
        return 's_scorings';
    }

    public function init() { }
    
    protected function relations(): array
    {
        return [
            'user' => [
                'classname' => User::class,
                'condition' => [ 'id' => $this->user_id ],
                'type'      => 'single',
            ],
            'order' => [
                'classname' => Order::class,
                'condition' => [ 'id' => $this->order_id ],
                'type'      => 'single',
            ],
            'body' => [
                'classname' => ScoringBody::class,
                'condition' => [ 'scoring_id' => $this->id ],
                'type'      => 'single',
            ],
        ];
    }
    
    public static function _getColumns(): array
    {
        return [
            'id',
            'user_id',
            'order_id',
            'audit_id',
            'type',
            'status',
            'success',
            'created',
            'scorista_id',
            'scorista_status',
            'scorista_ball',
            'string_result',
            'start_date',
            'end_date',
            'manual',
        ];
    }
}