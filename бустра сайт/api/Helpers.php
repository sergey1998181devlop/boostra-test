<?php

/**
 * Class Helpers
 * Класс с хелперами
 */

final class Helpers extends Simpla
{
    /**
     * Соль для паролей
     */
    public const SALT_PASSWORD = 'dg234sfvas';

    /**
     * Get change logs [old, new] values
     * @param array $updateData
     * @param object $order
     * @return array
     */
    public static function getChangeLogs(array $updateData, object $order): array
    {
        $old = [];
        foreach ($updateData as $key => $val) {
            if ($order->$key != $val) {
                $old[$key] = $order->$key;
            }
        }
        return [
            'old' => $old,
            'new' => array_diff($updateData, $old)
        ];
    }

    /**
     * Валидация паспорта
     * @param string $passport_serial
     * @return bool
     * маска ввода 99 99 999999
     */
    public static function validatePassport(string $passport_serial): bool
    {
        preg_match("~^\d{2} \d{2} \d{6}~", $passport_serial, $matches);
        return !empty($matches);
    }

    /**
     * Возвращает ФИО пользователя
     * @param $user
     * @param string $separator
     * @return string
     */
    public static function getFIO($user, string $separator = ' ')
    {
        return implode($separator, [$user->lastname, $user->firstname, $user->patronymic]);
    }

    /**
     * Возвращает сокращенное ФИО пользователя
     * @param $user
     * @return string
     */
    public static function getShortFIO($user): string
    {
        $short_name = $user->lastname . ' ' . mb_substr($user->firstname, 0, 1) . '.';

        if ($user->patronymic) {
            $short_name .= ' ' . mb_substr($user->patronymic, 0, 1) . '.';
        }

        return $short_name;
    }

    /**
     * Генерирует новый пароль пользователя
     * @param string $password
     * @return array
     */
    public static function generatePassword(string $password): array
    {
        $salt = uniqid();
        $hash = hash('sha256', $salt . $password . self::SALT_PASSWORD);
        return compact('salt', 'hash');
    }

    /**
     * Проверяет пароль пользователь на соответствие
     * @param string $password
     * @param $password_data
     * @return bool
     */
    public static function validatePassword(string $password, $password_data): bool
    {
        $hash = hash('sha256', $password_data->salt . $password . self::SALT_PASSWORD);
        return $hash === $password_data->hash;
    }

    /**
     * Проверка, одобрена ли заявка
     * @param array $order
     * @return bool
     */
    public static function isApproved(array $order = []): bool
    {
        return !empty($order['approve_date'])
            && ((int)$order['status'] === 2)
            && (!in_array(
                $order['1c_status'],
                ['5.Выдан', '6.Закрыт']
            ));
    }

    /**
     * Проверка, висит ли текущий кредит
     * @param array|null $order
     * @return bool
     */
    public static function isTaken(?array $order = null): bool
    {
        if ($order === null) {
            return false;
        }
        if (! isset($order['1c_status'])) {
            return false;
        }
        return $order['1c_status'] === '5.Выдан';
    }

    /**
     * Проверяет заблокирован ли пользователь в 1С
     * @param Simpla $simpla
     * @param string $user_phone
     * @return bool
     */
    public static function isBlockedUserBy1C(\Simpla $simpla, string $user_phone): bool
    {
        $phone = $simpla->users->clear_phone($user_phone);
        $user = $simpla->users->get_user($phone);

        if (!empty($user)) {
            if ($user->blocked) {
                return true;
            }
            $response = $simpla->soap->get_client_state($user->lastname, $user->firstname, $user->patronymic, $user->birth);
        } else {
            $response = null;
        }

        return $response === $simpla->users::BLOCKED_USER_1C;
    }

