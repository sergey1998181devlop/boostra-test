<?php

require_once __DIR__ . '/../api/Simpla.php';

class TestOrder_YTFchcrdRD extends Simpla
{
    public $order;

    public function __construct($order_id)
    {
        $this->order = $this->orders->get_order($order_id);
    }
}

/*
    Периодически возникает необходимость догружать данные для расчетов ПДН.

    Среднерыноные значения ПСК берем со страницы ЦБ https://cbr.ru/statistics/bank_sector/psk/, грузим в таблицу s_average_psk следующие файлы:
        1. Среднерыночные значения полной стоимости потребительского кредита (займа), определенные для кредитных организаций
        2. Среднерыночные значения полной стоимости потребительского кредита (займа), определенные для микрофинансовых организаций
    По колонкам:
        period - дата начала действия ПСК, ЦБ дает ставки на следующий квартал (например, для файла,
                    выставленного в ноябре 2021, ставим 2022-01-01 - первый квартал 2022-го)
        type - ko для первого типа файлов, mfo для второго
        code - код категории, в файле это "Номер строки". В классе нужная категория вычисляется в методе getAveragePSK в зависимости от свойств займа.
        description - можно не заполнять вообще или заполнить один раз для каких-то новых категорий. По сути, вспомогательная информация.
        psk - значение ПСК.
    
    Зарплаты Росстата берем со страницы https://rosstat.gov.ru/folder/13397, грузим в таблицу s_rosstat_incomes файл:
        Среднедушевые денежные доходы населения по субъектам Российской Федерации (новая методология)
    По колонкам:
        start_date - дата окончания квартала
        include_date - start_date + 6 месяцев (ЦБ берет данные с лагом, похоже из-за отставания выхода статистики)
        income - размер дохода из файла
        region_id - id региона из таблицы s_rosstat_regions
    
    Работа нечастая, не вижу смысла в автоматизации.
*/

class BoostraPTI extends Simpla
{
    private const LEFT_PADS = 4;
    
    private $messages = [];
    private $details = false;
    private $parsed_history = [];
    private $zaim_date;
    private $source = '';
    private $order = null;
    private $agrid = '';

    public function __construct($order)
    {
        parent::__construct();
        
        $this->order = $order;
        $this->zaim_date = new \DateTime($order->confirm_date ?: $order->date);
    }

