<?php

require_once 'Simpla.php';

/**
 * SmsAuthValidate class extends the Simpla class to handle functionalities
 * related to SMS authentication validation.
 */
class SmsAuthValidate extends Simpla
{
    /**
     * Флаг при входе
     */
    public const TYPE_LOGIN = 'login';

    /**
     * Регистрация
     */
    public const TYPE_REGISTRATION = 'registration';

    /**
     * Автоподтверждение
     */
    public const TYPE_AUTOCONFIRM = 'autoconfirm';

    /**
     * Добавление данных о проверки кода
     *
     * @param string $phone
     * @param string $type
     * @return mixed
     */
    public function add(string $phone, string $type = 'login')
    {
        $last_validate_at = date('Y-m-d H:i:s');
        $repeats = 1;

        $query = $this->db->placehold("INSERT INTO __sms_auth_validate SET ?%", compact('phone', 'type', 'last_validate_at', 'repeats'));
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получает данные о проверки кодов
     *
     * @param string $phone
     * @param string $type
     * @return false|int
     */
    public function get(string $phone, string $type = 'login')
    {
        $sql = $this->db->placehold("SELECT * FROM __sms_auth_validate WHERE phone = ? AND type = ?", $phone, $type);
        $this->db->query($sql);
        return $this->db->result();
    }

    /**
     * Чистим запись
     *
     * @param string $phone
     * @param string $type
     * @return mixed
     */
    public function delete(string $phone, string $type = '')
    {
        if (!$type) {
            $query = $this->db->placehold("DELETE FROM __sms_auth_validate WHERE phone = ?", $phone);
        } else {
            $query = $this->db->placehold("DELETE FROM __sms_auth_validate WHERE phone = ? AND type = ?", $phone, $type);
        }

        return $this->db->query($query);
    }

    /**
     * Обновляет данные проверки кодов
     *
     * @param int $id Идентификатор записи
     * @param array $data Массив данных для обновления
     *                    - phone            varchar(32)   not null - Номер телефона
     *                    - type             varchar(32)   null - Тип авторизации
     *                    - repeats          int default 0 not null - Кол-во попыток
     *                    - last_validate_at datetime      not null - Дата последней ошибки
     * @return bool Возвращает true в случае успешного обновления, иначе false
     */
    public function update(int $id, array $data)
    {
        $data['last_validate_at'] = date('Y-m-d H:i:s');
        $query = $this->db->placehold("UPDATE __sms_auth_validate SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Валидация смс кодов
     *
     * @param string $phone
     * @param string $type
     * @return array|true[]
     * @throws Exception
     */
    public function validateSms(string $phone, string $type): array
    {
        $model = $this->get($phone, $type);

        if (!$model) {
            $this->add($phone, $type);
            return [
                'success' => true,
            ];
        } else {

            if ($model->repeats > 2 && $model->repeats < 5) {
                $validate_seconds = 300;  // 4-5 попытка интервал 5 минут
            } elseif ($model->repeats > 4 && $model->repeats < 10) {
                $validate_seconds = 3600;   // 6-10 попытка интервал 1 час
            } elseif ($model->repeats > 10) {
                $validate_seconds = 86400; // с 11 попытки сутки
            }

            if (isset($validate_seconds)) {
                list($result, $diff_seconds) = $this->diffSeconds($model->last_validate_at, $validate_seconds);
                if (!$result) {
                    return [
                        'error' => "Повторная отправка SMS возможна только через $diff_seconds секунд",
                        'success' => false,
                    ];
                }
            }

            $this->update($model->id, ['repeats' => ++$model->repeats]);
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Вычисляет разницу в секундах между текущим временем и указанным временем
     *
     * @param string $last_validate_at Время последней валидации в формате строки
     * @param int $seconds Секунды сколько надо проверить
     * @return array Разница в секундах
     * @throws Exception
     */
    private function diffSeconds(string $last_validate_at, int $seconds): array
    {
        $lastValidateTime = new \DateTime($last_validate_at);
        $targetTime = clone $lastValidateTime; // Клонируем, чтобы не изменять исходную дату
        $targetTime->add(new \DateInterval('PT' . $seconds . 'S')); // Добавляем секунды

        $currentTime = new \DateTime();
        $diffInSeconds = $targetTime->getTimestamp() - $currentTime->getTimestamp();

        return [ $diffInSeconds <= 0, $diffInSeconds ];
    }
}
