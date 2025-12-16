<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Класс создает документы ШКД
 */
class OverdueDocumentGeneratorCron extends Simpla
{
    private int $limit = 1000;
    private int $pauseSeconds = 3;

    public function run(): void
    {
        $offset = 0;
        $totalProcessed = 0;
        $batchCount = 0;
        
        do {
            echo "Загрузка batch #$batchCount с offset: $offset, limit: $this->limit\n";

            $contracts = $this->documents->get_overdue_penalty_credit_doctor_documents([
                'limit' => $this->limit, 'offset' => $offset
            ]);

            $count = count($contracts);
            echo "Найдено договоров: $count\n";

            if ($count === 0) {
                echo "Обработка завершена. Всего обработано: $totalProcessed договоров.\n";
                break;
            }

            foreach ($contracts as $contract) {
                $contractNumber = $contract->zaim_number;

                echo "Обработка договора {$contractNumber}...\n";

                $docId = $this->createDocument($contractNumber);

                if ($docId) {
                    echo "Документ создан для {$contractNumber} (ID: {$docId})\n";
                    $totalProcessed++;
                } else {
                    echo "Ошибка при создании документа для {$contractNumber}\n";
                }
            }

            $offset += $this->limit;
            $batchCount++;

            echo "Пауза {$this->pauseSeconds} сек...\n";
            sleep($this->pauseSeconds);

        } while ($count === $this->limit);
    }

    /**
     * Создает договор
     * @param $contractNumber
     * @return mixed
     */
    public function createDocument($contractNumber)
    {
        $contract = $this->contracts->get_contract_by_params(['number' => $contractNumber]);
        $user = $this->users->get_user((int) $contract->user_id);
        $asp = $this->authcodes->find_code($user->phone_mobile);

        $params = $this->docs->getArbitrationAgreementParams(
            $user, $contract->order_id, $asp, $user->last_lk_visit_time
        );

        $data = [
            'order_id' => $contract->order_id,
            'user_id' => $contract->user_id,
            'contract_number' => $contractNumber,
            'params' => $params,
            'type' => Documents::PENALTY_CREDIT_DOCTOR
        ];

        return $this->documents->create_document($data);
    }
}

$cron = new OverdueDocumentGeneratorCron();
$cron->run();
