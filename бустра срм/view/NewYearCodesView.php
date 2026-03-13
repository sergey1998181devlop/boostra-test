<?php

require_once 'View.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class NewYearCodesView extends View
{
    public function fetch()
    {
        $items_per_page = 50;

        $filter = [
            'search' => $this->request->get('search'),
            'page' => max(1, $this->request->get('page', 'integer', 1)),
            'sort' => $this->request->get('sort', 'string', 'id_desc'),
        ];

        $codesCount = $this->newYearCodes->countParticipantCodes($filter);
        $participantCodes = $this->newYearCodes->getAllParticipantCodesWithUserInfo($filter);

        $current_page = max(1, $this->request->get('page', 'integer'));
        $totalPagesNum = ceil($codesCount / $items_per_page);

        $countsForLevels = $this->getDataForDisplay();

        $this->design->assign('count_level_1', $countsForLevels['level1']);
        $this->design->assign('count_level_2', $countsForLevels['level2']);
        $this->design->assign('items', $participantCodes);
        $this->design->assign('current_page_num', $current_page);
        $this->design->assign('total_pages_num', $totalPagesNum);
        $this->design->assign('codes_count', $codesCount);

        return $this->design->fetch('new_year_codes.tpl');
    }

    /**
     * Получить данные для отображения на странице
     *
     * @return array
     */
    private function getDataForDisplay(): array
    {
        $countsForLevels = $this->newYearCodes->getAllParticipantCodesForDisplay();

        return $countsForLevels;
    }

    /**
     * Выгрузка данных об участниках акции в Excel
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function downloadParticipantCodes(): void
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $filename = 'files/reports/new_year_participant_codes.xls';
        $activeSheet->setTitle(date('Y_m_d_H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $level = $this->request->post('level', 'integer');
        $participantCodes = $this->newYearCodes->getAllParticipantCodesForExport($level);

        $activeSheet->setCellValue('A1', 'ФИО');
        $activeSheet->setCellValue('B1', 'Номер телефона');
        $activeSheet->setCellValue('C1', 'Код участника');

        $rowNumber = 2;
        foreach ($participantCodes as $code) {
            $activeSheet->setCellValue('A' . $rowNumber, $code->lastname . ' ' . $code->firstname . ' ' . $code->patronymic);
            $activeSheet->setCellValue('B' . $rowNumber, $code->phone_mobile);
            $activeSheet->setCellValue('C' . $rowNumber, $code->code);
            $rowNumber++;
        }

        for ($i = 'A'; $i <= 'C'; $i++) {
            $activeSheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }

}