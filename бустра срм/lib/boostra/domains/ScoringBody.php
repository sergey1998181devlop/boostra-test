<?php

namespace boostra\domains;


use boostra\domains\abstracts\EntityObject;
use boostra\domains\Scoring\ResponseBody;
use boostra\helpers\Converter;

/**
 * @property int    $scoring_id
 * @property string $body
 *
 *      Entities
 * @property Scoring     $scoring
 */
class ScoringBody extends EntityObject{

    public static function table(): string
    {
        return 's_scoring_body';
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
            'scoring' => [
                'classname' => Scoring::class,
                'condition' => [ 'id' => $this->scoring_id ],
                'type'      => 'single',
            ],
        ];
    }

    public static function _getColumns(): array
    {
        return [
            'scoring_id',
            'body',
        ];
    }
}