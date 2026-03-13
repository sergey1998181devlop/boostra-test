<section id="extra_services_wrapper" class="--hide">
    {if $extra_services_to_refuse}

        <div class="tab_wrapper">
            <div class="tab_tabs">

                {foreach $extra_services_to_refuse as $service_type => $extra_services}

                    <!-- Tab -->
                    <div class="tab_tab">
                        <input type="radio" name="css-tabs" id="{$extra_services['slug']|escape}" {$extra_services['checked']|escape} class="tab_tab-switch">
                        <label for="{$extra_services['slug']|escape}" class="tab_tab-label">{$extra_services['title']|escape}</label>
                        <div class="tab_tab-content">
                            <div>{$extra_services['description']|escape}</div>

                            <div class="tab-button_wrapper">
                                <button class="action-open_service_refuse_modal --pale" service="{$extra_services['slug']|escape}">Оформить отказ для займа
                                    <select class="input-select_service_refuse_modal__loan-number">
                                        {foreach $extra_services as $loan => $extra_service}
                                            {if ! is_string( $extra_service ) }
                                                <option value="{$loan|escape}">{$loan|escape}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </button>
                            </div>
                        </div>
                    </div>

                    {foreach $extra_services as $loan => $extra_service}

                        {if ! is_string( $extra_service ) }

                            <!-- Modal window -->
                            <div id="extra_service-modal--{$extra_service->slug}" class="extra_service-modal" data-loan="{$loan|escape}">

                                <div class="extra_service-modal-content">
                                    <span class="action-close button-close-x">&times;</span>
                                    <h1>Возврат средств за услугу "{$extra_service->title|escape}"</h1>
                                    <div class="extra_service-modal-content-inner">
                                        <br>
                                        <hr>
                                        <p>Нажимая кнопку «Вернуть 50% стоимости и продолжить пользоваться услугой» я подтверждаю, что не отказываюсь от приобретенной услуги, а хочу воспользоваться скидкой и вернуть 50% стоимости и что согласен с возвратом денежных средств на банковскую карту{if $extra_service->slug !== 'credit_doctor'} {$extra_service->return_card->pan} {/if}, с которой была оплачена стоимость дополнительных услуг.</p>
                                        <p>Возврат по реквизитам третьего лица невозможен.</p>
                                        <p>Если у Вас остались вопросы {if $extra_service->slug !== 'credit_doctor'}или Вам необходимо оформить возврат на другую карту {/if}, обращайтесь на горячую линию по номеру телефона 8 800 333 30 73 или в почту info@boostra.ru.</p>
                                        <div class="wrapper-control" amount="50" service="{$extra_service->slug|escape}" loan_number="{$loan|escape}">
                                            <button class="button action-prepare_docs --inline-block --white" {if $extra_service->discount_refunded}disabled="disabled"{/if}>
                                                <p>Вернуть 50% и продолжить пользоваться</p>
                                                <img src="design/{$settings->theme|escape}/img/preloader.gif" class="button-preloader --hide">
                                            </button>
                                            <button class="button --inline-block --hide action-continue">Продолжить</button>
                                        </div>
                                        <hr>
                                        <p>Нажимая кнопку “Вернуть 100% и отказаться от услуги” я подтверждаю, что отказываюсь от приобретенной услуги и что согласен с возвратом денежных средств на банковскую карту{if $extra_service->slug !== 'credit_doctor'} {$extra_service->return_card->pan} {/if}, с которой была оплачена стоимость дополнительных услуг.</p>
                                        <p>Возврат по реквизитам третьего лица невозможен.</p>
                                        <p>Если у Вас остались вопросы {if $extra_service->slug !== 'credit_doctor'}или Вам необходимо оформить возврат на другую карту {/if}, обращайтесь на горячую линию по номеру телефона 8 800 333 30 73 или в почту info@boostra.ru.</p>
                                        <div class="wrapper-control" amount="100" service="{$extra_service->slug|escape}" loan_number="{$loan|escape}">
                                            <button class="button action-prepare_docs --inline-block --white" {if $extra_service->fully_refunded}disabled="disabled"{/if}>
                                                <p>Вернуть 100% и отказаться от услуги</p>
                                                <img src="design/{$settings->theme|escape}/img/preloader.gif" class="button-preloader --hide">
                                            </button>
                                            <button class="button --inline-block --hide action-continue --white">Продолжить</button>
                                        </div>
                                        <hr>
                                        <br>
                                        <button class="button button-exit--with_text --block --block-center action-close">
                                            <p>Продолжить пользоваться</p>
                                        </button>
                                        <br>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                {/foreach}

            </div>
        </div>

    {else}
        <div>
            <h3>Дополнительные услуги не подключены</h3>
        </div>
    {/if}

</section>

<script src="design/{$settings->theme|escape}/js/ajax.js"></script>
<script src="design/{$settings->theme|escape}/js/refuse_from_service.js?v=1.0.4"></script>

{literal}
    <script type="text/javascript">
        $('#link-extra_services').on('click', function( event ){
            $(event.target).toggleClass('--active_plate');
            $('#extra_services_wrapper').toggleClass('--hide');
        });
    </script>
{/literal}