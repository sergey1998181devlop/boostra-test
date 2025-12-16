<div class="tickets-message
         {if $comment->sender_type == 'user'}tickets-message-user{else}tickets-message-support{/if}">
    <div class="tickets-message-header">
        <strong>
            {if $comment->sender_type == 'user'}
                {$comment->user_lastname} {$comment->user_firstname}
            {else}
                Поддержка
            {/if}
        </strong>
        <span>{$comment->created_at|date_format:"%d.%m.%Y %H:%M"}</span>
    </div>
    <div class="tickets-message-content">
        <p>{$comment->message nofilter}</p>
        {if !empty($comment->attachments)}
            <div class="tickets-message-attachments">
                <span>Вложения:</span>
                <ul class="tickets-attachments-list">
                {foreach from=$comment->attachments item=attachment}
                    {assign var=ext value=$attachment.name|lower|regex_replace:'/^.*\./':''}
                    <li class="tickets-attachment-item">
                        <a href="{$attachment.url}" target="_blank" rel="noopener noreferrer">
                            {if in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])}
                                <span class="attachment-icon attachment-icon-image"></span>
                            {elseif $ext == 'pdf'}
                                <span class="attachment-icon attachment-icon-pdf"></span>
                            {elseif in_array($ext, ['zip','rar','7z'])}
                                <span class="attachment-icon attachment-icon-archive"></span>
                            {elseif in_array($ext, ['doc','docx'])}
                                <span class="attachment-icon attachment-icon-doc"></span>
                            {elseif in_array($ext, ['xls','xlsx'])}
                                <span class="attachment-icon attachment-icon-xls"></span>
                            {else}
                                <span class="attachment-icon attachment-icon-file"></span>
                            {/if}
                            {$attachment.name}
                        </a>
                    </li>
                {/foreach}
                </ul>
            </div>
        {/if}
    </div>
</div>