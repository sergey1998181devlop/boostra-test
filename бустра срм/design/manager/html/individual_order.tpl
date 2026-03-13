{$meta_title="Заявка №`$order->order_id`" scope=parent}
{assign 'stopfactorsImportant' [
"Негатив по ФССП",
"Высокая доля просрочек в КИ за последние 2 года",
"Глубокая просрочка в КИ за последние 2 года",
"Глубокая просрочка по последним займам в КИ",
"Негативы последних займов в КИ",
"Высокая просрочек в КИ за последние 2 года",
"Высокая вероятность дефолта по КИ",
"Высокий риск банкротства в течении 2х месяцев",
"Банкротство в КИ",
"Подозрение на фрод",
"Черный список скористы",
"Несовпадение ФИО с данными официальных источников",
"Реквизиты паспорта не уникальны",
"Большое количество разных телефонов в заявках на текущий паспорт",
"Регион проживания не совпадает с регионом телефона и регистрации",
"Регион повышенного риска (Белгородская обл)",
"Регион военных действий",
"Регион вблизи военных действий",
"Беженцы с территорий боевых действий",
"Сомнительная серия паспорта",
"Регион повышенного риска (Дальний Восток)",
"Высокая долговая нагрузка по КИ",
"Дополнительная оценка первого займа",
"Высокая доля просрочек в КИ за последние 2 года"
]}
{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/individual_order.js?v=1.09"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/check_images.js?v=1.05"></script>

    <script src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/changelogs.js"></script>

{/capture}


{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link href="design/{$settings->theme|escape}/assets/plugins/fancybox3/dist/jquery.fancybox.css" rel="stylesheet" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
    </style>
{/capture}

{function name='display_comments'}
    {if isset($comments[$block])}
        {foreach $comments[$block] as $comment}
            <div class="col-md-12 mb-2">
                <div class="bg-primary pt-1 pb-1 pl-4 pr-4 rounded" style="display:inline-block">
                    <div>
                        <strong>{$managers[$comment->manager_id]->name|escape}</strong>
                        <small><i>{$comment->created|date} {$comment->created|time}</i></small>
                    </div>
                    <div>{$comment->text|nl2br}</div>
                </div>
            </div>
        {/foreach}
    {/if}

{/function}

<div class="page-wrapper js-event-add-load" id="page_wrapper"  data-event="1" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">

        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-animation"></i> Заявка №{$order->order_id}
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item"><a href="individual_orders">Заявки</a></li>
                    <li class="breadcrumb-item active">Заявка №{$order->order_id}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active " data-event="5" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_order" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Заявка</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " data-event="6" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_scorings" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Скоринги</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " data-event="7" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_history" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Кредитная история</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " data-event="8" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#tab_comments" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                    <span class="hidden-xs-down">Комментарии</span>
                </a>
            </li>
            {if in_array('eventlogs', $manager->permissions)}
                <li class="nav-item">
                    <a class="nav-link " data-event="35" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-toggle="tab" href="#logs" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Логирование</span>
                    </a>
                </li>
            {/if}
        </ul>
        <div class="tab-content ">

            <div id="tab_order" class="tab-pane active" role="tabpanel">

                <div class="row" id="order_wrapper">
                    <div class="col-lg-12">
                        <div class="card card-outline-info">

                            <div class="card-body">

                                <div class="form-body">
                                    <div class="row pt-3 js-order-head" data-order="{$order->order_id}">

                                        {display_comments block='order'}

                                        <div class="col-md-9">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Номер заявки:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static"><strong>{$order->order_id}</strong></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Номер заявки 1C:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">{$order->id_1c}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Дата и время подачи заявки:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">{$individual_order->created|date} {$individual_order->created|time}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Статус оплаты:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">
                                                                {if $individual_order->paid}<span class="label label-success">Оплачен</span>
                                                                {else}<span class="label label-danger">Не оплачен</span>{/if}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Статус заявки CRM:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static mb-0">
                                                                {if $individual_order->status == 1}<span class="label label-rounded label-info">Новая</span>{/if}
                                                                {if $individual_order->status == 4}<span class="label label-rounded label-info">В работе</span>{/if}
                                                                {if $individual_order->status == 2}<span class="label label-rounded label-success">Одобрена</span>{/if}
                                                                {if $individual_order->status == 3}<span class="label label-rounded label-danger">Закрыта</span>{/if}
                                                                {if $individual_order->status == 9}<span class="label label-rounded label-success">Оплачена</span>{/if}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {if in_array($individual_order->status, [1, 4, 9])}
                                                    <div class="col-md-6 pr-0 pl-0 js-order-status-block" {if ($manager->id != $individual_order->manager_id && !in_array($manager->role, ['admin', 'developer', 'opr', 'ts_operator'])) || $individual_order->status == 1}style="display:none"{/if}>
                                                        <button type="button" class="btn btn-rounded btn-success js-approve-order " data-event="4" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" {if !$individual_order->paid}style="display:none"{/if}><i class="mdi mdi-checkbox-marked-circle-outline"></i>&nbsp;Одобрить</button>
                                                        <button type="button" class="btn btn-rounded btn-danger js-reject-order ml-2" data-event="4" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"><i class="mdi mdi-alert-circle-outline"></i>&nbsp;Закрыть</button>
                                                        {*if $order->status != 7}
                                                        <button type="button" class="btn btn-rounded btn-warning js-waiting-order ml-2" data-order="{$order->order_id}"><i class="mdi mdi-clock-fast"></i>&nbsp;Ожидание</button>
                                                        {/if*}
                                                    </div>

                                                    <div class="col-md-6 js-order-accept-block" {if $individual_order->status != 1}style="display:none"{/if}>
                                                        <button type="button" class="btn btn-lg btn-rounded btn-info js-accept-order " data-event="2" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}"> <i class="fas fa-hospital-symbol"></i>&nbsp;&nbsp; Принять</button>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="col-md-3">

                                            {if in_array($manager->role, ['admin', 'developer', 'chief_verificator', 'verificator', 'opr', 'ts_operator'])}
                                                <select class="form-control mb-2 js-order-manager {if in_array($manager->role, ['verificator', 'edit_verificator'])}js-need-comment{/if}" data-order="{$order->order_id}">
                                                    <option value="0"></option>
                                                    {foreach $managers as $m}
                                                        <option value="{$m->id}" {if $m->id == $individual_order->manager_id}selected{/if}>{$m->name|escape}</option>
                                                    {/foreach}
                                                </select>
                                            {else}

                                                <p>Менеджер: {$managers[$individual_order->manager_id]->name|escape}</p>
                                            {/if}

                                            <div class="pb-2 js-maratorium-block">
                                                {if $order->maratorium_valid}
                                                    <strong class="text-warning">Мораторий до {$order->maratorium_date|date} {$order->maratorium_date|time}</strong>
                                                    <br />
                                                    <small class="text-warning">{$maratoriums[$order->maratorium_id]->name}</small>
                                                {/if}
                                            </div>


                                            {if $user_balance->zaim_number != '' && $user_balance->zaim_number != 'Нет открытых договоров'}
                                                <div class="box bg-danger pt-2 pb-2">
                                                    <ul>
                                                        <li>{$user_balance->zaim_number}</li>
                                                        <li>Дата займа: {$user_balance->zaim_date|date}</li>
                                                        <li>Основной долг: {$user_balance->ostatok_od}</li>
                                                        <li>Проценты: {$user_balance->ostatok_percents}</li>
                                                        <li>Дата оплаты: {$user_balance->payment_date|date}</li>
                                                        <li>Мин платеж: {$user_balance->prolongation_amount*1}</li>
                                                    </ul>
                                                </div>
                                            {else}
                                                <div class="box bg-success pt-4 pb-4 pr-2 pl-2 text-center">
                                                    Нет открытых договоров
                                                </div>
                                                {if $order->pay_result}
                                                    <div>
                                                        Результат выдачи: {$order->pay_result}
                                                    </div>
                                                {/if}
                                            {/if}

                                            {if $order->percent != 1}
                                                <h3 class="d-block p-2 label label-primary text-center mt-2">Участвует в акции: {1 * $order->percent}%</h3>
                                            {/if}
                                        </div>
                                    </div>

                                    <div data-order="{$order->order_id}" class="" >
                                        <h3 class="box-title mt-5">
                                            <a href="javascript:void(0);" data-toggle="collapse" data-target="#sent_sms">
                                                <span>Смс сообщения:</span>
                                            </a>
                                            <a class="float-right btn btn-sm btn-outline-primary btn-rounded js-open-sms-modal">Отправить</a>
                                        </h3>
                                        <hr>
                                        <div class="row {if !$open_scorings}collapse{/if}" id="sent_sms">
                                            {if $sms_messages}
                                                <table class="table">
                                                    <tr>
                                                        <th>Дата</th>
                                                        <th>Сообщение</th>
                                                        <th>Статус</th>
                                                    </tr>
                                                    {foreach $sms_messages as $sm}
                                                        <tr>
                                                            <td>{$sm->created|date} {$sm->created|time}</td>
                                                            <td>{$sm->message}</td>
                                                            <td>{$sm->send_status}</td>
                                                        </tr>
                                                    {/foreach}
                                                </table>
                                            {else}
                                                <div class="col-12">
                                                    <h4>Нет отправленных сообщений</h4>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>

                                    <div data-order="{$order->order_id}" class="js-scorings-block {if $need_update_scorings}js-need-update{/if}" >

                                        <h3 class="box-title mt-5">
                                            <a href="javascript:void(0);" data-toggle="collapse" data-target="#scorings">
                                                <span>Скоринг тесты:</span>
                                            </a>
                                            {if $inactive_run_scorings}
                                                <a class="float-right btn btn-sm btn-outline-primary btn-rounded ">Выполняется</a>
                                            {else}
                                                <a class="float-right btn btn-sm btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="free">Запустить б/п проверки</a>
                                            {/if}

                                        </h3>
                                        <hr>
                                        <div class="row {if !$open_scorings}collapse{/if}" id="scorings">
                                            <div class="col-md-12">

                                                {$scor = ''}
                                                <table class="table">
                                                    <tr>
                                                        <th>Тип</th>
                                                        <th>Дата</th>
                                                        <th>Статус</th>
                                                        <th>Результат</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>

                                                    {foreach $scoring_types as $scoring_type}
                                                        {if $scoring_type->name != 'juicescore'}
                                                            {$scoring =  $scorings[$scoring_type->name]}

                                                            <tr>
                                                                <td>
                                                                    {if in_array($scoring_type->name, ['efrsb', 'fssp', 'axilink', 'scorista', 'juicescore', 'blacklist'])}
                                                                        <a href="#" data-toggle="collapse" data-target="#scoring_{$scoring->id}" data-type="{$scoring_type->name}" data-id="{$scoring->id}">{$scoring_type->title}</a>
                                                                    {else}
                                                                        {$scoring_type->title}
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    {if $scoring_type->name == 'scorista'}
                                                                        {if $scoring->status_name == 'completed'}
                                                                            {$scoring->end_date|date} {$scoring->end_date|time}
                                                                        {elseif $scoring->status_name == 'stopped'}
                                                                            {if $scoring->end_date}
                                                                                {$scoring->end_date|date} {$scoring->end_date|time}
                                                                            {else}
                                                                                {$scoring->start_date|date} {$scoring->start_date|time}
                                                                            {/if}
                                                                        {elseif $scoring->status_name == 'error'}
                                                                            {$scoring->start_date|date} {$scoring->start_date|time}
                                                                        {/if}
                                                                    {else}
                                                                        {if $scoring->created}
                                                                            {$scoring->created|date} {$scoring->created|time}
                                                                        {/if}
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    {if !$scoring}
                                                                        <span class="label label-warning">Не проводился</span>
                                                                    {elseif $scoring->status_name == 'new'}
                                                                        <span class="label label-info" title="Скоринг находится в очереди на выполнение">Ожидание</span>
                                                                    {elseif $scoring->status_name == 'process'}
                                                                        <span class="label label-primary">Выполняется</span>
                                                                    {elseif $scoring->status_name == 'error'}
                                                                        <span class="label label-danger">Ошибка</span>
                                                                    {elseif $scoring->status_name == 'completed'}
                                                                        <span class="label label-success">Завершен</span>
                                                                    {/if}

                                                                </td>
                                                                <td>
                                                                    {if $scoring->status_name == 'completed'}
                                                                        {if $scoring->success}<span class="label label-success">Пройден</span>
                                                                        {else}<span class="label label-danger">Не пройден</span>{/if}
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    {if $scoring->type == 'scorista'}
                                                                        {if $scoring->status_name == 'completed'}
                                                                            {if $scoring->success}
                                                                                <span class="label label-success">{$scoring->scorista_ball}</span>
                                                                            {else}
                                                                                <span class="label label-danger">{$scoring->scorista_ball}</span>
                                                                            {/if}

                                                                            {if $scoring->body->decision->decisionName == 'Отказ'}
                                                                                <span class="label label-danger">{$scoring->body->decision->decisionName}</span>
                                                                            {elseif $scoring->body->decision->decisionName}
                                                                                <span class="label label-info">{$scoring->body->decision->decisionName}</span>
                                                                            {/if}

                                                                            {if $order->loan_history|count == 0}
                                                                                {if $scoring->scorista_ball > 699}
                                                                                    <p class="p-0 m-0 text-success"><small>МОЖНО ЗВОНИТЬ ТОЛЬКО КЛИЕНТУ</small></p>
                                                                                {elseif $scoring->scorista_ball > 499 && $scoring->scorista_status == 'Одобрено'}
                                                                                    <p class="p-0 m-0 text-primary"><small>ЗВОНИМ КЛИЕНТУ И ОДНОМУ КОН. ЛИЦУ, либо 100 % работа , ищем телефон в анкете </small></p>
                                                                                {elseif $scoring->scorista_ball > 450}
                                                                                    <p class="p-0 m-0 text-warning"><small>ЗВОНИМ КЛИЕНТУ, БЕРЕМ  1-2 КОНТ. ЛИЦА, ПРОЗВАНИВАЕМ  РАБОТУ - НУЖНО УБЕДИТЬСЯ ЧТО КЛИЕНТ ТАМ РАБОТАЕТ, ЕСЛИ ВСЕ УСЛОВИЯ ВЫПОЛНЕНЫ ВЫДАЕМ</small></p>
                                                                                {/if}
                                                                            {/if}
                                                                        {else}
                                                                            <small>{$scoring->string_result}</small>
                                                                        {/if}
                                                                    {elseif $scoring->type == 'juicescore'}
                                                                        {*if $scoring->body}
                                                                            {if $scoring->success}
                                                                            <span class="label label-success">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                                            {else}
                                                                            <span class="label label-danger">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                                            {/if}
                                                                        {/if*}
                                                                    {else}
                                                                        <small>{$scoring->string_result|escape}</small>
                                                                    {/if}

                                                                </td>
                                                                <td>
                                                                    {if $scoring->status_name == 'new' || $scoring->status_name == 'process' || $scoring->status_name == 'import'}
                                                                        <a class="float-right btn btn-xs btn-outline-primary btn-rounded ">Выполняется</a>
                                                                    {else}
                                                                        <a class="float-right btn btn-xs btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="{$scoring_type->name}">Запустить</a>
                                                                    {/if}

                                                                </td>
                                                            </tr>

                                                            {if $scoring->type == 'blacklist'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">
                                                                        {if $scoring->body}
                                                                            <table class="table">
                                                                                {foreach $scoring->body as $key => $item}
                                                                                    <tr>
                                                                                        <td>{$item->created}</td>
                                                                                        <td>{$item->block}</td>
                                                                                        <td>{$item->text}</td>
                                                                                    </tr>
                                                                                {/foreach}
                                                                            </table>
                                                                        {else}
                                                                            Записей не найдено
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/if}

                                                            {if $scoring->type == 'fssp'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">
                                                                        {if $scoring->body->result[0]->result|count > 0}
                                                                            <ul>
                                                                                {foreach $scoring->body->result as $key => $value}
                                                                                    <li>
                                                                                        <ul>
                                                                                            {foreach $value->result as $kk =>  $item}
                                                                                                <li>
                                                                                                    <p>{$item->name}</p>
                                                                                                    <p>{$item->exe_production}</p>
                                                                                                    <p>{$item->details}</p>
                                                                                                    <p>{$item->subject}</p>
                                                                                                    <p>{$item->department}</p>
                                                                                                    <p>{$item->bailiff}</p>
                                                                                                    <p>{$item->ip_end}</p>
                                                                                                </li>
                                                                                            {/foreach}
                                                                                        </ul>
                                                                                    </li>
                                                                                {/foreach}
                                                                            </ul>
                                                                        {else}
                                                                            Производства не найдены
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/if}

                                                            {if !empty($scoring->body->additional->decisionSum)}
                                                                {$scor_amount = $scoring->body->additional->decisionSum}
                                                            {elseif !empty($scoring->body->sum)}
                                                                {$scor_amount = $scoring->body->sum}
                                                            {/if}
                                                            {if !empty($scoring->body->additional->decisionPeriod)}
                                                                {$scor_period = $scoring->body->additional->decisionPeriod}
                                                            {elseif !empty($scoring->body->limit_period)}
                                                                {$scor_period = $scoring->body->limit_period}
                                                            {/if}
                                                            {if !empty($scoring->body->additional->decisionMessage)}
                                                                {$scor_message = $scoring->body->additional->decisionMessage}
                                                            {elseif !empty($scoring->body->message)}
                                                                {$scor_message = $scoring->body->message}
                                                            {/if}
                                                            {if $scoring->type == 'scorista'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">
                                                                        {if $scoring->status_name == 'error'}
                                                                            <pre class="text-white">{$scoring->body|var_dump}</pre>
                                                                        {elseif $scoring->status_name == 'completed'}
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <p class="text-info m-0">Рекомендуемое решение: {$scoring->body->decision->decisionName}</p>
                                                                                    <p class="text-info m-0">Рекомендуемая сумма: {if $scoring->body->additional->decisionSum}{$scoring->body->additional->decisionSum}{else}Нет{/if}</p>
                                                                                    <p class="text-info">Рекомендуемый период: {if $scoring->body->additional->decisionPeriod}{$scoring->body->additional->decisionPeriod}{else}Нет{/if}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    {if $scoring->body->additional->decisionMessage}
                                                                                        <p class="box bg-primary m-0">{$scoring->body->additional->decisionMessage}</p>
                                                                                    {/if}
                                                                                </div>
                                                                            </div>

                                                                            <ul>
                                                                                {foreach $scoring->body as $key => $value}
                                                                                    <li>
                                                                                        {$key}
                                                                                        <ul>
                                                                                            {foreach $value as $kk =>  $item}
                                                                                                {if $item->description}
                                                                                                    <li>
                                                                                                        {if is_object($item->result)}
                                                                                                            {$kk}<br />
                                                                                                            {foreach $item->result as $k => $v}
                                                                                                                {$k}: {$v}<br />
                                                                                                            {/foreach}
                                                                                                        {else}
                                                                                                            {if $item->result > 0}
                                                                                                                <span
                                                                                                                {if in_array($item->description, $stopfactorsImportant)}
                                                                                                                    class="text-danger"
                                                                                                                {else}
                                                                                                                    class="text-info"
                                                                                                                {/if}
                                                                                                                >
                                                                                                            {/if}
                                                                                                            <strong>{$item->description}</strong>:
                                                                                                            {if is_null($item->result)}-
                                                                                                            {else}
                                                                                                                {$item->result}
                                                                                                            {/if}
                                                                                                            {if $item->result > 0}
                                                                                                                </span>
                                                                                                            {/if}
                                                                                                        {/if}
                                                                                                    </li>
                                                                                                {/if}
                                                                                            {/foreach}
                                                                                        </ul>
                                                                                    </li>
                                                                                {/foreach}
                                                                            </ul>
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/if}

                                                            {if $scoring->type == 'juicescore'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">

                                                                        <ul>
                                                                            {foreach $scoring->body as $key => $item}
                                                                                {if $key == 'Predictors'}
                                                                                    <li>
                                                                                        <p>{$key}</p>
                                                                                        <ul>
                                                                                            {foreach $item as $pkey => $pitem}
                                                                                                <li>{$pkey}: {$pitem}</li>
                                                                                            {/foreach}
                                                                                        </ul>
                                                                                    </li>
                                                                                {else}
                                                                                    <li>{$key}: {$item}</li>
                                                                                {/if}
                                                                            {/foreach}
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                            {/if}

                                                            {if $scoring->type == 'axilink'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">
                                                                        {if $scoring->status_name == 'error'}
                                                                            <pre class="text-white">{$scoring->string_result}</pre>
                                                                        {elseif $scoring->status_name == 'completed'}
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <p class="text-info m-0">Рекомендуемое решение: {if !empty($scoring->body->name)}{$scoring->body->name}{else}Нет{/if}</p>
                                                                                    <p class="text-info">Рекомендуемая сумма: {if !empty($scoring->body->sum)}{$scoring->body->sum}{else}Нет{/if}</p>
                                                                                    <p class="text-info">Рекомендуемый период: {if !empty($scoring->body->limit_period)}{$scoring->body->limit_period}{else}Нет{/if}</p>
                                                                                    <p class="text-info">Балл: {$scoring->scorista_ball}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    {if $scoring->body->message}
                                                                                        <p class="box bg-primary m-0">{$scoring->body->message}</p>
                                                                                    {/if}
                                                                                </div>
                                                                            </div>
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/if}

                                                            {if $scoring->type == 'efrsb'}
                                                                <tr class="collapse" id="scoring_{$scoring->id}">
                                                                    <td colspan="6">
                                                                        {if !empty($scoring->body)}
                                                                            {if is_array($scoring->body)}
                                                                                <span class="label label-danger">{$scoring->string_result}</span>
                                                                            {else}
                                                                                <a href="{$scoring->body}" target="_blank">{$scoring->body}</a>
                                                                            {/if}
                                                                        {else}
                                                                            Производства не найдены
                                                                        {/if}
                                                                    </td>
                                                                </tr>
                                                            {/if}


                                                        {/if}
                                                    {/foreach}
                                                </table>

                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">

                                        <!-- Сумма и период заявки -->
                                        <form action="{url}" class="col-md-6 js-order-item-form mb-3" id="period_amount_form">

                                            <input type="hidden" name="action" value="amount" />
                                            <input type="hidden" name="order_id" value="{$order->order_id}" />
                                            <input type="hidden" name="user_id" value="{$order->user_id}" />

                                            <h3 class="card-title">
                                                {if 1 || (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || (in_array($order->status, [1, 5, 6, 2, 7]) && (in_array($manager->role, ['admin', 'developer', 'chief_verificator','edit_verificator', 'opr', 'ts_operator'])))}
                                                    <a href="javascript:void(0);" class="js-edit-form " data-event="9" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                        <span>Сумма и срок заявки</span>
                                                    </a>
                                                {else}
                                                    <span>Сумма и срок заявки</span>
                                                {/if}
                                                <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="amount">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </a>
                                            </h3>
                                            <hr>

                                            <div class="row view-block {if $amount_error}hide{/if}">

                                                {display_comments block='amount'}

                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Сумма:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->amount} руб</strong>
                                                                {if $scor_amount}<label class="label label-primary" title="Скориста: рекомендуемая сумма">{$scor_amount} руб</label>{/if}
                                                                {if $scor_message}
                                                                    <br /><small><i class="text-primary">{$scor_message}</i></small>
                                                                {/if}
                                                            <div>
                                                                {if $order->max_amount}
                                                                    <span class="label label-info">Максимальная сумма: {$order->max_amount} руб</span>
                                                                {/if}
                                                                {if $order->loan_history|count > 0}
                                                                    {if $order->razgon}
                                                                        <span class="label label-danger">Разгон: Да</span>
                                                                    {else}
                                                                        <span class="label label-success">Разгон: Нет</span>
                                                                    {/if}
                                                                {/if}
                                                            </div>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Срок:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->period} {$order->period|plural:'день':'дней':'дня'}</strong>
                                                                {if $scor_period}<label class="label label-primary" title="Скориста: рекомендуемый срок">{$scor_period} {$scor_period|plural:'день':'дней':'дня'}</label>{/if}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Карта:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">

                                                                {foreach $card_list as $card}
                                                                    {if $card->CardId == $order->card_id}
                                                                        <strong>{$card->Pan} </strong> {$card->ExpDate}
                                                                    {/if}
                                                                {/foreach}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row edit-block {if !$amount_error}hide{/if}">
                                                {if $amount_error}
                                                    <div class="col-md-12">
                                                        <ul class="alert alert-danger">
                                                            {if in_array('empty_amount', (array)$amount_error)}<li>Укажите сумму заявки!</li>{/if}
                                                            {if in_array('empty_period', (array)$amount_error)}<li>Укажите срок заявки!</li>{/if}
                                                            {if in_array('empty_card', (array)$amount_error)}<li>Не выбрана карта!</li>{/if}
                                                        </ul>
                                                    </div>
                                                {/if}
                                                <div class="col-md-12">
                                                    <div class="form-group row {if in_array('empty_amount', (array)$amount_error)}has-danger{/if}">
                                                        <label class="col-6 control-label">Сумма:</label>
                                                        <div class="col-6">
                                                            <input type="text" name="amount" value="{$order->amount}" class="form-control js-order-summ" placeholder="Сумма заявки" required="true" />
                                                            {if in_array('empty_amount', (array)$amount_error)}<small class="form-control-feedback">Укажите сумму заявки!</small>{/if}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row form-group {if in_array('empty_period', (array)$amount_error)}has-danger{/if}">
                                                        <label class="col-6 control-label">Срок:</label>
                                                        <div class="col-6">
                                                            <input type="text" name="period" value="{$order->period}" class="form-control js-order-period" placeholder="Срок" required="true" />
                                                            {if in_array('empty_period', (array)$amount_error)}<small class="form-control-feedback">Укажите срок заявки!</small>{/if}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row form-group {if in_array('empty_period', (array)$amount_error)}has-danger{/if}">
                                                        <label class="col-6 control-label">Карта:</label>
                                                        <div class="col-6">
                                                            {if in_array('change_card', $manager->permissions)}
                                                                <select class="form-control  {if in_array($manager->role, ['verificator', 'edit_verificator'])}js-need-comment-card{/if} js-order-card" name="card_id">
                                                                    {foreach $card_list as $card}
                                                                        <option
                                                                                value="{$card->CardId}"
                                                                                {if !empty($card->Status) && $card->Status != 'A'}disabled="true"{/if}
                                                                                {if $card->CardId == $order->card_id}selected="true"{/if}>
                                                                            {$card->Pan} {$card->ExpDate}
                                                                        </option>
                                                                    {/foreach}
                                                                </select>
                                                            {else}
                                                                <input type="text" style="display:none" value="{$order->card_id}" name="card_id" />
                                                                <p class="form-control-static">
                                                                    {foreach $card_list as $card}
                                                                        {if $card->CardId == $order->card_id}
                                                                            <strong>{$card->Pan} </strong> {$card->ExpDate}
                                                                        {/if}
                                                                    {/foreach}
                                                                </p>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                        <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                    </div>
                                                </div>
                                            </div>

                                        </form>
                                        <!-- /Сумма и период заявки -->

                                        <form action="{url}" class="col-md-6 js-order-item-form mb-3" id="services_form">

                                            <input type="hidden" name="action" value="services" />
                                            <input type="hidden" name="order_id" value="{$order->order_id}" />
                                            <input type="hidden" name="user_id" value="{$order->user_id}" />

                                            <h3 class="card-title">
                                                {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                    <a href="javascript:void(0);" class="js-edit-form " data-event="10" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                        <span>Сервисные услуги</span>
                                                    </a>
                                                {else}
                                                    <span>Сервисные услуги</span>
                                                {/if}
                                                <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="services">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </a>
                                            </h3>
                                            <hr>

                                            <div class="row view-block {if $services_error}hide{/if}">

                                                {display_comments block='services'}

                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Смс информирование:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">
                                                                <strong>{if $order->service_sms}Да{else}Нет{/if}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Причина отказа:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">
                                                                <strong>{if $order->service_reason}Да{else}Нет{/if}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-6">Страхование:</label>
                                                        <div class="col-md-6">
                                                            <p class="form-control-static">
                                                                <strong>{if $order->service_insurance}Да{else}Нет{/if}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row edit-block {if !$services_error}hide{/if}">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" name="service_sms" id="service_sms" value="1" {if $order->service_sms}checked="true"{/if} />
                                                            <label class="custom-control-label" for="service_sms">Смс информирование</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" name="service_reason" id="service_reason" value="1" {if $order->service_reason}checked="true"{/if} />
                                                            <label class="custom-control-label" for="service_reason">Причина отказа</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" name="service_insurance" id="service_insurance" value="1" {if $order->service_insurance}checked="true"{/if} />
                                                            <label class="custom-control-label" for="service_insurance">Страхование</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                        <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                    </div>
                                                </div>
                                            </div>


                                        </form>
                                    </div>

                                    <!-- Персональные данные -->
                                    <form action="{url}" class="mb-3 js-order-item-form " id="personal_data_form">

                                        <input type="hidden" name="action" value="personal" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="card-title">
                                            {if (in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)) || in_array($manager->role, ['developer', 'ts_operator'])}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="11" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Персональная информация</span>
                                                </a>
                                            {else}
                                                <span>Персональная информация</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="personal">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $personal_error}hide{/if}">

                                            {display_comments block='personal'}

                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">ФИО:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->lastname} {$order->firstname} {$order->patronymic}</strong>
                                                            <a href="client/{$order->user_id}" target="_blank"><i class="far fa-user"></i></a>
                                                            {if $order->first_loan}<span class="label label-primary">Новая</span>
                                                            {elseif $order->have_close_credits}<span class="label label-success">ПК</span>
                                                            {else}<span class="label label-warning">Повтор</span>{/if}
                                                            {if $order->is_user_credit_doctor == 1}<span class="label label-danger">КД</span>{/if}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Телефон:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->phone_mobile}</strong>
                                                            <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call " data-phone="{$order->phone_mobile}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Почта:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->email}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Пол:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>
                                                                {if $order->gender == 'male'}Мужской
                                                                {elseif $order->gender == 'female'}Женский
                                                                {else}Не указан{/if}
                                                            </strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Дата и место рождения:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{if $order->birth}{$order->birth}, {/if}{$order->birth_place}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$personal_error}hide{/if}">
                                            {if $personal_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_lastname', (array)$personal_error)}<li>Укажите Фамилию!</li>{/if}
                                                        {if in_array('empty_firstname', (array)$personal_error)}<li>Укажите Имя!</li>{/if}
                                                        {if in_array('empty_patronymic', (array)$personal_error)}<li>Укажите Отчество!</li>{/if}
                                                        {if in_array('empty_gender', (array)$personal_error)}<li>Укажите Пол!</li>{/if}
                                                        {if in_array('empty_birth', (array)$personal_error)}<li>Укажите Дату рождения!</li>{/if}
                                                        {if in_array('empty_birth_place', (array)$personal_error)}<li>Укажите Место рождения!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_lastname', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Фамилия</label>
                                                    <input type="text" name="lastname" value="{$order->lastname}" class="form-control" placeholder="Фамилия" required="true" />
                                                    {if in_array('empty_lastname', (array)$personal_error)}<small class="form-control-feedback">Укажите Фамилию!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_firstname', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Имя</label>
                                                    <input type="text" name="firstname" value="{$order->firstname}" class="form-control" placeholder="Имя" required="true" />
                                                    {if in_array('empty_firstname', (array)$personal_error)}<small class="form-control-feedback">Укажите Имя!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_patronymic', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Отчество</label>
                                                    <input type="text" name="patronymic" value="{$order->patronymic}" class="form-control" placeholder="Отчество" required="true" />
                                                    {if in_array('empty_patronymic', (array)$personal_error)}<small class="form-control-feedback">Укажите Отчество!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group {if in_array('empty_gender', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Пол</label>
                                                    <select class="form-control custom-select" name="gender">
                                                        <option value="male" {if $order->gender == 'male'}selected="true"{/if}>Мужской</option>
                                                        <option value="female" {if $order->gender == 'female'}selected="true"{/if}>Женский</option>
                                                    </select>
                                                    {if in_array('empty_gender', (array)$personal_error)}<small class="form-control-feedback">Укажите Пол!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group {if in_array('empty_birth', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Дата рождения</label>
                                                    <input type="text" class="form-control" name="birth" value="{if $order->birth}{$order->birth}{/if}" placeholder="dd.mm.yyyy" required="true" />
                                                    {if in_array('empty_birth', (array)$personal_error)}<small class="form-control-feedback">Укажите Дату рождения!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_birth_place', (array)$personal_error)}has-danger{/if}">
                                                    <label class="control-label">Место рождения</label>
                                                    <input type="text" class="form-control" name="birth_place" value="{$order->birth_place|escape}" placeholder="" />
                                                    {if in_array('empty_birth_place', (array)$personal_error)}<small class="form-control-feedback">Укажите Место рождения!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group ">
                                                    <label class="control-label">Почта</label>
                                                    <input type="text" class="form-control" name="email" value="{$order->email|escape}" placeholder="" />
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Персональные данные -->

                                    <div class="row">
                                        <!-- Паспортные данные -->
                                        <form action="{url}" class="col-md-6 mb-3 js-order-item-form" id="passport_data_form">

                                            <input type="hidden" name="action" value="passport" />
                                            <input type="hidden" name="order_id" value="{$order->order_id}" />
                                            <input type="hidden" name="user_id" value="{$order->user_id}" />

                                            <h3 class="box-title">
                                                {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                    <a href="javascript:void(0);" class="js-edit-form "  data-event="12" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                        <span>Паспортные данные</span>
                                                    </a>
                                                {else}
                                                    <span>Паспортные данные</span>
                                                {/if}
                                                <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="passport">
                                                    <i class="mdi mdi-comment-text"></i>
                                                </a>
                                            </h3>
                                            <hr>

                                            <div class="row view-block {if $passport_error}hide{/if}">

                                                {display_comments block='passport'}

                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Паспорт:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static"><strong>{$order->passport_serial}{if $order->passport_date}, от {$order->passport_date}{/if}</strong></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Код подразделения:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static"><strong>{$order->subdivision_code}</strong></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Кем выдан:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static"><strong>{$order->passport_issued}</strong></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row edit-block {if !$passport_error}hide{/if}">
                                                {if $passport_error}
                                                    <div class="col-md-12">
                                                        <ul class="alert alert-danger">
                                                            {if $passport_error[$order->user_id]}
                                                                <li>
                                                                    Клиент с такими паспортными данными уже зарегистрирован!<br/>
                                                                    <a href="http://manager.boostra.ru/client/{$passport_error[$order->user_id]}">
                                                                        manager.boostra.ru/client/{$passport_error[$order->user_id]}
                                                                    </a>
                                                                </li>
                                                            {/if}
                                                            {if in_array('empty_passport_serial', (array)$passport_error)}<li>Укажите серию и номер паспорта!</li>{/if}
                                                            {if in_array('empty_passport_date', (array)$passport_error)}<li>Укажите дату выдачи паспорта!</li>{/if}
                                                            {if in_array('empty_subdivision_code', (array)$passport_error)}<li>Укажите код подразделения выдавшего паспорт!</li>{/if}
                                                            {if in_array('empty_passport_issued', (array)$passport_error)}<li>Укажите кем выдан паспорт!</li>{/if}
                                                        </ul>
                                                    </div>
                                                {/if}
                                                <div class="col-md-4">
                                                    <div class="form-group {if in_array('empty_passport_serial', (array)$passport_error)}has-danger{/if}">
                                                        <label class="control-label">Серия и номер паспорта</label>
                                                        <input type="text" class="form-control" name="passport_serial" value="{$order->passport_serial}" placeholder="" required="true" />
                                                        {if in_array('empty_passport_serial', (array)$passport_error)}<small class="form-control-feedback">Укажите серию и номер паспорта!</small>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group {if in_array('empty_passport_date', (array)$passport_error)}has-danger{/if}">
                                                        <label class="control-label">Дата выдачи</label>
                                                        <input type="text" class="form-control" name="passport_date" value="{if $order->passport_date}{$order->passport_date}{/if}" placeholder="" required="true" />
                                                        {if in_array('empty_passport_date', (array)$passport_error)}<small class="form-control-feedback">Укажите дату выдачи паспорта!</small>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group {if in_array('empty_subdivision_code', (array)$passport_error)}has-danger{/if}">
                                                        <label class="control-label">Код подразделения</label>
                                                        <input type="text" class="form-control" name="subdivision_code" value="{$order->subdivision_code}" placeholder="" required="true" />
                                                        {if in_array('empty_subdivision_code', (array)$passport_error)}<small class="form-control-feedback">Укажите код подразделения выдавшего паспорт!</small>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group {if in_array('empty_passport_issued', (array)$passport_error)}has-danger{/if}">
                                                        <label class="control-label">Кем выдан</label>
                                                        <input type="text" class="form-control" name="passport_issued" value="{$order->passport_issued|escape}" placeholder="" required="true" />
                                                        {if in_array('empty_passport_issued', (array)$passport_error)}<small class="form-control-feedback">Укажите кем выдан паспорт!</small>{/if}
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                        <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!-- /Паспортные данные -->

                                        <!-- Фото -->
                                        <div class="col-md-6">
                                            <form action="{url}" class="js-order-item-form mb-3 js-check-images" id="images_form" data-user="{$order->user_id}">

                                                <input type="hidden" name="action" value="images" />
                                                <input type="hidden" name="order_id" value="{$order->order_id}" />
                                                <input type="hidden" name="user_id" value="{$order->user_id}" />

                                                <h3 class="box-title">
                                                    <span>Фотографии</span>
                                                    <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="files">
                                                        <i class="mdi mdi-comment-text"></i>
                                                    </a>
                                                    <div class="spinner-border spinner-border-sm m-2 text-info float-right" role="status"></div>
                                                </h3>
                                                <hr>

                                                <div class="row view-block {if $socials_error}hide{/if}">

                                                    {display_comments block='files'}

                                                    <ul class="col-md-12 list-inline order-images-list ">
                                                        {foreach $files as $file}
                                                            {if $file->visible == 1}
                                                                {if $file->status == 0}
                                                                    {$item_class="border-warning"}
                                                                    {$ribbon_class="ribbon-warning"}
                                                                    {$ribbon_icon="fas fa-clock"}
                                                                {elseif $file->status == 1}
                                                                    {$item_class="border-info"}
                                                                    {$ribbon_class="ribbon-info"}
                                                                    {$ribbon_icon="fas fa-question"}
                                                                {elseif $file->status == 2}
                                                                    {$item_class="border-success border border-bg"}
                                                                    {$ribbon_class="ribbon-success"}
                                                                    {$ribbon_icon="fa fa-check-circle"}
                                                                {elseif $file->status == 3}
                                                                    {$item_class="border-danger border"}
                                                                    {$ribbon_class="ribbon-danger"}
                                                                    {$ribbon_icon="fas fa-times-circle"}
                                                                {/if}
                                                                <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}">
                                                                    {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{*}
                                                                    <a class="js-open-popup-image image-popup-fit-width "  data-event="19" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-fancybox="user_image" href="{$config->front_url}/files/users/{$file->name}">
                                                                        <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                                                    </a>
                                                                    <div class="label label-primary image-label" style="">
                                                                        {if $file->type == 'face1'}Профиль
                                                                        {elseif $file->type == 'face2'}Анфас
                                                                        {elseif $file->type == 'passport'}Документ
                                                                        {elseif $file->type == 'passport1'}Паспорт
                                                                        {elseif $file->type == 'passport2'}Прописка
                                                                        {elseif $file->type == 'passport3'}Брак
                                                                        {elseif $file->type == 'passport4'}Карта
                                                                        {elseif $file->type == 'selfi'}Селфи с паспортом
                                                                        {else}{$file->type}{/if}
                                                                    </div>
                                                                    <div class="label-exists js-label-exists">

                                                                        {*}
                                                                        <i class="text-success far fa-check-circle"></i>
                                                                        <i class="text-danger fas fa-ban"></i>
                                                                        {*}
                                                                    </div>

                                                                    {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                                        <div class="overlay-buttons">
                                                                            <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs  {if $file->status == 2}btn-success{else}btn-outline-success{/if}"  data-event="20" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                                                <i class="fas fa-check"></i>
                                                                            </a>
                                                                            <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs  {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}" data-event="21" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                                                <i class="fas fa-times"></i>
                                                                            </a>
                                                                        </div>
                                                                    {/if}
                                                                </li>
                                                            {/if}
                                                        {/foreach}
                                                    </ul>

                                                    <br>
                                                    <br>

                                                    <h4 class="box-title">
                                                        <span>Для внутреннего использования</span>
                                                        {if !$is_post}
                                                            <div class="spinner-border spinner-border-sm m-2 text-info float-right" role="status"></div>
                                                        {/if}
                                                    </h4>
                                                    <hr>
                                                    <ul class="col-md-12 list-inline order-images-list ">
                                                        {foreach $files as $file}
                                                            {if $file->visible == 0}
                                                                {if $file->status == 0}
                                                                    {$item_class="border-warning"}
                                                                    {$ribbon_class="ribbon-warning"}
                                                                    {$ribbon_icon="fas fa-clock"}
                                                                {elseif $file->status == 1}
                                                                    {$item_class="border-info"}
                                                                    {$ribbon_class="ribbon-info"}
                                                                    {$ribbon_icon="fas fa-question"}
                                                                {elseif $file->status == 2}
                                                                    {$item_class="border-success border border-bg"}
                                                                    {$ribbon_class="ribbon-success"}
                                                                    {$ribbon_icon="fa fa-check-circle"}
                                                                {elseif $file->status == 3}
                                                                    {$item_class="border-danger border"}
                                                                    {$ribbon_class="ribbon-danger"}
                                                                    {$ribbon_icon="fas fa-times-circle"}
                                                                {/if}
                                                                <li class="ribbon-wrapper border {$item_class} js-image-item" id="file_{$file->id}" data-id="{$file->id}">
                                                                    {*}<div class="ribbon ribbon-sm ribbon-corner {$ribbon_class}"><i class="{$ribbon_icon}"></i></div>{*}
                                                                    <a class="js-open-popup-image image-popup-fit-width "  data-event="19" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}" data-fancybox="user_image" href="{$config->front_url}/files/users/{$file->name}">
                                                                        <img src="{$file->name|resize:100:100}" loading="lazy" alt="" class="img-responsive js-image-thumb" style="max-width:100px;max-height:100px;" />
                                                                    </a>
                                                                    <div class="label label-primary image-label" style="">
                                                                        {if $file->type == 'face1'}Профиль
                                                                        {elseif $file->type == 'face2'}Анфас
                                                                        {elseif $file->type == 'passport'}Документ
                                                                        {elseif $file->type == 'passport1'}Паспорт
                                                                        {elseif $file->type == 'passport2'}Прописка
                                                                        {elseif $file->type == 'passport3'}Брак
                                                                        {elseif $file->type == 'passport4'}Карта
                                                                        {elseif $file->type == 'selfi'}Селфи
                                                                        {else}{$file->type}{/if}
                                                                    </div>
                                                                    <div class="label-exists js-label-exists">

                                                                        {*}
                                                                        <i class="text-success far fa-check-circle"></i>
                                                                        <i class="text-danger fas fa-ban"></i>
                                                                        {*}
                                                                    </div>

                                                                    {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                                        <div class="overlay-buttons">
                                                                            <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-accept btn btn-xs  {if $file->status == 2}btn-success{else}btn-outline-success{/if}"  data-event="20" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                                                <i class="fas fa-check"></i>
                                                                            </a>
                                                                            <a href="javascript:void(0);" data-id="{$file->id}" class="js-image-reject btn btn-xs  {if $file->status == 3}btn-danger{else}btn-outline-danger{/if}" data-event="21" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                                                <i class="fas fa-times"></i>
                                                                            </a>
                                                                        </div>
                                                                    {/if}
                                                                </li>
                                                            {/if}
                                                        {/foreach}
                                                    </ul>
                                                </div>

                                                <div class="row edit-block {if !$images_error}hide{/if}">
                                                    {foreach $files as $file}
                                                        <div class="col-md-4 col-lg-3 col-xlg-3">
                                                            <div class="card card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 col-lg-4 text-center">
                                                                        <a class="js-open-popup-image image-popup-fit-width" href="{$config->front_url}/files/users/{$file->name}">
                                                                            <img src="{$config->front_url}/files/users/{$file->name}" alt="" class="img-responsive" />
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-md-6 col-lg-8">
                                                                        <div class="form-group">
                                                                            <label class="control-label">Статус</label>
                                                                            <select id="status_{$file->id}" class="form-control custom-select js-file-status" name="status[{$file->id}]">
                                                                                <option value="0" {if $file->status == 0}selected="true"{/if}>Новый</option>
                                                                                <option value="1" {if $file->status == 1}selected="true"{/if}>На проверке</option>
                                                                                <option value="2" {if $file->status == 2}selected="true"{/if}>Принят</option>
                                                                                <option value="3" {if $file->status == 3}selected="true"{/if}>Отклонен</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/foreach}
                                                    <div class="col-md-12">
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                            <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                            <form method="POST" action="{$config->front_url}/ajax/upload_joxi.php" class="row">
                                                <br>


                                                <input type="text" id="file_url" name="file_url" class="form-control col-md-4" placeholder="Вставьте в поле ссылку Joxi" style="
                                        margin: 0px 10px 10px 20px;
                                    "><br><br>


                                                <select id="type" name="type" class="form-control col-md-3" placeholder="type...">
                                                    <option selected>выберите тип...</option>

                                                    <option value="face1">Профиль</option>
                                                    <option value="face2">Анфас</option>
                                                    <option value="passport">Документ</option>
                                                    <option value="passport1">Паспорт</option>
                                                    <option value="passport2">Прописка</option>
                                                    <option value="passport3">Брак</option>
                                                    <option value="passport4">Карта</option>
                                                    <option value="selfi">Селфи</option>
                                                </select><br><br>

                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="order_id" value="{$order->order_id}">
                                                <input type="hidden" name="user_id" value="{$order->user_id}">
                                                <input type="hidden" name="token" value="123ighdfgys_dfgd_1">

                                                <input type="submit" value="Добавить" class="btn btn-large btn-primary col-md-2" style="
                                        height: 36px;
                                        left: 10px;
                                        width: 226px;
                                    ">
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Прописка -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="reg_address_form">

                                        <input type="hidden" name="action" value="reg_address" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="13" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Адрес прописки</span>
                                                </a>
                                            {else}
                                                <span>Адрес прописки</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="regaddress">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $regaddress_error}hide{/if}">

                                            {display_comments block='regaddress'}

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {$order->Regindex},
                                                            {$order->Regregion} {$order->Regregion_shorttype},
                                                            {$order->Regcity_shorttype} {$order->Regcity},
                                                            {$order->Regstreet_shorttype} {$order->Regstreet},
                                                            д.{$order->Reghousing},
                                                            {if $order->Regbuilding}стр. {$order->Regbuilding},{/if}
                                                            {if $order->Regroom}кв.{$order->Regroom}{/if}
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$regaddress_error}hide{/if}">
                                            {if $regaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_regregion', (array)$regaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_regcity', (array)$regaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_regstreet', (array)$regaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_reghousing', (array)$regaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regregion', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" name="Regregion" value="{$order->Regregion}" placeholder="" required="true" />
                                                    {if in_array('empty_regregion', (array)$regaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regcity', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" name="Regcity" value="{$order->Regcity}" placeholder="" required="true" />
                                                    {if in_array('empty_regcity', (array)$regaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_regstreet', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" name="Regstreet" value="{$order->Regstreet}" placeholder=""  />
                                                    {if in_array('empty_regstreet', (array)$regaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_reghousing', (array)$regaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" name="Reghousing" value="{$order->Reghousing}" placeholder="" required="true" />
                                                    {if in_array('empty_reghousing', (array)$regaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Regbuilding" value="{$order->Regbuilding}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Квартира</label>
                                                    <input type="text" class="form-control" name="Regroom" value="{$order->Regroom}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Прописка -->

                                    <!-- Адрес проживания -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="faktaddress_form">

                                        <input type="hidden" name="action" value="fakt_address" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="14" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Адрес проживания</span>
                                                </a>
                                            {else}
                                                <span>Адрес проживания</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="faktaddress">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $faktaddress_error}hide{/if}">

                                            {display_comments block='faktaddress'}

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control-static">
                                                        <strong>
                                                            {$order->Faktindex},
                                                            {$order->Faktregion} {$order->Faktregion_shorttype},
                                                            {$order->Faktcity_shorttype} {$order->Faktcity},
                                                            {$order->Faktstreet_shorttype} {$order->Faktstreet},
                                                            д.{$order->Fakthousing},
                                                            {if $order->Faktbuilding}стр. {$order->Faktbuilding},{/if}
                                                            {if $order->Faktroom}кв.{$order->Faktroom}{/if}
                                                        </strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$faktaddress_error}hide{/if}">
                                            {if $faktaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_faktregion', (array)$faktaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_faktcity', (array)$faktaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_faktstreet', (array)$faktaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_fakthousing', (array)$faktaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktregion', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" name="Faktregion" value="{$order->Faktregion}" placeholder="" required="true" />
                                                    {if in_array('empty_faktregion', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktcity', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" name="Faktcity" value="{$order->Faktcity}" placeholder="" required="true" />
                                                    {if in_array('empty_faktcity', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_faktstreet', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" name="Faktstreet" value="{$order->Faktstreet}" placeholder="" />
                                                    {if in_array('empty_faktstreet', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_fakthousing', (array)$faktaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" name="Fakthousing" value="{$order->Fakthousing}" placeholder="" required="true" />
                                                    {if in_array('empty_fakthousing', (array)$faktaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Faktbuilding" value="{$order->Faktbuilding}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Квартира</label>
                                                    <input type="text" class="form-control" name="Faktroom" value="{$order->Faktroom}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Адрес проживания -->

                                    <!-- Контактные лица -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="contact_persons_form">

                                        <input type="hidden" name="action" value="contacts" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="15" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Контактные лица</span>
                                                </a>
                                            {else}
                                                <span>Контактные лица</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="contactpersons">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $contacts_error}hide{/if}">

                                            {display_comments block='contactpersons'}

                                            {foreach $contactpersons as $contactperson}
                                                <div class="col-md-12">
                                                    <div class="form-group row {if in_array('empty_fakthousing', (array)$contacts_error)}has-danger{/if}">
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>
                                                                    {$contactperson->name}
                                                                    ({$contactperson->relation})
                                                                    {$contactperson->phone}
                                                                </strong>
                                                                {if $contactperson->phone}
                                                                    <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call " data-phone="{$contactperson->phone}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                                {/if}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <i>{$contactperson->comment}</i>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>

                                        <div class="row edit-block {if !$contacts_error}hide{/if}">
                                            {if $contacts_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_contact_person_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица!</li>{/if}
                                                        {if in_array('empty_contact_person_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица!</li>{/if}
                                                        {if in_array('empty_contact_person_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо!</li>{/if}
                                                        {if in_array('empty_contact_person2_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 2!</li>{/if}
                                                        {if in_array('empty_contact_person2_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 2!</li>{/if}
                                                        {if in_array('empty_contact_person2_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 2!</li>{/if}
                                                        {if in_array('empty_contact_person3_name', (array)$contacts_error)}<li>Укажите ФИО контакного лица 3!</li>{/if}
                                                        {if in_array('empty_contact_person3_phone', (array)$contacts_error)}<li>Укажите тел. контакного лица 3!</li>{/if}
                                                        {if in_array('empty_contact_person3_relation', (array)$contacts_error)}<li>Укажите кем приходится контакное лицо 3!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}

                                            <div class="col-12" id="contactperson_edit_block">

                                                {foreach $contactpersons as $contactperson}
                                                    <div class="row">
                                                        <input type="hidden" name="contact_person_id[]" value="{$contactperson->id}" />
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_name', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">ФИО контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_name[]" value="{$contactperson->name}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_name', (array)$contacts_error)}<small class="form-control-feedback">Укажите ФИО контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_phone', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Тел. контакного лица</label>
                                                                <input type="text" class="form-control" name="contact_person_phone[]" value="{$contactperson->phone}" placeholder="" required="true" />
                                                                {if in_array('empty_contact_person_phone', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group {if in_array('empty_contact_person_relation', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Кем приходится</label>
                                                                <select class="form-control custom-select" name="contact_person_relation[]">
                                                                    <option value="" {if $contactperson->relation == ''}selected=""{/if}>Выберите значение</option>
                                                                    <option value="мать/отец" {if $contactperson->relation == 'мать/отец'}selected=""{/if}>мать/отец</option>
                                                                    <option value="муж/жена" {if $contactperson->relation == 'муж/жена'}selected=""{/if}>муж/жена</option>
                                                                    <option value="сын/дочь" {if $contactperson->relation == 'сын/дочь'}selected=""{/if}>сын/дочь</option>
                                                                    <option value="коллега" {if $contactperson->relation == 'коллега'}selected=""{/if}>коллега</option>
                                                                    <option value="друг/сосед" {if $contactperson->relation == 'друг/сосед'}selected=""{/if}>друг/сосед</option>
                                                                    <option value="иной родственник" {if $contactperson->relation == 'иной родственник'}selected=""{/if}>иной родственник</option>
                                                                </select>
                                                                {if in_array('empty_contact_person_relation', (array)$contacts_error)}<small class="form-control-feedback">Укажите кем приходится контакное лицо!</small>{/if}
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 mb-5">
                                                            <div class="form-group {if in_array('empty_contact_person_comment', (array)$contacts_error)}has-danger{/if}">
                                                                <label class="control-label">Комментарий</label>
                                                                <input type="text" class="form-control" name="contact_person_comment[]" value="{$contactperson->comment}" placeholder=""  />
                                                                {if in_array('empty_contact_person_comment', (array)$contacts_error)}<small class="form-control-feedback">Укажите тел. контакного лица!</small>{/if}
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/foreach}


                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                    <button type="submit" class="btn btn-rounded btn-outline-success js-add-contactperson float-right"><i class="fa fa-plus-circle"></i> Добавить</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="row js-contactperson-block" id="new_contactperson">
                                        <input type="hidden" name="contact_person_id[]" value="" />
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">ФИО контакного лица</label>
                                                <input type="text" class="form-control" name="contact_person_name[]" value="" placeholder="" required="true" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Тел. контакного лица</label>
                                                <input type="text" class="form-control" name="contact_person_phone[]" value="" placeholder="" required="true" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Кем приходится</label>
                                                <select class="form-control custom-select" name="contact_person_relation[]">
                                                    <option value="">Выберите значение</option>
                                                    <option value="мать/отец">мать/отец</option>
                                                    <option value="муж/жена">муж/жена</option>
                                                    <option value="сын/дочь">сын/дочь</option>
                                                    <option value="коллега">коллега</option>
                                                    <option value="друг/сосед">друг/сосед</option>
                                                    <option value="иной родственник">иной родственник</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-5">
                                            <div class="form-group">
                                                <label class="control-label">Комментарий</label>
                                                <div class="row">
                                                    <div class="col-10">
                                                        <input type="text" class="form-control" name="contact_person_comment[]" value="" placeholder=""  />
                                                    </div>
                                                    <div class="col-2 ">
                                                        <label class="control-label">&nbsp; </label>
                                                        <button class="btn btn-danger js-remove-contactperson"><i class="fas fas fa-times-circle"></i> Удалить</button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <!-- /Контактные лица -->


                                    <!-- Данные о работе -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="work_data_form">

                                        <input type="hidden" name="action" value="workdata" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="16" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Данные о работе</span>
                                                </a>
                                            {else}
                                                <span>Данные о работе</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="work">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $workdata_error}hide{/if}">

                                            {display_comments block='work'}

                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Сфера деятельности:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->work_scope}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            {if $order->profession}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Должность:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->profession}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->work_phone}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Рабочий телефон:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->work_phone}</strong>
                                                                <button class="btn waves-effect waves-light btn-xs btn-info js-mango-call " data-phone="{$order->work_phone}" data-order="{$order->order_id}" data-user="{$order->user_id}" title="Выполнить звонок"><i class="fas fa-phone-square"></i></button>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->workplace}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Название организации:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->workplace}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->workdirector_name}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">ФИО руководителя:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->workdirector_name}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="control-label col-md-4">Доход:</label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static">
                                                            <strong>{$order->income_base}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$workdata_error}hide{/if}">
                                            {if $workdata_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_work_scope', (array)$workdata_error)}<li>Укажите сферу деятельности!</li>{/if}
                                                        {if in_array('empty_income_base', (array)$workdata_error)}<li>Укажите доход!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                    <label class="control-label">Сфера деятельности</label>
                                                    <input type="text" class="form-control" name="work_scope" value="{$order->work_scope}" placeholder="" required="true" />
                                                    {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите сферу деятельности!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Должность</label>
                                                    <input type="text" class="form-control" name="profession" value="{$order->profession}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Рабочий телефон</label>
                                                    <input type="text" class="form-control" name="work_phone" value="{$order->work_phone}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Название организации</label>
                                                    <input type="text" class="form-control" name="workplace" value="{$order->workplace}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">ФИО руководителя</label>
                                                    <input type="text" class="form-control" name="workdirector_name" value="{$order->workdirector_name}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_income_base', (array)$workdata_error)}has-danger{/if}">
                                                    <label class="control-label">Доход</label>
                                                    <input type="text" class="form-control" name="income_base" value="{$order->income_base}" placeholder="" required="true" />
                                                    {if in_array('empty_income_base', (array)$workdata_error)}<small class="form-control-feedback">Укажите доход!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Данные о работе -->

                                    <!-- Рабочий адрес -->
                                    <form action="{url}" class="js-order-item-form mb-3" id="work_address_form">

                                        <input type="hidden" name="action" value="work_address" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="17" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Адрес Организации</span>
                                                </a>
                                            {else}
                                                <span>Адрес Организации</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="workaddress">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $workaddress_error}hide{/if}">

                                            {display_comments block='workaddress'}

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <p class="form-control-static">
                                                        {if $order->Workregion}
                                                            <strong>
                                                                {if $order->Workregion}{$order->Workregion},{/if}
                                                                {if $order->Workcity}{$order->Workcity},{/if}
                                                                {if $order->Workstreet}{$order->Workstreet},{/if}
                                                                {if $order->Workhousing}д.{$order->Workhousing},{/if}
                                                                {if $order->Workbuilding}стр. {$order->Workbuilding},{/if}
                                                                {if $order->Workroom}оф.{$order->Workroom}{/if}
                                                            </strong>
                                                        {elseif $order->work_address}
                                                            Адрес 1С: <strong>{$order->work_address}</strong>
                                                        {/if}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row edit-block {if !$workaddress_error}hide{/if}">
                                            {if $workaddress_error}
                                                <div class="col-md-12">
                                                    <ul class="alert alert-danger">
                                                        {if in_array('empty_workregion', (array)$workaddress_error)}<li>Укажите область!</li>{/if}
                                                        {if in_array('empty_workcity', (array)$workaddress_error)}<li>Укажите город!</li>{/if}
                                                        {if in_array('empty_workstreet', (array)$workaddress_error)}<li>Укажите улицу!</li>{/if}
                                                        {if in_array('empty_workhousing', (array)$workaddress_error)}<li>Укажите дом!</li>{/if}
                                                    </ul>
                                                </div>
                                            {/if}
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workregion', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Область</label>
                                                    <input type="text" class="form-control" name="Workregion" value="{$order->Workregion}" placeholder="" required="true" />
                                                    {if in_array('empty_workregion', (array)$workaddress_error)}<small class="form-control-feedback">Укажите область!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workcity', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Город</label>
                                                    <input type="text" class="form-control" name="Workcity" value="{$order->Workcity}" placeholder="" required="true" />
                                                    {if in_array('empty_workcity', (array)$workaddress_error)}<small class="form-control-feedback">Укажите город!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workstreet', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Улица</label>
                                                    <input type="text" class="form-control" name="Workstreet" value="{$order->Workstreet}" placeholder="" />
                                                    {if in_array('empty_workstreet', (array)$workaddress_error)}<small class="form-control-feedback">Укажите улицу!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group {if in_array('empty_workhousing', (array)$workaddress_error)}has-danger{/if}">
                                                    <label class="control-label">Дом</label>
                                                    <input type="text" class="form-control" name="Workhousing" value="{$order->Workhousing}" placeholder="" required="true" />
                                                    {if in_array('empty_workhousing', (array)$workaddress_error)}<small class="form-control-feedback">Укажите дом!</small>{/if}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Строение</label>
                                                    <input type="text" class="form-control" name="Workbuilding" value="{$order->Workbuilding}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="control-label">Офис</label>
                                                    <input type="text" class="form-control" name="Workroom" value="{$order->Workroom}" placeholder="" required="true" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /Рабочий адрес -->

                                    <!-- -->
                                    <form action="{url}" class="js-order-item-form mb-3" method="POST" id="socials_form">

                                        <input type="hidden" name="action" value="socials" />
                                        <input type="hidden" name="order_id" value="{$order->order_id}" />
                                        <input type="hidden" name="user_id" value="{$order->user_id}" />

                                        <h3 class="box-title">
                                            {if in_array($order->status, [1, 5, 6, 7]) && ($manager->id == $order->manager_id)}
                                                <a href="javascript:void(0);" class="js-edit-form "  data-event="18" data-manager="{$manager->id}" data-order="{$order->order_id}" data-user="{$order->user_id}">
                                                    <span>Ссылки на профили в соц. сетях</span>
                                                </a>
                                            {else}
                                                <span>Ссылки на профили в соц. сетях</span>
                                            {/if}
                                            <a href="javascript:void(0);" class="ml-3 js-open-comment-form" data-block="socials">
                                                <i class="mdi mdi-comment-text"></i>
                                            </a>
                                        </h3>
                                        <hr>

                                        <div class="row view-block {if $socials_error}hide{/if}">

                                            {display_comments block='socials'}

                                            {if $order->social_fb}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Facebook:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->social_fb}</strong>
                                                                <a href="{$order->social_fb}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->social_inst}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Instagram:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->social_inst}</strong>
                                                                <a href="{$order->social_inst}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->social_vk}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">В Контакте:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->social_vk}</strong>
                                                                <a href="{$order->social_vk}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            {if $order->social_ok}
                                                <div class="col-md-12">
                                                    <div class="form-group row">
                                                        <label class="control-label col-md-4">Одноклассники:</label>
                                                        <div class="col-md-8">
                                                            <p class="form-control-static">
                                                                <strong>{$order->social_ok}</strong>
                                                                <a href="{$order->social_ok}" target="_blank" title="Открыть соцсеть"><i class="fas fa-external-link-square-alt"></i></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="row edit-block {if !$socials_error}hide{/if}">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Facebook</label>
                                                    <input type="text" class="form-control" name="social_fb" value="{$order->social_fb}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Instagram</label>
                                                    <input type="text" class="form-control" name="social_inst" value="{$order->social_inst}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">В Контакте</label>
                                                    <input type="text" class="form-control" name="social_vk" value="{$order->social_vk}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Одноклассники</label>
                                                    <input type="text" class="form-control" name="social_ok" value="{$order->social_ok}" placeholder="" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                                    <button type="button" class="btn btn-inverse js-cancel-edit">Отмена</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- -->

                                    <!--
                                    <h3 class="box-title mt-5">UTM-метки</h3>
                                    <hr>
                                    -->



                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab_scorings" class="tab-pane" role="tabpanel">
                <div data-order="{$order->order_id}" class="js-scorings-block {if $need_update_scorings}js-need-update{/if}" >
                    <h3 class="box-title mt-5">
                        <span>Скоринг тесты</span>
                    </h3>
                    <hr>
                    <div class="row" >
                        <div class="col-md-12">
                            <table class="table">
                                <tr>
                                    <th>Тип</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Результат</th>
                                    <th></th>
                                    {*}<th></th>{*}
                                </tr>

                                {foreach $user_scorings as $scoring}

                                    <tr>
                                        <td>
                                            {if in_array($scoring->type->name, ['blacklist', 'axilink', 'fssp', 'scorista', 'juicescore'])}
                                                <a href="#" data-toggle="collapse" data-target="#tab_scoring_{$scoring->id}">{$scoring->type->title}</a>
                                            {else}
                                                {$scoring->type->title}
                                            {/if}
                                        </td>
                                        <td>
                                            {if $scoring->type->name == 'scorista'}
                                                {if $scoring->status_name == 'completed'}
                                                    {$scoring->end_date|date} {$scoring->end_date|time}
                                                {elseif $scoring->status_name == 'stopped'}
                                                    {if $scoring->end_date}
                                                        {$scoring->end_date|date} {$scoring->end_date|time}
                                                    {else}
                                                        {$scoring->start_date|date} {$scoring->start_date|time}
                                                    {/if}
                                                {elseif $scoring->status_name == 'error'}
                                                    {$scoring->start_date|date} {$scoring->start_date|time}
                                                {/if}
                                            {else}
                                                {if $scoring->created}
                                                    {$scoring->created|date} {$scoring->created|time}
                                                {/if}
                                            {/if}
                                        </td>
                                        <td>
                                            {if !$scoring}
                                                <span class="label label-warning">Не проводился</span>
                                            {elseif $scoring->status_name == 'new'}
                                                <span class="label label-info" title="Скоринг находится в очереди на выполнение">Ожидание</span>
                                            {elseif $scoring->status_name == 'import'}
                                                <span class="label label-info" title="Скоринг импортируется из 1C">Импорт</span>
                                            {elseif $scoring->status_name == 'process'}
                                                <span class="label label-primary">Выполняется</span>
                                            {elseif $scoring->status_name == 'error'}
                                                <span class="label label-danger">Ошибка</span>
                                            {elseif $scoring->status_name == 'completed'}
                                                <span class="label label-success">Завершен</span>
                                            {/if}

                                        </td>
                                        <td>
                                            {if $scoring->status_name == 'completed'}
                                                {if $scoring->success}<span class="label label-success">Пройден</span>
                                                {else}<span class="label label-danger">Не пройден</span>{/if}
                                            {/if}
                                        </td>
                                        <td>
                                            {if $scoring->type->name == 'scorista'}
                                                {if $scoring->status_name == 'completed'}
                                                    {if $scoring->success}
                                                        <span class="label label-success">{$scoring->scorista_ball}</span>
                                                    {else}
                                                        <span class="label label-danger">{$scoring->scorista_ball}</span>
                                                    {/if}

                                                    {if $scoring->body->decision->decisionName == 'Отказ'}
                                                        <span class="label label-danger">{$scoring->body->decision->decisionName}</span>
                                                    {elseif $scoring->body->decision->decisionName}
                                                        <span class="label label-info">{$scoring->body->decision->decisionName}</span>
                                                    {/if}

                                                    {*if $scoring->type->params['scorebal_nocall'] <= $scoring->scorista_ball}
                                                        <p class="p-0 m-0 text-success"><small>Можно не звонить клиенту</small></p>
                                                    {/if*}
                                                {else}
                                                    <small>{$scoring->string_result}</small>
                                                {/if}
                                            {elseif $scoring->type->name == 'juicescore'}
                                                {*if $scoring->body}
                                                    {if $scoring->success}
                                                    <span class="label label-success">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                    {else}
                                                    <span class="label label-danger">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                    {/if}
                                                {/if*}
                                            {else}
                                                <small>{$scoring->string_result|escape}</small>
                                            {/if}

                                        </td>
                                        {*}
                                        <td>
                                            {if $scoring->status_name == 'new' || $scoring->status_name == 'process'}
                                            <a class="float-right btn btn-xs btn-outline-primary btn-rounded ">Выполняется</a>
                                            {else}
                                            <a class="float-right btn btn-xs btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="{$scoring_type->name}">Запустить</a>
                                            {/if}

                                        </td>
                                        {*}
                                    </tr>

                                    {if $scoring->type == 'blacklist'}
                                        <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                            <td colspan="5">
                                                {if $scoring->body}
                                                    <table class="table">
                                                        {foreach $scoring->body as $key => $item}
                                                            <tr>
                                                                <td>{$item->created}</td>
                                                                <td>{$item->block}</td>
                                                                <td>{$item->text}</td>
                                                            </tr>
                                                        {/foreach}
                                                    </table>
                                                {else}
                                                    Записей не найдено
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}

                                    {if $scoring->type->name == 'fssp'}
                                        <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                            <td colspan="5">
                                                {if $scoring->body->result[0]->result|count > 0}
                                                    <ul>
                                                        {foreach $scoring->body->result as $key => $value}
                                                            <li>
                                                                <ul>
                                                                    {foreach $value->result as $kk =>  $item}
                                                                        <li>
                                                                            <p>{$item->name}</p>
                                                                            <p>{$item->exe_production}</p>
                                                                            <p>{$item->details}</p>
                                                                            <p>{$item->subject}</p>
                                                                            <p>{$item->department}</p>
                                                                            <p>{$item->bailiff}</p>
                                                                            <p>{$item->ip_end}</p>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {else}
                                                    Производства не найдены
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}


                                    {if $scoring->type->name == 'scorista'}
                                        <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                            <td colspan="6">
                                                {if $scoring->status_name == 'error'}
                                                    <pre class="text-white">{$scoring->body|var_dump}</pre>
                                                {elseif $scoring->status_name == 'completed'}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-info m-0">Рекомендуемое решение: {$scoring->body->decision->decisionName}</p>
                                                            <p class="text-info m-0">Рекомендуемая сумма: {if $scoring->body->additional->decisionSum}{$scoring->body->additional->decisionSum}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемый период: {if $scoring->body->additional->decisionPeriod}{$scoring->body->additional->decisionPeriod}{else}Нет{/if}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            {if $scoring->body->additional->decisionMessage}
                                                                <p class="box bg-primary m-0">{$scoring->body->additional->decisionMessage}</p>
                                                            {/if}
                                                        </div>
                                                    </div>

                                                    <ul>
                                                        {foreach $scoring->body as $key => $value}
                                                            <li>
                                                                {$key}
                                                                <ul>
                                                                    {foreach $value as $kk =>  $item}
                                                                        {if $item->description}
                                                                            <li>
                                                                                {if is_object($item->result)}
                                                                                    {$kk}<br />
                                                                                    {foreach $item->result as $k => $v}
                                                                                        {$k}: {$v}<br />
                                                                                    {/foreach}
                                                                                {else}
                                                                                    {if $item->result > 0}
                                                                                        <span
                                                                                        {if in_array($item->description, $stopfactorsImportant)}
                                                                                            class="text-danger"
                                                                                        {else}
                                                                                            class="text-info"
                                                                                        {/if}
                                                                                        >
                                                                                    {/if}
                                                                                    <strong>{$item->description}</strong>:
                                                                                    {if is_null($item->result)}-
                                                                                    {else}
                                                                                        {$item->result}
                                                                                    {/if}
                                                                                    {if $item->result > 0}
                                                                                        </span>
                                                                                    {/if}
                                                                                {/if}
                                                                            </li>
                                                                        {/if}
                                                                    {/foreach}
                                                                </ul>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}

                                    {if $scoring->type->name == 'juicescore'}
                                        <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                            <td colspan="6">

                                                <ul>
                                                    {foreach $scoring->body as $key => $item}
                                                        {if $key == 'Predictors'}
                                                            <li>
                                                                <p>{$key}</p>
                                                                <ul>
                                                                    {foreach $item as $pkey => $pitem}
                                                                        <li>{$pkey}: {$pitem}</li>
                                                                    {/foreach}
                                                                </ul>
                                                            </li>
                                                        {else}
                                                            <li>{$key}: {$item}</li>
                                                        {/if}
                                                    {/foreach}
                                                </ul>
                                            </td>
                                        </tr>
                                    {/if}

                                    {if $scoring->type->name == 'axilink'}
                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                            <td colspan="6">
                                                {if $scoring->status_name == 'error'}
                                                    <pre class="text-white">{$scoring->string_result}</pre>
                                                {elseif $scoring->status_name == 'completed'}
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="text-info m-0">Рекомендуемое решение: {if !empty($scoring->body->name)}{$scoring->body->name}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемая сумма: {if !empty($scoring->body->sum)}{$scoring->body->sum}{else}Нет{/if}</p>
                                                            <p class="text-info">Рекомендуемый период: {if !empty($scoring->body->limit_period)}{$scoring->body->limit_period}{else}Нет{/if}</p>
                                                            <p class="text-info">Балл: {$scoring->scorista_ball}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            {if $scoring->body->message}
                                                                <p class="box bg-primary m-0">{$scoring->body->message}</p>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/if}

                                {/foreach}
                            </table>

                        </div>
                    </div>
                </div>

            </div>

            <div id="tab_history" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-pills mt-4 mb-4">
                            <li class=" nav-item"> <a href="#navpills-orders" class="nav-link active" data-toggle="tab" aria-expanded="false">Заявки</a> </li>
                            <li class="nav-item"> <a href="#navpills-loans" class="nav-link" data-toggle="tab" aria-expanded="false">Кредиты</a> </li>
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
                            <div id="navpills-loans" class="tab-pane">
                                <div class="card">
                                    <div class="card-body">

                                        {if $order->user->loan_history}
                                            <table class="table">
                                                <tr>
                                                    <th>Договор</th>
                                                    <th>Дата</th>
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
                        </div>




                    </div>
                </div>
            </div>


            <div id="tab_comments" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {if $blacklist_comments}
                                    <h3>ЧС</h3>
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
                                    </table>
                                {/if}

                                {if !$comments && !$comments_1c && !$blacklist_comments}
                                    <h4>Нет комментариев</h4>
                                {/if}

                                {if $comments}
                                    <h4>Комментарии CRM</h4>
                                    <table class="table">
                                        <tr>
                                            <th>Дата</th>
                                            <th>Заявка</th>
                                            <th>Менеджер</th>
                                            <th>Блок</th>
                                            <th>Комментарий</th>
                                        </tr>
                                        {foreach $comments as $comment}
                                            <tr>
                                                <td>{$comment->created|date} {$comment->created|time}</td>
                                                <td>
                                                    <a href="order/{$comment->order_id}">{$comment->order_id}</a>
                                                </td>
                                                <td>{$managers[$comment->manager_id]->name|escape}</td>
                                                <td>{$comment_blocks[$comment->block]}</td>
                                                <td>{$comment->text|nl2br}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                {/if}

                                {if $comments_1c}
                                    <h3>Комментарии из 1С</h3>
                                    <table class="table">
                                        <tr>
                                            <th>Дата</th>
                                            <th>Блок</th>
                                            <th>Комментарий</th>
                                        </tr>
                                        {foreach $comments_1c as $comment}
                                            <tr>
                                                <td>{$comment->created|date} {$comment->created|time}</td>
                                                <td>{$comment->block|escape}</td>
                                                <td>{$comment->text|nl2br}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                {/if}

                                {if !$comments && !$comments_1c}
                                    <h4>Нет комментариев</h4>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {if in_array('eventlogs', $manager->permissions)}
                <div id="logs" class="tab-pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Логирование</h3>
                                    <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                                        <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                            <table class="jsgrid-table table table-striped table-hover">
                                                <tr class="jsgrid-header-row">
                                                    <th style="width: 80px; max-width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                                        Дата
                                                    </th>
                                                    <th style="width: 120px; max-width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'type_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'type_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                                        Тип операции
                                                    </th>
                                                    <th style="width: 100px;  max-width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                                        Менеджер
                                                    </th>
                                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'order_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'order_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                                        № заказа
                                                    </th>
                                                </tr>
                                                <tr class="jsgrid-filter-row" id="search_form">

                                                    <td style="width: 80px; max-width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                                        <input type="hidden" name="sort" value="{$sort}" />
                                                        <input type="text" name="date" value="{$search['date']}" class="form-control input-sm">
                                                    </td>
                                                    <td style="width: 120px;  max-width: 120px" class="jsgrid-cell">
                                                        <select name="type" class="form-control input-sm">
                                                            <option value=""></option>
                                                            {foreach $changelog_types as $t_key => $t_name}
                                                                <option value="{$t_key}" {if $t_key == $search['type']}selected="true"{/if}>{$t_name|escape}</option>
                                                            {/foreach}
                                                        </select>
                                                    </td>
                                                    <td style="width: 100px;  max-width: 100px" class="jsgrid-cell">
                                                        <select name="manager" class="form-control input-sm">
                                                            <option value=""></option>
                                                            {foreach $managers as $m}
                                                                <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                                                            {/foreach}
                                                        </select>
                                                    </td>
                                                    <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                                        <input type="text" name="order" value="{$search['order']}" class="form-control input-sm">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="jsgrid-grid-body">
                                            <table class="jsgrid-table table table-striped table-hover ">
                                                <tbody>
                                                {if $changelogs}
                                                    {foreach $changelogs as $changelog}
                                                        <tr class="jsgrid-row">
                                                            <td style="width: 80px; max-width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                                                <div class="button-toggle-wrapper">
                                                                    <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                                </div>
                                                                <span>{$changelog->created|date}</span>
                                                                {$changelog->created|time}
                                                            </td>
                                                            <td style="width: 120px; max-width: 120px;" class="jsgrid-cell">
                                                                {if $changelog_types[$changelog->type]}{$changelog_types[$changelog->type]}
                                                                {else}{$changelog->type|escape}{/if}
                                                            </td>
                                                            <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                                <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                                            </td>
                                                            <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                                                <a href="order/{$changelog->order_id}">{$changelog->order_id}</a>
                                                            </td>
                                                        </tr>
                                                        <tr class="order-details" id="changelog_{$changelog->id}" style="display:none">
                                                            <td colspan="4">
                                                                <div class="row">
                                                                    <table class="table">
                                                                        <tr>
                                                                            <th>Параметр</th>
                                                                            <th>Старое значение</th>
                                                                            <th>Новое значение</th>
                                                                        </tr>
                                                                        {foreach $changelog->old_values as $field => $old_value}
                                                                            <tr>
                                                                                <td>{$field}</td>
                                                                                <td>
                                                                                    {if $field == 'status' && is_numeric($old_value)}
                                                                                        {$order_statuses[$changelog->old_values[$field]]}
                                                                                    {else}
                                                                                        {$old_value|escape}
                                                                                    {/if}
                                                                                </td>
                                                                                <td>
                                                                                    {if $field == 'status'}
                                                                                        {$order_statuses[$changelog->new_values[$field]]}
                                                                                    {else}
                                                                                        {$changelog->new_values[$field]|escape}
                                                                                    {/if}
                                                                                </td>
                                                                            </tr>
                                                                        {/foreach}
                                                                    </table>

                                                                </div>
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                {/if}
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                                        </div>
                                        <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                                            Идет загрузка...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

        </div>
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->

    {include file='footer.tpl'}

</div>

<div id="modal_add_maratorium" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить мораторий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_maratorium" action="order/{$order->order_id}">

                    <div class="alert" style="display:none"></div>

                    <input type="hidden" name="action" value="add_maratorium" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Выберите мораторий:</label>
                        <select class="form-control" name="maratorium_id">
                            {foreach $maratoriums as $maratorium}
                                {$maratorium_period = $maratorium->period/86400}
                                <option value="{$maratorium->id}">{$maratorium->name} ({$maratorium_period} {$maratorium_period|plural:'день':'дня':'дней'})</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_comment" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить комментарий</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_comment" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                    <input type="hidden" name="block" value="" />
                    <input type="hidden" name="action" value="add_comment" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="name" class="control-label text-white">Комментарий:</label>
                        <textarea class="form-control" name="text"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loan_operations" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loan_operations_title">Операции по договору</h5>
                <button type="button" class="btn-close btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times text-white"></i>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<div id="modal_reject_reason" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отказать в выдаче кредита?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="reject_mko" aria-labelledby="home-tab">
                                <form class="js-reject-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="action" value="reject" />
                                    <input type="hidden" name="status" value="3" />
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину отказа:</label>
                                        <select name="reason_id" class="form-control">
                                            {foreach $reject_reasons as $reject_reason}
                                                <option value="{$reject_reason->id}">{$reject_reason->admin_name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отказать</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_waiting_reason" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Перевести заявку в ожидание?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-waiting-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="action" value="waiting" />
                                    <input type="hidden" name="status" value="3" />
                                    <div class="form-group">
                                        <label for="admin_name" class="control-label">Выберите причину ожидания:</label>
                                        <select name="reason_id" class="form-control">
                                            {foreach $waiting_reasons as $waiting_reason}
                                                <option value="{$waiting_reason->id}">{$waiting_reason->admin_name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, перевести</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_send_sms" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Отправить смс-сообщение?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">


                <div class="card">
                    <div class="card-body">

                        <div class="tab-content tabcontent-border p-3" id="myTabContent">
                            <div role="tabpanel" class="tab-pane fade active show" id="waiting_reason" aria-labelledby="home-tab">
                                <form class="js-sms-form">
                                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                                    <input type="hidden" name="action" value="send_sms" />
                                    <div class="form-group">
                                        <label for="name" class="control-label">Выберите шаблон сообщения:</label>
                                        <select name="template_id" class="form-control">
                                            {foreach $sms_templates as $sms_template}
                                                <option value="{$sms_template->id}" title="{$sms_template->template|escape}">{$sms_template->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="form-action clearfix">
                                        <button type="button" class="btn btn-danger btn-lg float-left waves-effect" data-dismiss="modal">Отменить</button>
                                        <button type="submit" class="btn btn-success btn-lg float-right waves-effect waves-light">Да, отправить</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_need_comment_card" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Укажите причину смены карты</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_need_comment_card" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="card_id" value="" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment" required=""></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modal_need_comment_manager" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Укажите причину смены менеджера</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_need_comment_manager" action="order/{$order->order_id}">

                    <input type="hidden" name="order_id" value="{$order->order_id}" />
                    <input type="hidden" name="user_id" value="{$order->user_id}" />
                    <input type="hidden" name="manager_id" value="" />
                    <input type="hidden" name="action" value="change_manager" />

                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="comment" class="control-label">Комментарий:</label>
                        <textarea class="form-control" id="comment" name="comment" required=""></textarea>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>