<?php

ini_set('memory_limit', '2048M');
error_reporting(0);
ini_set('display_errors', 'Off');

require_once 'View.php';

require_once __DIR__ . '/../lib/autoloader.php';

class PdnReportView extends View
{
    private const LIMIT_PER_PAGE = 250;

    /** @var string Экшен загрузки txt файла со списком заявок Аквариус для расчета ПДН */
    private const UPLOAD_ORDERS_LIST_AKVARIUS = 'upload_orders_list_akvarius';
    private const UPLOAD_ORDERS_LIST_AKVARIUS_2 = 'upload_orders_list_akvarius_2';

    /** @var string Экшен загрузки txt файла со списком заявок Финлаб для расчета ПДН */
    private const UPLOAD_ORDERS_LIST_FINLAB = 'upload_orders_list_finlab';

    /** @var string Экшен неуспешных расчетов ПДН по фильтру */
    private const DELETE_FAILED_PDN_CALCULATIONS_BY_FILTER = 'delete_failed_pdn_calculations_by_filter';

    public function fetch(): string
    {
        if (!in_array('pdn_report', $this->manager->permissions)) {
            return $this->design->fetch('403.tpl');
        }

        try {
            $action = $this->request->post('action');

            if (!empty($action)) {
                $this->doAction($action);
            }

            return $this->getList();

        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', '', ['error' => $error], 'pdn_report_view.txt');

            $this->design->assign('error', $e->getMessage());
            return $this->getList();
        }
    }

    /**
     * Выполнение экшена
     *
     * @param string $action
     * @return void
     */
    private function doAction(string $action): void
    {
        if ($action === self::UPLOAD_ORDERS_LIST_AKVARIUS) {
            $this->uploadOrdersListAkvarius();
        } else if ($action === self::UPLOAD_ORDERS_LIST_AKVARIUS_2) {
            $this->uploadOrdersListAkvarius2();
        } else if ($action === self::UPLOAD_ORDERS_LIST_FINLAB) {
            $this->uploadOrdersListFinlab();
        } else if ($action === self::DELETE_FAILED_PDN_CALCULATIONS_BY_FILTER) {
            $this->deleteFailedPdnCalculationsByFilter();
        } else {
            throw new RuntimeException('Некорректное действие');
        }
    }

    /**
     * Экшен сохранения файла со списком заявок для Аквариуса
     *
     * @return void
     */
    private function uploadOrdersListAkvarius(): void
    {
        $this->uploadOrdersList(self::UPLOAD_ORDERS_LIST_AKVARIUS, $this->config->root_dir . '/files/pdn_calculation/additional_orders.txt');
    }

    /**
     * Экшен сохранения файла со списком заявок для Аквариуса 2
     *
     * @return void
     */
    private function uploadOrdersListAkvarius2(): void
    {
        $this->uploadOrdersList(self::UPLOAD_ORDERS_LIST_AKVARIUS_2, $this->config->root_dir . '/files/pdn_calculation/additional_orders_2.txt');
    }

    /**
     * Экшен сохранения файла списка заявок
     *
     * @param string $fileInputKey
     * @param string $filePath
     * @return void
     */
    private function uploadOrdersList(string $fileInputKey, string $filePath): void
    {
        if (empty($fileInputKey) && empty($filePath)) {
            throw new RuntimeException('Не указан путь для сохранения файла');
        }

        if (empty($_FILES[$fileInputKey]['tmp_name'])) {
            throw new RuntimeException('Прикрепите файл');
        }

        $fileContent = file_get_contents($_FILES[$fileInputKey]['tmp_name']);

        if (empty($fileContent)) {
            throw new RuntimeException('Файл пустой');
        }

        $fileContent = json_decode($fileContent);

        if (empty($fileContent) && !is_array($fileContent)) {
            throw new RuntimeException('Не удалось получить json в файле');
        }

        $result = file_put_contents($filePath, json_encode($fileContent, JSON_PRETTY_PRINT));

        if (empty($result)) {
            throw new RuntimeException('Ошибка при сохранении файла! Не удалось сохранить файл');
        }
    }

    /**
     * Экшен сохранения файла со списком заявок для Финлаба
     *
     * @return void
     */
    private function uploadOrdersListFinlab(): void
    {
        $this->uploadOrdersList(self::UPLOAD_ORDERS_LIST_FINLAB, $this->config->root_dir . '/files/pdn_calculation/additional_orders_finlab.txt');
    }

    private function deleteFailedPdnCalculationsByFilter(): void
    {
        $filters = $this->getFilters();

        $where = $this->getSqlWhere($filters);

        if ($where === '1') {
            throw new RuntimeException('Необходимо указать фильтры');
        }

        $query = $this->db->placehold("
            DELETE
            FROM s_pdn_calculation p
            WHERE $where
            "
        );

        $this->db->query($query);
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

        $where = $this->getSqlWhere($filters);
        $items = $this->getItems($where, true);
        $this->design->assign('items', $items);

        $totalItemsAmount = $this->countItems($where);
        $this->design->assign('total_items_amount', $totalItemsAmount);

        $columns = $this->getColumns();
        $this->design->assign('columns', $columns);

        $this->design->assign('total_pages_num', $this->getTotalPagesAmount($totalItemsAmount));
        $this->design->assign('current_page_num', $this->request->get('page') ?? 1);

        return $this->design->fetch('pdn_report.tpl');
    }

