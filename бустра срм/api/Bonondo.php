<?php

require_once( __DIR__ . '/../api/Simpla.php');

class Bonondo extends Simpla
{
    /**
     * @param $order_id
     * @return array|null
     */
    public function sendShortApi($order_id)
    {   
        $order = $this->orders->get_order($order_id);
        $user  = $this->users->get_user((int)$order->user_id);
        if (!$user) {
            return null;
        }

        if (empty($order->order_id)) {
            $order->order_id = $order->id;
        }

        $params = $this->buildParams($order, $user);

        try {
            $response = $this->bonondoApi->sendShortApi($params, $user->site_id);
        } catch (Exception $e) {
            $this->logging(
                __METHOD__ . " - Order #$order->order_id",
                null,
                $params,
                'Error: ' . $e->getMessage(),
                'bonondo_short_api.txt'
            );
            return null;
        }

        $this->logging(
            __METHOD__ . " - Order #$order->order_id",
            null,
            $params,
            $response,
            'bonondo.txt'
        );

        return $response;
    }

    /**
     * @param int $order_id
     * @return array|null
     */
    public function sendIssuedLoan($order_id)
    {   
        $order = $this->orders->get_order($order_id);
        $user  = $this->users->get_user((int)$order->user_id);
        if (!$user) {
            return null;
        }

        $params = $this->buildParams($order, $user);

        try {
            $response = $this->bonondoApi->sendIssuedLoan($params, $user->site_id);
        } catch (Exception $e) {
            $this->logging(
                __METHOD__ . " - Order #$order->order_id",
                null,
                $params,
                'Error: ' . $e->getMessage(),
                'bonondo_issued_loans.txt'
            );
            return null;
        }

        $this->logging(
            __METHOD__ . " - Order #$order->order_id",
            null,
            $params,
            $response,
            'bonondo.txt'
        );

        return $response;
    }

    /**
     * @param int $order_id
     * @return array|null
     */
    public function sendUpcomingPayments($order_id)
    {   
        $order = $this->orders->get_order($order_id);
        $user  = $this->users->get_user((int)$order->user_id);
        if (!$user) {
            return null;
        }

        $params = $this->buildParams($order, $user);

        try {
            $response = $this->bonondoApi->sendUpcomingPayments($params, $user->site_id);
        } catch (Exception $e) {
            $this->logging(
                __METHOD__ . " - Order #$order->order_id",
                null,
                $params,
                'Error: ' . $e->getMessage(),
                'bonondo_upcoming_payments_api.txt'
            );
            return null;
        }

        $this->logging(
            __METHOD__ . " - Order #$order->order_id",
            null,
            $params,
            $response,
            'bonondo.txt'
        );

        return $response;
    }

    /**
     * @param int $order_id
     * @return array|null
     */
    public function sendOverduedPayments($order_id)
    {   
        $order = $this->orders->get_order($order_id);
        $user  = $this->users->get_user((int)$order->user_id);
        if (!$user) {
            return null;
        }

        $params = $this->buildParams($order, $user);

        try {
            $response = $this->bonondoApi->sendOverduedPayments($params, $user->site_id);
        } catch (Exception $e) {
            $this->logging(
                __METHOD__ . " - Order #$order->order_id",
                null,
                $params,
                'Error: ' . $e->getMessage(),
                'bonondo_overdued_payments_api.txt'
            );
            return null;
        }

        $this->logging(
            __METHOD__ . " - Order #$order->order_id",
            null,
            $params,
            $response,
            'bonondo.txt'
        );

        return $response;
    }

