{if $total_pages_num > 1}
    <div id="basicgrid" style="position: relative; width: 100%;">
        {* Количество выводимых ссылок на страницы *}
        {$visible_pages = 11}
        {* По умолчанию начинаем вывод со страницы 1 *}
        {$page_from = 1}

        {* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
        {if $current_page_num > floor($visible_pages/2)}
            {$page_from = max(1, $current_page_num-floor($visible_pages/2)-1)}
        {/if}

        {* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
        {if $current_page_num > $total_pages_num-ceil($visible_pages/2)}
            {$page_from = max(1, $total_pages_num-$visible_pages-1)}
        {/if}

        {* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
        {$page_to = min($page_from+$visible_pages, $total_pages_num-1)}

        <div class="jsgrid-pager-container" style="">
            <div class="jsgrid-pager">
                Страницы:

                {if $current_page_num == 2}
                    <span class="jsgrid-pager-nav-button "><a href="{url page=null}">Пред.</a></span>
                {elseif $current_page_num > 2}
                    <span class="jsgrid-pager-nav-button "><a href="{url page=$current_page_num-1}">Пред.</a></span>
                {/if}

                <span class="jsgrid-pager-page {if $current_page_num==1}jsgrid-pager-current-page{/if}">
                                            {if $current_page_num==1}1{else}<a href="{url page=null}">1</a>{/if}
                                        </span>
                {section name=pages loop=$page_to start=$page_from}
                    {* Номер текущей выводимой страницы *}
                    {$p = $smarty.section.pages.index+1}
                    {* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
                    {if ($p == $page_from + 1 && $p != 2) || ($p == $page_to && $p != $total_pages_num-1)}
                        <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                                <a href="{url page=$p}">...</a>
                                            </span>
                    {else}
                        <span class="jsgrid-pager-page {if $p==$current_page_num}jsgrid-pager-current-page{/if}">
                                                {if $p==$current_page_num}{$p}{else}<a href="{url page=$p}">{$p}</a>{/if}
                                            </span>
                    {/if}
                {/section}
                <span class="jsgrid-pager-page {if $current_page_num==$total_pages_num}jsgrid-pager-current-page{/if}">
                                            {if $current_page_num==$total_pages_num}{$total_pages_num}{else}<a href="{url page=$total_pages_num}">{$total_pages_num}</a>{/if}
                                        </span>

                {if $current_page_num<$total_pages_num}
                    <span class="jsgrid-pager-nav-button"><a href="{url page=$current_page_num+1}">След.</a></span>
                {/if}
                &nbsp;&nbsp; {$current_page_num} из {$total_pages_num}
            </div>
        </div>
        <div>Показано: {$items|@count}</div>
    </div>
{/if}

