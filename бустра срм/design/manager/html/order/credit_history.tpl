<div class="row">
    <div class="col-12">
        <ul class="nav nav-pills mt-4 mb-4">
            <li class=" nav-item"> <a href="#navpills-orders" class="nav-link active" data-toggle="tab" aria-expanded="false">Заявки</a> </li>
            {if !in_array($manager->role, ['verificator_minus'])}
                <li class="nav-item"> <a href="#navpills-loans" class="nav-link" data-toggle="tab" aria-expanded="false">Кредиты</a> </li>
            {/if}
        </ul>
        <div class="tab-content br-n pn">
            <div id="navpills-orders" class="tab-pane active">
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Номер</th>
                                <th>Номер 1С</th>
                                <th>Дата</th>
                                <th class="text-center">Сумма</th>
                                <th class="text-center">Период</th>
                                <th class="text-right">Статус 1С</th>
                            </tr>
                            {foreach $user_orders as $user_order}
                                <tr>
                                    <td>
                                        <a href="order/{$user_order->order_id}" target="_blank">{$user_order->order_id}</a>
                                    </td>
                                    <td>
                                        {$user_order->id_1c}
                                    </td>
                                    <td>{$user_order->date|date} {$user_order->date|time}</td>
                                    <td class="text-center">{$user_order->amount}</td>
                                    <td class="text-center">{$user_order->period}</td>
                                    <td class="text-right">{$user_order->status_1c}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            </div>
            {if !in_array($manager->role, ['verificator_minus'])}
                <div id="navpills-loans" class="tab-pane">
                    <div class="card">
                        <div class="card-body">

                            {if $order->user->loan_history}
                                <table class="table">
                                    <tr>
                                        <th>Договор</th>
                                        <th>Дата</th>
                                        <th>Просрочка</th>
                                        <th>Ответственный</th>
                                        <th class="text-right">Статус</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Остаток ОД</th>
                                        <th class="text-right">Остаток процентов</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    {foreach $order->user->loan_history as $loan_history_item}
                                        <tr>
                                            <td>
                                                {$loan_history_item->number}
                                            </td>
                                            <td>
                                                {$loan_history_item->date|date}
                                            </td>
                                            <td>{$loan_history_item->days_overdue}</td>
                                            <td>{$loan_history_item->responsible_collector}</td>
                                            <td class="text-right">
                                                {if $loan_history_item->loan_percents_summ > 0 || $loan_history_item->loan_body_summ > 0}
                                                    <span class="label label-success">Активный</span>
                                                {else}
                                                    <span class="label label-danger">Закрыт</span>
                                                {/if}
                                            </td>
                                            <td class="text-center">{$loan_history_item->amount}</td>
                                            <td class="text-center">{$loan_history_item->loan_body_summ}</td>
                                            <td class="text-right">{$loan_history_item->loan_percents_summ}</td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-info js-get-movements" data-number="{$loan_history_item->number}">Операции</button>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {else}
                                <h4>Нет кредитов</h4>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>