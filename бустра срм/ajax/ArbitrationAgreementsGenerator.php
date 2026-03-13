<?php

use boostra\services\Core;

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once './AjaxController.php';


class ArbitrationAgreementsGenerator extends AjaxController
{
    protected $allowed_extensions = ['xlsx'];

    protected $documentType;

    /**
     * Коды валидности договора для генерации соглашений
     */
    const CONTRACT_VALID = 1;                // все проверки прошли
    const INVALID_CONTRACT_NUMBER = 2;       // неверный формат номера договора
    const CONTRACT_NOT_FOUND = 3;            // контракт не найден
    const EMPTY_ASP = 4;                     // пустой АСП
    const EMPTY_ISSUANCE_DATE = 5;           // пустая дата выдачи
    const USER_NOT_FOUND = 6;                // пользователь не найден
    const USER_BALANCE_NOT_FOUND = 7;        // баланс пользователя не найден
    const INVALID_PAYMENT_DATE = 8;          // невалидная дата платежа

    public function __construct()
    {
        $this->setUploadPath();
        parent::__construct();
    }

    public function actions(): array
    {
        $this->setUploadPath();

        return [
            'arbitration_agreements_generation' => [
                'file_upload' => 'file',
                'document_type' => 'string'
            ],
            'download_unprocessed_contracts' => [
                'filename' => 'string'
            ],
        ];
    }

