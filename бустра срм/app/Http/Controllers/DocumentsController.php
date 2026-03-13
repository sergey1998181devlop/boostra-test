<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Core\Logger\LoggerFactory;
use App\Service\FileStorageService;
use App\Service\OneC\OneCClient;
use App\Core\Application\Facades\DB;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;

class DocumentsController
{
    public function getContract(Request $request): Response
    {
        $logger = LoggerFactory::createLogger('app');

        $number = (string)$request->input('number');
        $docType = (string)($request->input('doc_type') ?? 'Договор');

        if ($number === '') {
            return response()->json(['message' => 'Параметр number обязателен'], 422);
        }
        if ($docType === '') {
            return response()->json(['message' => 'Параметр doc_type обязателен'], 422);
        }

        $oneC = new OneCClient();
        $docs = $oneC->getContractDocuments($number);

        if (empty($docs)) {
            return response()->json(['message' => 'Документы не найдены'], 404);
        }

        $doc = null;
        foreach ($docs as $item) {
            if (($item['ТипДокумента'] ?? '') === $docType && !empty($item['УИДХранилища'])) {
                $doc = $item;
                break;
            }
        }

        if (!$doc) {
            $logger->info('DocumentsController:getContract not found', ['number' => $number, 'doc_type' => $docType]);
            return response()->json(['message' => 'Документ не найден'], 404);
        }

        $fs = new FileStorageService(
            config('services.s3.endpoint'),
            config('services.s3.region'),
            config('services.s3.key'),
            config('services.s3.secret'),
            config('services.s3.bucket'),
        );

        $key = (string)$doc['УИДХранилища'];
        $content = $fs->downloadFile($key);

        if ($content === '') {
            $logger->error('DocumentsController:getContract storage empty', ['key' => $key]);
            return response()->json(['message' => 'Не удалось скачать файл из хранилища'], 502);
        }

        $safeDocType = preg_replace('/[^A-Za-zА-Яа-я0-9_\-\.]+/u', '_', $docType);
        $filename = $safeDocType . '-' . preg_replace('/[^A-Za-z0-9_\-]+/', '_', $number) . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . strlen($content));

        echo $content;
        exit;
    }

    public function fixDirectorName(Request $request): Response
    {
        $fromDate = $request->input('fromDate');
        $type = $request->input('type');

        $batchSize = (int)($request->input('batchSize') ?? 500);
        if ($batchSize <= 0 || $batchSize > 5000) {
            $batchSize = 500;
        }

        $updated = 0;
        $skipped = 0;

        $processed = 0;
        $lastId = 0;

        do {
            $docs = DB::db()->select('s_documents', ['id', 'type', 'params', 'created'], [
                'type'        => $type,
                'created[>=]' => $fromDate,
                'id[>]'       => $lastId,
                'ORDER'       => ['id' => 'ASC'],
                'LIMIT'       => $batchSize,
            ]);

            if (empty($docs)) {
                break;
            }

            foreach ($docs as $doc) {
                $processed++;
                $lastId = max($lastId, (int)$doc['id']);

                $params = @unserialize($doc['params']);

                if (empty($params)) {
                    $skipped++;
                    continue;
                }

                if (is_object($params)) {
                    $params = (array)$params;
                } elseif (!is_array($params)) {
                    $skipped++;
                    continue;
                }

                if (!empty($params['director_name'] ?? null)) {
                    $skipped++;
                    continue;
                }

                if (
                    !isset($params['service_name']) ||
                    !isset($params['refund_amount']) ||
                    !isset($params['document_date'])
                ) {
                    $skipped++;
                    continue;
                }

                $docDateStr  = (string)$params['document_date'];
                $docDateObj  = \DateTime::createFromFormat('d.m.Y', $docDateStr)
                    ?: \DateTime::createFromFormat('Y-m-d', $docDateStr);

                if (!$docDateObj || $docDateObj < $fromDate) {
                    $skipped++;
                    continue;
                }

                $serviceName  = (string)$params['service_name'];
                $refundAmount = (int)$params['refund_amount'];

                if (
                    $serviceName === AdditionalServiceKey::LABEL_FINANCIAL_DOCTOR
                    || ($serviceName === AdditionalServiceKey::LABEL_STAR_ORACLE && $refundAmount === 350)
                ) {
                    $params['director_name'] = 'И.Ю. Вороному';
                } else {
                    $params['director_name'] = 'Н.В. Фетисовой';
                }

                DB::db()->update(
                    's_documents',
                    ['params' => serialize($params)],
                    ['id' => $doc['id']]
                );
                $updated++;
            }
        } while (true);

        return response()->json([
            'success'   => true,
            'from'      => $fromDate,
            'batchSize' => $batchSize,
            'processed' => $processed,
            'updated'   => $updated,
            'skipped'   => $skipped,
        ]);
    }
}


