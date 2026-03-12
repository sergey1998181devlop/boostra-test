<?php

error_reporting(-1);
ini_set('display_errors', 'Off');

session_start();

chdir('..');

require_once 'api/Simpla.php';
$simpla = new Simpla();

$action = trim((string)$simpla->request->post('action'));
$type   = trim((string)$simpla->request->post('type_addon'));
$loanId = $simpla->request->post('loan_id');

if (
    !$simpla->request->method('post') ||
    $action === '' ||
    $type === '' ||
    $loanId === null || $loanId === '' ||
    !isset($_SESSION['user_id']) ||
    !$simpla->users->get_user((int)$_SESSION['user_id'])
) {
    $simpla->request->json_output([
        'success' => false,
        'error'   => 'invalid_method_or_data',
        'message' => 'Обратитесь в службу поддержки! Произошла ошибка',
    ]);
    exit;
}

if ($simpla->request->post('action') == 'activate' && $simpla->request->post('type_addon') == 'pcd') {
    try {
        $response = $simpla->soap->send_addon_operation([
            'loan_number' => $simpla->request->post('loan_id'),
            'additional_service' => 'penalty_credit_doctor',
            'operation' => 'activate'
        ]);

        if (!isset($response)){
            $simpla->request->json_output([
                'success' => false,
                'error' => 'invalid_response',
                'message' => 'Обратитесь в службу поддержки! Произошла ошибка',
            ]);
        }

        if ($response == 'ОК'){
            $simpla->user_data->set($_SESSION['user_id'], 'IS_ENABLED_PENALTY_CD', 1);
        }

        $simpla->request->json_output([
            'success' => $response == 'ОК',
            'message' => $response == 'ОК' ? 'Успешно' : 'Попробуйте ещё раз!',
        ]);

    } catch (Throwable $e) {
        $simpla->request->json_output([
            'success' => false,
            'error' => 'invalid_method',
            'message' => 'Обратитесь в службу поддержки! Произошла ошибка',
        ]);
    }
}

$simpla->request->json_output([
    'success' => false,
    'error' => 'invalid_method',
    'message' => 'Что-то пошло не так.',
]);