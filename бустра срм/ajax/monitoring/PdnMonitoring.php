<?php

require_once '../../api/Simpla.php';

class PdnMonitoring extends Simpla
{
    public function run()
    {
        $stats = $this->calculatePdnStats();

        $this->response->json_output($stats);
    }

    /**
     * Получить все успешные записи расчёта ПДН за последний час
     *
     * @return array
     */
    private function getSuccessfulRecordsLastHour(): array
    {
        $query = $this->db->placehold("
            SELECT order_id, result
            FROM s_pdn_calculation
            WHERE success = 1
              AND date_create >= NOW() - INTERVAL 1 HOUR
              AND date_create <= NOW() - INTERVAL 2 MINUTE
        ");

        $this->db->query($query);

        $results = $this->db->results();

        return $results ?: [];
    }

    /**
     * Извлечь значение ПДН (pti_percent) из JSON результата
     *
     * @param string $resultJson
     * @return float|null
     */
    private function extractPdnFromResult(string $resultJson): ?float
    {
        $data = json_decode($resultJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        if (!isset($data['pti_percent'])) {
            return null;
        }

        return (float) $data['pti_percent'];
    }

    /**
     * Рассчитать медиану массива значений
     *
     * @param array $values
     * @return float|null
     */
    private function calculateMedian(array $values): ?float
    {
        if (empty($values)) {
            return null;
        }

        sort($values);
        $count = count($values);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    /**
     * Рассчитать статистику ПДН за последний час
     *
     * @return array
     */
    private function calculatePdnStats(): array
    {
        $records = $this->getSuccessfulRecordsLastHour();

        $pdnValues = [];
        $ordersPdn80Plus = [];

        foreach ($records as $record) {
            $pdn = $this->extractPdnFromResult($record->result);

            if ($pdn !== null) {
                $pdnValues[] = $pdn;

                if ($pdn >= 80) {
                    $ordersPdn80Plus[] = (int) $record->order_id;
                }
            }
        }

        if (empty($pdnValues)) {
            return [
                'count_pdn_80_plus' => 0,
                'orders_pdn_80_plus' => [],
                'max_pdn' => null,
                'median_pdn' => null,
                'total_records' => 0
            ];
        }

        $maxPdn = max($pdnValues);
        $medianPdn = $this->calculateMedian($pdnValues);

        return [
            'count_pdn_80_plus' => count($ordersPdn80Plus),
            'orders_pdn_80_plus' => $ordersPdn80Plus,
            'max_pdn' => round($maxPdn, 2),
            'median_pdn' => round($medianPdn, 2),
            'total_records' => count($pdnValues)
        ];
    }
}

(new PdnMonitoring())->run();