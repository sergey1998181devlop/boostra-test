<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Handlers\NotifyUserFeedbackHandler;
use App\Models\UserFeedback;

class UserFeedbackController
{
    public function notifyFeedback(Request $request): Response
    {
        $id = $request->getParam('id');

        if (is_null($id)) {
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует id'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $feedback = (new UserFeedback())->get(
            ['user_id', 'order_id', 'data'],
            ['id' => $id]
        )->getData();

        if (empty($feedback)) {
            return response()->json([
                'message' => 'Отзыв не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        $feedbackData = json_decode($feedback['data'], true);

        if (empty($feedbackData['rate']) || $feedbackData['rate'] > 2) {
            return response()->json([
                'message' => 'Отзыв не является негативным'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        (new NotifyUserFeedbackHandler())->handle($feedback);

        return response()->json([
            'message' => 'Уведомление о негативном отзыве успешно отправлено'
        ], Response::HTTP_OK);
    }
}