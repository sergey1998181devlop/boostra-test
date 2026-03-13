{function name='render_credit_card_list'}
    {if $card_list && $card_list|@count > 0}
    <select id="{$id_select}" class="form-control">
        <option disabled>--- Активные карты ---</option>
        {foreach $card_list as $card}
            {if $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->id == $order->card_id}selected{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
        <option disabled>--- Удаленные карты ---</option>
        {* Deleted cards *}
        {foreach $card_list as $card}
            {if ! $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->deleted}disabled{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
    </select>
    {/if}
{/function}

{*<div id="insures" class="tab-pane" role="tabpanel">*}
    <div class="row">
        <div class="col-12">
            <div class="tab-content br-n pn">
                <div id="navpills-orders" class="tab-pane active">
                    <div class="card">
                        <div class="card-body">
                            {if $credit_doctor_items}
                                <table class="table">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Дней с момента покупки</th>
                                        <th class="">Тип продукта</th>
                                        <th class="">Орг-я</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Сумма остаток</th>
                                        <th class="text-right">Статус</th>
                                    </tr>
                                    {foreach $credit_doctor_items as $credit_doctor_item}
                                        <tr>
                                            <td>{$credit_doctor_item->date_added|date}</td>
                                            <td>
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span>{$credit_doctor_item->days_since_purchase}</span>
                                                    {if !$credit_doctor_item->fully_returned}
                                                        <button class="btn btn-info btn-modal-send-sms"
                                                                data-type="doctor-sms-inform"
                                                                data-policy="{$credit_doctor_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Inform
                                                        </button>
                                                        <button class="btn btn-primary btn-modal-send-sms"
                                                                data-type="doctor-sms-key"
                                                                data-policy="{$credit_doctor_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Key
                                                        </button>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                {if $credit_doctor_item->is_penalty}
                                                    Кредитный доктор
                                                {else}
                                                    Финансовый доктор
                                                {/if}
                                            </td>
                                            <td>{$organizations[$credit_doctor_item->organization_id]->short_name|escape}</td>
                                            <td class="text-center">
                                                {$credit_doctor_item->amount} руб
                                            </td>
                                            <td class="text-center">
                                                {$credit_doctor_item->amount_left} руб
                                            </td>
                                            <td class="text-right">
                                                {foreach $credit_doctor_item->receipts as $item}
                                                    <div>
                                                        <strong class="text-danger">
                                                            Возвращено
                                                            {if $item->type == 'RECOMPENSE_CREDIT_DOCTOR'}(взаимозачет)
                                                            {elseif $item->type == 'REFUND_CREDIT_DOCTOR_REQUISITES'}(по реквизитам)
                                                            {else}(на карту){/if}
                                                            :
                                                        </strong>
                                                        <br />
                                                        {if !empty($item->receipt_url)}
                                                            <a class="ml-3 btn btn-primary px-5" href="https://receipts.ru/Home/Download/{$item->receipt_id}" target="_blank" download="return_receipt.pdf">Чек</a>
                                                        {/if}
                                                        <strong>{$item->amount} руб</strong>
                                                        <br />
                                                        <i><small>{$item->date_added|date} {$item->date_added|time}</small></i>
                                                        <br>
                                                    </div>
                                                {/foreach}

                                                {include file='html_blocks/return_status_controls.tpl'
                                                service_type='credit_doctor'
                                                service_id=$credit_doctor_item->id
                                                request=$credit_doctor_item->return_request}

                                                {if !$credit_doctor_item->fully_returned}


                                                    {if floor((time() - strtotime($credit_doctor_item->date_added)) / 60) >= 15}
                                                        <div class="btn-group">
                                                            <button class="btn btn-warning btn-small js-open-return-by-requisites mr-1"
                                                                    data-service-type="credit_doctor"
                                                                    data-service-id="{$credit_doctor_item->id}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-amount="{$credit_doctor_item->amount}"
                                                                    data-return_amount="{$credit_doctor_item->return_amount}"
                                                                    data-amount-left="{$credit_doctor_item->amount_left}"
                                                                    data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                                                    data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                                                    type="button">
                                                                <i class="fa fa-university"></i> Вернуть по реквизитам
                                                            </button>
                                                            <button class="btn btn-danger btn-small js-open-return-dop"
                                                                    data-service-type="credit_doctor"
                                                                    data-service-id="{$credit_doctor_item->id}"
                                                                    data-amount="{$credit_doctor_item->amount}"
                                                                    data-return_amount="{$credit_doctor_item->return_amount}"
                                                                    data-service-date="{$credit_doctor_item->date_added|date}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-manager-id="{$manager->id}"
                                                                    data-user-id="{$user->id}"

                                                                    type="button">Вернуть</button>
                                                            {include file='html_blocks/card_list_select.tpl' id_select='select-return-dop' organization_id=$credit_doctor_item->organization_id}
                                                        </div>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}

                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                            {if $star_oracle_items}
                                <table class="table">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Дней с момента покупки</th>
                                        <th class="">Тип продукта</th>
                                        <th class="">Орг-я</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Сумма остаток</th>
                                        <th class="text-right">Статус</th>
                                    </tr>
                                    {foreach $star_oracle_items as $star_oracle_item}
                                        <tr>
                                            <td>{$star_oracle_item->date_added|date}</td>
                                            <td>
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span>{$star_oracle_item->days_since_purchase}</span>
                                                    {if !$star_oracle_item->fully_returned}
                                                        <button class="btn btn-info btn-modal-send-sms"
                                                                data-type="oracle-sms-inform"
                                                                data-policy="{$star_oracle_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Inform
                                                        </button>
                                                        <button class="btn btn-primary btn-modal-send-sms"
                                                                data-type="oracle-sms-key"
                                                                data-policy="{$star_oracle_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Key
                                                        </button>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                Звёздный Оракул
                                            </td>
                                            <td>{$organizations[$star_oracle_item->organization_id]->short_name|escape}</td>
                                            <td class="text-center">
                                                {$star_oracle_item->amount} руб
                                            </td>
                                            <td class="text-center">
                                                {$star_oracle_item->amount_left} руб
                                            </td>
                                            <td class="text-right">
                                                {foreach $star_oracle_item->receipts as $item}
                                                    <div>
                                                        <strong class="text-danger">
                                                            Возвращено
                                                            {if $item->type == 'RECOMPENSE_STAR_ORACLE'}(взаимозачет)
                                                            {elseif $item->type == 'REFUND_STAR_ORACLE_REQUISITES'}(по реквизитам)
                                                            {else}(на карту){/if}
                                                            :
                                                        </strong>
                                                        <br />
                                                        {if !empty($item->receipt_url)}
                                                            <a class="ml-3 btn btn-primary px-5" href="https://receipts.ru/Home/Download/{$item->receipt_id}" target="_blank" download="return_receipt.pdf">Чек</a>
                                                        {/if}
                                                        <strong>{$item->amount} руб</strong>
                                                        <br />
                                                        <i><small>{$item->date_added|date} {$item->date_added|time}</small></i>
                                                        <br>
                                                    </div>
                                                {/foreach}

                                                {include file='html_blocks/return_status_controls.tpl'
                                                service_type='star_oracle'
                                                service_id=$star_oracle_item->id
                                                request=$star_oracle_item->return_request}

                                                {if !$star_oracle_item->fully_returned}


                                                    {if floor((time() - strtotime($star_oracle_item->date_added)) / 60) >= 15}
                                                        <div class="btn-group">
                                                            <button class="btn btn-warning btn-small js-open-return-by-requisites mr-1"
                                                                    data-service-type="star_oracle"
                                                                    data-service-id="{$star_oracle_item->id}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-amount="{$star_oracle_item->amount}"
                                                                    data-return_amount="{$star_oracle_item->return_amount}"
                                                                    data-amount-left="{$star_oracle_item->amount_left}"
                                                                    data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                                                    data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                                                    type="button">
                                                                <i class="fa fa-university"></i> Вернуть по реквизитам
                                                            </button>
                                                            <button class="btn btn-danger btn-small js-open-return-dop" data-service-type="star_oracle" data-service-id="{$star_oracle_item->id}" data-amount="{$star_oracle_item->amount}" data-return_amount="{$star_oracle_item->return_amount}" data-service-date="{$star_oracle_item->date_added|date}" data-order-id="{$order->order_id}" type="button">Вернуть</button>
                                                            {include file='html_blocks/card_list_select.tpl' id_select='select-return-dop' organization_id=$star_oracle_item->organization_id}
                                                        </div>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}

                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                            {if $multipolis_items}
                                <table class="table">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Дней с момента покупки</th>
                                        <th class="">Тип продукта</th>
                                        <th class="">Орг-я</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Сумма остаток</th>
                                        <th class="text-right">Статус</th>
                                    </tr>
                                    {foreach $multipolis_items as $multipolis_item}
                                        <tr>
                                            <td>{$multipolis_item->date_added|date}</td>
                                            <td>
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span>{$multipolis_item->days_since_purchase}</span>
                                                    {if !$multipolis_item->fully_returned}
                                                        <button class="btn btn-info btn-modal-send-sms"
                                                                data-type="concierge-sms-inform"
                                                                data-policy="{$multipolis_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Inform
                                                        </button>
                                                        <button class="btn btn-primary btn-modal-send-sms"
                                                                data-type="concierge-sms-key"
                                                                data-policy="{$multipolis_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Key
                                                        </button>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>Консьерж сервис {$multipolis_item->number}</td>
                                            <td>{$organizations[$multipolis_item->organization_id]->short_name|escape}</td>
                                            <td class="text-center">{$multipolis_item->amount} руб</td>
                                            <td class="text-center">{$multipolis_item->amount_left} руб</td>
                                            <td class="text-right">
                                                {foreach $multipolis_item->receipts as $item}
                                                    <div>
                                                        <strong class="text-danger">
                                                            Возвращено
                                                            {if $item->type == 'RECOMPENSE_MULTIPOLIS'}(взаимозачет)
                                                            {elseif $item->type == 'REFUND_MULTIPOLIS_REQUISITES'}(по реквизитам)
                                                            {else}(на карту){/if}
                                                            :
                                                        </strong>
                                                        <br />
                                                        {if !empty($item->receipt_url)}
                                                            <a class="ml-3 btn btn-primary px-5" href="https://receipts.ru/Home/Download/{$item->receipt_id}" target="_blank" download="return_receipt.pdf">Чек</a>
                                                        {/if}
                                                        <strong>{$item->amount} руб</strong>
                                                        <br />
                                                        <i><small>{$item->date_added|date} {$item->date_added|time}</small></i>
                                                        <br>
                                                    </div>
                                                {/foreach}

                                                {include file='html_blocks/return_status_controls.tpl'
                                                service_type='multipolis'
                                                service_id=$multipolis_item->id
                                                request=$multipolis_item->return_request}

                                                {if !$multipolis_item->fully_returned}
                                                    {if floor((time() - strtotime($multipolis_item->date_added)) / 60) >= 15}
                                                        <div class="btn-group">
                                                            <button class="btn btn-warning btn-small js-open-return-by-requisites mr-1"
                                                                    data-service-type="multipolis"
                                                                    data-service-id="{$multipolis_item->id}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-amount="{$multipolis_item->amount}"
                                                                    data-return_amount="{$multipolis_item->return_amount}"
                                                                    data-amount-left="{$multipolis_item->amount_left}"
                                                                    data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                                                    data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                                                    type="button">
                                                                <i class="fa fa-university"></i> Вернуть по реквизитам
                                                            </button>
                                                            <button class="btn btn-danger btn-small js-open-return-dop" data-service-type="multipolis" data-service-id="{$multipolis_item->id}" data-amount="{$multipolis_item->amount}" data-return_amount="{$multipolis_item->return_amount}" data-service-date="{$multipolis_item->date_added|date}" data-order-id="{$order->order_id}" type="button">Вернуть</button>
                                                            {include file='html_blocks/card_list_select.tpl' id_select='select-return-dop' organization_id=$multipolis_item->organization_id}
                                                        </div>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                            {if $tv_medical_items}
                                <table class="table">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Дней с момента покупки</th>
                                        <th>Тип продукта</th>
                                        <th>Орг-я</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Сумма остаток</th>
                                        <th class="text-right">Статус</th>
                                    </tr>
                                    {foreach $tv_medical_items as $tv_medical_item}
                                        <tr>
                                            <td>{$tv_medical_item->date_added|date}</td>
                                            <td>{$tv_medical_item->days_since_purchase}
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span>{$tv_medical_item->days_since_purchase}</span>
                                                    {if !$tv_medical_item->fully_returned}
                                                        <button class="btn btn-info btn-modal-send-sms"
                                                                data-type="vita-sms-inform"
                                                                data-policy="{$tv_medical_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Inform
                                                        </button>
                                                        <button class="btn btn-primary btn-modal-send-sms"
                                                                data-type="vita-sms-key"
                                                                data-policy="{$tv_medical_item->policy_id}"
                                                                data-order="{$order->order_id}"
                                                                data-manager="{$manager->id}"
                                                                data-target="#sms-modal"
                                                                data-toggle="modal">
                                                            Key
                                                        </button>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>Вита-мед тариф &laquo;{$tv_medical_item->name}&raquo;</td>
                                            <td>{$organizations[$tv_medical_item->organization_id]->short_name|escape}</td>
                                            <td class="text-center">{$tv_medical_item->amount} руб</td>
                                            <td class="text-center">{$tv_medical_item->amount_left} руб</td>
                                            <td class="text-right">
                                                {foreach $tv_medical_item->receipts as $item}
                                                    <div>
                                                        <strong class="text-danger">
                                                            Возвращено
                                                            {if $item->type == 'RECOMPENSE_TV_MEDICAL'}(взаимозачет)
                                                            {elseif $item->type == 'REFUND_TV_MEDICAL_REQUISITES'}(по реквизитам)
                                                            {else}(на карту){/if}
                                                            :
                                                        </strong>
                                                        <br />
                                                        {if !empty($item->receipt_url)}
                                                            <a class="ml-3 btn btn-primary px-5" href="https://receipts.ru/Home/Download/{$item->receipt_id}" target="_blank" download="return_receipt.pdf">Чек</a>
                                                        {/if}
                                                        <strong>{$item->amount} руб</strong>
                                                        <br />
                                                        <i><small>{$item->date_added|date} {$item->date_added|time}</small></i>
                                                        <br>
                                                    </div>
                                                {/foreach}

                                                {include file='html_blocks/return_status_controls.tpl'
                                                service_type='tv_medical'
                                                service_id=$tv_medical_item->payment_id
                                                request=$tv_medical_item->return_request}

                                                {if !$tv_medical_item->fully_returned}
                                                    {if floor((time() - strtotime($tv_medical_item->date_added)) / 60) >= 15}
                                                        <div class="btn-group">
                                                            <button class="btn btn-warning btn-small js-open-return-by-requisites mr-1"
                                                                    data-service-type="tv_medical"
                                                                    data-service-id="{$tv_medical_item->payment_id}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-amount="{$tv_medical_item->amount}"
                                                                    data-return_amount="{$tv_medical_item->return_amount}"
                                                                    data-amount-left="{$tv_medical_item->amount_left}"
                                                                    data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                                                    data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                                                    type="button">
                                                                <i class="fa fa-university"></i> Вернуть по реквизитам
                                                            </button>
                                                            <button class="btn btn-danger btn-small js-open-return-dop" data-service-type="tv_medical" data-service-id="{$tv_medical_item->payment_id}" data-amount="{$tv_medical_item->amount}" data-return_amount="{$tv_medical_item->return_amount}" data-service-date="{$tv_medical_item->date_added|date}" data-order-id="{$order->order_id}" type="button">Вернуть</button>
                                                            {include file='html_blocks/card_list_select.tpl' id_select='select-return-dop' organization_id=$tv_medical_item->organization_id}
                                                        </div>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                            {if $safe_deal_items}
                                <table class="table">
                                    <tr>
                                        <th>Дата</th>
                                        <th>Дней с момента покупки</th>
                                        <th class="">Тип продукта</th>
                                        <th class="">Орг-я</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Сумма остаток</th>
                                        <th class="text-right">Статус</th>
                                    </tr>
                                    {foreach $safe_deal_items as $safe_deal_item}
                                        <tr>
                                            <td>{$safe_deal_item->date_added|date}</td>
                                            <td>
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span>{$safe_deal_item->days_since_purchase}</span>
                                                </div>
                                            </td>
                                            <td>
                                                Безопасная сделка
                                            </td>
                                            <td>{$organizations[$safe_deal_item->organization_id]->short_name|escape}</td>
                                            <td class="text-center">
                                                {$safe_deal_item->amount} руб
                                            </td>
                                            <td class="text-center">
                                                {$safe_deal_item->amount_left} руб
                                            </td>
                                            <td class="text-right">
                                                {foreach $safe_deal_item->receipts as $item}
                                                    <div>
                                                        <strong class="text-danger">
                                                            Возвращено
                                                            {if $item->type == 'RECOMPENSE_SAFE_DEAL'}(взаимозачет)
                                                            {elseif $item->type == 'REFUND_SAFE_DEAL_REQUISITES'}(по реквизитам)
                                                            {else}(на карту){/if}
                                                            :
                                                        </strong>
                                                        <br />
                                                        {if !empty($item->receipt_url)}
                                                            <a class="ml-3 btn btn-primary px-5" href="https://receipts.ru/Home/Download/{$item->receipt_id}" target="_blank" download="return_receipt.pdf">Чек</a>
                                                        {/if}
                                                        <strong>{$item->amount} руб</strong>
                                                        <br />
                                                        <i><small>{$item->date_added|date} {$item->date_added|time}</small></i>
                                                        <br>
                                                    </div>
                                                {/foreach}

                                                {include file='html_blocks/return_status_controls.tpl'
                                                service_type='safe_deal'
                                                service_id=$safe_deal_item->id
                                                request=$safe_deal_item->return_request}

                                                {if !$safe_deal_item->fully_returned}
                                                    {if floor((time() - strtotime($safe_deal_item->date_added)) / 60) >= 15}
                                                        <div class="btn-group">
                                                            <button class="btn btn-warning btn-small js-open-return-by-requisites mr-1"
                                                                    data-service-type="safe_deal"
                                                                    data-service-id="{$safe_deal_item->id}"
                                                                    data-order-id="{$order->order_id}"
                                                                    data-amount="{$safe_deal_item->amount}"
                                                                    data-return_amount="{$safe_deal_item->return_amount}"
                                                                    data-amount-left="{$safe_deal_item->amount_left}"
                                                                    data-client-fio="{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}"
                                                                    data-client-birthdate="{$order->birth|date:'d.m.Y'}"
                                                                    type="button">
                                                                <i class="fa fa-university"></i> Вернуть по реквизитам
                                                            </button>
                                                            <button class="btn btn-danger btn-small js-open-return-dop" data-service-type="safe_deal" data-service-id="{$safe_deal_item->id}" data-amount="{$safe_deal_item->amount}" data-return_amount="{$safe_deal_item->return_amount}" data-service-date="{$safe_deal_item->date_added|date}" data-order-id="{$order->order_id}" type="button">Вернуть</button>
                                                            {render_credit_card_list id_select='select-return-dop' organization_id={$safe_deal_item->organization_id}}
                                                        </div>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}


                            {if $insures}
                                <table class="table">
                                    <tr>
                                        <th>Номер полиса</th>
                                        <th>Дата</th>
                                        <th class="text-center">Сумма</th>
                                        <th class="text-center">Статус</th>
                                    </tr>
                                    {foreach $insures as $insure}
                                        <tr>
                                            <td>
                                                {$insure->insurance->number}
                                            </td>
                                            <td>{$insure->date|date}</td>
                                            <td class="text-center">{$insure->amount}</td>
                                            <td class="text-right">
                                                {if $insure->return_status}
                                                    <strong class="text-danger">Возвращена</strong>
                                                    <br />
                                                    <i><small>{$insure->return_date|date} {$insure->return_date|time}</small></i>
                                                {else}
                                                    {if floor((time() - strtotime($insure->date)) / 60) >= 15}
                                                        <button class="btn btn-danger btn-small js-return-insure" data-insure="{$insure->id}" type="button">Вернуть</button>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}

                                    {foreach $insurances as $insurance}
                                        <tr>
                                            <td>
                                                {$insurance->number}
                                            </td>
                                            <td>{$insurance->create_date|date}</td>
                                            <td class="text-center">{$insurance->amount}</td>
                                            <td class="text-right">
                                                {if $insurance->return_status}
                                                    <strong class="text-danger">Возвращена</strong>
                                                    <br />
                                                    <i><small>{$insurance->return_date|date} {$insurance->return_date|time}</small></i>
                                                {else}
                                                    {if floor((time() - strtotime($insurance->create_date)) / 60) >= 15}
                                                        <button class="btn btn-danger btn-small js-open-return-insurance" data-insurance="{$insurance->id}" data-number="{$insurance->number}" data-amount="{$insurance->amount}" type="button">Вернуть</button>
                                                    {else}
                                                        Возврат ещё недоступен
                                                    {/if}
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                </table>
                            {/if}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{*</div>*}