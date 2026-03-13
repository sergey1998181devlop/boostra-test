<?php

namespace api\traits;

use XLSXWriter;

/**
 * Трейт для выгрузки в Excel воронок
 */
trait FunnelLoansReportTrait
{
    /**
     * Записывает строки рекурсией
     * @param XLSXWriter $writer
     * @param array $row_data
     * @param array $keys_parent
     * @param string $sheet_name
     * @return void
     */
    private function setRow(XLSXWriter $writer, array $row_data,  $keys_parent, string $sheet_name)
    {
        if (isset($row_data['webmaster_id']) || isset($row_data['total_links'])){
            $row = array_merge($keys_parent, $row_data);

            $writer->writeSheetRow($sheet_name, $row);
        }
        elseif (isset($row_data['items'])) {
            $row = array_merge($keys_parent, $row_data['items']);
            $writer->writeSheetRow($sheet_name, $row);

            $row = array_merge($keys_parent, $row_data['cv']);
            $writer->writeSheetRow($sheet_name, $row, ['color' => '#FFD359']);
        } else {
            foreach ($row_data as $key => $data) {
                $this->setRow($writer, $data, array_merge($keys_parent, [$key]), $sheet_name);
            }
        }
    }

    private function download($data = [])
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $filename = 'files/reports/' . ($data['filter_data']['filter_date_start'] . "_" . $data['filter_data']['filter_date_end']) . '_download_funnel_crm.xls';
        $sheet_name = $data['filter_data']['filter_date_start'] . "_" . $data['filter_data']['filter_date_end'];

        $writer = new XLSXWriter();
        $header = array_combine($data['fields_name'], array_fill(0, count($data['fields_name']), 'integer'));
        if (isset($data['client'])) {
            $header['Дата'] = 'string';
        }

        $writer->writeSheetHeader($sheet_name, $header);

            if (isset($data['client'])) {
                foreach ($data['results'] as $key => $result) {
                    foreach ($result as  $r) {
                        $array = get_object_vars($r);
                        $this->setRow($writer, $array, [$key], $sheet_name);
                    }
                }
                array_unshift($data['totals'],'');
                $this->setRow($writer, $data['totals'], ['Всего'], $sheet_name);
            }else{
                foreach ($data['results'] as $key => $result) {
                    $this->setRow($writer, $result, [$key], $sheet_name);
                }
            }

        $max_step = count($data['fields_name']) - count($this->totals['items']);

        foreach ($this->totals as $total) {
            for ($i = 0; $i < $max_step; $i++) {
                array_unshift($total, '');
            }

            $writer->writeSheetRow($sheet_name, $total, [
                'font-size' => 8,
                'font-style' => 'bold',
                'border' => 'left, right, top, bottom',
                'color' => '#8FD14F',
            ]);
        }

        $writer->writeToFile($this->config->root_dir . '/' . $filename);
        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }
}
