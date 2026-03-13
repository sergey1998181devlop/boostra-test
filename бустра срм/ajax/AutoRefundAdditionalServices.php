<?php

use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use boostra\services\Core;

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once './AjaxController.php';

class AutoRefundAdditionalServices extends AjaxController
{
    protected $allowed_extensions = ['xlsx'];

    protected array $columns = [
        'A' => 'created',
        'B' => 'confirmation_date',
        'C' => 'service',
        'D' => 'key_created_date',
        'E' => 'key',
        'F' => 'zaim_number',
        'G' => 'zaim_date',
        'H' => 'zaim_amount',
        'I' => 'service_price',
        'J' => 'fio',
        'K' => 'birth_date',
        'L' => 'email',
    ];

    protected array $services_white_list = [
        'звездный оракул',
        'витамед',
        'консьерж',
        'финансовый доктор',
    ];

    public function __construct()
    {
        $this->setUploadPath();
        parent::__construct();
    }

    public function actions(): array
    {
        $this->setUploadPath();

        return [
            'auto_refund_services' => [
                'file_upload' => 'file'
            ],
        ];
    }

    /**
     * Устанавливает новый путь для сохранения Excel файлов с возвратами
     */
    private function setUploadPath(): void
    {
        $refundDirPath = Core::instance()->config->root_dir . 'files/refunds/';

        if ($this->upload_file_path !== $refundDirPath) {
            $this->upload_file_path = $refundDirPath;

            if (!is_dir($this->upload_file_path)) {
                mkdir($this->upload_file_path, 0777, true);
            }
        }
    }

    /**
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function actionAutoRefundServices()
    {
        $default_refund_percent = Core::instance()->request->post('refund_percent', 'integer') ?: 100;

        if (empty($this->new_filename)) {
            throw new Exception('Файл не был загружен');
        }

        $cancellations = $this->getDataFromFile($this->tmp_file_name);

        $refunds = [];
        foreach ($cancellations as $cancellation) {
            if (!in_array(mb_strtolower($cancellation['service']), $this->services_white_list)) {
                if ($cancellation['service'] == null) {
                    continue;
                }
                throw new Exception("В документе обнаружен неизвестный продукт {$cancellation['service']}.");
            }

            $refund = $this->updateCancellationData($cancellation);

            // Для каждого займа определяем свой процент возврата
            $current_refund_percent = $default_refund_percent;
            if ($refund['order_id']) {
                $order = Core::instance()->orders->get_order($refund['order_id']);
                if ($order && $order->loan_type === 'IL') {
                    $current_refund_percent = 100;
                }
            }

            $this->saveRefusalLetter(
                $refund,
                $current_refund_percent,
                $this->getServiceShortLabel($cancellation['service'])
            );

            if ($refund['can_be_refunded']) {
                $refundResult = $this->refundProcess($refund, $current_refund_percent);

                $refund['status'] = $refundResult['status'] ?? 'Ошибка';
                $refund['message'] = $refundResult['message'];
            }

            $refunds[] = $refund;
        }

        $this->saveDataToXlsx($refunds, $this->upload_file_path.'auto_refunded_services.xlsx');

        $this->outputResponse($refunds);
    }

    /**
     * Получает данные из Excel файла
     * @param $filePath
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function getDataFromFile($filePath): array
    {
        $inputFileType = PHPExcel_IOFactory::identify($filePath);
        $reader = PHPExcel_IOFactory::createReader($inputFileType);
        $phpExcel = $reader->load($filePath);

        $sheet = $phpExcel->getActiveSheet();

        $data = [];
        $isFirstRow = true;
        foreach ($sheet->getRowIterator() as $i => $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $key => $cell) {
                $rowData[$this->columns[$key]] = $cell->getValue();
            }

            $data[] = $rowData;
        }

        return $data;
    }

    /**
     * Обновляет массив данных по отказу
     * @param array $cancellation
     * @return array
     */
    private function updateCancellationData(array $cancellation): array
    {
        $item = [
            'service_id' => '', // ID КД
            'can_be_refunded' => false, // Проходят ли все проверки перед возвратом
            'status' => false, // Статус возврата
            'message' => '',
            'order_id' => '',
            'user_id' => '',
            'manager_id' => $this->manager->id,
            'manager_name' => $this->manager->name,
        ];

        $contract = $this->getContract($cancellation['zaim_number']);

        if (empty($contract)) {
            $item = array_merge($cancellation, $item);
            return $this->setRefundStatus($item, false, 'Контракт не найден');
        }

        $item['order_id'] = $contract->order_id;
        $item['user_id'] = $contract->user_id;

        $service = $this->getService($contract->order_id, $contract->user_id, $cancellation['service'], $cancellation['key_created_date'], $cancellation['service_price']);

        if (empty($service)) {
            $item = array_merge($cancellation, $item);
            return $this->setRefundStatus($item, false, 'Услуга не найдена.');
        }

        $item = $this->checkServiceAndRefund($item, $service, $cancellation['confirmation_date'], $cancellation['zaim_number']);

        return array_merge($cancellation, $item);
    }

