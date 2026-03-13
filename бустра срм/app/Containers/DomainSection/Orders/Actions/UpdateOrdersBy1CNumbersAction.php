<?php

namespace App\Containers\DomainSection\Orders\Actions;

use Generator;
use Orders;
use Config;
use PHPExcel_IOFactory;

require_once ROOT_DIR . '/api/Config.php';
require_once ROOT_DIR . '/PHPExcel/Classes/PHPExcel.php';
require_once ROOT_DIR . '/api/Orders.php';
require_once ROOT_DIR . '/vendor/autoload.php';

class UpdateOrdersBy1CNumbersAction extends Orders
{
    /**
     * Имя файла из которого читаем 1с коды заявок
     *
     * @var string
     */
    protected string $fileName = 'like-moratory.xlsx';

    public function __construct()
    {
        parent::__construct();
    }

    public function execute(int $chunkSize = 1000): bool
    {
        $order1cNumbers = $this->getOrderNumbersFromExcel();
        $chunks = array_chunk($order1cNumbers, $chunkSize);
        $countUpdated = 0;

        foreach ($chunks as $chunk) {
            foreach ($this->getOrdersBy1CNumbers($chunk) as $order) {
                $query = $this->db->placehold(
                    'UPDATE s_orders SET reason_id = 10 WHERE id = ?',
                    $order->id
                );
                $this->db->query($query);

                $this->order_data->set($order->id, 'TASK:help-133', 1);

                $countUpdated++;
                echo 'Обновлена заявка ' . $order->id . '. User ID = ' . $order->user_id . PHP_EOL;
            }
        }

        echo "Всего обновлено заявок: $countUpdated" . PHP_EOL;
    }

    private function getOrdersBy1CNumbers(array $orderNumbers): Generator
    {
       $query = $this->db->placehold(
           'SELECT id, user_id FROM s_orders WHERE 1c_id IN (?@)',
           $orderNumbers
       );

       $this->db->query($query);
       $orders = $this->db->results() ?: [];
       foreach ($orders as $order) {
            yield $order ?: [];
       }
    }

    private function getOrderNumbersFromExcel(): array
    {
        $ordersIds = [];
        $filePath = $this->getExcelFilePath();

        if (!file_exists($filePath)) {
            echo "Файл не найден по пути: " . $filePath;
            return [];
        }

        $inputFileType = PHPExcel_IOFactory::identify($filePath);
        $reader = PHPExcel_IOFactory::createReader($inputFileType);
        $phpExcel = $reader->load($filePath);

        $sheet = $phpExcel->getActiveSheet();

        foreach ($sheet->getRowIterator() as $key => $row) {
            if ($key === 1) {
                continue;
            }

            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cellKey => $cell) {
                if ($cellKey === 'A') {
                    $ordersIds[] = $cell->getValue();
                }
            }
        }

        return $ordersIds;
    }

    private function getExcelFilePath(): string
    {
        $config = new Config();

        return $config->root_dir."files/". $this->fileName;
    }
}