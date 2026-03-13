<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';

ini_set('max_execution_time', '1200');
ini_set('memory_limit', '256M');

/**
 * Class ValidateMoratoriumView
 * Класс для проверки мораториев
 */
class ValidateMoratoriumView extends View
{
    private string $fileType = '';
    public const WORD_YES = 'Да';
    public const WORD_NO = 'Нет';

    /**
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->request->method('post')) {
            $files = [
                'first' => $this->request->files('validate_file'),
                'second_Finlab' => $this->request->files('validate_file_second'),
                'third_scorista' => $this->request->files('validate_file_third'),
            ];

            foreach ($files as $type => $file) {
                if (!empty($file['size'])) {
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    if (strtolower($extension) !== 'xlsx') {
                        $this->design->assign('error', 'Поддерживается только XLSX');
                        return;
                    }

                    $this->fileType = $type;

                    $this->validateFile();
                    return;
                }
            }

            $this->design->assign('error', 'Файл пуст либо не выбран');
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {
        return $this->design->fetch('validate_moratorium_view.tpl');
    }

    /**
     * Читает файл из формы
     * @return array
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function readFile(): array
    {
        $files = [
            'first' => $this->request->files('validate_file'),
            'second_Finlab' => $this->request->files('validate_file_second'),
            'third_scorista' => $this->request->files('validate_file_third'),
        ];

        if (!isset($files[$this->fileType])) {
            throw new \InvalidArgumentException("Неверный тип файла: {$this->fileType}");
        }

        $file = $files[$this->fileType];

        $objPHPExcel = PHPExcel_IOFactory::load( $file['tmp_name'] );
        $total_rows  = $objPHPExcel->setActiveSheetIndex()->getHighestRow();

        $new_data = [];
    
        for( $i = 2; $i <= $total_rows; $i++ )
        {
            $id               = (int)$objPHPExcel->getActiveSheet()->getCell( 'A' . $i )->getValue();
            $fio              = $objPHPExcel->getActiveSheet()->getCell( 'B' . $i )->getValue();
            $birth_day        = $objPHPExcel->getActiveSheet()->getCell( 'C' . $i )->getValue();
            $phone            = $objPHPExcel->getActiveSheet()->getCell( 'D' . $i )->getValue();

            if (empty($phone)) {
                continue;
            }
    
            $phone_formatted = $this->users->clear_phone( $phone, 7 );
            $user_id         = $this->users->get_phone_user( $phone_formatted );

            $row = compact('id', 'fio', 'birth_day', 'phone');

            if ($this->fileType === 'third_scorista') {
                if (empty($user_id)) {
                    $row['point_scorista'] = 'Пользователь не найден в CRM';
                } else {
                    $point_scorista = $this->scorings->get_last_scorista_for_user((int)$user_id);
                    $row['point_scorista'] = !empty($point_scorista->scorista_ball)
                        ? $point_scorista->scorista_ball
                        : self::WORD_NO;
                }
            } else {
                $organization_ids = [$this->organizations::FINLAB_ID, $this->organizations::RZS_ID, $this->organizations::LORD_ID];
                if (!$user_id) {
                    foreach ($organization_ids as $organization_id) {
                        $row['moratorium_' . $organization_id] = 'Пользователь не найден в CRM для организации ' . $organization_id;
                    }

                    $row['black_list'] = self::WORD_NO;
                    $row['active_contract'] = self::WORD_NO;
                    $row['delete_lk'] = self::WORD_NO;
                } else {
                    $moratoriums = $this->users->getMoratoriumByUserId($user_id, $organization_ids); // Вернёт массив с ОТСОРТИРОВАННЫМИ мораториями по id организации (11, 13, 16...)
                    foreach ($organization_ids as $organization_id) {
                        $isMatched = false;
                        foreach ($moratoriums as $moratorium) {
                            if ($moratorium->organization_id == $organization_id) {
                                $row['moratorium_' . $organization_id] = $moratorium->moratorium ? self::WORD_YES : self::WORD_NO;
                                $isMatched = true;
                                break;
                            }
                        }
                        if (!$isMatched) {
                            $row['moratorium_' . $organization_id] = self::WORD_NO;
                        }
                    }
                    
                    $row['black_list'] = (!empty($this->blacklist->getOne(compact('user_id'))) || !empty($this->blacklist->checkIsUserIn1cBlacklist($user_id))) ? self::WORD_YES : self::WORD_NO;
                    $row['active_contract'] = $this->hasActiveOrder($user_id) ? self::WORD_YES : self::WORD_NO;
                    $row['delete_lk'] = $this->hasDeleteLk($user_id) ? self::WORD_YES : self::WORD_NO;
                }

                if ($this->fileType === 'second_Finlab') {
                    // проверка записи финлаб
                    $row['active_finlab'] = $this->hasActiveFinlab($id);
                }
            }

            $new_data[] = $row;
        }

        return $new_data;
    }

    /**
     * Выгрузка данных в Excel
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function validateFile(): void
    {
        $header = [
            '№'                       => 'integer',
            'ФИО'                     => 'string',
            'Дата рождения'           => 'string',
            'Телефон'                 => 'string',
            'Мораторий ФЛБ'           => 'string',
            'Мораторий РЗС'           => 'string',
            'Мораторий ЛРД'           => 'string',
            'ЧС'                      => 'string',
            'Действует договор займа' => 'string',
            'Удален ЛК'               => 'string',
        ];

        if ($this->fileType === 'second_Finlab') {
            $header['Запись финлаб'] = 'string';
        }

        if ($this->fileType === 'third_scorista') {
            $header = [
                '№'             => 'integer',
                'ФИО'           => 'string',
                'Дата рождения' => 'string',
                'Телефон'       => 'string',
                'Балл скористы' => 'string',
            ];
        }

        $results = $this->readFile();
        $writer  = new XLSXWriter();
        
        $writer->writeSheetHeader('validate_moratorium', $header);
        
        foreach ($results as $row_data) {
            $writer->writeSheetRow('validate_moratorium', $row_data);
        }

        //$filename = 'files/reports/validate_moratorium.xlsx';
        $filename = 'files/validate_moratorium.xlsx';
        if (isset($_GET['f'])) {
            $writer->writeToFile($this->config->root_dir . 'validate_moratorium.xlsx');
            header('Location: validate_moratorium.xlsx');
            exit;
        }

        $writer->writeToFile($this->config->root_dir . $filename);
        header('Location:' . $filename);
        exit;
    }

    /**
     * Проверяет наличие выданного займа
     * @param int $user_id
     * @return bool
     */
    private function hasActiveOrder(int $user_id): bool
    {
        $this->db->query(
            $this->db->placehold(
                "SELECT EXISTS (SELECT * FROM s_user_balance WHERE user_id = ? AND (zaim_number != 'Нет открытых договоров' AND zaim_number <> '') AND (zayavka != '' AND zayavka != 0)) as r",
                $user_id,
            )
        );
        return (bool) $this->db->result('r');
    }

    private function hasActiveFinlab(int $id): bool
    {
        $this->db->query(
            $this->db->placehold(
                "SELECT COUNT(1) as count
                   FROM s_orders
                WHERE utm_medium = ?
                AND organization_id = 11
                AND credit_getted = 0
                LIMIT 1
            ;", $id
            )
        );

        return (bool) $this->db->result('count');
    }

    private function hasDeleteLk(int $user_id): bool
    {
        $this->db->query(
            $this->db->placehold(
                "SELECT EXISTS ( SELECT 1 FROM s_users 
                         WHERE enabled = 0 
                           AND blocked = 1
                           AND last_lk_visit_time IS NOT NULL 
                           AND id = ?) as r;",
                $user_id
            )
        );

        return (bool) $this->db->result('r');
    }
}