    /**
     * Получает контракт
     * @param string $loanNumber
     * @return object|null
     */
    private function getContract(string $loanNumber): ?object
    {
        return Core::instance()->contracts->get_contract_by_params(['number' => $loanNumber]);
    }

    /**
     * Получает сервис
     * @param int $orderId
     * @param int $userId
     * @return object|null
     */
    private function getService(int $orderId, int $userId, string $service, ?string $dateAdded = null, ?int $amount = null): ?object
    {
        if ($dateAdded !== null) {
            $dateAdded = \DateTime::createFromFormat('H:i d.m.Y', $dateAdded);
            if ($dateAdded === false) {
                $dateAdded = null;
            }
        }

        $serviceName = mb_strtolower($service);
        $dateAdded = $dateAdded->format('Y-m-d');

        if ($serviceName == 'звездный оракул') {
            $result = Core::instance()->star_oracle->getStarOracle($orderId, $userId, '', 'SUCCESS', $dateAdded);
        } elseif ($serviceName == 'консьерж') {
            $result = Core::instance()->multipolis->getMultipolis($orderId, $userId, 'SUCCESS', $dateAdded);
        } elseif ($serviceName == 'витамед') {
            $result = Core::instance()->tv_medical->getTvMedical($orderId, $userId, 'SUCCESS', $dateAdded);
        } else {
            $result = Core::instance()->credit_doctor->getUserCreditDoctor($orderId, $userId, 'SUCCESS', $dateAdded);
        }

        return $result;
    }

    /**
     * Проверяет услугу и возврат
     * @param array $item
     * @param object|null $service
     * @param string|null $confirmationDate
     * @return array
     */
    private function checkServiceAndRefund(array $item, ?object $service, ?string $confirmationDate, ?string $zaimNumber): array
    {
        if (empty($service)) {
            return $this->setRefundStatus($item, false, 'Услуга не найдена');
        }

        if ($service->return_status == 2) {
            $manager = Core::instance()->managers->get_manager($service->return_by_manager_id);

            $item['manager_id'] = $manager->id;
            $item['manager_name'] = $manager->name;

            return $this->setRefundStatus($item, false, 'Услуга уже была возвращена');
        }

        if (is_null($confirmationDate) || $this->isUserNotConfirmed($confirmationDate)) {
            return $this->setRefundStatus($item, false, 'Возврат не подтвержден');
        }

        if ($this->isRefundPeriodExpired($service)) {
            return $this->setRefundStatus($item, false, 'Прошел срок возврата услуги');
        }

        if ($this->isDebtAmountSufficient($service, $zaimNumber)) {
            return $this->setRefundStatus($item, false, 'Сумма задолженности меньше чем сумма услуги');
        }

        $item['can_be_refunded'] = 1;
        $item['service_id'] = $service->id;

        return $item;
    }