    /**
     * Устанавливает новый путь для сохранения Excel файлов с договорами
     */
    private function setUploadPath(): void
    {
        $refundDirPath = Core::instance()->config->root_dir . 'files/arbitration_agreements/';

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
    public function actionArbitrationAgreementsGeneration()
    {
        if (empty($this->new_filename)) {
            throw new Exception('Файл не был загружен');
        }

        $this->documentType = Core::instance()->request->post('document_type') ?? null;

        if (!in_array($this->documentType, [Documents::ARBITRATION_AGREEMENT, Documents::PENALTY_CREDIT_DOCTOR])) {
            throw new Exception('Недопустимый тип документа');
        }

        $processedCount = 0;
        $unprocessedContracts = [];
        $contractStatuses = [];

        foreach ($this->getDataFromFile($this->tmp_file_name) as $loanNumber) {
            try {
                $contractValidStatus = $this->getContractValidStatus($loanNumber);
                if ($contractValidStatus === self::CONTRACT_VALID) {
                    $data = $this->getArbitrationAgreementData($loanNumber);

                    // Создаем пакет документов (основной + сопутствующие)
                    $this->createAgreementDocuments($data);

                    // Проверяем, что все документы видны в ЛК клиента
                    $status = $this->verifyContractDocuments($loanNumber);
                    $contractStatuses[] = [
                        'contract_number' => $loanNumber,
                        'status' => $status
                    ];

                    if ($status) {
                        $processedCount++;
                    } else {
                        $unprocessedContracts[] = $loanNumber;
                    }
                } else {
                    $unprocessedContracts[] = $loanNumber;
                    $contractStatuses[] = [
                        'contract_number' => $loanNumber,
                        'status' => false,
                        'invalid_code' => $contractValidStatus,
                    ];
                }
            } catch (Exception $e) {
                $unprocessedContracts[] = $loanNumber;
                $contractStatuses[] = [
                    'contract_number' => $loanNumber,
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $response = [
            'processed_count' => $processedCount,
            'unprocessed_contracts' => $unprocessedContracts,
            'total_count' => count($contractStatuses),
            'contract_statuses' => $contractStatuses
        ];

        if (count($unprocessedContracts) > 0) {
            $this->saveUnprocessedContractsToFile($unprocessedContracts);
            $response['download_file'] = 'unprocessed_contracts_' . date('Y-m-d_H-i-s') . '.xlsx';
        }

        $this->outputResponse($response);
    }

    /**
     * Подготавливает данные арбитражного соглашения
     * @param $loanNumber
     * @return array
     */
    private function getArbitrationAgreementData($loanNumber): array
    {
        $contract = $this->getContract($loanNumber);
        if (!$contract->user_id) {
            throw new Exception('Не указан пользователь для займа ' . $loanNumber);
        }

        $user = Core::instance()->users->get_user($contract->user_id);
        if (!$user) {
            throw new Exception('Не смогли найти пользователя ' . $contract->user_id . ' для займа ' . $loanNumber);
        }

        $arbitrationAgreementsParams = Core::instance()->documents->getArbitrationAgreementParams(
            $user, $contract->order_id, $contract->asp, $contract->issuance_date
        );

        return [
            'order_id' => $contract->order_id,
            'user_id' => $contract->user_id,
            'contract_number' => $loanNumber,
            'params' => $arbitrationAgreementsParams,
            'type' => $this->documentType
        ];
    }

    /**
     * Создает пакет документов по соглашению:
     *  - основной документ (тип берется из $data['type'])
     *  - Соглашение об акцепте оферты
     *  - Соглашение об использовании АСП
     *  - Соглашение о подписании молчанием
     *
     * @param array $data Базовые данные документа (включая type, order_id, user_id, contract_number, params)
     * @return void
     */
    private function createAgreementDocuments(array $data): void
    {
        // Создаем основной документ (Арбитражное соглашение или штраф КД — в зависимости от выбранного типа)
        Core::instance()->documents->create_document($data);

        // Дополнительные документы
        $additionalTypes = [
            Documents::OFFER_AGREEMENT,
            Documents::ASP_AGREEMENT,
            Documents::OFFER_ARBITRATION,
        ];

        foreach ($additionalTypes as $type) {
            $docData = $data;
            $docData['type'] = $type;

            try {
                Core::instance()->documents->create_document($docData);
            } catch (Exception $e) {
                error_log(sprintf(
                    '[ArbitrationAgreementsGenerator] Ошибка создания документа %s for contract %s: %s',
                    $type,
                    $data['contract_number'] ?? '',
                    $e->getMessage()
                ));
            }
        }
    }

    /**
     * Получает данные из Excel файла
     * @param $filePath
     * @return \Generator
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws Exception
     */
    private function getDataFromFile($filePath): Generator
    {
        $reader = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($filePath));
        $sheet = $reader->load($filePath)->getActiveSheet();

        $highestRow = $sheet->getHighestRow();

        foreach ($sheet->getRowIterator(2, $highestRow) as $row) {
            $cell = $row->getCellIterator()->current();
            $loanNumber = trim($cell->getValue());

            if (!empty($loanNumber)) {
                yield $loanNumber;
            }
        }
    }

    /**
     * Проверяет условия для создания арбитражного соглашения и возвращает статус
     * 1 - все проверки прошли
     * 2 - неверный формат номера договора
     * 3 - контракт не найден
     * 4 - пустой АСП
     * 5 - пустая дата выдачи
     * 6 - пользователь не найден
     * 7 - баланс пользователя не найден
     * 8 - невалидная дата платежа
     *
     * @param string $loanNumber
     * @return int
     * @throws Exception
     */
    private function getContractValidStatus(string $loanNumber): int
    {
        // Проверяем формат договора Аквариус
        /*if (!preg_match('/A\d{2}-\d+/', $loanNumber)) {
            return self::INVALID_CONTRACT_NUMBER;
        }*/

        // Получаем контракт и проверяем АСП и дату выдачи
        $contract = $this->getContract($loanNumber);
        if (empty($contract)) {
            return self::CONTRACT_NOT_FOUND;
        }

        if (empty($contract->asp)) {
            return self::EMPTY_ASP;
        }

        if (empty($contract->issuance_date)) {
            return self::EMPTY_ISSUANCE_DATE;
        }

        // Получаем данные пользователя
        $user = Core::instance()->users->get_user($contract->user_id);
        if (!$user) {
            return self::USER_NOT_FOUND;
        }

        // Получаем баланс пользователя
        $userBalance = Core::instance()->users->get_user_balance($user->id);
        if (!$userBalance) {
            return self::USER_BALANCE_NOT_FOUND;
        }

        if (!$this->isValidPaymentDate($userBalance->payment_date)) {
            return self::INVALID_PAYMENT_DATE;
        }

        return self::CONTRACT_VALID;
    }

    /**
     * Проверяет есть ли арбитражное соглашение
     * @param string $loanNumber
     * @return array
     * @throws Exception
     */
    private function hasArbitrationAgreement(string $loanNumber): array
    {
        return Core::instance()->documents->get_documents([
            'type' => $this->documentType,
            'contract_number' => $loanNumber
        ]);
    }

    /**
     * Проверяет есть ли соглашение об акцепте оферты
     * @param string $loanNumber
     * @return array
     * @throws Exception
     */
    private function hasOfferAgreement(string $loanNumber): array
    {
        return Core::instance()->documents->get_documents([
            'type' => Documents::OFFER_AGREEMENT,
            'contract_number' => $loanNumber
        ]);
    }

    /**
     * Проверяет есть ли соглашение об использовании АСП
     * @param string $loanNumber
     * @return array
     * @throws Exception
     */
    private function hasAspAgreement(string $loanNumber): array
    {
        return Core::instance()->documents->get_documents([
            'type' => Documents::ASP_AGREEMENT,
            'contract_number' => $loanNumber
        ]);
    }

    /**
     * Проверяет есть ли Соглашение о подписании молчанием
     * @param string $loanNumber
     * @return array
     * @throws Exception
     */
    private function hasOfferArbitration(string $loanNumber): array
    {
        return Core::instance()->documents->get_documents([
            'type' => Documents::OFFER_ARBITRATION,
            'contract_number' => $loanNumber
        ]);
    }

    /**
     * Удаляет переданные документы по id
     * @param array $documents
     * @return void
     */
    private function removeDocuments(array $documents): void
    {
        if (empty($documents)) {
            return;
        }
        foreach ($documents as $document) {
            Core::instance()->documents->delete_document($document->id);
        }
    }

    /**
     * Чистит существующие документы по номеру договора (во избежание дублей)
     *
     * @param string $loanNumber
     * @return void
     * @throws Exception
     */
    private function cleanupExistingDocuments(string $loanNumber): void
    {
        // Проверяем наличие арбитражного соглашения и удаляем все связанные документы
        $this->removeDocuments($this->hasArbitrationAgreement($loanNumber));

        // Проверяем и удаляем документы об акцепте оферты
        $this->removeDocuments($this->hasOfferAgreement($loanNumber));

        // Проверяем и удаляем документы об использовании АСП
        $this->removeDocuments($this->hasAspAgreement($loanNumber));

        // Проверяем и удаляем документы Оферты о заключении арбитражного соглашения
        $this->removeDocuments($this->hasOfferArbitration($loanNumber));
    }

    /**
     * Проверяет установленна ли дата платежа
     * @param string|null $paymentDate
     * @return bool
     */
    private function isValidPaymentDate(?string $paymentDate): bool
    {
        return !empty($paymentDate) || !in_array($paymentDate, ['0001-01-01 00:00:00', '0001-01-01T00:00:00']);
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
     * Сохраняет необработанные договоры в Excel файл
     * @param array $unprocessedContracts
     * @throws \PHPExcel_Exception
     */
    private function saveUnprocessedContractsToFile(array $unprocessedContracts): void
    {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->setTitle('Необработанные договоры');
        $activeSheet->setCellValue('A1', 'Номер договора');

        $row = 2;
        foreach ($unprocessedContracts as $contract) {
            $activeSheet->setCellValue('A' . $row, $contract);
            $row++;
        }

        $filename = 'unprocessed_contracts_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = $this->upload_file_path . $filename;

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filepath);
    }

    /**
     * Скачивание файла с необработанными договорами
     */
    public function actionDownloadUnprocessedContracts()
    {
        $filename = Core::instance()->request->post('filename');

        if (empty($filename)) {
            throw new Exception('Имя файла не указано');
        }

        $filepath = $this->upload_file_path . $filename;

        if (!file_exists($filepath)) {
            throw new Exception('Файл не найден');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        unlink($filepath);
        exit;
    }

    /**
     * Проверяет наличие всех 3 документов в ЛК клиента
     * @param string $loanNumber
     * @return bool
     * @throws Exception
     */
    private function verifyContractDocuments(string $loanNumber): bool
    {
        // Проверяем арбитражное соглашение
        $arbitrationDocs = Core::instance()->documents->get_documents([
            'type' => $this->documentType,
            'contract_number' => $loanNumber,
            'client_visible' => 1
        ]);

        // Проверяем соглашение об акцепте оферты
        $offerDocs = Core::instance()->documents->get_documents([
            'type' => Documents::OFFER_AGREEMENT,
            'contract_number' => $loanNumber,
            'client_visible' => 1
        ]);

        // Проверяем соглашение об использовании АСП
        $aspDocs = Core::instance()->documents->get_documents([
            'type' => Documents::ASP_AGREEMENT,
            'contract_number' => $loanNumber,
            'client_visible' => 1
        ]);

        // Все 3 документа должны присутствовать и быть видимыми в ЛК
        return !empty($arbitrationDocs) && !empty($offerDocs) && !empty($aspDocs);
    }
}

new ArbitrationAgreementsGenerator;
