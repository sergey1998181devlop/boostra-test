<?php

use boostra\services\RegionService;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

chdir('..');

/**
 * Класс отвечает за расчет ПДН для заявок
 *
 * Если не удается получить ПДН по заявке по API сервиса расчета ПДН, то пытаемся актуализировать SSP_NBKI и NBKI отчеты акси.
 * Если не получается, то пытаемся рассчитать ПДН на основе других заявок клиента.
 */
class PdnCalculation extends Simpla
{
    private static ?Client $httpClient = null;

    private const REGIONS = [
        "Адыгея Республика" => 1,
        "Алтай Республика" => 2, // не опускать ниже Алтайский, т.к. является его подстрокой
        "Алтайский край" => 3,
        "Амурская область" => 4,
        "Архангельская область" => 5,
        "Астраханская область" => 6,
        "Башкортостан Республика" => 7,
        "Белгородская область" => 8,
        "Брянская область" => 9,
        "Бурятия Республика" => 10,
        "Байконур" => 43,// считаем как Московская область
        "Владимирская область" => 11,
        "Волгоградская область" => 12,
        "Вологодская область" => 13,
        "Воронежская область" => 14,
        "Дагестан Республика" => 15,
        "Еврейская авт.область" => 16,
        "Забайкальский край" => 17,
        "Ивановская область" => 18,
        "Ингушетия Республика" => 19,
        "Иркутская область" => 20,
        "Кабардино-Балкарская Республика" => 21,
        "Калининградская область" => 22,
        "Калмыкия Республика" => 23,
        "Калужская область" => 24,
        "Камчатский край" => 25,
        "Карачаево-Черкесская Республика" => 26,
        "Карелия Республика" => 27,
        "Кемеровская область" => 28,
        "Кировская область" => 29,
        "Коми Республика" => 30,
        "Омская область" => 49, // не опускать ниже Костромская и Томская, т.к. является их подстрокой
        "Костромская область" => 31,
        "Томская область" => 71,
        "Краснодарский край" => 32,
        "Красноярский край" => 33,
        "Крым Республика" => 34,
        "Курганская область" => 35,
        "Курская область" => 36,
        "Ленинградская область" => 37,
        "Липецкая область" => 38,
        "Магаданская область" => 39,
        "Марий Эл Республика" => 40,
        "Мордовия Республика" => 41,
        "Москва г." => 42,
        "Московская область" => 43,
        "Мурманская область" => 44,
        "Ненецкий авт.округ" => 45, // не опускать ниже Ямало-Ненецкий, т.к. является ее подстрокой
        "Ямало-Ненецкий авт.округ" => 84,
        "Нижегородская область" => 46,
        "Новгородская область" => 47,
        "Новосибирская область" => 48,
        "Оренбургская область" => 50,
        "Орловская область" => 51,
        "Пензенская область" => 52,
        "Пермский край" => 53,
        "Приморский край" => 54,
        "Псковская область" => 55,
        "Ростовская область" => 56,
        "Рязанская область" => 57,
        "Самарская область" => 58,
        "Санкт-Петербург г." => 59,
        "Саратовская область" => 60,
        "Саха (Якутия)" => 61, // не опускать ниже Сахалинская, т.к. является ее подстрокой
        "Сахалинская область" => 62,
        "Свердловская область" => 63,
        "Севастополь г." => 64,
        "Северная Осетия - Алания Республика" => 65,
        "Смоленская область" => 66,
        "Ставропольский край" => 67,
        "Тамбовская область" => 68,
        "Татарстан Республика" => 69,
        "Тверская область" => 70,
        "Тульская область" => 72,
        "Тыва Республика" => 73,
        "Тюменская область без авт.округов" => 74,
        "Удмуртская Республика" => 75,
        "Ульяновская область" => 76,
        "Хабаровский край" => 77,
        "Хакасия Республика" => 78,
        "Ханты-Мансийский автономный округ - Югра" => 79,
        "Челябинская область" => 80,
        "Чеченская Республика" => 81,
        "Чувашская Республика" => 82,
        "Чукотский авт.округ" => 83,
        "Ярославская область" => 85,
        "Донецкая народная республика" => 86,
        "Луганская народная республика" => 87,
        "Херсонская область" => 88,
        "Запорожская область" => 89
    ];

    private const REGION_CODE_MAP = [
        1  => '01', // Адыгея
        2  => '04', // Алтай Республика
        3  => '22', // Алтайский край
        4  => '28', // Амурская область
        5  => '29', // Архангельская область
        6  => '30', // Астраханская область
        7  => '02', // Башкортостан
        8  => '31', // Белгородская область
        9  => '32', // Брянская область
        10 => '03', // Бурятия
        11 => '33', // Владимирская область
        12 => '34', // Волгоградская область
        13 => '35', // Вологодская область
        14 => '36', // Воронежская область
        15 => '05', // Дагестан
        16 => '79', // Еврейская АО
        17 => '75', // Забайкальский край
        18 => '37', // Ивановская область
        19 => '06', // Ингушетия
        20 => '38', // Иркутская область
        21 => '07', // Кабардино-Балкария
        22 => '39', // Калининградская область
        23 => '08', // Калмыкия
        24 => '40', // Калужская область
        25 => '41', // Камчатский край
        26 => '09', // Карачаево-Черкесия
        27 => '10', // Карелия
        28 => '42', // Кемеровская область
        29 => '43', // Кировская область
        30 => '11', // Коми
        31 => '44', // Костромская область
        32 => '23', // Краснодарский край
        33 => '24', // Красноярский край
        34 => '82', // Крым
        35 => '45', // Курганская область
        36 => '46', // Курская область
        37 => '47', // Ленинградская область
        38 => '48', // Липецкая область
        39 => '49', // Магаданская область
        40 => '12', // Марий Эл
        41 => '13', // Мордовия
        42 => '77', // Москва
        43 => '50', // Московская область
        44 => '51', // Мурманская область
        45 => '83', // Ненецкий АО
        46 => '52', // Нижегородская область
        47 => '53', // Новгородская область
        48 => '54', // Новосибирская область
        49 => '55', // Омская область
        50 => '56', // Оренбургская область
        51 => '57', // Орловская область
        52 => '58', // Пензенская область
        53 => '59', // Пермский край
        54 => '25', // Приморский край
        55 => '60', // Псковская область
        56 => '61', // Ростовская область
        57 => '62', // Рязанская область
        58 => '63', // Самарская область
        59 => '78', // Санкт-Петербург
        60 => '64', // Саратовская область
        61 => '14', // Саха (Якутия)
        62 => '65', // Сахалинская область
        63 => '66', // Свердловская область
        64 => '92', // Севастополь
        65 => '15', // Северная Осетия
        66 => '67', // Смоленская область
        67 => '26', // Ставропольский край
        68 => '68', // Тамбовская область
        69 => '16', // Татарстан
        70 => '69', // Тверская область
        71 => '70', // Томская область
        72 => '71', // Тульская область
        73 => '17', // Тыва
        74 => '72', // Тюменская область
        75 => '18', // Удмуртия
        76 => '73', // Ульяновская область
        77 => '27', // Хабаровский край
        78 => '19', // Хакасия
        79 => '86', // ХМАО
        80 => '74', // Челябинская область
        81 => '95', // Чечня
        82 => '21', // Чувашия
        83 => '87', // Чукотский АО
        84 => '89', // ЯНАО
        85 => '76', // Ярославская область
        86 => '80', // ДНР
        87 => '81', // ЛНР
        88 => '84', // Херсонская область
        89 => '85', // Запорожская область
    ];

    /** @var int[] Регионы без необходимости проверки на кол-во совпадений по подстроке при поиске региона */
    private const REGIONS_WITHOUT_CHECK = [
        "Алтай Республика" => 2,
        "Омская область" => 49,
        "Ненецкий авт.округ" => 45,
        "Саха (Якутия)" => 61,
    ];