    public function setSource(string $xml_source = null)
    {
        if($xml_source) {
            $this->source = $xml_source;
        } else {
            $query = $this->db->placehold("
                SELECT scorista_id, type
                FROM __scorings
                WHERE
                    order_id = ?
                    AND type IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                    AND scorista_id IS NOT NULL
                ORDER BY id DESC
                LIMIT 1", $this->order->order_id);
            $this->db->query($query);
            $scoring = $this->db->result();
            if($scoring) {
                $path = "{$this->config->root_dir}files/" . ($scoring->type == $this->scorings::TYPE_AXILINK ? 'axilink' : 'equifax') . "_zipped/{$scoring->scorista_id}.zip";
                $this->agrid = $scoring->scorista_id;
                if(file_exists($path)) {
                    $zip_source  = new \ZipArchive();
                    $result = $zip_source->open($path, ZipArchive::RDONLY);
                    if($result === true) {
                        $this->source = $zip_source->getFromIndex(0);
                        $zip_source->close();
                    } else {
                        echo "Failed to open $path\n";
                        return false;
                    }
                } else {
                    echo "History's file doesn't exist: {$this->order->order_id} -> {$this->agrid}\n";
                    return false;
                }
            } else {
                echo "Scoring not found: {$this->order->order_id}\n";
                return false;
            }
        }
        return true;
    }

    private function getAveragePSK(array $contract)
    {
        $code  = '';
        $type  = '';
        $alt_code = '';
        /*if(empty($contract['contract_amount'])) {
            $contract['contract_amount'] = 0;
            echo "Empty amount {$this->agrid} - {$contract['id']}\n";
        }*/
        if(empty($contract['purpose'])) {
            $contract['purpose'] = 999;
            echo "Empty purpose {$this->agrid} - {$contract['id']}\n";
        }
        if(empty($contract['type'])) {
            $contract['type'] = 999;
            echo "Empty type {$this->agrid} - {$contract['id']}\n";
        }
        $sum   = $contract['contract_amount'];
        //14 - значение из мануалов Эквифакса/Скоринг-бюро, POS-кредит
        $isPOS = $contract['purpose'] == 14;
        //3 - значение из мануалов Эквифакса/Скоринг-бюро, микрозайм
        if($contract['type'] == 3) {
            $term = $contract['end_date']->diff($contract['date'])->days;
            $type = 'mfo';
            if($term <= 30) {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.1.1' : '2.3.1.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.1.2' : '2.3.1.2';
                } else {
                    $code = $isPOS ? '2.4.1.2' : '2.3.1.2';
                }
            } elseif($term <= 60) {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.1.1' : '2.3.2.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.1.2' : '2.3.2.2';
                } else {
                    $code = $isPOS ? '2.4.1.2' : '2.3.2.2';
                }
            } elseif($term <= 180) {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.1.1' : '2.3.3.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.1.2' : '2.3.3.2';
                } else {
                    $code = $isPOS ? '2.4.1.2' : '2.3.3.3';
                }
            } elseif($term <= 305) {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.2.1' : '2.3.4.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.2.2' : '2.3.4.1';
                } else {
                    $code = $isPOS ? '2.4.2.2' : '2.3.4.2';
                }
            } elseif($term <= 365) {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.3.1' : '2.3.4.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.3.2' : '2.3.4.1';
                } else {
                    $code = $isPOS ? '2.4.3.2' : '2.3.4.2';
                }
            } else {
                if($sum <= 30000) {
                    $code = $isPOS ? '2.4.4' : '2.3.5.1';
                } elseif($sum <= 100000) {
                    $code = $isPOS ? '2.4.4' : '2.3.5.1';
                } else {
                    $code = $isPOS ? '2.4.4' : '2.3.5.2';
                }
            }
        //2 - значение из мануалов Эквифакса/Скоринг-бюро, ипотека
        } elseif($contract['type'] == 2) {
            $type = 'ko';
            //значения из мануалов Эквифакса/Скоринг-бюро, тип приобретаемого имущества (земля, готовая недвижка, строительство)
            if(in_array($contract['purpose'], ['2.2', '2.3', '2.4', '2.5', '2.6', '2.7'])) {
                $code = '6';
            } elseif(in_array($contract['purpose'], ['2.1', '4.1'])) {
                $code = '7';
            } else {
                $code = '8';
            }
            $alt_code = '4.2.4';
        // карточный кредит
        } elseif($contract['sign_credit_card']) {
            $type = 'ko';
            if($sum <= 30000) {
                $code = '2.1';
            } elseif($sum <= 300000) {
                $code = '2.2';
            } else {
                $code = '2.3';
            }
        } else {
            $type = 'ko';
            if(in_array($contract['purpose'], ['17'])) {
                $code = '1.1';
            } elseif(in_array($contract['purpose'], ['18'])) {
                $code = '1.2';
            } else {
                $term = $contract['end_date']->diff($contract['date'])->days;
                if($term <= 365) {
                    if($sum <= 30000) {
                        $code = $isPOS ? '3.1.2' : '4.1.1';
                    } elseif($sum <= 100000) {
                        $code = $isPOS ? '3.1.2' : '4.1.2';
                    } elseif($sum <= 300000) {
                        $code = $isPOS ? '3.1.3' : '4.1.3';
                    } else {
                        $code = $isPOS ? '3.1.3' : '4.1.4';
                    }
                } else {
                    if($sum <= 30000) {
                        $code = $isPOS ? '3.2' : '4.2.1';
                    } elseif($sum <= 100000) {
                        $code = $isPOS ? '3.2' : '4.2.2';
                    } elseif($sum <= 300000) {
                        $code = $isPOS ? '3.2' : '4.2.3';
                    } else {
                        $code = $isPOS ? '3.2' : '4.2.4';
                    }
                }
            }
        }
        if($code && $type) {
            $query = $this->db->placehold('
                SELECT psk
                FROM __average_psk
                WHERE `type` = ?
                    AND `code` = ?
                    AND `period` <= ?
                ORDER BY `period` DESC
                LIMIT 1', $type, $code, $contract['date']->format('Y-m-d'));
            $this->db->query($query);
            $psk = (float)$this->db->result('psk');
            if(!$psk && $alt_code) {
                $query = $this->db->placehold('
                    SELECT psk
                    FROM __average_psk
                    WHERE `type` = ?
                        AND `code` = ?
                        AND `period` <= ?
                    ORDER BY `period` DESC
                    LIMIT 1', $type, $alt_code, $contract['date']->format('Y-m-d'));
                $this->db->query($query);
                $psk = (float)$this->db->result('psk');
            }
            return $psk;
        }
        return 0;
    }

    private function normalizeRegion(string $region)
    {
        $region = mb_strtoupper($region);
        $region = preg_replace('|\d+|', '', $region);
        $region = preg_replace('|^Г |', '', $region);
        $region = trim(str_replace(['Г.', 'РЕСПУБЛИКА', 'РЕСП', 'ОБЛАСТЬ', 'ОБЛ'], ' ', $region));
        $region = trim(str_replace([',', '.'], ' ', $region));
        $region = strtok($region, ' ');
        $region = trim(str_replace(['МОСКВОВСКАЯ', 'МОСКОВКАЯ', 'МОСКОВСКЯ'], 'МОСКОВСКАЯ', $region));
        $region = trim(str_replace(['НОВОСИБИРСКИЙ'], 'НОВОСИБИРСКАЯ', $region));
        $region = trim(str_replace(['САНКТ', 'САНКТ-ПЕТЕРБУРГ-ПЕТЕРБУРГ'], 'САНКТ-ПЕТЕРБУРГ', $region));
        $region = trim(str_replace(['СВЕРДЛОВСКА', 'СВЕРДЛОВСКАЯЯ'], 'СВЕРДЛОВСКАЯ', $region));
        $region = trim(str_replace(['ЛЕНИНГРАДСКОЙ'], 'ЛЕНИНГРАДСКАЯ', $region));
        $region = trim(str_replace(['КРАСНОДАРСКИ', 'КРАСНОДАРСКИЙЙ'], 'КРАСНОДАРСКИЙ', $region));
        $region = trim(str_replace(['КУРГАНСКА', 'КУРГАНСКАЯЯ'], 'КУРГАНСКАЯ', $region));
        $region = trim(str_replace(['СТАВРОПОЛЬСКИ', 'СТАВРОПОЛЬСКИЙЙ'], 'СТАВРОПОЛЬСКИЙ', $region));
        $region = trim(str_replace(['УДМУРТИЯ'], 'УДМУРТСКАЯ', $region));
        $region = trim(str_replace(['МАРИЙ'], 'МАРИЙ ЭЛ', $region));
        $region = trim(str_replace(['СЕВЕРНАЯ'], 'СЕВЕРНАЯ ОСЕТИЯ', $region));
        $region = implode('-', array_unique(explode('-', $region)));

        return $region;
    }

    private function calculateRosstatSalary()
    {
        if(empty($this->order->region_id)) {
            $query = $this->db->placehold("
                SELECT Regregion region
                FROM __users
                WHERE id = ?", $this->order->user_id);
            $this->db->query($query);
            $norm_region = $this->normalizeRegion($this->db->result('region'));
            $query = $this->db->placehold("
                SELECT SUM(income) / COUNT(*) avg_income
                FROM (SELECT income
                        FROM __rosstat_incomes rs
                        JOIN __rosstat_regions rr
                            ON rr.id = rs.region_id
                        WHERE rr.region = ?
                            AND rs.include_date <= ?
                        ORDER BY rs.include_date DESC
                        LIMIT 4) incomes",
                $norm_region, $this->zaim_date->format('Y-m-01'));
            $this->db->query($query);
            return (float)$this->db->result('avg_income');
        }
        return 0;
    }

    public function getPTIData()
    {
        $this->parseHistory();
        
        $psk = $this->order->percent * 3.65 * $this->order->period / 360;
        $total_debt_avg = 0;
        $loan_debt_avg  = !$psk ? $this->order->amount : ($psk * $this->order->amount / (1 - 1 / (1 + $psk)));
        $rosstat_salary = $this->calculateRosstatSalary();
        $result = [
            'rosstat_salary' => $rosstat_salary,
            'total_debt_avg' => $total_debt_avg,
            'loan_debt_avg'  => $loan_debt_avg,
            'rosstat_pti'    => $rosstat_salary ? ($loan_debt_avg / $rosstat_salary) : 0,
        ];

        if(empty($this->parsed_history) || empty($this->parsed_history['contracts'])) {
            echo "Credit history is empty: {$this->order->order_id} -> {$this->agrid}\n";
            return $result;
        }

        $this->pushMessage(['ДатаЗайма'], $this->zaim_date->format('Y-m-d'));
        foreach($this->parsed_history['contracts'] as $id => $contract) {
            if(!empty($contract['sum_current'])
                || !empty($contract['sum_overdue'])
                || !empty($contract['sign_credit_card'])) {
                    $loan_date = $this->zaim_date;
                    if(empty($contract['percent'])) {
                        $contract['percent'] = $this->getAveragePSK($contract, );
                        $this->pushMessage(['Займы', "_$id", 'contract'], [
                            'FullCost' => $contract['percent'],
                            'FullCostCB' => $contract['percent'],
                        ]);
                    }
                    if(empty($contract['sign_credit_card'])) {
                        $term = $contract['end_date']->diff($contract['date'])->days;
                        $psk  = $contract['percent'] / 12 / 100;
                        $rest = 0;
                        if($loan_date < $contract['end_date']) {
                            $end_date_month  = (int)$contract['end_date']->format('m');
                            $end_date_year   = (int)$contract['end_date']->format('Y');
                            $end_date_day    = (int)$contract['end_date']->format('d');
                            $loan_date_month = (int)$loan_date->format('m');
                            $loan_date_year  = (int)$loan_date->format('Y');
                            $loan_date_day   = (int)$loan_date->format('d');

                            $rest = $end_date_month - $loan_date_month + 12 * ($end_date_year - $loan_date_year) + ($end_date_day > $loan_date_day);
                        }
                        $this->pushMessage(['Займы', "_$id"], [
                            'ПолныйСрокДни' => $term,
                            'ПСК' => $contract['percent'],
                        ]);
                        if(empty($contract['percent']) || $contract['percent'] == 0) {
                            $contract['sum_overdue'] = ($contract['sum_overdue'] ?? 0) + ($contract['sum_current'] ?? 0);
                            $contract['op_sum_current'] = 0;

                            $mess = $this->getMessage(['Займы', "_$id"]);
                            $this->pushMessage(['Займы', 'НетПроцентов', "_$id"], $mess);
                            $psk = 1;
                        }
                        if($term <= 30) {
                            $psk *= $term / 30;
                            $this->pushMessage(['Займы', "_$id", 'ЧБП'], $term / 30);
                        }
                        $debt_avg = ($rest && $psk ? $psk * ($contract['op_sum_current'] ?? 0) / (1 - (1 + $psk) ** -$rest) : 0) + ($contract['sum_overdue'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'Т'], $rest);
                        $this->pushMessage(['Займы', "_$id", 'СрЗ'], $contract['op_sum_current'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'СрЗПолн'], $contract['sum_current'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'ПрЗ'], $contract['sum_overdue'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'СреднемесячныйПлатеж'],
                                            "$psk * " . ($contract['op_sum_current'] ?? 0) . " / (1 - (1 + $psk) ** -$rest) + " . ($contract['sum_overdue'] ?? 0) . " = $debt_avg");
                        $total_debt_avg += $debt_avg;
                    } else {
                        if($loan_date > $contract['end_date']) {
                            $debt_avg = 0;
                        } else {
                            $debt_avg = max(.05 * ($contract['contract_amount'] + ($contract['sum_overdue'] ?? 0)), $contract['sum_overdue'] ?? 0);
                        }
                        if(($contract['op_sum_current'] ?? 0) + ($contract['sum_overdue'] ?? 0)) {
                            $debt_avg1 = .1 * ($contract['op_sum_current'] ?? 0) + ($contract['sum_overdue'] ?? 0);
                            $debt_avg = $debt_avg ? min($debt_avg, $debt_avg1) : $debt_avg1;
                        }
                        $this->pushMessage(['Займы', "_$id", 'СрЗк'], $contract['op_sum_current'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'ПрЗк'], $contract['sum_overdue'] ?? 0);
                        $this->pushMessage(['Займы', "_$id", 'СреднемесячныйПлатежК'],
                                            "min(max(.05 * ({$contract['contract_amount']} + " . ($contract['sum_overdue'] ?? 0) . "), "
                                            . ($contract['sum_overdue'] ?? 0) . "), .1 * " . ($contract['op_sum_current'] ?? 0) . ' + '
                                            . ($contract['sum_overdue'] ?? 0) . ") = $debt_avg");
                        $total_debt_avg += $debt_avg;
                    }
                    if($mess = $this->getMessage(['Займы', "_$id"])) {
                        $this->unsetMessage(['Займы', "_$id"]);
                        if($debt_avg) {
                            $this->pushMessage(['Займы', 'ВключеныВРасчеты', "_$id"], $mess);
                        } else {
                            $this->pushMessage(['Займы', 'ИсключеныИзРасчетов', "_$id"], $mess);
                        }
                    }
                } else {
                    $mess = $this->getMessage(['Займы', "_$id"]);
                    $this->unsetMessage(['Займы', "_$id"]);
                    $this->pushMessage(['Займы', 'ИсключеныИзРасчетов', "_$id"], $mess);
                }
        }
        $result['total_debt_avg'] = $total_debt_avg;
        $result['rosstat_pti']    = ($rosstat_salary ? ($total_debt_avg + $loan_debt_avg) / $rosstat_salary : 0);
        if($this->details) {
            $result_details = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                                . "<root>\n"
                                . $this->buildXML($this->messages, 1)
                                . "\n</root>";
            $cb_request = $this->buildCBReport();
            $zip = new ZipArchive();
            $zip->open($this->config->root_dir . 'files/pdn_details/' . $this->agrid . '_' . (new DateTime())->format('YmdHis') . '.zip', ZipArchive::CREATE);
            $zip->addFromString('calculation.xml', $result_details);
            $zip->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
            $zip->addFromString('cb_left_part.csv', implode("\n", $cb_request['left_part']));
            $zip->setCompressionIndex(1, ZipArchive::CM_LZMA, 9);
            $zip->addFromString('cb_right_part.csv', implode("\n", $cb_request['right_part']));
            $zip->setCompressionIndex(2, ZipArchive::CM_LZMA, 9);
            $zip->addFromString('pti_result.json', json_encode($result, JSON_PRETTY_PRINT));
            $zip->setCompressionIndex(2, ZipArchive::CM_LZMA, 9);
            $zip->close();
        }
        return $result;
    }

    private function parseHistory()
    {
        $domDoc = new DOMDocument;
        $body   = $this->source;
        $orders = [
            'discipline' => [],
            'contracts' => [],
            'payload' => [],
        ];
        if(empty($this->source) || !$domDoc->loadXML($body)) {
            $this->parsed_history = $orders;
        }
        $xPathRoot = new DOMXPath($domDoc);
        $iterator  = [['node' => $xPathRoot->query("/")->item(0), 'path' => '']];
        $length    = 1;
        for($step = 0; $step < $length; $step++) {
            $node = $iterator[$step]['node'];
            $path = $iterator[$step]['path'];
            $parent = implode('', array_slice(explode('/', $path), -1));
            if($path != '/xml-stylesheet' && $node->childNodes->count()) {
                foreach($node->childNodes as $child) {
                    if(get_class($child) != 'DOMText') {
                        if($child->nodeName == 'contract') {
                            $contract = [];
                            $message  = [];
                            foreach($child->childNodes as $child1) {
                                switch($child1->nodeName) {
                                    case 'uid':
                                        $contract['uid'] = trim($child1->textContent);
                                        $message['UID'] = $contract['uid'];
                                        break;
                                    case 'deal':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['date'] = new DateTime($date);
                                                $message['Date'] = $contract['date']->format('Y-m-d');
                                            }
                                            if($child2->nodeName == 'end_date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['end_date'] = new DateTime($date);
                                                $message['DateEnd'] = $contract['end_date']->format('Y-m-d');
                                            }
                                            if($child2->nodeName == 'type') {
                                                $contract['sign_credit_card'] = in_array((int)trim($child2->textContent), [4, 5, 6, 7]) ? 1 : 0;
                                                $message['CreditCard'] = $contract['sign_credit_card'];
                                                $message['Type'] = $contract['type'] = (int)trim($child2->textContent);
                                            }
                                            if($child2->nodeName == 'purpose') {
                                                $message['Purpose'] = $contract['purpose'] = trim($child2->textContent);
                                            }
                                            /*if($child2->nodeName == 'sign_credit_card') {
                                                $contract['sign_credit_card'] = (int)trim($child2->textContent);
                                                $message['CreditCard'] = $contract['sign_credit_card'];
                                            }*/
                                        }
                                        break;
                                    case 'payments':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'last_payout_date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['last_payout_date'] = new DateTime($date);
                                                $message['LastPayoutDate'] = $contract['last_payout_date']->format('Y-m-d');
                                            }
                                        }
                                        break;
                                    case 'full_cost':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'percent') {
                                                $contract['percent'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['FullCost'] = $contract['percent'];
                                            }
                                        }
                                        break;
                                    case 'contract_amount':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'sum') {
                                                $contract['contract_amount'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['Amount'] = $contract['contract_amount'];
                                            }
                                        }
                                        break;
                                    case 'debt_current':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'op_sum') {
                                                $contract['op_sum_current'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['AmountCurrent'] = $contract['op_sum_current'];
                                            }
                                            if($child2->nodeName == 'sum') {
                                                $contract['sum_current'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['AmountCurrentFull'] = $contract['sum_current'];
                                            }
                                        }
                                        break;
                                    case 'debt_overdue':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'op_sum') {
                                                $contract['op_sum_overdue'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['OpOverdue'] = $contract['op_sum_overdue'];
                                            }
                                            if($child2->nodeName == 'sum') {
                                                $contract['sum_overdue'] = (float)str_replace(',', '.', trim($child2->textContent));
                                                $message['Overdue'] = $contract['sum_overdue'];
                                            }
                                        }
                                        break;
                                    case 'debt':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'calc_date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['calc_date'] = new DateTime($date);
                                                $message['CalcDate'] = $contract['calc_date']->format('Y-m-d');
                                            }
                                        }
                                        break;
                                    case 'extra_data':
                                        if($child1->attributes->getNamedItem('id')->textContent == '2') {
                                            $contract['id'] = $child1->attributes->getNamedItem('value')->textContent;
                                        }
                                        break;
                                }
                            }
                            if(!empty($contract['id'])) {
                                $orders['contracts'][$contract['id']] = $contract;
                                $this->pushMessage(['Доходы', '_' . $contract['id'], 'contract'], $message);
                                $this->pushMessage(['Займы', '_' . $contract['id'], 'contract'], $message);
                            }
                        } elseif($child->nodeName == 'credit' && $parent == 'base_part') {
                            $contract = [];
                            $message  = [];
                            foreach($child->childNodes as $child1) {
                                switch($child1->nodeName) {
                                    case 'uid':
                                        $contract['uid'] = trim($child1->textContent);
                                        $message['UID'] = $contract['uid'];
                                        break;
                                    case 'cred_date':
                                        $date = trim($child1->textContent) == '-' ? 'now' : trim($child1->textContent);
                                        $contract['date'] = new DateTime($date);
                                        $message['Date'] = $contract['date']->format('Y-m-d');
                                        break;
                                    case 'cred_enddate':
                                        $date = trim($child1->textContent) == '-' ? 'now' : trim($child1->textContent);
                                        $contract['end_date'] = new DateTime($date);
                                        $message['DateEnd'] = $contract['end_date']->format('Y-m-d');
                                        break;
                                    case 'cred_type':
                                        $contract['sign_credit_card'] = in_array((int)trim($child1->textContent), [4, 14, 24]) ? 1 : 0;
                                        $message['CreditCard'] = $contract['sign_credit_card'];
                                        break;
                                    case 'cred_full_cost':
                                        $contract['percent'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $message['FullCost'] = $contract['percent'];
                                        break;
                                    case 'cred_sum':
                                        $contract['contract_amount'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $message['Amount'] = $contract['contract_amount'];
                                        break;
                                    case 'cred_sum_debt':
                                        $contract['op_sum_current'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $contract['sum_current'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $message['AmountCurrent'] = $contract['op_sum_current'];
                                        $message['AmountCurrentFull'] = $contract['sum_current'];
                                        break;
                                    case 'cred_sum_overdue':
                                        $contract['op_sum_overdue'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $contract['sum_overdue'] = (float)str_replace(',', '.', trim($child1->textContent));
                                        $message['OpOverdue'] = $contract['op_sum_overdue'];
                                        $message['Overdue'] = $contract['sum_overdue'];
                                        break;
                                    case 'cred_id':
                                        $contract['id'] = trim($child1->textContent);
                                        break;
                                    /*
                                    case 'debt':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'calc_date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['calc_date'] = new DateTime($date);
                                                $message['CalcDate'] = $contract['calc_date']->format('Y-m-d');
                                            }
                                        }
                                        break;
                                    case 'payments':
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->nodeName == 'last_payout_date') {
                                                $date = trim($child2->textContent) == '-' ? 'now' : trim($child2->textContent);
                                                $contract['last_payout_date'] = new DateTime($date);
                                                $message['LastPayoutDate'] = $contract['last_payout_date']->format('Y-m-d');
                                            }
                                        }
                                        break;
                                    */
                                }
                            }
                            $orders['contracts'][$contract['id']] = $contract;
                            $this->pushMessage(['Займы', '_' . $contract['id'], 'contract'], $message);
                        } elseif($child->nodeName == 'section') {
                            if($child->attributes->getNamedItem('id')->textContent == '33') { //Платежная нагрузка
                                foreach($child->childNodes as $child1) {
                                    if($child1->nodeName == 'period') {
                                        $month = (int)$child1->attributes->getNamedItem('month')->textContent;
                                        $orders['payload'][$month] = [];
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->attributes
                                                && $child2->attributes->getNamedItem('n')->textContent == '833') {
                                                    $id = $child2->attributes->getNamedItem('c_i')->textContent;
                                                    $orders['payload'][$month][$id] = (float)$child2->attributes->getNamedItem('v')->textContent;
                                                    $this->pushMessage(['Доходы', '_' . $id, 'ПлатежнаяНагрузка', $month],
                                                                        $orders['payload'][$month][$id]);
                                            }
                                        }
                                    }
                                }
                            } elseif($child->attributes->getNamedItem('id')->textContent == '23') { //Платежная дисциплина за весь срок жизни кредита
                                foreach($child->childNodes as $child1) {
                                    if($child1->nodeName == 'period') {
                                        foreach($child1->childNodes as $child2) {
                                            if($child2->attributes
                                                && $child2->attributes->getNamedItem('n')->textContent == '931') {
                                                    $id = $child2->attributes->getNamedItem('c_i')->textContent;
                                                    $orders['discipline'][$id] = $child2->attributes->getNamedItem('v')->textContent;
                                                    $this->pushMessage(['Доходы', '_' . $id, 'ПлатежнаяДисциплина'], $orders['discipline'][$id]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            if($child->nodeName == 'response') {
                                $orders['dateofreport'] = new DateTime(trim($child->attributes->getNamedItem('dateofreport')->textContent));
                                $this->pushMessage(['DateOfReport'], $orders['dateofreport']->format('Y-m-d'));
                            }
                            $iterator[] = ['node' => $child, 'path' => $path . "/{$child->nodeName}"];
                        }
                    }
                }
            }
            $length = count($iterator);
        }
        $this->parsed_history = $orders;
    }

    private function pushMessage($path, $message)
    {
        if(!$this->details) {
            return;
        }
        $branch = &$this->messages;
        $last   = array_pop($path);
        foreach($path as $step) {
            $branch[$step] = $branch[$step] ?? [];
            $branch = &$branch[$step];
        }
        $branch[$last] = empty($branch[$last]) ? $message : array_merge($branch[$last], $message);
    }

    private function getMessage($path)
    {
        if(!$this->details) {
            return null;
        }
        $branch = $this->messages;
        foreach($path as $step) {
            if(empty($branch[$step])){
                return null;
            } else {
                $branch = $branch[$step];
            }
        }
        return $branch;
    }

    private function unsetMessage($path)
    {
        if(!$this->details) {
            return null;
        }
        $branch = &$this->messages;
        $last   = array_pop($path);
        foreach($path as $step) {
            if(empty($branch[$step])){
                return null;
            } else {
                $branch = &$branch[$step];
            }
        }
        unset($branch[$last]);
    }

    private function buildXML($source, $pads = 0)
    {
        $buf = [];
        foreach($source as $key => $value) {
            $key1 = str_replace(' ', '_', $key);
            if(is_array($value)) {
                $buf[] = str_pad('', static::LEFT_PADS * $pads) . "<$key1>";
                $buf[] = $this->buildXML($value, $pads + 1);
                $buf[] = str_pad('', static::LEFT_PADS * $pads) . "</$key1>";
            } else {
                $buf[] = str_pad('', static::LEFT_PADS * $pads) . "<$key1>$value</$key1>";
            }
        }
        return implode("\n", $buf);
    }

    public function toggleDetails(bool $mode)
    {
        $this->details = $mode;
    }

    public function buildCBReport()
    {
        $query = $this->db->placehold("SELECT
                                            sc.user_id,
                                            o.amount zaim_summ,
                                            o.percent,
                                            o.`period` term,
                                            o.`1c_id` onum,
                                            CAST(sc.end_date AS DATE) scorista,
                                            CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) fio,
                                            u.Regregion,
                                            u.income_base
                                        FROM s_orders o
                                        JOIN s_scorings sc
                                            ON o.id = sc.order_id
                                            AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                                            AND sc.status = ".$this->scorings::STATUS_COMPLETED."
                                        JOIN s_users u
                                            ON sc.user_id = u.id
                                        WHERE o.id = ?
                                        LIMIT 1", $this->order->order_id);
        $this->db->query($query);
        $row = $this->db->result();
        $left_part = [implode("\t", [
            'ФИО Заемщика',
            'Регион места жительства/места пребывания',
            '',
            '',
            'Дата ответа БКИ',
            'Дата расчета ПДН',
            '',
            'ПСК,%',
            'Срок займа, дней',
            'Т',
            'Сумма займа, руб.',
            'СМП1, руб.',
            'Доход, указанный заемщиком в заявлении, руб.',
            'Среднее арифметическое значение среднедушевого денежного дохода в регионе, руб.',
        ])];
        $psk = (float)$row->percent * 3.65 * $row->term / 360;
        $income_base = !empty($row->income_base) ? (float)preg_replace('|[^0-9,.]|', '', $row->income_base) : 0;
        $left_part[] = implode("\t", [
            $row->fio,
            $row->Regregion,
            '',
            '',
            $row->scorista,
            $row->scorista,
            '',
            number_format((float)$row->percent * 365, 3, ',', ''),
            $row->term,
            1,
            $row->zaim_summ,
            number_format(!$psk ? (float)$row->zaim_summ : ($psk * ((float)$row->zaim_summ) / (1 - 1 / (1 + $psk))), 2, ',', ''),
            $income_base,
            $this->calculateRosstatSalary(),
        ]);
        $right_part = [implode("\t", [
            'Идентификационный № займа/кредита',
            'ПСК, %',
            'Дата заключения договора займа/кредита',
            'Дата погашения займа/кредита',
            'Поправочный коэффициент к ПСК - ЧБП/30 (для кредитов (займов) на срок до 30 дней)',
            'СрЗ, руб.',
            'Т',
            'ПрЗ, руб.',
            'Сумма просроченной задолженности по кредиту (займу) сроком свыше 30 дней, по которому заемщик выступает поручителем, руб. (при наличии)',
            'Лимит кредитной карты, руб.',
            'ТЗ (для кредитной карты), руб.',
            'Сумма просроченной задолженности, которая будет погашена средствами, полученными по проверяемому договору, руб. (заполняется при рефинансировании задолженности, п.2.9 Приложения Указания 5114-У)',
            'Сумма срочной задолженности, которая будет погашена средствами, полученными по проверяемому договору, руб. (заполняется при рефинансировании задолженности, п.2.9 Приложения Указания 5114-У)',
            'СМП2, руб.',
            'fio',
        ])];
        if(!empty($this->messages['Займы']['ВключеныВРасчеты'])) {
            foreach($this->messages['Займы']['ВключеныВРасчеты'] as $loan_num => $loan_data) {
                $avg_payment = explode('=', $loan_data['СреднемесячныйПлатеж' . ($loan_data['contract']['CreditCard'] ? 'К' : '')]);
                $right_part[] = implode("\t", [
                    str_replace('_', '', $loan_num),
                    $loan_data['contract']['FullCost'],
                    $loan_data['contract']['Date'],
                    $loan_data['contract']['DateEnd'],
                    $loan_data['ЧБП'] ?? '',
                    $loan_data['contract']['CreditCard'] ? '' : ($loan_data['СрЗ'] ?? ''),
                    $loan_data['contract']['CreditCard'] ? '' : $loan_data['Т'],
                    $loan_data['ПрЗ'] ?? $loan_data['ПрЗк'] ?? '',
                    '',
                    $loan_data['contract']['CreditCard'] ? $loan_data['contract']['Amount'] : '',
                    $loan_data['contract']['CreditCard'] ? ($loan_data['СрЗк'] ?? '') : '',
                    '',
                    '',
                    trim(end($avg_payment)),
                    $row->fio,
                ]);
            }
        }
        return compact('right_part', 'left_part');
    }
}
/*
$order = (new TestOrder_YTFchcrdRD(1135759))->order;
$pti = new BoostraPTI($order);

$pti->setSource();
$pti->toggleDetails(true);
var_dump($pti->getPTIData());

$order = (new TestOrder_YTFchcrdRD(1205031))->order;
$pti = new BoostraPTI($order);

$pti->setSource();
$pti->toggleDetails(true);
var_dump($pti->getPTIData());

$order = (new TestOrder_YTFchcrdRD(1236639))->order;
$pti = new BoostraPTI($order);

$pti->setSource();
$pti->toggleDetails(true);
var_dump($pti->getPTIData());

$order = (new TestOrder_YTFchcrdRD(1237071))->order;
$pti = new BoostraPTI($order);

$pti->setSource();
$pti->toggleDetails(true);
var_dump($pti->getPTIData());
*/