    /**
     * @param $order
     * @param $user
     * @return array
     */
    private function buildParams($order, $user)
    {
        $birthdate = $this->convertDate($user->birth);

        list($passportSeries, $passportNumber) = $this->parsePassportSeriasAndNumber($user->passport_serial);
        $passportDepartmentCode = $this->parsePassportDepartmentCode($user->subdivision_code);
        $passportDate = $this->convertDate($user->passport_date);

        $education = $this->mapEducation((int) $user->education);
        $gender = $this->mapGender((string) $user->gender);

        list($factRegion, $factTimezone) = $this->parseRegion($user->Faktregion);
        list($regRegion) = $this->parseRegion($user->Regregion);

        return [
            'phone'                    => $user->phone_mobile,
            'email'                    => $order->email,
            'first_name'               => $user->firstname,
            'patronymic'               => $user->patronymic,
            'last_name'                => $user->lastname,
            'birthdate'                => $birthdate,
            'passport_series'          => $passportSeries,
            'passport_number'          => $passportNumber,
            'passport_date'            => $passportDate,
            'passport_department_code' => $passportDepartmentCode,
            'birth_place'              => $user->birth_place,
            'gender'                   => $gender,
            'registration_region'      => $regRegion,
            'registration_city'        => $user->Regcity,
            'registration_street'      => $user->Regstreet,
            'registration_house'       => $user->Reghousing,
            'registration_apartment'   => $user->Regroom,
            'actual_region'            => $factRegion,
            'actual_city'              => $user->Faktcity,
            'actual_street'            => $user->Faktstreet,
            'actual_house'             => $user->Fakthousing,
            'ractual_apartment'        => $user->Faktroom,
            'timezone'                 => $factTimezone,
            'amount'                   => $order->amount,
            'term'                     => $order->period,
            'scorista'                 => $order->scorista_ball,
            'education'                => $education,
            'utm_source'               => $order->utm_source ?? '',
            'utm_medium'               => $order->utm_medium ?? '',
            'utm_campaign'             => $order->utm_campaign ?? '',
            'wm_id'                    => $order->webmaster_id ?? '',
            'click_id'                 => $order->click_hash ?? '',
            'juicy_id'                 => $order->juicescore_session_id ?? '',
        ];
    }

    /**
     * @param  string $passportDepartmentCode
     * @return string
     */
    private function parsePassportDepartmentCode($passportDepartmentCode)
    {
        return str_replace('-', '', $passportDepartmentCode);
    }

    /**
     * @param string $passport
     * @return string[]
     */
    private function parsePassportSeriasAndNumber($passport)
    {
        $passport = str_replace([' ', '-'], '', $passport);
        $series = substr($passport, 0, 4);
        $number = substr($passport, 4);

        return [$series, $number];
    }

    /**
     * @param  string $region
     * @return array|null
     */
    private function parseRegion($region)
    {
        $searchRegion = mb_strtolower(
            $this->cutShortDistrictName($region)
        );

        foreach ($this->getRegions() as list($region, $timezone)) {
            if (mb_strpos(mb_strtolower($region), mb_strtolower($searchRegion)) !== false) {
                return [$region, $timezone];
            }
        }

        return null;
    }

    /**
     * @param  string $region
     * @return string
     */
    private function cutShortDistrictName($region)
    {
        $shorts = [' область', 'обл', ' край', 'г. ', 'г ', 'респ', 'республика '];
        $region = str_replace($shorts, '', $region);

        return trim($region);
    }

