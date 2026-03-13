<?php

ini_set('memory_limit', '2048M');
error_reporting(0);
ini_set('display_errors', 'of');

require_once 'View.php';

class PdnEditView extends View
{
    private static array $columns = [
        'order_id' => 'ID заявки',
        'order_uid' => 'Номер заявки',
        'contract_number' => 'Номер договора',
        'date_create' => 'Дата создания',
        'success' => 'Успешность',
        'income_base' => 'Доход',
        'income_rosstat' => 'Доход по Росстату',
        'pdn_calculation_type' => 'Тип ПДН',
        'pdn' => 'ПДН',
        'amount' => 'Сумма',
        'issuance_date' => 'Дата выдачи',
        'smp' => 'СМП',
        'smp1' => 'СМП1',
        'smp2' => 'СМП2',
        'smd' => 'СМД',
        'fakt_address' => 'Адрес',
    ];

    private static array $sortedColumns = [
        'date_create',
        'issuance_date',
    ];

    private const LIMIT = 50;

    private string $notification = '';

    public function fetch()
    {
        $filters = $this->request->get('filters') ?? [];
        $appliedDefaultFilters = [];

        if (empty($filters)) {
            $appliedDefaultFilters = $this->defaultFilters();
            $filters = $this->defaultFilters();
        }

        $this->processingEvents($this->request->get('action', 'string'), $filters);

        $pdnCalculations = $this->getPdnCalculations($filters);
        $pdnCalculationsPrepared = $this->prepareData($pdnCalculations);
        $changedColumns = $this->getChangedColumns();
        $identificationColumns = $this->getIdentificationColumns();
        $totalItems = $this->totalRows($filters);

        $items_per_page = $this->request->get('limit') ?? self::LIMIT;
        $pages_num = ceil($totalItems / $items_per_page);
        $current_page = max(1, $this->request->get('page', 'integer'));

        $this->design->assign('columns', self::$columns);
        $this->design->assign('items', $pdnCalculationsPrepared);
        $this->design->assign('pdnCalculationTypes', $this->pdnCalculation::$pdnCalculationTypes);
        $this->design->assign('sortedColumns', self::$sortedColumns);
        $this->design->assign('changedColumns', $changedColumns);
        $this->design->assign('identificationColumns', $identificationColumns);
        $this->design->assign('notification', $this->notification);
        $this->design->assign('totalRows', $totalItems);
        $this->design->assign('appliedDefaultFilters', $appliedDefaultFilters);

        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('current_page_num', $current_page);

        return $this->design->fetch('pdn_edit.tpl');
    }

    private function defaultFilters(): array
    {
        return [
            'sortedColumn' => 'date_create',
            'sortedDesc' => true,
        ];
    }

    private function getIdentificationColumns(): array
    {
        return [
            'order_id' => [
                'type' => 'number',
            ],
            'order_uid' => [
                'type' => 'string',
            ],
            'contract_number' => [
                'type' => 'string',
            ],
        ];
    }

    private function getChangedColumns(): array
    {
        return [
            'pdn_calculation_type' => [
                'type' => 'select',
                'options' => $this->pdnCalculation::$pdnCalculationTypes,
            ],
            'pdn' => [
                'type' => 'number',
            ],
            'smp' => [
                'type' => 'number',
            ],
            'smp1' => [
                'type' => 'number',
            ],
            'smp2' => [
                'type' => 'number',
            ],
            'smd' => [
                'type' => 'number',
            ],
            'income_base' => [
                'type' => 'number',
            ],
        ];
    }

    private function processingEvents(string $action, array $filters = []): void
    {
        if (!$this->request->method('POST')) {
            return;
        }

        if ($action === 'updateAll') {
            $this->processingUpdateAll($filters);
            return;
        }

        if ($action === 'updateSingle') {
            $this->processingUpdateSingle();
            return;
        }

        if ($action === 'updateFromCsv') {
            $this->processingUpdateFromCsv();
        }
    }

