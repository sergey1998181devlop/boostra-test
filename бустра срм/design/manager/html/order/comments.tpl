<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">


                            {if !$commentsData && !$comments_1c && !$blacklist_comments}
                                <h4>Нет комментариев</h4>
                            {/if}

                            {if $commentsData}
                                <h4 class="card-header">
                                    Комментарии CRM
                                </h4>
                                <table class="table table-striped">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Заявка</th>
                                        <th>Менеджер</th>
                                        <th>Блок</th>
                                        <th>Комментарий</th>
                                    </tr>
                                    {foreach $commentsData as $comment}
                                        <tr>
                                            <td>
                                                {$comment->created|date}
                                                <br />
                                                {$comment->created|time}
                                            </td>
                                            <td>
                                                <a href="order/{$comment->order_id}">{$comment->order_id}</a>
                                            </td>
                                            <td>{$managers[$comment->manager_id]->name|escape}</td>
                                            <td>
                                                {if $comment_blocks[$comment->block]}
                                                    {$comment_blocks[$comment->block]}
                                                {else}
                                                    {$comment->block}
                                                {/if}
                                            </td>
                                            <td><small>{$comment->text|make_urls_clickable|nl2br}</small></td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                        </div>
                        <div class="col-md-6">
                            <div class="js-load-comments-block load-comments-block"  data-user="{$order->user_id}">
                                <h4 class="card-header">
                                    Комментарии из 1С
                                    <a href="javascript:void(0);" class="btn btn-xs btn-outline-info float-right js-refresh-comments" title="обновить">
                                        <i class="fas fa-sync-alt"></i> Обновить
                                    </a>
                                </h4>
                                <div class="js-load-comments-inner">
                                    {if $comments_1c || $blacklist_comments}
                                        <table class="table">
                                            <tr>
                                                <th>Дата</th>
                                                <th>Блок</th>
                                                <th>Комментарий</th>
                                            </tr>

                                            {foreach $blacklist_comments as $comment}
                                                <tr class="text-danger">
                                                    <td>{$comment->created|date} {$comment->created|time}</td>
                                                    <td>{$comment->block|escape}</td>
                                                    <td>{$comment->text|nl2br}</td>
                                                </tr>
                                            {/foreach}

                                            {foreach $comments_1c as $comment}
                                                <tr>
                                                    <td>{$comment->created|date} {$comment->created|time}</td>
                                                    <td>{$comment->block|escape}</td>
                                                    <td>{$comment->text|make_urls_clickable|nl2br}</td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    {else}
                                        <h4>Нет комментариев</h4>
                                    {/if}
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>