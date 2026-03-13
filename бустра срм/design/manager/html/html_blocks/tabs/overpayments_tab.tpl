{* Вкладка "Переплаты" *}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Возврат переплаты по договору</h5>

                {if $overpayment_returns}
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Дата создания</th>
                                    <th>Сумма</th>
                                    <th>Реквизиты</th>
                                    <th>Статус</th>
                                    <th>Менеджер</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $overpayment_returns as $return}
                                    <tr>
                                        <td>{$return->created|date_format:'%d.%m.%Y %H:%M'}</td>
                                        <td>{$return->amount} руб</td>
                                        <td>
                                            <small>
                                                Номер счета: {$return->account_number}
                                                {if $return->bank_name}
                                                    <br />{$return->bank_name}
                                                {/if}
                                                {if $return->bik}
                                                    <br />БИК: {$return->bik}
                                                {/if}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-{$return->status_badge}">{$return->status_text}</span>
                                            {if $return->error_text}
                                                <br />
                                                <small class="text-danger">{$return->error_text}</small>
                                            {/if}
                                        </td>
                                        <td>{$return->manager_name}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info mb-3" role="alert">
                        <i class="fa fa-info-circle"></i> Заявок на возврат переплаты пока нет
                    </div>
                {/if}

                <div id="overpayment-check-section">
                    <button class="btn btn-primary js-check-overpayment mb-3"
                            data-order-id="{$order->order_id}"
                            type="button">
                        <i class="fa fa-search"></i> Проверить переплату
                    </button>

                    <div id="overpayment-result" style="display: none;" class="mb-3">
                        <div class="alert alert-success" role="alert">
                            <strong>Сумма переплаты: <span id="overpayment-amount">0</span> руб.</strong>
                        </div>
                        <button class="btn btn-warning js-open-return-by-requisites"
                                data-service-type="overpayment"
                                data-service-id="{$order->order_id}"
                                data-order-id="{$order->order_id}"
                                data-amount=""
                                data-amount-left=""
                                data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                type="button">
                            <i class="fa fa-university"></i> Вернуть переплату по реквизитам
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

