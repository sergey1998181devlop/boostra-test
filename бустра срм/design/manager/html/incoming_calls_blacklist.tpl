{$meta_title = 'Черный список входящих звонков' scope=parent}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script src="design/{$settings->theme|escape}/js/apps/incoming_calls_blacklist.app.js?v=1.0"></script>
{/capture}

{capture name='page_styles'}
    <style>
        .onoffswitch {
            display: inline-block!important;
            vertical-align: top!important;
            width: 60px!important;
            text-align: left;
        }
        .onoffswitch-switch {
            right: 38px!important;
            border-width: 1px!important;
        }
        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px!important;
        }
        .onoffswitch-label {
            margin-bottom: 0!important;
            border-width: 1px!important;
        }
        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height: 18px!important;
            line-height: 18px!important;
        }
        .onoffswitch-switch {
            width: 20px!important;
            margin: 1px!important;
        }
        .onoffswitch-inner::before {
            content: 'ДА'!important;
            padding-left: 10px!important;
            font-size: 10px!important;
        }
        .onoffswitch-inner::after {
            content: 'НЕТ'!important;
            padding-right: 6px!important;
            font-size: 10px!important;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Черный список входящих звонков</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Черный список входящих звонков</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить номер
                </button>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form class="form-inline mb-3" method="GET">
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control" placeholder="Поиск по номеру телефона" value="{$search|escape}">
                            </div>
                            <button type="submit" class="btn btn-primary">Найти</button>
                            {if $search}
                                <a href="{url search=null}" class="btn btn-secondary ml-2">Сбросить</a>
                            {/if}
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Номер телефона</th>
                                        <th>ФИО клиента</th>
                                        <th>Причина</th>
                                        <th>Дата добавления</th>
                                        <th>Последний звонок</th>
                                        <th>Кто добавил</th>
                                        <th>Номер в ЧС?</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $blacklist as $item}
                                    <tr>
                                        <td>{$item->phone_number}</td>
                                        <td>
                                            {if $item->user_id}
                                                <a href="client/{$item->user_id}" target="_blank">
                                                    {$item->lastname} {$item->firstname} {$item->patronymic}
                                                </a>
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                        <td>{$item->reason}</td>
                                        <td>{$item->created_at|date} {$item->created_at|time}</td>
                                        <td>{if $item->last_call_date}{$item->last_call_date|date} {$item->last_call_date|time}{/if}</td>
                                        <td>{$managers[$item->created_by]->name|escape}</td>
                                        <td>
                                            <div class="onoffswitch">
                                                <input type="checkbox"
                                                       class="onoffswitch-checkbox js-toggle-status"
                                                       id="switch_{$item->id}"
                                                       data-id="{$item->id}"
                                                       {if $item->is_active}checked{/if}>
                                                <label class="onoffswitch-label" for="switch_{$item->id}">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-xs js-delete-item" data-id="{$item->id}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                        
                        {include file='html_blocks/pagination.tpl'}
                        
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Модальное окно добавления -->
<div id="add-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Добавить номер в черный список</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-form">
                    <input type="hidden" name="manager_id" value="{$manager_id}">
                    <div class="form-group">
                        <label for="phone" class="control-label">Номер телефона:</label>
                        <input type="text" class="form-control" name="phone" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="reason_select" class="control-label">Причина:</label>
                        <select class="form-control" name="reason_select" id="reason_select">
                            <option value="">Выберите причину</option>
                            <option value="1">Грубое поведение, более 3раз (оскорбления, угрозы)</option>
                            <option value="2">Спам/злоупотребление, более 3раз (звонки автоматической системой, молчат в трубку)</option>
                            <option value="3">Неадекватное состояние, более 3раз (алкоголь, наркотики)</option>
                            <option value="4">Бессмысленные или фейковые обращения, более 3раз (троллинг)</option>
                            <option value="5">Иное</option>
                        </select>
                    </div>
                    <div class="form-group" id="custom_reason_group" style="display:none;">
                        <label for="reason" class="control-label">Укажите причину:</label>
                        <textarea class="form-control" name="reason" id="reason"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" id="add-item">Добавить</button>
            </div>
        </div>
    </div>
</div> 