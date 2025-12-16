<?php

require_once( __DIR__ . '/../api/Simpla.php');

class Bonondo extends Simpla
{
    public const PK_POSTFIX = 'pk'; // поток повторных клиентов
    public const NK_POSTFIX = 'nk'; // поток новых клиентов со флоу регистрации
    public const NK_ACC_POSTFIX = 'nk-acc'; // поток новых клиентов (без выданных займов) ил ЛК
    public const CLIENT_TYPES = [
        'NK' => 'NK',
        'PK' => 'PK',
    ];

    /** @var int Настройка для пропуска отказного флоу срабатывает всегда */
    const SKIP_CHANCE_0 = 0;

    /** @var int Настройка для пропуска отказного флоу делит поток пополам */
    const SKIP_CHANCE_50 = 1;

    /** @var int Настройка для пропуска отказного флоу на паузе */
    const SKIP_CHANCE_100 = 2;

    /**
     * Список скорингов которые нужно дождаться для вынесения решения.
     */
    const REQUIRED_SCORINGS = [
        Scorings::TYPE_AXILINK_2,
    ];

    /**
     * Общий список скорингов НК.
     */
    const NK_ALL_SCORINGS = [
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_HYPER_C,
    ];

    /**
     * Причины отказов
     */
    const REJECT_REASONS = [
        // Для скоринга АксиНБКИ отдельная логика получения причины отказа, Ctrl+F getAxiRejectReason
        Scorings::TYPE_AGE => 23,
        Scorings::TYPE_BLACKLIST => 2,
        // Scorings::TYPE_FNS - Не может дать отказ
        Scorings::TYPE_EFRSB => 22,
        Scorings::TYPE_LOCATION => 14,
        Scorings::TYPE_SCORISTA => 5,
        Scorings::TYPE_HYPER_C => 61,
    ];