    private function processingUpdateFromCsv(): void
    {
        $fileTmp = $this->request->files('updateFromCsvFile', 'tmp_name');
        if (empty($fileTmp)) {
            $this->notification = 'Не обновлено!!! Не удалось загрузить файл!';
            return;
        }

        $importFromCSV = $this->request->post('importFromCSV');
        if (empty($importFromCSV['identification']['local']) || empty($importFromCSV['identification']['external'])) {
            $this->notification = 'Не обновлено!!! Необходимо указать поля для идентификации';
            return;
        }

        $identificationColumns = $this->getIdentificationColumns();
        if (!isset($identificationColumns[$importFromCSV['identification']['local']])) {
            $this->notification = 'Не обновлено!!! Не корректный ключ идентификации';
            return;
        }

        $changedColumns = $this->getChangedColumns();
        $updates = array_intersect_key(array_filter($importFromCSV['update']), $changedColumns);
        if (empty($updates)) {
            $this->notification = 'Не обновлено!!! Вы не сопоставили не одного поля';
            return;
        }

        $inputHandle = fopen($fileTmp, 'rb');
        $keys = [];
        $rowNumber = 0;

        $this->db->begin_transaction();

        while (($row = fgetcsv($inputHandle, 0, ";", ',', '\\')) !== false) {
            $rowNumber++;

            if ($rowNumber === 1) {
                $row = array_map([$this, 'prepareExternalData'], $row);
                $keys = array_flip($row);

                continue;
            }

            if (count($row) === 0) {
                continue;
            }

            $set = [];
            $values = [];

            foreach ($updates as $localKey => $externalKey) {
                $externalKey = $this->prepareExternalData($externalKey);
                $value = $this->prepareExternalData($row[$keys[$externalKey]]);

                $set[] = "`$localKey` = ?";
                $values[] = $value;
            }

            $set = implode(', ', $set);
            $identificationKey = $keys[$this->prepareExternalData($importFromCSV['identification']['external'])];

            $localKey = $importFromCSV['identification']['local'];
            $values[] = $this->prepareExternalData($row[$identificationKey]);

            $sql = "UPDATE `pdn_calculation` SET $set WHERE `$localKey` = ?;";
            $query = $this->db->placehold($sql, ...$values);

            $updated = $this->db->query($query);

            if (!$updated) {
                $this->notification = 'Не обновлено!!! Не удалось выполнить ' . $query;
                $this->db->rollback();
                return;
            }
        }

        $this->notification = 'Успешно обновлено!';
        $this->db->commit();
    }

    protected function prepareExternalData($value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        return trim($value, '"');
    }

    private function processingUpdateSingle(): void
    {
        $orderId = $this->request->post('orderId');
        $update = $this->request->post('update');

        if ($orderId && $update) {
            $newData = [];

            foreach ($this->getChangedColumns() as $code => $column) {
                if (isset($update[$code])) {
                    $newData[$code] = $update[$code];
                }
            }

            if ($this->updatePdnCalculation($orderId, $newData)) {
                $this->notification = 'Успешно обновлено';
            } else {
                $this->notification = 'Ошибка! Не обновлено!';
            }
        }
    }

    private function processingUpdateAll(array $filters): void
    {
        $updatedFieldsAndValues = $this->request->post('update_all');
        $updatedFields = [];

        if (!empty($updatedFieldsAndValues)) {
            $changedColumns = $this->getChangedColumns();

            foreach ($updatedFieldsAndValues as $fieldCode => $field) {
                if (!isset($changedColumns[$fieldCode])) {
                    continue;
                }

                if (isset($field['active']) && filter_var($field['active'], FILTER_VALIDATE_BOOLEAN)) {
                    $updatedFields[$fieldCode] = $field['value'];
                }
            }

            if (!empty($updatedFields)) {
                [$where, $whereValues] = $this->buildWhere($filters);

                if (empty($whereValues)) {
                    $this->notification = 'Нельзя обновить все записи';
                    return;
                }

                if ($this->updateManyPdnCalculation($where, $whereValues, $updatedFields)) {
                    $this->notification = 'Успешно обновлено';
                } else {
                    $this->notification = 'Ошибка! Не обновлено!';
                }
            }
        }
    }

