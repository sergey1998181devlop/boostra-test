<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

ini_set('max_execution_time', 600);

/**
 * Class CDMultipolisView
 */
class CDMultipolisView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        return $this->design->fetch('cd_multipolis_view.tpl');
    }

    /**
     * Выгрузка данных в Excel
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function download()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['date_range']) && !empty($_GET['date_range'])) {
            $dateRange = explode(' - ', $_GET['date_range']);
            $startDateObj = date_create_from_format('Y.m.d', $dateRange[0]);
            $endDateObj = date_create_from_format('Y.m.d', $dateRange[1]);
            $startDate = $startDateObj->format('Y-m-d');
            $endDate = $endDateObj->format('Y-m-d');

            $objPHPExcel = new PHPExcel();

            $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
            $activeSheet->setTitle('Отчет о ключах Мультиполис');

            $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Отчет о ключах Мультиполис за период:');
            $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Дата начала');
            $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Дата конца');

            $objPHPExcel->getActiveSheet()->setCellValue('B2', $startDate);
            $objPHPExcel->getActiveSheet()->setCellValue('B3', $endDate);

            $previousKeysRow = 4;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $previousKeysRow, 'Остаток ключей на начало периода:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $previousKeysRow . ':E' . $previousKeysRow)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $previousKeysRow, 'Количество шт. = ');

            // Получаем количество оставшихся ключей на начало периода
            $query = $this->db->placehold("
                    SELECT 
                    COUNT(*) as created_keys_count_start
                    FROM __multipolis
                    WHERE date_added < ?");
            $this->db->query($query, $startDate);

            $results = $this->db->result();
            $createdKeysCountStart = $results->created_keys_count_start;

            $query = $this->db->placehold("
                     SELECT 
                     COUNT(*) as sold_keys_count
                     FROM __multipolis
                     WHERE date_added <= ? 
                     AND status = 'success'");
           $this->db->query($query, $startDate);

            $results = $this->db->result();
            $soldKeysCountStart = $results->sold_keys_count;

            $remainingKeysCountStart = $createdKeysCountStart - $soldKeysCountStart;

            // Добавляем значение количества оставшихся ключей на начало периода
            if (!empty($previousKeysRow)) {
                if ($remainingKeysCountStart < 0) {
                    $remainingKeysCountStart = 0;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $previousKeysRow, $remainingKeysCountStart);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $previousKeysRow, 0);
            }

            //Поступило ключей
            $receivedKeysRow = $previousKeysRow  + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $receivedKeysRow, 'Поступило ключей:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $receivedKeysRow . ':E' . $receivedKeysRow)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $receivedKeysRow, 'Общая сумма:');

            $queryTotalAmount = $this->db->placehold("
            SELECT SUM(amount) as totalAmount 
            FROM __multipolis 
            WHERE return_status = 0
            AND DATE(date_added) BETWEEN ? AND ?");

          $this->db->query($queryTotalAmount,$startDate, $endDate);

            $row = $this->db->result('totalAmount');
            if ($row > 0) {
                $totalAmount = $row;
            } else {
                $totalAmount = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $receivedKeysRow,  $totalAmount);

            // Добавляем строки с проданными ключами в таблицу
            $querySoldKeys = $this->db->placehold("
           SELECT m.number, u.lastname, u.firstname, u.patronymic
           FROM __multipolis m
           LEFT JOIN __users u ON m.user_id = u.id
           WHERE DATE(m.date_added) BETWEEN ? AND ?");

            $soldKeys = $this->db->query($querySoldKeys, $startDate, $endDate);
            $soldKeysRow = $receivedKeysRow + 2;

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $soldKeysRow, 'Ключи мультиполиса:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $soldKeysRow . ':E' . $soldKeysRow)->getFont()->setBold(true);
            if (!empty($soldKeys)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($soldKeysRow + 1), '№');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($soldKeysRow + 1), 'Ключ:');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($soldKeysRow + 1), 'ФИО клиента:');
                $objPHPExcel->getActiveSheet()->getStyle('A' . ($soldKeysRow + 1) . ':C' . ($soldKeysRow + 1))->getFont()->setBold(true);
                $soldKeysCount = 1;
                foreach ($soldKeys as $key) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($soldKeysRow + 2), $soldKeysCount);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($soldKeysRow +  2), $key['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($soldKeysRow + 2), $key['lastname'] . ' ' . $key['firstname'] . ' ' . $key['patronymic']);
                    $soldKeysRow += 1;
                    $soldKeysCount++;
                }
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells('A' . ($soldKeysRow + 1) . ':E' . ($soldKeysRow + 1));
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($soldKeysRow + 1), 'Данные о проданных ключах не найдены.');
            }


            // Выбытие сумма
            $outflowKeysRow = $soldKeysRow + 4;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $outflowKeysRow, 'Прочее выбытие:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $outflowKeysRow . ':E' . $outflowKeysRow)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $outflowKeysRow, 'Общая сумма = ');

            $countKeys = $this->db->placehold("
             SELECT SUM(return_amount) as total
             FROM __multipolis 
             WHERE return_status = 1
             AND DATE(date_added) BETWEEN ? AND ?");

            $this->db->query($countKeys, $startDate, $endDate);
            $row = $this->db->result('total');
            if ($row > 0) {
                $totalAmountOutflow = $row;
            } else {
                $totalAmountOutflow = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $outflowKeysRow,  $totalAmountOutflow);


            // Добавляем строки с возвращенными ключами в таблицу
            $queryReturnedKeys = $this->db->placehold("
           SELECT m.number, u.lastname, u.firstname, u.patronymic
           FROM __multipolis m
           LEFT JOIN __users u ON m.user_id = u.id
           WHERE m.return_status = 1 
           AND DATE(m.date_added) BETWEEN ? AND ?");

            $returnedKeys = $this->db->query($queryReturnedKeys,$startDate,$endDate);

            $returnedKeysRow = $outflowKeysRow + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $returnedKeysRow, 'Возвращено ключей:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $returnedKeysRow . ':E' . $returnedKeysRow)->getFont()->setBold(true);

            if (!empty($returnedKeys)) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($returnedKeysRow + 1), '№');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($returnedKeysRow + 1), 'Ключ:');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($returnedKeysRow + 1), 'ФИО клиента:');
                $objPHPExcel->getActiveSheet()->getStyle('A' . ($returnedKeysRow + 1) . ':C' . ($returnedKeysRow + 1))->getFont()->setBold(true);
                $returnedKeysCount = 1;
                foreach ($returnedKeys as $key) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . ($returnedKeysRow + 1 + $returnedKeysCount), $returnedKeysCount);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . ($returnedKeysRow + 1 + $returnedKeysCount), $key['number']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . ($returnedKeysRow + 1 + $returnedKeysCount), $key['lastname'] . ' ' . $key['firstname'] . ' ' . $key['patronymic']);

                    $returnedKeysCount++;
                }
            } else {
                $objPHPExcel->getActiveSheet()->mergeCells('A' . ($returnedKeysRow + 1) . ':E' . ($returnedKeysRow + 1));
                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($returnedKeysRow + 1), 'Данные о возвращенных ключах не найдены.');
            }

            //Остатки ключей на конец периода
            $remainingKeysRow = $returnedKeysRow + 4;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $remainingKeysRow, 'Остаток ключей на конец периода:');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $remainingKeysRow . ':E' . $remainingKeysRow)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $remainingKeysRow, 'Сумма = ');

            $soldKeys = $this->db->placehold("
                    SELECT 
                    COUNT(*) as sold_keys_count
                    FROM __multipolis
                    WHERE return_status = 0
                    AND status = 'success'
                    AND DATE(date_added) BETWEEN ? AND ?");
            $this->db->query($soldKeys, $startDate, $endDate);
            $soldKeysCont = $this->db->result('sold_keys_count');

            $returnKeys = $this->db->placehold("
                    SELECT 
                    COUNT(*) as return_keys_count
                    FROM __multipolis
                    WHERE return_status = 1
                    AND DATE(date_added) BETWEEN ? AND ?");

            $this->db->query($returnKeys, $startDate, $endDate);
            $returnKeysCount = $this->db->result('return_keys_count');

            $results = $remainingKeysCountStart + $totalAmount - $soldKeysCont - $totalAmountOutflow + $returnKeysCount;

            if ($results > 0) {
                $totalResults = $results;
            } else {
                $totalResults = 0;
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $remainingKeysRow,  $totalResults);


            foreach(range('A','C') as $columnLetter){
                $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            $filename = 'files/reports/multipolis_cd_report.xls';
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($this->config->root_dir . $filename);
            header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
            exit;
        }
    }

}
