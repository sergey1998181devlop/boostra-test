<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__ . '/../api/Simpla.php';

class FsspInfosphereCron extends Simpla
{
    private const MAX_WORKERS = 3;

    public function __construct()
    {
        $workers_list = glob(__DIR__ . '/../logs/*.fssp_worker');
        if(count($workers_list) >= static::MAX_WORKERS) {
            exit;
        }

    	parent::__construct();
    }
    
    public function run()
    {
        $i = 10;
        $scoring = 1;
        $worker_id = '' . microtime(true) . '.fssp_worker';
        file_put_contents(__DIR__ . "/../logs/$worker_id", '');
        while ($i > 0 && !empty($scoring)) {
            if ($scoring = $this->scorings->get_new_scoring([$this->scorings::TYPE_FSSP], true, true)) {
                if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                    $update = [];
                    $this->scorings->update_scoring($scoring->id, [
                        'status' => $this->scorings::STATUS_WAIT,
                        'string_result' => 'Идет проверка...',
                        'success' => 0,
                    ]);
                    if (empty($order->lastname)
                        || empty($order->firstname)
                        || empty($order->patronymic)
                        || empty($order->birth)) {
                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'string_result' => 'в заявке не достаточно данных для проведения скоринга',
                                'success' => 1,
                            );
                    } else {
                        $data = array(
                            'middle' => $order->lastname,
                            'first' => $order->firstname,
                            'paternal' => $order->patronymic,
                            'birthDt' => $order->birth,
                        );
                        $result = $this->infosphere->check_fssp($data);
                        if (empty($result) || isset($result['Source']['Error'])) {
                            $update = [
                                'status' => $this->scorings::STATUS_ERROR,
                                'body' => serialize($result),
                                'string_result' => $result['Source']['Error'],
                                'success' => 0,
                            ];
                        } else {
                            $badArticle = [];
                            $maxExp = $this->scorings->get_type($this->scorings::TYPE_FSSP);
                            $maxExp = $maxExp->params;
                            $maxExp = $maxExp['amount'];
                            $expSum = [];
                            $update = [
                                'status' => $this->scorings::STATUS_COMPLETED,
                                'body' => serialize($result),
                                'success' => 1,
                                'string_result' => 'Долгов нет',
                                'end_date' => date('Y-m-d H:i:s'),
                            ];

                            $source = isset($result['Source']['@attributes']) ? $result['Source'] : reset($result['Source']);
                            if(($source['ResultsCount'] ?? 0) > 0) {
                                foreach($source['Record'] as $key => $record) {
                                    $record_info   = [];
                                    $record_source = ($key === 'Field' ? $record : $record['Field']);
                                    foreach($record_source as $field) {
                                        $record_info[$field['FieldName']] = $field['FieldValue'];
                                    }
                                    if(isset($record_info['CloseReason1']) && in_array($record_info['CloseReason1'], [46, 47])) {
                                        $badArticle[$record_info['DocNumber']] = $record_info['CloseReason'];
                                    } else {
                                        $expSum[$record_info['DocNumber']] = (float)$record_info['Total'];
                                    }
                                }
                            }
                            
                            $totalSum = array_sum($expSum);
                            if ($totalSum > 0) {
                                $update['string_result'] = 'Сумма долга: ' . $totalSum;
                            }
                            if ($totalSum > $maxExp || !empty($badArticle)) {
                                $update['success'] = 1;
                                if (!empty($badArticle)) {
                                    $articles = implode(',', array_unique($badArticle));
                                    $update['string_result'] .= "\n Обнаружены статьи: " . $articles;
                                }
                            }
                        }
                    }
                } else {
                    $update = array(
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'не найдена заявка',
                        'success' => 1,
                    );
                }
                if (!empty($update)) {
                    $this->scorings->update_scoring($scoring->id, $update);
                }
                $this->db->query("DO RELEASE_LOCK('SCORING_CHECK_{$scoring->id}')");
            }
            $i--;
            sleep(1);
        }
        unlink(__DIR__ . "/../logs/$worker_id");
    }
}

$cron = new FsspInfosphereCron();
$cron->run();
