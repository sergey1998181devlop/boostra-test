{if $sms_messages}
    <div class="table-responsive">
        <table class="table table-dark">
            <thead>
                <tr>
                    <th>id заявки</th>
                    <th>Телефон</th>
                    <th>Сообщение</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>id в smsc.ru</th>
                </tr>
            </thead>
            <tbody>
                {foreach $sms_messages as $sms_message}
                    <tr>
                        <td>{if $sms_message->order_id}{$sms_message->order_id}{/if}</td>
                        <td>{$sms_message->phone}</td>
                        <td>{$sms_message->message}</td>
                        <td>{$sms_message->created}</td>
                        <td>{$sms_message->send_status}</td>
                        <td>{$sms_message->send_id}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    {include file='html_blocks/pagination.tpl'}
{/if}
