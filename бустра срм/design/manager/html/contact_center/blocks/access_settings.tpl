{* Карточка с настройками доступа к теме "Допы и прочее" *}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Менеджеры с доступом к теме "Допы и прочее"</h4>
        <button type="button" class="btn btn-primary save-managers" data-type="dopy">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">Выберите менеджеров, которые будут иметь доступ к тикетам с темой "Допы и прочее" и её дочерними темами.</p>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Доступные менеджеры</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="dopy-available-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="dopy-available-managers" multiple size="15">
                        {foreach $available_managers as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="col-md-2 text-center">
                <div style="margin-top: 100px;">
                    <button type="button" class="btn btn-primary btn-block mb-3" id="dopy-btn-move-right">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-block" id="dopy-btn-move-left">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-group">
                    <label>Менеджеры с доступом</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="dopy-selected-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="dopy-selected-managers" multiple size="15">
                        {foreach $authorized_dopy_managers as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{* Карточка с настройками доступа к типу "Взыскание" *}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Менеджеры с доступом к типу "Взыскание"</h4>
        <button type="button" class="btn btn-primary save-managers" data-type="collection">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">Выберите менеджеров, которые будут иметь доступ к управлению тикетам с темой "Взыскание" и её дочерними темами.</p>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Доступные менеджеры</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="collection-available-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="collection-available-managers" multiple size="15">
                        {foreach $available_managers as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="col-md-2 text-center">
                <div style="margin-top: 100px;">
                    <button type="button" class="btn btn-primary btn-block mb-3" id="collection-btn-move-right">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-block" id="collection-btn-move-left">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-group">
                    <label>Менеджеры с доступом</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="collection-selected-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="collection-selected-managers" multiple size="15">
                        {foreach $authorized_collection_managers as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{* Карточка с настройками автоматического назначения *}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Менеджеры для автоматического назначения тикетов</h4>
        <button type="button" class="btn btn-primary save-managers" data-type="auto_assign_ticket">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">Выберите менеджеров, которые будут автоматически назначаться на тикеты (допы и взыскание).</p>

        <div class="row">
            <div class="col-md-5">
                <div class="form-group">
                    <label>Доступные менеджеры с правами на тикеты</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="auto_assign_ticket-available-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="auto_assign_ticket-available-managers" multiple size="15">
                        {foreach $available_for_auto_assign as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="col-md-2 text-center">
                <div style="margin-top: 100px;">
                    <button type="button" class="btn btn-primary btn-block mb-3" id="auto_assign_ticket-btn-move-right">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-block" id="auto_assign_ticket-btn-move-left">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-group">
                    <label>Менеджеры для автоматического назначения</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="auto_assign_ticket-selected-search" placeholder="Поиск...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <select class="form-control" id="auto_assign_ticket-selected-managers" multiple size="15">
                        {foreach $auto_assign_ticket_managers as $manager}
                            <option value="{$manager->id}">{$manager->name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
