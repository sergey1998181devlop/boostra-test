<?php

namespace App\Http\Controllers;

use App\Core\Application\Request\Request;
use App\Core\Application\Response\Response;
use App\Dto\UsedeskTicketAnalysisDto;
use App\Enums\UsedeskTicketPriorities;
use App\Handlers\UserTicketNewCommentHandler;
use App\Handlers\UserTicketUpdateHandler;
use App\Models\UsedeskTicketAnalysis;
use App\Models\User;
use App\Service\TelegramService;
use App\Service\UsedeskService;
use Exception;

class UsedeskController
{
    public function complaintTicket(Request $request): Response
    {
        $ticketId = $request->json('ticket')['id'] ?? null;

        if (is_null($ticketId)) {
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует ticket.id'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = config('services.usedesk.complaint_ticket_secret_key');
        $ticket = (new UsedeskService())->getTicket($token, $ticketId)['ticket'] ?? [];

        if (empty($ticket)) {
            return response()->json([
                'message' => 'Тикет не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        if (!isset($ticket['priority'])) {
            return response()->json([
                'message' => 'Неверный формат ответа от Usedesk: отсутствует priority'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($ticket['priority'] !== UsedeskTicketPriorities::EXTREME) {
            return response()->json([
                'message' => sprintf(
                    'Неверный приоритет тикета. Ожидалось: %s, получено: %s',
                    UsedeskTicketPriorities::EXTREME,
                    $ticket['priority']
                )
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = (new User())->select(['id', 'firstname', 'lastname', 'patronymic', 'phone_mobile'], [
            'email' => $ticket['email']
        ])->getData();

        $user = count($user) === 1 ? $user[0] : null;

        $this->sendComplaintTicketNotification($user, $ticket);

        return response()->json([
            'message' => 'Уведомление о письме жалобщика успешно отправлено'
        ], Response::HTTP_OK);
    }

    public function saveAnalysis(Request $request): Response
    {
        $body = $request->json('body', []);

        if (empty($body)) {
            return response()->json([
                'message' => 'Неверный формат запроса: отсутствует body'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $analysisExist = (new UsedeskTicketAnalysis())->has(['ticket_id' => $body['ticket_id']])->getData();

        if ($analysisExist) {
            return response()->json([
                'message' => 'Анализ для данного тикета уже создан'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = config('services.usedesk.api_secret_key');
        $ticket = (new UsedeskService())->getTicket($token, $body['ticket_id'])['ticket'] ?? [];

        $data = (new UsedeskTicketAnalysisDto($body, $ticket))->jsonSerialize();

        (new UsedeskTicketAnalysis())->insert($data);

        return response()->json(['message' => 'Анализ тикета успешно сохранен'], Response::HTTP_OK);
    }

    public function userTicket(Request $request): Response
    {
        $data = $request->json();

        if (isset($data['trigger']['new_status'])) {
            (new UserTicketUpdateHandler())->handle($data);

            return response()->json(['message' => 'Событие изменения обращения успешно обработано'], Response::HTTP_OK);
        } elseif (isset($data['comment'])) {
            try {
                (new UserTicketNewCommentHandler())->handle($data);
            } catch (Exception $e) {
                return response()->json(
                    ['message' => 'Ошибка обработки события нового комментария: ' . $e->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return response()->json(['message' => 'Событие нового комментария обращения успешно обработано'], Response::HTTP_OK);
        }

        return response()->json(['message' => 'Не определенное событие'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function sendComplaintTicketNotification(?array $user, array $ticket): void
    {
        $chatId = config('services.telegram.notifications_chat_id');
        $messageThreadId = config('services.telegram.complaints_tickets_thread_id');

        $ticketLink = "https://secure.usedesk.ru/tickets/{$ticket['id']}";

        if (!is_null($user)) {
            $clientLink = "https://manager.boostra.ru/client/{$user['id']}";
            $fullName = trim("{$user['lastname']} {$user['firstname']} {$user['patronymic']}");

            $message = sprintf(
                "<b>Клиент жалобщик написал на почту</b>\n" .
                "<a href='%s'>%s</a>\n\n" .
                "Клиент: <a href='%s'>%s</a>\n" .
                "Телефон: %s\n" .
                "Почта: %s",
                $ticketLink,
                $ticketLink,
                $clientLink,
                $fullName,
                '+' . $user['phone_mobile'],
                $ticket['email']
            );
        } else {
            $message = sprintf(
                "<b>Клиент жалобщик написал на почту</b>\n" .
                "<a href='%s'>%s</a>\n\n" .
                "Клиент:\n" .
                "Клиента обнаружить в СРМ не удалось\n" .
                "Почта: %s",
                $ticketLink,
                $ticketLink,
                $ticket['email'],
            );
        }

        (new TelegramService())->sendMessage(
            $chatId,
            $message,
            [
                'parse_mode' => 'HTML',
                'message_thread_id' => $messageThreadId
            ]
        );
    }
}