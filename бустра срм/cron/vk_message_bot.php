<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';

/**
 * Преколекшн.
 * Рассылка сообщений через ВК бота клиентам с -5 — 0 днями оплаты.
 *
 * Таблица с настройками рассылаемых сообщений
 * https://manager.boostra.ru/vk_sending_settings
 */
class VkMessageBotCron extends Simpla
{
    private const GET_USERS_QUERY = "
        SELECT
            o.id, o.user_id, c.number,
            o.utm_source, o.organization_id,
            u.gender, u.regregion, u.faktregion, u.regcity, u.faktcity,
            u.firstname, u.patronymic, u.phone_mobile,
            TIMESTAMPDIFF(YEAR, STR_TO_DATE(u.birth, '%d.%m.%Y'), CURDATE()) AS age,
            s.success, s.scorista_ball
        FROM s_orders o
        INNER JOIN s_users u ON u.id = o.user_id
        LEFT JOIN s_contracts c ON c.id = o.contract_id
        LEFT JOIN s_scorings AS s ON s.user_id = o.user_id AND s.type = 1 AND s.status = 4
        WHERE c.number IN (?@)
        ORDER BY s.id DESC
        ";


    public function run()
    {
        if (!$this->vk_message_settings->isEnabled()) {
            echo 'Disabled';
            return;
        }

        // Получаем пользователей для рассылки
        $users = $this->getUsers();
        if (empty($users))
            return;

        // Генерация сообщений (Подставляем ссылку и имя)
        $messages = $this->getMessages($users);

        // Отправка сообщений
        $this->vk_message_settings->sendVkMessages($messages);
    }

    /**
     * Получение списка пользователей которым можно отправить сообщение в текущий час
     * @return array
     * @throws Exception
     */
    private function getUsers()
    {
        $active_settings = $this->vk_message_settings->getWhere([
            'enabled'   => 1
        ]);

        // Определяем актуальные границы просрочки чтобы не запрашивать лишнего
        $day_from = null;
        $day_to = null;
        foreach ($active_settings as $setting) {
            if (!isset($day_from)) {
                $day_from = $setting->day_from;
                $day_to = $setting->day_to;
                continue;
            }

            if ($setting->day_from < $day_from)
                $day_from = $setting->day_from;

            if ($setting->day_to > $day_to)
                $day_to = $setting->day_to;
        }

        /** Заявки с приближающимся днём оплаты */
        $overdue_contracts = $this->soap->getOverdueContracts($day_from, $day_to);
        if (empty($overdue_contracts['response']))
            return [];
        $overdue_contracts = $overdue_contracts['response'];

        /** Список номеров контрактов с приближающимся днём оплаты */
        $contract_numbers = [];
        /** Соотношение между номером контракта (Ключ) и днями до оплаты (Значение) */
        $contract_delays = [];
        foreach ($overdue_contracts as $contract) {
            $contract_numbers[] = $contract['LoanNumber'];
            $contract_delays[$contract['LoanNumber']] = $contract['DayOfDelay'];
        }

        /** Информация о заявках клиентов, может содержать дубликаты */
        $all_rows = [];
        // Вытягиваем инфу о клиентах по номеру контракта, 1000 строк за раз
        $contract_numbers_chunks = array_chunk($contract_numbers, 1000);
        foreach ($contract_numbers_chunks as $chunk) {
            $this->db->query(self::GET_USERS_QUERY, $chunk);
            $rows = $this->db->results();
            if (!empty($rows))
                $all_rows = array_merge($all_rows, $rows);
        }

        if (empty($all_rows))
            return [];

        /** Отфильтрованные дубликаты, только уникальные клиенты */
        $unique_rows = [];
        foreach ($all_rows as $row) {
            if (empty($unique_rows[$row->user_id])) {
                // Клиент ещё не отфильтрован
                $unique_rows[$row->user_id] = $row;
            }
            else {
                // Клиент уже отфильтрован, заменяем старую заявку если эта новее
                if ($unique_rows[$row->user_id]->id < $row->id) {
                    $unique_rows[$row->user_id] = $row;
                }
            }
        }

        /** id всех клиентов которым подписаны на бота */
        $vk_users = $this->vk_message_settings->getVkUsers();
        /** Клиенты которым можно сделать рассылку */
        $subscribed_users = [];
        foreach ($vk_users as $user_id) {
            if (empty($unique_rows[$user_id]))
                continue; // Клиент недоступен для рассылки

            $row = $unique_rows[$user_id];
            $row->timezone = Helpers::getRegionTimezone($row);
            $row->delay = $contract_delays[$row->number];
            $subscribed_users[] = $row;
        }

        if (empty($subscribed_users))
            return [];

        /** Финальный массив на рассылку. Ключ - Id настройки, значения - данные о клиентах */
        $result = [];
        /** Текущий час по МСК */
        $current_hour = date("G");
        foreach ($subscribed_users as $user) {
            /** Текущий час по локальному времени клиента */
            $user_current_hour = $current_hour + $user->timezone;
            if ($user_current_hour > 23)
                $user_current_hour -= 24;
            elseif ($user_current_hour < 0)
                $user_current_hour += 24;

            /** Подходящая настройка */
            $final_setting = null;
            /** Количество точных совпадений критериев по настройке */
            $final_setting_ball = 0;

            // Поиск самой подходящей настройки
            foreach ($active_settings as $setting) {
                if ($setting->send_hour != $user_current_hour)
                    continue;

                if ($setting->day_from > $user->delay || $setting->day_to < $user->delay)
                    continue;

                if ($setting->age_from > $user->age || $setting->age_to < $user->age)
                    continue;

                if ($setting->scorista_ball_from > $user->scorista_ball || $setting->scorista_ball_to < $user->scorista_ball)
                    continue;

                $ball = 0;
                if ($setting->gender != 'any') {
                    if ($setting->gender != $user->gender)
                        continue;
                    $ball += 1;
                }

                if ($setting->scorista_decision != 'any') {
                    if ($setting->scorista_decision == 'approve' && $user->success == 0)
                        continue;
                    elseif ($setting->scorista_decision == 'decline' && $user->success == 1)
                        continue;
                    $ball += 1;
                }

                if (!in_array($setting->utm_source, ['', ' ', '*'])) {
                    if ($setting->utm_source != $user->utm_source)
                        continue;
                    $ball += 1;
                }

                if (!in_array($setting->organization_id, ['', ' ', '*'])) {
                    if ($setting->organization_id != $user->organization_id)
                        continue;
                    $ball += 1;
                }

                if (empty($final_setting) || $ball > $final_setting_ball) {
                    $final_setting = $setting;
                    $final_setting_ball = $ball;
                }
            }

            if (!empty($final_setting))
                $result[$final_setting->id][] = $user;
        }

        return $result;
    }

