<?php

namespace boostra\domains;


use boostra\domains\abstracts\EntityObject;
use boostra\domains\Scoring\ResponseBody;
use boostra\helpers\Converter;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $order_id
 * @property string $inn
 * @property string $status 'new','process','stopped','completed','error','import','wait'
 * @property string $body
 * @property int    $success
 * @property int    $created
 * @property string $string_result
 * @property string $start_date
 * @property string $end_date
 * @property int    $manual
 * @property string $bankruptcy_date;
 *
 *      Entities
 * @property User        $user
 * @property Order       $order
 */
class ScoringEFRSB extends EntityObject{
    
    public static function table(): string
    {
        return 's_scoring_efrsb';
    }
    
    public function init()
    {
        // Decode body
        $this->initBody();
        
        // Reformat bankruptcy date
        $this->bankruptcy_date = $this->bankruptcy_date
            ? date( 'Y-m-d', strtotime( $this->bankruptcy_date ) )
            : null;
        
        $this->success = ! is_null( $this->success )
            ? (int) $this->success
            : null;
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
            'inn',
            'status',
            'body',
            'success',
            'created',
            'string_result',
            'start_date',
            'end_date',
            'bankruptcy_date',
        ];
    }
}