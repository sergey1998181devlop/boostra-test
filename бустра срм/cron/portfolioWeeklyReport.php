<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

require_once __DIR__ . '/../api/Simpla.php';
require_once __DIR__ . '/../scorings/BoostraPTI.php';
require_once __DIR__ . '/../PHPExcel/Classes/PHPExcel.php';

define('REPORTS_DIR', 'files/reports/');

class ActiveLoansReport extends Simpla
{
    public function process() {

        $query  = $this->db->placehold("SELECT zaim_number
                                        FROM __user_balance
                                        WHERE
                                            ostatok_od > 0
                                            AND zaim_summ >= 10000");
        $this->db->query($query);
        $db_results = $this->db->results('zaim_number');
        
        $loaded  = [];
        $chunks  = array_chunk($db_results, 200);
        foreach($chunks as $chunk) {
            $data = ['ArrayContracts' => json_encode($chunk)];
            $object   = $this->soap->generateObject($data);
            $response = $this->soap->requestSoap($object, 'WebSignal', 'ZaymUID');
            foreach($response['response'] as $pair) {
                $loaded[$pair['Номер']] = $pair['УИД'];
            }
        }

        $cessions = [];
        $chunks   = array_chunk($db_results, 200);
        foreach($chunks as $chunk) {
            $data = ['ArrayContracts' => json_encode($chunk)];
            $object   = $this->soap->generateObject($data);
            $response = $this->soap->requestSoap($object, 'WebSignal', 'CessionsContracts');
            foreach($chunk as $num) {
                $cessions[$num] = empty($response['response']) ? false : in_array($num, $response['response']);
            }
        }

        $closed = [];
        $chunks = array_chunk($db_results, 200);
        foreach($chunks as $chunk) {
            $data = ['ArrayContracts' => json_encode($chunk)];
            $object   = $this->soap->generateObject($data);
            $response = $this->soap->requestSoap($object, 'WebSignal', 'CloseContracts');
            foreach($chunk as $num) {
                $closed[$num] = empty($response['response']) ? false : in_array($num, $response['response']);
            }
        }

        $query = "SELECT
                        base.*,
                        CAST(base.zaim_date AS DATE) zaim_date_only,
                        (SELECT CAST(sc.end_date AS DATE)
                            FROM s_scorings sc
                            WHERE sc.order_id = base.oid
                            AND sc.`type` = ".$this->scorings::TYPE_SCORISTA."
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                            ORDER BY id DESC
                            LIMIT 1) scorista,
                        (SELECT scorista_id
                            FROM s_scorings sc
                            WHERE sc.order_id = base.oid
                            AND sc.`type` = ".$this->scorings::TYPE_SCORISTA."
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                            ORDER BY id DESC
                            LIMIT 1) scorista_id,
                        IFNULL(JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                                    FROM s_scorings sc
                                    WHERE sc.order_id = base.oid
                                        AND sc.`type` = ".$this->scorings::TYPE_SCORISTA."
                                        AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                                    ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.pti.result'),
                                JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                                    FROM s_scorings_old sc
                                    WHERE sc.order_id = base.oid
                                        AND sc.`type` = 'scorista'
                                        AND sc.`status` = 'completed'
                                    ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.pti.result')) pti_rosstat,
                        CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) fio,
                        u.birth birth_date,
                        REPLACE(REPLACE(u.passport_serial, ' ', ''), '-', '') passport,
                        u.Regregion,
                        u.income_base
                    FROM (SELECT
                                sub.user_id,
                                sub.zaim_number,
                                sub.zaim_summ,
                                sub.percent,
                                CAST(sub.zaim_date AS DATETIME) zaim_date,
                                CAST(substring_index(GROUP_CONCAT(o.id ORDER BY o.id DESC), ',', 1) AS UNSIGNED) oid,
                                substring_index(GROUP_CONCAT(o.`period` ORDER BY o.id DESC), ',', 1) term,
                                substring_index(GROUP_CONCAT(o.`1c_id` ORDER BY o.id DESC), ',', 1) onum,
                                substring_index(GROUP_CONCAT(o.order_uid ORDER BY o.id DESC), ',', 1) ouid
                            FROM s_user_balance sub
                            LEFT JOIN s_orders o
                                ON o.user_id = sub.user_id
                                AND o.approve_date IS NOT NULL
                                AND o.approve_date <= CAST(sub.zaim_date AS DATETIME)
                            WHERE sub.ostatok_od > 0
                                AND sub.zaim_summ >= 10000
                            GROUP BY sub.user_id, zaim_date, zaim_number, zaim_summ, percent) base
                    JOIN s_users u
                        ON base.user_id = u.id";
        $this->db->query($query);
        $all_loans = $this->db->results();
        $output = [];
        foreach($all_loans as $row) {
            if(!empty($cessions[$row->zaim_number]) || !empty($closed[$row->zaim_number])) {
                continue;
            }
            $output[] = $row;
        }
        $csv = [[
            'Номер займа',
            'Номер заявки',
            'УУИД',
            'Дата займа',
            'Номер займа',
            'Дата скористы',
            'ФИО',
            'Дата рождения',
            'Номер паспорта',
            'Регион',
            'Сумма займа',
            'Процент',
            'Агрид скористы',
            'ПДН Росстат',
        ]];
        foreach($output as $row) {
            $csv[] = [
                $row->zaim_number,
                $row->onum,
                empty($loaded[$row->zaim_number]) ? '' : $loaded[$row->zaim_number],
                $row->zaim_date_only,
                $row->zaim_number,
                $row->scorista,
                $row->fio,
                $row->birth_date,
                $row->passport,
                $row->Regregion,
                number_format((float)$row->zaim_summ, 2, ',', ''),
                number_format((float)$row->percent, 2, ',', ''),
                $row->scorista_id,
                number_format((float)$row->pti_rosstat, 3, ',', ''),
            ];
        }
        $filename = REPORTS_DIR . 'active_loans_' . (new \DateTime)->format('Ymd') . '.xls';
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $active_sheet->setTitle((new \DateTime)->format('Ymd'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->fromArray($csv, null, 'A1');

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save(__DIR__ . '/../' . $filename);
        
        $report_name = (new \DateTime)->format('Y-m-d');
        $this->db->query("INSERT INTO s_local_storage (type, name, path) VALUES ('pdn_remains', '$report_name', '$filename')");
    }
}

class QuarterlyLoansReport extends Simpla
{
    public function process()
    {
        $quarter_month = str_pad(floor(((int)(new \DateTime)->format('m') - 1) / 3) * 3 + 1, 2, '0', STR_PAD_LEFT);
        
        $query  = $this->db->placehold("
            SELECT
                'ID заемщика'
                ,'Номер займа'
                ,'Сумма займа'
                ,'Дата выдачи'
                ,'ID заявки'
                ,'Процент'
                ,'Срок'
                ,'Номер заявки 1С'
                ,'УУИД заявки'
                ,'ПДН'
                ,'Дата скористы'
                ,'Агрид Скористы'
                ,'ПДН Скориста'
                ,'ФИО заемщика'
                ,'Дата рождения'
                ,'Паспорт'
                ,'Регион'
                ,'Доход по заявке'
            UNION ALL
            SELECT
               base.*,
               IFNULL((SELECT CAST(sc.end_date AS DATE)
                        FROM s_scorings sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                        ORDER BY id DESC
                        LIMIT 1),
                      (SELECT CAST(sc.end_date AS DATE)
                        FROM s_scorings_old sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN ('scorista', 'axilink')
                            AND sc.`status` = 'completed'
                        ORDER BY id DESC
                        LIMIT 1)) scorista,
               IFNULL((SELECT scorista_id
                        FROM s_scorings sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                        ORDER BY id DESC
                        LIMIT 1),
                      (SELECT scorista_id
                        FROM s_scorings_old sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN ('scorista', 'axilink')
                            AND sc.`status` = 'completed'
                        ORDER BY id DESC
                        LIMIT 1)) scorista_id,
               REPLACE(REPLACE(IFNULL((SELECT
                                    JSON_EXTRACT(CONCAT('[', IFNULL(body, ''), ']'),
                                                 IF(sc.`type` = ".$this->scorings::TYPE_SCORISTA.",
                                                    '$[0].additional.pti_RosStat.pti.result',
                                                    '$[0].pdn'))
                                FROM s_scorings sc
                                WHERE sc.order_id = base.oid
                                    AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                                    AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                                ORDER BY id DESC LIMIT 1),
                              (SELECT
                                    JSON_EXTRACT(CONCAT('[', IFNULL(body, ''), ']'),
                                                 IF(sc.`type` = 'scorista',
                                                    '$[0].additional.pti_RosStat.pti.result',
                                                    '$[0].pdn'))
                                FROM s_scorings_old sc
                                WHERE sc.order_id = base.oid
                                    AND sc.`type` IN ('scorista', 'axilink')
                                    AND sc.`status` = 'completed'
                                ORDER BY id DESC LIMIT 1)), '.', ','), '\"', '') pti_rosstat,
               CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) fio,
               u.birth birth_date,
               REPLACE(REPLACE(u.passport_serial, ' ', ''), '-', '') passport,
               u.Regregion,
               u.income_base
            FROM (
                SELECT
                    all_loans.id user_id,
                    all_loans.loan_num,
                    all_loans.loan_sum,
                    CAST(all_loans.loan_date AS DATE) zaim_date_only,
                    CAST(substring_index(GROUP_CONCAT(o.id ORDER BY o.id DESC), ',', 1) AS UNSIGNED) oid,
                    REPLACE(substring_index(GROUP_CONCAT(o.percent ORDER BY o.id DESC), ',', 1), '.', ',') percent,
                    substring_index(GROUP_CONCAT(o.`period` ORDER BY o.id DESC), ',', 1) term,
                    substring_index(GROUP_CONCAT(o.`1c_id` ORDER BY o.id DESC), ',', 1) onum,
                    substring_index(GROUP_CONCAT(o.order_uid ORDER BY o.id DESC), ',', 1) ouid,
                    substring_index(GROUP_CONCAT(o.pti_loan ORDER BY o.id DESC), ',', 1) pti_our
                FROM (
                    SELECT
                        CAST(REPLACE(REPLACE(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].date')), 'T', ' '), '\"', '') AS DATETIME) loan_date,
                        REPLACE(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].number')), '\"', '') loan_num,
                        JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].amount')) loan_sum,
                        id
                    FROM s_users, (SELECT @indx := @indx + 1 AS indx FROM s_reasons tmp WHERE @indx < 100) indxs
                    WHERE
                        ifnull(s_users.loan_history, '[]') <> '[]'
                        AND JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].number')) IS NOT NULL
                        AND REPLACE(SUBSTRING_INDEX(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].date')), 'T', 1), '\"', '') >= ?
                        AND JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].amount')) * 1 >=10000
                    ORDER BY loan_date) all_loans
                    LEFT JOIN s_orders o
                        ON o.user_id = all_loans.id
                        AND o.approve_date IS NOT NULL
                        AND o.approve_date <= all_loans.loan_date
                    GROUP BY all_loans.id, all_loans.loan_num, all_loans.loan_sum, all_loans.loan_date
                    HAVING oid IS NOT NULL) base
            JOIN s_users u
                ON base.user_id = u.id", (new \DateTime)->format("Y-$quarter_month-01"));
        $this->db->query('SET @indx := -1');
        $this->db->query($query);
        $db_results = array_map(fn($item) => array_values((array)$item), $this->db->results());

        $filename = REPORTS_DIR . 'quarterly_loans_' . (new \DateTime)->format('Ymd') . '.xls';
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $active_sheet->setTitle((new \DateTime)->format('Ymd'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->fromArray($db_results, null, 'A1');

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save(__DIR__ . '/../' . $filename);
        
        $report_name = (new \DateTime)->format('Y-m-d');
        $this->db->query("INSERT INTO s_local_storage (type, name, path) VALUES ('pdn_quarterly', '$report_name', '$filename')");
    }
}

class LoansWithRosstatReport extends Simpla
{
    public function process()
    {
        $query  = $this->db->placehold("
            SELECT
                'ID заемщика'
                ,'Номер займа'
                ,'Сумма займа'
                ,'Дата выдачи'
                ,'Дата погашения'
                ,'ID заявки'
                ,'Процент'
                ,'Срок'
                ,'Номер заявки 1С'
                ,'УУИД заявки'
                ,'ПДН'
                ,'Дата скористы'
                ,'Агрид Скористы'
                ,'ПДН Скориста'
                ,'Месячный платеж'
                ,'Платеж по займу'
                ,'Доход Росстат'
                ,'ФИО заемщика'
                ,'Дата рождения'
                ,'Паспорт'
                ,'Регион'
                ,'Доход по заявке'
            UNION ALL
            SELECT
               base.*,
               IFNULL((SELECT CAST(sc.end_date AS DATE)
                        FROM s_scorings sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                        ORDER BY id DESC
                        LIMIT 1),
                      (SELECT CAST(sc.end_date AS DATE)
                        FROM s_scorings_old sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN ('scorista', 'axilink')
                            AND sc.`status` = 'completed'
                        ORDER BY id DESC
                        LIMIT 1)) scorista,
               IFNULL((SELECT scorista_id
                        FROM s_scorings sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                            AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                        ORDER BY id DESC
                        LIMIT 1),
                      (SELECT scorista_id
                        FROM s_scorings_old sc
                        WHERE sc.order_id = base.oid
                            AND sc.`type` IN ('scorista', 'axilink')
                            AND sc.`status` = 'completed'
                        ORDER BY id DESC
                        LIMIT 1)) scorista_id,
               REPLACE(REPLACE(IFNULL((SELECT
                                    JSON_EXTRACT(CONCAT('[', IFNULL(body, ''), ']'),
                                                 IF(sc.`type` = ".$this->scorings::TYPE_SCORISTA.",
                                                    '$[0].additional.pti_RosStat.pti.result',
                                                    '$[0].pdn'))
                                FROM s_scorings sc
                                WHERE sc.order_id = base.oid
                                    AND sc.`type` IN (".$this->scorings::TYPE_SCORISTA.", ".$this->scorings::TYPE_AXILINK.")
                                    AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                                ORDER BY id DESC LIMIT 1),
                              (SELECT
                                    JSON_EXTRACT(CONCAT('[', IFNULL(body, ''), ']'),
                                                 IF(sc.`type` = 'scorista',
                                                    '$[0].additional.pti_RosStat.pti.result',
                                                    '$[0].pdn'))
                                FROM s_scorings_old sc
                                WHERE sc.order_id = base.oid
                                    AND sc.`type` IN ('scorista', 'axilink')
                                    AND sc.`status` = 'completed'
                                ORDER BY id DESC LIMIT 1)), '.', ','), '\"', '') pti_rosstat,
               REPLACE(IFNULL(JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                            FROM s_scorings sc
                            WHERE sc.order_id = base.oid
                                AND sc.`type` = ".$this->scorings::TYPE_SCORISTA."
                                AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                            ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.monthPayment.result'),
                        JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                            FROM s_scorings_old sc
                            WHERE sc.order_id = base.oid
                                AND sc.`type` = 'scorista'
                                AND sc.`status` = 'completed'
                            ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.monthPayment.result')), '.', ',') pti_monthPayment,
               REPLACE(IFNULL(JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                            FROM s_scorings sc
                            WHERE sc.order_id = base.oid
                                AND sc.`type` = ".$this->scorings::TYPE_SCORISTA."
                                AND sc.`status` = ".$this->scorings::STATUS_COMPLETED."
                            ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.payment.result'),
                        JSON_EXTRACT(CONCAT('[', IFNULL((SELECT body
                            FROM s_scorings_old sc
                            WHERE sc.order_id = base.oid
                                AND sc.`type` = 'scorista'
                                AND sc.`status` = 'completed'
                            ORDER BY id DESC LIMIT 1), '[]'), ']'), '$[0].additional.pti_RosStat.payment.result')), '.', ',') pti_ourPayment,
               0, 
               CONCAT(u.lastname, ' ', u.firstname, ' ', u.patronymic) fio,
               u.birth birth_date,
               REPLACE(REPLACE(u.passport_serial, ' ', ''), '-', '') passport,
               u.Regregion,
               u.income_base
            FROM (
                SELECT
                    all_loans.id user_id,
                    all_loans.loan_num,
                    all_loans.loan_sum,
                    CAST(all_loans.loan_date AS DATE) zaim_date_only,
                    all_loans.close_date,
                    CAST(substring_index(GROUP_CONCAT(o.id ORDER BY o.id DESC), ',', 1) AS UNSIGNED) oid,
                    REPLACE(substring_index(GROUP_CONCAT(o.percent ORDER BY o.id DESC), ',', 1), '.', ',') percent,
                    substring_index(GROUP_CONCAT(o.`period` ORDER BY o.id DESC), ',', 1) term,
                    substring_index(GROUP_CONCAT(o.`1c_id` ORDER BY o.id DESC), ',', 1) onum,
                    substring_index(GROUP_CONCAT(o.order_uid ORDER BY o.id DESC), ',', 1) ouid,
                    substring_index(GROUP_CONCAT(o.pti_loan ORDER BY o.id DESC), ',', 1) pti_our
                FROM (
                    SELECT
                        CAST(REPLACE(REPLACE(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].date')), 'T', ' '), '\"', '') AS DATETIME) loan_date,
                        REPLACE(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].number')), '\"', '') loan_num,
                        JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].amount')) loan_sum,
                        CAST(REPLACE(REPLACE(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].close_date')), 'T', ' '), '\"', '') AS DATE) close_date,
                        id
                    FROM s_users, (SELECT @indx := @indx + 1 AS indx FROM s_reasons tmp WHERE @indx < 100) indxs
                    WHERE
                        ifnull(s_users.loan_history, '[]') <> '[]'
                        AND JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].number')) IS NOT NULL
                        AND REPLACE(SUBSTRING_INDEX(JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].date')), 'T', 1), '\"', '') >= ?
                        AND JSON_EXTRACT(s_users.loan_history, CONCAT('$[', indxs.indx, '].amount')) * 1 >=10000
                    ORDER BY loan_date) all_loans
                    LEFT JOIN s_orders o
                        ON o.user_id = all_loans.id
                        AND o.approve_date IS NOT NULL
                        AND o.approve_date <= all_loans.loan_date
                    GROUP BY all_loans.id, all_loans.loan_num, all_loans.loan_sum, all_loans.loan_date
                    HAVING oid IS NOT NULL) base
            JOIN s_users u
                ON base.user_id = u.id", '2023-04-01');
        $this->db->query('SET @indx := -1');
        $this->db->query($query);
        $db_results = array_map(fn($item) => array_values((array)$item), $this->db->results());
        /*
        $header = array_shift($db_results);
        $header[] = 'Месячный платеж';
        $header[] = 'Платеж по займу';
        $header[] = 'Наш ПДН';
        foreach($db_results as &$loan) {
            $order = $this->orders->get_order($loan[5]);
            $pti   = new BoostraPTI($order);
            $pti->setSource();
            $pti_data = $pti->getPTIData();
            $loan[14] = ceil($pti_data['rosstat_salary'] / 1000) * 1000;
            $loan[] = $pti_data['total_debt_avg'] ;
            $loan[] = $pti_data['loan_debt_avg'] ;
            $loan[] = $pti_data['rosstat_pti'] ;
        }
        array_unshift($db_results, $header);
        */
        $filename = REPORTS_DIR . 'loans_with_rosstat' . (new \DateTime)->format('Ymd') . '.xls';
        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();

        $active_sheet->setTitle((new \DateTime)->format('Ymd'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet->fromArray($db_results, null, 'A1');

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save(__DIR__ . '/../' . $filename);
    }
}

(new ActiveLoansReport)->process();
(new QuarterlyLoansReport)->process();
#(new LoansWithRosstatReport)->process();
