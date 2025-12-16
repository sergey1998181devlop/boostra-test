<div class="cards">
    <div class="about">
        <a href="javascript:void(0);" class="js-toggle-cards toggle-cards underline">Мои
            реквизиты</a>
    </div>
    <div class="js-cards " style="display:none">
        <div class="split">
            <input type="hidden" class="card-user-id" value="{$user->id}">
            <ul id="card_list">
                {if (!empty($selected_bank))}
                    <div class="selected_bank_block">
                        <p class="selected_bank">
                            <span>Банк для зачисления денежных средств по СБП: {$selected_bank->title}</span>
                            {if !empty($order_for_choosing_card) && ($order_for_choosing_card['status'] == 3 || $order_for_choosing_card['1c_status'] == '6.Закрыт')}
                                <button  type="button" onclick="openChooseSbpBankModal(true)">изменить</button>
                            {/if}
                        </p>
                    </div>
                {/if}

                <p class="underline">Мои карты</p>
                {if !empty($cards)}
                    {foreach $cards as $card}
                        {if ((!$user->use_b2p && $card->rebill_id) || $user->use_b2p)}
                            <li data-card-id="{$card->id}">
                                <div>
                                    Номер карты: {$card->pan}

                                    {if (!empty($order_for_choosing_card) && $order_for_choosing_card['card_id'] !== $card->id &&
                                    $cards|@count > 1 && !empty($order_for_choosing_card['have_close_credits']) &&
                                    $order_for_choosing_card['status'] == 2) && $order_for_choosing_card['organization_id'] == $card->organization_id}
                                        <a
                                                href="#"
                                                class="button small modal-choose_card"
                                                data-button-card-id="{$card->id}"
                                                data-button-order-id="{$order_for_choosing_card['id']}"
                                                data-button-action="choose_card"
                                                style="margin: 10px 0 10px 10px; box-shadow: none; font-size: .9rem!important;"
                                        >Выбрать карту</a>
                                    {/if}

                                    {if (!$busy_cards[$card->id] && $cards|@count > 1)}
                                        <a
                                                href="#"
                                                class="button small modal-remove_card"
                                                data-button-card-id="{$card->id}"
                                                style="margin-left: 10px; box-shadow: none; font-size: .9rem!important;"
                                        >Удалить из ЛК</a>
                                    {else}

                                        {if $user->balance->zaim_number != 'Нет открытых договоров' && $card->id == $zaim_order->card_id}
                                            <p style="margin:0;font-size:15px;color:#21CA50  ">Используется для
                                                займа {$user->balance->zaim_number}</p>
                                        {else}
                                            {if (!empty($last_order['1c_id']) && ($last_order['status'] != 3 &&  $last_order['status'] != 4 && $last_order['status'] != 5 && $last_order['status'] != 11) && $card->id == $last_order['card_id'])}
                                                <p style="margin:0;font-size:15px;color:#21CA50  ">Используется для
                                                    заявки {$last_order['1c_id']}</p>
                                            {/if}
                                        {/if}
                                    {/if}
                                </div>
                                <span></span>
                            </li>
                        {else}
                            <li style="color:red">
                                <div>Номер карты: {$card->pan}
                                    <div style="font-size:1rem;">Ошибка привязки карты. Пожалуйста привяжите карту
                                        повторно.
                                    </div>
                                </div>
                            </li>
                        {/if}
                    {/foreach}
                {else}
                    <div>Нет доступных карт</div>
                {/if}

                {if $settings->b2p_enabled || $user->use_b2p}
                    <div id="not_checked_info_card_list" style="display:none">
                        <strong style="color:#f11">Вы должны согласиться с договором и нажать "Добавить карту"</strong>
                    </div>
                    <div class="docs_wrapper docs_wrapper_card_list">
                        <div class="conditions" style="max-width: none;">
                            <h3>Я согласен со следующим</h3>
                            <div style="max-width: none;">
                                <label class="spec_size">
                                    <div class="checkbox" style="border-width:1px;width:10px!important;height:10px!important;">
                                        <input id="recurrent_card_list" type="checkbox"
                                            name="recurring_consent_card_list" value="1" checked="checked"/>
                                        <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                                    </div>
                                </label>
                                <p>Я согласен с <a href="/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf" class="" target="_blank">cоглашением о применении регулярных (рекуррентных) платежах</a></p>
                            </div>
                            <div id="b2pay_card_list" style="max-width: none;">
                                <label class="spec_size">
                                    <div class="checkbox" style="border-width:1px;width:10px!important;height:10px!important;">
                                        <input class="js-need-verify-card-list js-agree-claim-value-card-list" type="checkbox"
                                            name="agree_claim_value_card_list" value="0" checked="checked"/>
                                        <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                                    </div>
                                </label>
                                <p>Я согласен с <a href="/files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf" class="" target="_blank">договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)</a></p>
                            </div>
                        </div>
                    </div>
                    <button id="myBtn" class="button medium" data-organization_id="{$organization_id}"
                            style="margin-top:5px;">Добавить карту
                    </button>
                {/if}

                <p class="underline">Мои счета</p>
                {if !empty($sbp_accounts)}
                    {foreach $sbp_accounts as $sbp_account}
                        <li>{$sbp_account->title} (СБП)

                            {if empty($selected_sbp_account_id) || $selected_sbp_account_id != $sbp_account->id}
                                <a
                                        href="#"
                                        class="button small modal-choose_card"
                                        data-button-card-id="{$sbp_account->id}"
                                        data-button-order-id="{$order_for_choosing_card['id']}"
                                        data-button-action="choose_sbp"
                                        style="margin: 10px 0 10px 10px; box-shadow: none; font-size: .9rem!important;"
                                >Выбрать счет СБП</a>
                            {/if}
                        </li>
                    {/foreach}
                {else}
                    <div>Нет доступных счетов</div>
                {/if}

                {if $settings->b2p_enabled || $user->use_b2p}
                <div id="not_checked_info_sbp_list" style="display:none">
                    <strong style="color:#f11">Вы должны согласиться с договором и нажать "Добавить СБП счет"</strong>
                </div>
                <div class="docs_wrapper docs_wrapper_sbp_list">
                    <div class="conditions" style="max-width: none;">
                        <h3>Я согласен со следующим</h3>
                        <div style="max-width: none;">
                            <label class="spec_size">
                                <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                                    <input id="recurrent_sbp_list" type="checkbox"
                                        name="recurring_consent_sbp_list" value="1" checked="checked"/>
                                    <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                                </div>
                            </label>
                            <p>Я согласен с <a href="/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf" class="" target="_blank">cоглашением о применении регулярных (рекуррентных) платежах</a></p>
                        </div>
                        <div id="b2pay_sbp_list" style="max-width: none;">
                            <label class="spec_size">
                                <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                                    <input class="js-need-verify-sbp-list js-agree-claim-value-sbp-list" type="checkbox"
                                        name="agree_claim_value_sbp_list" value="0" checked="checked"/>
                                    <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                                </div>
                            </label>
                            <p>Я согласен с <a href="/files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf" class="" target="_blank">договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)</a></p>
                        </div>
                    </div>
                </div>
                {/if}

                <button class="button medium attach_sbp_btn" href="#" data-organization_id="{$organization_id}"
                        style="margin-top:5px;">Добавить СБП счет
                </button>
            </ul>
        </div>
        <div id="removeCardModal" class="mfp-hide">
            <div class="removeCardModal-close">
                <p onclick="$.magnificPopup.close();">X</p>
            </div>
            <h2 class="text-center">Уверены, что хотите удалить карту?</h2>
            <div id='removeCardModal-buttons'>
                <button id="confirmRemove" class="action-remove_card">Да</button>
                <button id="cancelRemove" onclick="$.magnificPopup.close();">Нет</button>
            </div>
        </div>
        <div id="chooseCardModal" class="mfp-hide modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Замена карты</h5>
                </div>

                <div class="modal-body">
                    {if isset($user->order) && $user->order.status|intval === 2}
                        <p>Заменить карту (счет) в одобренной заявке? После привязки потребуется повторная
                            проверка.</p>
                    {else}
                        <p>Уверены, что хотите выбрать данную карту (счет) для получения займа?</p>
                    {/if}
                </div>

                <div class="modal-footer">
                    <button class="button button-inverse" onclick="$.magnificPopup.close()">Нет</button>

                    <button id="confirmChooseCard" class="action-choose_card">Да</button>
                </div>
            </div>
        </div>

    {if $autoapprove_card_reassign}
        <p style="color: #FF0000">Для получения одобренной заявки привяжите
            карту {$last_order_card->pan}</p>
    {/if}

    {$card_error}

    <p style="margin: 10px 0; font-size: 12px; color: #333;">
        Убедитесь, что привязанный номер телефона<br>
        <b>+{$user->phone_mobile|substr:0:1} ({$user->phone_mobile|substr:1:3}
            ) {$user->phone_mobile|substr:4:3}-{$user->phone_mobile|substr:7:2}
            -{$user->phone_mobile|substr:9:2}</b><br>
        совпадает с номером телефона СБП в выбранном банке!
    </p>

    <link href="design/{$settings->theme}/css/add_card.css?v=1" rel="stylesheet" type="text/css">
    {if Helpers::isFilesRequired($user)}
            <div id="myModal" class="add_card_photo_modal">
                <!-- Modal content -->
                <div class="modal-content add_photo">
                    <fieldset class="passport4-file file-block">

                        <legend>Фото карты</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                            {if $passport4_file}
                                <label class="file-label">
                                    <div class="file-label-image">
                                        <img src="{$passport4_file->name|resize:100:100}"/>
                                    </div>
                                    {*<span class="js-remove-file" data-id="{$passport4_file->id}">Удалить</span>*}
                                    <input type="hidden" id="passport4" name="user_files[]"
                                           value="{$passport4_file->id}"/>
                                </label>
                            {/if}
                        </div>
                    </fieldset>
                    <div>
                        <button class="button medium next-step-button" style="margin-top:5px;" disabled>Далее
                        </button>
                    </div>
                </div>

            </div>
        {/if}

    </div>
</div>