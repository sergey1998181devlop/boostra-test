<?php

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once __DIR__ . '/../api/Simpla.php';

class CreateAgreementCopies extends Simpla
{
    public function run()
    {
        $today = date('Y-m-d');
        
        echo "[" . date('Y-m-d H:i:s') . "] Начинаем обработку договоренностей на дату: {$today}\n";

        $agreements = $this->tickets->getUnprocessedAgreements($today);
        $processedCount = 0;
        
        echo "[" . date('Y-m-d H:i:s') . "] Найдено договоренностей для обработки: " . count($agreements) . "\n";
        
        foreach ($agreements as $agreement) {
            try {
                $newTicketId = $this->tickets->createAgreementCopy($agreement);
                
                if ($newTicketId) {
                    $this->tickets->markAgreementAsProcessed($agreement->agreement_id);
                    
                    $processedCount++;
                    echo "[" . date('Y-m-d H:i:s') . "] Создана копия тикета #{$newTicketId} для договоренности #{$agreement->agreement_id}\n";
                    
                } else {
                    echo "[" . date('Y-m-d H:i:s') . "] ОШИБКА: Не удалось создать копию для договоренности #{$agreement->agreement_id}\n";
                }
                
            } catch (Exception $e) {
                echo "[" . date('Y-m-d H:i:s') . "] ОШИБКА при обработке договоренности #{$agreement->agreement_id}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Обработка завершена. Создано копий: {$processedCount}\n";
    }
}

$cron = new CreateAgreementCopies();
$cron->run();
