<?php

namespace api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyPhoneData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyPhoneDB.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyIPData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyIPbyDB.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyCoockieData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyCoockieDB.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnection.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnectionAdapter.php');

class VerifyConnectionHandler
{
    public static function setByPhone(string $phone)
    {
        $verifyPhoneData = new VerifyPhoneData($phone);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyPhoneDB());
        $verifyConnection = new VerifyConnection($verifyPhoneData, $verifyConnectionAdapter);

        $result = $verifyConnection->get($verifyPhoneData->phoneDecimal);
        if (empty($result)) {
            $resultCreate = $verifyConnection->create();
            $result = $verifyConnection->get($verifyPhoneData->phoneDecimal);
        } else {
            $verifyPhoneData->count = $result->count;
            if ($verifyPhoneData->count > VerifyConnection::maxConnection()) {
                $verifyPhoneData->count = 0;
            }
            $verifyPhoneData->countIncrement();
            $verifyConnection->setVerifyConnectionData($verifyPhoneData);
            $resultSet = $verifyConnection->set();
            $result = $verifyConnection->get($verifyPhoneData->phoneDecimal);
        }

        return $result;
    }

    public static function getByPhone(string $phone)
    {
        $verifyPhoneData = new VerifyPhoneData($phone);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyPhoneDB());
        $verifyConnection = new VerifyConnection($verifyPhoneData, $verifyConnectionAdapter);
        $result = $verifyConnection->get($verifyPhoneData->phoneDecimal);
        return $result;
    }

    public static function setByIP(string $ip)
    {
        $verifyIPData = new VerifyIPData($ip);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyIPbyDB());
        $verifyConnection = new VerifyConnection($verifyIPData, $verifyConnectionAdapter);

        $result = $verifyConnection->get($verifyIPData->ip);
        if (empty($result)) {
            $resultCreate = $verifyConnection->create();
            $result = $verifyConnection->get($verifyIPData->ip);
        } else {
            $verifyIPData->count = $result->count;
            if ($verifyIPData->count > VerifyConnection::maxConnection()) {
                $verifyIPData->count = 0;
            }
            $verifyIPData->countIncrement();
            $verifyConnection->setVerifyConnectionData($verifyIPData);
            $resultSet = $verifyConnection->set();
            $result = $verifyConnection->get($verifyIPData->ip);
        }

        return $result;
    }

    public static function getByIP(string $ip)
    {
        $verifyIPData = new VerifyIPData($ip);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyIPbyDB());
        $verifyConnection = new VerifyConnection($verifyIPData, $verifyConnectionAdapter);
        $result = $verifyConnection->get($verifyIPData->ip);
        return $result;
    }

    public static function canSet($data): bool
    {
        return VerifyConnection::canSet($data);
    }

    public static function setByCoockie(string $coockie)
    {
        $verifyCoockieData = new VerifyCoockieData($coockie);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyCoockieDB());
        $verifyConnection = new VerifyConnection($verifyCoockieData, $verifyConnectionAdapter);

        $result = $verifyConnection->get($verifyCoockieData->coockie);
        if (empty($result)) {
            $resultCreate = $verifyConnection->create();
            $result = $verifyConnection->get($verifyCoockieData->coockie);
        } else {
            $verifyCoockieData->count = $result->count;
            if ($verifyCoockieData->count > VerifyConnection::maxConnection()) {
                $verifyCoockieData->count = 0;
            }
            $verifyCoockieData->countIncrement();
            $verifyConnection->setVerifyConnectionData($verifyCoockieData);
            $resultSet = $verifyConnection->set();
            $result = $verifyConnection->get($verifyCoockieData->coockie);
        }

        return $result;
    }

    public static function getByCoockie(string $coockie)
    {
        $verifyCoockieData = new VerifyCoockieData($coockie);
        $verifyConnectionAdapter = new VerifyConnectionAdapter(new VerifyCoockieDB());
        $verifyConnection = new VerifyConnection($verifyCoockieData, $verifyConnectionAdapter);
        $result = $verifyConnection->get($verifyCoockieData->coockie);
        return $result;
    }
}