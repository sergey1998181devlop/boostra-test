{capture name=tabs}
		<li class="active"><a href="{url module=StatsAdmin}">Статистика</a></li>
{/capture}
{$meta_title='Статистика' scope=parent}

{literal}


<style>
    #filter > * {
        display:inline-block;
    }
    .period_select {
        margin-right:20px;
    }
    .datepicker-block {
        position: relative;
    }
    .datepicker-block > span {
        position: absolute;
        display:block;
        height:20px;
        width:20px;
        background:#eee url('design/images/date_task.png') center no-repeat;
        border:1px solid #999;
        border-radius:3px;
        right:0;
        top:0;
        z-index:9;
        cursor:pointer;
    }
    .datepicker-block.disabled > span {
        cursor:default;
        opacity:0.5
    }
    .table_stats {
        width:100%;
    }
    .table_stats td {
        text-align:center;
        padding:10px 15px;
        border-bottom:1px dotted #ccc;
    }
    .table_stats td:first-child {
        text-align:left;
    }
    .table_stats th {
        position:relative;
        line-height:40px;
        vertical-align:middle;
        font-size:14px;
        background:#ddd;
        color:#333;
        border-bottom:5px double #aaa;
    }
    .table_stats th:hover {
        background:#bbb;
    }
    .table_stats .stat_item:nth-child(even) {
        background:#f9f9f9;
    }
    .table_stats .stat_item:nth-child(even):hover {
        background:#f5f5f5;
    }
    .table_stats .stat_item:nth-child(odd) {
        background:#f0f0f0;
    }
    .table_stats .stat_item:nth-child(odd):hover {
        background:#eaeaea;
    }
    [name=sort] {
        position:absolute;
        right:10px;
        border:0;
        background:transparent;
        display:block;
        width:20px;
        height:20px;
        text-align:center;
        opacity:0.3;
        line-height:20px;
        cursor:pointer;
    }
    [name=sort]:hover {
        opacity:0.8;
    }
    [name=sort].active {
        opacity:1;
    }
    [name=sort] img{
        display:inline-block;
        vertical-align:middle;
    }
    [name=sort].asc {
        top:0;
    } 
    [name=sort].desc {
        top:20px;
    } 
    .red {
        color:#d22;
        margin-bottom:15px;
        font-size:12px;
    }
    
</style>

<script src="design/js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>
<script>
    $(function(){
        
        $('.datepicker-block').each(function(){
            var $this = $(this);
            var $picker = $this.find('input');
            
            $picker.datepicker({
                regional: 'ru',
                
            });
            $this.find('span').click(function(){
                if (!$this.hasClass('disabled'))
                    $picker.datepicker('show')
            })
        });
        
        $('[name=period]').change(function(){
            if ($(this).val() == 'optional')
            {
                $('.datepicker-block').removeClass('disabled');
                $('.datepicker-block input').removeAttr('disabled');
            }
            else
            {
                $('.datepicker-block').addClass('disabled');
                $('.datepicker-block input').attr('disabled', true);
            }
        });
        /*
        $('#stat_form').live('submit', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    
                },
                success: function(resp){
                    $('#stat_result').html($(resp).find('#stat_result'));
                }
            })
        });
        */
    })
</script>

{/literal}

<div>

    <form method="POST" id="stat_form">
    	<input type="hidden" name="session_id" value="{$smarty.session.id}">
        <div class="block " id="filter">
            <select name="period" class="period_select">
                <option value="today" {if $period == 'today'}selected=""{/if}>Сегодня</option>
                <option value="yesterday" {if $period == 'yesterday'}selected=""{/if}>Вчера</option>
                <option value="week" {if $period == 'week'}selected=""{/if}>На этой неделе</option>
                <option value="month" {if $period == 'month'}selected=""{/if}>В этом месяце</option>
                <option value="optional" {if $period == 'optional'}selected=""{/if}>Выбрать период</option>
            </select>
            
            <div class="datepicker-block {if $period != 'optional'}disabled{/if}">
                <input type="text" name="from" value="{if $from}{$from|date}{/if}" {if $period != 'optional'}disabled{/if} />
                <span></span>
            </div>
            -
            <div class="datepicker-block {if $period != 'optional'}disabled{/if}">
                <input type="text" name="to" value="{if $to}{$to|date}{/if}" {if $period != 'optional'}disabled{/if} />
                <span></span>
            </div>
            
            <input type="submit" class="button_green" value="Показать" />
        </div>
    
        <div class="block " id="stat_result">
            {if $stats}
            <table class="table_stats">
                <tr>
                    <th class="source_cell">
                        <strong>Сеть</strong>
                        <button type="submit" name="sort" value="source_asc" class="asc {if $sort=='source_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="source_desc" class="desc {if $sort=='source_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                    <th class="webmaster_cell">
                        <strong>Вебмастер</strong>
                        <button type="submit" name="sort" value="webmaster_asc" class="asc {if $sort=='webmaster_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="webmaster_desc" class="desc {if $sort=='webmaster_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                    <th class="referral_cell">
                        <strong>Переходы</strong>
                        <button type="submit" name="sort" value="referral_asc" class="asc {if $sort=='referral_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="referral_desc" class="desc {if $sort=='referral_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                    <th class="order_cell">
                        <strong>Заявки</strong>
                        <button type="submit" name="sort" value="order_asc" class="asc {if $sort=='order_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="order_desc" class="desc {if $sort=='order_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                    <th class="amount_cell">
                        <strong>Сумма</strong>
                        <button type="submit" name="sort" value="amount_asc" class="asc {if $sort=='amount_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="amount_desc" class="desc {if $sort=='amount_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                    <th class="conversion_cell">
                        <strong>Конверсия</strong>
                        <button type="submit" name="sort" value="conversion_asc" class="asc {if $sort=='conversion_asc'}active{/if}"><img src="design/images/bullet_arrow_up.png" /></button>
                        <button type="submit" name="sort" value="conversion_desc" class="desc {if $sort=='conversion_desc'}active{/if}"><img src="design/images/bullet_arrow_down.png" /></button>
                    </th>
                </tr>
                {foreach $stats as $stat}
                <tr class="stat_item">
                    <td class="source_cell">
                        <strong>{$stat->utm_source}</strong>
                    </td>
                    <td class="webmaster_cell">
                        <span>{$stat->webmaster_id}</span>
                    </td>
                    <td class="referral_cell">
                        {$stat->count}
                    </td>
                    <td class="order_cell">
                        {$stat->count_orders}
                        {if $stat->count_orders > 0}
                        (
                        <span style="color:green;cursor:help" title="Количество заявок новых клиентов">{$stat->count_first}</span>
                        +
                        <span style="color:blue;cursor:help" title="Количество повторных заявок">{$stat->count_orders - $stat->count_first}</span>
                        )
                        {/if}
                    </td>
                    <td class="amount_cell">
                        {$stat->amount}
                    </td>
                    <td class="conversion_cell">
                        {$stat->conversion} %
                    </td>
                </tr>
                {/foreach}
            </table>
            {/if}
        </div>
    </form>

</div>