<div class="panel">

    <h1 style="margin-bottom:1rem">{$salute|escape}</h1>

    {include 'credit_rating/credit_rating.tpl'}

    {if $indulgensia}
        <h3 class="green" style="line-height:1.2;font-weight:normal;margin-bottom:1rem">
            Прощаем вам все начисления и проценты по займу, верните ровно столько, сколько брали!
            <br />
            Акция действительна только 3 дня с 17 по 19 ноября включительно.
            <br />
            Оплатите через кнопку "Оплатить другую сумму", позвоните на <a href="tel:88003333073">88003333073</a>

            и мы закроем ваш долг!
        </h3>
    {/if}

    {if $user->id != 56219 && !$user->file_uploaded && (!$user->balance->zaim_number || $user->balance->zaim_number=='Нет открытых договоров')}
        <div class="files">
            <p>
                Прикрепите фотографии с лицом и паспортом для подтверждения
            </p>
            <a href="user/upload" class="button medium"> Добавить</a>
        </div>
    {/if}

    {*if $notsend_files}
    <div class="files">
        <p>У Вас есть загруженные файлы, которые не отправлены на проверку.<br />Они будут удалены спустя 5 дней после загрузки.</p>
        <a href="user/upload" class="button medium"> Перейти к файлам</a>
    </div>
    {/if*}

    {*if $reject_files}
    <div class="files">
        <p>У Вас есть загруженные файлы, которые не прошли проверку и их необходимо перезалить.</p>
        <a href="user/upload" class="button medium"> Заменить файлы</a>
    </div>
    {/if*}

    {if $user->id == 56219}
        <div class="about">
            <div>Договор продан</div>
        </div>
        <div class="collector_agency">
            <p>Коллекторское агентство «Правовая защита»</p>
            <p><a style="float:none;" href="//pravza.com">www.pravza.com</a></p>
            <p><a style="float:none;" href="tel:88003334043">88003334043</a></p>
        </div>
    {elseif $user->balance->zaim_number && $user->balance->zaim_number!='Ошибка' && $user->balance->zaim_number!='Нет открытых договоров'}

        {if $user->balance->sale_info=='Договор продан'}
            <div class="about">
                <div>Договор продан</div>
            </div>
            <div class="collector_agency">
                {if $user->balance->buyer=='Легал Коллекшн ООО'}
                    <p>ООО "Легал Коллекшн"</p>
                    <p><a style="float:none;" href="https://legalc.ru/">legalc.ru</a></p>
                    <p><a style="float:none;" href="tel:88007758461">8 800 775 84 61</a></p>
                {elseif $user->balance->buyer=='Правовая защита'}
                    <p>Коллекторское агентство «Правовая защита»</p>
                    <p><a style="float:none;" href="//pravza.com">www.pravza.com</a></p>
                    <p><a style="float:none;" href="tel:88003334043">88003334043</a></p>
                {elseif $user->balance->buyer=='БЮРО ВЗЫСКАНИЯ ПРАВЁЖ ООО'}
                    <p>КОЛЛЕКТОРСКОЕ АГЕНТСТВО "БЮРО ВЗЫСКАНИЯ "ПРАВЁЖ"</p>
                    <p><a style="float:none;" href="https://bv-pravezh.ru">bv-pravezh.ru</a></p>
                    <p><a style="float:none;" href="tel:84959681331">84959681331</a></p>
                {elseif $user->balance->buyer=='ООО "ЮРИДИЧЕСКАЯ ФИНЗАЩИТА"'}
                    <p>ООО "Юридическая Финзащита" </p>
                    <p><a style="float:none;" href="http://yuridfinans.ru">yuridfinans.ru </a></p>
                    <p><a style="float:none;" href="tel:79068191556">7 (906) 819-15-56</a></p>
                    <p><a style="float:none;" href="mailto:urid-finans@mail.ru">urid-finans@mail.ru</a></p>
                {elseif $user->balance->buyer}
                    <p>{$user->balance->buyer}</p>
                {/if}
            </div>


        {elseif $user->balance->zaim_number=='Ошибка. Обратитесь в офис'}
            <div class="about">
                <div>{$user->balance->zaim_number}</div>
            </div>

        {else}

            <div class="about">
                <div>Номер займа  <ins>{$user->balance->zaim_number}</ins></div>
                <a class="button small button-inverse {*view-contract*} " target="_blank" href="user/docs" data-number="{$user->balance->zaim_number}">Смотреть договор</a>
            </div>
        {/if}

        {if $user->balance->sale_info!='Договор продан' && $user->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
            <div class="split">
                <ul>
                    <li>
                        <div>Остаток Основного долга</div>
                        <div>{$user->balance->ostatok_od}</div>
                    </li>
                    <li>
                        <div>Остаток Процентов</div>
                        <div>{$user->balance->ostatok_percents}</div>
                    </li>
                    {if $user->balance->ostatok_peni}
                        <li>
                            <div>Остаток Пени</div>
                            <div>{$user->balance->ostatok_peni}</div>
                        </li>
                    {/if}
                    <li>
                        <div>Итого на сегодня</div>
                        <div>{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}</div>
                    </li>
                    <li>
                        <div>Дата планового платежа</div>
                        <div>{$user->balance->payment_date|date}</div>
                    </li>
                    {if $user->balance->last_prolongation == 1}
                        <li>
                            <div>Итого на {$user->balance->payment_date|date}</div>
                            <div>{$user->balance->prolongation_amount}</div>
                        </li>
                    {/if}
                </ul>
            </div>
        
            {if $user->balance->sale_info!='Договор продан' && $user->balance->zaim_number!='Нет открытых договоров' && $user->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
                {if $loan_expired}
                    <div>
                        <p class="text-red">Ваш заём просрочен</p>
                    </div>
                {/if}
            {/if}
            
            {if $user->balance->prolongation_amount > 0}
                <div class="user_payment_form">
                    {if $user->balance->last_prolongation == 1}
                        <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                У вас осталась последняя пролонгация
                            </span>
                    {/if}
                    {if $user->balance->last_prolongation == 2}
                        <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                Уважаемый клиент, Вы использовали лимит пролонгаций по данному займу.
                                <br />
                                Для формирования позитивной кредитной истории срочно погасите заем!
                            </span>
                    {/if}
                    {if $user->balance->last_prolongation != 2}
                        <div class="action flex-block">
                            <button class="payment_button green button big js-prolongation-open-modal js-save-click" data-user="{$user->id}" data-event="1" type="button" data-number="{$user->balance->zaim_number}">
                                Минимальный платеж
                                {if $user->id|@array_search:[299082, 278878, 246778, 153750]}
                                    <span class="user_amount_pay">{$user->balance->ostatok_percents}</span>
                                {else}
                                    {$user->balance->prolongation_amount*1}
                                {/if} &nbsp;руб
                            </button>
                            <div class="min_payment_info">
                                Знаете ли вы, что: при регулярной оплате минимальных платежей в МФО ваша кредитная история становится лучше, рейтинг доверия повышается, а значит кредитный лимит будет максимальным
                            </div>
                        </div>
                    {/if}
                </div>
                {include file='prolongation.tpl'}
            {/if}



        {/if}

    {elseif $user->order}

        <div class="">

            {if !$user->order['status']}
                <p>Спасибо за вашу заявку, она будет обработана в ближайшее время.</p>
            {/if}


            {if $user->order['status_1c'] == '3.Одобрено'}

                {include file='accept_credit.tpl'}

                {if !$exitpool_completed}
                    {include file='exitpool.tpl'}
                {/if}

            {elseif in_array($user->order['status'], [9, 10])}

                <div class="waits">
                    <p>
                        Договор подписан!
                        <br />
                        Ожидайте мы переводим Вам займ на карту
                    </p>
                </div>
            {elseif in_array($user->order['status'], [11])}

                <div >
                    <p style="color:#d22">
                        При переводе произошла ошибка
                    </p>
                </div>
                {loan_form cards=$cards}

            {elseif $user->order['status'] == 5}

                <div class="files">
                    <p>Некоторые ваши фото не прошли проверку. Для получения займа вам необходимо их заменить!</p>
                    <a href="user/upload" class="button medium"> Заменить файлы</a>
                </div>

            {elseif $user->order['status'] == 1}

                {if $view_fake_first_order}
                    <div>
                        <p style="color:#d22">
                            К сожалению Вам отказано.
                            <br />Попробуйте отправить заявку повторно,
                            <br />так как возможны технические сбои.
                        </p>
                        <form method="POST" id="repeat_loan_form">

                            <input type="hidden" name="service_recurent" value="1" />
                            <input type="hidden" name="service_sms" value="1" />
                            <input type="hidden" name="service_insurance" value="1" />
                            <input type="hidden" name="service_reason" value="0" />
                            {if ($user_return_credit_doctor)}
                                <input type="hidden" name="service_doctor" value="0" />
                            {else}
                                <input type="hidden" name="service_doctor" value="1" />
                            {/if}

                            <input type="hidden" value="1" name="repeat_first_loan" />
                            <input type="hidden" value="{$user->order['id']}" name="order_id" />

                            <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" >
                                <div class="checkbox">
                                    <input class="js-input-accept" type="checkbox" value="1" id="repeat_loan_terms" name="accept" {if $accept}checked="true"{/if} />
                                    <span></span>
                                </div>
                                Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                                <span class="error">Необходимо согласиться с условиями</span>
                            </label>

                            <p>
                                <button type="submit" id="repeat_loan_submit" class="button big">
                                    Отправить повторно
                                </button>
                            </p>

                        </form>
                    </div>
                {else}
                    <p>Ваша заявка обрабатывается. Очень скоро мы Вам ответим.</p>

                    <div class="cdoctor" style="margin-top:0;">
                        <div class="cdoctor-left">
                            <div class="cdoctor-title"><span>Устал от долгов?</span></div>
                            <div class="cdoctor-info">Переходи по ссылке</div>
                        </div>
                        <div class="cdoctor-right">
                            <a class="button medium" href='https://kreditoff-net.ru' target="_blank" type="button">Хочу избавиться от долгов!</a>
                        </div>
                    </div>

                {/if}
            {/if}

            {if $user->order['status_1c'] == '6.Закрыт' && $user->file_uploaded}
                {loan_form cards=$cards}
            {/if}

            {if $user->order['individual']}
                {if $user->order['individual']->paid}
                    <p>Ваша заявка находится на индивидуальном рассмотрении</p>
                {else}
                    <p style="margin:1rem 0" class="warning-credit-text">
                        К сожалению по Вашей заявке отказано.
                        {if $user->order['official_response']}
                            <br />Причина отказа: {$user->order['official_response']}
                        {/if}
                    </p>

                    <div class="individual-block">
                        <div class="individual-action">
                            <button class="button medium js-individual-pay" data-order="{$user->order['individual']->id}" type="button">Персональное рассмотрение</button>
                        </div>
                        <div style="padding-top:10px">
                            <div class="checkbox" style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top:3px;">
                                <input class="" type="checkbox" value="1" id="individual-accept" name="individual_accept" checked="true" />
                                <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                            </div> Принимаю <a class="block_2" href="#" target="_blank">соглашение о персональном рассмотрении</a>

                        </div>
                    </div>

                    {include file='credit_doctor/credit_doctor_allowed.tpl'}
                    {include file='credit_doctor/credit_doctor_banner.tpl'}
                {/if}
            {elseif $user->order['cdoctor'] && ($settings->apikeys['cdoctor']['enabled'])}

                <div class="cdoctor">
                    <div class="cdoctor-left">
                        <div class="cdoctor-title"><span>Воспользуйтесь тарифом</span></div>
                        <div class="cdoctor-info">Получите деньги под 0%</div>
                    </div>
                    <div class="cdoctor-right">
                        <button class="button medium js-cdoctor-modal-open" type="button">Активировать</button>
                    </div>
                </div>
                <div style="display:none">
                    <div class="cdoctor-modal" id="cdoctor_modal">
                        <div class="cdoctor-modal-title">Оплата кредитного рейтинга</div>
                        <div class="cdoctor-modal-icn"><span>ICN</span></div>
                        <div class="cdoctor-modal-price">
                            <span>1900 P</span>
                        </div>
                        <div class="cdoctor-modal-link">
                            <a class="button medium" href="{$user->order['cdoctor']->url}">Оплатить 1900 Р</a>
                        </div>
                    </div>
                </div>

            {elseif $reason_block}
                <span class="has-reason-block"></span>

                {if !$user->user_approved}
                    {loan_form cards=$cards}
                    <style>
                        .credit-doctor-banner {
                            margin-top: 30px;
                        }
                    </style>
                {/if}

                {if $user->order['status'] == 3}
                    <p style="margin:1rem 0" class="warning-credit-text">
                        К сожалению по Вашей заявке отказано.
                        {if $user->order['official_response']}
                            <br />Причина отказа: {$user->order['official_response']}
                        {/if}
                    </p>
                {/if}

                {if $reason_block == 999}
                    <p style="margin:1rem 0" class="warning-credit-text">Вы не можете оставить заявку {$reason_block}</p>
                {else}
                    <p style="margin:1rem 0" class="warning-credit-text">Вы можете повторно обратиться за займом : {$reason_block|date} {$reason_block|time} (мск)</p>
                {/if}
                {*}
                <br />
                <a href="partners" target="_blank" class="part-item__link button">Обратитесь к нашим партнерам</a>
                {*}
                {include file='credit_doctor/credit_doctor_allowed.tpl'}
                {include file='credit_doctor/credit_doctor_banner.tpl'}

            {elseif $user->order['status'] == 3}
                {if $first_time_visit_after_rejection }
                    <span class="first_time_visit_after_rejection"></span>
                {/if}
                {if $repeat_loan_block}
                    <p>
                        К сожалению по Вашей заявке отказано.
                        {if $user->order['official_response']}
                            <br />
                            Причина отказа: {$user->order['official_response']}
                        {/if}
                        <br />

                        Вы можете повторно обратиться за займом {$repeat_loan_block|date} {$repeat_loan_block|time} (мск)

                        {include file='credit_doctor/credit_doctor_allowed.tpl'}
                        {include file='credit_doctor/credit_doctor_banner.tpl'}
                    </p>
                    {*}
                    <p>
                        <a href="partners" target="_blank" class="part-item__link button">Обратитесь к нашим партнерам</a>
                    </p>
                    {*}
                {else}
                    {if $user->fake_order_error == 0}
                            <p class="warning-credit-text">К сожалению по Вашей заявке от {$user->order['date']|date} отказано.</p>
                            {if $user->id != 42863} {* фикс для одного пользователя (просьба Толика) *}
                                {if $user->order['official_response']}

                                    <p class="warning-credit-text">Причина отказа: {$user->order['official_response']}</p>
                                {/if}
                            {/if}
                        {include file='credit_doctor/credit_doctor_allowed.tpl'}
                        {include file='credit_doctor/credit_doctor_banner.tpl'}

                    {/if}
                    <div class="clearfix">
                        {if $user->file_uploaded}
                            {loan_form cards=$cards}
                        {/if}
                    </div>
                {/if}


            {/if}

        </div>

    {else}

        <div class="about">
            <div>Открытых займов не найдено</div>
        </div>


        {if $user->file_uploaded}
            {loan_form cards=$cards}
        {/if}
    {/if}

    {if $user->cdoctor_pdf}
        <div class="cdoctor-file" style="background:url(design/{$settings->theme|escape}/img/cdoctor.svg) right center no-repeat">
            <div class="cdoctor-file-left">
                <div class="cdoctor-file-title">Зачем ждать, когда можно действовать?</div>
                <div class="cdoctor-file-info">Мы уже получили Вашу кредитную историю и принимаем решение. Узнайте больше.</div>
                <a href="{$user->cdoctor_pdf}" target="_blank" class="button medium">Узнать кредитную историю</a>
            </div>
            <div class="cdoctor-file-left">
            </div>
        </div>
    {/if}


    {if $user->balance->sale_info!='Договор продан' && $user->balance->zaim_number!='Нет открытых договоров' && $user->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
        {if $loan_expired}
            <div>
                <p class="text-red">Ваш заём просрочен.</p>
            </div>
        {/if}
    {/if}
    
    {if !$user_approved}
        {include 'promo_100000.tpl'}
    {/if}

    {if $user->id != 56219 && $user->balance->sale_info!='Договор продан' && $user->balance->zaim_number != 'Ошибка. Обратитесь в офис'}
        <div class="cards">

            {if $cards}
                <div class="about">
                    <div>Доступные карты</div>
                    {*}<div class="annotacion" >(для погашения задолженности по займам через личный кабинет)</div>{*}
                </div>

                <div class="split">
                        <ul id="card_list">
                            {foreach $cards as $card}
                                {if (!$user->use_b2p && $card->rebill_id) || $user->use_b2p}
                                    <li>
                                        <div>Номер карты: {$card->pan}</div>
                                        <a href="javascript:void(0);"
                                           class="toggle-link js-autodebit {if $card->autodebiting}toggle-link-on js-detach{/if}"
                                           data-number="{$card->pan}"
                                           data-card="{$card->id}"
                                        >
                                            <span>Автоплатеж</span>
                                        </a>
                                    </li>
                                {else}
                                    <li style="color:red">
                                        <div>Номер карты: {$card->pan}
                                            <div style="font-size:1rem;">Ошибка привязки карты. Пожалуйста привяжите карту повторно.</div>
                                        </div>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                </div>

            {else}

                <div class="nocards">Нет доступных карт</div>

            {/if}

            {$card_error}

            {if $settings->b2p_enabled || $user->use_b2p}
{*                <a href="{$user->add_card}" class="button medium js-b2p-add-card" style="margin-top:5px;">Добавить карту</a>*}
                <button id="myBtn" class="button medium" style="margin-top:5px;">Добавить карту</button>
            {elseif $user->add_card}
{*                <a href="{$user->add_card}" class="button medium" style="margin-top:5px;">Добавить карту</a>*}
                <button id="myBtn" class="button medium" style="margin-top:5px;">Добавить карту</button>
            {/if}


            <div id="myModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <fieldset class="passport4-file file-block">

                        <legend>Фото карты</legend>

                        <div class="alert alert-danger " style="display:none"></div>

                        <div class="user_files">
                            {if $passport4_file}
                                <label class="file-label">
                                    <div class="file-label-image">
                                        <img src="{$passport4_file->name|resize:100:100}" />
                                    </div>
                                    <span class="js-remove-file" data-id="{$passport4_file->id}">Удалить</span>
                                    <input type="hidden" id="passport4" name="user_files[]" value="{$passport4_file->id}" />
                                </label>
                            {/if}
                        </div>

                        <div class="file-field" {if $passport4_file}style="display:none"{/if}>
                            <div class="file-label">
                                <label for="user_file_passport4" class="file-label-image">
                                    <img src="design/{$settings->theme|escape}/img/card_logo.svg" />
                                </label>
                                <label>
                                    <i style="font-size: 22px">Приложите фото вашей именной карты так, чтобы отчетливо были видны все цифры её номера и фамилия владельца</i>
                                </label>
                                <label onclick="sendMetric('reachGoal', 'get_user_photo_5');"  class="get_mobile_photo photo_btn not-visible-sm" for="user_file_passport4" >
                                    Сделать фото
                                </label>

                                <label onclick="sendMetric('reachGoal', 'download_user_photo_5');"  class="photo_btn" for="user_file_passport4">Загрузить фото</label>
                                <input type="file" id="user_file_passport4" name="passport4" accept="image/jpeg,image/png" data-type="passport4" />
                            </div>
                        </div>
                    </fieldset>
                    <div>
                        <button class="button medium next-step-button" style="margin-top:5px;" disabled="true">Далее </button>
                    </div>
                </div>

            </div>


        </div>
    {/if}

    {if $user->balance->sale_info!='Договор продан' && $user->balance->zaim_number && $user->balance->zaim_number!='Ошибка' && $user->balance->zaim_number!='Нет открытых договоров'}

        {if $user->balance->last_prolongation != 2}

            {if $user->balance->prolongation_amount > 0}
                <div class="user_payment_form" style="margin-top:20px;">
                    <div class="action">
                        <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="2" onclick="$('#close_credit_form').fadeIn('fast');$(this).hide()" type="button">Погасить заём полностью</button>
                    </div>
                </div>

            {else}
                <form method="POST" action="user/payment" style="margin-top:15px;" class="user_payment_form" >
                    <input type="hidden" name="number" value="{$user->balance->zaim_number}" />
                    <div class="action">
                        {if $check_prolong}
                            <input style="display:none" class="payment_amount" data-order_id="{$user->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount" value="{($user->balance->ostatok_percents-$user->balance->ostatok_peni+999)}" max="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" min="1" />
                            <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="4" type="submit">Погасить заём полностью {($user->balance->ostatok_percents-$user->balance->ostatok_peni+999)} руб</button>
                        {else}
                            <input style="display:none" class="payment_amount" data-order_id="{$user->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount" value="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" max="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" min="1" />
                            <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="5" type="submit">Погасить заём полностью</button>
                        {/if}
                    </div>
                </form>
            {/if}
        {/if}
        <div id="close_credit_form"  style="margin-top:15px;{if $user->balance->last_prolongation != 2}display:none{/if}">
            {if $user->balance->last_prolongation != 2}
                <div style="max-width:500px;margin-bottom:10px;">
                    <p style="color:#080;margin-bottom:10px;">
                        При оплате минимальной суммы ваша кредитная история станет лучше, а кредитный лимит максимальным
                    </p>
                    <button class="payment_button green button big js-prolongation-open-modal js-save-click" data-user="{$user->id}" data-event="3" type="button" data-number="{$user->balance->zaim_number}">
                        Минимальный платеж {$user->balance->prolongation_amount*1}&nbsp;руб
                    </button>

                </div>
            {/if}

            <form method="POST" action="user/payment" class="user_payment_form" >
                <div class="action">
                    <input type="hidden" name="number" value="{$user->balance->zaim_number}" />
                    {if $check_prolong}
                        <input style="display:none" class="payment_amount" data-order_id="{$user->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount" value="{($user->balance->ostatok_percents-$user->balance->ostatok_peni+999)}" max="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" min="1" />
                        <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="4" type="submit">Погасить заём полностью {($user->balance->ostatok_percents-$user->balance->ostatok_peni+999)} руб</button>
                    {else}
                        <input style="display:none" class="payment_amount" data-order_id="{$user->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount" value="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" max="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" min="1" />
                        <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="5" type="submit">Погасить заём полностью</button>
                    {/if}
                </div>
            </form>
        </div>

        <div class="user_payment_form">
            <div class="action">
                <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="6" onclick="$('#other_summ').fadeIn('fast');$(this).hide()" type="button">Оплатить другую сумму</button>
            </div>
        </div>

        <form method="POST" action="user/payment" id="other_summ" class="user_payment_form" style="display:none">
            <input type="hidden" name="number" value="{$user->balance->zaim_number}" />
            <div class="action">
                {if $user->balance->prolongation_amount > 0}
                    <div style="max-width:500px;">
                        <p style="margin-bottom:0;">Внимание, после оплаты дата возврата займа не изменится!
                            <br />Во избежание возникновения просрочки и ухудшения вашей кредитной истории,
                            пожалуйста, убедитесь в том, что вы успеете полностью погасить заём до {$user->balance->payment_date|date}.
                            <br />Если вы хотите пролонгировать заём, воспользуйтесь кнопкой «Минимальный платеж»
                        </p></div>
                {/if}
                <p style="margin-bottom:0;">Другая сумма</p>
                <input class="payment_amount" data-order_id="{$user->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount" value="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" max="{($user->balance->ostatok_od+$user->balance->ostatok_percents+$user->balance->ostatok_peni)}" min="1" />
                <button class="payment_button button medium js-save-click" data-user="{$user->id}" data-event="7" type="submit">Оплатить</button>
            </div>
        </form>

    {/if}

    {if $user->balance->zaim_number == 'Нет открытых договоров' && (!$user->order || $user->order['status'] == 3 || $user->order['status'] == 2)}
        <div class="remove_account_block">
            <a href="javascript:void(0);" id="remove_account"> Удалить личный кабинет</a>
            <br />
            <a href="{$config->root_url}/files/docs/zayavlenie_na_udalenie_kabineta.odt" target="_blank">Заявление на удаление кабинета</a>
        </div>
        <div style="display:none">
            <div id="confirm_remove_account">
                <p><strong>Для удаления личного кабинета отправьте официальное заявление на почту info@boostra.ru</strong></p>
                <div class="actions">
                    <button type="button" id="close_modal_remove" class="button button-inverse medium">Закрыть</button>
                </div>
            </div>
        </div>
        {*}
        <div style="display:none">
            <div id="confirm_remove_account">
                <p><strong>Вы действительно хотите удалить личный кабинет?</strong></p>
                <p>Будьте внимательны, это действие нельзя отменить!</p>
                <p>Вы больше не сможете войти в кабинет и создать учетную запись на сайте!</p>
                <div class="actions">
                    <button type="button" id="close_modal_remove" class="button button-inverse medium">Отменить</button>
                    <button type="button" id="confirm_remove" class="button medium">Удалить</button>
                </div>
            </div>
        </div>
        {*}
    {/if}

    {if ($show_asp_modal)}
        <style>
            #asp_sms {
                background: #fff;
                max-width: 420px;
                border-radius: 40px;
                margin: 0 auto;
                padding: 60px 30px;
                position: relative;
            }
            #asp_sms label {
                margin-top: 20px;
                display: flex;
            }
            #asp_sms label:not(.error) {
                margin-bottom: 20px;
            }
            #asp_sms a {
                text-decoration: underline;
                cursor: pointer;
            }
            #asp_sms .close-modal {
                position: absolute;
                top: 20px;
                right: 20px;
                color: grey;
            }
            #asp_sms .text-error {
                color: #f00;
            }
            #asp_sms [name="sms_asp"] {
                max-width: 100px;
            }
            .wrapper_sms_code, .wrapper_sms_code .sms-asp-code-error, .asp-sign-accept {
                margin-top: 10px;
            }
            .wrapper_sms_code [disabled], .wrapper_sms_code .disabled {
                cursor: no-drop;
            }
            .wrapper_sms_code .sms-asp-code-error {
                font-size: 14px;
                color: red;
            }
            @media screen and (max-width: 520px) {
                .sms-asp-code-error {
                    margin-top: 15px;
                }
            }
        </style>

        <div id="asp_sms">
            <a style="display: none;" onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();"><small>пропустить</small></a>
            <h5>Может будем общаться чаще?<br/>
                Повысить уровень доверия и привлекательности в компании.</h5>
            <label class="big left">
                <div class="checkbox">
                    <input type="checkbox" value="1" name="accept_asp" required />
                    <span></span>
                </div> Я ознакомлен и согласен со <a style="margin-left: 5px;" href="/files/docs/asp_zaim.pdf" target="_blank">следующим</a>
            </label>
            <div class="button medium asp-sign-accept" onclick="asp_app.click_asp_accept();">Подписать</div>
            <div class="wrapper_sms_code" style="display: none;">
                <div class="button sms-asp-send-button" onclick="!asp_app.validate_accept() || asp_app.send_sms();">Получить код</div>
                <input type="text" name="sms_asp" disabled />
                <div class="sms-asp-code-error" style="display: none;"></div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function (){
                $.magnificPopup.open({
                    items: {
                        src: '#asp_sms'
                    },
                    type: 'inline',
                    showCloseBtn: false,
                    modal:true,
                });

                asp_app.init_mask();
                asp_app.init_skip_button_timer();
                $('#asp_sms a, #asp_sms .button').on('click', function () {
                    asp_app.skip_button_second = 10;
                });
            });

            let asp_app = Object();
            const default_sms_delay_seconds = 30;
            const $code_field = $('[name="sms_asp"]');
            const user_phone = '{$user->phone_mobile}';
            const ASP_SMS_ERROR = 'Вы ввели неверный код.';

            asp_app.timer_second = 0;
            asp_app.asp_timer = null;
            asp_app.skip_button_second = 10;
            asp_app.skip_button_timer = null;
            asp_app.skip_button_elements = $('#asp_sms .close-modal');
            asp_app.accept_field = $('[name="accept_asp"]');

            asp_app.init_skip_button_timer = function () {
                asp_app.skip_button_timer = setInterval(function () {
                    if (asp_app.skip_button_second === 0) {
                        asp_app.skip_button_elements.show();
                        clearInterval(asp_app.skip_button_timer);
                    }
                    asp_app.skip_button_second--;
                }, 1000);
            };

            asp_app.validate_accept = function () {
                let accept_val = asp_app.accept_field.is(':checked');
                $('#asp_sms .text-error').remove();

                if (accept_val) {
                    $('#asp_sms label').removeClass('error');
                } else {
                    $('#asp_sms label').addClass('error').after('<small class="text-error">для продолжения необходимо Ваше согласие</small>');
                }

                return !$('#asp_sms label').hasClass('error');
            };

            asp_app.click_asp_accept = function () {
                if (asp_app.validate_accept()) {
                    $('.asp-sign-accept').hide();
                    $('.wrapper_sms_code').show();
                }
            };

            // выключение таймера и снятие блокировок
            asp_app.delete_timer = function () {
                clearInterval(asp_app.asp_timer);
                $('.sms-asp-send-button').removeClass('disabled').text('Отправить ещё раз');
                $("[name='sms_asp']").val('').prop('disabled', true);
                $('.sms-asp-code-error').hide();
            };

            // функция таймера отправки смс
            asp_app.init_timer = function (seconds) {
                asp_app.timer_second = seconds;

                $('.sms-asp-send-button').addClass('disabled');
                $("[name='sms_asp']").prop('disabled', false);

                asp_app.asp_timer = setInterval(function (){
                    if (asp_app.timer_second === 0) {
                        asp_app.delete_timer();
                    } else {
                        $('.sms-asp-send-button').text(asp_app.timer_second);
                    }
                    asp_app.timer_second--;
                }, 1000);
            };

            // отправка СМС
            asp_app.send_sms = function () {
                asp_app.init_timer(default_sms_delay_seconds);
                $.ajax({
                    url: 'ajax/sms.php',
                    data: {
                        phone: user_phone,
                        action: 'send'
                    },
                    success: function (resp) {
                        if (resp.error) {
                            if (resp.error === 'sms_time')
                                asp_app.init_timer(resp.time_left);
                            else
                                console.log(resp);
                        } else {
                            if (resp.mode === 'developer') {
                                $("[name='sms_asp']").prop('disabled', false).val(resp.developer_code);
                                asp_app.validate_sms_code();
                            } else {
                                console.log('response: ', resp);
                            }
                        }
                    }
                });
            };

            // маска ввода для СМС
            asp_app.init_mask = function () {
                $code_field.inputmask({
                    mask: "9999",
                    oncomplete: function() {
                        asp_app.validate_sms_code();
                    }
                });
            };

            // проверка СМС
            asp_app.validate_sms_code = function () {
                let sms_code = $code_field.val();
                $.ajax({
                    url: 'ajax/sms.php',
                    data: {
                        phone: user_phone,
                        action: 'check_asp',
                        code: sms_code,
                    },
                    success: function (resp) {
                        if (resp.success && resp.validate_sms) {
                            $.magnificPopup.close();
                        } else {
                            $('.sms-asp-code-error').show().text(resp.soap_fault ? resp.error : ASP_SMS_ERROR);
                        }
                    }
                });
            };
        </script>
    {/if}

    <script>
        $(".partner-href").on('click', function (event) {
            event.preventDefault();
            let url = $(this).attr('href');
            $.ajax({
                url: 'ajax/user.php?action=add_statistic_partner_href',
                success: function () {
                    window.location = url;
                }
            });
        });

        {if $user->id == 153750}
            $(document).on('change', '#choose_insure', function () {
               let checked = $(this).prop('checked'),
                   amount = {$user->balance->ostatok_percents};
               if (checked) {
                   amount = {$user->balance->ostatok_percents + ($user->balance->ostatok_od * 0.1)};
               }

               $('.user_amount_pay').text(amount);
            });
        {/if}
    </script>
</div>