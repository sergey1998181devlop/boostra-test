{$meta_title='Настройки тикетов' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-12 col-md-10 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-ticket"></i>
                    <span>Настройки тикетов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active"><a href="/tickets">Тикеты</a></li>
                    <li class="breadcrumb-item active">Настройки ОПР</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                {* Навигация *}
                <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="access-tab" data-toggle="tab" href="#access" role="tab">
                            <i class="fas fa-users"></i> Настройки доступа
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="notifications-tab" data-toggle="tab" href="#notifications" role="tab">
                            <i class="fas fa-bell"></i> Уведомления
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="competency-tab" data-toggle="tab" href="#competency" role="tab">
                            <i class="fas fa-graduation-cap"></i> Компетенции менеджеров
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="sla-tab" data-toggle="tab" href="#sla" role="tab">
                            <i class="fas fa-clock"></i> SLA настройки
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="companies-tab" data-toggle="tab" href="#companies" role="tab">
                            <i class="fas fa-building"></i> Управление компаниями
                        </a>
                    </li>
                </ul>

                {* Контент вкладок *}
                <div class="tab-content p-4" id="settingsTabContent">
                    {* Вкладка настроек доступа *}
                    <div class="tab-pane fade show active" id="access" role="tabpanel">
                        {include file="contact_center/blocks/access_settings.tpl"}
                    </div>

                    {* Вкладка уведомлений *}
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        {include file="contact_center/blocks/notification_settings.tpl"}
                    </div>

                    {* Вкладка компетенций *}
                    <div class="tab-pane fade" id="competency" role="tabpanel">
                        {include file="contact_center/blocks/competency_settings.tpl"}
                    </div>

                    {* Вкладка SLA *}
                    <div class="tab-pane fade" id="sla" role="tabpanel">
                        {include file="contact_center/blocks/sla_settings.tpl"}
                    </div>

                    {* Вкладка компаний *}
                    <div class="tab-pane fade" id="companies" role="tabpanel">
                        {include file="contact_center/blocks/company_settings.tpl"}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* Подключение скриптов *}
{capture name='page_scripts'}
    {* Основной скрипт настроек *}
    <script src="/design/{$settings->theme}/js/ticket_settings.js?v=1.0"></script>
    
    {* Дополнительные скрипты для каждой вкладки *}
    <script src="/design/{$settings->theme}/js/ticket_access_settings.js?v=1.0"></script>
    <script src="/design/{$settings->theme}/js/ticket_notification_settings.js?v=1.0"></script>
    <script src="/design/{$settings->theme}/js/ticket_competency_settings.js?v=1.0"></script>
    <script src="/design/{$settings->theme}/js/ticket_sla_settings.js?v=1.0"></script>
    <script src="/design/{$settings->theme}/js/ticket_company_settings.js?v=1.0"></script>
{/capture}