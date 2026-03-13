<?php

if (!defined('ROOT'))
    define('ROOT', dirname(__DIR__));

/**
 * Скоринг для проверки актуальности ССП и КИ отчетов
 */
class Report extends Simpla
{
    /** @var int Время ожидания скоринга (в минутах). Не уменьшать, так как отчеты изначально получаем во время скоринга акси, поэтому необходимо ждать завершения акси */
    private const REPORT_SCORING_TIME_LIMIT = 6;

    /** @var string Лог файл */
    private const LOG_FILE = 'report.txt';

    /**
     * @param stdClass $scoring
     * @return void
     */
    public function run_scoring(stdClass $scoring): void
    {
        if ((int)$scoring->status === $this->scorings::STATUS_NEW) {
            $this->scorings->update_scoring($scoring->id, array(
                'status' => $this->scorings::STATUS_PROCESS,
                'start_date' => date('Y-m-d H:i:s')
            ));
        } elseif ((int)$scoring->status === $this->scorings::STATUS_WAIT) {
            $this->scorings->update_scoring($scoring->id, array(
                'status' => $this->scorings::STATUS_PROCESS
            ));
        } elseif (empty($scoring->start_date)) {
            $this->scorings->update_scoring($scoring->id, array(
                'start_date' => date('Y-m-d H:i:s')
            ));
        }

        $order = $this->orders->get_order((int)$scoring->order_id);

        if (empty($order)) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Не найдена заявка',
                'end_date' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Отказ по заявке',
                'end_date' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        if (!$this->needCheckReports((int)$order->order_id)) {
            $this->finishReportScoring((int)$order->order_id, (int)$scoring->id);
            return;
        }

        try {
            $this->checkReportsDateForScoring($order, $scoring);
        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];

            $this->scorings->update_scoring($scoring->id, [
                'status' => $this->scorings::STATUS_ERROR,
                'string_result' => 'Ошибка при выполнении скоринга',
                'end_date' => date('Y-m-d H:i:s'),
            ]);

            $this->logging(__METHOD__, '', ['scoring_id' => $scoring->id], ['error' => $error], self::LOG_FILE);
        }
    }

    /**
     * Проверяет необходимость проверки ССП и КИ отчетов согласно настройкам
     *
     * @param int $orderId
     * @param int $scoringId
     * @return void
     */
    private function finishReportScoring(int $orderId, int $scoringId): void
    {
        if (!$this->isCheckReportsForAllLoansEnabled()) {
            $this->scorings->update_scoring($scoringId, [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'string_result' => 'Отключена проверка актуальности ССП и КИ отчетов для всех заявок',
                'end_date' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!$this->isCheckReportsForCurrentLoanEnabled($orderId)) {
            $this->scorings->update_scoring($scoringId, [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'string_result' => 'Отключена проверка актуальности ССП и КИ отчетов для данной заявки',
                'end_date' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Нужно ли проверять ССП и КИ отчетов для заявки
     *
     * @param int $orderId
     * @return bool
     */
    public function needCheckReports(int $orderId): bool
    {
        return $this->isCheckReportsForAllLoansEnabled() && $this->isCheckReportsForCurrentLoanEnabled($orderId);
    }

    /**
     * Проверка отключения проверки ССП и КИ отчетов для ВСЕХ заявок
     *
     * @return bool
     */
    private function isCheckReportsForAllLoansEnabled(): bool
    {
        return (bool)$this->settings->check_reports_for_loans_enable;
    }

    /**
     * Проверка отключения проверки ССП и КИ отчетов для ДАННОЙ заявки
     *
     * @param int $orderId
     * @return bool
     */
    private function isCheckReportsForCurrentLoanEnabled(int $orderId): bool
    {
        return empty($this->order_data->read($orderId, $this->order_data::DISABLE_CHECK_REPORTS_FOR_LOAN));
    }

    /**
     * Проверить превышено ли максимальное допустимое время выполнения скоринга
     *
     * @param stdClass $scoring
     * @return bool
     */
    public function isScoringTimeOut(stdClass $scoring): bool
    {
        $minutesAfterCreatingLastReportScorings = (int)((time() - (int)strtotime($scoring->created)) / 60);
        return $minutesAfterCreatingLastReportScorings >= self::REPORT_SCORING_TIME_LIMIT;
    }

    private function checkReportsDateForScoring(stdClass $order, stdClass $scoring): void
    {
        $curDate = new DateTimeImmutable();

        $result = $this->checkReportsDate($order, $curDate);

        $update = [];
        if (empty($result)) {
            $update['string_result'] = 'Результат отсутствует';
            $update['status'] = $this->isScoringTimeOut($scoring) ? $this->scorings::STATUS_ERROR : $this->scorings::STATUS_WAIT;
        } elseif (empty($result['success'])) {
            $update['string_result'] = $result['message'] ?? 'Некорректный результат';
            $update['status'] = $this->isScoringTimeOut($scoring) ? $this->scorings::STATUS_ERROR : $this->scorings::STATUS_WAIT;
        } else {
            $update['string_result'] = $result['message'] ?? 'Некорректный успешный результат';
            $update['body'] = $result['body'] ?? 'Некорректный успешный результат скоринга';
            $update['status'] = $result['body'] ? $this->scorings::STATUS_COMPLETED : $this->scorings::STATUS_ERROR;
            $update['success'] = $result['body'] ? 1 : 0;
        }

        if (in_array($update['status'], [$this->scorings::STATUS_COMPLETED, $this->scorings::STATUS_ERROR])) {
            $update['end_date'] = date('Y-m-d H:i:s');
        }

        $this->scorings->update_scoring($scoring->id, $update);
    }

    /**
     * Проверяет актуальность и актуализирует (при необходимости) ССП и КИ отчеты финлаба
     *
     * @param stdClass $order
     * @param DateTimeInterface $dateToCompare
     * @param bool $disableInquiringNewReports
     * @return array
     */
    public function checkCrossOrderReportsDate(stdClass $order, DateTimeInterface $dateToCompare, bool $disableInquiringNewReports = false): array
    {
        // 1. Проверяем дату сохраненных отчетов
        $reportCheckResult[$this->axi::SSP_REPORT] = $this->checkReportDate($order, $this->axi::SSP_REPORT, $dateToCompare);
        $reportCheckResult[$this->axi::CH_REPORT] = $this->checkReportDate($order, $this->axi::CH_REPORT, $dateToCompare);

        if (
            !empty($reportCheckResult[$this->axi::SSP_REPORT]['success']) &&
            !empty($reportCheckResult[$this->axi::CH_REPORT]['success'])
        ) {
            $result = $this->formatReportCheckResult($reportCheckResult);
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                $result, self::LOG_FILE);
            return $result;
        }

        $reportsType = [];
        if (empty($reportCheckResult[$this->axi::SSP_REPORT]['success'])) {
            $reportsType[] = $this->axi::SSP_REPORT;
        }

        if (empty($reportCheckResult[$this->axi::CH_REPORT]['success'])) {
            $reportsType[] = $this->axi::CH_REPORT;
        }

        // 2. Если неактуальны, перезапрашиваем из питоновского адаптера
        foreach ($reportsType as $reportType) {
            $reportCheckResult[$reportType] = $this->checkReportDate($order, $reportType, $dateToCompare);

            if (!empty($reportCheckResult[$reportType]['success'])) {
                continue;
            }

            if (!$disableInquiringNewReports && $this->needAddPythonScoring($order, $reportType)) {
                $this->report->inquireNewReportsForFinlab($order, $reportType);
            }

            $reportCheckResult[$reportType] = $this->checkReportDate($order, $reportType, $dateToCompare);
        }

        $result = $this->formatReportCheckResult($reportCheckResult);

        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            $result, self::LOG_FILE);

        return $result;
    }

    /**
     * @param stdClass $order
     * @param string $reportType
     * @return bool
     */
    public function needAddPythonScoring(stdClass $order, string $reportType): bool
    {
        if ($reportType === $this->axi::SSP_REPORT) {
            $scoringType = $this->scorings::TYPE_PYTON_SMP;
        } elseif ($reportType === $this->axi::CH_REPORT) {
            $scoringType = $this->scorings::TYPE_PYTON_NBKI;
        } else {
            return true;
        }

        $isCreditReportsDisabled = $this->order_data->read($order->order_id ?? $order->id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);
        if (!empty($isCreditReportsDisabled)) {
            return false;
        }

        $lastScoring = $this->scorings->get_last_type_scoring($scoringType, $order->user_id);

        if (empty($lastScoring)) {
            return true;
        }

        $minutesAfterCreatingLastScoring = (int)((time() - (int)strtotime($lastScoring->created)) / 60);

        // Не добавляем скоринг, если скоринг еще выполняется ИЛИ предыдущий скоринг был добавлен недавно
        if (in_array((int)$lastScoring->status, $this->axi::AXI_SCORING_PROGRESS_STATUSES) || $minutesAfterCreatingLastScoring < $this->axi::AXI_SCORING_TIME_LIMIT) {
            return false;
        }

        return true;
    }
    private function formatReportCheckResult(array $reportCheckResult): array
    {
        $body = [];
        if (!empty($reportCheckResult[$this->axi::SSP_REPORT]['success'])) {
            $body['SSP_NBKI_REPORT_DATE'] = date('Y-m-d H:i:s', strtotime($reportCheckResult[$this->axi::SSP_REPORT]['message']));
            $reportCheckResult[$this->axi::SSP_REPORT]['message'] =
                'Найден актуальный ' . $this->axi->getReportTypeRus($this->axi::SSP_REPORT) . ' отчет от ' . $reportCheckResult[$this->axi::SSP_REPORT]['message'];
        }

        if (!empty($reportCheckResult[$this->axi::CH_REPORT]['success'])) {
            $body['NBKI_REPORT_DATE'] = date('Y-m-d H:i:s', strtotime($reportCheckResult[$this->axi::CH_REPORT]['message']));
            $reportCheckResult[$this->axi::CH_REPORT]['message'] =
                'Найден актуальный ' . $this->axi->getReportTypeRus($this->axi::CH_REPORT) . ' отчет от ' . $reportCheckResult[$this->axi::CH_REPORT]['message'];
        }

        $isSuccess = !empty($reportCheckResult[$this->axi::SSP_REPORT]['success']) && !empty($reportCheckResult[$this->axi::CH_REPORT]['success']);

        $result = [
            'message' => $reportCheckResult[$this->axi::SSP_REPORT]['message'] . '. ' . $reportCheckResult[$this->axi::CH_REPORT]['message'],
            'success' => $isSuccess,
        ];

        if (!empty($body)) {
            $result['body'] = json_encode($body);
        }

        return $result;
    }


    /**
     * Проверяет актуальность и актуализирует (при необходимости) ССП и КИ отчеты
     *
     * @param stdClass $order
     * @param DateTimeInterface $dateToCompare
     * @param bool $disableInquiringNewReports
     * @return array
     */
    public function checkReportsDate(stdClass $order, DateTimeInterface $dateToCompare, bool $disableInquiringNewReports = false): array
    {
        $appData = $this->axi->getAppData($order->order_id);
        if (empty($appData->app_id)) {
            $result = $this->axi->addAxiScoring($order);

            if ($result) {
                $message = 'Добавлен скоринг акси';
            } else {
                $message = 'Не добавлен скоринг акси, так как он был добавлен ранее';
            }

            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                $message, self::LOG_FILE);
            return ['success' => false, 'message' => 'Ожидание завершения скоринга акси'];
        }

        // 1. Проверяем дату сохраненных отчетов
        $reportCheckResult[$this->axi::SSP_REPORT] = $this->checkReportDate($order, $this->axi::SSP_REPORT, $dateToCompare);
        $reportCheckResult[$this->axi::CH_REPORT] = $this->checkReportDate($order, $this->axi::CH_REPORT, $dateToCompare);

        if (
            !empty($reportCheckResult[$this->axi::SSP_REPORT]['success']) &&
            !empty($reportCheckResult[$this->axi::CH_REPORT]['success'])
        ) {
            $result = $this->formatReportCheckResult($reportCheckResult);
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                $result, self::LOG_FILE);
            return $result;
        }

        $reportsType = [];
        if (empty($reportCheckResult[$this->axi::SSP_REPORT]['success'])) {
            $reportsType[] = $this->axi::SSP_REPORT;
        }

        if (empty($reportCheckResult[$this->axi::CH_REPORT]['success'])) {
            $reportsType[] = $this->axi::CH_REPORT;
        }

        // 2. Если отчеты неактуальны, перезапрашиваем из акси по app_id из s_axilink
        $this->dbrainAxi->saveChData($appData->app_id, $order->order_id, [$this->dbrainAxi::STATUS_RESPONSE, $this->dbrainAxi::STATUS_REQUEST], $reportsType);

        foreach ($reportsType as $reportType) {

            $reportCheckResult[$reportType] = $this->checkReportDate($order, $reportType, $dateToCompare);

            if (!empty($reportCheckResult[$reportType]['success'])) {
                continue;
            }

            $axiRequestLog = $this->ssp_nbki_request_log->getLog([
                'order_id' => $order->order_id,
                'request_type' => $reportType
            ]);

            // 3. Если отчеты неактуальны, перезапрашиваем из акси по app_id из ssp_nbki_request_log
            if (!empty($axiRequestLog->app_id) && $appData->app_id !== $axiRequestLog->app_id) {
                $this->dbrainAxi->saveChData($axiRequestLog->app_id, $order->order_id, [$this->dbrainAxi::STATUS_RESPONSE, $this->dbrainAxi::STATUS_REQUEST], [$reportType]);

                $reportCheckResult[$reportType] = $this->checkReportDate($order, $reportType, $dateToCompare);

                if (!empty($reportCheckResult[$reportType]['success'])) {
                    continue;
                }
            }

            if (!$disableInquiringNewReports) {
                // 4. Если отчеты неактуальны, запрашиваем новые отчеты из акси
                $reportCheckResult[$reportType] = $this->report->inquireNewReports($order, $reportType, $appData);
            }

            if (empty($reportCheckResult[$reportType]['success'])) {
                continue;
            }

            $reportCheckResult[$reportType] = $this->checkReportDate($order, $reportType, $dateToCompare);
        }

        $result = $this->formatReportCheckResult($reportCheckResult);

        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            $result, self::LOG_FILE);

        return $result;
    }

    /**
     * Запросить новые отчеты
     *
     * @param stdClass $order
     * @param string $reportType
     * @param stdClass $appData
     * @return array
     */
    private function inquireNewReports(stdClass $order, string $reportType, stdClass $appData): array
    {
        $isSspReportSaved = false;
        $isChReportSaved = false;

        if ($reportType === $this->axi::SSP_REPORT) {
            $isSspReportSaved = $this->axi->inquireNewReportFromAxi($order, $appData, $this->axi::SSP_REPORT);
            $isChReportSaved = true;
        } elseif ($reportType === $this->axi::CH_REPORT) {
            $isChReportSaved = $this->axi->inquireNewReportFromAxi($order, $appData, $this->axi::CH_REPORT);
            $isSspReportSaved = true;
        }

        if (!$isSspReportSaved || !$isChReportSaved) {
            $message = 'Возникла ошибка при попытке актуализации ' . $this->axi->getReportTypeRus($reportType) . ' отчета';
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $message, self::LOG_FILE);
            return ['success' => false, 'message' => $message];
        }

        $message = 'Запрошен новый ' . $this->axi->getReportTypeRus($reportType) . ' отчет из акси';
        $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $message, self::LOG_FILE);
        return ['success' => true, 'message' => $message];
    }

    /**
     * Запросить новые отчеты для Финлаб
     *
     * @param stdClass $order
     * @param string $reportType
     * @return void
     */
    private function inquireNewReportsForFinlab(stdClass $order, string $reportType): void
    {
        if ($reportType === $this->axi::SSP_REPORT) {
            try {
                $this->pyton_smp->process($order);

                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    'Запрошен новый ' . $this->axi->getReportTypeRus($reportType) . ' отчет по заявке', self::LOG_FILE);
            } catch (\Exception $e) {
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    'Не удалось запросить ' . $this->axi->getReportTypeRus($reportType) . ' отчет по заявке. ' . $e->getMessage(), self::LOG_FILE);
            }
        }

        if ($reportType === $this->axi::CH_REPORT) {
            try {
                $this->pyton_nbki->process($order);

                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    'Запрошен новый ' . $this->axi->getReportTypeRus($reportType) . ' отчет по заявке', self::LOG_FILE);
            } catch (\Exception $e) {
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    'Не удалось запросить ' . $this->axi->getReportTypeRus($reportType) . ' отчет по заявке. ' . $e->getMessage(), self::LOG_FILE);
            }
        }
    }

    /**
     * Проверяет актуальность ССП или КИ отчета
     *
     * @param stdClass $order
     * @param string $reportType
     * @param DateTimeInterface|null $dateToCompare
     * @return array
     */
    private function checkReportDate(stdClass $order, string $reportType, DateTimeInterface $dateToCompare): array
    {
        $fileRow = $this->credit_history->getRow([
            'order_id' => $order->order_id,
            'type' => $reportType,
        ]);

        if (!empty($fileRow)) {
            $s3_file = $this->s3_api_client->getFileContent($fileRow->s3_name);
            $fileContent = $s3_file->getContents();
        } else {
            $filePath = $this->getReportFilePath($order, $reportType);

            if (!file_exists($filePath)) {
                $message = $this->axi->getReportTypeRus($reportType) . ' отчет не создан';
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    $message, self::LOG_FILE);
                return ['success' => false, 'message' => $message];
            }

            $fileContent = file_get_contents($filePath);
        }

        if (empty($fileContent)) {
            $message = $this->axi->getReportTypeRus($reportType) . ' отчет пустой';
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                $message, self::LOG_FILE);
            return ['success' => false, 'message' => $message];
        }

        if (stripos($fileContent, 'encoding="windows-1251"') !== false) {
            $fileContent = str_ireplace('encoding="windows-1251"', 'encoding="UTF-8"', $fileContent);
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($fileContent);

        if (empty($xml)) {
            $fileContent = iconv("windows-1251", "UTF-8", $fileContent);
            $xml = simplexml_load_string($fileContent);

            if (empty($xml)) {
                $message = 'Некорректный формат файла ' . $this->axi->getReportTypeRus($reportType) . ' отчета';
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    $message, self::LOG_FILE);
                return ['success' => false, 'message' => $message];
            }
        }

        if ($reportType === $this->axi::SSP_REPORT) {
            $startingXmlTag = $xml->Сведения ?? $xml;

            if (!empty($startingXmlTag->КБКИ)) {
                foreach ($startingXmlTag->КБКИ as $value) {
                    $attributes = $value->attributes();
                    $reportDate = (string)$attributes['ПоСостояниюНа'];

                    $checkDateResult = $this->checkDate($reportDate, $this->axi::SSP_REPORT, $dateToCompare);

                    if (empty($checkDateResult['success'])) {
                        $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $checkDateResult['message'], self::LOG_FILE);
                        return ['success' => false, 'message' => $checkDateResult['message']];
                    }
                }
            }

            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                'Найден актуальный ' . $this->axi->getReportTypeRus($reportType) . ' отчет от ' . $checkDateResult['message'], self::LOG_FILE);
            return ['success' => true, 'message' => $checkDateResult['message'] ?? ''];
        } else if ($reportType === $this->axi::CH_REPORT) {
            foreach ($xml->preply->report as $value) {
                $reportDate = (string)$value->reportIssueDateTime;

                $checkDateResult = $this->checkDate($reportDate, $this->axi::CH_REPORT, $dateToCompare);

                if (empty($checkDateResult['success'])) {
                    $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $checkDateResult['message'], self::LOG_FILE);
                    return ['success' => false, 'message' => $checkDateResult['message']];
                }
            }

            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                'Найден актуальный ' . $this->axi->getReportTypeRus($reportType) . ' отчет от ' . $checkDateResult['message'], self::LOG_FILE);
            return ['success' => true, 'message' => $checkDateResult['message'] ?? ''];
        }

        $message = 'Неверный тип отчета';
        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            $message, self::LOG_FILE);
        return ['success' => false, 'message' => $message];
    }


    private function checkSspReportDate(stdClass $order, string $reportType, DateTimeInterface $dateToCompare): array
    {
        $reportXmlResult = $this->getReportXml($order, $reportType);

        if (empty($reportXmlResult['success']) || empty($reportXmlResult['xml'])) {
            return $reportXmlResult;
        }

        $xml = $reportXmlResult['xml'];

        $startingXmlTag = $xml->Сведения ?? $xml;

        if (empty($startingXmlTag->КБКИ)) {
            return ['success' => false, 'message' => 'Отсутствует дата создания ' . $this->axi->getReportTypeRus($reportType) . ' отчета'];
        }

        $checkDateResult = [];
        foreach ($startingXmlTag->КБКИ as $value) {
            $attributes = $value->attributes();
            $reportDate = (string)$attributes['ПоСостояниюНа'];

            $checkDateResult = $this->checkDate($reportDate, $this->axi::SSP_REPORT, $dateToCompare);

            if (empty($checkDateResult['success'])) {
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $checkDateResult['message'], self::LOG_FILE);
                return ['success' => false, 'message' => $checkDateResult['message']];
            }
        }

        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            'Найден актуальный ' . $this->axi->getReportTypeRus($reportType) . ' отчет от ' . $checkDateResult['message'], self::LOG_FILE);
        return ['success' => true, 'message' => $checkDateResult['message'] ?? ''];
    }

    private function checkChReportDate(stdClass $order, string $reportType, DateTimeInterface $dateToCompare): array
    {
        $reportXmlResult = $this->getReportXml($order, $reportType);

        if (empty($reportXmlResult['success']) || empty($reportXmlResult['xml'])) {
            return $reportXmlResult;
        }

        $xml = $reportXmlResult['xml'];

        if (empty($xml->preply->report)) {
            return ['success' => false, 'message' => 'Отсутствует дата создания ' . $this->axi->getReportTypeRus($reportType) . ' отчета'];
        }

        $checkDateResult = [];
        foreach ($xml->preply->report as $value) {
            $reportDate = (string)$value->reportIssueDateTime;

            $checkDateResult = $this->checkDate($reportDate, $this->axi::CH_REPORT, $dateToCompare);

            if (empty($checkDateResult['success'])) {
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id], $checkDateResult['message'], self::LOG_FILE);
                return ['success' => false, 'message' => $checkDateResult['message']];
            }
        }

        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            'Найден актуальный ' . $this->axi->getReportTypeRus($reportType) . ' отчет от ' . $checkDateResult['message'], self::LOG_FILE);
        return ['success' => true, 'message' => $checkDateResult['message'] ?? ''];
    }

    private function getReportXml(stdClass $order, string $reportType): array
    {
        //        $fileRow = $this->credit_history->getRow([
//            'order_id' => $order->order_id,
//            'type' => $reportType,
//        ]);

        // Проверяем файл в s3
        if (!empty($fileRow)) {
//            $s3_file = $this->s3_api_client->getFileContent($fileRow->s3_name);
//            $fileContent = $s3_file->getContents();
        } else {
            $filePath = $this->getReportFilePath($order, $reportType);

            // Проверяем файл локально
            if (!file_exists($filePath)) {
                $message = $this->axi->getReportTypeRus($reportType) . ' отчет не создан';
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    $message, self::LOG_FILE);
                return ['success' => false, 'message' => $message];
            }

            $fileContent = file_get_contents($filePath);
        }

        if (empty($fileContent)) {
            $message = $this->axi->getReportTypeRus($reportType) . ' отчет пустой';
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                $message, self::LOG_FILE);
            return ['success' => false, 'message' => $message];
        }

        // Меняем кодировку на UTF-8 для корректной работы simplexml_load_string
        if (stripos($fileContent, 'encoding="windows-1251"') !== false) {
            $fileContent = str_ireplace('encoding="windows-1251"', 'encoding="UTF-8"', $fileContent);
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($fileContent);

        if (empty($xml)) {
            $fileContent = iconv("windows-1251", "UTF-8", $fileContent);
            $xml = simplexml_load_string($fileContent);

            if (empty($xml)) {
                $message = 'Некорректный формат файла ' . $this->axi->getReportTypeRus($reportType) . ' отчета';
                $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                    $message, self::LOG_FILE);
                return ['success' => false, 'message' => $message];
            }
        }

        return ['success' => true, 'xml' => $xml];
    }

    /**
     * Получить путь до расположения файла ССП отчета
     *
     * @param stdClass $order
     * @param string $reportType
     * @return string
     */
    public function getReportFilePath(stdClass $order, string $reportType): string
    {
        if ($reportType === $this->axi::SSP_REPORT) {
            return $this->organizations->isFinlab((int)$order->organization_id)
                ? ROOT . '/files/finlab/CCP/' . $order->order_id . '.xml'
                : ROOT . '/files/CCP/' . $order->order_id . '.xml';
        }

        if ($reportType === $this->axi::CH_REPORT) {
            return $this->organizations->isFinlab((int)$order->organization_id)
                ? ROOT . '/files/finlab/credit_history/' . $order->order_id . '.xml'
                : ROOT . '/files/credit_history/' . $order->order_id . '.xml';
        }

        return '';
    }

    /**
     * Проверить актуальность отчета
     *
     * @param string $reportDate
     * @param string $reportType
     * @param DateTimeInterface $dateToCompare
     * @return array
     */
    private function checkDate(string $reportDate, string $reportType, DateTimeInterface $dateToCompare): array
    {
        try {
            $reportDate = new DateTimeImmutable($reportDate);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Некорректная дата создания ' . $this->axi->getReportTypeRus($reportType) . ' отчета'];
        }

        // Если дата отчета позже даты для сравнения (дата выдачи или текущая дата)
        if ($reportDate > $dateToCompare) {
            return ['success' => true, 'message' => $reportDate->format('d.m.Y H:i:s')];
        }

        $isReportRelevant = $reportDate->diff($dateToCompare)->format("%a") < $this->axi::REPORTS_RELEVANCE_MAX_DAYS;

        if (!$isReportRelevant) {
            return ['success' => false, 'message' => $this->axi->getReportTypeRus($reportType) . ' отчет неактуальный от '
                . $reportDate->format('d.m.Y H:i:s')];
        }

        return ['success' => true, 'message' => $reportDate->format('d.m.Y H:i:s')];
    }
}