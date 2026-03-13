{$meta_title='Отчёт по спящей базе клиентов' scope=parent}

{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/table-manager.js"></script>

    <script>
        $(function() {
            const dormantClientsTable = new TableManager({
                tableId: 'dormant-clients',
                filterFormId: 'filter-form',
                storageKey: 'columnWidthsInDormantClientsPage',
                url: '{$reportUri}',
                dateRangeSelector: 'input[name="daterange"]',
                dateRangeOptions: {
                    autoApply: true,
                    locale: {
                        format: 'DD.MM.YYYY'
                    }
                }
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'Font Awesome 5 Free';
            font-style: normal;
            font-weight: 400;
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot");
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot?#iefix") format("embedded-opentype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff2") format("woff2"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff") format("woff"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.ttf") format("truetype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.svg#fontawesome") format("svg");
        }

        th {
            border: 1px solid rgba(0, 0, 0, 0.4) !important;
        }

        thead tr:first-of-type {
            background-color: #1a1f27;
        }

        thead tr:nth-of-type(2) {
            background-color: #383f48;
        }

        .sortable::after {
            position: relative;
        }

        .sortable::after {
            font-family: 'Font Awesome 5 Free';
            content: '\f0dc';
            font-weight: 900;
            margin-left: 5px;
        }

        .asc::after {
            content: '\f0de';
            font-family: 'Font Awesome 5 Free';

        }

        .desc::after {
            content: '\f0dd';
            font-family: 'Font Awesome 5 Free';
        }

        th a, td a {
            color: inherit;
            font-weight: 500;
            text-decoration: none;
        }

        th a:hover, td a:hover {
            color: #ababab;
            text-decoration: none;
        }
    </style>
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>{$meta_title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{$meta_title} за период {if $date_from}{$date_from} - {$date_to}{/if}</h4>

                        <form id="filter-form">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $date_from && $date_to}{$date_from} - {$date_to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-info">Отфильтровать</button>
                                    <button type="button" class="btn btn-success download-btn">
                                        <i class="ti-save"></i> Выгрузить
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div id="result" class="">
                            <table class="table table-bordered table-hover" id="dormant-clients">
                                <thead class="position-sticky">
                                    <tr>
                                        <th>Клиент</th>
                                        <th>Номер телефона</th>
                                        <th scope="col" class="resizable" data-column="date">
                                            <a href="#" class="sortable {if $sort == 'date'}asc{elseif $sort == '-date'}desc{/if}" data-sort="{($sort=='date') ? '-date' : 'date'}">
                                                Последний займ
                                            </a>
                                        </th>
                                        <th>Компания</th>
                                        <th>Закрылся до просрочки</th>
                                        <th>Брал доп. услуги</th>
                                        <th>Возвращал доп. услуги</th>
                                        <th>Балл скориста</th>
                                        <th>Отправлял жалобу</th>
                                    </tr>
                                    <tr id="filter-row">
                                        <th><input type="hidden" name="sort" value="{$sort|escape}"></th>
                                        <th></th>
                                        <th></th>
                                        <th>
                                            <select name="company_id" class="form-control">
                                                <option value="">Выберите компанию</option>
                                                {foreach $companies as $company}
                                                    <option value="{$company->id}" {if $company->id == $filters['company_id']}selected{/if}>{$company->short_name|escape}</option>
                                                {/foreach}
                                            </select>
                                        </th>
                                        <th>
                                            <select name="closed_before_due" class="form-control">
                                                <option value="">Все</option>
                                                <option value="yes" {if $filters.closed_before_due == 'yes'}selected{/if}>Да</option>
                                                <option value="no" {if $filters.closed_before_due == 'no'}selected{/if}>Нет</option>
                                            </select>
                                        </th>
                                        <th>
                                            <select name="has_additional_services" class="form-control">
                                                <option value="">Все</option>
                                                <option value="yes" {if $filters.has_additional_services == 'yes'}selected{/if}>Да</option>
                                                <option value="no" {if $filters.has_additional_services == 'no'}selected{/if}>Нет</option>
                                            </select>
                                        </th>
                                        <th>
                                            <select name="returned_additional_service" class="form-control">
                                                <option value="">Все</option>
                                                <option value="yes" {if $filters.returned_additional_service == 'yes'}selected{/if}>Да</option>
                                                <option value="no" {if $filters.returned_additional_service == 'no'}selected{/if}>Нет</option>
                                            </select>
                                        </th>
                                        <th>
                                            <div class="input-group">
                                                <input type="number" name="scorista_ball_min" value="{$filters.scorista_ball_min}"
                                                       class="form-control input-sm" placeholder="От">
                                                <input type="number" name="scorista_ball_max" value="{$filters.scorista_ball_max}"
                                                       class="form-control input-sm" placeholder="До">
                                            </div>
                                        </th>
                                        <th>
                                            <select name="has_complaint" class="form-control">
                                                <option value="">Все</option>
                                                <option value="yes" {if $filters.has_complaint == 'yes'}selected{/if}>Да</option>
                                                <option value="no" {if $filters.has_complaint == 'no'}selected{/if}>Нет</option>
                                            </select>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $item}
                                        <tr>
                                            <td>
                                                <a href="/client/{$item->user_id}">{$item->firstname} {$item->lastname} {$item->patronymic|escape}</a>
                                            </td>
                                            <td><a href="tel:{$item->phone_mobile}">{$item->phone_mobile}</a></td>
                                            <td><a href="order/{$item->order_id}">{$item->order_id}</a> от {$item->date}</td>
                                            <td>{$item->organization_name}</td>
                                            <td>{($item->closed_before_due) ? 'Да' : 'Нет'}</td>
                                            <td>{($item->has_additional_services) ? 'Да' : 'Нет'}</td>
                                            <td>{($item->returned_additional_service) ? 'Да' : 'Нет'}</td>
                                            <td>{(!empty($item->scorista_ball)) ? $item->scorista_ball : 'Не пройден'}</td>
                                            <td>{($item->has_complaint) ? 'Да' : 'Нет'}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="8" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>

                            {include file='html_blocks/table_pagination.tpl'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>