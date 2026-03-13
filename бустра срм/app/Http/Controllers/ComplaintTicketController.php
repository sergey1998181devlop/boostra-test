<?php

namespace App\Http\Controllers;

use api\handlers\CreateComplaintTicketHandler;
use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;

class ComplaintTicketController
{
    public function create(Request $request): Response
    {
        $phone = $request->input('phone_mobile') ?? $request->json('phone_mobile');
        $message = $request->input('message') ?? $request->json('message');
        $subjectId = $request->input('subject_id') ?? $request->json('subject_id');
        $chatLink = $request->input('link') ?? $request->json('link');
        $managerId = $request->input('manager_id') ?? $request->json('manager_id');
        $attachments = $request->input('attachments') ?? $request->json('attachments');
        $priority = $request->input('priority') ?? $request->json('priority');
        if ($managerId === null) {
            $managerId = 295;
        }

        if (empty($phone) || empty($message)) {
            return response()->json([
                'success' => false,
                'message' => 'Не заполнены обязательные поля: phone_mobile и message'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!empty($chatLink) && is_string($chatLink)) {
            $chatLink = str_replace('usedesk.com', 'usedesk.ru', $chatLink);
            if (!preg_match('/^https?:\/\//i', $chatLink)) {
                $chatLink = 'https://' . $chatLink;
            }
        }

        $params = [
            'phone_mobile' => $phone,
            'message' => $message,
            'subject_id' => $subjectId !== null ? (int)$subjectId : null,
            'link' => $chatLink,
            'manager_id' => $managerId,
            'attachments' => $attachments,
            'priority' => $priority
        ];

        try {
            $handler = new CreateComplaintTicketHandler();
            $result = $handler->handle($params);

            $status = ($result['success'] ?? false) ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
            return response()->json($result, $status);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}


