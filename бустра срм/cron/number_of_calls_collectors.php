<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';


class NumberOfCallsCollectors extends Simpla
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run(): void
    {
        $collectors = $this->voximplant->getCollectorsData();

        if (!isset($collectors->result->stat) || empty($collectors->result->stat)) {
            $this->logError('No data found from API.');
            return;
        }

        $statArray = (array) $collectors->result->stat;
        $collectorData = [];

        foreach ($statArray as $collector) {
            $collectorId = $collector->last24h->user_id ?? null;
            $dateStart   = $collector->last24h->from_date ?? null;
            $dateEnd     = $collector->last24h->to_date ?? null;
            $callsCount  = $collector->last24h->metrics->total_direct_outbound_calls ?? 0;
            $successCallsCount = $collector->last24h->metrics->total_direct_outbound_successful_calls ?? 0;

            if ($this->isValidCollectorData($collectorId, $dateStart, $dateEnd)) {
                $collectorData[] = [
                    'collector_id' => $collectorId,
                    'date_start'   => $dateStart,
                    'date_end'     => $dateEnd,
                    'calls_count'  => $callsCount,
                    'success_calls_count' => $successCallsCount
                ];
            }
        }

        if (!empty($collectorData)) {
            $this->insertCollectorsData($collectorData);
        } else {
            $this->logError('No valid collector data to insert.');
        }
    }

    private function isValidCollectorData($collectorId, $dateStart, $dateEnd): bool
    {
        return !empty($collectorId) && !empty($dateStart) && !empty($dateEnd);
    }

    private function insertCollectorsData(array $collectors): void
    {
        $values = [];
        foreach ($collectors as $collector) {
            $values[] = $this->db->placehold("(?, ?, ?, ?, ?)",
                $collector['collector_id'],
                $collector['date_start'],
                $collector['date_end'],
                $collector['calls_count'],
                $collector['success_calls_count']
            );
        }
        if (!empty($values)) {
            $query = "INSERT INTO collector_calls (collector_id, date_start, date_end, calls_count, success_calls_count) VALUES " . implode(", ", $values);
            $this->db->query($query);
        }
    }

    private function logError(string $message): void
    {
        $this->logging(__METHOD__, '', '', ['error' => $message], 'number_of_calls_collectors.txt');
    }
}

new NumberOfCallsCollectors();
