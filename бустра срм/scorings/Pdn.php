<?php

require_once __DIR__ . '/../lib/autoloader.php';

class Pdn extends Simpla
{
    /**
     * @param int $scoring_id
     */
    public function run_scoring(int $scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);

        if (!$scoring) {
            return null;
        }

        $order = $this->orders->get_order((int)$scoring->order_id);

        if (!$order) {
            $update = [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Заявка не найдена'
            ];
        } else {

            $site_id = $this->organizations->get_site_organization($order->organization_id);
            if (!empty($site_id)) {
                $this->settings->setSiteId($site_id);
            }

            $update = $this->checkPdn($order);
        }

        $update['end_date'] = date('Y-m-d H:i:s');
        $this->scorings->update_scoring($scoring_id, $update);
    }

    /**
     * Стандартная проверка ПДН
     */
    private function checkPdn($order): array
    {
        $disablePdnCheck = (bool)$this->settings->disable_pdn_check;

        $pdn = $this->runPdn($order);

        $pdnScoringType = $this->scorings->get_type($this->scorings::TYPE_PDN);
        $maxPdnThreshold = (float)($pdnScoringType->params['max_pdn_threshold'] ?? 80);

        if ($pdn > $maxPdnThreshold && !$disablePdnCheck) {
            if ($pdnScoringType->negative_action == 'stop') {
                try {
                    $this->orders->rejectOrder($order, $this->reasons::REASON_HIGH_PDN);
                } catch (Exception $e) {
                    $this->logging(__METHOD__, '', 'Ошибка при отклонении заявки: ' . $e->getMessage(), [
                        'order_id' => $order->order_id,
                        'pdn' => $pdn
                    ]);
                }
            }

            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Высокий ПДН: ' . $pdn,
                'success' => 0
            ];
        }

        if (!$pdn) {
            return [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка при расчете ПДН',
                'success' => 0
            ];
        }
        
        return [
            'status' => $this->scorings::STATUS_COMPLETED,
            'string_result' => 'ПДН: ' . $pdn,
            'success' => 1
        ];
    }

    public function runPdn($order)
    {
        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1
        ];

        $result = $this->pdnCalculation->run($order->order_uid, $flags);

        if (empty($result) || !isset($result->pti_percent)) {
            return false;
        }

        return (float)$result->pti_percent;
    }
}
