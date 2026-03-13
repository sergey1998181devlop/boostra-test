{*
    Шаблон для отображения комментариев в блоке на вкладке заявки.
    Используется для AJAX-загрузки комментариев.
    Переменные:
    - $block_comments - массив комментариев для данного блока
    - $managers - массив менеджеров
*}
{if $block_comments}
    {foreach $block_comments as $comment}
        <div class="col-md-12 mb-2">
            <div class="bg-primary pt-1 pb-1 pl-4 pr-4 rounded" style="display:inline-block">
                <div>
                    <strong>{$managers[$comment->manager_id]->name|escape}</strong>
                    <small><i>{$comment->created|date} {$comment->created|time}</i></small>
                </div>
                <div>{$comment->text|make_urls_clickable|nl2br}</div>
            </div>
        </div>
    {/foreach}
{/if}
