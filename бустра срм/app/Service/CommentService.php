<?php

namespace App\Service;

use App\Enums\CommentHandlers;
use Exception;
use SoapClient;
use stdClass;

class CommentService
{
    public function getResponsibleFrom1C(array $comment): array
    {
        $request = new stdClass();
        $request->CallData = json_encode($comment);

        try {
            $client = new SoapClient($this->getWebSignalUrl());
            $response = $client->__soapCall('GetResponsibleForCalls', [$request]);
        } catch (Exception $fault) {
            logger('comment_1c')->error('CommentService::getResponsibleFrom1C: SOAP call failed', [
                'error' => $fault->getMessage(),
                'exception' => get_class($fault),
                'code' => $fault->getCode(),
                'trace' => $fault->getTraceAsString(),
                'request' => [$request],
            ]);

            return [
                'error' => true,
                'message' => $fault->getMessage(),
            ];
        }

        logger('comment_1c')->info('GetResponsibleForCalls response', [
            'request' => [$request],
            'response' => $response,
            'response_type' => gettype($response)
        ]);

        $jsonResponse = $response->return ?? '';

        if (empty($jsonResponse) || !is_string($jsonResponse)) {
            return [];
        }

        $decodedResponse = json_decode($jsonResponse, true);

        if (is_array($decodedResponse)) {
            return $decodedResponse;
        }

        return [];
    }
    public function sendCommentTo1C(array $comment)
    {
        $item = $this->createCommentItem($comment);

        $request = new stdClass();
        $request->TextJson = json_encode([$item]);
        $request->Partner = 'Boostra';

        try {
            $client = new SoapClient($this->getWsdlUrl());
            $response = $client->__soapCall('Comments', [$request]);
        } catch (Exception $fault) {
            return $fault;
        }

        return $response->return ?? $response;
    }

    private function createCommentItem(array $comment): stdClass
    {
        $item = new stdClass();
        $item->НомерЗайма = $comment['number'] ?? '';
        $item->ИдентификаторКлиента = $comment['user_uid'] ?? '';
        $item->Дата = date('YmdHis');
        $item->Комментарий = $this->generateCommentContent($comment['data']);
        $item->Ответственный = $comment['manager'] ?? '';

        return $item;
    }

    private function generateCommentContent(array $data): string
    {
        $tag = $data['tag'] ?? '';
        $stage = $data['stage'] ?? '';
        $handledBy = $data['handled_by'] ?? '';
        $operatorName = $data['operator_name'] ?? '';
        $assessment = $data['assessment'] ?? '';
        $blacklisted = $data['blacklisted'] ?? '';

        $comment = "Тег: $tag\n";
        $comment .= "Стадия: $stage\n";
        $comment .= "Клиент в черном списке: $blacklisted\n";
        $comment .= "Кто обработал звонок: ";

        switch ($handledBy) {
            case CommentHandlers::AVIAR:
                $comment .= "Aviar\n";
                break;
            case CommentHandlers::OPERATOR:
                $comment .= "Оператор (ФИО): $operatorName\n";
                break;
            case CommentHandlers::MISSED:
                $comment .= "Пропущен\n";
                break;
        }

        if ($handledBy === CommentHandlers::OPERATOR && $assessment) {
            $comment .= "Оценка клиента: $assessment\n";
        }

        return $comment;
    }

    private function getWsdlUrl(): string
    {
        $baseUrl = config('services.1c.url');
        $dbName = config('services.1c.db');
        return "{$baseUrl}{$dbName}/ws/WebLK.1cws?wsdl";
    }

    private function getWebSignalUrl(): string
    {
        $baseUrl = config('services.1c.url');
        $dbName = config('services.1c.db');
        return "{$baseUrl}{$dbName}/ws/WebSignal.1cws?wsdl";
    }
}