    /**
     * Формирование персонализированных сообщений в необходимом для отправки в бота формате
     * @param array $settings
     * @return array
     */
    private function getMessages($settings)
    {
        /** Список всех включенных настроек */
        $active_settings = $this->vk_message_settings->getWhere([
            'enabled'   => 1
        ]);
        // Преобразуем в массив, где ключом будет id настройки, а значением - сама настройка
        $active_settings = array_map(fn($item) => (array)$item, $active_settings);
        $active_settings = array_column($active_settings, null, 'id');

        $result = [];
        foreach ($settings as $setting_id => $users) {
            $setting = $active_settings[$setting_id];
            foreach ($users as $user) {
                $link = $this->getPayLink($user->user_id, $user->number, $user->phone_mobile);

                $message = $setting['message'];
                $message = str_replace([
                    '$n', // Имя
                    '$p', // Отчество
                    '$l', // Ссылка
                ], [
                    $user->firstname,   // $n
                    $user->patronymic,  // $p
                    $link,              // $l
                ], $message);

                $result[] = [
                    'user_id' => $user->user_id,
                    'text' => $message,
                    'setting_id' => $setting_id
                ];
            }
        }

        return $result;
    }

    /**
     * Генерация ссылки на моментальный вход в ЛК
     * @param $userId
     * @param $contractNumber
     * @param $phone
     * @return string
     */
    private function getPayLink($userId, $contractNumber, $phone)
    {
        $code = $this->orders->getShortLink($userId, $contractNumber);

        if (empty($code)) {
            $code = Helpers::generateLink();
            $count = $this->orders->getLinkExists($code);
            while ($count > 0) {
                $code = Helpers::generateLink();
                $count = $this->orders->getLinkExists($code);
            }

            $this->orders->add_short_link([
                'link' => $code,
                'user_id' => $userId,
                'phone' => $phone,
                'zaim_number' => $contractNumber,
                'active' => true
            ]);
        }

        return $this->config->front_url . '/pay/' . $code . '?utm_source=vk_bot_pay';
    }
}

$cron = new VkMessageBotCron();
$cron->run();