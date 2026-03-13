<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'Off');

chdir('..');
require_once dirname(__FILE__) . '/../api/Simpla.php';

class ShkdEmailReader extends Simpla
{
    private const DEFAULT_CONTRACT_FORM = 'Займ';
    private const DEFAULT_CEDENT = 'Алфавит';
    private const DEFAULT_IMPORTANCE = 'Не срочно';
    private const DEFAULT_EXECUTION_STATUS = 'Новый';

    private $imapConnection;

    /**
     * Регулярное выражение для разбора темы письма.
     * Ожидаемый формат темы:
     *   [Контрагент] [ФИО] [Дата рождения] [Номер договора] [Номер ШКД]
     *
     * Расшифровка групп:
     * - [1] Название контрагента — одно слово, состоящее из букв
     * - [2] ФИО — от 3 до 4 слов, каждое минимум из 2 букв, разделённые пробелами
     * - [3] Дата рождения — в формате dd.mm.yyyy или dd-mm-yyyy или dd/mm/yyyy
     * - [4] Номер договора — латинские/кириллические буквы + 2 цифры, тире, 6+ цифр (например: АА12-123456)
     * - [5] Номер ШКД — 2 буквы + 2 цифры, тире, 7 цифр (например: ИвАН12-1234567)
     *
     * Пример темы письма, подходящей под шаблон:
     *   Компания Иванов Иван Иванович 01.02.1990 АА12-123456 ИвАН12-1234567
     */
    private const SUBJECT_REGEX = '/^([\p{L}]+)\s+((?:[\p{L}]{2,}\s+){2,3}[\p{L}]{2,})\s+(\d{2}[.\-\/]\d{2}[.\-\/]\d{4})\s+([AАFLБ]{1,2}\d{2}[-–]\d{6,})\s+([\p{L}]{2}\d{2}[-–]\d{7})$/u';

    public function __construct()
    {
        parent::__construct();

        $this->imapConnection = imap_open(
            $this->config->imap_host,
            $this->config->imap_username,
            $this->config->imap_password
        );

        if (!$this->imapConnection) {
            $this->logging(__METHOD__, 'error', imap_last_error(), 'Ошибка подключения к IMAP', 'shkd_reader.log');
            return;
        }
    }

    public function run(): void
    {
        $this->logging(__METHOD__, 'info', null, 'Начало чтения писем', 'shkd_reader.log');

        $allEmails = imap_search($this->imapConnection, 'ALL') ?: [];

        /**
         * Вычитает 3ч. от текущего времени
         * получаем письма, полученные за последние 3ч.
         */
        $cutoff = time() - (3 * 60 * 60);
        $emailsFiltered = [];

        foreach ($allEmails as $emailId) {
            $header = imap_headerinfo($this->imapConnection, $emailId);
            if (!empty($header->udate) && $header->udate >= $cutoff) {
                $emailsFiltered[] = $emailId;
            }
        }

        $this->logging(__METHOD__, 'info', [
            'total_found' => count($allEmails),
            'filtered_for_processing' => count($emailsFiltered),
        ], 'Фильтрация писем за последние 3ч.', 'shkd_reader.log');

        foreach ($emailsFiltered as $emailId) {
            $this->processEmail($emailId);
        }

        imap_close($this->imapConnection);
    }

