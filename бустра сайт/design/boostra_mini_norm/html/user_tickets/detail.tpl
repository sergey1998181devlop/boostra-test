{$canonical="user/tickets" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}

<div class="tickets-modal-header">
    <h3 class="tickets-modal-title">Обращение #{$ticket->usedesk_id}</h3>
    <button class="ticket-detail-modal-close" aria-label="Закрыть">
        &times;
    </button>
</div>
<div class="tickets-ticket-body">
    <div class="tickets-ticket-meta">
        <div class="tickets-ticket-meta-row">
            <span class="tickets-ticket-label">Статус:</span>
            <span class="tickets-status
                            {if $ticket->status eq "Новое"}tickets-status-new
                            {elseif $ticket->status eq "Закрыто"}tickets-status-closed
                            {/if}
                        ">
                            {$ticket->status}
                        </span>
        </div>
        <div class="tickets-ticket-meta-row">
            <span class="tickets-ticket-label">Тема:</span>
            <span class="tickets-ticket-value">{$ticket->subject}</span>
        </div>
        <div class="tickets-ticket-meta-row">
            <span class="tickets-ticket-label">Создано:</span>
            <span class="tickets-ticket-value">{$ticket->created_at|date_format:"%d.%m.%Y %H:%M"}</span>
        </div>
        <div class="tickets-ticket-meta-row">
            <span class="tickets-ticket-label">Обновлено:</span>
            <span class="tickets-ticket-value">{$ticket->updated_at|date_format:"%d.%m.%Y %H:%M"}</span>
        </div>
    </div>
    <div class="tickets-ticket-chat" id="chatContainer" style="overflow-y: auto;">
        {foreach from=$ticket->comments item=comment}
            {include file='user_tickets/comment.tpl' comment=$comment}
        {/foreach}
    </div>
    <div class="tickets-ticket-reply">
        <form id="replyForm" method="POST" action="/user/tickets?action=createComment" enctype="multipart/form-data">
            <textarea name="reply_message"
                      class="tickets-reply-input"
                      rows="4"
                      placeholder="Напишите ответ..."
                      required></textarea>
            <input type="hidden" name="ticket_id" value="{$ticket->id}">
            <div class="tickets-reply-attachments">
                <label for="reply_attachments" class="tickets-reply-attachments-label">Вложения</label>
                <input id="reply_attachments"
                       name="attachments[]"
                       class="tickets-reply-attachments-input"
                       type="file"
                       multiple
                       accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.rar"
                />
            </div>
            <div class="tickets-ticket-actions">
                <button type="submit" class="tickets-btn tickets-btn-primary tickets-btn-send">
                    Отправить
                </button>
            </div>
        </form>
    </div>
</div>
