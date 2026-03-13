<?php

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

chdir('..');

//require 'api/Simpla.php';
require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class GenerateReportTickets extends Simpla
{
    private string $path = 'files/tickets/report/';
//    private string $path = 'files/';

    public function run(array $dates)
    {
        $arrayRep = $this->generate($dates);

        return $this->generate_file($arrayRep);
    }

    /**
     * Generate report
     *
     * @param $dataPost
     * @return array
     */
    private function generate($dataPost): array
    {
        $dataDays = []; // даты -> Канал -> Тема -> Решение
        $channels_ids = [];
        $subjects_ids = [];
        $results_ids = [];
        $max_results = 0;

        $dates = $this->listDaysReportTicket($dataPost);

        // перебираем все даты
        foreach ($dates as $key => $date) {
            $dataDays[$key]["data"] = $date; // просто закидываем дату
            $dataDays[$key]["count"] = $this->get__channels_count_report($date); // общее кол-во обращений
            $dataDays[$key]["channels"] = $this->get__channels_report($date); // тут возвращаем все каналы коммуникации

            foreach ($dataDays[$key]["channels"] as $key_chanel => $channel_id) {
                $array_subjects = $this->get__subjects_report($channel_id, $date); // массив тем по каналу обращения

                $channels_ids[] = $channel_id;

                // пустые массивы - не наш конёк
                if (count($array_subjects) < 1) {
                    unset($dataDays[$key]["channels"][$key_chanel]);
                    continue;
                }

                unset($dataDays[$key]["channels"][$key_chanel]); // сразу удалим по ключу
                $dataDays[$key]["channels"][$key_chanel]["id"] = $channel_id; // перенесём ключ суда
                $dataDays[$key]["channels"][$key_chanel]["subjects"] = $array_subjects;


                foreach ($dataDays[$key]["channels"][$key_chanel]["subjects"] as $key_subject => $subject) {
                    unset($dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]);

                    $subjects_ids[] = $subject;

                    $dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]["id"] = $subject;
                    $dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]["results"] = $this->get__results_report($subject, $date, $channel_id);

                    $count_res = count($dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]["results"]);
                    $max_results = max($count_res, $max_results);


                    foreach ($dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]["results"] as $result) {
                        $results_ids[] = $result->id;
                        $dataDays[$key]["channels"][$key_chanel]["subjects"][$key_subject]["count"] += $result->result_count;
                    }
                }
            }
        }

        return [
            'days' => $dataDays,
            'channels' => array_unique($channels_ids),
            'subjects' => array_unique($subjects_ids),
            'results' => array_unique($results_ids),
            'max_results' => $max_results,
        ];
    }


    /**
     * @return void
     */
    private function listDaysReportTicket($dataPost): array
    {
        $filterPlaceholder = $dataPost["filter"] ? 'AND created_at >= "' . $dataPost["filter"]["filter_date_start"] . '" AND  created_at <= "' . $dataPost["filter"]["filter_date_end"]. '"' : '';
        $queryDates = $this->db->placehold(
            "
          SELECT CAST(created_at AS DATE) as date
            FROM __mytickets
            WHERE 1 $filterPlaceholder 
            GROUP BY CAST(created_at AS DATE  ) ORDER BY created_at DESC
        "
        );

        $this->db->query($queryDates);

        return json_decode(json_encode($this->db->results('date')), true);
    }


    /**
     *
     * @param string $date
     * @return array
     */
    private function get__channels_report(string $date): array
    {
        $dateSt = date("Y-m-d 00:00:00", strtotime($date));
        $dateFn = date("Y-m-d 23:59:59", strtotime($date));

        $queryDates = $this->db->placehold(
            "
          SELECT chanel_id
            FROM s_mytickets
        where '$dateSt' < created_at  AND created_at < '$dateFn'
            GROUP BY chanel_id
        "
        );

        $this->db->query($queryDates);

        return json_decode(json_encode($this->db->results('chanel_id')), true);
    }


    /**
     * @param string $channel_id
     * @param string $date
     * @return array
     */
    private function get__subjects_report(string $channel_id, string $date): array
    {
        $dateSt = date("Y-m-d 00:00:00", strtotime($date));
        $dateFn = date("Y-m-d 23:59:59", strtotime($date));

        $queryDates = $this->db->placehold(
            "
          SELECT subject_id
            FROM s_mytickets
        where '$dateSt' < created_at  AND created_at < '$dateFn' AND chanel_id = $channel_id
            GROUP BY subject_id
        "
        );

        $this->db->query($queryDates);

        return json_decode(json_encode($this->db->results('subject_id')), true);
    }

    /**
     * @param string $date
     * @return string
     */
    private function get__channels_count_report(string $date): string
    {
        $dateSt = date("Y-m-d 00:00:00", strtotime($date));
        $dateFn = date("Y-m-d 23:59:59", strtotime($date));

        $queryDates = $this->db->placehold(
            "
          SELECT count(id) as count_items
            FROM s_mytickets
        where '$dateSt' < created_at  AND created_at < '$dateFn'
    
        "
        );

        $this->db->query($queryDates);

        return $this->db->result('count_items');
    }

    /**
     * @param string $subject
     * @param string $date
     * @param string $channel_id
     * @return array
     */
    private function get__results_report(string $subject_id, string $date, string $channel_id): array
    {
        $dateSt = date("Y-m-d 00:00:00", strtotime($date));
        $dateFn = date("Y-m-d 23:59:59", strtotime($date));

        $queryDates = $this->db->placehold(
            "
                SELECT IFNULL(result_id, 0) as id, count(*) as result_count
                FROM s_mytickets
                WHERE '$dateSt' < created_at  AND created_at < '$dateFn'
                  AND chanel_id = $channel_id
                  AND subject_id = $subject_id
                GROUP BY result_id
        "
        );

        $this->db->query($queryDates);

        return $this->db->results(['result_id', 'result_count']);
    }

    /**
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function generate_file(array $arrayRep)
    {
        $subj = $this->getSubjects($arrayRep["subjects"], '__mytickets_subjects');
        $сhan = $this->getSubjects($arrayRep["channels"], '__mytickets_channels');
        $resl = $this->getSubjects($arrayRep["results"], '__mytickets_results');

        $resl[0] = 'Не выбрано';


        $row = 2;
        $row_2 = 2;
        $row_3 = 2;
        $cell = 'A';
        $cell_2 = 'A';
        $cell_3 = 'A';
        $cell_2++;
        $cell_3++;
        $cell_3++;
        $count_rows = 0;

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $active_sheet = $excel->getActiveSheet();


        $bg = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'D9D9D9')
            )
        );

        $active_sheet->getColumnDimensionByColumn("A")->setAutoSize(true);
        $active_sheet->getColumnDimensionByColumn("B")->setAutoSize(true);

        $active_sheet->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle("A")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $active_sheet->getStyle("B")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle("B")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


        // даты
        foreach ($arrayRep["days"] as $key_day => $dayItem) {
            foreach ($dayItem["channels"] as $channel) {
                $count_rows += (count($channel["subjects"]) * 2) + 1;
            }
            $row_main = $count_rows + 1;

            // bg color
            $active_sheet->getStyle($cell . $row)->applyFromArray($bg);
            $active_sheet->getStyle($cell_2 . $row_2)->applyFromArray($bg);


            // Объединение ячеек в колонке
            $active_sheet->setCellValue($cell . $row++, "Дата");
            $active_sheet->setCellValue($cell_2 . $row_2++, "Общее");


            // запись даты
            $active_sheet->mergeCells($cell . $row++ . ':' . $cell . $row_main);
            $active_sheet->setCellValue($cell . ($row - 1), $dayItem["data"]);

            // запись даты
            $active_sheet->mergeCells($cell_2 . $row_2 . ':' . $cell_2 . $row_main);
            $active_sheet->setCellValue($cell_2 . ($row_2), $dayItem["count"]);


            $row_channel = $row - 2;

            foreach ($dayItem["channels"] as $channel) {
                $cell_time = $cell_3;
                $count_subjects = count($channel);

                for ($x = 0; $x <= $arrayRep["max_results"] - 1; $x++) {
                    $cell_time++;
                }

                $active_sheet->getStyle($cell_3 . $row_channel)->applyFromArray($bg);
                $active_sheet->mergeCells($cell_3 . $row_channel . ':' . $cell_time . $row_channel);
                $active_sheet->setCellValue($cell_3 . $row_channel, $сhan[$channel["id"]]);

                $cell_subject = $cell_3;
                $row_subject = $row_channel + 2;

                foreach ($channel["subjects"] as $subject) {
                    $active_sheet->setCellValue($cell_subject . ($row_subject - 1), $subj[$subject["id"]]);
                    $active_sheet->setCellValue($cell_subject . $row_subject++, $subject["count"]);

                    $cel_result = $cell_subject;
                    $row_result = $row_subject;

                    foreach ($subject["results"] as $result) {
                        $cel_result++;
                        $row_result = $row_result - 1;

                        $active_sheet->setCellValue($cel_result . ($row_result - 1), $resl[$result->id]);
                        $active_sheet->setCellValue($cel_result . $row_result++, $result->result_count);
                    }

                    $row_subject++;
                }

                $row_channel = $row_channel + (count($channel["subjects"]) * 2) + 1;
            }

            $row = $row_main + 1;
            $row_2 = $row_main + 1;
        }


//        $this->generateFileSettings($active_sheet, 'A');


        // получение массива данных orders
//        $results_to_write = $this->dataChunks;


        // генератор
//        foreach ($results_to_write as $line) {
//            // тут функция для записи
//            $this->writeValues($active_sheet, $line, $row++);
//        }

        for ($i = 'A'; $i <= 'AQ'; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        // генерация названия файла
        $filename = $this->generateFileName();

        //создание файла и запись
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);


        return ["success" => true, "file" => $filename];
    }

    /**
     * Generate file name
     * @return string
     */
    private function generateFileName()
    {
        return $this->path . date('Y') . '____' . date('Y_m_d_H_i_s') . '.xls';
    }

    /**
     * Получить темы обращения
     *
     * @return array
     */
    public function getSubjects($ids = ["0"], $table)
    {
        $res_ret = [];


        if (count((array) $ids) < 1){
            return ['0'];
        }

        $query = $this->db->placehold(
            "
        SELECT *
        FROM $table
        WHERE  id IN (?@)",
            (array)$ids
        );

        $this->db->query($query);
        $ids_new = $this->db->results(['id', 'name']);

        foreach ($ids_new as $item) {
            $res_ret[$item->id] = $item->name;
        }

        return (array)$res_ret;
    }

}