{$meta_title = 'Увеличение одобренной суммы' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
{/capture}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>

    <script src="design/{$settings->theme|escape}/js/apps/scorista_leadgid_settings.app.js"></script>

{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Увеличение одобренной суммы
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Увеличение одобренной суммы
                    </li>
                </ol>
            </div>
        </div>

        <ul class="mt-2 nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#settings-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="ti-home"></i></span>
                    <span class="hidden-xs-down">Настройка</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#check-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="mdi mdi-animation"></i></span>
                    <span class="hidden-xs-down">Проверка</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#logs-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="mdi mdi-animation"></i></span>
                    <span class="hidden-xs-down">Логирование</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#autoretry-tab" role="tab" aria-selected="true">
                    <span class="hidden-sm-up"><i class="mdi mdi-animation"></i></span>
                    <span class="hidden-xs-down">Настройки автоповторов</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <div id="settings-tab" class="tab-pane active" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h3>Список настроек</h3>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mt-auto mb-auto">
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input name="approve_amount_settings_enabled" type="checkbox" class="custom-control-input" id="approve_amount_settings_enabled" value="1" {if $approve_amount_settings_enabled}checked{/if} />
                                            <label class="custom-control-label" for="approve_amount_settings_enabled">
                                                <span class="text-white">Включить настройки из таблицы</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-6 align-self-center">
                                        <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                                            <i class="mdi mdi-plus-circle"></i> Добавить
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                        <table id="config-table" class="table display table-striped dataTable">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Лидген <span class="text-muted">(utm_source)</span></th>
                                                <th>Вебмастер <span class="text-muted">(utm_medium)</span></th>
                                                <th>Тип<span class="info text-warning">*</span></th>
                                                <th>Мин. Балл</th>
                                                <th>Добавляемая сумма</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody id="table-body">

                                            {foreach $amounts as $amount}
                                                <tr class="js-item">
                                                    <td>
                                                        <div class="js-visible-view js-text-id">
                                                            {$amount->id}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="js-visible-view js-text-utm_source">
                                                            {if $amount->utm_source == '*'}Любой <span class="text-muted">(*)</span>{else}{$amount->utm_source}{/if}
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <input type="text" class="form-control form-control-sm" name="utm_source" value="{$amount->utm_source|escape}" />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="js-visible-view js-text-utm_medium">
                                                            {if $amount->utm_medium == '*'}Любой <span class="text-muted">(*)</span>{else}{$amount->utm_medium}{/if}
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <input type="text" class="form-control form-control-sm" name="utm_medium" value="{$amount->utm_medium|escape}" />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="js-visible-view js-text-have_close_credits">
                                                            {if $amount->have_close_credits == 1}
                                                                ПК
                                                            {else}
                                                                НК
                                                            {/if}
                                                            <span class="text-muted">({$amount->have_close_credits})</span>
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <input type="text" class="form-control form-control-sm" name="have_close_credits" value="{$amount->have_close_credits|escape}" />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="js-visible-view js-text-min_ball">
                                                            {$amount->min_ball|escape}
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <input type="text" class="form-control form-control-sm" name="min_ball" value="{$amount->min_ball|escape}" />
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="js-visible-view js-text-amount">
                                                            {$amount->amount|escape}
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <input type="text" class="form-control form-control-sm" name="amount" value="{$amount->amount|escape}" />
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="js-visible-view">
                                                            <a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                            <a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                        </div>
                                                        <div class="js-visible-edit" style="display:none">
                                                            <a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                            <a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}

                                            </tbody>
                                        </table>
                                        <span class="info text-warning">* НК - 0, ПК - 1</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div id="check-tab" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3>Проверка настроек</h3>
                                <div class="row">
                                    <div class="col-xl-2 col-3 mt-auto">
                                        <span class="text-white">Лидген <span class="text-muted">(utm_source)</span></span>
                                        <input type="text" name="check-utm_source" value="" class="form-control input-sm">
                                    </div>
                                    <div class="col-xl-2 col-3 mt-auto">
                                        <span class="text-white">Вебмастер <span class="text-muted">(utm_medium)</span></span>
                                        <input type="text" name="check-utm_medium" value="" class="form-control input-sm">
                                    </div>
                                    <div class="col-xl-1 col-2 mt-auto">
                                        <span class="text-white">Тип</span>
                                        <select name="check-have_close_credits" class="form-control input-sm">
                                            <option value="0">НК</option>
                                            <option value="1">ПК</option>
                                        </select>
                                    </div>
                                    <div class="col-xl-1 col-2 mt-auto">
                                        <span class="text-white">Балл</span>
                                        <input type="text" name="check-scorista_ball" value="500" class="form-control input-sm">
                                    </div>
                                    <div class="col-xl-2 col-2 mt-auto">
                                        <span></span>
                                        <button class="btn hidden-sm-down btn-success js-check">
                                            Проверить
                                        </button>
                                    </div>
                                </div>
                                <div id="check-result" class="mt-3">
                                    Проверка ещё не проводилась
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="logs-tab" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3>Логи изменений настроек</h3>
                                <div id="logs-grid" class="jsgrid" style="position: relative; width: 100%;">
                                    <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                        <table class="jsgrid-table table table-striped table-hover">
                                            <tr class="jsgrid-header-row">
                                                <th style="width: 80px; max-width: 80px;" class="jsgrid-header-cell">
                                                    Дата
                                                </th>
                                                <th style="width: 100px; max-width: 120px;" class="jsgrid-header-cell">
                                                    Тип операции
                                                </th>
                                                <th style="width: 100px;  max-width: 100px;" class="jsgrid-header-cell">
                                                    Менеджер
                                                </th>
                                                <th style="width: 80px;" class="jsgrid-header-cell">
                                                    ID настройки
                                                </th>
                                                <th style="width: 100px;" class="jsgrid-header-cell">
                                                    Лидген
                                                </th>
                                                <th style="width: 100px;" class="jsgrid-header-cell">
                                                    Вебмастер
                                                </th>
                                            </tr>
                                            <tr class="jsgrid-filter-row" id="js-logs-search">
                                                <td style="width: 80px; max-width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                                    <input type="text" name="created" value="{$logs_filter['created']}" class="form-control input-sm">
                                                </td>
                                                <td style="width: 100px;  max-width: 100px" class="jsgrid-cell">
                                                    <select name="type" class="form-control input-sm">
                                                        <option value=""></option>
                                                        {foreach $changelog_types as $t_key => $t_name}
                                                            <option value="{$t_key}" {if $t_key == $logs_filter['created']}selected="true"{/if}>{$t_name|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td style="width: 100px;  max-width: 100px" class="jsgrid-cell">
                                                    <select name="manager_id" class="form-control input-sm">
                                                        <option value=""></option>
                                                        {foreach $managers as $m}
                                                            <option value="{$m->id}" {if $m->id == $logs_filter['manager_id']}selected="true"{/if}>{$m->name|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                                    <input type="text" name="setting_id" value="{$logs_filter['setting_id']}" class="form-control input-sm">
                                                </td>
                                                <td style="width: 100px; max-width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                                    <input type="text" name="utm_source" value="{$logs_filter['utm_source']}" class="form-control input-sm">
                                                </td>
                                                <td style="width: 100px; max-width: 100px;" class="jsgrid-cell jsgrid-align-right">
                                                    <input type="text" name="utm_medium" value="{$logs_filter['utm_medium']}" class="form-control input-sm">
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
                                                                <button class="js-open-changelog button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                            </div>
                                                            <span>{$changelog->created|date}</span>
                                                            {$changelog->created|time}
                                                        </td>
                                                        <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                            {if $changelog_types[$changelog->type]}{$changelog_types[$changelog->type]}
                                                            {else}{$changelog->type|escape}{/if}
                                                        </td>
                                                        <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                            <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                                        </td>
                                                        <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                                            {$changelog->setting_id}
                                                        </td>
                                                        <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                            {$changelog->utm_source}
                                                        </td>
                                                        <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                            {$changelog->utm_medium}
                                                        </td>
                                                    </tr>
                                                    <tr class="changelog-details" id="changelog_{$changelog->id}" style="display:none">
                                                        <td colspan="6">
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
                                                                                {$old_value|escape}
                                                                            </td>
                                                                            <td>
                                                                                {$changelog->new_values[$field]|escape}
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

            <div id="autoretry-tab" class="tab-pane" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3>Настройки автоповторов</h3>
                                {if !empty($autorety_settings_error) }
                                    <div class="row">
                                        <div class="col-12 mt-4 alert alert-danger">{$autorety_settings_error}</div>
                                    </div>
                                {/if}
                                <div class="row">
                                    <form action="/approve_amount_settings" method="POST">
                                        <div class="col-12 mt-4">
                                            <label class="text-white">Минимальный балл скористы для повышения суммы одобрения
                                                <input type="text" name="min_scorista_ball_for_autoretry" value="{$min_scorista_ball_for_autoretry}" class="form-control input-sm">
                                            </label>
                                        </div>
                                        <div class="col-12 mt-4">
                                            <label class="text-white">Минимальная сумма одобрения, если балл скористы по заявке превышает пороговое значение
                                                <input type="text" name="increased_order_amount_for_autoretry" value="{$increased_order_amount_for_autoretry}" class="form-control input-sm">
                                            </label>
                                        </div>
                                        <input type="hidden" name="action" value="save-autoretry-settings">
                                        <div class="col-12 mt-4">
                                            <button class="btn hidden-sm-down btn-success">
                                                Сохранить
                                            </button>
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

    {include file='footer.tpl'}

</div>

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить настройку</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="utm_source" class="control-label text-white">utm_source (Лидген)</label>
                        <input type="text" class="form-control" name="utm_source" id="utm_source" value="" />
                        <span class="info">Укажите "*" если хотите затронуть все лидгены которые ещё не указаны в таблице.</span>
                    </div>

                    <div class="form-group">
                        <label for="utm_medium" class="control-label text-white">utm_medium (Вебмастер)</label>
                        <input type="text" class="form-control" name="utm_medium" id="utm_medium" value="*" />
                        <span class="info">Укажите "*" если хотите затронуть всех вебмастеров в рамках лидгена.
                            <br>Это затронет только тех вебмастеров, которые ещё не указаны в таблице.</span>
                    </div>

                    <div class="form-group">
                        <label for="have_close_credits" class="control-label text-white">Тип</label>
                        <input type="text" class="form-control" name="have_close_credits" id="have_close_credits" value="" />
                        <span class="info">НК - 0, ПК - 1</span>
                    </div>

                    <div class="form-group">
                        <label for="min_ball" class="control-label text-white">Мин. Балл</label>
                        <input type="text" class="form-control" name="min_ball" id="min_ball" value="" />
                    </div>

                    <div class="form-group">
                        <label for="amount" class="control-label text-white">Добавляемая сумма</label>
                        <input type="text" class="form-control" name="amount" id="amount" value="" />
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