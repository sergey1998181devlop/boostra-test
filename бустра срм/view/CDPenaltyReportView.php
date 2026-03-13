<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

require_once 'View.php';

/**
 * Class CDPenaltyReportView
 * Класс для работы с отчётом - отчёт по штрафным КД по просрочке
 */
class CDPenaltyReportView extends View
{
    /**
     * Список ключей массива
     */
    public const DEFAULT_ARRAY_KEYS = [
        'total_users',
        'insure',
        'total_count',
        'total_pays',
        'cv',
    ];

    private const EXPIRED_PERIODS = [
        'nine_plus_count' => '9',
        'thirty_plus_count' => '30',
        'eighty_five_plus_count' => '85',
    ];

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

        if ($this->request->get('ajax')) {
            $results = $this->getResults();
            $this->design->assign('results', $this->filterReportResults($results));

            $totals = $this->generateTotals($results);
            $this->design->assign('totals', $totals);
        }

        return $this->design->fetch('cd_penalty_report_view.tpl');
    }

    /**
     * Выбор фильтров
     * @return array
     */
    private function getFilterData(): array
    {
        return [
            'filter_date_created' => Helpers::getDataRange($this),
            'group_by' => 'date, nine_plus, thirty_plus, eighty_five_plus DESC',
        ];
    }

    /**
     * Генерация данных
     * @return array
     * @throws Exception
     */
    private function getResults(): array
    {
        $filter_data = $this->getFilterData();
        $results = $this->insurances->getPenaltyCreditDoctor($filter_data);

        array_walk($results, function ($item) {
            $item->cv = round($item->insure / $item->total_count, 2);
        });

        return $results;
    }

    /**
     * Получаем итоговые значения
     * @param array $results
     * @return array
     */
    private function generateTotals(array $results): array
    {
        $totals = [];
        // Добавялем в итоговый массив подмассивы с ключами 9, 30 и 85
        foreach (self::EXPIRED_PERIODS as $k => $v) {
            $totals[$v] = self::getDefaultArray();
        }

        if (!empty($results)) {
            array_map(function ($item) use (&$totals) {
                // Если есть свойство periodName, значит здесь есть данные о просрочке и эти данные надо обработать
                if (isset($item->periodName)) {
                    $period = $item->periodName;
                    $periodNameString = array_flip(self::EXPIRED_PERIODS)[$period];
                    // На свякий случай, проверяем есть ли на самом деле данные об определённом периоде просрочки
                    if ($item->{$periodNameString} > 0) {
                        foreach (array_keys((array)$item) as $key) {
                            // Не добавляем в итоговый массив данные с этими ключами
                            $keysToAvoid = array_merge(['date', 'cv', 'periodName'], array_keys(self::EXPIRED_PERIODS));
                            if (in_array($key, $keysToAvoid)) {
                                continue;
                            }

                            // Все остальные суммируем и добавляем
                            $totals[$period][$key] += $item->{$key};
                        }
                    }
                }
            }, $results);

            // Считаем конверсию TODO: подставить нужное
            foreach ($totals as $period => $items) {
                if (!empty($totals[$period]['total_count'])) {
                    $totals[$period]['cv'] = round($totals[$period]['insure'] / $totals[$period]['total_count'] , 2);
                }
            }
        }

        return $totals;
    }

    /**
     * Выгрузка данных в Excel
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws Exception
     */
    private function download()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $excel = new PHPExcel();

        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $filename = 'files/reports/download_cd_penalty.xls';

        $active_sheet->setTitle(date('Y_m_d H_i_s'));

        $excel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = 'A';

        $active_sheet->setCellValue($cell++ .'1', 'Период');
        $active_sheet->setCellValue($cell++ .'1', 'Просрочка');
        $active_sheet->setCellValue($cell++ .'1', 'Кол-во клиентов');
        $active_sheet->setCellValue($cell++ .'1', 'Кол-во подключений');
        $active_sheet->setCellValue($cell++ .'1', 'Кол-во списаний');
        $active_sheet->setCellValue($cell++ .'1', 'Сумма списания, рублей');
        $active_sheet->setCellValue($cell++ .'1', 'Конверсия в списание');

        $row = 2;

        $set_row = function ($array) use (& $active_sheet, & $row) {
            $cell = 'A';
            foreach ($array as $item) {
                $active_sheet->setCellValue($cell++ . $row, $item);
            }
            $row++;
        };

        $set_row_color = function ($color_bg, $color_text = 'EA0F0F') use (& $active_sheet, & $row, & $cell) {
            $active_sheet->getStyle('A' . $row . ':' . $cell . $row)->applyFromArray(
                [
                    'fill' => [
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => $color_bg],
                    ],
                    'font' => [
                        'color' => ['rgb' => $color_text],
                    ]
                ]
            );
        };

        $results = $this->getResults();
        $this->filterReportResults($results);
        $totals = $this->generateTotals($results);
        foreach ($results as $values) {
            // Removes expired periods' data from values
             foreach (array_keys(self::EXPIRED_PERIODS) as $key) {
                unset($values->{$key});
            }
           // var_dump($values);
            $set_row($values);
        }



        foreach ($totals as $periodName => $period) {
            $set_row_color('8FD14F', 'EA0F0F');
            // Добавляем пустой столбец и имя периода в итого
            array_unshift($period, '', $periodName);
            $set_row($period);
        }

        for ($i = 'A'; $i <= $cell; $i++) {
            $active_sheet->getColumnDimension($i)->setAutoSize(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($this->config->root_dir . $filename);

        header('Location:' . $this->config->root_url . '/' . $filename . '?v=' . time());
        exit;
    }

    /**
     * Filter records by filter days (9+ 30+, 85+)
     *
     * @param array $results
     * @return array
     */
    private function filterReportResults(array $results): array
    {
        $report = [];
        foreach ($results as $item) {
            if (!isset($report[$item->date])) {
                $report[$item->date] = [];
            }

            foreach (self::EXPIRED_PERIODS as $periodCount => $periodArrayKey) {
                if ((int) $item->{$periodCount} > 0) {
                    $item->periodName = $periodArrayKey;
                    $report[$item->date][$periodArrayKey] = $item;
                }
            }
        }

        // Adds periods with empty data
        foreach ($report as $date => $periods) {
            if (count($periods) !== 3) {
                foreach (self::EXPIRED_PERIODS as $key => $value) {
                    if (!isset($report[$date][$value])) {
                        $missingPeriod = new StdClass();
                        $missingPeriod->periodName = $value;
                        $missingPeriod->total_users = 0;
                        $missingPeriod->insure = 0;
                        $missingPeriod->total_count = 0;
                        $missingPeriod->total_pays = 0;
                        $missingPeriod->cv = 0;
                        $report[$date][$value] = $missingPeriod;

                    }
                }
            }
        }

        // Sort periods, i.e. 9, 30, 85
        foreach ($report as $date => $periods) {
            ksort($periods);
            $report[$date] = $periods;
        }

        return $report;
    }

    /**
     * Формирует дефолтный массив
     * @return array|false
     */
    public static function getDefaultArray()
    {
        return array_combine(self::DEFAULT_ARRAY_KEYS, array_fill(0, count(self::DEFAULT_ARRAY_KEYS), 0));
    }
}