    /**
     * Проверяет смс на флуд
     * @param Simpla $simpla
     * @param int $max_total
     * @param string $phone
     * @return false|int|void|null
     * @throws Exception
     */
    public static function validateFloodSMS(\Simpla $simpla, int $max_total, string $phone)
    {
        $white_ip_list = [
            '141.0.180.209',
            '46.0.237.22',
            '89.169.29.154',
        ];

        $sms_validate = $simpla->sms_validate->getRow($_SERVER['REMOTE_ADDR'], $phone);

        if (!empty($sms_validate) && !in_array($sms_validate->ip, $white_ip_list)) {
            $date = new DateTime($sms_validate->date_edit ?: $sms_validate->date_added);
            $date->setTime(0, 0); // ставим время для корректной проверки разницы в днях
            $interval_days = $date->diff((new DateTime()));

            if ($interval_days->days > 0) {
                $simpla->sms_validate->deleteRow($sms_validate->id);
            } else {
                if (($sms_validate->total ?? 0) > $max_total) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Разделение строки серии паспорта из БД на номер и серию
     *
     * (12 34 567890 -> serial: 1234, number: 567890)
     * @param string $serial
     * @return array
     */
    public static function splitPassportSerial(string $serial)
    {

        $numbersArray = explode(' ', $serial); // 12 34 567890
        $passport_number = $numbersArray[2]; // 567890
        $passport_serial = $numbersArray[0] . $numbersArray[1]; // 1234
        return [
            'number' => $passport_number,
            'serial' => $passport_serial
        ];
    }

    /**
     * Разделяет серию и номер паспорта для документов
     * Работает с паспортами БЕЗ пробелов (например '3213355283')
     * Возвращает серию С пробелом: '32 13'
     *
     * @param string $serial - паспорт (может быть с пробелами или без)
     * @return array ['serial' => '32 13', 'number' => '355283']
     */
    public static function splitPassportSerialForDocuments(string $serial): array
    {
        $clear = preg_replace('/[^0-9]/', '', $serial);
        if (strlen($clear) == 10) {
            return [
                'serial' => substr($clear, 0, 2) . ' ' . substr($clear, 2, 2),
                'number' => substr($clear, 4, 6)
            ];
        }
        return ['serial' => '', 'number' => ''];
    }

    /**
     * Валидация номера карты с помощью алгоритма "Луна"
     */
    public static function cardLunaValidate(string $cardNumber): bool
    {
        $s = strrev(preg_replace('/[^\d]/', '', $cardNumber));

        // вычисление контрольной суммы
        $sum = 0;
        for ($i = 0, $j = strlen($s); $i < $j; $i++) {
            // использовать четные цифры как есть
            if (($i % 2) == 0) {
                $val = $s[$i];
            } else {
                // удвоить нечетные цифры и вычесть 9, если они больше 9
                $val = $s[$i] * 2;
                if ($val > 9)  $val -= 9;
            }
            $sum += $val;
        }

        // число корректно, если сумма равна 10
        return (($sum % 10) == 0);
    }


    /**
     * Валидация срока действия карты
     * @param string $cardValidity 'мм / гг'
     * @return bool
     */
    public static function cardValidityValidate(string $cardValidity): bool
    {
        $expiryDateTime = \DateTime::createFromFormat('m / y / d', $cardValidity . ' / 1')->setTime(0, 0);
        $minDateTime = \DateTime::createFromFormat('m / y / d', '02 / 22 / 1')->setTime(0, 0);
        $maxDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
            ->add(\DateInterval::createFromDateString('10 year'));

        if ($expiryDateTime < $minDateTime) {
            return false;
        }
        if ($expiryDateTime > $maxDateTime) {
            return false;
        }
        return true;
    }

    /**
     * Требуется ли прикрепить фото, используется для нового флоу с УПРИДом
     * @param $user
     * @param $order
     * @return bool
     */
    public static function isFilesRequired($user, $order = null)
    {
        return true;
        $simpla = Simpla::getSimpla();

        $user = (array)$user;
        $user_id = $user['id'] ?? $user['user_id'];

        $isShortFlowUser = $simpla->short_flow->isShortFlowUser($user_id);

        // Для короткого флоу не надо
        if ($isShortFlowUser) {
            return false;
        }

        // Новое флоу выключено, всегда нужны фото
        $new_flow_enabled = $simpla->settings->new_flow_enabled;
        if (empty($new_flow_enabled))
            return true;

        if (empty($order)) {
            $order = $simpla->orders->get_last_order($user_id);
            // НК ещё без заявки
            if (empty($order))
                return false;
        }

        $order = (array)$order;
        // У ПК должны быть фото
        if ($order['have_close_credits'] == 1)
            return true;

        // Заявку взял верификатор
        if (!empty($order['manager_id']) && $order['manager_id'] != $simpla->managers::MANAGER_SYSTEM_ID)
            return true;

        return false;
    }

    public static function getSafeStringForXml(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        $initialSymbols = ['*', '«', '»', '&', '"', "'"];
        $formattedSymbols = ['', '', '', '', '&quot;', "\\'"];
        $string = trim(str_replace($initialSymbols, $formattedSymbols, $string));

        if ($string === '') {
            return 'НЕТ';
        }

        return $string;
    }

    public static function validate_passport_date($birth_date, $passport_date): ?string
    {
        if (empty($birth_date) || empty($passport_date)) {
            return false;
        }

        $birthDate = new DateTime($birth_date);
        $passportDate = new DateTime($passport_date);
        $today = new DateTime();

        $age = $today->diff($birthDate)->y;
        $passportAge = $passportDate->diff($birthDate)->y;

        if ($passportAge < 14) {
            return false;
        }

        if ($age > 20 && $passportAge < 20) {
            return false;
        }

        if ($age >= 45 && $passportAge < 45) {
            return false;
        }

        return true;
    }

    public function isDev(): bool
    {
        $is_dev = $this->config->is_dev; // (string)true = '1'; (string)false = ''

        if (!empty($is_dev) && $is_dev === '1') {
            return true;
        }

        return false;
    }
}
