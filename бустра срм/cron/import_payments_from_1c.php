<?php
error_reporting(-1);
ini_set('max_execution_time', 600);

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Class ImportPaymentsFrom1C
 * Класс для импорта информации о платежах из 1С
 * в частности импорт по уплаченным процентам
 */
final class ImportPaymentsFrom1C extends Simpla {

    private int $limit = 500;

    /**
     * Запускаем импорт
     * @return void
     * @throws Exception
     */
    public function run()
    {
        do {
            $transactions = $this->getTransactions();
            $this->get1CDataByUid($transactions);
        } while (!empty($transactions));
    }

    /**
     * Получает необработанные транзакции для импорта данных из 1С
     * @return array|false
     */
    private function getTransactions()
    {
        $sql = "SELECT uid, id FROM s_transactions WHERE cron_import_completed = 0 AND status IN (?@) ORDER BY id ASC LIMIT ?";
        $query = $this->db->placehold($sql, $this->transactions::STATUSES_SUCCESS, $this->limit);
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Проверяет наличие записи в БД
     * @param string $loan_id
     * @param string $uid
     * @param string $payment_date
     * @return bool
     */
    private function hasTransaction(string $loan_id, string $uid, string $payment_date): bool
    {
        $sql = "SELECT EXISTS(SELECT * FROM s_user_payments_1c WHERE loan_id = ? AND uid = ? AND payment_date = ?) as r";
        $query = $this->db->placehold($sql, $loan_id, $uid, $payment_date);
        $this->db->query($query);

        return (bool)$this->db->result('r');
    }

    /**
     * Добавляет новую запись из 1С в БД
     * @param array $data
     * @return mixed
     */
    private function addPayment(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_user_payments_1c SET ?%", $data);
        return $this->db->query($query);
    }

    /**
     * Получает данные из 1С
     * @param array $data_uid
     * @return void
     * @throws Exception
     */
    private function get1CDataByUid(array $data_uid = [])
    {

        $uids = array_values(array_unique(array_column($data_uid, 'uid')));

        $data = [
            'Partner' => 'Boostra',
            'MassUID' => json_encode($uids),
        ];

        $object = $this->soap->generateObject($data);
        $response = $this->soap->requestSoap($object,'WebLK', 'PaymentPercents');
        $data_update = [
            'cron_import_completed' => 1,
        ];

        if (!empty($response['response'])) {
            foreach ($response['response'] as $item) {
                $data_insert = [
                    'uid' => trim($item['УИД']),
                    'percent_amount' => (int)$item['СуммаПроцент'],
                    'full_amount' => (int)$item['СуммаОбщая'],
                    'insurer_amount' => (int)$item['СуммаСтраховки'],
                    'id_1c' => trim((string)$item['id']),
                    'loan_id' => trim((string)$item['НомерЗайма']),
                    'payment_date' => (new DateTime($item['ДатаОплаты']))->format('Y-m-d H:i:s'),
                ];

                if (!$this->hasTransaction($data_insert['loan_id'], $data_insert['uid'], $data_insert['payment_date'])) {
                    $this->addPayment($data_insert);
                }
            }
        }

        if (empty($response['errors'])) {
            $update_ids = array_column($data_uid, 'id');
            $this->transactions->update_transactions($update_ids, $data_update);
        }
    }
}

(new ImportPaymentsFrom1C())->run();
