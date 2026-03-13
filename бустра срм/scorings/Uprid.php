<?php

require_once( __DIR__ . '/../api/Simpla.php');

class Uprid extends Simpla
{
    /** @var string Фасадная заглушка для статуса, который B2P не умеет обрабатывать */
    public const STATE_UNKNOWN = 'UNKNOWN';
    /** @var string В очереди на проверку, B2P пока не отправил данные в внешнюю систему */

    /** Максимальное количество попыток УПРИД при ошибке сервиса */
    public const MAX_UPRID_ERROR_ATTEMPTS = 2;

    public const STATE_PENDING = 'PENDING';
    /** @var string ИНН найден */
    public const STATE_APPROVED = 'APPROVED';
    /** @var string ИНН пока нет, B2P ждёт ответа от внешней системы */
    public const STATE_SENT = 'SENT';
    /** @var string Ошибка формата данных на проверку */
    public const STATE_FORMAT_ERROR = 'FORMAT_ERROR';
    /** @var string ИНН не найден */
    public const STATE_NOT_FOUND = 'INN_NOT_FOUND';

    /** @var int Максимальное время ожидания ответа по запросу */
    const REQUEST_TIMEOUT_MINUTES = 30;

    /** @var string Количество попыток получить уприд */
    public const USER_DATA_UPRID_ATTEMPTS = 'uprid_attempts';

    /** @var string Запись в s_user_data - Время до которого не спрашиваем ответ уприд */
    public const USER_DATA_PAUSE_UPRID_UNTIL = 'pause_uprid_until';

    /** @var int Количество попыток получить уприд */
    private $attempt;

    /** @var int[] Паузы между опросами УПРИДа */
    const WAIT_BEFORE_NEXT_REQUEST = [
        1, 1, 1, 1, // 1-4 попытки, 1 мин
        5, 5,       // 5-6 попытки, 5 мин
        10,         // 7 попытка, 10 мин
        15,         // 8 попытка, 15 мин
        30,         // 9 попытка, 30 мин
        60          // 10 попытка, 60 мин
    ];

    public function run_scoring($scoringId)
    {
        $scoring = $this->scorings->get_scoring($scoringId);
        if (empty($scoring))
            return null;

        $result = $this->run($scoring);
        if (!empty($result)) {
            if (!empty($result['status']) && in_array($result['status'], [
                    $this->scorings::STATUS_COMPLETED,
                    $this->scorings::STATUS_ERROR,
                ]))
                $result['end_date'] = date('Y-m-d H:i:s');

            if (!empty($result['body']))
                $result['body'] = serialize((array)$result['body']);

            $this->scorings->update_scoring($scoringId, $result);

            /** Если скоринг ещё не готов и часто опрашивается - ставим на паузу на Х минут */
            if ($result['status'] == $this->scorings::STATUS_WAIT && !empty($this->attempt)) {
                if (!empty(self::WAIT_BEFORE_NEXT_REQUEST[$this->attempt - 1])) {
                    $minutes = self::WAIT_BEFORE_NEXT_REQUEST[$this->attempt - 1];
                    $this->scorings->update_scoring($scoringId, [
                        'next_run_at' => date('Y-m-d H:i:s', time() + $minutes * 60)
                    ]);
                }
            }            
        }
        return $result;
    }

    private function run(object $scoring)
    {
        $user = $this->users->get_user((int)$scoring->user_id);
        $order = $this->orders->get_order((int)$scoring->order_id);

        if (empty($user)){
            return $this->returnError('Клиент не найден');
        }

        if (empty($order)){
            return $this->returnError('Заявка не найдена');
        }

        if (empty($user->passport_serial)) {
            return $this->returnError('Пустая серия паспорта');
        }

        if (!$this->organizations->isActiveOrganization((int)$order->organization_id)) {
            return $this->returnError('Организация отключена для выдачи');
        }

        $this->attempt = $this->user_data->read($scoring->user_id, self::USER_DATA_UPRID_ATTEMPTS) ?? 0;
        $this->attempt += 1;
        $this->user_data->set($scoring->user_id, self::USER_DATA_UPRID_ATTEMPTS, $this->attempt);

        // region Запрос и обработка ошибок
        $response = $this->best2pay->identification_status(
            $user->firstname,
            $user->lastname,
            $user->patronymic,
            $user->birth,
            $user->passport_serial,
            (int)$order->organization_id
        );
        if (empty($response))
            return $this->returnError('Ошибка при запросе');
        $response = $response->identification_data;

        try {
            $state = (string)$response->identification_state;
            if ($state == self::STATE_FORMAT_ERROR)
                return $this->returnError('Ошибка в формате запроса', $response);
        }
        catch (Exception $e) {
            return $this->returnError('Ошибка при разборе ответа');
        }
        // endregion
        // region Разбор ответа
        // Таймаут долгих запросов
        if (in_array($state, [
            self::STATE_UNKNOWN,
            self::STATE_PENDING,
            self::STATE_SENT
        ])) {
            if ($this->isTimeOut($scoring->start_date))
                return $this->returnError('Время ожидания ответа истекло');
            else
                return [
                    'status' => $this->scorings::STATUS_WAIT
                ];
        }

        if ($state == self::STATE_NOT_FOUND)
            return [
                'status' => $this->scorings::STATUS_COMPLETED,
                'body' => $response,
                'success' => 0,
                'string_result' => 'Проверка не пройдена: '. $response->message ?? 'ИНН не найден'
            ];

        if (empty($user->inn) && !empty($response->inn))
            $this->users->update_user($user->id, [
                'inn' => (string)$response->inn
            ]);

        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'body' => $response,
            'success' => 1,
            'string_result' => (string)$response->inn
        ];
        // endregion
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
                'string_result' => $string_result,
                'end_date' => date('Y-m-d H:i:s'),
            ];

        return [
            'status' => $this->scorings::STATUS_ERROR,
            'success' => 0,
            'string_result' => $string_result,
            'body' => $body,
            'end_date' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Истекло ли время ожидания запроса
     * @param string $startDate Дата начала скоринга `$scoring->start_date`
     * @return bool
     * @throws Exception
     */
    private function isTimeOut($startDate)
    {
        $startDate = new DateTime($startDate);
        $currentDate = new DateTime();
        // Разница между временем старта и текущим временем
        $interval = $currentDate->diff($startDate);
        // Преобразуем разницу в минуты
        $minutes = $interval->days * 24 * 60;
        $minutes += $interval->h * 60;
        $minutes += $interval->i;
        return $minutes >= self::REQUEST_TIMEOUT_MINUTES;
    }
}