    /**
     * Устанавливает статус и сообщение об отказе
     * @param array $item
     * @param bool $status
     * @param string $message
     * @return array
     */
    private function setRefundStatus(array $item, bool $status, string $message): array
    {
        $item['can_be_refunded'] = $status;
        $item['message'] = $message;

        return $item;
    }

    /**
     * Проверяет, что пользователь не подтвердил возврат
     * @param $confirmationDate
     * @return bool
     */
    private function isUserNotConfirmed($confirmationDate): bool
    {
        return empty($confirmationDate) || $confirmationDate === '-';
    }

    /**
     * Проверяет, истек ли срок возврата услуги
     * @param object $service
     * @return bool
     */
    private function isRefundPeriodExpired(object $service): bool
    {
        $refundDate = date_create();
        $serviceDateAdded = date_create($service->date_added);
        $interval = date_diff($refundDate, $serviceDateAdded);

        return $interval->days > 30;
    }

    /**
     * Проверяет, достаточна ли сумма задолженности
     * @param object $service
     * @param string $zaim_number
     * @return bool
     */
    private function isDebtAmountSufficient(object $service, string $zaim_number): bool
    {
        $userUID = Core::instance()->users->getUserUidById($service->user_id);

        if ($userUID) {
            $amountLeft = $service->amount - $service->amount_total_returned;
            $site_id = Core::instance()->users->get_site_id_by_user_id($service->user_id);
            $userBalances = Core::instance()->soap->get_user_balances_array_1c($userUID, $site_id);

            $number = mb_strtoupper(trim($zaim_number));

            foreach ($userBalances as $userBalance) {
                $number1c = mb_strtoupper(trim((string)$userBalance['НомерЗайма']));

                if (hash_equals($number, $number1c)) {
                    $amountDept = !empty($userBalance['ОстатокОД']) ? (float)$userBalance['ОстатокОД'] : 0;
                    $percentDept = !empty($userBalance['ОстатокПроцентов']) ? (float)$userBalance['ОстатокПроцентов'] : 0;

                    return $amountLeft >= ($amountDept + $percentDept);
                }
            }
        }

        return true;
    }

    /**
     * Обработка возврата
     * @param $item
     * @return array
     */
    private function refundProcess($item, int $refund_percent): array
    {
        $service = $this->getService($item['order_id'], $item['user_id'], $item['service'], $item['key_created_date'], $item['service_price']);
        $order = Core::instance()->orders->get_order($item['order_id']);

        $serviceData = $this->getServiceData([
            'service' => $service,
            'number' => $item[3],
            'order' => $order,
            'refund_percent' => $refund_percent,
            'service_name' => $item['service'],
        ]);

        $returnTransactionId = $this->refund($serviceData);

        $this->updateServiceData($serviceData, $returnTransactionId);

        $this->addReceipt($serviceData, $returnTransactionId);

        $this->addComment($serviceData);

        $this->addChangeLog($serviceData, $returnTransactionId);

        return [
            'status' => true,
            'message' => 'Возврат успешно проведен',
        ];
    }

