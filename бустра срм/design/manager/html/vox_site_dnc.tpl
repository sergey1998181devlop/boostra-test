{$meta_title='Vox DNC по сайтам' scope=parent}

{capture name='page_scripts'}
    <script>
        window.VoxSiteDncConfig = {
            sites: {$sites_json nofilter},
            organizations: {$organizations_json nofilter}
        };
    </script>
    <script src="design/{$settings->theme|escape}/js/apps/vox_site_dnc.app.js?v=1.0"></script>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">{$meta_title}</h4>
            </div>
            <div class="col-md-7 align-self-center text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?">Главная</a></li>
                        <li class="breadcrumb-item"><a href="?module=SettingsView">Настройки</a></li>
                        <li class="breadcrumb-item active">{$meta_title}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">Доступы и DNC-лист по сайту (отключение звонков робота)</h4>
                        <button type="button" class="btn btn-light btn-sm js-vox-site-dnc-add">Добавить</button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Настройки Vox по паре (сайт, организация): доступы и ID DNC-листа для отключения исходящих
                            звонков робота.
                        </p>

                        <div class="mb-3">
                            <label class="mr-2">Фильтр по сайту:</label>
                            <select class="form-control form-control-sm d-inline-block w-auto js-vox-site-dnc-filter-site">
                                <option value="">— все —</option>
                                {foreach from=$sites item=site}
                                    <option value="{$site->site_id|escape}">{$site->title|escape|default:$site->domain}
                                        (site_id: {$site->site_id})
                                    </option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="voxSiteDncTable">
                                <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Сайт</th>
                                    <th>Организация</th>
                                    <th>Vox domain</th>
                                    <th>Vox token</th>
                                    <th>API URL</th>
                                    <th>DNC list ID</th>
                                    <th>Активен</th>
                                    <th>Комментарий</th>
                                    <th style="width: 120px;">Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="js-loading-row">
                                    <td colspan="10" class="text-center text-muted">Загрузка...</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления/редактирования -->
<div class="modal fade" id="voxSiteDncModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title js-modal-title">Добавить запись</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="voxSiteDncForm">
                    <input type="hidden" name="id" id="voxSiteDncId" value="">
                    <div class="form-group">
                        <label for="voxSiteDncSiteId">Сайт *</label>
                        <select class="form-control" name="site_id" id="voxSiteDncSiteId" required>
                            <option value="">— выбрать —</option>
                            {foreach from=$sites item=site}
                                <option value="{$site->site_id|escape}">{$site->title|escape|default:$site->domain}
                                    (site_id: {$site->site_id})
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncOrganizationId">Организация *</label>
                        <select class="form-control" name="organization_id" id="voxSiteDncOrganizationId" required>
                            <option value="">— выбрать —</option>
                            {foreach from=$organizations item=org}
                                <option value="{$org->id|escape}">{$org->short_name|escape|default:$org->name}
                                    (id: {$org->id})
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncDomain">Vox domain</label>
                        <input type="text" class="form-control" name="vox_domain" id="voxSiteDncDomain" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncToken">Vox token</label>
                        <input type="password" class="form-control" name="vox_token" id="voxSiteDncToken" placeholder=""
                               autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncApiUrl">API URL</label>
                        <input type="text" class="form-control" name="api_url" id="voxSiteDncApiUrl" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncDncListId">Outgoing calls DNC list ID</label>
                        <input type="number" class="form-control" name="outgoing_calls_dnc_list_id"
                               id="voxSiteDncDncListId" placeholder="">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="is_active" id="voxSiteDncIsActive"
                                   value="1" checked>
                            <label class="custom-control-label" for="voxSiteDncIsActive">Активен</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="voxSiteDncComment">Комментарий</label>
                        <input type="text" class="form-control" name="comment" id="voxSiteDncComment" placeholder="">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary js-vox-site-dnc-save">Сохранить</button>
            </div>
        </div>
    </div>
</div>
