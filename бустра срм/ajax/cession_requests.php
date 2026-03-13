<?php
session_start();
chdir('..');

require 'api/Simpla.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/RevocationMailer.php';

class CessionRequestsHandler extends Simpla
{
    private const CONTRACT_NUMBER_RE = '/^(?i:[A-ZА-ЯЁ])\d{2}-\d{7}$/u';
    private const SHKD_NUMBER_RE = '/^(?i:[A-ZА-ЯЁ]{2})[0-9]{2}-[0-9]{7}$/u';
    private const FIO_BIRTH_RE = '/^[А-ЯЁ][а-яё]+\s[А-ЯЁ][а-яё]+\s[А-ЯЁ][а-яё]+\s\d{2}\.\d{2}\.\d{4}$/u';

    public function run(): void
    {
        if (!isset($_SESSION['manager_id'])) {
            return;
        }

        $action = $this->request->get('action');

        switch ($action) {
            case 'update_row_value':
                $this->update_row_value();
                break;
            case 'download':
                $this->download();
                break;
            case 'add_manual_request':
                $this->add_manual_request();
                break;
            case 'delete_manual_request':
                $this->delete_manual_request();
                break;
            default:
                $this->request->json_output(['success' => false, 'error' => 'Неизвестное действие']);
        }
    }

    /**
     * Массовое/одиночное обновление поля заявки.
     */
    public function update_row_value(): void
    {
        $allowed_fields = [
            'execution_status', 'importance', 'comments', 'counterparty', 'transfer_date',
            'client_replace_status', 'extra_actions', 'lawyer_comment', 'contract_form', 'cedent', 'email'
        ];

        $field = $this->request->post('field');
        $value = $this->request->post('value');
        $isBulk = (bool)$this->request->post('bulk');

        if (!in_array($field, $allowed_fields, true)) {
            $this->request->json_output(['success' => false, 'error' => 'Недопустимое поле']);
            return;
        }

        if ($isBulk) {
            $ids = array_values(array_filter(array_map('intval', (array)$this->request->post('ids'))));
            if (!$ids) {
                $this->request->json_output(['success' => false, 'error' => 'Пустой список заявок']);
                return;
            }

            $this->db->query("UPDATE cession_requests SET ?% WHERE id IN (?@)", [$field => $value], $ids);

            if (!($field === 'execution_status' && $value === 'Отозвано')) {
                $this->request->json_output(['success' => true]);
                return;
            }

            $this->db->query("SELECT * FROM cession_requests WHERE id IN (?@)", $ids);
            $rows = $this->db->results();
            if (!$rows) {
                $this->request->json_output(['success' => false, 'error' => 'Заявки не найдены']);
                return;
            }

            $responses = [];
            foreach ($rows as $row) {
                $responses[] = $this->send_revocation_email($row, false);
            }
            $this->request->json_output(['success' => true, 'details' => $responses]);
            return;
        }

        $id = (int)$this->request->post('id');
        if ($id <= 0) {
            $this->request->json_output(['success' => false, 'error' => 'Неверный ID']);
            return;
        }

        $this->db->query("UPDATE cession_requests SET ?% WHERE id = ?", [$field => $value], $id);

        if ($field === 'execution_status' && $value === 'Отозвано') {
            $this->db->query("SELECT * FROM cession_requests WHERE id = ?", $id);
            $row = $this->db->result();
            if (!$row) {
                $this->request->json_output(['success' => false, 'error' => 'Заявка не найдена']);
                return;
            }

            $this->send_revocation_email($row);
            return;
        }

        $this->request->json_output(['success' => true]);
    }

