<?php

error_reporting(0);
ini_set('display_errors', 'Off');

ini_set('max_execution_time', '3600');
ini_set('memory_limit', '2048M');

date_default_timezone_set('Europe/Moscow');

require dirname(__DIR__) . '/api/Simpla.php';

class Mpl extends Simpla
{
    public function run()
    {
        $dateTo = $this->request->get('date');

        if (empty($dateTo)) {
            $this->request->json_output(['error' => 'Некорректна дата']);
        }

        try {
            $dateTo = (new DateTimeImmutable($dateTo))->format('Y-m-d 23:59:59');
        } catch (Throwable $e) {
            $this->request->json_output(['error' => 'Некорректна дата']);
        }

        $result = $this->calculateMpl($dateTo);

        $this->request->json_output($result);
    }

    private function calculateMpl($dateTo): array
    {
        $orders = $this->getOrders($dateTo);

        if (empty($orders)) {
            $this->request->json_output(['error' => 'Не хватает данных для указанного квартала']);
        }

        $totalSum = 0;
        $totalSumPdn50_80 = 0;
        $totalSumPdnGreater80 = 0;

        foreach ($orders as $order) {
            $totalSum += (int)$order->amount;

            if ((float)$order->pdn > 80) {
                $totalSumPdnGreater80 += $order->amount;
            } else if ((float)$order->pdn > 50 && (float)$order->pdn <= 80) {
                $totalSumPdn50_80 += $order->amount;
            }
        }

        if (!empty($totalSum)) {
            $mplA = number_format((($totalSumPdn50_80 / $totalSum) * 100), 1, ',', ' ') . '%';
        } else {
            $mplA = '0%';
        }

        if (!empty($totalSum)) {
            $mplB = number_format((($totalSumPdnGreater80 / $totalSum) * 100), 1, ',', ' ') . '%';
        } else {
            $mplB = '0%';
        }

        return ['MPL_A' => $mplA, 'MPL_B' => $mplB];
    }

    private function getOrders($dateTo)
    {
        $timestamp = strtotime(date($dateTo));
        $month = date('n', $timestamp);
        $year = date('Y', $timestamp);
        switch (ceil($month / 3)) {
            case 1:
                $startMonth = 1;
                break;
            case 2:
                $startMonth = 4;
                break;
            case 3:
                $startMonth = 7;
                break;
            default:
                $startMonth = 10;
        }

        $dateFrom = date('Y-m-d 00:00:00', strtotime("$year-$startMonth-01"));

        $query = $this->db->placehold(
            'SELECT p.order_id, p.pdn, p.amount, p.issuance_date
            FROM pdn_calculation p
            WHERE p.issuance_date >= ? AND p.issuance_date <= ?', $dateFrom, $dateTo
        );

        $this->db->query($query);
        return $this->db->results();
    }
}

$mpl = new Mpl();
$mpl->run();
