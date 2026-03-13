<?php

namespace App\Containers\DomainSection\Tickets\Actions;

use App\Containers\DomainSection\Tickets\Tables\TsTicketTable;

class CreateTsTicketsAction
{
    public function execute(): int
    {
        $table = TsTicketTable::getInstance();

        $filter = [
            '>created_at' => new \DateTime('2025-08-01 00:00:00'),
            'subject_id' => $table->getSubjectId(),
        ];

        $i = 0;
        foreach ($table->get([], $filter)->getResult() as $ticket) {
            $i++;
            $table->initTsTicket($ticket->getId());
        }

        return $i;
    }
}