    /** @var int[] Вариации написания регионов у старых клиентов */
    private const REGIONS_VARIATIONS = [
        "Карачаево-Черкесия" => 26,
        "Краснодарски Край" => 32,
        "Саха /Якутия/" => 61,
        "Саха Якутия" => 61,
        "Ханты-Мансийский  - Югра" => 79,
        "Кемеровская область - Кузбасс" => 28,
        "Респ Татарстан" => 69,
        "Республика Северная" => 65,
        "АО Ханты-Манскийский автономный" => 79,
        "Респ Башкортостан" => 7,
        "Респ Северная Осетия - Алания" => 65,
        "Республика Северная осетия" => 65,
        'Республика Чувашия' => 82,
        "Республика Удмуртия" => 75,
        "ДНР" => 86,
        "Донецкая республика" => 86,
        "ЛНР" => 87,
        "Луганская республика" => 87,
        "Запорожье" => 89
    ];

    /** @var int[] Регионы, по которым не нужно рассчитывать пдн */
    private const REGIONS_WITH_DISABLED_PDN_CALCULATION = [];

    private const ORGANIZATIONS_UUIDS = [
        0 => '1675d3bb-e83a-4a72-b2b8-0b1adb6a6376',  // тест
        6 => '4a12e48f-a923-4afc-81ea-f1107ad0b06c',  // ООО МКК «Аквариус»
        11 => 'c54c33ab-847c-453b-8278-9f22081a0641', // ООО МКК «Финлаб»
        13 => '8080c40c-cc31-4cfa-87aa-ff25693df218', // ООО МКК "РУСЗАЙМСЕРВИС"
        15 => '87858615-5eac-4254-adc7-4794d1e7477c', // ООО МКК «Лорд»
        17 => 'f7592ec0-ded9-439a-84a7-26f5a6a79e63', // ООО МКК «МореДенег»
        20 => '9567e39b-95c0-4445-baf8-32126877636b', // ООО МКК «Фрида»
        14 => 'f643d057-b650-4fe2-a553-12705008b0de', // ООО МКК «Форинт»
        22 => '6d448b1c-f0f7-482c-8e2b-484ca6c0f9fa', // ООО МКК "Фаст Финанс"
        21 => '68cc5a84-e3fc-4c49-9d36-338ca3cc06e5', // ООО МКК "Рубль.Ру"
    ];

    private const CALCULATE_ORDER_PDN_URL = '/api/v2/dbi/';

    private const CALCULATE_RCL_PDN = '/api/v4/dbi/resumable-credit-line/';

    private const CALCULATE_ORDER_PDN_FOR_MD_URL = '/api/v2/dbi/amp-1-758p-simplified-credit-report-filtering/';

    private const CALCULATE_ORDER_PDN_V4_URL = '/api/v4/dbi/';

    private const CALCULATE_ORDER_PDN_FOR_RUBLE_URL = '/api/v2/dbi/amp-1-758p-simplified-credit-report/';

    private const CALCULATE_ORDER_PDN_HEADERS = [
        'accept' => 'application/json',
        'X-CSRFTOKEN' => 'aYsXyzlq3ToHTE0c69GantUjZjHP4XDdAdvWLocXSA1JHjxRVqvPHjfOwzGhl045'
    ];

    private const PDN_CALCULATION_TYPE_FOR_NEW_REGIONS = 5;

    private const LOG_FILE = 'pdn_calculation.txt';

    /** @var int Максимальная продолжительность (в днях) актуальности результатов скоринга банкротства и ФССП */
    private const MAX_EFRSB_AND_FSSP_SCORING_AVAILABILITY_DAYS = 7;

    /** @var string Флаг отключения перезапроса ССП и КИ отчетов (при необходимости актуализации отчетов) */
    public const DISABLE_INQUIRING_NEW_REPORTS = 'disable_inquiring_new_reports';

    /** @var string Флаг отключения расчета ПДН для заявки (будут только отправляться данные для "Лист оценки платежеспособности" в 1С) */
    public const ONLY_DEBTS_DOCUMENT = 'only_debts_document';

    /** @var string Флаг расчета ПДН без обязательности КИ отчета и проверки актуальности ССП отчета на стороне сервиса ПДН */
    public const IS_FORCED_CALCULATION = 'is_forced_calculation';

    /** @var string Флаг расчета ПДН без проверки актуальности ССП и КИ отчетов */
    public const WITHOUT_CHECK_REPORTS_DATE = 'without_check_reports_date';

    /** @var string Флаг расчета ПДН без отправки КИ отчета (для работы также нужен флаг self::IS_FORCED_CALCULATION) */
    public const WITHOUT_CH_REPORT = 'without_ch_report';

    /** @var string Флаг расчета ПДН без отправки ССП и КИ отчетов */
    public const WITHOUT_REPORTS = 'without_reports';

    /** @var string Флаг только обновления значения ПДН (без расчета ПДН). Используется вместе с ключом pti_percent */
    public const ONLY_UPDATE_PDN = 'only_update_pdn';

    /** @var string Флаг только изменения кодировки локального файла КИ отчета (если есть) (без расчета ПДН) */
    public const ONLY_CONVERT_CREDIT_HISTORY_REPORT_ENCODING = 'only_convert_credit_history_report_encoding';

    /** @var string Флаг расчета ПДН до выдачи */
    public const CALCULATE_PDN_BEFORE_ISSUANCE = 'calculate_pdn_before_issuance';

    /** @var string Флаг используется, если для заявки нужно рассчитать ПДН, используя другую МКК */
    public const FORCED_ORGANIZATION_ID_FOR_PDN_CALCULATION = 'forced_organization_id_for_pdn_calculation';

    /** @var string Флаг финального расчета ПДН до выдачи (см. cron/check_pdn_before_issuance.php) */
    public const FINAL_PDN_BEFORE_ISSUANCE = 'final_pdn_before_issuance';

    public const IS_SELF_EMPLOYEE_ORDER = 'is_self_employee_order';

    public static array $pdnCalculationTypes = [
        'ПДН при доходе по Росстату' => 1,
        'ПДН при доходе по кредитному отчёту' => 2,
        'Анкетный доход при ПДН=49%' => 3,
        'Анкетный доход при ПДН=79%' => 4,
        'ПДН для новых территорий' => 5,
        'Анкетный доход при ПДН>79%' => 6,
    ];

    /**
     * Ориентировочная максимальная сумма допов, чтобы более точно считать ПДН до выдачи.
     * Максимальная сумма допов для PDL (Финансовый Доктор (ФД) - 6150 руб /fd_base_tariffs, Звездный Оракул (ОЗ) - 350 руб)
     */
    private const MAX_DOPS_AMOUNT = 6500;

    /**
     * Ориентировочная максимальная сумма допов для инстолментов, чтобы более точно считать ПДН до выдачи.
     * Максимальная сумма допов для инстолментов (Финансовый Доктор (ФД) - 11750 руб /fd_base_tariffs, Звездный Оракул (ОЗ) - 350 руб)
     */
    private const MAX_DOPS_AMOUNT_IL = 12100;

    public const IL_MAX_AMOUNT = 49900;

    /** @var string[] */
    private $organization_uuids = self::ORGANIZATIONS_UUIDS;

    private const DEFAULT_VALUE_FORM_EXPENSE = 0;

    public function __construct()
    {
        if (!empty($this->config->notificationCenter['telegram_pm.token'])) {
            $this->notificationCenter->register_channel(
                'telegram_pm', new Telegram(
                    $this->config->notificationCenter['telegram_pm.token'],
                    $this->config->notificationCenter['telegram_pm.chat_id'],
                    ['parse_mode' => 'HTML']
                )
            );
        }
    }

