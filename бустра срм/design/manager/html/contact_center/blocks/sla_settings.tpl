{* Настройки SLA *}
<div class="sla-settings">
    <h4 class="mb-3">
        <i class="fas fa-clock"></i>
        Настройки SLA (Service Level Agreement)
    </h4>

    <form id="slaSettingsForm" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="save_sla_settings">

        {* Включение SLA *}
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="sla_enabled" name="sla_enabled" 
                       {if $sla_settings.sla_enabled}checked{/if}>
                <label class="custom-control-label" for="sla_enabled">
                    <strong>Включить SLA</strong>
                </label>
            </div>
            <small class="form-text text-muted">
                При включении SLA тикеты будут автоматически эскалироваться при превышении времени обработки
            </small>
        </div>

        <hr>

        {* Настройки времени SLA *}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="timeout_level_1">
                        <strong>SLA Уровень 1 (часы)</strong>
                    </label>
                    <input type="number" class="form-control" id="timeout_level_1" name="timeout_level_1" 
                           value="{$sla_settings.timeout_level_1}" min="1" max="24" required>
                    <small class="form-text text-muted">
                        Время до эскалации на старшего специалиста
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="timeout_level_2">
                        <strong>SLA Уровень 2 (часы)</strong>
                    </label>
                    <input type="number" class="form-control" id="timeout_level_2" name="timeout_level_2" 
                           value="{$sla_settings.timeout_level_2}" min="1" max="48" required>
                    <small class="form-text text-muted">
                        Время до эскалации на руководителя
                    </small>
                </div>
            </div>
        </div>

        <hr>

        {* Настройки менеджеров эскалации *}
        <h5 class="mb-3">Менеджеры для эскалации</h5>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Внимание:</strong> Для эскалации доступны только менеджеры, имеющие компетенции 
            в работе с тикетами "Допы и прочее" или "Взыскание".
        </div>

        {* Таблица менеджеров с SLA настройками *}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Настройка SLA уровней для менеджеров</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="slaManagersTable">
                        <thead>
                            <tr>
                                <th>Менеджер</th>
                                <th>Тип тикетов</th>
                                <th>Уровень компетенции</th>
                                <th>SLA уровень</th>
                            </tr>
                        </thead>
                        <tbody>
                            {* Менеджеры допов *}
                            {foreach $sla_managers.additional_services as $manager}
                                <tr data-manager-id="{$manager->id}" data-type="additional_services">
                                    <td>{$manager->name}</td>
                                    <td><span class="badge badge-primary">Допы и прочее</span></td>
                                    <td><span class="badge badge-{$manager->competency_level_color}">{$manager->competency_level_name}</span></td>
                                    <td>
                                        <select class="form-control form-control-sm sla-level-select" 
                                                data-manager-id="{$manager->id}" 
                                                data-type="additional_services">
                                            <option value="">Нет SLA</option>
                                            <option value="1" {if $manager->sla_level == 1}selected{/if}>Уровень 1 (Старший специалист)</option>
                                            <option value="2" {if $manager->sla_level == 2}selected{/if}>Уровень 2 (Руководитель)</option>
                                        </select>
                                    </td>
                                </tr>
                            {/foreach}
                            
                            {* Менеджеры взыскания *}
                            {foreach $sla_managers.collection as $manager}
                                <tr data-manager-id="{$manager->id}" data-type="collection">
                                    <td>{$manager->name}</td>
                                    <td><span class="badge badge-warning">Взыскание</span></td>
                                    <td><span class="badge badge-{$manager->competency_level_color}">{$manager->competency_level_name}</span></td>
                                    <td>
                                        <select class="form-control form-control-sm sla-level-select" 
                                                data-manager-id="{$manager->id}" 
                                                data-type="collection">
                                            <option value="">Нет SLA</option>
                                            <option value="1" {if $manager->sla_level == 1}selected{/if}>Уровень 1 (Старший специалист)</option>
                                            <option value="2" {if $manager->sla_level == 2}selected{/if}>Уровень 2 (Руководитель)</option>
                                        </select>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="saveSlaManagers">
                        <i class="fas fa-save"></i> Сохранить SLA настройки
                    </button>
                </div>
            </div>
        </div>

        <hr>

        {* Кнопки сохранения *}
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить настройки SLA
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetSLAForm()">
                <i class="fas fa-undo"></i> Сбросить
            </button>
        </div>
    </form>
</div>
