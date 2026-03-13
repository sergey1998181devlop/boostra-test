<?php

/**
 * @description Запрашивает данные по открытым займам в CRM
 *              и записывает их в таблицу s_scoring_efrsb
 * @period 14 дней
 */


use boostra\domains\User;
use boostra\repositories\Repository;
use boostra\services\Core;

require_once __DIR__ . '/../lib/autoloader.php';
require_once __DIR__ . '/../api/Simpla.php';

// Получение номеров договоров активных займов
$active_loans = Core::instance()
    ->soap
    ->getActiveLoans();

// Сбро дополнительных данных для скоринга из БД
$scorings_data = ( new Repository( \boostra\domains\Contract::class, Core::instance()->dbAccess ) )
    ->readBatch(
        ['number' => ['in', $active_loans ]],
        '',
        '',
        0,
        100000,
        'user_id, order_id',
        [
            'user' => [
                'classname' => User::class,
                'condition' => [ 'user_id' => 'id', ],
                'columns'   => [ 'inn', ],
            ],
        ],
    );

// Сборка данных для записи в таблицу
$db  = Core::instance()->dbAccess;
array_walk(
    $scorings_data,
    static function( &$item, $key ) use ($db){
        $item = $db->placehold(
            "(?, ?, ?, 'new')",
            (int) $item->user_id,
            (int) $item->order_id,
            $item->user->inn
        );
    }
);
$scorings_data = implode( ',', $scorings_data );

// Запись скорингов для дальнейшего выполнения
Core::instance()->dbAccess->
    query(
        "INSERT
            INTO s_scoring_efrsb
                (user_id, order_id, inn, status)
            VALUES
                $scorings_data
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id)"
    );

exit();