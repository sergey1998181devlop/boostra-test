<?php

require_once 'AService.php';

class CheckBlacklist extends AService
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        $filters = [];

        $fio = $this->request->get('fio');
        if (!empty($fio)) {
            $filters['search']['fio'] = $fio;
        }

        $passportSerial = $this->request->get('passport_serial');
        if (!empty($passportSerial)) {
            $passportSerial = preg_replace('/\D+/iu', '', $passportSerial);
            $filters['column']["REPLACE(REPLACE(passport_serial, '-', ''), ' ', '')"] = $passportSerial;
        }

        $birthday = $this->request->get('birthday');
        if (!empty($birthday)) {
            $filters['column']['birth'] = $birthday;
        }

        if (empty($filters)) {
            $this->response = [
                'success' => false,
                'error' => 'EMPTY_PARAMETERS',
                'message' => 'Необходимо передать фильтры: fio, passport_serial, birthday (dd.mm.yyyy)' ,
            ];
            $this->json_output_unicode();
        }

        $users = $this->users->get_users($filters);

        if (empty($users)) {
            $this->response = [
                'success' => false,
                'error' => 'USER_NOT_FOUND',
                'message' => 'Пользователь не найден',
            ];
            $this->json_output_unicode();
        }

        $isInBlacklist = false;
        $isError       = false;

        // Проверяем пользователей
        foreach ($users as $user) {
            // 1. Проверка ЧС в бд
            if ($this->blacklist->in($user->id)) {
                $isInBlacklist = true;
                break;
            }

            // 2. Проверка в 1С
            $check = $this->blacklist->checkIsUserIn1cBlacklistSafe($user->UID);

            // Если ошибка 1С — выходим
            if (!$check['ok']) {
                $isError = true;
                break;
            }

            // Если найден в ЧС в 1С — выходим
            if ($check['in_blacklist']) {
                $isInBlacklist = true;
                break;
            }
        }

        $this->response = [
            'success'         => !$isError,
            'fio'             => $fio,
            'passport_serial' => $passportSerial,
            'birthday'        => $birthday,
            'in_blacklist'    => $isError ? null : $isInBlacklist,
            'message'         => $isError
                ? 'Сервис 1С недоступен. Невозможно проверить ЧС в 1С.'
                : ($isInBlacklist ? 'Найден в ЧС' : 'Не найден в ЧС'),
        ];
        $this->json_output_unicode();
    }
}

new CheckBlacklist();