    /**
     * @return array[]
     */
    private function getRegions()
    {
        return [
            ['г. Москва', 'Europe/Moscow'],
            ['Московская область', 'Europe/Moscow'],
            ['г. Санкт-Петербург', 'Europe/Moscow'],
            ['Ленинградская область', 'Europe/Moscow'],
            ['Республика Адыгея', 'Europe/Moscow'],
            ['Республика Алтай', 'Asia/Krasnoyarsk'],
            ['Алтайский край', 'Asia/Krasnoyarsk'],
            ['Амурская область', 'Asia/Yakutsk'],
            ['Архангельская область', 'Europe/Moscow'],
            ['Астраханская область', 'Europe/Samara'],
            ['Республика Башкортостан', 'Asia/Yekaterinburg'],
            ['Белгородская область', 'Europe/Moscow'],
            ['Брянская область', 'Europe/Moscow'],
            ['Республика Бурятия', 'Asia/Irkutsk'],
            ['Владимирская область', 'Europe/Moscow'],
            ['Волгоградская область', 'Europe/Moscow'],
            ['Вологодская область', 'Europe/Moscow'],
            ['Воронежская область', 'Europe/Moscow'],
            ['Республика Дагестан', 'Europe/Moscow'],
            ['Донецкая народная республика', 'Europe/Moscow'],
            ['Еврейская автономная область', 'Asia/Vladivostok'],
            ['Забайкальский край', 'Asia/Yakutsk'],
            ['Запорожская область', 'Europe/Moscow'],
            ['Ивановская область', 'Europe/Moscow'],
            ['Республика Ингушетия', 'Europe/Moscow'],
            ['Иркутская область', 'Asia/Irkutsk'],
            ['Кабардино-Балкарская Республика', 'Europe/Moscow'],
            ['Калининградская область', 'Europe/Kaliningrad'],
            ['Республика Калмыкия', 'Europe/Moscow'],
            ['Калужская область', 'Europe/Moscow'],
            ['Камчатский край', 'Asia/Kamchatka'],
            ['Республика Карелия', 'Europe/Moscow'],
            ['Карачаево-Черкесская Республика', 'Europe/Moscow'],
            ['Кемеровская область — Кузбасс', 'Asia/Krasnoyarsk'],
            ['Кировская область', 'Europe/Moscow'],
            ['Республика Коми', 'Europe/Moscow'],
            ['Костромская область', 'Europe/Moscow'],
            ['Краснодарский край', 'Europe/Moscow'],
            ['Красноярский край', 'Asia/Krasnoyarsk'],
            ['Республика Крым', 'Europe/Moscow'],
            ['г. Севастополь', 'Europe/Moscow'],
            ['Курганская область', 'Asia/Yekaterinburg'],
            ['Курская область', 'Europe/Moscow'],
            ['Липецкая область', 'Europe/Moscow'],
            ['Луганская народная республика', 'Europe/Moscow'],
            ['Магаданская область', 'Asia/Magadan'],
            ['Мурманская область', 'Europe/Moscow'],
            ['Ненецкий автономный округ', 'Europe/Moscow'],
            ['Нижегородская область', 'Europe/Moscow'],
            ['Новгородская область', 'Europe/Moscow'],
            ['Новосибирская область', 'Asia/Krasnoyarsk'],
            ['Республика Марий Эл', 'Europe/Moscow'],
            ['Республика Мордовия', 'Europe/Moscow'],
            ['Омская область', 'Asia/Omsk'],
            ['Оренбургская область', 'Asia/Yekaterinburg'],
            ['Орловская область', 'Europe/Moscow'],
            ['Пензенская область', 'Europe/Moscow'],
            ['Пермский край', 'Asia/Yekaterinburg'],
            ['Приморский край', 'Asia/Vladivostok'],
            ['Псковская область', 'Europe/Moscow'],
            ['Ростовская область', 'Europe/Moscow'],
            ['Рязанская область', 'Europe/Moscow'],
            ['Самарская область', 'Europe/Samara'],
            ['Саратовская область', 'Europe/Samara'],
            ['Республика Саха (Якутия)', 'Asia/Yakutsk'],
            ['Сахалинская область', 'Asia/Magadan'],
            ['Свердловская область', 'Asia/Yekaterinburg'],
            ['Республика Северная Осетия — Алания', 'Europe/Moscow'],
            ['Смоленская область', 'Europe/Moscow'],
            ['Ставропольский край', 'Europe/Moscow'],
            ['Тамбовская область', 'Europe/Moscow'],
            ['Республика Татарстан', 'Europe/Moscow'],
            ['Тверская область', 'Europe/Moscow'],
            ['Томская область', 'Asia/Krasnoyarsk'],
            ['Тульская область', 'Europe/Moscow'],
            ['Республика Тыва', 'Asia/Krasnoyarsk'],
            ['Тюменская область', 'Asia/Yekaterinburg'],
            ['Удмуртская Республика', 'Europe/Samara'],
            ['Ульяновская область', 'Europe/Samara'],
            ['Хабаровский край', 'Asia/Vladivostok'],
            ['Республика Хакасия', 'Asia/Krasnoyarsk'],
            ['Ханты-Мансийский автономный округ — Югра', 'Asia/Yekaterinburg'],
            ['Херсонская область', 'Europe/Moscow'],
            ['Челябинская область', 'Asia/Yekaterinburg'],
            ['Чеченская Республика', 'Europe/Moscow'],
            ['Чувашская Республика — Чувашия', 'Europe/Moscow'],
            ['Чукотский автономный округ', 'Asia/Kamchatka'],
            ['Ямало-Ненецкий автономный округ', 'Asia/Yekaterinburg'],
            ['Ярославская область', 'Europe/Moscow'],
        ];
    }

    /**
     * @param  string $date
     * @return string
     */
    public function convertDate($date)
    {
        $date_time = DateTime::createFromFormat('d.m.Y', $date);
        
        return $date_time ? $date_time->format('Y-m-d') : '';
    }

    /**
     * @param  int $education
     * @return int
     */
    private function mapEducation($education)
    {
        $educationMap = [
            1 => 5,
            2 => 3,
            3 => 4,
            4 => 2,
            5 => 8,
        ];

        return isset($educationMap[$education]) ? $educationMap[(int) $education] : 0;
    }

    /**
     * @param string $gender
     * @return int|null
     */
    private function mapGender($gender)
    {
        $genderMap = [
            'male' => 0,
            'female' => 1,
        ];

        return isset($genderMap[$gender]) ? $genderMap[$gender] : null;
    }
}