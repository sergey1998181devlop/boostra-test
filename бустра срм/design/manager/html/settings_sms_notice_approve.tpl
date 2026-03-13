{$meta_title = 'Настройка рассылки для Одобренных заявок' scope=parent}

{capture name='page_styles'}

    <!--nestable CSS -->
    <link href="design/{$settings_notice_sms_approve->theme}/assets/plugins/nestable/nestable.css" rel="stylesheet" type="text/css" />

    <style>
        .onoffswitch {
            display:inline-block!important;
            vertical-align:top!important;
            width:60px!important;
            text-align:left;
        }
        .onoffswitch-switch {
            right:38px!important;
            border-width:1px!important;
        }
        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right:0px!important;
        }
        .onoffswitch-label {
            margin-bottom:0!important;
            border-width:1px!important;
        }
        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height:18px!important;
            line-height:18px!important;
        }
        .onoffswitch-switch {
            width:20px!important;
            margin:1px!important;
        }
        .onoffswitch-inner::before {
            content:'ВКЛ'!important;
            padding-left: 10px!important;
            font-size:10px!important;
        }
        .onoffswitch-inner::after {
            content:'ВЫКЛ'!important;
            padding-right: 6px!important;
            font-size:10px!important;
        }

        .scoring-content {
            position:relative;
            z-index:999;
            border:1px solid rgba(120, 130, 140, 0.13);;
            border-top:0;
            background:#383f48;
            border-bottom-left-radius:4px;
            border-bottom-right-radius:4px;
            margin-top: -5px;
        }

        .collapsed .fa-minus-circle::before {
            content: "\f055";
        }
        h4.text-white {
            display:inline-block
        }
        .move-zone {
            display:inline-block;
            color:#fff;
            padding-right:15px;
            margin-right:10px;
            border-right:1px solid #30b2ff;
            cursor:move
        }
        .move-zone span {
            font-size:24px;
        }

        .dd {
            max-width:100%;
        }

        small.label {
            margin-left:10px;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Настройки
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Автоматическая отправка смс</li>
                    <li class="breadcrumb-item active js-site-id-text">{$active_site_id}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <div class="btn-group" role="group">
                    {foreach $active_sites as $site}
                        <a class="js-site-tab-filter btn btn-xs mr-2 {if $site->site_id == $active_site_id}btn-outline-info disabled active{else} btn-outline-primary{/if}" data-site="{$site->site_id}" href="/settings_sms_notice_approve?site_id={$site->site_id}">{$site->title} <span>[{$site->site_id}]</span></a>
                    {/foreach}
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <div class="row grid-stack" data-gs-width="12" data-gs-animate="yes">
            <div class="col-md-12">
                <ul class="mt-2 nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#sms" role="tab" aria-selected="true">
                            Настройки СМС (одобрения)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#sms_reject" role="tab" aria-selected="true">
                            Настройка смс отказникам
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#sms_all" role="tab" aria-selected="true">
                            Настройки СМС
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#sms_after_maratory" role="tab" aria-selected="true">
                            СМС после моратория
                        </a>
                    </li>
                </ul>
                <div class="tab-content js-ajax-container">
                    <div id="sms" class="tab-pane active" role="tabpanel">
                        <div class="card mt-5">
                            <div class="card-header">
                                <h4>Условия отправки СМС (одобрения)</h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Одобрена заявка по займу (автоодобрение в том числе), деньги по одобренной заявке не получены.</li>
                                    <li>СМС в день одобрения заявки уходит сразу после одобрения.</li>
                                    <li>СМС через день и все последующие дни отправляются в 15 ч по МСК.</li>
                                    <li>СМС после моратория.</li>
                                </ul>
                            </div>
                        </div>
                        <p class="my-3 label label-warning">
                            <a class="text-danger" href="https://smsc.ru/api/http/status_messages/statuses/#menu"
                               target="_blank">Информация о статусах сообщения</a>
                        </p>
                        <div id="sms_list_result"></div>
                        <form class="" method="POST" >
                            <input type="hidden" name="site_id" value="{$active_site_id}" />
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Шаблон сообщения сразу после одобрения</label>
                                            <textarea name="sms_template_approve[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_approve->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{amount}}{{firstname}}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_approve[status]" class="onoffswitch-checkbox" value="1" id="status_sms_template_approve" {if $sms_approve_status}checked{/if} />
                                                <label class="onoffswitch-label" for="status_sms_template_approve">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    {foreach $settings_approve as $key => $setting}
                                        <hr/>
                                        <div class="form-group row align-items-center">
                                            <div class="col-md-6">
                                                <label>Шаблон сообщения через {$key} дней(я) после одобрения</label>
                                                <textarea name="notice_sms_approve[message_day_{$key}][text]" class="form-control" placeholder="Текст сообщения">{$setting['text']}</textarea>
                                                <small class="form-text text-muted">
                                                    {literal}В шаблоне используется автоподстановка переменных <code>{{amount}}{{firstname}}</code>.{/literal}
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="notice_sms_approve[message_day_{$key}][status]" class="onoffswitch-checkbox" value="1" id="status_{$key}" {if $setting['status']}checked{/if} />
                                                    <label class="onoffswitch-label" for="status_{$key}">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 my-1">
                                                <p class="text-warning"><small>Кол-во СМС для отправки: {$setting['total_sms']|intval}</small></p>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="sms_reject" class="tab-pane" role="tabpanel">
                        <div class="card mt-5">
                            <div class="card-header">
                                <h4>Условия отправки СМС (отказникам)</h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>
                                        Первая смс отправляется при отказе по заявке, сразу же.
                                    </li>
                                    <li>Вторая отправляется через 15 минут после условий  выше.  </li>
                                    <li>Третья отправляется в течении 15-и минут после второй.</li>
                                    <li>Четвёртая отправляется в течении 15-и минут после третьей.</li>
                                    <li>Пятая отправляется в течении 15-и минут после четвёртой.</li>
                                </ul>
                            </div>
                        </div>
                        <form class="" method="POST" >
                            <input type="hidden" name="site_id" value="{$active_site_id}" />
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Шаблон сообщения сразу после отказа </label>
                                            <textarea name="sms_template_reject[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_reject->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{firstname}}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_reject[status]" class="onoffswitch-checkbox" value="1" id="status_sms_template_reject" {if $sms_template_reject->status}checked{/if} />
                                                <label class="onoffswitch-label" for="status_sms_template_reject">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Шаблон через 15 минут</label>
                                            <textarea name="sms_template_reject_second[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_reject_second->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{firstname}}{{amount}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_reject_second[status]" class="onoffswitch-checkbox" value="1" id="sms_template_reject_second" {if $sms_template_reject_second->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_reject_second">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Шаблон через 15 после отправки второго шаблона</label>
                                            <textarea name="sms_template_reject_third[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_reject_third->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{firstname}}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_reject_third[status]" class="onoffswitch-checkbox" value="1" id="sms_template_reject_third" {if $sms_template_reject_third->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_reject_third">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Смс через 15 после отправки третьего шаблона</label>
                                            <textarea name="sms_template_reject_fourth[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_reject_fourth->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{firstname}}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_reject_fourth[status]" class="onoffswitch-checkbox" value="1" id="sms_template_reject_fourth" {if $sms_template_reject_fourth->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_reject_fourth">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-primary">Смс через 15 после отправки четвёртого шаблона</label>
                                            <textarea name="sms_template_reject_fifth[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_reject_fifth->template}</textarea>
                                            <small class="form-text text-muted">
                                                {literal}В шаблоне используется автоподстановка переменных <code>{{firstname}}</code>.{/literal}
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_reject_fifth[status]" class="onoffswitch-checkbox" value="1" id="sms_template_reject_fifth" {if $sms_template_reject_fifth->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_reject_fifth">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="sms_all" class="tab-pane" role="tabpanel">
                        <form class="" method="POST" >
                            <input type="hidden" name="site_id" value="{$active_site_id}" />
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-warning">СМС клиентам с мотивацией на закрытие</label>
                                            <textarea  name="sms_template_motivation_close[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_motivation_close->template}</textarea>
                                            <small class="form-text text-muted">
                                                Условия для отбора получателей смс: Договор открыт (действует), НК (ранее не было закрытых договоров займа), ставка в договоре займа 0%, Просрочка = -10; -5; -1 (до Даты платежа, указанной в договоре займа остался 1 день, 5 дней или 10 дней ).
                                                Автоматическая отправка смс каждый день в 13:00 по МСК
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_motivation_close[status]" class="onoffswitch-checkbox" value="1" id="sms_template_motivation_close" {if $sms_template_motivation_close_status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_motivation_close">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label for="sms_template_phone_partner[template]" class="text-warning">СМС по телефону, который получили по апи</label>
                                            <textarea id="sms_template_phone_partner[template]"  name="sms_template_phone_partner[template]" class="form-control" placeholder="Текст сообщения">{$settings->sms_template_phone_partner.template}</textarea>
                                            <small class="form-text text-muted">
                                                Данный телефон мы получаем по апи из URL <code>{$config->front_url}/partner-phone-api</code> cron работает каждые 5 минут
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_phone_partner[status]" class="onoffswitch-checkbox" value="1" id="sms_template_phone_partner[status]" {if $settings->sms_template_phone_partner.status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_phone_partner[status]">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    {if $manager->role == 'developer' || $manager->role == 'ts_operator'}
                                    <hr class="mt-2 mb-2"/>
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-warning">СМС клиентам c переходом на Лайкзайм</label>
                                            <textarea  name="sms_template_likezaim[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_likezaim->template}</textarea>
                                            <small class="form-text text-muted">
                                                Сообщение отправляется спустя 10 минут после отправки запроса в Лайкзайм.<br />
                                                В конце сообщения добавляется короткая ссылка вида likezaim67.ru/b/64vjzcXA (24 символа)
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="likezaim_enabled" class="onoffswitch-checkbox" value="1" id="likezaim_enabled" {if $settings->likezaim_enabled}checked{/if} />
                                                <label class="onoffswitch-label" for="likezaim_enabled">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                            <label class="text-info"><small>Отправка займов на Лайкзайм</small></label>
                                            <br />
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_likezaim[status]" class="onoffswitch-checkbox" value="1" id="sms_template_likezaim" {if $sms_template_likezaim->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_likezaim">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                            <label class="text-info"><small>Отправка СМС клиентам</small></label>
                                        </div>
                                    </div>
                                    {/if}
                                    {if $manager->role == 'developer' || $manager->role == 'ts_operator'}
                                        <hr class="mt-2 mb-2"/>
                                        <div class="form-group row align-items-center">
                                            <div class="col-md-6">
                                                <label class="text-warning">Количество дней для временной отписки</label>
                                                <input type="number" name="temporary_sms_unsubscribe_days" class="form-control" value="{$temporary_sms_unsubscribe_days}">
                                                <small class="form-text text-muted">
                                                    Укажите количество дней, на которое вы хотите временно отключить SMS-уведомления через чат.
                                                </small>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="sms_after_maratory" class="tab-pane" role="tabpanel">
                        <div class="card mt-5">
                            <div class="card-header">
                                <h4>Условия отправки СМС клиентам, у которых прошел мораторий</h4>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>
                                        Клиент не должен быть отписан от рекламных рассылок
                                    </li>
                                    <li>Клиент не должен находится в ЧС  </li>
                                    <li>У клиента не должно быть активного договора (статусы заявки, которые исключаем - Одобрено, Выдано).</li>
                                    <li><strong style="color:red;">Ограничение:
                                            Рассылка осуществляется только с 10-00 до 13-00 по МСК.</strong></li>

                                </ul>
                            </div>
                        </div>
                        <form class="" method="POST" >
                            <input type="hidden" name="site_id" value="{$active_site_id}" />
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="text-warning">Отказ Скористы</label>
                                            <textarea  name="sms_template_emergency[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_emergency->template}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_emergency[status]" class="onoffswitch-checkbox" value="1" id="sms_template_emergency" {if $sms_template_emergency->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_emergency">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <!-- second -->
                                        <div class="col-md-6">
                                            <label class="text-warning">Недействительный паспорт</label>
                                            <textarea  name="sms_template_invalid_passport[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_invalid_passport->template}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_invalid_passport[status]" class="onoffswitch-checkbox" value="1" id="sms_template_invalid_passport" {if $sms_template_invalid_passport->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_invalid_passport">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <!-- third -->
                                        <div class="col-md-6">
                                            <label class="text-warning">Истёк срок действия</label>
                                            <textarea  name="sms_template_expired[template]" class="form-control" placeholder="Текст сообщения">{$sms_template_expired->template}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="sms_template_expired[status]" class="onoffswitch-checkbox" value="1" id="sms_template_expired" {if $sms_template_expired->status}checked{/if} />
                                                <label class="onoffswitch-label" for="sms_template_expired">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>


{capture name='page_scripts'}
    <script src="design/{$settings->theme}/assets/plugins/toast-master/js/jquery.toast.js"></script>
    <script>
        $(document).ready(function (e) {
            $(".onoffswitch input[type='checkbox']").on('change', function () {
                let elem = $(this).closest('.form-group').find('textarea');
                if ($(this).prop('checked')) {
                    $(elem).attr('required', 'required')
                } else {
                    $(elem).removeAttr('required')
                }
            });

            $(document).on('submit', 'form', function(e){
                let $form = $(this);
                let $button = $form.find('button[type="submit"]');
                $button.prop('disabled', true);
            });

            $(document).on('click', '.js-site-tab-filter', function(e){
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    return;
                }
                $('.preloader').show();
            });

            $(document).on('click', '#sms_list_result .jsgrid-pager a', function (e) {
                e.preventDefault();
            });
        });
    </script>
{/capture}