    /**
     * Получает данные для возврата услуги
     * @param array $params
     * @return array
     */
    private function getServiceData(array $params): array
    {
        $secondReturnText = (int)$params['service']->return_status === 2 ? ' оставшейся части' : '';
        $serviceName = $params['service_name'];
        if (mb_strtolower($serviceName) == 'звездный оракул') {
            $transactionType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE;
            $paymentType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE;

            $receiptDescription = Core::instance()->receipts::PAYMENT_DESCRIPTIONS[Core::instance()->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE];

            $refundName = ' Звездного оракула';
        } elseif (mb_strtolower($serviceName)  == 'консьерж') {
            $transactionType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_CHEQUE;
            $paymentType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_CHEQUE;

            $receiptDescription = Core::instance()->receipts::PAYMENT_DESCRIPTIONS[Core::instance()->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS];

            $refundName = ' Консьержа';
        } elseif (mb_strtolower($serviceName)  == 'витамед') {
            $transactionType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_CHEQUE;
            $paymentType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_CHEQUE;

            $receiptDescription = Core::instance()->receipts::PAYMENT_DESCRIPTIONS[Core::instance()->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL];

            $refundName = ' Витамеда';
        } else {
            $transactionType = Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE;
            $paymentType = $params['service']->is_penalty
                ? Core::instance()->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR_CHEQUE
                : Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE;

            $receiptDescription = $params['service']->is_penalty
                ? Core::instance()->receipts::PAYMENT_DESCRIPTIONS[Core::instance()->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR]
                : Core::instance()->receipts::PAYMENT_DESCRIPTIONS[Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR];

            $refundName = ' Кредитного Доктора';
        }

        if (mb_strtolower($serviceName) === 'звездный оракул' or mb_strtolower($serviceName) === 'финансовый доктор') {
            $reference = $params['service']->transaction_id;
        } else {
            $reference = $params['service']->payment_id;
        }

        $commentText = 'Возврат' . $secondReturnText . $refundName . ' от ' . date('d.m.Y', strtotime($params['service']->date_added)) . ' (Дата услуги) при выдаче в зачет оплаты займа';
        $refund_percent = isset($params['refund_percent']) ? (int)$params['refund_percent'] : 100;

        $amountLeft = $params['service']->amount - $params['service']->amount_total_returned;
        $refundAmount = ceil($amountLeft * ($refund_percent / 100));

        return [
            'service' => $params['service'],
            'service_name' => $serviceName,
            'transaction_type' => $transactionType,
            'payment_type' => $paymentType,
            'description' => 'Возврат взаимозачетом услуги "' . $serviceName . '" по договору ' . $params['number'],
            'amount' => $refundAmount,
            'reference' => $reference,
            'receipt_description' => $receiptDescription,
            'comment_text' => $commentText,
            'order_id' => $params['order']->order_id,
            'user_id' => $params['order']->user_id,
            'order_id_1c' => $params['order']->id_1c,
        ];
    }

    /**
     * Выполняет возврат и возвращает ID транзакции
     * @param array $serviceData
     * @return int
     */
    private function refund(array $serviceData): int
    {
        $operationDate = date('Y-m-d H:i:s');
        return Core::instance()->best2pay->add_transaction([
            'user_id' => $serviceData['user_id'],
            'order_id' => $serviceData['order_id'],
            'type' => $serviceData['transaction_type'],
            'amount' => $serviceData['amount'] * 100,
            'sector' => 0,
            'register_id' => 0,
            'contract_number' => $serviceData['service']->contract_number,
            'reference' => $serviceData['reference'],
            'description' => $serviceData['description'],
            'created' => $operationDate,
            'operation' => 0,
            'reason_code' => 1,
            'state' => 'APPROVED',
            'body' => '',
            'operation_date' => $operationDate,
            'callback_response' => ' ',
        ]);
    }

    /**
     * Обновляет данные услуги после возврата
     * @param array $serviceData
     * @param int $returnTransactionId
     */
    private function updateServiceData(array $serviceData, int $returnTransactionId): void
    {
        $serviceName = mb_strtolower($serviceData['service_name']);
        $prepareData = [
            'return_status' => 2,
            'amount_total_returned' => $serviceData['service']->amount_total_returned + $serviceData['amount'],
            'return_date' => date('Y-m-d H:i:s'),
            'return_amount' => round($serviceData['amount']),
            'return_transaction_id' => $returnTransactionId,
            'return_sent' => 0,
            'return_by_manager_id' => $this->manager->id,
        ];

        if ($serviceName == 'звездный оракул') {
            Core::instance()->star_oracle->updateStarOracleData($serviceData['service']->id, $prepareData);
        } elseif ($serviceName == 'консьерж') {
            Core::instance()->multipolis->update_multipolis($serviceData['service']->id, $prepareData);
        } elseif ($serviceName == 'витамед') {
            Core::instance()->tv_medical->updatePayment($serviceData['service']->id, $prepareData);
        } else {
            Core::instance()->credit_doctor->updateUserCreditDoctorData($serviceData['service']->id, $prepareData);
        }
    }

