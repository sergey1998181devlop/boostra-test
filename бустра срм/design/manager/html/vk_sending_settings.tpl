{$meta_title = 'Рассылка ВК' scope=parent}

{capture name='page_styles'}
<link type="text/css" rel="stylesheet"
      href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"/>
<link type="text/css" rel="stylesheet"
      href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css"/>
<link type="text/css" rel="stylesheet"
      href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css"/>

<style>
    .long-cell {
        max-width: 200px;
        overflow: auto;
    }

    #messages-table::-webkit-scrollbar,
    .long-cell::-webkit-scrollbar {
        width: 12px; /* Ширина вертикального скроллбара */
        height: 12px; /* Высота горизонтального скроллбара */
    }

    #messages-table::-webkit-scrollbar-thumb,
    .long-cell::-webkit-scrollbar-thumb {
        background-color: #6F7277; /* Цвет бегунка */
        border-radius: 6px; /* Скругленные углы бегунка */
    }

    #messages-table::-webkit-scrollbar-thumb:hover,
    .long-cell::-webkit-scrollbar-thumb:hover {
        background-color: #878a8e; /* Цвет бегунка при наведении */
    }

    #messages-table::-webkit-scrollbar-track,
    .long-cell::-webkit-scrollbar-track {
        background-color: #383F48; /* Цвет трека (фон скроллбара) */
        border-radius: 6px; /* Скругленные углы трека */
    }

    #messages-table,
    .long-cell {
        scrollbar-color: #6F7277 #383F48; /* Цвет бегунка и трека */
        scrollbar-width: thin; /* Тонкий скроллбар */
    }

    .table-gray > .jsgrid-cell {
        vertical-align: middle;
    }
</style>

{/capture}

{capture name='page_scripts'}

<script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>

<script src="design/{$settings->theme|escape}/js/apps/vk_sending_settings.app.js"></script>

