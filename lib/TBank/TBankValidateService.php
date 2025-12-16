<?php

class TBankValidateService
{
    public function validateInit(array $params): bool
    {
        $requiredKeys = [
            'amount',
            'payment_id',
            'user_id',
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $params)) {
                throw new \Exception("Обязательный параметр $key не передан");
            }
        }

        if (!is_numeric($params['amount']) || $params['amount'] <= 0) {
            throw new \Exception('Некорректная сумма платежа');
        }

        if (!is_numeric($params['payment_id']) || $params['payment_id'] <= 0) {
            throw new \Exception('Некорректный ID платежа');
        }

        if (!is_numeric($params['user_id']) || $params['user_id'] <= 0) {
            throw new \Exception('Некорректный ID пользователя');
        }

        return true;
    }

    public function validateAddAccountQr(array $params): bool
    {
        $requiredKeys = [
            'user_id',
            'description'
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $params)) {
                throw new \Exception("Обязательный параметр $key не передан");
            }
        }

        if (!is_numeric($params['user_id']) || $params['user_id'] <= 0) {
            throw new \Exception('Некорректный ID пользователя');
        }

        if (!is_string($params['description']) || empty($params['description'])) {
            throw new \Exception('Некорректное описание');
        }

        return true;
    }

    public function validateNotificationCallback(array $params): bool
    {
        $requiredKeys = [
            'NotificationType',
            'Success',
            'Status',
            'AccountToken',
            'RequestKey',
            'PaymentId'
        ];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $params)) {
                throw new \Exception("Обязательный параметр $key не передан");
            }
        }

        if (!is_string($params['NotificationType']) || empty($params['NotificationType'])) {
            throw new \Exception('Некорректный тип уведомления');
        }

        if (!is_bool($params['Success'])) {
            throw new \Exception('Некорректный статус успешности');
        }

        if (!is_string($params['Status']) || empty($params['Status'])) {
            throw new \Exception('Некорректный статус');
        }

        if (!is_string($params['AccountToken']) || empty($params['AccountToken'])) {
            throw new \Exception('Некорректный токен счета');
        }

        if (!is_string($params['RequestKey']) || empty($params['RequestKey'])) {
            throw new \Exception('Некорректный ключ запроса');
        }

        return true;
    }
}