    /**
     * Добавляет чек
     * @param array $serviceData
     * @param int $returnTransactionId
     */
    private function addReceipt(array $serviceData, int $returnTransactionId): void
    {
        $organizationId = $serviceData['service']->organization_id;

        if (in_array($serviceData['payment_type'], [
            Core::instance()->receipts::PAYMENT_TYPE_CREDIT_DOCTOR,
            Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR,
            Core::instance()->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE,
        ], true)) {
            $organizationId = Core::instance()->receipts::ORGANIZATION_FINTEHMARKET;
        }

        Core::instance()->receipts->addItem([
            'user_id' => $serviceData['user_id'],
            'order_id' => $serviceData['order_id'],
            'amount' => $serviceData['amount'],
            'payment_method' => Core::instance()->orders::PAYMENT_METHOD_B2P,
            'payment_type' => $serviceData['payment_type'],
            'organization_id' => $organizationId,
            'description' => $serviceData['receipt_description'],
            'transaction_id' => $returnTransactionId,
        ]);
    }

    /**
     * Добавляет комментарий
     * @param array $serviceData
     */
    private function addComment(array $serviceData): void
    {
        Core::instance()->comments->add_comment([
            'manager_id' => $this->manager->id,
            'user_id' => $serviceData['user_id'],
            'order_id' => $serviceData['order_id'],
            'block' => 'recompense',
            'text' => $serviceData['comment_text'],
            'created' => date('Y-m-d H:i:s'),
        ]);

        Core::instance()->soap->send_comment([
            'manager' => $this->manager->name_1c,
            'text' => $serviceData['comment_text'],
            'created' => date('Y-m-d H:i:s'),
            'number' => $serviceData['order_id_1c'],
        ]);
    }

