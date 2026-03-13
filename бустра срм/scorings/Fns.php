<?php

class Fns extends Simpla
{
    private $url = "https://service.nalog.ru/inn-proc.do";
    

    /** @var array Обязательные поля анкеты которые должны быть заполнены */
    const REQUIRED_FIELDS = [
        'lastname', 'firstname', 'patronymic', 'passport_serial', 'passport_date', 'birth'
    ];

    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        if (empty($scoring)) {
            return null;
        }

        $result = $this->run($scoring);
        if (!empty($result)) {
            if (!empty($result['status']) && in_array($result['status'], [$this->scorings::STATUS_COMPLETED, $this->scorings::STATUS_ERROR, ])) {
                $result['end_date'] = date('Y-m-d H:i:s');
            }

            if (!empty($result['body'])) {
                $result['body'] = serialize($result['body']);
            }

            $this->scorings->update_scoring($scoring_id, $result);
        }
        return $result;
    }

    /**
     * @param Object $scoring
     * @return array
     */
    function run($scoring)
    {
        $user = $this->users->get_user($scoring->user_id);
        if (empty($user)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Пользователь не найден'
            ];
        }

        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($user->$field)) {
                return [
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'в заявке не достаточно данных для проведения скоринга'
                ];
            }
        }

        if (!empty($user->inn)) {
            return [
                'status' => $this->scorings::STATUS_COMPLETED,
                'body' => 'ИНН найден у клиента',
                'success' => 1,
                'string_result' => $user->inn
            ];
        }

        if ($fns = $this->get_inn_from_sphere($user)) {
            if (!empty($fns->inn)) {
                $this->users->update_user($scoring->user_id, ['inn' => $fns->inn]);
                return [
                    'status' => $this->scorings::STATUS_COMPLETED,
                    'body' => $fns,
                    'success' => 1,
                    'string_result' => $fns->inn
                ];
            }

            $this->logging(__METHOD__, '', $user, $fns, 'fns_errors.txt');
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'body' => $fns,
                'string_result' => $fns->error ?? 'Неизвестная ошибка'
            ];
        }

        return [
            'status' => $this->scorings::STATUS_ERROR,
            'string_result' => 'Сервер ФНС не отвечает'
        ];
    }

    public function get_inn_from_sphere($user) {
        $params = [
            'sources' => 'fns',
            'PersonReq' => [
                'first' => $user->firstname,
                'middle' => $user->patronymic,
                'paternal' => $user->lastname,
                'birthDt' => date('Y-m-d', strtotime($user->birth)),
                'issueDate' => $user->passport_issued,
                'passport_series' => substr(str_replace(array(' ', '-'), '', $user->passport_serial), 0, 4),
                'passport_number' => substr(str_replace(array(' ', '-'), '', $user->passport_serial), 4, 6),
            ]
        ];
        
        $response = $this->Infosphere->check_fns($params);
        $fns = new stdClass();
        $fns->inn = $this->get_inn_from_response($response);
        $fns->error = $this->get_inn_error_from_response($response);
        $fns->response = $response;
        $fns->source = 'sphere';
        return $fns;
    }

    public function get_inn_from_response($response) {
        foreach($response['Source'] as $source) {

            if (empty($source['@attributes'])) {
                continue;
            }

            if ($source['@attributes']['checktype'] === 'fns_inn') {
                foreach($source['Record']['Field'] as $field) {
                    if ($field['FieldName'] === 'INN') {
                        return $field['FieldValue'];
                    }
                }
            }
        }
        return null;
    }

    public function get_inn_error_from_response($response) {
        if (empty($response) || isset($response['Source']['Error'])) {
            return $response['Source']['Error'];
        }

        try {
            foreach ($response['Source'] as $source) {
                if (!empty($source['@attributes']['checktype']) && $source['@attributes']['checktype'] === 'fns_inn') {
                    foreach ($source['Record']['Field'] as $field) {
                        if ($field['FieldName'] === 'Result') {
                            return $field['FieldValue'];
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            return "Неизвестная ошибка";
        }

        return $response['message'] ?: null;
    }

}