<?php

namespace Traits;

use Scorings;
use Throwable;

require_once __DIR__ . '/../../api/Scorings.php';

trait HasScoringsForAxiTrait
{
    private array $orderColumns = [
        'id',
        'user_id',
        'cdoctor_id',
        'confirm_date',
        'approve_date',
        'reject_date',
        'payment_method_id',
        'paid',
        'payment_date',
        'closed',
        'date',
        'status',
        'ip',
        'amount',
        'approve_amount',
        'period',
        'percent',
        'first_loan',
        'status_1c',
        'reason_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'webmaster_id',
        'have_close_credits',
        'razgon',
        'loan_type',
        'credit_getted',
        'insurer',
        'insure_amount',
        'insure_percent',
        'scorista_ball',
        'is_credit_doctor',
        'order_uid',
        'complete',
        'is_user_credit_doctor',
        'additional_service',
        'additional_service_repayment',
        'additional_service_partial_repayment',
        'deleteKD',
        'organization_id',
        'pdn_nkbi_loan'
    ];

    public function internalCR(int $userId): string
    {
        try {
            $executionStartTime = microtime(true);

            $orders = $this->orders->get_orders(['user_id' => $userId,]);

            $this->logging(__METHOD__, '', '', 'Запрос orders из БД  для userId(' . $userId . '): ' . (microtime(true) - $executionStartTime) . ' mc', 'debug_axi.log');

            $internalCR = '';

            foreach ($orders as $order) {
                $order = array_intersect_key((array)$order, array_flip($this->orderColumns));

                $arguments = implode(' ', array_map(
                    fn($v, $k) => sprintf('%s="%s"', $this->prepareXmlKey($k), $this->prepareXmlValue($v)),
                    $order,
                    array_keys($order)
                ));

                $internalCR .= <<<XML
                <order $arguments />
            XML;
            }

            $this->logging(__METHOD__, '', '', 'Формирование internalCR для userId(' . $userId . '): ' . (microtime(true) - $executionStartTime) . ' mc', 'debug_axi.log');

            return $internalCR;
        } catch (Throwable $e) {
            $this->logging(__METHOD__, '', '', 'Ошибка internalCR для userId(' . $userId . '): ' . $e->getMessage(), 'debug_axi.log');
        }

        return '';
    }

    public function scorings(int $userId, int $orderId): string
    {
        try {
            $dataXml = '';

            $executionStartTime = microtime(true);

            // Не запрашиваем скоринги, заглушка
            $scorings = [];

            $this->logging(__METHOD__, '', '', 'Запрос скорингов из БД для orderId(' . $orderId .'): ' . (microtime(true) - $executionStartTime) . ' mc', 'debug_axi.log');

            $types = array_unique(array_column($scorings, 'type'));

            foreach ($types as $type) {
                $scoringsByType = array_filter(
                    $scorings, fn($scoring) => $scoring->type === $type && $scoring->order_id === $orderId
                );

                if ($scoringsByType === []) {
                    $scoringsByType = array_filter($scorings, fn($scoring) => $scoring->type === $type);
                }

                if ($scoringsByType === []) {
                    continue;
                }

                usort($scoringsByType, fn($a, $b) => $a->created <=> $b->created);

                $scoringByType = current($scoringsByType);

                $body = $this->scorings->get_body_by_type($scoringByType);

                $dataXml .= $this->prepareData($type, $body ?? '');
            }

            $this->logging(__METHOD__, '', '', 'Формирование scorings для orderId(' . $orderId .'): ' . (microtime(true) - $executionStartTime) . ' mc', 'debug_axi.log');

            return $dataXml;
        } catch (Throwable $e) {
            $this->logging(__METHOD__, '', '', 'Ошибка scorings для orderId(' . $orderId . '): ' . $e->getMessage(), 'debug_axi.log');
        }

        return '';
    }

    private function prepareData(string $type, $data): string
    {
//        if ($type == Scorings::TYPE_JUICESCORE) {
//            return $this->prepareJuicescore($data);
//        }

        return '';
    }

    private function prepareJuicescore($juicescore): string
    {
        if (empty($juicescore)) {
            return '';
        }

        $body = $this->arrayToXml($juicescore);

        return <<<XML
            <JUICESCORE>
                $body
            </JUICESCORE>
        XML;
    }

    private function arrayToXml($array): string
    {
        $xml = '';
        $array = (array)$array;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }

            $key = $this->prepareXmlKey($key);

            if (is_array($value) || is_object($value)) {
                $body = '';
                $arguments = [];
                $value = (array)$value;

                foreach ($value as $k => $v) {
                    if (is_numeric($k)) {
                        $k = 'item';
                    }

                    if (is_array($v)) {
                        $body .= $this->arrayToXml($v);
                        continue;
                    }

                    $arguments[] = sprintf('%s="%s"', $this->prepareXmlKey($k), $this->prepareXmlValue($v));
                }

                $arguments = implode(' ', $arguments);

                $xml .= <<<XML
                    <$key $arguments >
                        $body
                    </$key>
                XML;

                continue;
            }

            $value = $this->prepareXmlValue($value);

            $xml .= <<<XML
                <$key value="$value" />
            XML;
        }


        return $xml;
    }

    private function prepareXmlKey(string $key): string
    {
        $key = mb_strtolower(str_replace([' ', '-'], '_', $key), 'UTF-8');
        $key = preg_replace('/[^\p{L}\p{N}_]/u', '', $key);

        if (preg_match('/^\d/', $key)) {
            $key = '_' . $key;
        }

        return $key;
    }

    private function prepareXmlValue($value): string
    {
        if (empty($value)) {
            return '';
        }

        return htmlspecialchars((string)$value, ENT_QUOTES);
    }
}