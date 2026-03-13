{$meta_title='Настройки операторов Vox' scope=parent}

{capture name='page_scripts'}
    <script>
        function toggleOperator(voxUserId, checkbox) {
            const enabled = checkbox.checked ? 1 : 0;
            $.ajax({
                url: '?module=VoxOperatorsSettingsView&action=toggle&vox_user_id=' + voxUserId + '&enabled=' + enabled,
                method: 'GET',
                success: function(resp) {
                    if (resp.status === 'success') {
                        alert.success('Настройка сохранена');
                    } else {
                        alert.error('Ошибка сохранения');
                        checkbox.checked = !checkbox.checked;
                    }
                },
                error: function() {
                    alert.error('Ошибка сохранения');
                    checkbox.checked = !checkbox.checked;
                }
            });
        }

        function setUserDepartment(voxUserId, select) {
            $.ajax({
                url: '?module=VoxOperatorsSettingsView',
                method: 'POST',
                data: {
                    action: 'set_user_department',
                    vox_user_id: voxUserId,
                    department_id: select.value
                },
                success: function(resp) {
                    if (resp.status === 'success') {
                        alert.success('Подразделение сохранено');
                    } else {
                        alert.error(resp.error || 'Ошибка сохранения');
                    }
                },
                error: function() {
                    alert.error('Ошибка сохранения');
                }
            });
        }

        function addDepartment() {
            var name = $('#new_department_name').val().trim();
            if (!name) {
                alert.error('Укажите название подразделения');
                return;
            }
            $.ajax({
                url: '?module=VoxOperatorsSettingsView',
                method: 'POST',
                data: { action: 'add_department', name: name },
                success: function(resp) {
                    if (resp.status === 'success') {
                        location.reload();
                    } else {
                        alert.error(resp.error || 'Ошибка');
                    }
                },
                error: function() { alert.error('Ошибка сохранения'); }
            });
        }

        function startEditDepartment(btn) {
            var $row = $(btn).closest('tr');
            $row.find('.js-dept-view').hide();
            $row.find('.js-dept-edit').show();
        }

        function cancelEditDepartment(btn) {
            var $row = $(btn).closest('tr');
            $row.find('.js-dept-edit').hide();
            $row.find('.js-dept-view').show();
        }

        function saveDepartment(btn) {
            var $row = $(btn).closest('tr');
            var id = $row.data('id');
            var name = $row.find('input[name="dept_name"]').val().trim();
            if (!name) {
                alert.error('Укажите название подразделения');
                return;
            }
            $.ajax({
                url: '?module=VoxOperatorsSettingsView',
                method: 'POST',
                data: { action: 'update_department', id: id, name: name },
                success: function(resp) {
                    if (resp.status === 'success') {
                        location.reload();
                    } else {
                        alert.error(resp.error || 'Ошибка');
                    }
                },
                error: function() { alert.error('Ошибка сохранения'); }
            });
        }

        function deleteDepartment(id, name) {
            if (!confirm('Удалить подразделение "' + name + '"? У операторов с этим подразделением оно будет сброшено.')) {
                return;
            }
            $.ajax({
                url: '?module=VoxOperatorsSettingsView',
                method: 'POST',
                data: { action: 'delete_department', id: id },
                success: function(resp) {
                    if (resp.status === 'success') {
                        location.reload();
                    } else {
                        alert.error(resp.error || 'Ошибка');
                    }
                },
                error: function() { alert.error('Ошибка удаления'); }
            });
        }
    </script>
{/capture}

<style>
    .operator-toggle {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
</style>

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

        {if $save_success}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Настройки сохранены
                <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        {/if}

        {* === Блок подразделений === *}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0 text-white">Подразделения операторов</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Управление подразделениями для группировки операторов. Название подразделения передаётся в TQM как Organizational Unit ID.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$departments item=dept}
                                        <tr data-id="{$dept->id}">
                                            <td>{$dept->id}</td>
                                            <td>
                                                <span class="js-dept-view">{$dept->name|escape}</span>
                                                <div class="js-dept-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="dept_name" value="{$dept->name|escape}">
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <span class="js-dept-view">
                                                    <a href="javascript:void(0)" class="text-info" onclick="startEditDepartment(this)" title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="text-danger ml-2" onclick="deleteDepartment({$dept->id}, '{$dept->name|escape:'javascript'}')" title="Удалить">
                                                        <i class="far fa-trash-alt"></i>
                                                    </a>
                                                </span>
                                                <span class="js-dept-edit" style="display:none">
                                                    <a href="javascript:void(0)" class="text-success" onclick="saveDepartment(this)" title="Сохранить">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="text-danger ml-2" onclick="cancelEditDepartment(this)" title="Отменить">
                                                        <i class="fas fa-times-circle"></i>
                                                    </a>
                                                </span>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td></td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" id="new_department_name" placeholder="Новое подразделение">
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-sm btn-success" onclick="addDepartment()">
                                                <i class="mdi mdi-plus-circle"></i> Добавить
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {* === Блок операторов === *}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0 text-white">Операторы Vox для отправки звонков на анализ в Тбанк</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Выберите операторов, звонки которых будут отправлены на анализ в Тбанк.
                            Только звонки включённых операторов будут отправлены на анализ.
                        </p>

                        <form class="form-inline mb-3" method="GET">
                            <input type="hidden" name="module" value="VoxOperatorsSettingsView">
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control" placeholder="Поиск по ФИО" value="{$search|escape}">
                            </div>
                            <button type="submit" class="btn btn-primary">Найти</button>
                            {if $search}
                                <a href="{url search=null page=null}" class="btn btn-secondary ml-2">Сбросить</a>
                            {/if}
                        </form>

                        {if $users|@count}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 60px;">Вкл.</th>
                                            <th>ID оператора</th>
                                            <th>ФИО</th>
                                            <th>Подразделение</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$users item=user}
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           class="operator-toggle"
                                                           {if $user->is_call_analysis}checked{/if}
                                                           onchange="toggleOperator({$user->vox_user_id}, this)">
                                                </td>
                                                <td>{$user->vox_user_id|escape}</td>
                                                <td>{$user->full_name|escape|default:'—'}</td>
                                                <td>
                                                    <select class="form-control form-control-sm"
                                                            onchange="setUserDepartment({$user->vox_user_id}, this)">
                                                        <option value="">— Не указано —</option>
                                                        {foreach from=$departments item=dept}
                                                            <option value="{$dept->id}"
                                                                {if $user->department_id == $dept->id}selected{/if}>
                                                                {$dept->name|escape}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            {if $total_pages > 1}
                                {assign var="current_page_num" value=$page}
                                {assign var="total_pages_num" value=$total_pages}
                                {assign var="items" value=$users}
                                {include file='html_blocks/pagination.tpl'}
                            {/if}
                        {else}
                            <div class="alert alert-warning">
                                Операторы не найдены. Запустите крон синхронизации для загрузки операторов из Voximplant.
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
