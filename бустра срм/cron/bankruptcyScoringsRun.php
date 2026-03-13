<?php

/**
 * @description Получает 10000 скорингов по банкротствам со статусом new не старше 14 дней и выполняет их
 * @period 2 дней
 */

use boostra\repositories\Repository;
use boostra\services\Core;

@session_start();
chdir( '..' );
ini_set( 'max_execution_time', 0 );

require_once __DIR__ . '/../api/Simpla.php';
require_once __DIR__ . '/../scorings/ScoringEfrsb.php';
require_once __DIR__ . '/../lib/autoloader.php';

$simpla = new Simpla();

/** @var \boostra\domains\ScoringEFRSB[] $scorings */
$scorings = ( new Repository( \boostra\domains\ScoringEFRSB::class, Core::instance()->dbAccess ) )
    ->readBatch(
        [
            'created' => [ '<', date( 'Y-m-d H:i:s', strtotime( '+14days' ) ) ],
            'status'  => 'new',
        ],
        '',
        '',
        0,
        10000
    );

$efrsbScoring = new ScoringEfrsb();
foreach( $scorings as $scoring ){
    try{
        $efrsbScoring->run_scoring( $scoring );
    }catch( \Exception $exception ){
        $scoring->status        = $simpla->scorings::STATUS_ERROR;
        $scoring->string_result = 'Ошибка выполнения: ' . substr( $exception->getMessage(), 0, 1024 );
        $scoring->save();
    }
}

exit();