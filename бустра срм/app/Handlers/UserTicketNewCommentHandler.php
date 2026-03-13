<?php

namespace App\Handlers;

use App\Contracts\UserTicketHandlerContract;
use App\Enums\UserTicketStatuses;
use App\Models\UserTicket;
use App\Models\UserTicketComment;
use App\Service\FileStorageService;
use App\Service\UsedeskService;
use Exception;
use InvalidArgumentException;

class UserTicketNewCommentHandler implements UserTicketHandlerContract
{
    private FileStorageService $fileStorageService;

    public function __construct()
    {
        $this->fileStorageService = new FileStorageService(
            config('services.user_ticket_storage.url'),
            config('services.user_ticket_storage.region'),
            config('services.user_ticket_storage.access_key'),
            config('services.user_ticket_storage.secret_key'),
            config('services.user_ticket_storage.bucket'),
        );
    }

    /**
     * @throws Exception
     */
    public function handle(array $data): void
    {
        if (!isset($data['comment']['ticket_id'])) {
            throw new InvalidArgumentException('Invalid data for comment handler');
        }

        $usedeskTicketId = $data['comment']['ticket_id'];
        $usedeskTicket = $this->getUsedeskTicket($usedeskTicketId);

        $this->updateTicketStatus($usedeskTicket['ticket'], $usedeskTicketId);

        $comments = array_reverse($usedeskTicket["comments"] ?? []);

        foreach ($comments as $comment) {
            if ($comment['type'] === 'public' && $comment['from'] !== 'client') {
                $ticket = $this->getTicket($usedeskTicketId);

                if (empty($ticket)) {
                    throw new Exception("Ticket not found: $usedeskTicketId");
                }

                if ($this->commentExists($comment['id'], $ticket['id'])) {
                    continue;
                }

                $attachments = $this->processFiles($comment['files'] ?? [], $ticket['id']);

                $this->insertComment($comment, $ticket['id'], $attachments);

                $this->readingUserComments($ticket['id']);
            }
        }
    }

    private function getUsedeskTicket(int $usedeskTicketId): array
    {
        $token = config('services.usedesk.user_ticket_secret_key');

        return (new UsedeskService())->getTicket($token, $usedeskTicketId) ?? [];
    }

    private function updateTicketStatus(array $ticket, int $usedeskTicketId): void
    {
        try {
            $ticketStatusName = UserTicketStatuses::getStatusName($ticket['status_id']);

            (new UserTicket())->update(['status' => $ticketStatusName], ['usedesk_id' => $usedeskTicketId]);
        } catch (InvalidArgumentException $e) {
            // ignore status error
        }
    }

    private function getTicket(int $usedeskTicketId)
    {
        return (new UserTicket())->get(['id'], ['usedesk_id' => $usedeskTicketId])->getData();
    }

    private function processFiles(array $files, int $ticketId): array
    {
        $attachments = [];

        if (!empty($files)) {

            foreach ($files as $file) {
                $tmpFile = tempnam(sys_get_temp_dir(), 'usedesk_');
                $fileContents = @file_get_contents($file['file']);

                if ($fileContents !== false) {
                    file_put_contents($tmpFile, $fileContents);

                    $data = [
                        'tmp_name' => $tmpFile,
                        'name' => $file['name'],
                    ];

                    $uploadKey = $this->fileStorageService->uploadFile($data, "tickets/$ticketId/");

                    if ($uploadKey) {
                        $attachments[] = $uploadKey;
                    }

                    @unlink($tmpFile);
                }
            }
        }
        return $attachments;
    }

    private function insertComment(array $comment, $ticketId, array $attachments): void
    {
        (new UserTicketComment())->insert([
            'usedesk_id' => $comment['id'],
            'ticket_id' => $ticketId,
            'sender_type' => 'operator',
            'message' => $comment['message'],
            'attachments' => !empty($attachments) ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    private function readingUserComments(int $ticketId): void
    {
        (new UserTicketComment())->update(['is_read' => true], ['ticket_id' => $ticketId, 'sender_type' => 'user']);
    }

    private function commentExists(int $usedeskCommentId, int $ticketId): bool
    {
        $comment = (new UserTicketComment())
            ->get(['id'], ['usedesk_id' => $usedeskCommentId, 'ticket_id' => $ticketId])
            ->getData();

        return !empty($comment);
    }
}