{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Настройка рассылки ВК
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Настройка рассылки ВК
                    </li>
                </ol>
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#messages-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Сообщения</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bot-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Настройки бота</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#statistics-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="mdi mdi-animation"></i></span>
                    <span class="hidden-xs-down">Статистика</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <div id="messages-tab" class="tab-pane active" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-12 col-md-8 col-lg-6">
                                        <h3>Настройка отправки сообщений</h3>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-6 align-self-center">
                                        <button class="btn float-left float-md-right btn-success js-open-add-modal">
                                            <i class="mdi mdi-plus-circle"></i> Добавить
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <table id="messages-table" class="table table-striped table-hover table-responsive text-nowrap">
                                            <tbody>

                                                <tr class="table-gray">
                                                    <td class="text-center jsgrid-cell">ID</td>
                                                    <td class="text-center jsgrid-cell">Время<br>отправки</td>
                                                    <td class="text-center jsgrid-cell">День<br>просрочки</td>
                                                    <td class="text-center jsgrid-cell">Возраст</td>
                                                    <td class="text-center jsgrid-cell">Пол</td>
                                                    <td class="text-center jsgrid-cell">Балл<br>скористы</td>
                                                    <td class="text-center jsgrid-cell">Решение<br>скористы</td>
                                                    <td class="text-center jsgrid-cell">Источник</td>
                                                    <td class="text-center jsgrid-cell">Организация</td>
                                                    <td class="text-center jsgrid-cell">Регион<br>регистрации</td>
                                                    <td class="text-center jsgrid-cell">Регион<br>проживания</td>
                                                    <td class="text-center jsgrid-cell">Сообщение</td>
                                                    <td class="text-center jsgrid-cell">Действие</td>
                                                </tr>

                                                {foreach $messages as $message}
                                                    <tr class="table-gray-light js-item" data-id="{$message->id}">
                                                        <td class="text-center jsgrid-cell">{$message->id}</td>
                                                        <td class="text-center jsgrid-cell">{$message->send_hour}:00</td>
                                                        <td class="text-center jsgrid-cell">С {$message->day_from} по {$message->day_to}</td>
                                                        <td class="text-center jsgrid-cell">С {$message->age_from} по {$message->age_to}</td>
                                                        <td class="text-center jsgrid-cell">
                                                            {if $message->gender == 'any'}Любой
                                                            {elseif $message->gender == 'male'}Муж.
                                                            {else}Жен.{/if}
                                                        </td>
                                                        <td class="text-center jsgrid-cell">C {$message->scorista_ball_from} по {$message->scorista_ball_to}</td>
                                                        <td class="text-center jsgrid-cell">
                                                            {if $message->scorista_decision == 'any'}Любое
                                                            {elseif $message->scorista_decision == 'approve'}Одобрение
                                                            {else}Отказ{/if}
                                                        </td>
                                                        <td class="text-center jsgrid-cell">
                                                            {if !empty($message->utm_source)}{$message->utm_source}
                                                            {else}Любой{/if}</td>
                                                        <td class="text-center jsgrid-cell">{$message->organization_name}</td>
                                                        <td class="text-center jsgrid-cell long-cell">
                                                            {if !empty($message->reg_region)}{$message->reg_region}
                                                            {else}Любой{/if}</td>
                                                        <td class="text-center jsgrid-cell long-cell">
                                                            {if !empty($message->fakt_region)}{$message->fakt_region}
                                                            {else}Любой{/if}</td>
                                                        <td class="text-center jsgrid-cell long-cell">{$message->message}</td>
                                                        <td class="text-center jsgrid-cell">
                                                            {if empty($message->enabled)}
                                                                <a href="#" class="text-muted js-toggle-item" title="Включить" data-enabled="0"><i class=" fas fa-toggle-off"></i></a>
                                                            {else}
                                                                <a href="#" class="text-white js-toggle-item" title="Выключить" data-enabled="1"><i class=" fas fa-toggle-on"></i></a>
                                                            {/if}
                                                            <a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                            <a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                        </td>
                                                    </tr>
                                                {/foreach}

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div id="bot-tab" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3>Настройки бота</h3>
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mt-auto mb-auto">
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input name="vk_bot_enabled" type="checkbox" class="custom-control-input" id="vk_bot_enabled" value="1" {if $vk_bot_enabled}checked{/if} />
                                            <label class="custom-control-label" for="vk_bot_enabled">
                                                <span class="text-white">Рассылки включены</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="statistics-tab" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3>Статистика</h3>
                                {if empty($statistic)}
                                    Пусто
                                {else}
                                    <p class="text-white"><strong>Подписано на бота:</strong> {$statistic['total_users']}</p>
                                    <table id="messages-table" class="table table-striped table-hover table-responsive text-nowrap">
                                        <tbody>

                                        <tr class="table-gray">
                                            <td class="text-center"><strong>Дата</strong></td>
                                            <td class="text-center"><strong>Настройка</strong></td>
                                            <td class="text-center">Создано</td>
                                            <td class="text-center">Отправлено</td>
                                            <td class="text-center">Перешло по ссылке</td>
                                        </tr>

                                        {foreach $statistic['messages'] as $date => $messages}
                                            {foreach $messages as $message}
                                                <tr class="table-gray-light">
                                                    {if !empty($message['rowspan'])}
                                                        <td class="text-center" rowspan="{$message['rowspan']}">{$date}</td>
                                                    {/if}
                                                    <td class="text-center">{$message['setting_id']}</td>
                                                    <td class="text-center">{$message['created_count']}</td>
                                                    <td class="text-center">{$message['sent_count']}</td>
                                                    <td class="text-center">{$message['link_clicked_count']}</td>
                                                </tr>
                                            {/foreach}
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {include file='footer.tpl'}

</div>

<div id="modal_editor" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Редактирование сообщения</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_editor">
                    <div class="alert" style="display:none"></div>

                    <input type="text" class="form-control" name="id"  value="" style="display: none" />

                    <div class="form-group">
                        <label for="send_hour" class="control-label text-white">Час для отправки</label>
                        <input type="text" class="form-control" name="send_hour" id="send_hour" value="" />
                        <span class="info">По локальному времени клиента (Например 14)</span>
                    </div>

                    <div class="form-group">
                        <label for="day_from" class="control-label text-white">С какого дня</label>
                        <input type="text" class="form-control" name="day_from" id="day_from" value="" />
                        <span class="info">С какого дня просрочки (Например -1)</span>
                    </div>

                    <div class="form-group">
                        <label for="day_to" class="control-label text-white">По какой день</label>
                        <input type="text" class="form-control" name="day_to" id="day_to" value="" />
                        <span class="info">По какой день просрочки (Например 0)</span>
                    </div>

                    <div class="form-group">
                        <label for="age_from" class="control-label text-white">С возраста</label>
                        <input type="text" class="form-control" name="age_from" id="age_from" value="" />
                        <span class="info">Минимальный возраст</span>
                    </div>

                    <div class="form-group">
                        <label for="age_to" class="control-label text-white">До возраста</label>
                        <input type="text" class="form-control" name="age_to" id="age_to" value="" />
                        <span class="info">Максимальный возраст</span>
                    </div>

                    <div class="form-group">
                        <label for="gender" class="control-label text-white">Пол</label>
                        <input type="text" class="form-control" name="gender" id="gender" value="" />
                        <span class="info">"any", "male" или "female"</span>
                    </div>

                    <div class="form-group">
                        <label for="scorista_ball_from" class="control-label text-white">С балла скористы</label>
                        <input type="text" class="form-control" name="scorista_ball_from" id="scorista_ball_from" value="" />
                        <span class="info">Мин.балл скористы</span>
                    </div>

                    <div class="form-group">
                        <label for="scorista_ball_to" class="control-label text-white">До балла скористы</label>
                        <input type="text" class="form-control" name="scorista_ball_to" id="scorista_ball_to" value="" />
                        <span class="info">Макс.балл скористы</span>
                    </div>

                    <div class="form-group">
                        <label for="scorista_decision" class="control-label text-white">Решение скористы</label>
                        <input type="text" class="form-control" name="scorista_decision" id="scorista_decision" value="" />
                        <span class="info">"any", "approve" или "decline"</span>
                    </div>

                    <div class="form-group">
                        <label for="utm_source" class="control-label text-white">Источник (utm_source)</label>
                        <input type="text" class="form-control" name="utm_source" id="utm_source" value="" />
                        <span class="info">Можно оставить пустым</span>
                    </div>

                    <div class="form-group">
                        <label for="organization_id" class="control-label text-white">Id организации</label>
                        <input type="text" class="form-control" name="organization_id" id="organization_id" value="" />
                        <span class="info">Можно оставить пустым. 6 - Аквариус, 11 - Финлаб</span>
                    </div>

                    <div class="form-group">
                        <label for="message" class="control-label text-white">Текст сообщения</label>
                        <input type="text" class="form-control" name="message" id="message" value="" />
                        <span class="info">$n - Имя, $p - Отчество, $l - Ссылка на оплату</span>
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