    /**
     * Проверяем, должен ли клиент проходить по флоу с возможной продажей карты.
     *
     * По флоу не проходит органика с 10 до 17 МСК.
     *
     * В остальных случаях клиент проходит по флоу если подходит под настройки https://manager.boostra.ru/declined_traffic_settings
     * @return bool
     */
    function trySkipCheck($user): bool
    {
        $is_skipped = $this->user_data->read($user->id, 'rejected_nk_skipped');
        if (isset($is_skipped) && $is_skipped == 1) {
            return true; // Проверка уже проводилась
        }

        // Включен ли Банон в настройках сайта
        // Если продажа отказных НК сейчас выключена, отмечаем не прошедшего проверок клиента как не проданного
        $bonon_enabled = $this->settings->bonon_enabled;
        if (empty($bonon_enabled)) {
            $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
            $this->user_data->set($user->id, 'bonon_disabled', 1);
            return true;
        }

        if($this->checkExcludedUtm($user->id)) {
            $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
            $this->user_data->set($user->id, 'bonon_utm_skipped', 1);
            return true;
        }

        $inn_not_found = $this->user_data->read($user->id, 'inn_not_found');
        if (!empty($inn_not_found)) {
            // Данные заполнены некорректно, ИНН не нашло
            $this->user_data->set($user->id, 'bonon_inn_skipped', 1);
            $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
            return true;
        }

        // Проверки для органики
        if ($this->users->checkUtmSource($user->id)) {
            // Клиент - органика
            $dayOfWeek = date('N');
            if ($dayOfWeek < 6) {
                // Рабочий день
                $currentHour = date('G');
                if ($currentHour >= 10 && $currentHour <= 17) {
                    // Промежуток между 10 и 17 МСК, в это время действует безопасный флоу
                    // Органика пропускает этап с проверкой на необходимость продажи
                    $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
                    $this->user_data->set($user->id, 'bonon_organic_skipped', 1);
                    return true;
                }
            }
        }

        // Пропуск повторных НК
        if ($orders = $this->orders->get_orders(['user_id' => $user->id])) {
            if (count($orders) > 1) {
                // У клиента >1 заявки, пропускаем повторного НК мимо продажи
                $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
                $this->user_data->set($user->id, 'bonon_pk_skipped', 1);
                return true;
            }
        }

        // Ищем подходящую настройку
        $setting = $this->bonondo->getBononSourceSetting($user->utm_source, $user->utm_medium);
        if (empty($setting)) {
            // utm_source в заявке может отличаться, ищем настройку по ней тоже
            $last_order = $this->orders->get_last_order($user->id);
            if (!empty($last_order)) {
                $setting = $this->bonondo->getBononSourceSetting($last_order->utm_source, $last_order->utm_medium);
            }
        }

        if (empty($setting)) {
            // Настройки на продажу нет, пропускаем
            $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
            $this->user_data->set($user->id, 'bonon_empty_setting', 1);
            return true;
        }

        switch ($setting->chance) {
            case self::SKIP_CHANCE_0:
                // Всегда идёт по флоу
                //$this->user_data->set($user->id, 'rejected_nk_skipped', 0);
                return false;

            case self::SKIP_CHANCE_50:
                // 50% шанс пропуска, разделение потока
                mt_srand();
                $is_skipped = mt_rand(0, 1);
                if($is_skipped) {
                    $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
                    $this->user_data->set($user->id, 'bonon_skip_chance', 50);
                }
                return !!$is_skipped;

            case self::SKIP_CHANCE_100:
            default:
                // На паузе
                $this->user_data->set($user->id, 'rejected_nk_skipped', 1);
                $this->user_data->set($user->id, 'bonon_skip_chance', 100);
                return true;
        }
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getDeclineState(int $user_id): array
    {
        $user = $this->users->get_user($user_id);
        $last_order = $this->orders->get_last_order($user_id);
        $scorings   = $this->scorings->get_scorings([
            'user_id' => $user_id,
            'type' => self::NK_ALL_SCORINGS,
        ]);
        $response = [
            'axilink' => null,
            'scorista' => null,
            'reason' => null,
        ];

        if (empty($scorings)) {
            // Так быть не должно, но такое, в теории, может произойти.
            // В таком случае пропускаем клиента дальше, на этап привязки карты
            $this->user_data->set($user_id, 'bonon_empty_scorings', 1);
            return $response;
        }

        // Нет автоотказа или автоодобрения скористы по leadstech, используется для HyperC
        if ($this->scorings->isHyperEnabledForUser($user)) {
            $required = [ $this->scorings::TYPE_HYPER_C ];
        } else {
            $required = self::REQUIRED_SCORINGS;
        }

        $scoring_result = array_fill_keys($required, -1);
        foreach ($scorings as $scoring) {
            if ($scoring->type == $this->scorings::TYPE_AXILINK_2) {
                $response['axilink'] = $scoring;
            }
            if ($scoring->type == $this->scorings::TYPE_SCORISTA) {
                $response['scorista'] = $scoring;
            }
            if(isset($scoring_result[$scoring->type])) {
                if ($scoring->status == Scorings::STATUS_ERROR) {
                    $scoring_result[$scoring->type] = -2;
                } elseif ($scoring->status == Scorings::STATUS_COMPLETED) {
                    $scoring_result[$scoring->type] = $scoring->success;
                }
            }
        }

        if (isset($response['scorista'])) {
            $scorista_body = $this->scorings->get_body_by_type($response['scorista']);
            if (!empty($scorista_body->additional->no_need_for_underwriter)) {
                $this->user_data->set($user_id, 'bonon_scorista_nnfu', 1);
                return $response;
            }
        }

        $has_uncompleted_scoring = !empty(array_filter($scoring_result, fn($scor_result) => $scor_result == -1));
        if(!$has_uncompleted_scoring) {
            // Смотрим, есть ли отказ по одному из скорингов и, если есть - продаём клиента
            foreach ($scoring_result as $type => $result) {
                if ($result == 0) {
                    if ($type == $this->scorings::TYPE_AXILINK_2) {
                        $axi_body = $this->scorings->get_body_by_type($response['axilink']);
                        if (!empty($axi_body) && $axi_body->message) {
                            $axi_reason = $this->scorings->getAxiRejectReason($user_id, $axi_body->message);
                            if (empty($axi_reason) || ($axi_reason == $this->reasons::REASON_SCORISTA
                                && $this->order_data->read($last_order->id, $this->order_data::FAKE_SCORISTA_AMOUNT))) {

                                continue;
                            }
                            $response['reason'] = $axi_reason;
                        }
                    } else {
                        $response['reason'] = (self::REJECT_REASONS[$type] ?? $this->reasons::REASON_CARD_SELLED_TO_BONON);
                    }
                    return $response;
                }
            }
        } else {
            $response['reason'] = -1;
        }
        return $response;
    }

    /**
     * @param int $user_id
     * @param int $order_id
     * @param string $type
     * @return void
     */
    public function tryToSell(int $user_id, $type = self::CLIENT_TYPES['NK']): void
    {
        if($user_id && ($user = $this->users->get_user($user_id))) {
            switch($type) {
                case self::CLIENT_TYPES['NK']:
                    $is_rejected_nk = $this->user_data->read($user_id, 'is_rejected_nk');
                    // Решение уже принято
                    if(isset($is_rejected_nk)) {
                        return;
                    }

                    // Клиент должен идти по обычному флоу без вероятности продажи карты
                    if ($this->trySkipCheck($user)) {
                        $this->user_data->set($user_id, 'is_rejected_nk', 0);
                        return;
                    }

                    $last_order = $this->orders->get_last_order($user_id);
                    // Заявка уже в отказе, отмечаем клиента для продажи
                    if ($last_order && $last_order->status == $this->orders::STATUS_REJECTED) {
                        $this->user_data->set($user_id, 'is_rejected_nk', 1);
                        return;
                    }

                    // Проверяем готовность скорингов
                    // Если $decline_reason > 0, есть причина отказа, отмечаем клиента для продажи
                    // Если $decline_reason === null, скоринги по клиенту отсутствуют либо пройдены успешно
                    $decline_state = $this->getDeclineState($user_id);
                    if(!isset($decline_state['reason'])) {
                        $this->user_data->set($user_id, 'is_rejected_nk', 0);
                        // Если включен функционал отправки смс при одобрении скористы и скориста одобрила, то отправляем смс
                        $needNotifyUserWhenScoristaSuccess = $this->settings->need_notify_user_when_scorista_success;
                        if (!empty($needNotifyUserWhenScoristaSuccess) && $decline_state['scorista'] && !empty($decline_state['scorista']->success)) {
                            $this->scorings->sendSmsSuccessScorista($decline_state['scorista']);
                        }
                    } elseif($decline_state['reason'] > 0) {
                        $this->user_data->set($user_id, 'is_rejected_nk', 1);
                    }

                    break;
            }
        }
    }

    /**
     * Флоу для клиентов, прошедших проверку
     *
     * С клиентом всё хорошо, либо при проверках произошла ошибка.
     * @param int $user_id
     * @return null
     */
    function userApprove(int $user_id): void
    {
        if ($last_order = $this->orders->get_last_order($user_id)) {
            if ($last_order->status == $this->orders::STATUS_REJECTED) {
                $this->leadgid->reject_actions($last_order->id);
            }
        }
    }

    /**
     * Флоу для клиентов, не прошедших проверку и переданных Бонону
     *
     * @param int $user_id
     * @return null
     */
    function userDecline(int $user_id): void
    {
        // Отказ по заявке без отправки постбеков и смс
        if ($order = $this->orders->get_last_order($user_id)) {
            $this->order_data->set($order->id, 'is_sold_to_bonon', 1);

            if ($order->status != $this->orders::STATUS_REJECTED) {
                $manager = $this->managers->get_crm_manager($this->managers::MANAGER_SYSTEM_ID);
                $decline_state = $this->getDeclineState($user_id);
                $update_order = [
                    'status' => $this->orders::STATUS_REJECTED,
                    'manager_id' => $manager->id,
                    'reason_id' => $decline_state['reason'],
                    'reject_date' => date('Y-m-d H:i:s')
                ];
                $this->orders->update_order($order->id, $update_order);

                $changeLogs = Helpers::getChangeLogs($update_order, $order);
                $this->changelogs->add_changelog(array(
                    'manager_id' => $manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'status',
                    'old_values' => serialize($changeLogs['old']),
                    'new_values' => serialize($changeLogs['new']),
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ));

                $reason = $this->reasons->get_reason($decline_state['reason']);
                $this->soap->update_status_1c($order->{'1c_id'}, $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND, $manager->name_1c, 0, 1, $reason->admin_name);
                $this->soap->send_order_manager($order->{'1c_id'}, $manager->name_1c);

                $this->scorings->stopOrderScorings($order->id, ['string_result' => 'Причина: Карта отказного НК продана']);
            }
        }

        if ($this->short_flow->isShortFlowUser($user_id)) {
            $this->short_flow->setRegisterStage($user_id, $this->short_flow::STAGE_BONON);
        }
    }
    
    /**
     * @param object $order
     * @param string $postfix
     * @return string|null
     */
    public function createClientUrlForOrder($order, $postfix)
    {
        $user = $this->users->get_user((int)$order->user_id);
        if (!$user) {
            return null;
        }

        if (empty($order->order_id)) {
            $order->order_id = $order->id;
        }

        $params = $this->buildParams($order, $user);

        try {
            $bonon_api = new BonondoApi($postfix);
            $loaner = $bonon_api->createLoaner($params);
        } catch (Exception $e) {
            $this->logging(
                __METHOD__ . " - Order #$order->order_id",
                null,
                $params,
                'Error: ' . $e->getMessage(),
                'bonondo.txt'
            );
            return null;
        }

        if ($loaner
            && isset($loaner['clientUrl'])
            && $clientUrl = $loaner['clientUrl']
        ) {
            return $clientUrl;
        }

        $this->logging(
            __METHOD__ . " - Order #$order->order_id",
            null,
            $params,
            $loaner,
            'bonondo.txt'
        );

        return null;
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

        list($factRegion, $factTimezone) = $this->getRegion($user->Faktregion_code, $user->Faktregion);
        list($regRegion) = $this->getRegion($user->Regregion_code, $user->Regregion);

        $referer = $this->order_data->read($order->id, $this->order_data::HTTP_REFERER);
        $userAgent = $this->order_data->read($order->id, $this->order_data::USERAGENT);

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
            'guru_id'                  => '',
            'guru_data'                => '',
            'referer'                  => $referer ?? '',
            'ip'                       => $order->ip,
            'user_agent'               => $userAgent ?? '',
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
    private function getRegion($region_code, $region_name)
    {
        $excl = ['республика', 'респ', 'край', 'область', 'округ', 'автономная', 'автономный'
                 , 'област', 'облас', 'обла', 'район', 'улица', 'бласть', 'город', 'области', ];
        if($region_code && $region = $this->getRegions()[$region_code] ?? null) {
            return [$region[0], $region[1]];
        } elseif($region_name) {
            $region_name = str_replace(['.', '-', ',', '/', '(', ')', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ' ', $region_name);
            $token = strtok($region_name, ' ');
            while($token !== false) {
                $token = mb_strtolower($token);
                if(strlen($token) > 3 && !in_array($token, $excl)) {
                    foreach($this->getRegions() as $region) {
                        if(strpos(mb_strtolower($region[0]), $token) !== false) {
                            return [$region[0], $region[1]];
                        }
                    }
                }
                $token = strtok(' ');
            }
        }

        return null;
    }

    /**
     * @return array[]
     */
    private function getRegions()
    {
        return [
            '77' => ['г. Москва', 'Europe/Moscow'],
            '50' => ['Московская область', 'Europe/Moscow'],
            '78' => ['г. Санкт-Петербург', 'Europe/Moscow'],
            '47' => ['Ленинградская область', 'Europe/Moscow'],
            '01' => ['Республика Адыгея', 'Europe/Moscow'],
            '04' => ['Республика Алтай', 'Asia/Krasnoyarsk'],
            '22' => ['Алтайский край', 'Asia/Krasnoyarsk'],
            '28' => ['Амурская область', 'Asia/Yakutsk'],
            '29' => ['Архангельская область', 'Europe/Moscow'],
            '30' => ['Астраханская область', 'Europe/Samara'],
            '02' => ['Республика Башкортостан', 'Asia/Yekaterinburg'],
            '31' => ['Белгородская область', 'Europe/Moscow'],
            '32' => ['Брянская область', 'Europe/Moscow'],
            '03' => ['Республика Бурятия', 'Asia/Irkutsk'],
            '33' => ['Владимирская область', 'Europe/Moscow'],
            '34' => ['Волгоградская область', 'Europe/Moscow'],
            '35' => ['Вологодская область', 'Europe/Moscow'],
            '36' => ['Воронежская область', 'Europe/Moscow'],
            '05' => ['Республика Дагестан', 'Europe/Moscow'],
            '79' => ['Еврейская автономная область', 'Asia/Vladivostok'],
            '75' => ['Забайкальский край', 'Asia/Yakutsk'],
            '90' => ['Запорожская область', 'Europe/Moscow'],
            '37' => ['Ивановская область', 'Europe/Moscow'],
            '06' => ['Республика Ингушетия', 'Europe/Moscow'],
            '38' => ['Иркутская область', 'Asia/Irkutsk'],
            '07' => ['Кабардино-Балкарская Республика', 'Europe/Moscow'],
            '39' => ['Калининградская область', 'Europe/Kaliningrad'],
            '08' => ['Республика Калмыкия', 'Europe/Moscow'],
            '40' => ['Калужская область', 'Europe/Moscow'],
            '41' => ['Камчатский край', 'Asia/Kamchatka'],
            '10' => ['Республика Карелия', 'Europe/Moscow'],
            '09' => ['Карачаево-Черкесская Республика', 'Europe/Moscow'],
            '42' => ['Кемеровская область — Кузбасс', 'Asia/Krasnoyarsk'],
            '43' => ['Кировская область', 'Europe/Moscow'],
            '11' => ['Республика Коми', 'Europe/Moscow'],
            '55' => ['Омская область', 'Asia/Omsk'],
            '44' => ['Костромская область', 'Europe/Moscow'],
            '23' => ['Краснодарский край', 'Europe/Moscow'],
            '24' => ['Красноярский край', 'Asia/Krasnoyarsk'],
            '45' => ['Курганская область', 'Asia/Yekaterinburg'],
            '46' => ['Курская область', 'Europe/Moscow'],
            '48' => ['Липецкая область', 'Europe/Moscow'],
            '49' => ['Магаданская область', 'Asia/Magadan'],
            '51' => ['Мурманская область', 'Europe/Moscow'],
            '83' => ['Ненецкий автономный округ', 'Europe/Moscow'],
            '52' => ['Нижегородская область', 'Europe/Moscow'],
            '53' => ['Новгородская область', 'Europe/Moscow'],
            '54' => ['Новосибирская область', 'Asia/Krasnoyarsk'],
            '12' => ['Республика Марий Эл', 'Europe/Moscow'],
            '13' => ['Республика Мордовия', 'Europe/Moscow'],
            '56' => ['Оренбургская область', 'Asia/Yekaterinburg'],
            '57' => ['Орловская область', 'Europe/Moscow'],
            '58' => ['Пензенская область', 'Europe/Moscow'],
            '59' => ['Пермский край', 'Asia/Yekaterinburg'],
            '25' => ['Приморский край', 'Asia/Vladivostok'],
            '60' => ['Псковская область', 'Europe/Moscow'],
            '61' => ['Ростовская область', 'Europe/Moscow'],
            '62' => ['Рязанская область', 'Europe/Moscow'],
            '63' => ['Самарская область', 'Europe/Samara'],
            '64' => ['Саратовская область', 'Europe/Samara'],
            '14' => ['Республика Саха (Якутия)', 'Asia/Yakutsk'],
            '65' => ['Сахалинская область', 'Asia/Magadan'],
            '66' => ['Свердловская область', 'Asia/Yekaterinburg'],
            '15' => ['Республика Северная Осетия — Алания', 'Europe/Moscow'],
            '67' => ['Смоленская область', 'Europe/Moscow'],
            '26' => ['Ставропольский край', 'Europe/Moscow'],
            '68' => ['Тамбовская область', 'Europe/Moscow'],
            '16' => ['Республика Татарстан', 'Europe/Moscow'],
            '69' => ['Тверская область', 'Europe/Moscow'],
            '70' => ['Томская область', 'Asia/Krasnoyarsk'],
            '71' => ['Тульская область', 'Europe/Moscow'],
            '17' => ['Республика Тыва', 'Asia/Krasnoyarsk'],
            '72' => ['Тюменская область', 'Asia/Yekaterinburg'],
            '18' => ['Удмуртская Республика', 'Europe/Samara'],
            '73' => ['Ульяновская область', 'Europe/Samara'],
            '27' => ['Хабаровский край', 'Asia/Vladivostok'],
            '19' => ['Республика Хакасия', 'Asia/Krasnoyarsk'],
            '86' => ['Ханты-Мансийский автономный округ — Югра', 'Asia/Yekaterinburg'],
            '74' => ['Челябинская область', 'Asia/Yekaterinburg'],
            '21' => ['Чувашская Республика — Чувашия', 'Europe/Moscow'],
            '87' => ['Чукотский автономный округ', 'Asia/Kamchatka'],
            '89' => ['Ямало-Ненецкий автономный округ', 'Asia/Yekaterinburg'],
            '76' => ['Ярославская область', 'Europe/Moscow'],
            '92' => ['г. Севастополь', 'Europe/Moscow'],

            '80' => ['Донецкая народная республика', 'Europe/Moscow'],
            '81' => ['Луганская народная республика', 'Europe/Moscow'],
            '82' => ['Республика Крым', 'Europe/Moscow'],
            '84' => ['Херсонская область', 'Europe/Moscow'],
            '95' => ['Чеченская Республика', 'Europe/Moscow'],
        ];
    }

    /**
     * @param  string $date
     * @return string
     */
    public function convertDate($date)
    {
        return DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
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

    /**
     * @param $userId
     * @return bool
     */
    public function checkExcludedUtm($userId): bool
    {
        $query = $this->db->placehold('SELECT utm_source FROM s_users WHERE id = ?', $userId);
        $this->db->query($query);

        $utm_source = $this->db->result('utm_source');
        $exclusions = explode(',', $this->settings->bonon_excluded_utms);

        return in_array($utm_source, $exclusions);
    }

    /**
     * Поиск подходящей настройки по продаваемости источника
     *
     * https://manager.boostra.ru/bonon_settings
     * @param string $utm_source
     * @param string $utm_medium
     * @return ArrayObject|false
     */
    public function getBononSourceSetting($utm_source, $utm_medium)
    {
        $sources = $this->settings->bonon_sources;
        if (empty($sources))
            return false;

        // Ищем наиболее подходящую настройку, если есть
        $foundSource = $foundAny = false;
        foreach ($sources['rows'] as $source) {
            if ($source->utm_source == $utm_source) {
                if ($source->utm_medium == $utm_medium)
                    return $source; // Точное совпадение по utm_source и utm_medium
                elseif ($source->utm_medium == '*')
                    $foundSource = $source; // Совпадение по utm_source, общая настройка для всех utm_medium
            }
            elseif ($source->utm_source == '*')
                $foundAny = $source; // Общая настройка для всех utm_source
        }
        return $foundSource ?: $foundAny;
    }
}