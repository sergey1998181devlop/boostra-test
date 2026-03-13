
<script src="js/tasks.js"></script>
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i>Задачи по просрочкам</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Задачи по просрочкам</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Задачи по просрочкам</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; overflow-x: auto; width: auto; white-space: normal; padding-bottom: 15px;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr class="jsgrid-header-row">
                                        <th  class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'fio_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'fio_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'fio_asc'}<a href="{url page=null sort='fio_desc'}">ФИО</a>
                                            {else}<a href="{url page=null sort='fio_asc'}">ФИО</a>{/if}
                                        </th>
                                        <th  class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'phone_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'phone_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'phone_asc'}<a href="{url page=null sort='phone_desc'}">Телефон</a>
                                            {else}<a href="{url page=null sort='phone_asc'}">Телефон</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'number_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'number_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'number_asc'}<a href="{url page=null sort='number_desc'}">Займ</a>
                                            {else}<a href="{url page=null sort='number_asc'}">Займ</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_asc'}<a href="{url page=null sort='date_desc'}">Дата выдачи</a>
                                            {else}<a href="{url page=null sort='date_asc'}">Дата выдачи</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'summ_asc'} jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'summ_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'summ_asc'}<a href="{url page=null sort='summ_desc'}">Сумма</a>
                                            {else}<a href="{url page=null sort='summ_asc'}">Сумма</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_payment_asc'} jsgrid-header-sort jsgrid-header-sort-asc {elseif $sort == 'date_payment_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'date_payment_asc'}<a href="{url page=null sort='date_payment_desc'}">Дата возврата</a>
                                            {else}<a href="{url page=null sort='date_payment_asc'}">Дата возврата</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'payment_asc'}jsgrid-header-sort jsgrid-header-sort-asc {elseif $sort == 'payment_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'payment_asc'}<a href="{url page=null sort='payment_desc'}">Сумма возврата</a>
                                            {else}<a href="{url page=null sort='payment_asc'}">Сумма возврата</a>{/if}
                                        </th>
                                        <th class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'prolongation_asc'}jsgrid-header-sort jsgrid-header-sort-asc {elseif $sort == 'prolongation_desc'} jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'prolongation_asc'}<a href="{url page=null sort='prolongation_desc'}">Пролонгация</a>
                                            {else}<a href="{url page=null sort='prolongation_asc'}">Пролонгация</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'status_asc'}<a href="{url page=null sort='status_desc'}">Статус</a>
                                            {else}<a href="{url page=null sort='status_asc'}">Статус</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"></th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"><a href="{url page=null}">Количество дней просрочки</a></th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'tag_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'tag_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'tag_asc'}<a href="{url page=null sort='tag_desc'}">Статус (LPT)</a>
                                            {else}<a href="{url page=null sort='tag_asc'}">Статус (LPT)</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'lpt_status_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'lpt_status_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'lpt_status_asc'}<a href="{url page=null sort='lpt_status_desc'}">Статус (CRM)</a>
                                            {else}<a href="{url page=null sort='lpt_status_asc'}">Статус (CRM)</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable text-right jsgrid-header-cell jsgrid-header-sortable {if $sort == 'the_day_of_the_last_call_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'the_day_of_the_last_call_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                            {if $sort == 'the_day_of_the_last_call_asc'}<a href="{url page=null sort='the_day_of_the_last_call_desc'}">Дата последнего звонка</a>
                                            {else}<a href="{url page=null sort='the_day_of_the_last_call_asc'}">Дата последнего звонка</a>{/if}
                                        </th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"><a href="{url page=null}">Обещаете оплатить в течение 2-х дней</a></th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"><a href="{url page=null}">Готов продлить</a></th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"><a href="{url page=null}">Нужна ли помощь спеца</a></th>
                                        <th style="width: 50px;" class="text-right jsgrid-header-cell jsgrid-header-sortable jsgrid-header-sort jsgrid-header-sort-asc"><a href="{url page=null}">Комментарий</a></th>
                                        <!--
                                        <th>id</th>
                                        <th>lpt_id</th>
                                        <th>json</th>
                                        <th>status</th>
                                        <th>Опции</th>
                                        <th>tag</th>
                                        <th>custom_array</th>
                                        <th>created_at</th>
                                        <th>updated_at</th>
                                        -->
                                    </tr>
                                    {foreach $tasks AS $task}
                                        <tr class="jsgrid-row" id="main_{$task->user_id}">                                              
                                            <td  class="jsgrid-cell jsgrid-align-right">   
                                                <a href="client/{$task->user_id}" target="_blank">
                                                    {$task->lastname|escape} {$task->firstname|escape} {$task->patronymic|escape}
                                                </a>
                                            </td>
                                            <td  class="jsgrid-cell">
                                                <strong>{$task->phone_mobile|escape}</strong>
                                                <button class="btn waves-effect waves-light btn-xs btn-info float-right js-mango-call" data-phone="{$task->phone_mobile}" data-user="{$task->user_id}" title="Выполнить звонок">
                                                    <i class="fas fa-phone-square"></i>
                                                </button>
                                            </td>
                                            <td class="jsgrid-cell">
                                                {if $task}
                                                    {if $task->zaim_number != 'Нет открытых договоров'}
                                                        <a href="order/{$task->zaim_number}">{$task->zaim_number}</a>
                                                    {else}
                                                        <div>{$task->zaim_number}</div>
                                                    {/if}
                                                {/if}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {if $task->zaim_date|date == '01.01.0001'}
                                                    Не известна
                                                {else}
                                                    {$task->zaim_date|date}
                                                    <br />
                                                    <button class="js-get-movements btn btn-link btn-xs js-no-peni" data-number="{$task->zaim_number}">Операции</button>
                                                {/if}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {$task->zaim_summ}
                                            </td>
                                            <td class="jsgrid-cell">
                                                {if $task->payment_date|date == '01.01.0001'}
                                                    Не указано
                                                {else}
                                                {$task->payment_date|date}
                                                {/if}
                                            </td>
                                            <td class="jsgrid-cell">
                                                <a class="" href="#" data-toggle="collapse" data-target="#ostatok_{$task->user_id}">{$task->ostatok_od + $task->ostatok_percents + $task->ostatok_peni}</a>
                                                <div id="ostatok_{$task->user_id}" class="collapse">
                                                    <div>Основной долг: <strong>{$task->ostatok_od}</strong></div>
                                                    <div>Проценты: <strong>{$task->ostatok_percents}</strong></div>
                                                    <div>Пени: <strong>{$task->ostatok_peni}</strong></div>
                                                </div>
                                            </td>
                                            <td class="jsgrid-cell">
                                                {if $task->prolongation_amount > 0}
                                                    <a class="" href="#" data-toggle="collapse" data-target="#prolongation_{$task->user_id}">{$task->prolongation_amount}</a>
                                                {/if}
                                                {if $task->last_prolongation == 2}
                                                    <span class="label label-danger float-right" title="Количество пролонгаций займа">
                                                    {elseif $task->last_prolongation == 1}
                                                        <span class="label label-warning float-right" title="Количество пролонгаций займа">
                                                        {else}
                                                            <span class="label label-primary float-right" title="Количество пролонгаций займа">
                                                                <h6 class="m-0">{$task->prolongation_count}</h6>
                                                            </span>
                                                        {/if}
                                                        <div id="prolongation_{$task->user_id}" class="collapse">
                                                            {if $task->prolongation_summ_percents > 0}
                                                                <div>Проценты: <strong>{1 * $task->prolongation_summ_percents}</strong></div>
                                                            {/if}
                                                            {if $task->prolongation_summ_insurance > 0}
                                                                <div>Страховка: <strong>{1 * $task->prolongation_summ_insurance}</strong></div>
                                                            {/if}
                                                            {if $task->prolongation_summ_cost > 0}
                                                                <div>Пролонгация: <strong>{1 * $task->prolongation_summ_cost}</strong></div>
                                                            {/if}
                                                            {if $task->prolongation_summ_sms > 0}
                                                                <div>СМС-информ: <strong>{1 * $task->prolongation_summ_sms}</strong></div>
                                                            {/if}
                                                        </div>
                                                        {if $is_developer}
                                                            <br />
                                                            <small title="Дата обновления">{$task->last_update|date}</small>
                                                        {/if}
                                                        </td>
                                                        <td style="width: 50px;" class="jsgrid-cell text-right">
                                                            <div class="btn-group js-status-{$task->user_id}">
                                                                {if $task->cc_status == 0}<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Новая</button>{/if}
                                                                {if $task->cc_status == 1}<button type="button" class="btn btn-warning btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Буфер</button>{/if}
                                                                {if $task->cc_status == 2}<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Завершена</button>{/if}
                                                                <div class="dropdown-menu" x-placement="bottom-start">
                                                                    {if $task->cc_status != 0}<a class="dropdown-item text-info js-toggle-status" data-status="0" data-task="{$task->user_id}" href="javascript:void(0)">Новая</a>{/if}
                                                                    {if $task->cc_status != 1}<a class="dropdown-item text-warning js-toggle-status" data-status="1" data-task="{$task->user_id}" href="javascript:void(0)">Буфер</a>{/if}
                                                                    {if $task->cc_status != 2}<a class="dropdown-item text-success js-toggle-status" data-status="2" data-task="{$task->user_id}" href="javascript:void(0)">Завершена</a>{/if}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="jsgrid-cell">
                                                            <button type="button" class="btn btn-sm btn-success js-open-comment-form" title="Добавить комментарий" data-order="{$task->order_id}" data-user="{$task->user->id}" data-task="{$task->user_id}" data-uid="{$task->UID}">
                                                                <i class="mdi mdi-comment-text"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-primary js-open-sms-modal" title="Отправить смс" data-user="{$task->user->id}">
                                                                <i class=" far fa-share-square"></i>
                                                            </button>
                                                        </td>


                                                        <td class="jsgrid-cell">
                                                            {if $task->zaim_summ == '0.00' AND $task->ostatok_od + $task->ostatok_percents + $task->ostatok_peni == 0 OR $task->dayDelay < 1}
                                                                <span class="label label-primary label-success label-default label-warning label-danger float-right" title="Количество дней просрочки">
                                                                    <h6 class="m-0" >0</h6>
                                                                </span>
                                                            {else}
                                                                <span class="label {if $task->dayDelay == 1 }label-primary{elseif $task->dayDelay == 2 }label-success{elseif $task->dayDelay == 3 }label-default{elseif $task->dayDelay == 4 }label-warning{else}label-danger{/if} float-right" title="Количество дней просрочки">
                                                                    <h6 class="m-0" >{$task->dayDelay}</h6>
                                                                </span>
                                                            {/if}
                                                        </td>
                                                        <td class="jsgrid-cell">
                                                            <div class="btn-group js-status-{$task->user_id}">
                                                                {if $task->tag}
                                                                    <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">{$task->tag}</button>
                                                                {else}
                                                                    <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Установите значение</button>
                                                                {/if}
                                                                <div class="dropdown-menu" x-placement="bottom-start">
                                                                    {foreach $statuses as $idStatus => $valueStatus}
                                                                        {if {$task->tag|lower} != {$valueStatus|lower}}<a class="dropdown-item text-info js-toggle-status" data-status="0" data-task="{$task->id}" href="/update_lpt.php?id={$task->lpt_id}&status={$idStatus}">{$valueStatus}</a>{/if}
                                                                    {/foreach}
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="jsgrid-cell">
                                                            {$task->status}
                                                        </td>
                                                        <td class="jsgrid-cell">
                                                            <div class="btn-group js-status-{$task->user_id}">
                                                                {if $task->tag}
                                                                    <button type="button" class="js-get-movements btn btn-link btn-xs js-no-peni" title="Прослушать звонок" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> <i class="fa fa-play" style="padding-right: 10px;"></i> {$task->dateOfTheLastCall|date} </button>
                                                                    {/if}
                                                                <div class="dropdown-menu" x-placement="bottom-start">
                                                                    <audio src="{$task->recordCall}" controls />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="jsgrid-cell">{if !{$task->iPromiseToPayWithinTwoDays}}Нет ответа{else}{$task->iPromiseToPayWithinTwoDays}{/if}</td>
                                                        <td class="jsgrid-cell">{if !{$task->ReadyToExtend}}Нет ответа{else}{$task->ReadyToExtend}{/if}</td>
                                                        <td class="jsgrid-cell">
                                                        {if !{$task->professionalHelp}}Нет ответа{else}{$task->professionalHelp}{/if}
                                                    </td>
                                                    <td class="jsgrid-cell">
                                                        <div>
                                                            <div id="fild-{$task->id}{$task->id}{$task->id}-{$task->id}">
                                                                {$task->comment}
                                                            </div>
                                                            <form style="text-align: center;">
                                                                <textarea name="comment" id="comment-{$task->id}{$task->id}{$task->id}-{$task->id}" class="form-control"></textarea>
                                                                <br/>
                                                                <button type="button" class="btn waves-effect waves-light btn-rounded btn-xs btn-info" onClick="sendComment('{$task->id}');">Сохранить</button>
                                                            </form>
                                                        </div>
                                                    </td>

                                                    <!--
                                                    
                                                    -->
                                                    </tr>
                                                {/foreach}
                                                </table>
                                                {if $total_pages_num>1}

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

                                                    <div class="jsgrid-pager-container" style="padding: 10px;">
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
                                        {/if}
                                        </div>
                                        </div>
                                        </div>
                                        </div>
                                        </div>
                                        </div>
                                        </div>
                                        {include file='footer.tpl'}
                                        </div>