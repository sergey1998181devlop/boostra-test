{$canonical="user/tickets" scope=parent}

{$body_class = "gray" scope=parent}

{$add_order_css_js = true scope=parent}


{* Таблица тикетов *}
<div class="tickets-table-wrapper">
    <table id="ticketsTable" class="tickets-table">
        <thead class="tickets-table-head">
        <tr>
            {assign var="queryString" value=""}
            {foreach from=$smarty.get key=key item=value}
                {if $key neq 'sort'}
                    {assign var="queryString" value=$queryString|cat:"&"|cat:$key|cat:"="|cat:$value}
                {/if}
            {/foreach}

            <th class="tickets-table-header-cell"></th>

            <th class="tickets-table-header-cell">
                {if $smarty.get.sort == "usedesk_id_asc"}
                    {assign var="newSort" value="usedesk_id_desc"}
                    {assign var="sortIcon" value='&#9650;'} {* ▲ *}
                {elseif $smarty.get.sort == "usedesk_id_desc"}
                    {assign var="newSort" value=""}
                    {assign var="sortIcon" value='&#9660;'} {* ▼ *}
                {else}
                    {assign var="newSort" value="usedesk_id_asc"}
                    {assign var="sortIcon" value=''}
                {/if}
                <a href="{$viewUri}?sort={$newSort}{$queryString}">
                    № {$sortIcon}
                </a>
            </th>
            <th class="tickets-table-header-cell">
                {if $smarty.get.sort == "subject_asc"}
                    {assign var="newSort" value="subject_desc"}
                    {assign var="sortIcon" value='&#9650;'} {* ▲ *}
                {elseif $smarty.get.sort == "subject_desc"}
                    {assign var="newSort" value=""}
                    {assign var="sortIcon" value='&#9660;'} {* ▼ *}
                {else}
                    {assign var="newSort" value="subject_asc"}
                    {assign var="sortIcon" value=''}
                {/if}
                <a href="{$viewUri}?sort={$newSort}{$queryString}">
                    Тема {$sortIcon}
                </a>
            </th>
            <th class="tickets-table-header-cell">
                {if $smarty.get.sort == "created_at_asc"}
                    {assign var="newSort" value="created_at_desc"}
                    {assign var="sortIcon" value='&#9650;'} {* ▲ *}
                {elseif $smarty.get.sort == "created_at_desc"}
                    {assign var="newSort" value=""}
                    {assign var="sortIcon" value='&#9660;'} {* ▼ *}
                {else}
                    {assign var="newSort" value="created_at_asc"}
                    {assign var="sortIcon" value=''}
                {/if}
                <a href="{$viewUri}?sort={$newSort}{$queryString}">
                    Дата создания {$sortIcon}
                </a>
            </th>
            <th class="tickets-table-header-cell">
                {if $smarty.get.sort == "status_asc"}
                    {assign var="newSort" value="status_desc"}
                    {assign var="sortIcon" value='&#9650;'} {* ▲ *}
                {elseif $smarty.get.sort == "status_desc"}
                    {assign var="newSort" value=""}
                    {assign var="sortIcon" value='&#9660;'} {* ▼ *}
                {else}
                    {assign var="newSort" value="status_asc"}
                    {assign var="sortIcon" value=''}
                {/if}
                <a href="{$viewUri}?sort={$newSort}{$queryString}">
                    Статус {$sortIcon}
                </a>
            </th>
            <th class="tickets-table-header-cell">
                {if $smarty.get.sort == "updated_at_asc"}
                    {assign var="newSort" value="updated_at_desc"}
                    {assign var="sortIcon" value='&#9650;'} {* ▲ *}
                {elseif $smarty.get.sort == "updated_at_desc"}
                    {assign var="newSort" value=""}
                    {assign var="sortIcon" value='&#9660;'} {* ▼ *}
                {else}
                    {assign var="newSort" value="updated_at_asc"}
                    {assign var="sortIcon" value=''}
                {/if}
                <a href="{$viewUri}?sort={$newSort}{$queryString}">
                    Последнее обновление {$sortIcon}
                </a>
            </th>
        </tr>
        </thead>
        <tbody>
        {if $tickets}
            {foreach from=$tickets item=ticket}
                {include 'user_tickets/row.tpl' ticket=$ticket}
            {/foreach}
        {else}
            <tr>
                <td colspan="5" class="tickets-table-cell no-results">Ничего не найдено.</td>
            </tr>
        {/if}
        </tbody>
    </table>
</div>

{* Пагинация *}
{if $tickets|@count > 0}
    <div class="tickets-pagination">
        {assign var="start" value=($current_page - 1) * $per_page + 1}
        {assign var="end" value=$current_page * $per_page}
        {if $end > $total_items}
            {assign var="end" value=$total_items}
        {/if}
        <div class="tickets-showed">
            Показано: {$start}–{$end} из {$total_items}
        </div>
        {if $current_page > 1}
            <a class="tickets-btn tickets-btn-pagination"
               href="user/tickets?{http_build_query($smarty.get)}&page={$current_page-1}">Назад</a>
        {else}
            <span class="tickets-btn tickets-btn-pagination"
                  style="pointer-events: none; opacity: 0.5;">Назад</span>
        {/if}

        <span class="tickets-page-info">Страница {$current_page} из {$total_pages}</span>

        {if $current_page < $total_pages}
            <a class="tickets-btn tickets-btn-pagination"
               href="user/tickets?{http_build_query($smarty.get)}&page={$current_page+1}">Вперед</a>
        {else}
            <span class="tickets-btn tickets-btn-pagination"
                  style="pointer-events: none; opacity: 0.5;">Вперед</span>
        {/if}
    </div>
{/if}