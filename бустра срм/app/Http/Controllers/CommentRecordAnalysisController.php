<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Dto\CommentRecordAnalysisDto;
use App\Handlers\RecordAnalysis\BoostraRecordAnalysisSender;
use App\Models\Comment;
use App\Models\CommentRecordAnalysis;

class CommentRecordAnalysisController
{
    public function save(Request $request): Response
    {
        $body = $request->json('body', []);

        if (empty($body)) {
            logger('mango')->info('CommentRecordAnalysisController::save: ' . "Неверный формат запроса: отсутствует body: " . json_encode($body));
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует body'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comment = (new Comment())->get(['id', 'created', 'block', 'text'], ['id' => $body['comment_id']])->getData();

        if (!$comment) {
            logger('mango')->info('CommentRecordAnalysisController::save: ' . "Комментарий не найден: " . json_encode($body));
            return response()->json([
                'message' => 'Комментарий не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        $analysisExist = (new CommentRecordAnalysis())->has(['comment_id' => $body['comment_id']])->getData();

        if ($analysisExist) {
            logger('mango')->info('CommentRecordAnalysisController::save: ' . "Анализ для данного звонка уже создан");
            return response()->json([
                'message' => 'Анализ для данного звонка уже создан'
            ], Response::HTTP_OK); // Меняем на ОК
        }

        $data = (new CommentRecordAnalysisDto($body, $comment))->jsonSerialize();

        logger('mango')->info('CommentRecordAnalysisController::save: ' . "data: " . json_encode($data));

        (new CommentRecordAnalysis())->insert($data);

        logger('mango')->info('CommentRecordAnalysisController::save: ' . "Анализ звонка успешно сохранен");
        return response()->json(['message' => 'Анализ звонка успешно сохранен'], Response::HTTP_OK);
    }

    public function send(Request $request): Response
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $tag = $request->input('tag');

        if (empty($dateFrom)) {
            logger('mango')->info('CommentRecordAnalysisController::send: ' . "Неверный формат запроса: отсутствует dateFrom");
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует dateFrom'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (empty($dateTo)) {
            logger('mango')->info('CommentRecordAnalysisController::send: ' . "Неверный формат запроса: отсутствует dateTo");
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует dateTo'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pageSize = 30;

        $boostraSender = new BoostraRecordAnalysisSender();

        $totalCount = $boostraSender->sendBatch($dateFrom, $dateTo, $tag, $pageSize);

        logger('mango')->info('CommentRecordAnalysisController::send: ' . "Отправлено на анализ ИИ total=$totalCount");

        return response()->json([
            'message' => $totalCount . ' Звонков успешно отправлено на анализ ИИ',
            'details' => [
                'total_count' => $totalCount,
            ],
        ], Response::HTTP_OK);
    }
}