    /**
     * Выгрузка в XLSX с применением фильтров.
     */
    public function download(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $headers = [
            'ID' => 'integer',
            'Дата заявки' => 'string',
            'ФИО + Дата рождения' => 'string',
            'Номер договора' => 'string',
            'Номер ШКД' => 'string',
            'Дата договора' => 'string',
            'Форма договора' => 'string',
            'Цедент' => 'string',
            'Контрагент' => 'string',
            'Дата передачи' => 'string',
            'Важность' => 'string',
            'Статус' => 'string',
            'Комментарий' => 'string',
            'Доп. действия' => 'string',
            'Замена клиента' => 'string',
            'Email' => 'string'
        ];

        $searchFields = [
            'full_name_with_birth',
            'contract_number',
            'shkd_number',
            'contract_date',
            'request_date',
            'email',
            'contract_form',
            'cedent',
            'counterparty',
            'transfer_date',
            'importance',
            'execution_status',
            'implementation_status',
            'comments',
            'extra_actions',
            'client_replace_status'
        ];

        $conditions = [];
        $params = [];

        foreach ($searchFields as $field) {
            $value = preg_replace('/\s+/u', ' ', trim($this->request->get($field)));
            if ($value !== '') {
                $conditions[] = "LOWER(`$field`) LIKE LOWER(?)";
                $params[] = '%' . $value . '%';
            }
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $this->db->query("SELECT * FROM cession_requests $where ORDER BY id", ...$params);
        $results = $this->db->results() ?: [];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Цессия', $headers);

        foreach ($results as $r) {
            $writer->writeSheetRow('Цессия', [
                $r->id,
                $r->request_date,
                $r->full_name_with_birth,
                $r->contract_number,
                $r->shkd_number,
                $r->contract_date,
                $r->contract_form,
                $r->cedent,
                $r->counterparty,
                $r->transfer_date,
                $r->importance,
                $r->execution_status,
                $r->comments,
                $r->extra_actions,
                $r->client_replace_status,
                $r->email
            ]);
        }

        $filename = 'cession_export_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    /**
     * Ручное добавление заявки (INSERT).
     */
    public function add_manual_request(): void
    {
        $required = ['request_date', 'full_name_with_birth', 'contract_number', 'shkd_number', 'contract_date'];
        foreach ($required as $field) {
            if (trim($this->request->post($field)) === '') {
                $this->request->json_output(['success' => false, 'error' => "Поле '{$field}' обязательно для заполнения."]);
                return;
            }
        }

        $contract_number = $this->request->post('contract_number');
        $shkd_number = $this->request->post('shkd_number');
        $full_name_with_birth = trim($this->request->post('full_name_with_birth'));

        if (!preg_match(self::CONTRACT_NUMBER_RE, $contract_number)) {
            $this->request->json_output(['success' => false, 'error' => "Номер договора должен быть в формате A25-5555555"]);
            return;
        }

        if (!preg_match(self::SHKD_NUMBER_RE, $shkd_number)) {
            $this->request->json_output(['success' => false, 'error' => "Номер ШКД должен быть в формате AA25-5555555"]);
            return;
        }

        if (!preg_match(self::FIO_BIRTH_RE, $full_name_with_birth)) {
            $this->request->json_output(['success' => false, 'error' => "ФИО + Дата рождения должны быть в формате: Иванов Иван Иванович 01.01.1980"]);
            return;
        }

        $fields = [
            'request_date' => $this->request->post('request_date'),
            'full_name_with_birth' => $this->request->post('full_name_with_birth'),
            'contract_number' => $this->request->post('contract_number'),
            'shkd_number' => $this->request->post('shkd_number'),
            'contract_date' => $this->request->post('contract_date'),
            'contract_form' => $this->request->post('contract_form'),
            'cedent' => $this->request->post('cedent'),
            'counterparty' => $this->request->post('counterparty'),
            'transfer_date' => trim($this->request->post('transfer_date')) ?: null,
            'importance' => $this->request->post('importance'),
            'execution_status' => $this->request->post('execution_status'),
            'comments' => $this->request->post('comments'),
            'extra_actions' => $this->request->post('extra_actions'),
            'client_replace_status' => $this->request->post('client_replace_status'),
            'email' => $this->request->post('email'),
        ];

        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($fields as $col => $val) {
            $columns[] = "`{$col}`";
            if ($val === null) {
                $placeholders[] = "NULL";
            } else {
                $placeholders[] = "?";
                $values[] = $val;
            }
        }

        $columns[] = "`source`";
        $placeholders[] = "'manual'";

        $columns[] = "`created_at`";
        $placeholders[] = "NOW()";

        $sql = "INSERT INTO `cession_requests` (" . implode(", ", $columns) . ")
                VALUES (" . implode(", ", $placeholders) . ")";

        try {
            $this->db->query($sql, ...$values);
            $this->request->json_output(['success' => true]);
        } catch (Exception $e) {
            $this->request->json_output(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Удаление вручную добавленной заявки.
     */
    public function delete_manual_request(): void
    {
        $id = (int)$this->request->post('id');

        $this->db->query("SELECT source FROM cession_requests WHERE id = ?", $id);
        $row = $this->db->result();

        if (!$row) {
            $this->request->json_output(['success' => false, 'error' => 'Запись не найдена']);
            return;
        }

        if ($row->source !== 'manual') {
            $this->request->json_output(['success' => false, 'error' => 'Нельзя удалить автоматически добавленную запись']);
            return;
        }

        $this->db->query("DELETE FROM cession_requests WHERE id = ?", $id);
        $this->request->json_output(['success' => true]);
    }

    /**
     * Отправка писем, когда клиент отозван (статус «Отозвано»).
     */
    private function send_revocation_email($row, $outputResponse = true)
    {
        $smtpSettings = $this->settings->getApiKeys('smtp') ?? [];
        $mailer = new RevocationMailer($smtpSettings);

        $contractNumber = is_array($row) ? $row['contract_number'] : $row->contract_number;
        $info = $this->get1cLoanAssignmentInfo($contractNumber);

        if ($info) {
            if (is_object($row)) {
                $row->loan_sum = $info['loan_sum'] ?? 0;
                $row->total_debt = $info['total_debt'] ?? 0;
                $row->contract_date = $info['loan_date'] ?? $row->contract_date;
                $row->cession_number = $info['cession_number'] ?? '';
                $row->cession_date = $info['cession_date'] ?? '';
                $row->percent = $info['percent'] ?? '';
            } else {
                $row['loan_sum'] = $info['loan_sum'] ?? 0;
                $row['total_debt'] = $info['total_debt'] ?? 0;
                $row['contract_date'] = $info['loan_date'] ?? $row['contract_date'];
                $row['cession_number'] = $info['cession_number'] ?? '';
                $row['cession_date'] = $info['cession_date'] ?? '';
                $row['percent'] = $info['percent'] ?? '';
            }
        }

        $cedent = is_object($row) ? $row->cedent : $row['cedent'];
        $counterpartyName = is_object($row) ? $row->counterparty : $row['counterparty'];

        if (empty($counterpartyName) || !is_string($counterpartyName)) {
            error_log('counterpartyName is invalid: ' . print_r($counterpartyName, true));
            $counterpartyData = null;
        } else {
            $this->db->query("SELECT * FROM counterparties WHERE name = ?", $counterpartyName);
            $counterpartyData = $this->db->result();
        }

        if (is_object($row)) {
            $row->cedent_data = $cedent;
            $row->counterparty_data = $counterpartyData;
        } else {
            $row['cedent_data'] = $cedent;
            $row['counterparty_data'] = $counterpartyData;
        }

        $response = $mailer->send([$row]);

        if ($outputResponse) {
            $this->request->json_output($response);
        } else {
            return $response;
        }
    }

    private function get1cLoanAssignmentInfo(string $loanNumber): array
    {
        $payload = $this->soap->generateObject(['LoanNumber' => $loanNumber]);
        $resp = $this->soap->requestSoap($payload, 'WebSignal', 'GetLoanAssignmentInfo');

        if (!$resp) return [];
        if (isset($resp['response'])) $resp = $resp['response'];
        if (is_string($resp)) {
            $d = json_decode($resp, true);
            if (json_last_error() === JSON_ERROR_NONE) $resp = $d;
        }
        if (!is_array($resp)) return [];

        $formatDate = function ($iso) {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T/', $iso, $m)) {
                return "{$m[3]}.{$m[2]}.{$m[1]}";
            }
            return $iso;
        };

        return [
            'loan_number' => trim((string)($resp['НомерДоговораЗайма'] ?? '')),
            'loan_date' => $formatDate((string)($resp['ДатаДоговораЗайма'] ?? '')),
            'cession_number' => trim((string)($resp['НомерДоговораЦессии'] ?? '')),
            'cession_date' => $formatDate((string)($resp['ДатаДоговораЦессии'] ?? '')),
            'loan_sum' => (float)($resp['СуммаДопУслуги'] ?? 0),
            'total_debt' => (float)($resp['ОбщаяСуммаДопУслуги'] ?? 0),
            'paid_service_sum' => (float)($resp['СуммаОплатыДопУслуги'] ?? 0),
            'percent' => (float)($resp['ПроцентПродажи'] ?? 0),
        ];
    }
}

$handler = new CessionRequestsHandler();
$handler->run();
