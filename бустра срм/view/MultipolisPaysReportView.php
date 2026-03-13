<?php

use api\DbModels\CreditDoctorPaysReportDbModel;
use api\DbModels\MultipolisPaysReportDbModel;
use api\DbModels\TvMedicalPaysReportDbModel;
use api\interfaces\AdditionalPaysDbReportInterface;

require_once 'View.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

ini_set('max_execution_time', 180);
ini_set('memory_limit', -1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

class MultipolisPaysReportView extends View
{
    /**
     * @var AdditionalPaysDbReportInterface
     */
    private $db_model;

    private $active_row_number = 0;

    /**
     * @var XLSXWriter
     */
    private $writer;

    /**
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->request->get('ajax')) {
            $pays = $this->getResults();
            $this->design->assign('pays', $pays);
        }

        if ($this->request->get('action', 'string') === 'download') {
            $this->download();
        }

        $this->design->assign('title', 'Отчёт о движении допа Мультиполис');
        return $this->design->fetch('multipolis_pays_report_view.tpl');
    }

    /**
     * @param AdditionalPaysDbReportInterface $model_class
     * @return void
     */
    private function setDbModel(AdditionalPaysDbReportInterface $model_class)
    {
        $this->db_model = $model_class;
    }

    /**
     * Генерируем данные
     * @return array
     * @throws Exception
     */
    public function getResults(): array
    {
        $pays = [];

        $filter_data = $this->getFilterData();

        $this->setDbModel(new MultipolisPaysReportDbModel());
        $multipolis_pays_data = $this->db_model->getPays($filter_data);

        $this->setDbModel(new TvMedicalPaysReportDbModel());
        $tv_medical_pays_data = $this->db_model->getPays($filter_data);

        $this->setDbModel(new CreditDoctorPaysReportDbModel());
        $credit_doctor_pays_data = $this->db_model->getPays($filter_data);

        $data = array_merge($multipolis_pays_data, $tv_medical_pays_data, $credit_doctor_pays_data);

        usort($data, function($a, $b){
            return $a->date_added > $b->date_added;
        });

        foreach ($data as $row) {
            $pays[] = (object)[
                'name_pay' => $row->name_pay,
                'fio' => trim($row->fio),
                'contract_number' => $row->contract_number,
                'multipolis_key' => $row->product_key,
                'action_variants' => $this->getAmountsForActionVariants($row),
                'pays_detail' => (object)[
                    'sale_not_return' => [
                        'date' => $row->pay_date,
                        'operation_id' => $row->pay_operation_id,
                        'amount' =>  $row->amount,
                    ],
                    'sale_with_return' => [
                        'date' => $row->return_date,
                        'operation_id' => $row->return_operation_id,
                        'amount' =>  $row->return_amount,
                    ],
                ],
            ];
        }

        return $pays;
    }

    /**
     * Выбор фильтров
     * @return array
     * @throws Exception
     */
    public function getFilterData(): array
    {
        $filter_date_added = Helpers::getDataRange($this);
        return [
            'filter_date_start' => $filter_date_added['filter_date_start'],
            'filter_date_end' => $filter_date_added['filter_date_end'],
        ];
    }

    /**
     * @param object $pay_item
     * @return float[]|null[]
     */
    private function getAmountsForActionVariants(object $pay_item): array
    {
        $sale_not_return = $sale_with_return = $not_sale = null;

        if (!empty((float)$row->return_amount)) {
            $sale_with_return = (float)$pay_item->return_amount;
        } else {
            if ($pay_item->status === Multipolis::STATUS_SUCCESS && $pay_item->reason_code == Best2pay::REASON_CODE_SUCCESS) {
                switch ($pay_item->name_pay) {
                    case 'Теле-медицина':
                    case 'Мультиполис':
                        $sale_not_return = (float)$pay_item->amount;
                        break;
                    default:
                        $sale_not_return = (float)$pay_item->pay_amount;
                }
            }

            if (!empty((float)$pay_item->amount) && $pay_item->status !== Multipolis::STATUS_SUCCESS) {
                $not_sale = (float)$pay_item->amount;
            }
        }

        return compact('sale_not_return', 'sale_with_return', 'not_sale');
    }

    /**
     * @return void
     * @throws Exception
     */
    private function download()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $filter_data = $this->getFilterData();
        $pays = $this->getResults();

        $row = [
            'Тип услуги',
            'ФИО',
            'Договор найма',
            'Номер ключа',
            'Варианты события',
            '',
            '',
            'Данные оплаты',
        ];

        $filename = 'files/reports/' . ($filter_data['filter_date_start'] . "_" . $filter_data['filter_date_end']) . '_multipolis_pays_report_crm.xls';
        $sheet_name = $filter_data['filter_date_start'] . "_" . $filter_data['filter_date_end'];

        $this->writer = new XLSXWriter();
        $this->setRow($sheet_name, $row);

        $row = array_merge(array_fill(0, 4, ''), [
            'проданы (оплачены) и НЕ возвращены',
            'проданы (оплачены) и возвращены',
            'проданы (НЕ оплачены)',
            'дата платежа',
            'operation_id',
            'сумма',
        ]);
        $this->setRow($sheet_name, $row);

        $this->writer->markMergedCell($sheet_name, 0, 4, 0, 6);
        $this->writer->markMergedCell($sheet_name, 0, 7, 0, 9);

        foreach ($pays as $pay_values) {
            $start_row = $this->active_row_number;
            $row_data = [
                $pay_values->name_pay,
                $pay_values->fio,
                $pay_values->contract_number,
                $pay_values->multipolis_key,
            ];

            foreach ($pay_values->action_variants as $action_variant) {
                $row_data[] = $action_variant;
            }

            $this->setDetailDataRow($row_data, $pay_values->pays_detail->sale_not_return);

            $this->setRow($sheet_name, $row_data);

            foreach ($pay_values->pays_detail as $key => $pay_value) {
                $row_data = array_fill(0, 6, '');
                if ($key === 'sale_not_return') {
                    continue;
                }

                $this->setDetailDataRow($row_data, $pay_value);
                $this->setRow($sheet_name, $row_data);
            }

            for ($col_merged = 0; $col_merged < 6; $col_merged++) {
                $this->writer->markMergedCell($sheet_name, $start_row, $col_merged, $this->active_row_number - 2, $col_merged);
            }
        }

        $this->writer->writeToFile($this->config->root_dir . '/' . $filename);
        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }

    /**
     * @param $sheet_name
     * @param $data
     * @return void
     */
    private function setRow($sheet_name, $data)
    {
        $this->writer->writeSheetRow($sheet_name, $data);
        $this->active_row_number++;
    }

    /**
     * @param $row_data
     * @param $pay_value
     * @return void
     */
    private function setDetailDataRow(&$row_data, $pay_value)
    {
        $row_data[] = $pay_value['date'];
        $row_data[] = $pay_value['operation_id'];
        $row_data[] = $pay_value['amount'];
    }
}