    private function getPrepareFunction(string $column): ?Closure
    {
        return [
            'success' => static fn($value) => $value ? 'Успешно' : 'Не успешно',
            'pdn_calculation_type' => fn($value) => (array_flip($this->pdnCalculation::$pdnCalculationTypes)[$value] ?? ''),
        ][$column] ?? null;
    }

    private function prepareData(array $data): array
    {
        foreach ($data as &$item) {
            foreach ($item as $key => $value) {
                $fn = $this->getPrepareFunction($key);

                if (!is_null($fn)) {
                    $item->{$key} = $fn($value);
                }
            }
        }

        return $data;
    }

    private function updateManyPdnCalculation(string $where, array $whereValues, array $data)
    {
        $query = $this->db->placehold("UPDATE pdn_calculation SET ?% $where", $data, ...$whereValues);
        return $this->db->query($query);
    }

    private function updatePdnCalculation(int $orderId, array $data)
    {
        $query = $this->db->placehold("UPDATE pdn_calculation SET ?% WHERE order_id = ?", $data, $orderId);
        return $this->db->query($query);
    }

    private function getPdnCalculations(array $filters = []): array
    {
        $columns = implode(',', array_keys(self::$columns));
        $limit = $this->request->get('limit') ?? self::LIMIT;
        $page = $this->request->get('page') ?? 1;
        $offset = ($page - 1) * $limit;
        [$where, $values] = $this->buildWhere($filters);
        $orderBy = '';

        if (!empty($filters['sortedColumn'])) {
            $orderBy = 'ORDER BY ' . $filters['sortedColumn'] . ' ' . ($filters['sortedDesc'] ? 'DESC' : 'ASC');
        }

        $query = <<<SQL
                SELECT $columns
                FROM pdn_calculation
                $where
                $orderBy
                LIMIT $limit OFFSET $offset
        SQL;

        $query = $this->db->placehold($query, ...$values);
        $this->db->query($query);
        return $this->db->results();
    }

    private function totalRows(array $filters = []): int
    {
        [$where, $values] = $this->buildWhere($filters);
        $query = $this->db->placehold("SELECT COUNT(*) as totalRows FROM pdn_calculation $where", ...$values);
        $this->db->query($query);

        return $this->db->result('totalRows');
    }

    private function buildWhere(array $filters): array
    {
        $where = 'WHERE 1=1';
        $values = [];

        if (!empty($filters['order_id'])) {
            $where .= ' AND `order_id` IN (?@)';
            $values[] = explode(',', $filters['order_id']);
        }

        if (!empty($filters['order_uid'])) {
            $where .= ' AND `order_uid` IN (?@)';
            $values[] = explode(',', $filters['order_uid']);
        }

        if (!empty($filters['contract_number'])) {
            $where .= ' AND `contract_number` IN (?@)';
            $values[] = explode(',', $filters['contract_number']);
        }

        if (isset($filters['success']) && $filters['success'] !== '') {
            $where .= ' AND `success` = ?';
            $values[] = $filters['success'];
        }

        if (!empty($filters['pdn_calculation_type'])) {
            $where .= ' AND `pdn_calculation_type` = ?';
            $values[] = $filters['pdn_calculation_type'];
        }

        if (!empty($filters['date_create_from'])) {
            $where .= ' AND `date_create` >= ?';
            $values[] = $filters['date_create_from'];
        }

        if (!empty($filters['date_create_to'])) {
            $where .= ' AND `date_create` <= ?';
            $values[] = $filters['date_create_to'];
        }

        return [$where, $values];
    }
}