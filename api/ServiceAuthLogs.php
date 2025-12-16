<?php

require_once 'Simpla.php';

/**
 * Пишет логи для неудавшихся авторизаций по сервисам Госуслуги и ТБанк
 */
class ServiceAuthLogs extends Simpla
{
    /**
     * Пишет запись в лог для Госуслуг
     *
     * @param string $request_uid
     * @param array $errors
     * @return void
     */
    public function addEsiaErrorLog(string $request_uid, array $errors)
    {
        $error_flags = array_fill_keys(array_keys($errors), 1);
        $insert_data = array_merge($error_flags, [
            'auth_type' => 'esia',
            'request_uid' => $request_uid,
        ]);

        $query = $this->db->placehold("INSERT INTO s_auth_service_errors  SET ?%", $insert_data);
        $this->db->query($query);
    }

    /**
     * Пишет запись в лог для Тид
     *
     * @param array $data
     * @return void
     */
    public function addTidErrorLog(array $data)
    {
        $errors = [];
        $session_uid = $this->TBankIdService->getSessionId();

        foreach ($data as $key => $error) {
            if (in_array($key, ['fio', 'gender', 'phone', 'birth_date'])) {
                if (empty($errors['main'])) {
                    $errors['main'] = [
                        $key => 1,
                        'session_uid' => $session_uid,
                        'auth_type' => 'tid',
                        'request_uid' => $this->TBankIdService->getRequestId('main'),
                    ];
                } else {
                    $errors['main'][$key] = 1;
                }
            } elseif (in_array($key, ['birth_place', 'passport'])) {
                if (empty($errors['passport'])) {
                    $errors['passport'] = [
                        $key => 1,
                        'session_uid' => $session_uid,
                        'auth_type' => 'tid',
                        'request_uid' => $this->TBankIdService->getRequestId('passport'),
                    ];
                } else {
                    $errors['passport'][$key] = 1;
                }
            } else {
                $errors[] = [
                    $key => 1,
                    'session_uid' => $session_uid,
                    'auth_type' => 'tid',
                    'request_uid' => $this->TBankIdService->getRequestId($key),
                ];
            }
        }

        foreach ($errors as $error) {
            $query = $this->db->placehold("INSERT INTO s_auth_service_errors  SET ?%", $error);
            $this->db->query($query);
        }
    }
}
