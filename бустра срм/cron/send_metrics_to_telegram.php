<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
ini_set('mysql.connect_timeout', 600);
ini_set('default_socket_timeout', 600);
ini_set('memory_limit', -1);

date_default_timezone_set('Europe/Moscow');


require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';

// Бот рассылает метрики в обозначенные телеграм каналы

class SendMetricsToTelegram extends Simpla
{
    use api\traits\FunnelLoansReportTrait;

    // Токен для бота BoostraRuBot
    const TelegramToken = '6749541984:AAErSsfK8oc_HQ_vtN-zeutmFgFr-JCj9FA';

    // Массив Chat IDs , узнать свой chat id можно написал боту https://t.me/ShowJsonBot любое сообщение
    const ChatIDs = [
        '511338841',
        '644407955',
    ];

    public const DEFAULT_ARRAY_KEYS = [
        'visits' => 'Переход на сайт',
        'contact_step_1' => 'Вход на 1 шаг анкеты (ФИО)',
        'contact_step_2' => 'Прошёл ФИО (Поставил Согласие + Клик Далее)',
        'account_created_data' => 'Подтвердил телефон (Успешный ввод смс + клик Далее)',
        'personal_data' => 'Ввод паспортных данных (Корректно ввёл + клик Далее)',
        'address_data' => 'Ввод адреса (Корректно + клик Получить решение)',
        'accept_data' => 'Прошёл страницу с предварительным решением (клик Получить деньги)',
        'credit_card_data' => 'Успешно привязал карту',
        'photo_data' => 'Успешно прикрепил фото (2 фото + клик Далее)',
        'work_data' => 'Успешно ввёл данные о работе',
        'orders_all' => 'Заявки',
        'orders_approved' => 'Одобрено',
        'orders_issued'  => 'Выдано'
    ];

    /**
     * Сообщение для отправки
     * @var string
     */
    private string $msg = "";

    /**
     * Дата для фильтра
     * @var string
     */
    private string $filter_date = "13.07.2023";
    private int $current_time;

    /**
     * @var resource $curl
     */
    private $curl = null;


    private $yandex_metric;
    public function __construct()
    {
        $this->current_time = strtotime('-5 minutes');
        $this->filter_date = date("Y-m-d", $this->current_time);
        $this->yandex_metric = new api\YaMetric\YaMetric();
        if ($this->msg = $this->getData()) $this->send();
        echo "OK";
    }

    public function send()
    {
        foreach (self::ChatIDs as $chatID) {
            $this->sendTelegram($chatID);
        }

    }


    private function getCurl()
    {
        if (empty($this->curl)) {
            $this->curl = curl_init();
        }
    }


    /**
     * Получение данных для отправки
     *
     * @return string
     */
    private function getData(): string
    {
        $message = "";
        $filter_data['filter_date_start'] = $this->filter_date;
        $filter_data['filter_date_end'] = $this->filter_date;
        $filter_data['filter_group_by'] = 'day';
        $filter_data['filter_client_type'] =  $this->orders::ORDER_BY_NEW_CLIENT;
        $filter_data['filter_no_validate_postback'] = $this->orders::UTM_SOURCES_NO_VALIDATE_POSTBACK;
        $filter_data['filter_webmaster_id'] = [];
        $filter_data['isNewUser'] = true;

        $getYaStatistic = function ($filter_data) {
            $result = [];
            $filter_data['filter_date_start'] = new \DateTime($filter_data['filter_date_start']);
            $filter_data['filter_date_end'] = new \DateTime($filter_data['filter_date_end']);
            $filter_level = [];

            $metric_response =  $this->yandex_metric->getStatistic(
                [
                    'view' => 'ym:s:{{type_visit}}',
                    'contact_step_1' => 258855672,
                    'contact_step_2' => 225208454,
                ], $filter_data);

            // переберем сначала метрику и приведем к виду Б.Д.
            foreach ($metric_response['data'] as $metric_data) {
                $date = str_replace('-', '.', $metric_data['dimensions'][0]['name']);
                $result[$date] = [
                    'visits' => (int)$metric_data['metrics'][0],
                    'contact_step_1' => (int)$metric_data['metrics'][1],
                    'contact_step_2' => (int)$metric_data['metrics'][2],
                ];
            }

            return $result;
        };

        $metric_data = $getYaStatistic($filter_data);
        $funnel_statistics = $this->users->getFunnelStatistic($filter_data);
        $date_dot = str_replace('-','.',$this->filter_date);
        $totals = array_merge($metric_data[$date_dot] ?? [], $funnel_statistics[$date_dot] ?? []);
        $divider = 0;
        foreach (self::DEFAULT_ARRAY_KEYS as $key => $name) {
            if (isset($totals[$key])) {
                $message .=   $name . ': ' . $totals[$key] ;
                $message .=  ($divider) ? ' ( ' .sprintf("%.2f",$totals[$key]/$divider) . ' ) ' : '';
                $message .= PHP_EOL;
                $divider = $totals[$key];
            }

        }
        if ($message) {
            $date = "Дата: ". date("d.m.Y",strtotime($this->filter_date));
            $date .= ($this->filter_date != date("Y-m-d")) ? ' (полный день)' : date(" H:i",strtotime("-1 minute"));
            $message =  $date  . PHP_EOL . PHP_EOL . $message;
        }

        return $message;

    }


    /**
     * Send To Telegram
     *
     * @param $msg
     * @param $chatID
     * @return void
     */
    private function sendTelegram($chatID)
    {
        $data = array(
            'chat_id' => $chatID,
            'text' => $this->msg,
            'parse_mode' => 'html',
        );

        //$this->getCurl();
        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1, // тут нужен тебе return ? (нет)
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://api.telegram.org/bot' . self::TelegramToken . '/sendMessage',
            CURLOPT_POSTFIELDS => $data
        ));
        curl_exec($this->curl);
        curl_close($this->curl);
    }


}

new SendMetricsToTelegram();
