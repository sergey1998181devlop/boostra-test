<?php

class Dbrain_passport extends Simpla
{
    private const STRING_RESULT = [
        'bad_image_quality' => 'Плохое качество фото',
        'definitely_fake' => 'Поддельное фото',
        'potentially_fake' => 'Возможно поддельное фото',
        'wrong_document' => 'На фото не найден паспорт',
        'genuine' => 'Проверка пройдена'
    ];

    /**
     * @param stdClass|int $scoring
     * @return array|null
     */
    public function run_scoring($scoring)
    {
        if (is_string($scoring) || is_numeric($scoring))
            $scoring = $this->scorings->get_scoring($scoring);

        if (is_array($scoring))
            $scoring = (object)$scoring;

        if (empty($scoring))
            return null;

        if (empty($scoring->scorista_id))
            $result = $this->sendRequest($scoring);
        else
            $result = $this->handleResult($scoring);

        if (!empty($result)) {
            if (!empty($result['status']) && in_array($result['status'], [
                    $this->scorings::STATUS_COMPLETED,
                    $this->scorings::STATUS_ERROR,
                ]))
                $result['end_date'] = date('Y-m-d H:i:s');

            $this->scorings->update_scoring($scoring->id, $result);
        }
        return $result;
    }

    private function sendRequest($scoring)
    {
        $files = $this->users->get_files([
            'user_id' => $scoring->user_id,
            'types' => 'passport1',
            'status' => [1, 2], // Не рассмотрено, Одобрено
            'visible' => 1
        ]);

        if (empty($files)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Нет фото паспорта'
            ];
        }

        // Берём последнее (свежее) фото
        $file = end($files);
        $task_id = $this->dbrain_api->checkAntiFraud($file->id);

        if (empty($task_id)) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка при создании запроса'
            ];
        }

        return [
            'string_result' => 'В обработке',
            'scorista_id' => $task_id,
            'start_date' => date('Y-m-d H:i:s')
        ];
    }

    private function handleResult($scoring)
    {
        $task_id = $scoring->scorista_id;
        $response = $this->dbrain_api->get_result($task_id);

        if (is_numeric($response)) { // Ответ ещё не готов
            if ($this->isOldScoring($scoring) || $response == 404) {
                return [
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'По запросу не был получен ответ'
                ];
            }
            // Иначе просто ждём ответ
            return null;
        }

        $result = $response['result']['overall_result'] ?? 'Пустой ответ';
        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'string_result' => self::STRING_RESULT[$result] ?? $result,
            'success' => $result == 'genuine'
        ];
    }

    /**
     * Возвращает true если скоринг ждёт ответа уже 6 минут
     * @param $scoring
     * @return bool
     * @throws Exception
     */
    private function isOldScoring($scoring)
    {
        $scoringDate = new DateTime($scoring->start_date ?: $scoring->created);
        $currentDate = new DateTime();

        $interval = $currentDate->getTimestamp() - $scoringDate->getTimestamp();

        return abs($interval) >= 360; // 6 минут
    }
}