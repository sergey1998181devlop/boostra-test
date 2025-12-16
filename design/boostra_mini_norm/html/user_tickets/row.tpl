<tr class="tickets-table-row" data-id="{$ticket->id}">
    <td class="tickets-table-cell tickets-table-cell-alert">
        {if $ticket->unread_operator_count > 0}
            <span class="tickets-cell-alert" title="Есть непрочитанные сообщения"></span>
        {/if}
    </td>
    <td class="tickets-table-cell">{$ticket->usedesk_id}</td>
    <td class="tickets-table-cell">{$ticket->subject}</td>
    <td class="tickets-table-cell">{$ticket->created_at|date_format:"%d.%m.%Y %H:%M"}</td>
    <td class="tickets-table-cell">
    <span class="tickets-status
         {if $ticket->status eq "Новое"}tickets-status-new
         {elseif $ticket->status eq "Закрыто"}tickets-status-closed{/if}">
      {$ticket->status}
    </span>
    </td>
    <td class="tickets-table-cell">{$ticket->updated_at|date_format:"%d.%m.%Y %H:%M"}</td>
</tr>