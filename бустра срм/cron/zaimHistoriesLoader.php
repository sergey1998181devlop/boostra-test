<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '0');

require_once __DIR__ . '/../api/Simpla.php';

class HistoryZaim extends Simpla
{
    public function process() {

        echo 'Started: ' . (new Datetime('now'))->format('Y-m-d H:i:s') . "\n";

        $endDate  = new Datetime();
        $loadDate = (new Datetime())->sub(new DateInterval('P10D'));
        $oneDay   = new DateInterval('P1D');
        $loaded   = [];

        while($loadDate <= $endDate) {
            $prev  = count($loaded);
            $users = [];
            $requestData  = [
                'Partner' => 'Boostra',
                'Date' => $loadDate->format('Ymd000000'),
            ];

            $object   = $this->soap->generateObject($requestData);
            $response = $this->soap->requestSoap($object,'WebLK', 'HistoryZaimList');

            if (!isset($response['response']) || !is_array($response['response'])) {
                $error = $response['errors'] ?? 'Empty or invalid response';

                $this->logging(
                    'HistoryZaim::process - ERROR',
                    'cron/zaimHistoriesLoader.php',
                    $requestData,
                    $response,
                    'history_zaim_list.txt'
                );

                echo $loadDate->format('Y-m-d') . " - ERROR: {$error}\n";
                $loadDate->add($oneDay);
                continue;
            }

            foreach($response['response'] as $record) {
                $users[$record['УИД']]   = $users[$record['УИД']] ?? [];
                $users[$record['УИД']][] = $record;
            }

            foreach($users as $uid => $user_loans) {
                if(!in_array($uid, $loaded)) {
                    $loanData = array_map(fn($item) => [
                        "date" => $item['ДатаЗайма'],
                        "number" => $item['НомерЗайма'],
                        "amount" => $item['СуммаЗайма'],
                        "loan_body_summ" => $item['ОстатокОД'],
                        "loan_percents_summ" => $item['ОстатокПроцентов'],
                        "close_date" => $item['ДатаЗакрытия'],
                        "paid_percents" => $item['ОплатаПроцентов'],
                        "prolongation_count" => $item['КоличествоПролонгаций'],
                        "plan_close_date" => $item['ПланДатаВозврата'],
                        "days_overdue" => $item['ДеньПросрочки'] ?? null,
                    ], $user_loans);
                    usort($loanData, function($item1, $item2) {return $item1 <=> $item2;});
                    $query = $this->db->placehold("UPDATE __users SET loan_history = ? WHERE UID = ?", json_encode($loanData), $uid);
                    $this->db->query($query);
                    $loaded[] = $uid;
                    #file_put_contents('loaded.json', json_encode($loaded));
                }
            }
            if (!empty($response['response'])) {
                $this->logging(
                    'HistoryZaim::process - SUCCESS',
                    'cron/zaimHistoriesLoader.php',
                    $requestData,
                    $response,
                    'history_zaim_list.txt'
                );
            }

            echo $loadDate->format('Y-m-d') . ' - ' . count($users) . "\n";
            echo 'Updated at ' . (new Datetime('now'))->format('Y-m-d H:i:s') . ': ' . (count($loaded) - $prev) . "\n";

            $loadDate->add($oneDay);
        }
    }
}

(new HistoryZaim)->process();
