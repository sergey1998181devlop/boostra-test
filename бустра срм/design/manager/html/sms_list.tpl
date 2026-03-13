<div id="sms_list" class="tab-pane" role="tabpanel">
    <div class="row justify-content-end my-3">
        <div class="col-auto">
            <button id="blocked_adv_sms"
                    data-id="{$client->id}"
                    type="button"
                    class="btn {if $blocked_adv_sms}btn-warning{else}btn-danger{/if}">
                {if $blocked_adv_sms}Разблокировать{else}Заблокировать{/if} рекламные смс
            </button>
        </div>
    </div>
    <p> 
        <a class="text-warning" href="https://smsc.ru/api/http/status_messages/statuses/#menu" target="_blank">Информация о статусах сообщения</a>
    </p>
    <div class="row">
        <div class="col-12">
            {if $sms_messages}
                <div class="card">
                    <div class="card-body">
                        <table class="table table-stripped">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Сообщение</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $sms_messages as $sms_item}
                                    <tr>
                                        <td>{$sms_item->created}</td>
                                        <td>{$sms_item->message}</td>
                                        <td>{$sms_item->api_status[0]}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            {else}
                <h3>СМС не найдены</h3>
            {/if}
        </div>
    </div>
</div>
