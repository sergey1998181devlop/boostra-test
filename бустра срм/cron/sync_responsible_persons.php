<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'Off');

require_once dirname(__FILE__).'/../api/Simpla.php';

class SyncResponsiblePersons extends Simpla
{
    public function run(): void
    {
        $responsiblePersons = $this->getResponsiblePersons();

        foreach ($responsiblePersons as $responsiblePerson) {
            $contractNumbers = $this->soap->getContractsByResponsibleCode($responsiblePerson->code);

            if (!empty($contractNumbers)) {
                $this->attachResponsibleToBulkContracts($contractNumbers, (int) $responsiblePerson->id);
            }
        }
    }

    private function attachResponsibleToBulkContracts(array $numbers, int $responsiblePersonId): void
    {
        $chunks = array_chunk($numbers, 500);

        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $query = "UPDATE __contracts SET responsible_person_id = ? WHERE number IN ($placeholders)";
            $this->db->query($query, $responsiblePersonId, ...$chunk);
        }
    }

    private function getResponsiblePersons()
    {
        $this->db->query('SELECT id, code FROM __responsible_persons WHERE is_sync_available = TRUE');
        return $this->db->results();
    }
}

$cron = new SyncResponsiblePersons();
$cron->run();