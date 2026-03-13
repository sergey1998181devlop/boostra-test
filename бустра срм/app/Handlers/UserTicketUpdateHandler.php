<?php

namespace App\Handlers;

use App\Contracts\UserTicketHandlerContract;
use App\Enums\UserTicketStatuses;
use App\Models\UserTicket;
use InvalidArgumentException;

class UserTicketUpdateHandler implements UserTicketHandlerContract
{
    public function handle(array $data): void
    {
        if (!isset($data['trigger']['new_status'], $data['trigger']['ticket_id'])) {
            throw new InvalidArgumentException('Invalid data for update handler');
        }

        $usedeskTicketId = $data['trigger']['ticket_id'];
        $newStatusName = $this->getStatusName($data['trigger']['new_status']);

        $this->updateTicket($newStatusName, $usedeskTicketId);
    }

    private function getStatusName(int $status): string
    {
        return UserTicketStatuses::getStatusName($status);
    }

    private function updateTicket(string $newStatus, int $usedeskTicketId): void
    {
        (new UserTicket())->update(['status' => $newStatus], ['usedesk_id' => $usedeskTicketId]);
    }
}