    /**
     * Получить фильтры из crm
     *
     * @return object[]
     */
    public function getFilters(): array
    {
        $filters = [
            'p.id' => (object)[
                'name' => 'ID записей через запятую',
                'code' => 'p.id',
                'type' => 'text',
                'show_label' => true,
                'value' => null
            ],
            'p.order_id' => (object)[
                'name' => 'ID заявок через запятую',
                'code' => 'p.order_id',
                'type' => 'text',
                'show_label' => true,
                'value' => null
            ],
            'p.order_uid' => (object)[
                'name' => 'Номера заявок через запятую',
                'code' => 'p.order_uid',
                'type' => 'text',
                'show_label' => true,
                'value' => null
            ],
            'p.contract_number' => (object)[
                'name' => 'Номера договоров через запятую',
                'code' => 'p.contract_number',
                'type' => 'text',
                'show_label' => true,
                'value' => null
            ],
            'p.success' => (object)[
                'name' => 'Успешность',
                'code' => 'p.success',
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
                        'name' => 'Не успешно',
                        'selected' => false
                    ],
                    (object)[
                        'value' => '1',
                        'name' => 'Успешно',
                        'selected' => false
                    ],
                ]
            ],
            'p.result' => (object)[
                'name' => 'Результат через запятую',
                'code' => 'p.result',
                'type' => 'text',
                'show_label' => true,
                'value' => null
            ],
        ];

        $filtersInRequest = $this->request->post('filters');

        if ($filtersInRequest === null) {
            return $filters;
        }

        return $this->mergeFiltersWithFiltersInRequest($filters, $filtersInRequest);
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
                'name' => '#',
                'code' => 'counter',
            ],
            'id' => (object)[
                'name' => 'ID записи',
                'code' => 'id',
            ],
            'order_id' => (object)[
                'name' => 'ID заявки',
                'code' => 'order_id',
            ],
            'order_uid' => (object)[
                'name' => 'Номер заявки',
                'code' => 'order_uid',
            ],
            'contract_number' => (object)[
                'name' => 'Номер договора',
                'code' => 'contract_number',
            ],
            'date_create' => (object)[
                'name' => 'Дата создания',
                'code' => 'date_create',
            ],
            'success' => (object)[
                'name' => 'Успешность',
                'code' => 'success',
            ],
            'result' => (object)[
                'name' => 'Результат',
                'code' => 'result',
            ],
        ];
    }

    /**
     * Получить записи
     *
     * @param string $where
     * @param bool $withLimit
     * @return array
     */
    private function getItems(string $where, bool $withLimit = false): array
    {
        $items = $this->fetchItems($where, $withLimit);
        return $this->formatItems($items);
    }

    /**
     * Выполнение sql для получения записей
     *
     * @param string $where
     * @param bool $withLimit
     * @return array
     */
    private function fetchItems(string $where, bool $withLimit): array
    {
        $limit = $withLimit ? 'LIMIT ' . $this->getOffset() . ', ' . self::LIMIT_PER_PAGE : '';

        $query = $this->db->placehold("
            SELECT p.*
            FROM s_pdn_calculation p
            WHERE $where
            ORDER BY p.id DESC
            $limit
            "
        );

        $this->db->query($query);

        $items = $this->db->results();

        if (empty($items)) {
            return [];
        }

        return $items;
    }

    /**
     * Получить WHERE для sql для получения записей
     *
     * @param array $filters
     * @return string
     */
    private function getSqlWhere(array $filters): string
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

        return $conditions ? implode(' AND ', $conditions) : '1';
    }

    /**
     * @param array $items
     * @return array
     */
    private function formatItems(array $items): array
    {
        $i = 0;
        foreach ($items as $item) {
            if ($item->success === '0') {
                $item->success = 'Не успешно';
            } elseif ($item->success === '1') {
                $item->success = 'Успешно';
            }

            $item->counter = ++$i;
        }

        return $items;
    }

    private function getOffset(): int
    {
        $offset = 0;

        if (!empty($this->request->get('page'))) {
            $offset = ($this->request->get('page', 'integer') - 1) * self::LIMIT_PER_PAGE;
        }

        return $offset;
    }

    /**
     * Выполнение sql для получения кол-ва записей
     *
     * @param string $where
     * @return int
     */
    private function countItems(string $where): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(*) AS 'total_items_amount'
            FROM s_pdn_calculation p
            WHERE $where
            "
        );

        $this->db->query($query);

        $item = $this->db->result();

        if (empty($item) || empty((int)$item->total_items_amount)) {
            return 0;
        }

        return (int)$item->total_items_amount;
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

        $conditions[] = $this->db->placehold(
            "$filter->code >= ?", $filterDateStart->format('Y-m-d 00:00:00')
        );

        $filterDateEnd = (new DateTimeImmutable())->createFromFormat('d.m.Y', $dates[1]);

        $conditions[] = $this->db->placehold(
            "$filter->code <= ?", $filterDateEnd->format('Y-m-d 23:59:59')
        );
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
                $selectedOptionsValue[] = $option->value;
            }
        }

        if (!empty($selectedOptionsValue)) {

            $conditions[] = $this->db->placehold("$filter->code IN (?@)", $selectedOptionsValue);
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
        $values = explode(',', $filter->value);
        $values = array_map('trim', $values);
        $conditions[] = $this->db->placehold("$filter->code IN (?@)", $values);
    }

    /**
     * Получить общее кол-во страниц для пагинации
     *
     * @param int $totalItemsAmount
     * @return int
     */
    private function getTotalPagesAmount(int $totalItemsAmount): int
    {
        return (int)ceil($totalItemsAmount / self::LIMIT_PER_PAGE);
    }
}