    /**
     * @param string $orderUid
     * @param array $flags
     * @return false|stdClass
     */
    public function run(string $orderUid, array $flags)
    {
        $order = $this->getOrderData([
            'order_uid' => $orderUid
        ], $flags);

        if ($order === null) {
            return false;
        }

        try {
            return $this->handleOrder($order, $flags);
        } catch (Throwable $e) {
            $autoRecalc = false;

            if ($e instanceof GuzzleException) {
                $code = $e->getCode();
                $mesError = $e->getMessage();

                if ($e instanceof BadResponseException) {
                    $code = $e->getResponse()->getStatusCode();
                    $mesError .= ' ' . $e->getResponse()->getBody()->getContents();
                }

                $autoRecalc = ($code >= 300 && $code < 400) || $code >= 500 || $code === 404;
                $errMessage = sprintf('HTTP %d %s', $code, $mesError);
            } elseif ($e instanceof Exception) {
                $errMessage = $e->getMessage();
            } else {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Код: ' . $e->getCode(),
                    'Подробности: ' . $e->getTraceAsString()
                ];

                $errMessage = json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            $this->saveErrorPdnCalculation($order, $errMessage, $autoRecalc);

            // не отправляем оповещение в телеграм, так как сообщение в первые 10 мин не является ошибкой
            if ($errMessage === 'Ожидание завершения скоринга акси.' && time() - strtotime($order->date) <= 600) {
                return false;
            }

            $this->notificationCenter->notifyTemplate(
                'telegram_pm',
                'calculation_error.tpl',
                [
                    'order_id' => $order->order_id,
                    'order_number' => $order->contract_number,
                    'error' => $errMessage,
                    'calculate_date' => date('d.m.Y H:i'),
                ]
            );

            return false;
        }
    }

    /**
     * Сохраняет в БД успешно рассчитанный ПДН
     *
     * @param stdClass $order
     * @param array $flags
     * @param string $pdnResult
     * @param int $regionCode
     * @return void
     */
    private function saveSuccessPdnCalculation(stdClass $order, array $flags, string $pdnResult, int $regionCode): void
    {
        $data = [
            'order_id' => $order->order_id,
            'order_uid' => $order->order_uid,
            'contract_number' => $order->contract_number ?? '',
            'date_create' => date('Y-m-d H:i:s'),
            'success' => 1,
            'result' => $pdnResult,
            'auto_recalc' => false,
        ];

        if (!empty($flags[self::FINAL_PDN_BEFORE_ISSUANCE])) {
            $data['final_pdn_before_issuance'] = 1;
        }

        $this->savePdn($data);

        $pdnResult = json_decode($pdnResult);

        if (empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            $data = [
                'order_id' => $order->order_id,
                'order_uid' => $order->order_uid,
                'contract_number' => $order->contract_number ?? '',
                'date_create' => date('Y-m-d'), // храним только дату, чтобы не было разночтений между временем расчета ПДН и временем выдачи займа
                'success' => 1,
                'smp' => $pdnResult->average_monthly_payment,
                'smp1' => $pdnResult->loan_monthly_payment,
                'smp2' => $pdnResult->accumulated_monthly_payment,
                'smd' => $pdnResult->average_monthly_income,
                'income_base' => max($order->income_base, $pdnResult->form_income_salary_rounded),
                'income_rosstat' => $pdnResult->average_per_capita_income,
                'pdn' => $pdnResult->pti_percent,
                'pdn_calculation_type' => $this->getPdnCalculationType($pdnResult),
                'amount' => $order->amount,
                'issuance_date' => $order->issuance_date,
                'fakt_address' => $this->getFaktAddressForPdn($order, $pdnResult, $regionCode),
                'amp_report_link' => $pdnResult->amp_report_storage_link ?? '',
                'credit_history_link' => $pdnResult->credit_report_storage_link ?? ''
            ];

            $this->savePdnData($data, ((int)$order->organization_id));
        }
    }

    private function getFaktAddressForPdn(stdClass $order, stdClass $pdnResult, int $regionCode): string
    {
        // Не меняем адрес проживания, если
        // 1. Тип расчета ПДН НЕ self::PDN_CALCULATION_TYPE_FOR_NEW_REGIONS ИЛИ
        // 2. В ответе из сервиса ПДН не получен ключ new_region_id ИЛИ
        // 3. В ответ из сервиса ПДН пришел id региона, совпадающий с исходным id региона клиента из анкеты
        if (
            empty($pdnResult->new_region_id) ||
            (int)$pdnResult->new_region_id === $regionCode
        ) {
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id], 'Код региона из сервиса ПДН совпадает с кодом региона из анкеты, поэтому не меняем регион', self::LOG_FILE);

            $crmOrder = $this->orders->get_order((int)$order->order_id);

            $userAddress = [
                'address_index' => $crmOrder->Faktindex,
                'region' => $crmOrder->Faktregion,
                'region_code' => $crmOrder->Faktregion_code,
                'district' => $crmOrder->Faktdistrict,
                'city' => $crmOrder->Faktcity,
                'locality' => $crmOrder->Faktlocality,
                'street' => $crmOrder->Faktstreet,
                'building' => $crmOrder->Faktbuilding,
                'housing' => $crmOrder->Fakthousing,
                'room' => $crmOrder->Faktroom,
                'region_shorttype' => $crmOrder->Faktregion_shorttype,
                'city_shorttype' => $crmOrder->Faktcity_shorttype,
                'street_shorttype' => $crmOrder->Faktstreet_shorttype
            ];

