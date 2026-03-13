{$meta_title='Настройки очередей Vox' scope=parent}

{capture name='page_scripts'}
    <script>
        function toggleQueue(voxQueueId, checkbox) {
            const enabled = checkbox.checked ? 1 : 0;
            $.ajax({
                url: '?module=VoxQueuesSettingsView&action=toggle&vox_queue_id=' + voxQueueId + '&enabled=' + enabled,
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
    </script>
{/capture}

<style>
    .scenario-toggle {
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0 text-white">Очереди для отчёта статистики операторов Vox</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Выберите очереди, звонки которых будут включены в отчёт статистики операторов Vox.
                            Только звонки по включённым очередям будут синхронизироваться и отображаться в отчёте.
                        </p>

                        {if $queues|@count}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 60px;">Вкл.</th>
                                            <th>ID очереди</th>
                                            <th>Название</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$queues item=queue}
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           class="queue-toggle"
                                                           {if $queue->enabled_for_report}checked{/if}
                                                           onchange="toggleQueue({$queue->vox_queue_id}, this)">
                                                </td>
                                                <td>{$queue->vox_queue_id|escape}</td>
                                                <td>{$queue->title|escape|default:'—'}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-warning">
                                Очереди не найдены. Запустите крон синхронизации для загрузки очередей из Voximplant.
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
