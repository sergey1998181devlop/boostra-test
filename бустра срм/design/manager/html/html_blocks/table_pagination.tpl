<nav id="pagination-nav">
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">
            {if isset($items) && is_array($items)}
                Показано {$items|count} из {$total_items}
            {else}
                Показано 0 из {$total_items}
            {/if}

        </div>
        {if $total_pages_num > 1}
            <ul class="pagination mb-0">
                {* Кнопка "Предыдущая" *}
                <li class="page-item {if $current_page_num == 1}disabled{/if}">
                    <a class="page-link"
                       href="#"
                       data-page="{if $current_page_num > 1}{$current_page_num-1}{/if}">
                        <span class="d-none d-sm-inline">Предыдущая</span>
                        <span class="d-inline d-sm-none">&laquo;</span>
                    </a>
                </li>

                {* Номера страниц *}
                {$visible_pages = 5}
                {$half_visible = floor($visible_pages/2)}

                {* Вычисляем начальную страницу *}
                {$page_from = max(1, min(
                $current_page_num - $half_visible,
                $total_pages_num - $visible_pages + 1
                ))}

                {* Вычисляем конечную страницу *}
                {$page_to = min($page_from + $visible_pages - 1, $total_pages_num)}

                {* Показываем первую страницу *}
                {if $page_from > 1}
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                    {if $page_from > 2}
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    {/if}
                {/if}

                {* Показываем страницы *}
                {for $page=$page_from to $page_to}
                    <li class="page-item {if $page == $current_page_num}active{/if}">
                        <a class="page-link" href="#" data-page="{$page}">{$page}</a>
                    </li>
                {/for}

                {* Показываем последнюю страницу *}
                {if $page_to < $total_pages_num}
                    {if $page_to < $total_pages_num-1}
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    {/if}
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="{$total_pages_num}">{$total_pages_num}</a>
                    </li>
                {/if}

                {* Кнопка "Следующая" *}
                <li class="page-item {if $current_page_num == $total_pages_num}disabled{/if}">
                    <a class="page-link"
                       href="#"
                       data-page="{if $current_page_num < $total_pages_num}{$current_page_num+1}{/if}">
                        <span class="d-none d-sm-inline">Следующая</span>
                        <span class="d-inline d-sm-none">&raquo;</span>
                    </a>
                </li>
            </ul>
        {/if}
    </div>
</nav>