            return json_encode($userAddress, JSON_UNESCAPED_UNICODE);
        }

        // Если уже меняли ранее регион, то берем тот адрес, чтобы не генерировать новый
        $userAddress = $this->getUserPreviouslyChangedFaktAddress($order, $pdnResult);

        if ($userAddress === null) {
            $userAddress = $this->generateFaktAddress($order, $pdnResult);

            if ($userAddress === null) {
                return '';
            }
        }

        // Добавляем без квартиры на всякий случай
        $userAddress = [
            'address_index' => $userAddress->address_index,
            'region' => $userAddress->region,
            'region_code' => $userAddress->region_code,
            'district' => $userAddress->district,
            'city' => $userAddress->city,
            'locality' => $userAddress->locality,
            'street' => $userAddress->street,
            'building' => $userAddress->building,
            'housing' => $userAddress->housing,
            'region_shorttype' => $userAddress->region_shorttype,
            'city_shorttype' => $userAddress->city_shorttype,
            'street_shorttype' => $userAddress->street_shorttype
        ];

        return json_encode($userAddress, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить ранее сгенерированный фактический адрес
     *
     * @param stdClass $order
     * @param stdClass $pdnResult
     * @return stdClass|null
     */
    private function getUserPreviouslyChangedFaktAddress(stdClass $order, stdClass $pdnResult): ?stdClass
    {
        $lastAddressInRegion = $this->getLastValidAddressForUserInRegion($order->user_id, $order->order_id, $pdnResult->new_region_id);

        if (!empty($lastAddressInRegion->fakt_address)) {
            return json_decode($lastAddressInRegion->fakt_address);
        }

        return null;
    }

    /**
     * Сгенерировать фактический адрес
     *
     * @param stdClass $order
     * @param stdClass $pdnResult
     * @return stdClass|null
     */
    private function generateFaktAddress(stdClass $order, stdClass $pdnResult): ?stdClass
    {
        $newRegionCode = null;

        if (!empty($pdnResult->new_region_id) && isset(self::REGION_CODE_MAP[$pdnResult->new_region_id])) {
            $regionLocalServiceCode = self::REGION_CODE_MAP[$pdnResult->new_region_id];

            if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
                require_once __DIR__ . '/../lib/autoloader.php';
                $region = (new RegionService())->getRegionByCode($regionLocalServiceCode);
                $newRegionCode = $region->code;
            }
        }

        if (empty($newRegionCode)) {
            $this->logging(__METHOD__, '', 'Не удалось получить код региона', ['order_id' => $order->order_id, 'new_region_id' => $pdnResult->new_region_id], self::LOG_FILE);
            return null;
        }

        // Установить RAND() в @rand, т.к. если использовать RAND() в подзапросе, она работает некорректно
        // (вычисляется своя для каждой строки при поиске)
        // C RAND() внутри ORDER BY отрабатывало долго (больше 1 мин)
        $this->db->query("SET @rand := RAND();");

        // Получаем случайный адрес из users_addresses по нужным регионам и в адресе которых есть квартира
        // для страховки, чтобы был многоквартирный дом
        $this->db->query("
                SELECT *
                FROM users_addresses
                WHERE 
                    id >= (
                        SELECT FLOOR(@rand * (SELECT MAX(id) 
                        FROM users_addresses 
                        WHERE region_code = '$newRegionCode' AND room != ''))
                    )
                    AND region_code = '$newRegionCode'
                    AND room != ''
                LIMIT 1
            ");
        $userAddress = $this->db->result();

        if (empty($userAddress)) {
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id, 'new_region_code' => $newRegionCode], 'Не найден адрес региона для смены!', self::LOG_FILE);
            return null;
        }

        $this->logging(__METHOD__, '', 'Взят новый адрес для заявки', ['order_id' => $order->order_id, 'user_address' => $userAddress], self::LOG_FILE);

        return $userAddress;
    }

    private function getPdnCalculationType(stdClass $pdnResult): ?int
    {
        return self::$pdnCalculationTypes[$pdnResult->calculation_type] ?? null;
    }

    /**
     * Сохраняет в БД неуспешно рассчитанный ПДН
     *
     * @param stdClass $order
     * @param string $message
     * @param bool $autoRecalc
     * @return void
     */
    private function saveErrorPdnCalculation(stdClass $order, string $message, bool $autoRecalc = false): void
    {
        $this->savePdn([
            'order_id' => $order->order_id,
            'order_uid' => $order->order_uid,
            'contract_number' => $order->contract_number ?? '',
            'date_create' => date('Y-m-d H:i:s'),
            'success' => 0,
            'result' => $message,
            'auto_recalc' => $autoRecalc,
        ]);

        $this->logging(__METHOD__, '', 'Не удалось рассчитать ПДН', ['order_id' => $order->order_id, 'error' => $message], self::LOG_FILE);
    }

    /**
     * Сохраняет расчет ПДН в БД
     *
     * @param array $data
     * @return void
     */
    private function savePdn(array $data): void
    {
        $pdnRow = $this->getPdnRow((int)$data['order_id']);

        if (empty($pdnRow)) {
            $this->insertPdnRow($data);
        } else {
            $this->updatePdnRow((int)$data['order_id'], $data);
        }
    }

    public function savePdnData(array $data, int $organizationId = Organizations::RZS_ID): void
    {
        if ($this->organizations->isFinlab($organizationId)) {
            $tableName = 'pdn_calculation_finlab';
        } else {
            $tableName = 'pdn_calculation';
        }

        $query = $this->db->placehold(
            'SELECT * FROM ' . $tableName . ' 
            WHERE order_id = ?',
            (int)$data['order_id']
        );

        $this->db->query($query);
        $pdnRow = $this->db->result();

        if (empty($pdnRow)) {
            $query = $this->db->placehold("INSERT INTO " . $tableName . " SET ?%", $data);
        } else {
            $query = $this->db->placehold("UPDATE " . $tableName . " SET ?% WHERE order_id = ?", $data, (int)$data['order_id']);
        }

        $this->db->query($query);
    }

    /**
     * @param array $ordersId
     * @param int $organizationId
     * @return false|array|null
     */
    public function getPdnCalculationsByOrderId(array $ordersId, int $organizationId = Organizations::RZS_ID)
    {
        if ($this->organizations->isFinlab($organizationId)) {
            $tableName = 'pdn_calculation_finlab';
        } else {
            $tableName = 'pdn_calculation';
        }

        $query = $this->db->placehold(
            'SELECT * FROM ' . $tableName . ' 
            WHERE order_id IN (?@)',
            $ordersId
        );

        $this->db->query($query);
        return $this->db->results();
    }

    private function insertPdnRow(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __pdn_calculation SET ?%", $data);
        $this->db->query($query);
        $pdnRowId = $this->db->insert_id();

        $this->logging(__METHOD__, '', '', ['data' => $data, 'pdnRowId' => $pdnRowId, 'debug' => debug_backtrace(0)], 'pdn_calculation_debug.txt');
    }

    private function updatePdnRow(int $orderId, array $data)
    {
        $query = $this->db->placehold("UPDATE __pdn_calculation SET ?% WHERE order_id = ?", $data, $orderId);
        $this->db->query($query);
    }

    public function getPdnRow(int $orderId, bool $finalPdnBeforeIssuance = false)
    {
        $conditions[] = $this->db->placehold("`order_id` = ?", $orderId);

        if ($finalPdnBeforeIssuance) {
            $conditions[] = $this->db->placehold("`final_pdn_before_issuance` = 1");
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __pdn_calculation WHERE $conditions");

        return $this->db->result();
    }

    /**
     * Получить запись ПДН по ID
     * @param int|null $id
     * @return object|null
     */
    public function getPdnRowById(?int $id)
    {
        if (!$id) {
            return null;
        }

        $query = $this->db->placehold(
            'SELECT * FROM __pdn_calculation WHERE id = ?',
            $id
        );

        $this->db->query($query);
        return $this->db->result();
    }

    private function getOrderData($filter, ?array $flags = null): ?stdClass
    {
        $orderUid_filter = '';
        if (!empty($filter['order_uid'])) {
            $orderUid_filter = $this->db->placehold("AND o.order_uid = ? ", (string)$filter['order_uid']);
        }

        if (empty($orderUid_filter)) {
            return null;
        }

        // o.confirm_date - дата подписания договора, c.issuance_date - дата выдачи
        // В промежутке между o.confirm_date и c.issuance_date отправляется запрос в b2p
        // Разница между датами может составлять от несколько секунд до нескольких дней
        // o.amount - без учета КД, c.amount - с учетом КД, но иногда отличается от суммы займа в 1С. Можно сравнивать с c.loan_body_summ
        $query = $this->db->placehold(
            "SELECT o.id AS order_id, o.user_id, o.date, o.contract_id, o.confirm_date, o.period, o.percent, o.order_uid, c.issuance_date,
                o.organization_id, o.1c_id AS id_1c, o.utm_content, CONCAT_WS(' ', u.lastname, u.firstname, u.patronymic) AS fio, u.phone_mobile, u.lastname, u.firstname, u.patronymic, o.loan_type, u.Faktregion, u.Faktregion_shorttype, u.income_base, u.education, u.birth, ua.region AS user_address_faktregion, o.utm_source, ua.region_shorttype AS user_address_faktregion_shorttype, t.name_zone, o.amount as order_amount, c.amount as contract_amount, IFNULL(c.amount, o.amount) AS amount, c.number AS contract_number, u.profession, u.income_base, m.name AS manager
             FROM s_orders o
                            INNER JOIN s_users u on o.user_id = u.id
                            LEFT JOIN s_time_zones t on u.timezone_id = t.time_zone_id
                            LEFT JOIN users_addresses ua on u.factual_address_id = ua.id
                            LEFT JOIN s_contracts c on c.order_id = o.id
                            LEFT JOIN s_managers m on o.manager_id = m.id
             
                            WHERE 1
                            $orderUid_filter
                            ");
        $this->db->query($query);
        $order = $this->db->result();

        if (empty($order)) {
            $message = 'Заявка не найдена';
            $this->savePdn([
                'order_id' => $filter['order_id'],
                'order_uid' => $filter['order_uid'],
                'date_create' => date('Y-m-d H:i:s'),
                'success' => 0,
                'result' => $message,
                'auto_recalc' => false,
            ]);
            $this->logging(__METHOD__, '', $message, ['order' => $order], self::LOG_FILE);

            return null;
        }

        if (!empty($flags[self::FORCED_ORGANIZATION_ID_FOR_PDN_CALCULATION])) {
            $order->organization_id = $flags[self::FORCED_ORGANIZATION_ID_FOR_PDN_CALCULATION];
        }

        // При расчете ПДН до выдачи нужно брать максимальную сумму займа (запрашиваемая клиентом или рекомендуемая акси) + сумма допов
        if (!empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            sleep(1);
            $approveAmountScoring = $this->scorings->getApproveAmountScoring($order->user_id, $order->order_id);

            $this->logging(__METHOD__, '', 'Сумма заявки для расчета ПДН до выдачи', [
                'order_id' => $order->order_id,
                'order->loan_type' => $order->loan_type,
                'order_amount' => $order->order_amount,
                'contract_amount' => $order->contract_amount,
                'approve_amount_scoring' => $approveAmountScoring,
                'flags' => $flags
            ], self::LOG_FILE);

            if ($order->loan_type === $this->orders::LOAN_TYPE_IL) {

                // Если взят s_contracts.amount, то не добавляем допы, так как они уже добавлены в s_contracts.amount
                $order->amount = max($order->order_amount + self::MAX_DOPS_AMOUNT_IL, $order->contract_amount, $approveAmountScoring + self::MAX_DOPS_AMOUNT_IL);

                if ($order->amount > self::IL_MAX_AMOUNT) {
                    $order->amount = self::IL_MAX_AMOUNT;
                }
            } else {

                // Если взят s_contracts.amount, то не добавляем допы, так как они уже добавлены в s_contracts.amount
                $order->amount = max($order->order_amount + self::MAX_DOPS_AMOUNT, $order->contract_amount, $approveAmountScoring + self::MAX_DOPS_AMOUNT);

                if ($order->amount > $this->orders::PDL_MAX_AMOUNT) {
                    $order->amount = $this->orders::PDL_MAX_AMOUNT;
                }
            }
        }

        $this->logging(__METHOD__, '', 'Взята заявка для расчета ПДН', ['order' => $order, 'flags' => $flags], self::LOG_FILE);

        return $order;
    }

    /**
     * Обработка заявок для получения ПДН
     *
     * @param stdClass $order
     * @param array $flags
     * @return stdClass
     * @throws GuzzleException
     */
    private function handleOrder(stdClass $order, array $flags): stdClass
    {
        $this->setAdditionalFlagsForCalculate($order, $flags);

        $regionCode = $this->getRegionCode($order);

        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);

        if (!empty($isRcl)) {
            throw new RuntimeException('Заявка - ВКЛ, поэтому не рассчитываем ПДН, order_id: ' . $order->order_id);
        }

        $jsonResponse = $this->getPdnCalculation($order, $flags, $regionCode);

        $response = json_decode($jsonResponse);

        if (!isset($response->pti_percent)) {
            throw new RuntimeException('Получили не корректный ответ от сервиса расчета ПДН - '. $jsonResponse);
        }

        $this->saveSuccessPdnCalculation($order, $flags, $jsonResponse, $regionCode);

        if (empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            $this->sendPdnResultTo1C($order, $response);

            $this->orders->update_order($order->order_id, [
                'pdn_nkbi_loan' => $response->pti_percent ?? 0,
                'salary_for_pti_3' => $response->form_income_salary_rounded ?? 0
            ]);

            $this->order_data->set($order->order_id, $this->order_data::PDN_CALCULATION_DATE, date('Y-m-d'));
        }

        return $response;
    }

    private function setAdditionalFlagsForCalculate(stdClass $order, array &$flags)
    {
        $orderData = $this->order_data->readAll($order->order_id);

        if (!empty($orderData[$this->order_data::AXI_WITHOUT_CREDIT_REPORTS])) {
            $flags[self::WITHOUT_REPORTS] = 1;
            $flags[self::WITHOUT_CHECK_REPORTS_DATE] = 1;
        }

        if (!empty($orderData[$this->order_data::SELF_EMPLOYEE_ORDER])) {
            $flags[self::IS_SELF_EMPLOYEE_ORDER] = 1;
        }
    }

    /**
     * @param stdClass $order
     * @param array $flags
     * @param int $regionCode
     * @return string
     * @throws GuzzleException
     */
    private function getPdnCalculation(stdClass $order, array $flags, int $regionCode): string
    {
        if (empty($flags[self::WITHOUT_CHECK_REPORTS_DATE])) {
            $checkReportsDateResult = $this->checkReportsDate($order, $flags);

            if (empty($checkReportsDateResult['success'])) {
                throw new RuntimeException($checkReportsDateResult['message'] ?: 'Не удалось проверить актуальность отчетов');
            }
        }

        $reportsUrl = [
            $this->axi::SSP_REPORT => $this->getReportUrl($order, $this->axi::SSP_REPORT),
            $this->axi::CH_REPORT => $this->getReportUrl($order, $this->axi::CH_REPORT)
        ];

        $pdnData = $this->getPdnData($order, $regionCode, $reportsUrl, $flags);

        $this->savePdn([
            'order_id' => $order->order_id,
            'order_uid' => $order->order_uid,
            'contract_number' => $order->contract_number ?? '',
            'date_create' => date('Y-m-d H:i:s'),
            'request' => json_encode($pdnData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'result' => 'Ожидание ответа из сервиса расчета ПДН',
            'auto_recalc' => false,
        ]);

        $this->logging(__METHOD__, '', 'Данные для расчета ПДН', ['order_id' => $order->order_id, 'flags' => $flags, 'pdn_data' => $pdnData], self::LOG_FILE);

        return $this->sendRequest($pdnData, self::CALCULATE_ORDER_PDN_HEADERS, $pdnData['url']);
    }

    private function checkReportsDate(stdClass $order, array $flags): array
    {
        if (!empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            $orderIssuanceDate = new DateTimeImmutable();
        } else {
            if (empty($order->issuance_date)) {
                throw new RuntimeException('Не указана дата выдачи у заявки');
            }

            try {
                $orderIssuanceDate = new DateTimeImmutable($order->issuance_date);
            } catch (Throwable $e) {
                throw new RuntimeException('Некорректная дата выдачи у заявки');
            }
        }

        $crmOrder = $this->orders->get_order((int)$order->order_id);

        if (empty($crmOrder)) {
            throw new RuntimeException('Не удалось получить заявку в crm');
        }

        return $this->report->checkReportsDate($crmOrder, $orderIssuanceDate, $flags[self::DISABLE_INQUIRING_NEW_REPORTS] ?? false);
    }

    /**
     * @param stdClass $order
     * @param string $reportType
     * @return string
     */
    private function getReportUrl(stdClass $order, string $reportType): string
    {
        $fileRow = $this->credit_history->getRow([
            'order_id' => $order->order_id,
            'type' => $reportType,
        ]);

        if (!empty($fileRow)) {
            return $this->s3_api_client->buildUrl($fileRow->s3_name);
        }

        // TODO для старых заявок, удалить после переноса всех заявок в s3
        $filePath = $this->report->getReportFilePath($order, $reportType);
        return str_replace(ROOT, $this->config->back_url, $filePath);
    }

    /**
     * Формирование и получение данных для запроса в сервис расчета ПДН
     *
     * @param stdClass $order
     * @param int $regionCode
     * @param array $reportsUrl
     * @param array $flags
     * @return array
     */
    private function getPdnData(stdClass $order, int $regionCode, array $reportsUrl, array $flags): array
    {
        $ampRequestURL = $this->ssp_nbki_request_log->getLog([
            'order_id' => $order->order_id,
            'request_type' => $this->axi::SSP_REPORT,
        ]);

        $ampRequestS3 = '';

        if (!empty($ampRequestURL->s3_name)) {
            $ampRequestS3 = $this->s3_api_client->buildUrl($ampRequestURL->s3_name);
        }

        $percent = (float)$order->percent;

        // Если процент 0.0, то заменяем на 0.8
        if (empty($percent)) {
            $percent = 0.8;
        }

        $education = $this->users->getEducationCode($order->education);
        if (is_null($education)) {
            $education = 5; // Другое
        }

        if (!empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            $issuanceDate = date('Y-m-d');
        } else {
            $issuanceDate = date('Y-m-d', strtotime($order->issuance_date)); // дата выдачи
        }

        $effectiveInterestRatePercent = round($percent * $this->getNumbersDaysInYear(), 6);
        $formIncome = !empty($order->income_base) ? $order->income_base : 1;

        $data = [
            'effective_interest_rate_percent' => $effectiveInterestRatePercent,
            'loan_close_date' => date('Y-m-d', strtotime($order->issuance_date . ' +' . $order->period . ' days')), // дата планируемого закрытия займа
            'loan_amount' => $order->amount, // сумма займа
            'loan_request_date' => $issuanceDate,
            'client_uuid' => $this->getOrganizationUuid($order->organization_id),
            'region_number' => $regionCode,
            'order_number' => $order->order_uid,
            'chb' => 1,
            'age' => $this->getUserAge($order->birth, $issuanceDate),
            'form_income' => $formIncome,
            'borrower_id' => $order->user_id,
            'education' => $education,
            'url' => $this->getURL($order->organization_id),
            'form_expense' => self::DEFAULT_VALUE_FORM_EXPENSE,
            'is_self_employed' => !empty($flags[self::IS_SELF_EMPLOYEE_ORDER]),
        ];

        if (empty($flags[self::WITHOUT_REPORTS])) {
            $data['amp_report_url'] = $reportsUrl[$this->axi::SSP_REPORT] ?: '';
            $data['amp_request_url'] = $ampRequestS3;
        } else {
            $data['use_credit_reporting_for_calculations'] = 0;
        }

        if (empty($flags[self::CALCULATE_PDN_BEFORE_ISSUANCE])) {
            $data['loan_date'] = $issuanceDate;
        }

        if (empty($flags[self::WITHOUT_REPORTS]) && empty($flags[self::WITHOUT_CH_REPORT])) {
            $data['report_url'] = $reportsUrl[$this->axi::CH_REPORT] ?: '';
        } else {
            $formExpense = $this->getFormExpenseByOrderId($order->order_id);

            if (is_null($formExpense)) {
                if ($order->utm_content === $this->orders::UTM_SOURCE_ORGANIZATION_SWITCH) {
                    $parentOrderId = $this->order_data->read($order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

                    if (!is_null($parentOrderId)) {
                        $formExpense = $this->getFormExpenseByOrderId($parentOrderId);
                    }
                }

                //  if the previous value is not found or is equal to the default value, we generate a new value
                if (is_null($formExpense) || $formExpense === self::DEFAULT_VALUE_FORM_EXPENSE) {
                    $formExpense = $this->getFakeFormExpense();
                }
            }

            $data['form_expense'] = $formExpense;
        }

        if (!empty($flags[self::IS_FORCED_CALCULATION])) {
            $data['is_forced_calculation'] = 1;
        }

        return $data;
    }

    public function getURL($organizationId): string
    {
        $pdnHost = $this->config->pdnHost;

        $paths = [
            17 => self::CALCULATE_ORDER_PDN_FOR_MD_URL,          // море денег
            22 => self::CALCULATE_ORDER_PDN_V4_URL,              // фаст финанс
            14 => self::CALCULATE_ORDER_PDN_V4_URL,              // форинт
            21 => self::CALCULATE_ORDER_PDN_FOR_RUBLE_URL,       // рубль ру
        ];

        $path = $paths[$organizationId] ?? self::CALCULATE_ORDER_PDN_URL;

        return $pdnHost . $path;
    }

    private function isPdnOrganicEnabled(): bool
    {
        $isPdnOrganicEnabled = $this->settings->pdn_organic_enabled;
        return !empty($isPdnOrganicEnabled);
    }

    private function isOrganic(string $utmSource): bool
    {
        return $utmSource === '' || $utmSource === 'Boostra';
    }

    /**
     * @param $organizationId
     * @return string|null
     */
    private function getOrganizationUuid($organizationId): ?string
    {
        return $this->organization_uuids[$organizationId] ?? null;
    }

    /**
     * Установка тестового CLIENT_UID для всех организаций
     */
    public function setTestOrganizationUuid()
    {
        foreach (self::ORGANIZATIONS_UUIDS as $uuid => $value) {
            $this->organization_uuids[$uuid] = self::ORGANIZATIONS_UUIDS[0];
        }
    }

    /**
     * Получить регион проживания
     *
     * @param stdClass $order
     * @return string
     */
    private function getRegion(stdClass $order): string
    {
        if (!empty($order->user_address_faktregion)) {
            return $order->user_address_faktregion . ' ' . $order->user_address_faktregion_shorttype;
        }

        if (!empty($order->Faktregion)) {
            return $order->Faktregion . ' ' . $order->Faktregion_shorttype;
        }

        if (!empty($order->name_zone)) {
            return $order->name_zone;
        }

        return '';
    }

    private function getFormattedRegion(string $region): string
    {
        $regex = '/\bавтономная область\b|' .
            '\bавтономный округ\b|' .
            '\bавт.\b|' .
            '\bокруг\b|' .
            '\bбез авт.округов\b|' .
            '\bобласть\b|' .
            '\bкрай\b|' .
            '\bреспублика\b|' .
            '\bгород\b|' .
            '\bг\b|' .
            '\bрайон\b|' .
            '\bсело\b|' .
            '\bаобл\b|' .
            '\bао\b|' .
            '\bобл\b|' .
            '\bресп\b|' .
            '[^а-яА-Я\- ]/ui';

        return trim(preg_replace($regex, '', $region));
    }

    /**
     * Проверка региона
     *
     * @param string $region
     * @return bool
     */
    private function validateRegion(string $region): bool
    {
        foreach (self::REGIONS_WITH_DISABLED_PDN_CALCULATION as $regionName) {
            if (mb_stripos($regionName, $region) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Получить код региона
     *
     * @param stdClass $order
     * @return int
     */
    private function getRegionCode(stdClass $order): int
    {
        $region = $this->getRegion($order);

        if (empty($region)) {
            throw new RuntimeException('Не удалось определить код региона у клиента. Регион: ' . $region);
        }

        $regionFormatted = $this->getFormattedRegion($region);

        if (!$this->validateRegion($regionFormatted)) {
            throw new RuntimeException('Регион проживания не подлежит расчету ПДН. Регион: ' . $region);
        }

        foreach (self::REGIONS_WITHOUT_CHECK as $regionName => $regionCode) {
            if (mb_stripos($regionName, $regionFormatted) !== false) {
                return $regionCode;
            }
        }

        $regionCodesForPdnCalculation = [];
        foreach (self::REGIONS as $regionName => $regionCode) {
            if (mb_stripos($regionName, $regionFormatted) !== false) {
                $regionCodesForPdnCalculation[] = $regionCode;
            }
        }

        if (!empty($regionCodesForPdnCalculation) && count($regionCodesForPdnCalculation) === 1) {
            return $regionCodesForPdnCalculation[0];
        }

        $regionCodesForPdnCalculation = [];
        foreach (self::REGIONS_VARIATIONS as $regionName => $regionCode) {
            if (mb_stripos($regionName, $regionFormatted) !== false) {
                $regionCodesForPdnCalculation[] = $regionCode;
            }
        }

        if (!empty($regionCodesForPdnCalculation) && count($regionCodesForPdnCalculation) === 1) {
            return $regionCodesForPdnCalculation[0];
        }

        $regionParts = explode(' ', $regionFormatted);
        foreach ($regionParts as $regionPart) {

            $regionCodesForPdnCalculation = [];

            foreach (self::REGIONS as $regionName => $regionCode) {
                if (mb_stripos($regionName, $regionPart) !== false) {
                    $regionCodesForPdnCalculation[] = $regionCode;
                }
            }

            if (!empty($regionCodesForPdnCalculation) && count($regionCodesForPdnCalculation) === 1) {
                return $regionCodesForPdnCalculation[0];
            }
        }

        throw new RuntimeException('Не удалось определить код региона у клиента. Регион: ' . $region);
    }

    private static function getHttpClient(): Client
    {
        if (self::$httpClient === null) {
            self::$httpClient = new Client([
                'http_errors' => true,
                'allow_redirects' => true,
                'connect_timeout' => 20,
                'timeout' => 20,
            ]);
        }

        return self::$httpClient;
    }

    /**
     * @throws GuzzleException
     */
    private function sendRequest(array $data, array $headers, string $url): string
    {
        $res = self::getHttpClient()->request('POST', $url, [
            'json' => $data,
            'headers' => $headers,
            'version' => '1.1',
        ]);

        $body = $res->getBody()->getContents();
        $status = $res->getStatusCode();

        $this->logging(__METHOD__, $url, 'Произведен расчет ПДН', ['data' => $data, 'response' => $body, 'status' => $status], self::LOG_FILE);

        return $body;
    }

    /**
     * @param $birthDate
     * @param $issuanceDate
     * @return int|null
     */
    private function getUserAge($birthDate, $issuanceDate): ?int
    {
        if (empty($birthDate) || empty($issuanceDate)) {
            return null;
        }

        $birthDate = DateTime::createFromFormat('d.m.Y', $birthDate);
        $issuanceDate = DateTime::createFromFormat('Y-m-d', $issuanceDate);

        if ($birthDate === false || $issuanceDate === false) {
            return null;
        }

        $interval = $issuanceDate->diff($birthDate);

        return $interval === false ? null : $interval->format('%y');
    }

    public function onlyConvertCreditHistoryEncoding(stdClass $order)
    {
        $order = $this->getOrderData([
            'order_uid' => $order->order_number
        ]);

        if (empty($order)) {
            throw new RuntimeException('Заявка с номером ' . $order->order_number . ' не найдена..');
        }

        $filePath = $this->report->getReportFilePath($order, $this->axi::CH_REPORT);

        if (!file_exists($filePath)) {
            throw new RuntimeException('Локальный файл кредитного отчета для заявки ' . $order->order_number . ' не найден');
        }

        $data = file_get_contents($filePath);

        if (empty($data)) {
            throw new RuntimeException('Файл кредитного отчета для заявки ' . $order->order_number . ' пустой');
        }

        $data = iconv('UTF-8', 'windows-1251', $data);

        $result = file_put_contents($filePath, $data);

        if (empty($result)) {
            throw new RuntimeException('Не удалось сохранить файл по пути ' . $filePath);
        }
    }

    /**
     * При флаге ONLY_UPDATE_PDN только обновляем значение ПДН в заявке
     *
     * @param stdClass $order
     * @param float $pdnValue
     * @return void
     */
    public function onlyUpdatePdnValue(stdClass $order, float $pdnValue)
    {
        $order = $this->getOrderData([
            'order_uid' => $order->order_number
        ]);

        if (empty($order)) {
            throw new RuntimeException('Заявка с номером ' . $order->order_number . ' не найдена.');
        }

        $this->orders->update_order($order->order_id, [
            'pdn_nkbi_loan' => $pdnValue
        ]);

        $data = [
            'order_id' => $order->order_id,
            'pdn' => $pdnValue
        ];

        $this->savePdnData($data, (int)$order->organization_id);
    }

    /**
     * При флаге ONLY_DEBTS_DOCUMENT только отправляем данные для "Лист оценки платежеспособности" в 1С, без расчета ПДН
     *
     * @param stdClass $order
     * @param stdClass $pdnCalculationResult
     * @return void
     */
    public function onlyAddDebtsDocumentAction(stdClass $order, stdClass $pdnCalculationResult)
    {
        $order = $this->getOrderData([
            'order_uid' => $order->order_number
        ]);

        if (empty($order)) {
            throw new RuntimeException('Заявка с номером ' . $order->order_number . ' не найдена');
        }

        $this->pdnCalculation->sendPdnResultTo1C($order, $pdnCalculationResult);
    }

    /**
     * Отправляет расчет ПДН и данные для "Лист оценки платежеспособности заемщика" в 1С
     *
     * @param stdClass $order
     * @param stdClass $pdnCalculationResult
     * @return void
     */
    private function sendPdnResultTo1C(stdClass $order, stdClass $pdnCalculationResult)
    {
        $efrsb = $this->checkEfrsb($order);
        $fssp = $this->checkFssp($order);

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '',
            'До запроса в 1C', self::LOG_FILE);

        $result = $this->soap->send_pdn($order, $pdnCalculationResult, $efrsb, $fssp);

        $this->logging(__METHOD__, '', '', 'После запроса в 1C. Продолжительность: ' .
            (microtime(true) - $executionStartTime) . ' сек', self::LOG_FILE);

        $this->savePdn([
            'order_id' => $order->order_id,
            'debts_document_added' => $result['response'] ?? $result['errors']
        ]);
    }

    /**
     * Проверка на банкротство
     *
     * @param stdClass $order
     * @return string
     */
    private function checkEfrsb(stdClass $order): string
    {
        return 'Не удалось определить';

        $scoring = $this->getLastScoring($order, $this->scorings::TYPE_EFRSB);

        if (!empty($scoring) && (int)$scoring->status === $this->scorings::STATUS_COMPLETED) {
            return !empty($scoring->success) ? 'Отсутствует' : 'Найдено';
        }

        $newScoring = array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'status' => $this->scorings::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => $this->scorings::TYPE_EFRSB,
        );

        $scoringId = $this->scorings->add_scoring($newScoring);
        $this->efrsb->run_scoring($scoringId);

        $scoring = $this->scorings->get_scoring($scoringId);

        if (!empty($scoring) && (int)$scoring->status === $this->scorings::STATUS_COMPLETED) {
            return !empty($scoring->success) ? 'Отсутствует' : 'Найдено';
        }

        return 'Не удалось определить';
    }

    /**
     * @param stdClass $order
     * @param int $scoringType
     * @return stdClass|null
     */
    private function getLastScoring(stdClass $order, int $scoringType): ?stdClass
    {
        $lastScoring = $this->scorings->getLastScoring([
            'order_id' => $order->order_id,
            'type' => $scoringType
        ]);

        // order_id может быть 0, поэтому ищем по user_id
        if (empty($lastScoring)) {
            $lastScoring = $this->scorings->getLastScoring([
                'order_id' => 0,
                'user_id' => $order->user_id,
                'type' => $scoringType
            ]);

            if (!empty($lastScoring)) {
                try {
                    $scoringDate = new DateTimeImmutable($lastScoring->created);
                } catch (Throwable $e) {
                    return null;
                }

                if ($scoringDate->format('%a') > self::MAX_EFRSB_AND_FSSP_SCORING_AVAILABILITY_DAYS) {
                    return null;
                }
            }
        }

        return $lastScoring ?: null;
    }

    /**
     * Получение кол-ва исполнительных производств (истребование денег с должника)
     *
     * @param stdClass $order
     * @return string
     */
    private function checkFssp(stdClass $order): string
    {
        // Для ускорения расчета ПДН отключаем запрос ФССП
        return 'Не удалось определить';

        $scoring = $this->getLastScoring($order, $this->scorings::TYPE_FSSP);

        if (!empty($scoring) && (int)$scoring->status === $this->scorings::STATUS_COMPLETED && !empty($scoring->body)) {
            $fsspDebtsRecordsAmount = $this->fssp->getFsspDebtsRecordsAmount((int)$scoring->id);
            return $fsspDebtsRecordsAmount !== null ? (string)$fsspDebtsRecordsAmount : 'Не удалось определить';
        }

        $newScoring = array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'status' => $this->scorings::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => $this->scorings::TYPE_FSSP,
        );

        $scoringId = $this->scorings->add_scoring($newScoring);

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'До запроса в ФССП', self::LOG_FILE);

        $this->fssp->run_scoring($scoringId);

        $this->logging(__METHOD__, '', '', 'После запроса в ФССП. Продолжительность: ' .
            (microtime(true) - $executionStartTime) . ' сек', self::LOG_FILE);

        if ($scoringId) {
            $fsspDebtsRecordsAmount = $this->fssp->getFsspDebtsRecordsAmount($scoringId);
            return $fsspDebtsRecordsAmount !== null ? (string)$fsspDebtsRecordsAmount : 'Не удалось определить';
        }

        return 'Не удалось определить';
    }

    private function getNumbersDaysInYear(): int
    {
        return Carbon::now()->daysInYear;
    }

    private function getFakeFormExpense(): int
    {
        // 15% клиентов — 0
        if (mt_rand(1, 100) <= 15) {
            return 0;
        }

        // диапазон от 1000 до 5000 с шагом 100
        $min = 1000;
        $max = 5000;
        $step = 100;

        $value = mt_rand($min / $step, $max / $step) * $step;

        return $value;
    }

    /**
     * @param string $orderUid
     * @param array $flags
     * @return false|stdClass
     */
    public function calculatePdnForRcl(string $orderUid, array $flags)
    {
        $order = $this->getOrderData([
            'order_uid' => $orderUid
        ]);

        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена', ['order_uid' => $orderUid, 'order' => $order], self::LOG_FILE);
            return false;
        }

        $regionCode = $this->getRegionCode($order);

        try {
            $response = $this->getPdnCalculationForRcl($order, $flags, $regionCode);
        } catch (Throwable $error) {
            $this->logging(__METHOD__, '', 'Ошибка при расчете ПДН для ВКЛ', ['order_id' => (int)$order->order_id, 'error' => $error], self::LOG_FILE);
            $this->saveErrorPdnCalculation($order, $error->getMessage());

            $this->notificationCenter->notifyTemplate(
                'telegram_pm',
                'calculation_error.tpl',
                [
                    'order_id' => $order->order_id,
                    'order_number' => $order->contract_number,
                    'error' => $error->getMessage(),
                    'calculate_date' => date('d.m.Y H:i'),
                ]
            );

            return false;
        }

        return $response;
    }

    /**
     * @return false|stdClass
     * @throws GuzzleException
     */
    private function getPdnCalculationForRcl(stdClass $order, array $flags, int $regionCode)
    {
        $reportsUrl = [];

        if (empty($flags[self::WITHOUT_REPORTS])) {
            $checkReportsDateResult = $this->checkReportsDate($order, $flags);

            if (empty($checkReportsDateResult['success'])) {
                throw new RuntimeException($checkReportsDateResult['message'] ?: 'Не удалось проверить актуальность отчетов');
            }

            $reportsUrl = [
                $this->axi::SSP_REPORT => $this->getReportUrl($order, $this->axi::SSP_REPORT),
                $this->axi::CH_REPORT => $this->getReportUrl($order, $this->axi::CH_REPORT)
            ];
        }

        $pdnData = $this->getPdnDataForRcl($order, $regionCode, $reportsUrl, $flags);

        $this->logging(__METHOD__, '', 'Данные для расчета ПДН для ВКЛ', ['order_id' => $order->order_id, 'pdn_data' => $pdnData], self::LOG_FILE);

        $jsonResponse = $this->sendRequest($pdnData, self::CALCULATE_ORDER_PDN_HEADERS, $pdnData['url']);

        $response = json_decode($jsonResponse);

        if (empty($response->dbi)) {
            $this->saveErrorPdnCalculation($order, $jsonResponse);
            return false;
        }

        $this->saveSuccessPdnCalculationForRcl($order, $jsonResponse);

        return $response;
    }

    private function getPdnDataForRcl(stdClass $order, int $regionCode, array $reportsUrl, array $flags): array
    {
        $issuanceDate = date('Y-m-d');

        $data = [
            'loan_request_date' => $issuanceDate,
            'client_uuid' => $this->getOrganizationUuid($order->organization_id),
            'region_number' => $regionCode,
            'order_number' => $order->order_uid,
            'borrower_id' => $order->user_id,
            'url' => $this->config->pdnHost . self::CALCULATE_RCL_PDN,
            'use_credit_reporting_for_calculations' => 0
        ];

        if (empty($flags[self::WITHOUT_REPORTS])) {
            $data['use_credit_reporting_for_calculations'] = 1;
            $data['amp_report_url'] = $reportsUrl[$this->axi::SSP_REPORT] ?: '';
            $data['report_url'] = $reportsUrl[$this->axi::CH_REPORT] ?: '';
        }

        return $data;
    }

    private function saveSuccessPdnCalculationForRcl(stdClass $order, string $pdnResult): void
    {
        $data = [
            'order_id' => $order->order_id,
            'order_uid' => $order->order_uid,
            'contract_number' => $order->contract_number ?? '',
            'date_create' => date('Y-m-d H:i:s'),
            'success' => 1,
            'result' => $pdnResult,
            'auto_recalc' => false,
        ];

        $this->savePdn($data);
    }

    private function getFormExpenseByOrderId(string $orderId): ?int
    {
        $sql = <<<SQL
            SELECT
                IF(JSON_VALID(request), JSON_UNQUOTE(JSON_EXTRACT(request, '$.form_expense')), NULL) AS form_expense
            FROM s_pdn_calculation
            WHERE order_id = ?
                AND request IS NOT NULL
            ORDER BY date_create DESC
            LIMIT 1;
        SQL;

        $query = $this->db->placehold($sql, $orderId);

        $this->db->query($query);
        $result = $this->db->result();

        // if the string is not found or the value is not numeric
        if (is_null($result) || !is_numeric($result->form_expense)) {
            return null;
        }

        return (int) $result->form_expense;
    }

    public function getLastValidAddressForUserInRegion($userId, $orderId, $newRegionId)
    {
        $sql = <<<SQL
            SELECT rspdn.fakt_address
            FROM s_orders o
                     LEFT JOIN s_pdn_calculation lgpdn on o.id = lgpdn.order_id
                     LEFT JOIN pdn_calculation rspdn on o.id = rspdn.order_id
            WHERE user_id = ?
              AND o.id < ?
              AND JSON_VALID(rspdn.fakt_address)
              AND JSON_VALID(lgpdn.result)
              AND CAST(lgpdn.result->>'$.new_region_id' AS UNSIGNED) = ?
            ORDER BY o.id DESC
            LIMIT 1;
        SQL;

        $query = $this->db->placehold($sql, (int) $userId, (int) $orderId, (int) $newRegionId);

        $this->db->query($query);
        return $this->db->result();
    }
}