    /**
     * Добавляет запись в журнал изменений
     * @param array $serviceData
     * @param int $returnTransactionId
     */
    private function addChangeLog(array $serviceData, int $returnTransactionId): void
    {
        Core::instance()->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $serviceData['transaction_type'],
            'old_values' => $serviceData['service']->id,
            'new_values' => serialize(['amount' => $serviceData['amount']]),
            'order_id' => $serviceData['order_id'],
            'user_id' => $serviceData['user_id'],
            'file_id' => $returnTransactionId,
        ]);
    }

    /**
     * Создает новый документ с результатами
     * @param $data
     * @param $outputFilePath
     * @return mixed
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function saveDataToXlsx($data, $outputFilePath)
    {
        $phpExcel = new PHPExcel();

        $sheet = $phpExcel->getActiveSheet();

        $headers = [
            'A1' => 'Создан',
            'B1' => 'Подтвержден',
            'C1' => 'Ключ',
            'D1' => 'Договор',
            'E1' => 'Дата',
            'F1' => 'Займ',
            'G1' => 'Цена',
            'H1' => 'ФИО',
            'I1' => 'Дата Рождения',
            'J1' => 'Email',
            'K1' => 'Возвращен',
            'L1' => 'Причина',
            'M1' => 'Менеджер',
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column, $header);
        }

        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['created']);
            $sheet->setCellValue('B' . $row, $item['confirmation_date']);
            $sheet->setCellValue('C' . $row, $item['key']);
            $this->addHyperlink($sheet, 'D' . $row, Core::instance()->config->back_url . 'order/' . $item['order_id'], $item['zaim_number']);
            $sheet->setCellValue('E' . $row, $item['zaim_date']);
            $sheet->setCellValue('F' . $row, $item['zaim_amount']);
            $sheet->setCellValue('G' . $row, $item['service_price']);
            $this->addHyperlink($sheet, 'H' . $row, Core::instance()->config->back_url . 'client/' . $item['user_id'], $item['fio']);
            $sheet->setCellValue('I' . $row, $item['birth_date']);
            $sheet->setCellValue('J' . $row, $item['email']);
            $sheet->setCellValue('K' . $row, $item['status'] ? '✅' : '❌');
            $sheet->setCellValue('L' . $row, $item['message']);
            $this->addHyperlink($sheet, 'M' . $row, Core::instance()->config->back_url . 'manager/' . $item['manager_id'], $item['manager_name']);
            $row++;
        }

        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $writer->save($outputFilePath);

        return $outputFilePath;
    }

    /**
     * Добавляет гиперссылку в ячейку
     * @param PHPExcel_Worksheet $sheet
     * @param string $cell
     * @param string $url
     * @param string $text
     * @throws \PHPExcel_Exception
     */
    private function addHyperlink(PHPExcel_Worksheet $sheet, string $cell, string $url, string $text): void
    {
        $sheet->setCellValue($cell, $text);
        $sheet->getCell($cell)->getHyperlink()->setUrl($url);
    }

    /**
     *  Создает Заявление об отказе от дополнительной услуги
     * @param $refund
     * @param $refundPercent
     * @param $serviceName
     */
    private function saveRefusalLetter($refund, $refundPercent, $serviceName)
    {
        $refundAmount = $refund['service_price'] * $refundPercent / 100;
        $currentDate = date('d.m.Y');
        $type = "ZAYAVLENIE_NA_OTKAZ_OT_DOP_USLUGI";

        $contract = Core::instance()->contracts->get_contract_by_params(['number' => $refund['zaim_number']]);

        $user_id = $contract->user_id;
        $order_id = $contract->order_id;
        $user = Core::instance()->users->get_user((int)$user_id);

        // Можем получать ФИО в родительном падеже через внешний сервис, пока решено не использовать
        $fioGenitive = $refund['fio'];
        /*
        try {
            $morpherUrl = "https://ws3.morpher.ru/russian/declension?s=" . urlencode($fioGenitive);
            $xmlResponse = file_get_contents($morpherUrl);

            if (preg_match('#<Р>(.*?)<\/Р>#u', $xmlResponse, $matches)) {
                $fioGenitive = $matches[1];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        */

        try {
            Core::instance()->documents->create_document([
                'type'               => $type,
                'notification_title' => "Заявление об отказе от дополнительной услуги",
                'user_id'            => $user_id,
                'order_id'           => $order_id,
                'contract_number'    => $refund['zaim_number'],
                'params'             =>
                    [
                        'lastname'               => $user->lastname,
                        'firstname'              => $user->firstname,
                        'patronymic'             => $user->patronymic,
                        'phone_mobile'           => $user->phone_mobile,
                        'passport_serial'        => str_replace('-', ' ', $user->passport_serial),
                        'contract_number'        => $refund['zaim_number'],
                        'contract_date'          => $refund['zaim_date'],
                        'refund_amount'          => $refundAmount,
                        'document_date'          => $currentDate,
                        'fio_genitive'           => $fioGenitive,
                        'service_name'           => $serviceName,
                        'director_name'          => $this->getDirectorName($refundAmount, $serviceName),
                    ]
            ]);
        } catch (Exception $e) {
            error_log('Document creation error: ' . $e->getMessage());
        }
    }

    private function getDirectorName($refundAmount, $serviceName): string
    {
        if (($serviceName === AdditionalServiceKey::LABEL_FINANCIAL_DOCTOR)
            || ($serviceName === AdditionalServiceKey::LABEL_STAR_ORACLE && $refundAmount === 350)
        ) {
            return 'И.Ю. Вороному';
        }

        return 'Н.В. Фетисовой';
    }

    private function getServiceShortLabel(string $serviceName): string
    {
        $typeMap = [
            'звездный оракул'   => 'star_oracle',
            'консьерж'          => 'multipolis',
            'витамед'           => 'tv_medical',
            'финансовый доктор' => 'credit_doctor',
        ];

        $type = $typeMap[mb_strtolower($serviceName)] ?? null;

        if ($type === null) {
            return $serviceName;
        }

        return AdditionalServiceKey::getShortLabelByType($type);
    }
}

new AutoRefundAdditionalServices;