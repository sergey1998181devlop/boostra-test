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
 * @property string $inn
 * @property string $status 'new','process','stopped','completed','error','import','wait'
 * @property string $body
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
 */
class ScoringEFRSB extends EntityObject{
    
    public static function table(): string
    {
        return 's_scoring_efrsb_14_days_period';
    }
    
    public function init()
    {
        $this->initBody();
    }
    
    /**
     * @throws \JsonException
     * @throws \Exception
     */
    private function initBody()
    {
        $converter = new Converter( $this->body );
        $this->body = $converter->detectFormat() === 'unknown'
            ? $this->body
            : new ResponseBody( $converter->to( 'array' ) );
    }
    
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
            ]
        ];
    }
    
    public static function _getColumns(): array
    {
        return [
            'id',
            'user_id',
            'order_id',
            'audit_id',
            'inn',
            'status',
            'body',
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