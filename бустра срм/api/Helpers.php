<?php

/**
 * Class Helpers
 * Класс с хелперами
 */

final class Helpers extends Simpla
{
    /** @var int[] Смещения часовых поясов относительно московского (GMT+3) */
    private const REGIONS_TIMEZONE = [
        'калинингр' => -1,
        'тыва' => 4,
        'ямало' => 2,
        'камчат' => 9,
        'моск' => 0,
        'коми' => 0,
        "магад" => 8,
        "хакас" => 4,
        "санкт" => 0,
        "ленинг" => 0,
        "нижегород" => 0,
        "татарст" => 0,
        "ростов" => 0,
        "вороноб" => 0,
        "воронеж" => 0,
        "краснодар" => 1,
        "саратов" => 1,
        "ярослав" => 0,
        "дагестан" => 0,
        "рязан" => 0,
        "пенз" => 0,
        "липецк" => 0,
        "киров" => 0,
        "чуваш" => 0,
        "тульс" => 0,
        "курск" => 0,
        "ставропол" => 0,
        "твер" => 0,
        "иванов" => 0,
        "брянск" => 0,
        "белгород" => 0,
        "владимир" => 0,
        "архангел" => 0,
        "калуж" => 0,
        "калуг" => 0,
        "смоленск" => 0,
        "вологод" => 0,
        "мордов" => 0,
        "орлов" => 0,
        "осетия" => 0,
        "чечен" => 0,
        "муромс" => 0,
        "тамбов" => 0,
        "карел" => 0,
        "костром" => 0,
        "марий" => 0,
        "кабардин" => 0,
        "новгород" => 0,
        "псков" => 0,
        "ставрополь" => 0,
        "крым" => 0,
        "самар" => 1,
        "волгоград" => 1,
        "удмурт" => 1,
        "ульяновск" => 1,
        "астрахан" => 1,
        "свердлов" => 2,
        "челябинск" => 2,
        "башкор" => 2,
        "перм" => 2,
        "тюмен" => 2,
        "оренбург" => 2,
        "ханты" => 2,
        "курган" => 2,
        "новосибир" => 4,
        "краснояр" => 4,
        "алтай" => 4,
        "томск" => 4,
        "кемеров" => 4,
        "иркутс" => 5,
        "бурят" => 5,
        "забайк" => 6,
        "саха" => 6,
        "якут" => 6,
        "амур" => 6,
        "хабаров" => 7,
        "приморс" => 7,
        "омск" => 3,
    ];

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
     * Translit from ru to lat
     * @param string $text
     * @return string
     */
    public static function translit($text)
    {
        $rus = array('ё', 'ж', 'ц', 'ч', 'ш', 'щ', 'ю', 'я', 'Ё', 'Ж', 'Ц', 'Ч', 'Ш', 'Щ', 'Ю', 'Я');
        $lat = array('yo', 'zh', 'tc', 'ch', 'sh', 'sh', 'yu', 'ya', 'YO', 'ZH', 'TC', 'CH', 'SH', 'SH', 'YU', 'YA');
        $text = str_replace($rus, $lat, $text);
        preg_match_all('/./u', 'АБВГДЕЗИЙКЛМНОПРСТУФХЪЫЬЭабвгдезийклмнопрстуфхъыьэ', $keys);
        preg_match_all('/./u', 'ABVGDEZIJKLMNOPRSTUFH_I_Eabvgdezijklmnoprstufh_i_e', $vals);
        if (!empty($keys[0]) && !empty($vals[0])) {
            $text = strtr($text, array_combine($keys[0], $vals[0]));
        }
        return $text;
    }

    /**
     * Получает массив дат для фильтрации
     * @param View $view
     * @param bool $get
     * @return array
     */
    public static function getDataRange( \View $view, bool $get = true): array
    {
        $filter_data = [];

        $filter_date_start = date('Y-m-d');
        $filter_date_end = date('Y-m-d');

        $filter_date_range = $view->request->{$get ? 'get' : 'post'}('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0]);
            $filter_date_end = str_replace('.', '-', $filter_date_array[1]);
        }

        $filter_data['filter_date_start'] = $filter_date_start;
        $filter_data['filter_date_end'] = $filter_date_end;

        return $filter_data;
    }