    private function processEmail(int $emailId): void
    {
        $header = imap_headerinfo($this->imapConnection, $emailId);
        if (!$header) {
            $this->logging(__METHOD__, 'error', [
                'emailId' => $emailId,
                'reason' => 'imap_headerinfo вернул false',
            ], 'Ошибка получения заголовка письма', 'shkd_reader.log');
            return;
        }

        $subject = isset($header->subject) ? mb_decode_mimeheader($header->subject) : '';
        $from = '';
        if (!empty($header->from)) {
            $parsedFrom = imap_rfc822_parse_adrlist($header->fromaddress, 'localhost');
            if (!empty($parsedFrom) && isset($parsedFrom[0]->mailbox, $parsedFrom[0]->host)) {
                $from = strtolower(trim($parsedFrom[0]->mailbox . '@' . $parsedFrom[0]->host));
            }
        }

        $requestDate = (new DateTime())->setTimestamp($header->udate)->format('Y-m-d H:i:s');

        $this->logging(__METHOD__, 'debug', [
            'emailId' => $emailId,
            'subject' => $subject,
        ], 'Получена тема письма для парсинга', 'shkd_reader.log');

        $parsed = $this->parseSubject($subject);
        if (!$parsed) {
            $this->logging(__METHOD__, 'info', [
                'emailId' => $emailId,
                'subject' => $subject,
                'expected_format' => 'Контрагент ФИО Дата Договора Номер Договора Номер ШКД',
                'regex' => self::SUBJECT_REGEX,
            ], 'Не удалось распарсить тему письма', 'shkd_reader.log');
            return;
        }

        $counterpartiesMap = $this->getCounterpartiesMap();
        if (empty($counterpartiesMap)) {
            $this->logging(__METHOD__, 'error', [
                'emailId' => $emailId,
            ], 'Список контрагентов пуст', 'shkd_reader.log');
            return;
        }

        $counterpartyName = mb_strtolower(trim($parsed['counterparty']));

        if (!isset($counterpartiesMap[$counterpartyName])) {
            $this->logging(__METHOD__, 'info', [
                'emailId' => $emailId,
                'counterparty' => $parsed['counterparty'],
                'error' => 'Контрагент не найден в системе'
            ], 'Контрагент не существует', 'shkd_reader.log');
            return;
        }

        $this->logging(__METHOD__, 'info', [
            'emailId' => $emailId,
            'subject' => $subject,
            'parsed' => $parsed,
            'from' => $from,
        ], 'Успешный парсинг темы письма и верификация контрагента/почты', 'shkd_reader.log');

        $contractNumber = $parsed['contract_number'];
        $contract = $this->contracts->get_contract_by_params(['number' => $contractNumber]);
        $contractDate = null;
        if (!empty($contract) && !empty($contract->create_date)) {
            $contractDate = date('Y-m-d', strtotime($contract->create_date));
        }

        try {
            $exists = $this->db->query("
                    SELECT id FROM cession_requests
                    WHERE contract_number = ?
                      AND shkd_number = ?
                      AND full_name_with_birth = ?
                      AND execution_status NOT IN ('Новый', 'Не отозвано')
                    LIMIT 1
                ",
                $parsed['contract_number'],
                $parsed['shkd_number'],
                $parsed['full_name_with_birth']
            );
        } catch (\Exception $e) {
            $this->logging(__METHOD__, 'error', [
                'emailId' => $emailId,
                'contract_number' => $parsed['contract_number'],
                'shkd_number' => $parsed['shkd_number'],
                'full_name_with_birth' => $parsed['full_name_with_birth'],
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'Ошибка при проверке дубликата контракта', 'shkd_reader.log');
            return;
        }

        if ($exists && $this->db->num_rows() > 0) {
            $this->logging(__METHOD__, 'info', [
                'emailId' => $emailId,
                'contract_number' => $contractNumber,
                'shkd_number' => $parsed['shkd_number'],
                'fio_birth' => $parsed['full_name_with_birth'],
                'execution_status' => 'NOT IN ("Новый", "Не отозван")'
            ], 'Контракт уже существует, пропускаем', 'shkd_reader.log');
            return;
        }

        $data = [
            'request_date'     => $requestDate,
            'full_name_with_birth' => $parsed['full_name_with_birth'],
            'contract_number'  => $contractNumber,
            'shkd_number'      => $parsed['shkd_number'],
            'contract_date'    => $contractDate,
            'email'            => $from,
            'counterparty'     => $counterpartyName,
            'contract_form'    => self::DEFAULT_CONTRACT_FORM,
            'cedent'           => self::DEFAULT_CEDENT,
            'importance'       => self::DEFAULT_IMPORTANCE,
            'execution_status' => self::DEFAULT_EXECUTION_STATUS,
        ];

        try {
            $this->logging(__METHOD__, 'info', [
                'emailId' => $emailId,
                'data_to_insert' => $data,
            ], 'Перед вставкой в базу данных', 'shkd_reader.log');

            $query = $this->db->placehold("
                INSERT INTO cession_requests SET
                    request_date = ?,
                    full_name_with_birth = ?,
                    contract_number = ?,
                    shkd_number = ?,
                    contract_date = ?,
                    email = ?,
                    counterparty = ?,
                    contract_form = ?,
                    cedent = ?,
                    importance = ?,
                    execution_status = ?,
                    source = 'auto',
                    created_at = NOW()
            ",
                $data['request_date'],
                $data['full_name_with_birth'],
                $data['contract_number'],
                $data['shkd_number'],
                $data['contract_date'],
                $data['email'],
                $data['counterparty'],
                $data['contract_form'],
                $data['cedent'],
                $data['importance'],
                $data['execution_status'],
            );

            $this->db->query($query);

            $this->logging(__METHOD__, 'info', [
                'emailId' => $emailId,
                'contract_number' => $contractNumber,
                'shkd_number' => $parsed['shkd_number'],
                'fio_birth' => $parsed['full_name_with_birth'],
            ], 'Успешно добавлено в базу данных', 'shkd_reader.log');

        } catch (\Exception $e) {
            $this->logging(__METHOD__, 'error', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ], 'Ошибка при выполнении запроса в БД', 'shkd_reader.log');
        }
    }

    private function parseSubject(string $subject): ?array
    {
        $subject = preg_replace('/[\x{00A0}\s]{2,}/u', ' ', trim($subject));

        if (stripos($subject, 'RE:') === 0 || stripos($subject, 'FWD:') === 0) {
            return null;
        }

        if (preg_match(
            self::SUBJECT_REGEX, $subject, $matches
        )) {
            $counterparty = trim($matches[1]);
            $fio = trim($matches[2]);
            $birthRaw = str_replace(['-', '/'], '.', $matches[3]);

            $date = DateTime::createFromFormat('d.m.Y', $birthRaw);
            if (!$date) {
                return null;
            }

            $birthFormatted = $date->format('Y-m-d');

            return [
                'counterparty' => $counterparty,
                'full_name_with_birth' => $fio . ' ' . $matches[3],
                'contract_number' => strtoupper(trim($matches[4])),
                'shkd_number' => strtoupper(trim($matches[5])),
                'birth_date' => $birthFormatted,
            ];
        }

        return null;
    }

    private function getCounterpartiesMap(): array
    {
        $map = [];

        $this->db->query("SELECT name FROM counterparties");
        $rows = $this->db->results();

        if (!is_array($rows) || !$rows) {
            return $map;
        }

        foreach ($rows as $row) {
            $name = mb_strtolower(trim((string)$row->name));
            $map[$name] = [];
        }

        return $map;
    }
}

$cron = new ShkdEmailReader();
$cron->run();