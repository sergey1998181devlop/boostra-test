<?php

use boostra\services\RegionService;

ini_set('memory_limit', '2048M');
error_reporting(0);
ini_set('display_errors', 'Off');

require_once 'View.php';

require_once __DIR__ . '/../lib/autoloader.php';

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class SmsSendingReportView extends View
{
    private const LIMIT_PER_PAGE = 250;

    /** @var int День начала просрочки по умолчанию */
    private const DEFAULT_DELAY_DAY_START = 0;
    /** @var int День окончания просрочки по умолчанию */
    private const DEFAULT_DELAY_DAY_END = 0;

    /** @var string Экшен скачивания excel файла для отправки смс */
    private const DOWNLOAD_EXCEL_SMS_ACTION = 'download_excel_sms';
    /** @var string Экшен скачивания excel файла для отправки в IVR */
    private const DOWNLOAD_EXCEL_IVR_ACTION = 'download_excel_ivr';
    /** @var string Экшен скачивания excel файла контрольной группы для отправки смс */
    private const DOWNLOAD_EXCEL_SMS_CONTROL_ACTION = 'download_excel_sms_control';
    /** @var string Экшен скачивания excel файла контрольной группы для отправки в IVR */
    private const DOWNLOAD_EXCEL_IVR_CONTROL_ACTION = 'download_excel_ivr_control';

    /**
     * @return string|void
     */
    public function fetch()
    {
        try {
            $action = $this->request->post('action');

            if (!empty($action)) {
                $this->doAction($action);
                return;
            }

            return $this->getList();

        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', '', ['error' => $error], 'sms_sending_report_view.txt');

            $this->design->assign('error', $e->getMessage());
            return $this->design->fetch('sms_sending_report.tpl');
        }
    }

    /**
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function doAction(string $action): void
    {
        if ($action === self::DOWNLOAD_EXCEL_SMS_ACTION) {
            $this->downloadExcelSms();
        } elseif ($action === self::DOWNLOAD_EXCEL_IVR_ACTION) {
            $this->downloadExcelIvr();
        } elseif ($action === self::DOWNLOAD_EXCEL_SMS_CONTROL_ACTION) {
            $this->downloadExcelSmsControl();
        } elseif ($action === self::DOWNLOAD_EXCEL_IVR_CONTROL_ACTION) {
            $this->downloadExcelIvrControl();
        }
    }

    /**
     * Экшен скачивания excel для отправки смс
     *
     * @param string $fileName
     * @param bool $controlGroup Получить отчет для контрольной группы (т.е. WHERE для sql инвертируем)
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function downloadExcelSms(string $fileName = '', bool $controlGroup = false): void
    {
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex();
        $active_sheet = $excel->getActiveSheet();

        $fileName = $fileName ?: 'Отчет для отправки смс от ' . date('Y_m_d_H_i_s') . '.xls';

        $filePath = 'files/reports/' . $fileName;

        $active_sheet->setTitle('Отчет от ' . date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName()->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $filters = $this->getFilters();

        $orders = $this->getOrders($filters, $controlGroup);

        $row = 1;
        foreach ($orders as $order) {
            $cell = 'A';
            $active_sheet->setCellValue($cell++ . $row, $order->contract_number);
            $active_sheet->setCellValue($cell++ . $row, $order->phone_mobile);
            $active_sheet->setCellValue($cell . $row, (string)$order->timezone);
            $row++;
        }

        for ($i = 'A'; $i <= $active_sheet->getHighestColumn(); $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $this->saveExcelFile($excel, $filePath, $fileName);
    }

    /**
     * Сохранение excel файла
     *
     * @param PHPExcel $excel
     * @param string $filePath
     * @param string $fileName
     * @return void
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function saveExcelFile(PHPExcel $excel, string $filePath, string $fileName)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $filePath = $this->config->root_dir . $filePath;
        $objWriter->save($filePath);

        if (!file_exists($filePath)) {
            throw new RuntimeException('Файл не найден');
        }

        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $fileName);
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        readfile($filePath);

        die();
    }

    /**
     * Экшен скачивания excel для отправки в IVR
     *
     * @param string $fileName
     * @param bool $controlGroup Получить отчет для контрольной группы (т.е. WHERE для sql инвертируем)
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function downloadExcelIvr(string $fileName = '', bool $controlGroup = false): void
    {
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex();
        $active_sheet = $excel->getActiveSheet();

        $fileName = $fileName ?: 'Отчет для отправки IVR от ' . date('Y_m_d_H_i_s') . '.xls';

        $filePath = 'files/reports/' . $fileName;

        $active_sheet->setTitle('Отчет от ' . date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName()->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $filters = $this->getFilters();

        $orders = $this->getOrders($filters, $controlGroup);

        $row = 1;
        foreach ($orders as $order) {
            $cell = 'A';
            $active_sheet->setCellValue($cell . $row, $order->phone_mobile);
            $row++;
        }

        for ($i = 'A'; $i <= $active_sheet->getHighestColumn(); $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $this->saveExcelFile($excel, $filePath, $fileName);
    }

    /**
     * Экшен скачивания excel контрольной группы для отправки смс
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function downloadExcelSmsControl(): void
    {
        $fileName = 'Отчет для отправки смс контроль от ' . date('Y_m_d_H_i_s') . '.xls';
        $this->downloadExcelSms($fileName, true);
    }

    /**
     * Экшен скачивания excel контрольной группы для отправки в IVR
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function downloadExcelIvrControl(): void
    {
        $fileName = 'Отчет для отправки IVR контроль от ' . date('Y_m_d_H_i_s') . '.xls';
        $this->downloadExcelIvr($fileName, true);
    }

    /**
     * Получить список заявок с просрочкой
     *
     * @return string
     */
    private function getList(): string
    {
        $filters = $this->getFilters();
        $this->design->assign('filters', $filters);

        $this->design->assign('day_of_delay_start', $this->getDayOfDelayStart());
        $this->design->assign('day_of_delay_end', $this->getDayOfDelayEnd());

        $orders = $this->getOrders($filters);
        $ordersToShow = $this->getOrdersToShow($orders);
        $this->design->assign('items', $ordersToShow);
        $this->design->assign('total_items_amount', count($orders));

        $columns = $this->getColumns();
        $this->design->assign('columns', $columns);

        $this->design->assign('total_pages_num', $this->getTotalPagesAmount($orders));
        $this->design->assign('current_page_num', $this->request->get('page') ?? 1);

        return $this->design->fetch('sms_sending_report.tpl');
    }

    /**
     * @return int
     */
    private function getDayOfDelayStart(): int
    {
        $dayOfDelayStart = $this->request->post('day_of_delay_start');

        if (!is_numeric($dayOfDelayStart)) {
            return self::DEFAULT_DELAY_DAY_START;
        }

        return (int)$dayOfDelayStart;
    }

    /**
     * @return int
     */
    private function getDayOfDelayEnd(): int
    {
        $dayOfDelayEnd = $this->request->post('day_of_delay_end');

        if (!is_numeric($dayOfDelayEnd)) {
            return self::DEFAULT_DELAY_DAY_END;
        }

        return (int)$dayOfDelayEnd;
    }

    /**
     * Получить фильтры из crm
     *
     * @return object[]
     */
    public function getFilters(): array
    {
        $filters = [
            'u.birth' => (object)[
                'name' => 'Дата рождения',
                'code' => 'u.birth',
                'type' => 'daterange',
                'show_label' => true,
                'value' => null
            ],
            's.scorista_ball' => (object)[
                'name' => 'Балл скористы',
                'code' => 's.scorista_ball',
                'type' => 'numberrange',
                'show_label' => true,
                'pair_label' => 's.scorista_ball_to',
                'value' => 0
            ],
            's.scorista_ball_to' => (object)[
                'name' => 'Балл скористы',
                'code' => 's.scorista_ball_to',
                'type' => 'numberrange',
                'show_label' => false,
                'value' => 1000
            ],
            'u.gender' => (object)[
                'name' => 'Пол',
                'code' => 'u.gender',
                'type' => 'select',
                'show_label' => true,
                'value' => [
                    (object)[
                        'value' => '-',
                        'name' => 'Не выбрано',
                        'selected' => false
                    ],
                    (object)[
                        'value' => 'male',
                        'name' => 'Мужской',
                        'selected' => false
                    ],
                    (object)[
                        'value' => 'female',
                        'name' => 'Женский',
                        'selected' => false
                    ],
                    (object)[
                        'value' => '',
                        'name' => 'Не указан',
                        'selected' => false
                    ],
                ]
            ],
            's.success' => (object)[
                'name' => 'Решение скористы',
                'code' => 's.success',
                'type' => 'select',
                'show_label' => true,
                'value' => [
                    (object)[
                        'value' => '-',
                        'name' => 'Не выбрано',
                        'selected' => false
                    ],
                    (object)[
                        'value' => '0',
                        'name' => 'Отказ',
                        'selected' => false
                    ],
                    (object)[
                        'value' => '1',
                        'name' => 'Одобрено',
                        'selected' => false
                    ],
                    (object)[
                        'value' => 'NULL',
                        'name' => 'Без решения',
                        'selected' => false
                    ],
                ]
            ],
            'o.utm_source' => (object)[
                'name' => 'Источник',
                'code' => 'o.utm_source',
                'type' => 'select',
                'show_label' => true,
                'value' => [
                    (object)[
                        'value' => '-',
                        'name' => 'Не выбрано',
                        'selected' => false
                    ],
                ]
            ],
            'o.organization_id' => (object)[
                'name' => 'Организация',
                'code' => 'o.organization_id',
                'type' => 'select',
                'show_label' => true,
                'value' => [
                    (object)[
                        'value' => '-',
                        'name' => 'Не выбрано',
                        'selected' => false
                    ],
                ]
            ],
            'u.Regregion' => (object)[
                'name' => 'Регион регистрации',
                'code' => 'u.Regregion',
                'type' => 'multiselect',
                'show_label' => true,
                'value' => []
            ],
            'u.Faktregion' => (object)[
                'name' => 'Регион проживания',
                'code' => 'u.Faktregion',
                'type' => 'multiselect',
                'show_label' => true,
                'value' => []
            ],
        ];

        $filters = $this->addRegionsToFilters($filters);
        $filters = $this->addOrganizationsToFilters($filters);
        $filters = $this->addSourcesToFilter($filters);

        $filtersInRequest = $this->request->post('filters');

        if ($filtersInRequest === null) {
            return $filters;
        }

        return $this->mergeFiltersWithFiltersInRequest($filters, $filtersInRequest);
    }

    /**
     * Добавляет фильтр по региону
     *
     * @param array $filters
     * @return array
     */
    private function addRegionsToFilters(array $filters): array
    {
        $regions = (new RegionService())->getRegions();

        $notSelectedOption = [
            'value' => '-',
            'name' => 'Не выбрано',
            'selected' => false
        ];

        // Нужно избегать передачи по ссылке, чтобы, например, при выборе региона регистрации не выбирался регион проживания
        $filters['u.Regregion']->value[] = (object)$notSelectedOption;
        $filters['u.Faktregion']->value[] = (object)$notSelectedOption;

        $notIndicatedOption = [
            'value' => '',
            'name' => 'Не указан',
            'selected' => false
        ];

        $filters['u.Regregion']->value[] = (object)$notIndicatedOption;
        $filters['u.Faktregion']->value[] = (object)$notIndicatedOption;

        foreach ($regions as $region) {

            $regionForFilter = [
                'value' => $region->name,
                'name' => $region->name,
                'selected' => false
            ];

            $filters['u.Regregion']->value[] = (object)$regionForFilter;
            $filters['u.Faktregion']->value[] = (object)$regionForFilter;
        }

        return $filters;
    }

    /**
     * Добавляет фильтр по организации
     *
     * @param array $filters
     * @return array
     */
    private function addOrganizationsToFilters(array $filters): array
    {
        $organizations = $this->organizations->getList();

        $organizationsIdToShow = [
            $this->organizations::BOOSTRA_ID,
            $this->organizations::AKVARIUS_ID,
            $this->organizations::AKADO_ID,
            $this->organizations::FINLAB_ID,
        ];

        foreach ($organizations as $organization) {

            if (!in_array($organization->id, $organizationsIdToShow)) {
                continue;
            }

            $organizationForFilter = [
                'value' => $organization->id,
                'name' => $organization->short_name,
                'selected' => false
            ];

            $filters['o.organization_id']->value[] = (object)$organizationForFilter;
        }

        return $filters;
    }

    /**
     * Добавляет фильтр по источнику
     *
     * @param array $filters
     * @return array
     */
    private function addSourcesToFilter(array $filters): array
    {
        $sources = $this->leadgidScorista->getAll();

        foreach ($sources as $source) {
            $sourceForFilter = [
                'value' => $source->utm_source,
                'name' => $source->utm_source,
                'selected' => false
            ];

            $filters['o.utm_source']->value[] = (object)$sourceForFilter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @param array $filtersInRequest
     * @return array
     */
    private function mergeFiltersWithFiltersInRequest(array $filters, array $filtersInRequest): array
    {
        foreach ($filtersInRequest as $filterCode => $filterValue) {

            if ($filters[$filterCode]->type === 'select') {
                foreach ($filters[$filterCode]->value as $option) {
                    if ($option->value === $filterValue) {
                        $option->selected = true;
                    }
                }

                continue;
            }

            if ($filters[$filterCode]->type === 'multiselect') {
                foreach ($filterValue as $optionInRequest) {
                    foreach ($filters[$filterCode]->value as $option) {
                        if ($option->value === $optionInRequest) {
                            $option->selected = true;
                        }
                    }
                }

                continue;
            }

            if (empty($filters[$filterCode]) || $filterValue === '') {
                continue;
            }

            $filters[$filterCode]->value = $filterValue;
        }

        return $filters;
    }

    /**
     * Получить столбцы для таблицы в crm
     *
     * @return object[]
     */
    private function getColumns(): array
    {
        return [
            'counter' => (object)[
                'name' => '№',
                'code' => 'counter',
            ],
            'contract_number' => (object)[
                'name' => 'Номер договора',
                'code' => 'contract_number',
            ],
            'order_id' => (object)[
                'name' => 'ID заявки',
                'code' => 'order_id',
            ],
            'user_id' => (object)[
                'name' => 'ID клиента',
                'code' => 'user_id',
            ],
            'fio' => (object)[
                'name' => 'ФИО',
                'code' => 'fio',
            ],
            'birth' => (object)[
                'name' => 'Дата рождения',
                'code' => 'birth',
            ],
            'gender' => (object)[
                'name' => 'Пол',
                'code' => 'gender',
            ],
            'Regregion' => (object)[
                'name' => 'Регион регистрации',
                'code' => 'Regregion',
            ],
            'Faktregion' => (object)[
                'name' => 'Регион проживания',
                'code' => 'Faktregion',
            ],
            'delay_day' => (object)[
                'name' => 'День просрочки',
                'code' => 'delay_day',
            ],
            'scorista_success' => (object)[
                'name' => 'Решение скористы',
                'code' => 'scorista_success',
            ],
            'scorista_ball' => (object)[
                'name' => 'Балл скористы',
                'code' => 'scorista_ball',
            ],
            'organization_id' => (object)[
                'name' => 'Организация',
                'code' => 'organization_id',
            ],
            'utm_source' => (object)[
                'name' => 'Источник',
                'code' => 'utm_source',
            ],
            'timezone' => (object)[
                'name' => 'GMT (МСК: 0)',
                'code' => 'timezone',
            ],
            'phone_mobile' => (object)[
                'name' => 'Телефон',
                'code' => 'phone_mobile',
            ],
            'email' => (object)[
                'name' => 'Email',
                'code' => 'email',
            ],
            'whatsapp' => (object)[
                'name' => 'WhatsApp',
                'code' => 'whatsapp',
            ],
            'viber' => (object)[
                'name' => 'Viber',
                'code' => 'viber',
            ],
            'skype' => (object)[
                'name' => 'Skype',
                'code' => 'skype',
            ],
        ];
    }

    /**
     * Получить список заявок с просрочкой
     *
     * @param array $filters
     * @param bool $controlGroup Получить отчет для контрольной группы (т.е. WHERE для sql инвертируем)
     * @return array
     */
    private function getOrders(array $filters, bool $controlGroup = false): array
    {
        if (!$this->validateDatesOfDelay()) {
            return [];
        }

        $overdueContracts = $this->getOverdueContracts();

        if (empty($overdueContracts)) {
            return [];
        }

        // Устанавливаем ключами LoanNumber
        $overdueContracts = array_column($overdueContracts, null, 'LoanNumber');

        $orders = $this->getOverdueOrders($overdueContracts, $filters, $controlGroup);

        $uniqueOrders = $this->remainUniqueOrders($orders);

        if (empty($uniqueOrders)) {
            return [];
        }

        $usersData = $this->getUsersData($uniqueOrders);

        return $this->formatOrders($uniqueOrders, $overdueContracts, $usersData);
    }

    /**
     * @return bool
     */
    private function validateDatesOfDelay(): bool
    {
        if ($this->getDayOfDelayStart() > $this->getDayOfDelayEnd()) {
            $this->design->assign('error', 'День начала просрочки не может быть больше дня окончания просрочки');
            return false;
        }

        return true;
    }

    /**
     * Получить заявки с просрочкой из 1С (получаем номер договора и день просрочки)
     *
     * @return array
     */
    private function getOverdueContracts(): array
    {
        $response = $this->soap->getOverdueContracts($this->getDayOfDelayStart(), $this->getDayOfDelayEnd());

        if (empty($response['response'])) {
            throw new RuntimeException('Ошибка при запросе в 1С для получения просроченных заявок');
        }

        return $response['response'];
    }

    /**
     * Получить просроченные заявки
     *
     * @param array $overdueContracts
     * @param array $filters
     * @param bool $controlGroup
     * @return array
     */
    private function getOverdueOrders(array $overdueContracts, array $filters, bool $controlGroup): array
    {
        // По 5000 номеров договоров будет помещено в WHERE IN ()
        $chunkedOverdueContracts = array_chunk($overdueContracts, 5000);

        $orders = [];
        foreach ($chunkedOverdueContracts as $overdueContractsInChunk) {
            $overdueContractsNumberInChunk = array_column($overdueContractsInChunk, 'LoanNumber');

            $moreOrders = $this->fetchOrders($overdueContractsNumberInChunk, $filters, $controlGroup);

            $orders = array_merge($orders, $moreOrders);
        }

        return $orders;
    }

    /**
     * Получить просроченные заявки после фильтрации
     *
     * @param array $overdueContractsNumber
     * @param array $filters
     * @param bool $controlGroup
     * @return array
     */
    private function fetchOrders(array $overdueContractsNumber, array $filters, bool $controlGroup): array
    {
        $where = $this->getSqlWhere($overdueContractsNumber, $filters, $controlGroup);

        $query = $this->db->placehold("
            SELECT o.id AS order_id, u.id as user_id, u.birth as birth, s.scorista_ball as scorista_ball, 
                   org.short_name AS organization_id, o.utm_source as utm_source,
                   CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) AS fio,
                   c.number AS contract_number, u.phone_mobile AS phone_mobile, u.email as email, u.timezone_id AS timezone,
                   s.success AS scorista_success, s.id as scorista_scoring_id, u.gender AS gender, 
                   IF (ua_reg.region IS NOT NULL, ua_reg.region, u.Regregion) AS Regregion,
                   IF (ua_fakt.region IS NOT NULL, ua_fakt.region, u.Faktregion) AS Faktregion,
                   IF (ua_reg.city IS NOT NULL, ua_reg.city, u.Regcity) AS Regcity,
                   IF (ua_fakt.city IS NOT NULL, ua_fakt.city, u.Faktcity) AS Faktcity
            FROM s_orders AS o

            INNER JOIN s_users AS u ON u.id = o.user_id
            LEFT JOIN s_scorings AS s ON s.user_id = o.user_id AND s.type = 1 AND s.status = 4
            LEFT JOIN users_addresses AS ua_reg ON ua_reg.id = u.registration_address_id
            LEFT JOIN users_addresses AS ua_fakt ON ua_fakt.id = u.factual_address_id
            LEFT JOIN s_contracts AS c ON c.id = o.contract_id
            LEFT JOIN s_organizations AS org ON org.id = o.organization_id

            WHERE $where
            ORDER BY s.id, o.id DESC"
        );

        $this->db->query($query);

        $orders = $this->db->results();

        if (empty($orders)) {
            return [];
        }

        return $orders;
    }

    /**
     * Получить WHERE для sql для фильтрации заявок из 1С с просрочкой
     *
     * @param array $overdueContractsNumberInChunk Номера договоров просроченных заявок
     * @param array $filters
     * @param bool $controlGroup Получить отчет для контрольной группы (т.е. инвертируем WHERE для sql)
     * @return string
     */
    private function getSqlWhere(array $overdueContractsNumberInChunk, array $filters, bool $controlGroup = false): string
    {
        $conditions = [];

        foreach ($filters as $filter) {
            if (!isset($filter->value)) {
                continue;
            }

            // Дата от и до
            if ($filter->type === 'daterange') {
                $this->addDateRangeCondition($filter, $conditions);
            } elseif ($filter->type === 'numberrange') {
                $this->addNumberRangeCondition($filter, $conditions);
            } // Селектор
            elseif ($filter->type === 'select') {
                $this->addSelectCondition($filter, $conditions);
            } // Мультиселект
            elseif ($filter->type === 'multiselect') {
                $this->addMultiSelectCondition($filter, $conditions);
            } // Остальное
            else {
                $this->addOtherCondition($filter, $conditions);
            }
        }

        if ($controlGroup) {
            $conditions = $conditions ? 'NOT (' . implode(' AND ', $conditions) . ')' : '0';
            $conditions = [$conditions];
        }

        // Добавляем в WHERE проверку только по просроченным заявкам
        $conditions[] = $this->db->placehold("c.number IN (?@)", $overdueContractsNumberInChunk);

        return $conditions ? implode(' AND ', $conditions) : '1';
    }

    /**
     * Оставить только уникальные заявки
     *
     * @param array $orders
     * @return array
     */
    private function remainUniqueOrders(array $orders): array
    {
        // Оставляем только уникальные заявки (так как, например, скориста по заявке могла выполняться несколько раз) и добавляем счетчик
        $i = 0;
        $uniqueOrders = [];
        foreach ($orders as $order) {
            if (empty($uniqueOrders[$order->order_id])) {
                $order->counter = ++$i;
                $uniqueOrders[$order->order_id] = $order;
            }
        }

        return $uniqueOrders;
    }

    /**
     * @param array $orders
     * @return array
     */
    private function getUsersData(array $orders): array
    {
        $usersId = array_column($orders, 'user_id');

        $usersData = $this->user_data->getAll($usersId);

        if (empty($usersData)) {
            return [];
        }

        $formattedUsersData = [];
        foreach ($usersData as $userData) {
            $formattedUsersData[$userData->user_id][$userData->key] = $userData;
        }

        return $formattedUsersData;
    }

    /**
     * @param array $orders
     * @param array $overdueContracts
     * @param array $usersData
     * @return array
     */
    private function formatOrders(array $orders, array $overdueContracts, array $usersData): array
    {
        foreach ($orders as $order) {
            $order->delay_day = $overdueContracts[$order->contract_number]['DayOfDelay'];
            $order->timezone = Helpers::getRegionTimezone($order);
            $order->gender = $order->gender === 'male' ? 'мужской' : 'женский';

            if ($order->scorista_success === '0') {
                $order->scorista_success = 'Отказ';
            } elseif ($order->scorista_success === '1') {
                $order->scorista_success = 'Одобрено';
            } else {
                $order->scorista_success = 'Без решения';
            }

            $order->fio = mb_convert_case($order->fio, MB_CASE_TITLE, "UTF-8");
            $order->email = $order->email ?: 'Не указан';

            $order->whatsapp = $this->getMessenger($order, $usersData, 'has_whatsapp', 'whatsapp_phone');
            $order->viber = $this->getMessenger($order, $usersData, 'has_viber', 'viber_phone');
            $order->skype = $this->getMessenger($order, $usersData, 'has_skype', 'skype_login');
        }

        return $orders;
    }

    /**
     * Получить мессенджер пользователя (если был ранее найден)
     *
     * @param stdClass $order
     * @param array $usersData
     * @param string $messengerKeyToCheckExistence
     * @param string $messengerKeyToGet
     * @return string
     */
    private function getMessenger(stdClass $order, array $usersData, string $messengerKeyToCheckExistence, string $messengerKeyToGet): string
    {
        if (!isset($usersData[$order->user_id][$messengerKeyToCheckExistence]->value)) {
            return 'Поиск не проводился';
        }

        if ($usersData[$order->user_id][$messengerKeyToCheckExistence]->value === '0' || empty($usersData[$order->user_id][$messengerKeyToGet]->value)) {
            return 'Не найден';
        }

        return $usersData[$order->user_id][$messengerKeyToGet]->value;
    }

    /**
     * Получить заявки, которые необходимо отобразить согласно пагинации
     *
     * @param array $orders
     * @return array
     */
    private function getOrdersToShow(array $orders): array
    {
        $offset = 0;

        if (!empty($this->request->get('page'))) {
            $offset = ($this->request->get('page', 'integer') - 1) * self::LIMIT_PER_PAGE;
        }

        $ordersToShow = array_slice($orders, $offset, self::LIMIT_PER_PAGE);

        // Если заявки найдены, но на выбранной странице заявок нет, то просто возвращаем первую страницу
        if (!empty($orders) && empty($ordersToShow)) {
            return $orders;
        }

        return $ordersToShow;
    }

    /**
     * Добавляет условие типа Дата в WHERE для получения заявок
     *
     * @param stdClass $filter
     * @param array $conditions
     * @return void
     */
    private function addDateRangeCondition(stdClass $filter, array &$conditions): void
    {
        $dates = array_map('trim', explode('-', $filter->value));
        $filterDateStart = (new DateTimeImmutable())->createFromFormat('d.m.Y', $dates[0]);

        if (empty($filterDateStart)) {
            throw new RuntimeException('Некорректная дата начала в ' . $filter->name);
        }

        if ($filter->code === 'u.birth') {
            $conditions[] = $this->db->placehold(
                "STR_TO_DATE($filter->code, '%d.%m.%Y') >= ?", $filterDateStart->format('Y-m-d')
            );
        } else {
            $conditions[] = $this->db->placehold(
                "$filter->code >= ?", $filterDateStart->format('Y-m-d 00:00:00')
            );
        }

        $filterDateEnd = (new DateTimeImmutable())->createFromFormat('d.m.Y', $dates[1]);

        if (empty($filterDateEnd)) {
            throw new RuntimeException('Некорректная дата завершения в ' . $filter->name);
        }

        if ($filter->code === 'u.birth') {
            $conditions[] = $this->db->placehold(
                "STR_TO_DATE($filter->code, '%d.%m.%Y') <= ?", $filterDateEnd->format('Y-m-d')
            );
        } else {
            $conditions[] = $this->db->placehold(
                "$filter->code <= ?", $filterDateEnd->format('Y-m-d 23:59:59')
            );
        }
    }

    /**
     * Добавляет условие типа Число в WHERE для получения заявок
     *
     * @param stdClass $filter
     * @param array $conditions
     * @return void
     */
    private function addNumberRangeCondition(stdClass $filter, array &$conditions): void
    {
        // Число от
        if ($filter->type === 'numberrange' && $filter->show_label) {
            $conditions[] = $this->db->placehold("$filter->code >= ?", (int)$filter->value);
            return;
        }

        // Число до
        if ($filter->type === 'numberrange' && !$filter->show_label) {
            $code = str_replace('_to', '', $filter->code);
            $conditions[] = $this->db->placehold("$code <= ?", (int)$filter->value);
        }
    }

    /**
     * Добавляет условие из Select в WHERE для получения заявок
     *
     * @param stdClass $filter
     * @param array $conditions
     * @return void
     */
    private function addSelectCondition(stdClass $filter, array &$conditions): void
    {
        foreach ($filter->value as $option) {
            if ($option->selected && $option->value !== '-') {
                if ($option->value === 'NULL') {
                    $conditions[] = $this->db->placehold("$filter->code IS NULL");
                } else {
                    $conditions[] = $this->db->placehold("$filter->code = ?", $option->value);
                }
                break;
            }
        }
    }

    /**
     * Добавляет условие из Select с множественным выбором в WHERE для получения заявок
     *
     * @param stdClass $filter
     * @param array $conditions
     * @return void
     */
    private function addMultiSelectCondition(stdClass $filter, array &$conditions): void
    {
        $selectedOptionsValue = [];
        foreach ($filter->value as $option) {
            if ($option->selected && $option->value !== '-') {

                if ($filter->code === 'u.Regregion' || $filter->code === 'u.Faktregion') {
                    $option = clone $option;
                    $option->value = trim(preg_replace('/[Аа]втономная область|[Аа]втономный округ$|[Оо]бласть|[Кк]рай|[Рр]еспублика/ui', '', $option->value));
                }

                $selectedOptionsValue[] = $option->value;
            }
        }

        if (!empty($selectedOptionsValue)) {

            // Для регионов нужно через OR LIKE, а не WHERE IN(), так как есть пользователи, у которых регион указан,
            // например, не Алтайский, а Алтайский край
            if ($filter->code === 'u.Regregion') {

                $regions = array_map(fn(string $region) => "u.Regregion LIKE '%$region%'", $selectedOptionsValue);
                $conditions[] = $this->db->placehold('(' . implode(' OR ', $regions) . ')');

            } elseif ($filter->code === 'u.Faktregion') {

                $regions = array_map(fn(string $region) => "u.Faktregion LIKE '%$region%'", $selectedOptionsValue);
                $conditions[] = $this->db->placehold('(' . implode(' OR ', $regions) . ')');

            } else {
                $conditions[] = $this->db->placehold("$filter->code IN (?@)", $selectedOptionsValue);
            }
        }
    }

    /**
     *  Добавляет остальные условия в WHERE для получения заявок
     *
     * @param stdClass $filter
     * @param array $conditions
     * @return void
     */
    private function addOtherCondition(stdClass $filter, array &$conditions): void
    {
        $conditions[] = $this->db->placehold("$filter->code = ?", $filter->value);
    }

    /**
     * Получить общее кол-во страниц для пагинации
     *
     * @param array $orders
     * @return int
     */
    private function getTotalPagesAmount(array $orders): int
    {
        return (int)ceil(count($orders) / self::LIMIT_PER_PAGE);
    }
}