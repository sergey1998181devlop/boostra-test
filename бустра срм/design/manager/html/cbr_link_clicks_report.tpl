{$meta_title = 'Отчет по кликам на ссылку ЦБ' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">Отчет по кликам на ссылку ЦБ</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Клики по ссылке ЦБ</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form class="form-inline mb-3" method="GET">
                            <div class="form-group mr-2">
                                <input type="date" name="date_from" class="form-control" value="{$date_from|escape}">
                            </div>
                            <div class="form-group mr-2">
                                <input type="date" name="date_to" class="form-control" value="{$date_to|escape}">
                            </div>
                            <button type="submit" class="btn btn-primary">Показать</button>
                            {if $filter_applied}
                                <a href="{url date_from=null date_to=null}" class="btn btn-secondary ml-2">Сбросить</a>
                            {/if}
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Дата/время</th>
                                    <th>IP</th>
                                    <th>Телефон</th>
                                    <th>ФИО</th>
                                    <th>Должник</th>
                                    <th>Срок просрочки</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $clicks as $item}
                                    <tr>
                                        <td>{$item->created|date} {$item->created|time}</td>
                                        <td>{$item->ip|escape}</td>
                                        <td>{$item->phone|escape}</td>
                                        <td>
                                            {if $item->user_id}
                                                <a href="client/{$item->user_id}" target="_blank">
                                                    {$item->fio|escape}
                                                </a>
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                        <td>{if $item->is_debtor}Да{else}Нет{/if}</td>
                                        <td>{if $item->debt_days}{$item->debt_days}{else}-{/if}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        {include file='html_blocks/pagination.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>