    /**
     * Проверяет вхождение даты в промежуток
     * @param $start_date
     * @param $end_date
     * @param $date_from_user
     * @return bool
     */
    public static function check_in_range($start_date, $end_date, $date_from_user): bool
    {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);
        // Check that user date is between start & end
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }

    /**
     * Возвращает ФИО пользователя
     * @param $user
     * @param string $separator
     * @return string
     */
    public static function getFIO($user, string $separator = ' '): string
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
     * @param array $calls
     * @param array $lastCalls
     * @return array
     * @throws Exception
     */
    public static function filterCalls(array $calls, array $lastCalls): array
    {
        foreach ($calls as $call) {
            if (!array_key_exists($call->user_id, $lastCalls)) {
                $lastCalls[$call->user_id] = $call;

                continue;
            }

            if (new DateTime($lastCalls[$call->user_id]->created) < new DateTime($call->created)) {
                $lastCalls[$call->user_id] = $call;
            }
        }

        return $lastCalls;
    }

    /**
     * @return string
     */

    public static function generateLink(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Разделяет серию и номер паспорта.
     * Работает с входом как с пробелами, так и без — предварительно очищает все нецифровые символы.
     *
     * Важно: в отличие от документов-специфичного форматирования, здесь серия возвращается без пробела (например, '3213'),
     * а номер — оставшиеся цифры после первых четырех.
     *
     * Примеры:
     * - '32 13 355283' -> ['serial' => '3213', 'number' => '355283']
     * - '3213355283'   -> ['serial' => '3213', 'number' => '355283']
     *
     * @param string $serial Паспортная строка (с пробелами или без)
     * @return array{serial:string, number:string} Массив с ключами 'serial' (первые 4 цифры без пробела) и 'number' (остальные цифры)
     */
    public static function splitPassportSerial(string $serial): array
    {
        $clear_passport_serial = preg_replace('/[^0-9]/', '', $serial);
        $passport_serial = substr($clear_passport_serial, 0, 4);
        $passport_number = substr($clear_passport_serial, 4);

        return [
            'number' => $passport_number,
            'serial' => $passport_serial
        ];
    }

    /**
     * Возвращает смещение часового пояса **относительно Москвы (GMT+3)**.
     *
     * У аргумента `$order` должно быть заполнено одно из следующих полей:
     * - `$order->Regregion`
     * - `$order->Faktregion`
     * - `$order->Regcity`
     * - `$order->Faktcity`
     *
     * Функционал взят из 1С. API метод для получения часового пояса из 1С - $this->soap->getContractsTimezone().
     * Однако метод в 1С работает медленно, поэтому функционал скопирован сюда.
     *
     * При изменении часового пояса в 1С необходимо внести изменения в Helpers::REGIONS_TIMEZONE!
     *
     * 1. Регион регистрации
     * 2. Регион проживания
     * 3. Город регистрации
     * 4. Город проживания
     *
     * @param stdClass $order
     * @return int
     */
    public static function getRegionTimezone(stdClass $order): int
    {
        if (!empty($order->Regregion)) {
            foreach (self::REGIONS_TIMEZONE as $regionTimezoneName => $regionTimezoneValue) {
                if (mb_stripos($order->Regregion, $regionTimezoneName) !== false) {
                    return $regionTimezoneValue;
                }
            }
        }

        if (!empty($order->Faktregion)) {
            foreach (self::REGIONS_TIMEZONE as $regionTimezoneName => $regionTimezoneValue) {
                if (mb_stripos($order->Faktregion, $regionTimezoneName) !== false) {
                    return $regionTimezoneValue;
                }
            }
        }

        if (!empty($order->Regcity)) {
            foreach (self::REGIONS_TIMEZONE as $regionTimezoneName => $regionTimezoneValue) {
                if (mb_stripos($order->Regcity, $regionTimezoneName) !== false) {
                    return $regionTimezoneValue;
                }
            }
        }

        if (!empty($order->Faktcity)) {
            foreach (self::REGIONS_TIMEZONE as $regionTimezoneName => $regionTimezoneValue) {
                if (mb_stripos($order->Faktcity, $regionTimezoneName) !== false) {
                    return $regionTimezoneValue;
                }
            }
        }

        return 0;
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

    public function isDev(): bool
    {
        $is_dev = $this->config->is_dev; // (string)true = '1'; (string)false = ''

        if (!empty($is_dev) && $is_dev === '1') {
            return true;
        }

        return false;
    }
}
