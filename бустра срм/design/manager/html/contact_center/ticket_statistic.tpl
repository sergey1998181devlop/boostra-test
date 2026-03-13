{$meta_title='Статистика по тикетам' scope=parent}

<link href="design/{$settings->theme|escape}/css/ticket_statistics.css?v=1.1" rel="stylesheet">

<div class="page-wrapper ticket-statistics">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-12 col-md-10 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-closed-caption"></i>
                    <span>Статистика</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Статистика</li>
                </ol>
            </div>
            <div class="col">
                <a href="tickets/statistics?action=download_report" class="btn btn-success float-right">
                    Скачать отчет
                </a>
            </div>
        </div>

        <!-- Карточка с вкладками -->
        <div class="card">
            <div class="card-body p-0">
                <!-- Навигация -->
                <ul class="nav nav-tabs" id="statsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="detailed-tab" data-toggle="tab" href="#detailed" role="tab">
                            Детальная статистика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="timing-tab" data-toggle="tab" href="#timing" role="tab">
                            Время обработки
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="statuses-tab" data-toggle="tab" href="#statistics-by-status-tab" role="tab">
                            Статистика по статусам
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="subjects-tab" data-toggle="tab" href="#statistics-by-subject-tab" role="tab">
                            Статистика по темам
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="assignment-tab" data-toggle="tab" href="#statistics-by-assignment-tab" role="tab">
                            Статистика автоназначения
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="complaints-manager-tab" data-toggle="tab" href="#complaints-by-manager-tab" role="tab">
                            Жалобы по менеджерам
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="complaints-responsible-tab" data-toggle="tab" href="#complaints-by-responsible-tab" role="tab">
                            Жалобы по ответственным
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-4" id="statsTabContent">
                    <!-- Вкладка детальной статистики -->
                    {include file="contact_center/blocks/detailed_statistics.tpl"}
                    <!-- Вкладка времени обработки -->
                    {include file='contact_center/blocks/ticket_processing_time_statistics.tpl'}
                    <!-- Вкладка "Статистика по статусам" -->
                    {include file='contact_center/blocks/statistics_by_statuses.tpl'}
                    <!-- Вкладка "Статистика по темам" -->
                    {include file='contact_center/blocks/statistics_by_subjects.tpl'}
                    <!-- Вкладка "Статистика автоназначения" -->
                    {include file='contact_center/blocks/statistics_by_assignment.tpl'}
                    <!-- Вкладка "Жалобы по менеджерам" -->
                    {include file='contact_center/blocks/complaints_statistics.tpl' type='manager'}
                    <!-- Вкладка "Жалобы по ответственным" -->
                    {include file='contact_center/blocks/complaints_statistics.tpl' type='responsible'}
                </div>
            </div>
        </div>
    </div>
</div>

{capture name='page_scripts'}
    <script src="/design/{$settings->theme}/js/ticket_statistics.js?v=1.3"></script>
{/capture}
