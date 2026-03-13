<?php
    require_once 'LoginClass.php';
    $loginClass = new \ajax\loginClass\LoginClass();
    $action = $_GET['action'];

    switch ($action) {
        case 'login':
            $loginClass->loginUserByPassport();
            break;
        case 'get_payment_link':
            $loginClass->getPaymentLink();
            break;
        default:
            die('Action не найден');
    }

