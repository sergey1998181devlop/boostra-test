{* Информация о коэффициентах *}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calculator"></i> Расчет нагрузки менеджеров</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle text-muted"></i>
            <span class="text-muted">Коэффициенты нагрузки зависят от количества дней просрочки по займу</span>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            Soft
                            <div class="small">Просрочка 1-7 дней</div>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Базовый коэффициент:</span>
                            <span class="font-weight-bold">1.0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">С высоким приоритетом:</span>
                            <span class="font-weight-bold">1.2</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            Middle
                            <div class="small">Просрочка 8-30 дней</div>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Базовый коэффициент:</span>
                            <span class="font-weight-bold">1.5</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">С высоким приоритетом:</span>
                            <span class="font-weight-bold">1.8</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            Hard
                            <div class="small">Просрочка более 30 дней</div>
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Базовый коэффициент:</span>
                            <span class="font-weight-bold">2.0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">С высоким приоритетом:</span>
                            <span class="font-weight-bold">2.4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-light border mt-3 mb-0">
            <i class="fas fa-info-circle text-muted"></i>
            <span class="text-muted">Для тикетов с высоким приоритетом базовый коэффициент умножается на 1.2</span>
        </div>
    </div>
</div>

{* Настройки компетенций для "Допы и прочее" *}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Компетенции менеджеров для "Допы и прочее"</h4>
        <button type="button" class="btn btn-primary save-competencies" data-type="additional_services">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            {* Soft уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i> Soft (1-7 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-soft-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-soft-available" multiple size="8">
                                        {foreach $dopy_managers.soft.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="dopy-soft-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="dopy-soft-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-soft-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-soft-selected" multiple size="8">
                                        {foreach $dopy_managers.soft.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {* Middle уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i> Middle (8-30 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-middle-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-middle-available" multiple size="8">
                                        {foreach $dopy_managers.middle.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="dopy-middle-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="dopy-middle-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-middle-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-middle-selected" multiple size="8">
                                        {foreach $dopy_managers.middle.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {* Hard уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i> Hard (>30 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-hard-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-hard-available" multiple size="8">
                                        {foreach $dopy_managers.hard.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="dopy-hard-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="dopy-hard-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="dopy-hard-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="dopy-hard-selected" multiple size="8">
                                        {foreach $dopy_managers.hard.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* Настройки компетенций для "Взыскание" *}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Компетенции менеджеров для "Взыскание"</h4>
        <button type="button" class="btn btn-primary save-competencies" data-type="collection">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            {* Soft уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i> Soft (1-7 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-soft-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-soft-available" multiple size="8">
                                        {foreach $collection_managers.soft.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="collection-soft-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="collection-soft-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-soft-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-soft-selected" multiple size="8">
                                        {foreach $collection_managers.soft.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {* Middle уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i> Middle (8-30 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-middle-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-middle-available" multiple size="8">
                                        {foreach $collection_managers.middle.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="collection-middle-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="collection-middle-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-middle-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-middle-selected" multiple size="8">
                                        {foreach $collection_managers.middle.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {* Hard уровень *}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i> Hard (>30 дней)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Доступные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-hard-available-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-hard-available" multiple size="8">
                                        {foreach $collection_managers.hard.available as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 text-center my-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="collection-hard-btn-move-down">
                                        <i class="fas fa-arrow-down"></i> Добавить
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="collection-hard-btn-move-up">
                                        <i class="fas fa-arrow-up"></i> Убрать
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Назначенные менеджеры</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="collection-hard-selected-search" placeholder="Поиск...">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <select class="form-control" id="collection-hard-selected" multiple size="8">
                                        {foreach $collection_managers.hard.selected as $manager}
                                            <option value="{$manager->id}">{$manager->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>