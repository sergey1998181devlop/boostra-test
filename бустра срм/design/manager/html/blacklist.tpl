{$meta_title = 'Черный Список' scope=parent}

{capture name='page_scripts'}
    <script type="text/javascript">
        function clearSearch() {
            let params = document.location.search.replace('?', '').split('&');
            let url = [];
            if (params) {
                for (let x in params) {
                    if (!~decodeURIComponent(params[x]).indexOf('search[')) {
                        url.push(params[x]);
                    }
                }
            }
            return url;
        }
        function getSearchData() {
            let searchData = clearSearch();
            $('.jsgrid-filter-row input').each(function() {
                let searchText = $(this).val().trim();
                if (searchText.length > 3) {
                    searchData.push($(this).attr('name') + '=' + encodeURIComponent(searchText));
                }
            })
            return searchData ? '?' + searchData.join('&') : '';
        }
        $(function() {
            $('.jsgrid-filter-row input').change(function() {
                location.search = getSearchData();
            });
        });
    </script>
{/capture}

{capture name='page_styles'}
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-grid-body, .jsgrid-grid-header {
            overflow-y: auto;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    Черный Список
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Черный Список</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
        <form class="" method="POST" >
            <div class="card">
                <div class="card-body">

                    <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                        <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                            <table class="jsgrid-table table table-striped table-hover">
                                <tr class="jsgrid-header-row">
                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                        {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                    </th>
                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                        {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                    </th>
                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'reason_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'reason_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'reason_asc'}<a href="{url page=null sort='reason_desc'}">Причина</a>
                                        {else}<a href="{url page=null sort='reason_asc'}">Причина</a>{/if}
                                    </th>
                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'comment_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'comment_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'comment_asc'}<a href="{url page=null sort='comment_desc'}">Комменты</a>
                                        {else}<a href="{url page=null sort='comment_asc'}">Комменты</a>{/if}
                                    </th>
                                    <th style="width: 60px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата добавления</a>
                                        {else}<a href="{url page=null sort='date_asc'}">Дата добавления</a>{/if}
                                    </th>
                                </tr>

                                <tr class="jsgrid-filter-row" id="search_form">
                                    <td style="width: 80px;" class="jsgrid-cell jsgrid-align-left">
                                        <input type="text" name="search[fio]" value="{$search['fio']}" class="form-control input-sm">
                                    </td>
                                    <td style="width: 80px;" class="jsgrid-cell jsgrid-align-left">
                                        <input type="text" name="search[phone]" value="{$search['phone']}" class="form-control input-sm">
                                    </td>
                                    <td style="width: 80px;" class="jsgrid-cell jsgrid-align-left">
                                        <input type="text" name="search[comment]" value="{$search['comment']}" class="form-control input-sm">
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                            </table>
                        </div>
                        <table class="jsgrid-grid-body jsgrid-table table table-striped table-hover">
                            <tbody>
                                {foreach $blacklist as $record}
                                    <tr class="jsgrid-row" id="main_{$record->id}">
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <a href="client/{$record->user_id}" target="_blank">
                                                {$record->firstname|escape}
                                                {$record->patronymic|escape}
                                                {$record->lastname|escape}
                                            </a>
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <strong>{$record->phone_mobile|escape}</strong>
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <strong>{$record->reason|escape}</strong>
                                        </td>
                                        <td style="width: 80px;" class="jsgrid-cell">
                                            <strong>{$record->comment|escape}</strong>
                                        </td>
                                        <td style="width: 60px;" class="jsgrid-cell">
                                            {$record->created_date|date}
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        {if $showCount > 1}
                            {$page_from = 1}
                            {if $currentPage > floor($showCount / 2)}
                                {$page_from = max(1, $currentPage - floor($showCount / 2) - 1)}
                            {/if}
                            {if $currentPage > $totalPage - ceil($showCount / 2)}
                                {$page_from = max(1, $totalPage - $showCount - 1)}
                            {/if}
                            {$page_to = min($page_from + $showCount, $totalPage - 1)}
                            <div class="jsgrid-pager-container">
                                <div class="jsgrid-pager">
                                    Страницы:
                                    {if $currentPage == 2}
                                        <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
                                    {elseif $currentPage > 2}
                                        <span class="jsgrid-pager-nav-button "><a href="{url page=$currentPage - 1}">Пред.</a></span>
                                    {/if}
                                    <span class="jsgrid-pager-page {if $currentPage == 1}jsgrid-pager-current-page{/if}">
                                        {if $currentPage == 1}1{else}<a href="{url page=null}">1</a>{/if}
                                    </span>
                                    {section name=pages loop=$page_to start=$page_from}
                                        {$p = $smarty.section.pages.index + 1}
                                        {if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $totalPage - 1)}
                                            <span class="jsgrid-pager-page {if $p==$currentPage}jsgrid-pager-current-page{/if}">
                                            <a href="{url page=$p}">...</a>
                                        </span>
                                        {else}
                                            <span class="jsgrid-pager-page {if $p == $currentPage}jsgrid-pager-current-page{/if}">
                                            {if $p==$currentPage}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                                        </span>
                                        {/if}
                                    {/section}

                                    {if $currentPage < $totalPage}
                                        <span class="jsgrid-pager-page"><a href="{url page=$totalPage}">{$totalPage}</a></span>
                                        <span class="jsgrid-pager-nav-button"><a href="{url page=$currentPage + 1}">След.</a></span>
                                    {/if}
                                    {$currentPage} из {$totalPage}
                                </div>
                            </div>
                        {/if}
                        <div class="mt-2">Показано: {$showCount}</div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>