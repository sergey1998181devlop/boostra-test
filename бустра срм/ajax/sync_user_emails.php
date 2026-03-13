<?php

date_default_timezone_set('Europe/Moscow');

header('Content-type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

define('ROOT', dirname(__DIR__));

session_start();
chdir('..');

require 'api/Simpla.php';

class SyncUserEmailsAjax extends Simpla
{
    public function run(): void
    {
        $userId = (int)($this->request->post('user_id', 'integer') ?? 0);

        if (empty($userId)) {
            echo json_encode([
                'success' => false,
                'error'   => 'Не передан user_id',
            ]);
            return;
        }

        $result = $this->emails->syncUnsyncedForUser($userId);

        echo json_encode([
            'success'   => true,
            'total'     => $result['total'],
            'processed' => $result['processed'],
        ]);
    }
}

$ajax = new SyncUserEmailsAjax();
$ajax->run();
