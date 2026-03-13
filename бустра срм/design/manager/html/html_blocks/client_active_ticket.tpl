<div class="d-flex justify-content-end align-items-center mb-1 ticket-status-highlight">
    <div class="mr-2 text-right">
        <small>
            <strong>Активный тикет:</strong>
            от {$active_ticket->created_at|date_format:'%d.%m.%Y %H:%M'}
            —
            <span style="color: {$active_ticket->status_color|default:'#000'}">
                {$active_ticket->status_name|escape}
            </span>
            — {$active_ticket->subject_name|escape}
            {if $active_ticket->duplicates_count > 0}
                <span class="text-muted">(+{$active_ticket->duplicates_count} дубл.)</span>
            {/if}
        </small>
    </div>
    <div class="btn-group btn-group-sm ticket-buttons">
        {if $active_ticket->is_highlighted}
            <button type="button" class="btn btn-success disabled ticket-buttons__btn" 
                    data-ticket-id="{$active_ticket->id}">ПОДСВЕЧЕНО</button>
        {elseif $active_ticket->status_id == 5}
            <button type="button" class="btn btn-secondary disabled ticket-buttons__btn" 
                    data-ticket-id="{$active_ticket->id}" title="Тикет уже в работе">ПОДСВЕТИТЬ</button>
        {else}
            <button type="button" class="btn btn-warning ticket-buttons__btn js-highlight-ticket" 
                    data-ticket-id="{$active_ticket->id}">ПОДСВЕТИТЬ</button>
        {/if}
        <a href="/tickets/{$active_ticket->id}" class="btn btn-primary ticket-buttons__btn"
           target="_blank">В тикет</a>
    </div>
</div>
