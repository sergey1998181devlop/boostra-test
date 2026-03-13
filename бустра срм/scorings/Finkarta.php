<?php

require_once( __DIR__ . '/../api/Simpla.php');

class Finkarta extends Simpla
{
    /** @var array Правила которые мы проверяем */
    const REQUIRED_CHECKS = [
        'basicstdcheck_5_8_3' => 'Принадлежность карты',
    ];

    /**
     * @param $scoringId
     * @return array|null
     */
    public function run_scoring($scoringId)
    {
        $scoring = $this->scorings->get_scoring($scoringId);
        if (empty($scoring))
            return null;

        // Будет выключен в ночь с 31 августа на 1 сентября
        $isScoringEnabled = new DateTime() < new DateTime('2025-09-01 00:00:00');
        if ($isScoringEnabled) {
            $result = $this->run($scoring);
        }
        else {
            $result = [
                'status' => $this->scorings::STATUS_STOPPED,
                'string_result' => 'Скоринг отключен с 01.09.2024'
            ];
        }

        if (!empty($result)) {
            if (!empty($result['status']) && in_array($result['status'], [
                    $this->scorings::STATUS_COMPLETED,
                    $this->scorings::STATUS_ERROR,
                ]))
                $result['end_date'] = date('Y-m-d H:i:s');

            $this->scorings->update_scoring($scoringId, $result);
        }
        return $result;
    }

    /**
     * @param object $scoring
     * @return array
     */
    public function run(object $scoring)
    {
        $order = $this->orders->get_order((int)$scoring->order_id);
        if (empty($order))
            return $this->returnError('Заявка не найдена');

        // region Запрос и обработка ошибок
        $request = $this->finkarta_api->makeRequestXml($scoring);
        if (empty($request))
            return $this->returnError('Ошибка при формировании запроса');

        try {
            $responseXML = $this->finkarta_api->request($request);
            $response = simplexml_load_string($responseXML);

            if (empty($response['request_id']) || !isset($response->file->response->person->rules->rule)) {
                return $this->returnError('Ошибка при запросе', $responseXML);
            }
        }
        catch (Exception $e) {
            return $this->returnError('Ошибка при запросе');
        }
        // endregion

        // region Разбор ответа
        $rules = $response->file->response->person->rules;
        $body = [];
        $success = true;
        $rejectReason = '';
        foreach ($rules->children() as $rule) {
            $ruleName = (string)$rule['rule_name'];
            /**
             * 2 - Успешный результат.
             * 1 - Негативный результат.
             * 0 - Пройдено, негатив не обнаружен.
             */
            $value = (int)$rule['rule_value'];
            $body[$ruleName] = $value;

            if (!empty(self::REQUIRED_CHECKS[$ruleName]) && $value == 1) {
                $success = false;
                $rejectReason = self::REQUIRED_CHECKS[$ruleName];
            }
        }

        foreach (self::REQUIRED_CHECKS as $ruleName => $ruleDesc) {
            if (!isset($body[$ruleName])) {
                $body[$ruleName] = 'Результат не найден';
            }
        }
        // endregion

        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'body' => json_encode($body),
            'success' => (int)$success,
            'string_result' => $success ? 'Проверка пройдена' : ('Не пройдено: ' . $rejectReason)
        ];
    }

    /**
     * Генерация `$update` ответа об ошибке
     * @param string $string_result
     * @param string|null $body
     * @return array
     */
    private function returnError(string $string_result, $body = null)
    {
        if (empty($body))
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'success' => 0,
                'string_result' => $string_result
            ];

        return [
            'status' => $this->scorings::STATUS_ERROR,
            'success' => 0,
            'string_result' => $string_result,
            'body' => $body
        ];
    }
}