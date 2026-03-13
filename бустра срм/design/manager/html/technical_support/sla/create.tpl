{$meta_title='Настройки SLA' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/css/create_ticket.css" rel="stylesheet"/>
    <link href="design/manager/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="page-header">
            <div>
                <h3 class="page-title"><i class="mdi mdi-settings mr-2"></i>Настройки SLA</h3>
                <ol class="breadcrumb p-0 mb-0 mt-2">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Тех. поддержка</li>
                    <li class="breadcrumb-item active">SLA</li>
                    <li class="breadcrumb-item active">Создание</li>
                </ol>
            </div>

            <div class="col-md-6 col-4 float-right">
                <form action="/technical-support/sla/save/" method="POST" id="add-ts-sla" class="js-order-item-form">
                    <input type="hidden" name="action" value="sla_save"/>
                    <button type="submit" class="btn btn-success float-right">Создать SLA</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Основная информация</h5>
                    </div>

                    <div class="card-body">
                        <div class="form-row">
                            <label for="quarter">Квартал <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select id="quarter" name="quarter" class="form-control" required="required">
                                    <option value="" disabled selected>Выбрать квартал</option>
                                    {foreach $quarterMap as $quarter}
                                        <option value="{$quarter.id}">{$quarter.name|escape}</option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите квартал
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="year">Год <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <input type="number" name="year" id="year" class="form-control" placeholder="Год квартала">
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите год
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="priority">Приоритет <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select id="priority" name="priority" class="form-control" required="required">
                                    <option value="" disabled selected>Выбрать приоритет</option>
                                    {foreach $priorities as $priority}
                                        <option
{*                                            class="badge badge-pill"*}
                                            style="background-color: {$priority.color}"
                                            value="{$priority.id}"
                                        >
                                            {$priority.name}
                                        </option>
                                    {/foreach}
                                </select>
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите приоритет
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="react_limit_minutes">Лимит по времени реакции в минутах <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <input type="number" step="0.01" name="react_limit_minutes" id="react_limit_minutes" class="form-control" placeholder="Лимит по времени реакции (мин.)">
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите лимит по реакции в минутах
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="react_limit_percents">Плановый процент попадания в SLA для реакции <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <input type="number" step="0.01" name="react_limit_percents" id="react_limit_percents" class="form-control" placeholder="Лимит по времени реакции (%)">
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите лимит по реакции в процентах
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="resolve_limit_minutes">Лимит по времени решения в минутах <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <input type="number" step="0.01" name="resolve_limit_minutes" id="resolve_limit_minutes" class="form-control" placeholder="Лимит по времени решения (мин.)">
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите лимит по решению в минутах
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <label for="resolve_limit_percents">Плановый процент попадания в SLA для решения <span class="text-danger">*</span></label>
                            <div class="form-input">
                                <input type="number" step="0.01" name="resolve_limit_percents" id="resolve_limit_percents" class="form-control" placeholder="Лимит по времени решения (%)">
                                <div class="invalid-feedback">
                                    Пожалуйста, выберите лимит по решению в процентах
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/i18n/ru.js"></script>
    <script src="design/manager/js/create_ts_sla.js"></script>
{/capture}