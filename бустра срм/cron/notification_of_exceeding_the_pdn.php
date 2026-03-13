<?php
ini_set('display_errors', 'on');
error_reporting(1);

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2048M');

require_once dirname(__FILE__).'/../api/Simpla.php';

/**
 * Класс для генерации документа "Уведомление о повышенном риске невыполнения кредитных обязательств", который создается
 * при превышении ПДН в размере 50%
 */
class NotificationExceedingPdn extends Simpla
{
    private const MAX_EXECUTION_TIME = 3500;
    private const LOG_FILE = 'notification_of_exceeding_the_pdn.txt';

    /** @var int Максимальное кол-во заявок для обработки за 1 запуск крона */
    private const MAX_ORDERS_AMOUNT_TO_HANDLE = 10000;

    /**
     * @return void
     */
    public function run(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крона: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);

        $this->getCountOfClients();

        // Отбираем заявки с 3-го квартала 2024
        $dateFrom = '2024-07-01 00:00:00';

        // 1. Генерация документов
        $this->generateDocuments($executionStartTime, $dateFrom);

        // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
        if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
            $this->logging(__METHOD__, '', '',
                'Достигнута максимальная продолжительность работы крон. Время ' .
                date('Y-m-d H:i:s'), self::LOG_FILE);
            return;
        }

        // 2. Удаление ненужных документов
        $this->deleteUnnecessaryDocuments($executionStartTime, $dateFrom);

        // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
        if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
            $this->logging(__METHOD__, '', '',
                'Достигнута максимальная продолжительность работы крон. Время ' .
                date('Y-m-d H:i:s'), self::LOG_FILE);
            return;
        }

        // 3. ПДН для существующих документов
        $this->updatePdn($executionStartTime, $dateFrom);

        // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
        if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
            $this->logging(__METHOD__, '', '',
                'Достигнута максимальная продолжительность работы крон. Время ' .
                date('Y-m-d H:i:s'), self::LOG_FILE);
            return;
        }

        // 4. Дата получения для существующих документов
        $this->updateReceivingDate($executionStartTime, $dateFrom);

        $this->logging(__METHOD__, '', '', 'Крон завершен: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);
    }

    private function generateDocuments(float $executionStartTime, string $dateFrom)
    {
        $orders = $this->getOrdersForGeneratingDocument($dateFrom);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены для генерации документов', self::LOG_FILE);
            return;
        }

        foreach ($orders as $order) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон генерации документов. Время ' .
                    date('Y-m-d H:i:s'), self::LOG_FILE);
                break;
            }

            try {
                $issuanceDate = new DateTimeImmutable($order->issuance_date);
            } catch (Throwable $e) {
                $this->logging(__METHOD__, '', '',
                    'Некорректная дата выдачи займа при генерации документа ' . $order->document_id, self::LOG_FILE);
                continue;
            }

            $user = $this->users->get_user($order->user_id);

            if (empty($user)) {
                $this->logging(__METHOD__, '', ['order_id' => $order->id], ['error' => 'Пользователь не найден'], self::LOG_FILE);
                continue;
            }

            // На всякий случай повторно проверяем, что документ не был сгенерирован ранее
            if (empty($this->documents->get_documents(['order_id' => $order->id, 'type' => Documents::PDN_EXCESSED]))) {
                try {
                    $document_id = $this->documents->create_document([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'type' => Documents::PDN_EXCESSED,
                        'params' => $this->getExcessedPdnDocumentParams($user, $order->accept_sms, $order->pdn_nkbi_loan, $issuanceDate),
                        'organization_id' => $order->organization_id
                    ]);
                } catch (Throwable $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Код: ' . $e->getCode(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];
                    $this->logging(__METHOD__, '', 'Ошибка при генерации документа', ['error' => $error], self::LOG_FILE);
                    continue;
                }

                $this->logging(__METHOD__, '', 'Успешно сгенерирован документ',
                    ['order_id' => $order->id, 'document_id' => $document_id], self::LOG_FILE);
            }
        }
    }

    /**
     * Получаем заявки, для которых ранее не было сгенерировано "Уведомление о повышенном риске невыполнения кредитных обязательств"
     *
     * @param string $dateFrom
     * @return array|false|null
     */
    private function getOrdersForGeneratingDocument(string $dateFrom)
    {
        $query = $this->db->placehold("SELECT o.*, c.issuance_date, d.id as document_id
                                        FROM s_orders o
                                            INNER JOIN s_contracts c ON c.id = o.contract_id
                                            LEFT JOIN s_documents d ON o.id = d.order_id AND d.type = 'PDN_EXCESSED' 

                                        WHERE o.pdn_nkbi_loan > 50
                                        AND d.id IS NULL
                                        AND c.issuance_date >= ?
                                        LIMIT ?;", $dateFrom, self::MAX_ORDERS_AMOUNT_TO_HANDLE
        );

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * @param $user
     * @param int $sms_code
     * @param $pdn
     * @param DateTimeInterface $issuance_date
     * @return array
     */
    private function getExcessedPdnDocumentParams($user, int $sms_code, $pdn, DateTimeInterface $issuance_date): array
    {
        $passport = Helpers::splitPassportSerial($user->passport_serial);
        $passport_serial = $passport['serial'];
        $passport_number = $passport['number'];

        return [
            'full_name' => "{$user->lastname} {$user->firstname} {$user->patronymic}",
            'birth' => $user->birth,
            'passport_serial' => $passport_serial,
            'passport_number' => $passport_number,
            'passport_issued' => $user->passport_issued,
            'passport_date' => $user->passport_date,
            'regregion' => $user->Regregion,
            'regcity' => $user->Regcity,
            'regstreet' => $user->Regstreet,
            'reghousing' => $user->Reghousing,
            'regroom' => $user->Regroom,
            'sms' => $sms_code,
            'receiving_date' => $issuance_date->format('d.m.Y'),
            'pdn' => $pdn
        ];
    }

    private function deleteUnnecessaryDocuments(float $executionStartTime, string $dateFrom)
    {
        $orders = $this->getOrdersToDeleteUnnecessaryDocument($dateFrom);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены для удаления ненужных документов', self::LOG_FILE);
            return;
        }

        foreach ($orders as $order) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон удаления документов. Время ' .
                    date('Y-m-d H:i:s'), self::LOG_FILE);
                break;
            }

            if ($order->document_type === Documents::PDN_EXCESSED && (float)$order->pdn_nkbi_loan <= 50) {
                try {
                    $this->documents->delete_document($order->document_id);
                } catch (Throwable $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Код: ' . $e->getCode(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];
                    $this->logging(__METHOD__, '', 'Ошибка при удалении документа', ['order_id' => $order->id, 'document_id' => $order->document_id, 'error' => $error], self::LOG_FILE);
                    continue;
                }

                $this->logging(__METHOD__, '', 'Успешно удален документ',
                    ['order_id' => $order->id, 'document_id' => $order->document_id], self::LOG_FILE);
            }
        }
    }

    /**
     * Получаем заявки Аквариус, для которых был сгенерирован документ "Уведомление о повышенном риске невыполнения кредитных обязательств",
     * но у которых ПДН меньше или равен 50% и поэтому документ не нужен (удаляем)
     *
     * @param string $dateFrom
     * @return array|false|null
     */
    private function getOrdersToDeleteUnnecessaryDocument(string $dateFrom)
    {
        $query = $this->db->placehold("SELECT o.id, c.issuance_date, o.pdn_nkbi_loan, d.id AS document_id, d.type AS document_type
            FROM s_orders o
                     INNER JOIN s_contracts c ON c.id = o.contract_id
                     INNER JOIN s_documents d ON d.order_id = o.id AND d.type = 'PDN_EXCESSED'
            WHERE o.pdn_nkbi_loan <= 50
              AND d.id IS NOT NULL
              AND c.issuance_date >= ?
              AND o.organization_id = 6
            LIMIT ?;", $dateFrom, self::MAX_ORDERS_AMOUNT_TO_HANDLE
        );

        $this->db->query($query);
        return $this->db->results();
    }

    private function updatePdn(float $executionStartTime, string $dateFrom)
    {
        $orders = $this->getOrdersForPdn($dateFrom);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены для ПДН', self::LOG_FILE);
            return;
        }

        foreach ($orders as $order) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон ПДН. Время ' .
                    date('Y-m-d H:i:s'), self::LOG_FILE);
                break;
            }

            if (!is_numeric($order->pdn_in_document)) {
                $this->logging(__METHOD__, '', '',
                    'Некорректно значение ПДН в документе ' . $order->document_id, self::LOG_FILE);
                continue;
            }

            $documentParams = unserialize($order->document_params);

            if ($order->document_type === Documents::PDN_EXCESSED && (float)$order->pdn_nkbi_loan !== (float)$documentParams['pdn']) {
                try {
                    $documentParams['pdn'] = (float)$order->pdn_nkbi_loan;
                    $this->documents->update_document($order->document_id, ['params' => $documentParams]);
                } catch (Throwable $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Код: ' . $e->getCode(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];
                    $this->logging(__METHOD__, '', 'Ошибка ПДН для документа', ['order_id' => $order->id, 'document_id' => $order->document_id, 'error' => $error], self::LOG_FILE);
                    continue;
                }

                $this->logging(__METHOD__, '', 'Успешный ПДН для документа',
                    ['order_id' => $order->id, 'document_id' => $order->document_id], self::LOG_FILE);
            }
        }
    }

    /**
     * Получаем заявки Аквариус с некорректным значением ПДН в "Уведомление о повышенном риске невыполнения кредитных обязательств"
     *
     * @param string $dateFrom
     * @return array|false|null
     */
    private function getOrdersForPdn(string $dateFrom)
    {
        // REGEXP_REPLACE(d.params, '.*pdn.*:(\"?)(.*)([\";]).*', '$2') - парсим сериализованную строку d.params,
        // чтобы получить значение ПДН в документе. Заменяем d.params на $3 (значение ПДН)
        // В WHERE сравниваем его с o.pdn_nkbi_loan
        $query = "SELECT o.id,
                   c.issuance_date,
                   d.id AS document_id,
                   CONVERT(o.pdn_nkbi_loan, FLOAT) AS pdn_nkbi_loan,
                   CONVERT(REGEXP_REPLACE(d.params, '.*pdn.*:(\"?)(.*)([\";]).*', '$2'), FLOAT) AS pdn_in_document,
                   d.params AS document_params,
                   d.type AS document_type
            FROM s_documents d
                     INNER JOIN s_orders o ON d.order_id = o.id
                     INNER JOIN s_contracts c ON c.id = o.contract_id
            WHERE d.type = 'PDN_EXCESSED'
              AND c.issuance_date >= '$dateFrom'
              AND o.organization_id = 6
              AND o.pdn_nkbi_loan > 50
              AND CONVERT(REGEXP_REPLACE(d.params, '.*pdn.*:(\"?)(.*)(\"?).*', '$2'), FLOAT) != CONVERT(o.pdn_nkbi_loan, FLOAT)
            LIMIT " . self::MAX_ORDERS_AMOUNT_TO_HANDLE;

        $this->db->query($query);
        return $this->db->results();
    }

    private function updateReceivingDate(float $executionStartTime, string $dateFrom)
    {
        $orders = $this->getOrdersForReceivingDate($dateFrom);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', '', 'Заявки не найдены для даты получения', self::LOG_FILE);
            return;
        }

        foreach ($orders as $order) {

            // Завершаем, если прошел почти час, чтобы не мешал работе следующего крона
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон даты получения. Время ' .
                    date('Y-m-d H:i:s'), self::LOG_FILE);
                break;
            }

            try {
                $receivingDate = new DateTimeImmutable($order->receiving_date);
            } catch (Throwable $e) {
                $this->logging(__METHOD__, '', '',
                    'Некорректная дата получения в документе ' . $order->document_id, self::LOG_FILE);
                continue;
            }

            try {
                $issuanceDate = new DateTimeImmutable($order->issuance_date);
            } catch (Throwable $e) {
                $this->logging(__METHOD__, '', '',
                    'Некорректная дата выдачи займа при обработке документа ' . $order->document_id, self::LOG_FILE);
                continue;
            }

            $documentParams = unserialize($order->document_params);

            if ($order->document_type === Documents::PDN_EXCESSED && $receivingDate->format('Y-m-d') !== $issuanceDate->format('Y-m-d')) {
                try {
                    $documentParams['receiving_date'] = $issuanceDate->format('d.m.Y');
                    $this->documents->update_document($order->document_id, ['params' => $documentParams]);
                } catch (Throwable $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Код: ' . $e->getCode(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];
                    $this->logging(__METHOD__, '', 'Ошибка даты получения для документа', ['order_id' => $order->id, 'document_id' => $order->document_id, 'error' => $error], self::LOG_FILE);
                    continue;
                }

                $this->logging(__METHOD__, '', 'Успешно по дате получения для документа',
                    ['order_id' => $order->id, 'document_id' => $order->document_id], self::LOG_FILE);
            }
        }
    }

    /**
     * Получаем заявки Аквариус с некорректным значением ПДН в "Уведомление о повышенном риске невыполнения кредитных обязательств"
     *
     * @param string $dateFrom
     * @return array|false|null
     */
    private function getOrdersForReceivingDate(string $dateFrom)
    {
        // REGEXP_REPLACE(d.params, '.*receiving_date(.*?)(:\")(.*?)\".*', '$3') - парсим сериализованную строку d.params,
        // чтобы получить дату получения в документе. Заменяем d.params на $3 (значение даты получения)
        // В WHERE сравниваем его с c.issuance_date
        $query = "SELECT o.id,
                         c.issuance_date,
                         d.id AS document_id,
                         CONVERT(o.pdn_nkbi_loan, FLOAT) AS pdn_nkbi_loan,
                         REGEXP_REPLACE(d.params, '.*receiving_date(.*?)(:\")(.*?)\".*', '$3') AS receiving_date,
                         DATE_FORMAT(c.issuance_date, '%d.%m.%Y') as issuance_date_rus,
                         d.params AS document_params,
                         d.type AS document_type
                  FROM s_documents d
                      FORCE INDEX(order_id, type)
                           INNER JOIN s_orders o ON d.order_id = o.id
                           INNER JOIN s_contracts c ON c.id = o.contract_id
                  WHERE d.type = 'PDN_EXCESSED'
                    AND c.issuance_date >= '$dateFrom'
                    AND REGEXP_REPLACE(d.params, '.*receiving_date(.*?)(:\")(.*?)\".*', '$3') != DATE_FORMAT(c.issuance_date, '%d.%m.%Y')
            LIMIT " . self::MAX_ORDERS_AMOUNT_TO_HANDLE;

        $this->db->query($query);
        return $this->db->results();
    }

    private function getCountOfClients()
    {
        try {
            $this->db->query("START TRANSACTION");

            $count_of_client = $this->orders->getCountOfUniqueClients();
            $formatted_count = number_format($count_of_client, 0, '', ' ');

            $deleteQuery = $this->db->placehold("DELETE FROM __settings WHERE name = ?", "count_of_clients");
            $this->db->query($deleteQuery);

            $insertQuery = $this->db->placehold("INSERT INTO __settings(name, value) VALUES(?, ?)", "count_of_clients", $formatted_count);
            $this->db->query($insertQuery);

            $this->db->query("COMMIT");

            $this->logging(__METHOD__, '', '', 'Счетчик клиентов обновлен: ' . $formatted_count, self::LOG_FILE);
        } catch (Throwable $e) {
            $this->db->query("ROLLBACK");
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
            ];
            $this->logging(__METHOD__, '', 'Ошибка при обновлении счетчика клиентов', ['error' => $error], self::LOG_FILE);
        }
    }
}

$notificationExceedingPdn = new NotificationExceedingPdn();
$notificationExceedingPdn->run();